<?php
/**
 * Plugin Name: Recipe AI Summary Notice for Firefox
 * Description: Shows mobile Firefox visitors a recipe summary warning with a choice to continue to the original recipe.
 * Version: 1.4.0
 * Plugin URI: https://github.com/And-or-Labs/recipe-ai-summary-notice
 * Author: And/or Labs Inc.
 * Author URI: https://github.com/And-or-Labs
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * License: GPL-2.0-or-later
 * Text Domain: recipe-ai-summary-notice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Recipe_Warning {
	const OPTION = 'recipe_warning_options';
	const META = '_recipe_warning_enabled';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_init', array( __CLASS__, 'privacy_policy' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( __CLASS__, 'action_links' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'save_post', array( __CLASS__, 'save_meta' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	public static function defaults() {
		return array(
			'enabled' => true,
			'dismissible' => true,
			'title'   => __( 'Keep the original recipe', 'recipe-ai-summary-notice' ),
			'message' => __( "Firefox offers optional AI summaries on some mobile devices. A generated summary can differ from the original recipe.\n\nYou can continue to the original recipe or copy this link to use another browser.", 'recipe-ai-summary-notice' ),
		);
	}

	public static function required_message() {
		return __( "Firefox offers optional AI summaries on some mobile devices. A generated summary can differ from the original recipe.\n\nYou can copy this link to use another browser. If you have disabled summaries, confirm below to view the original recipe.", 'recipe-ai-summary-notice' );
	}

	public static function visitor_message( $options ) {
		$defaults = self::defaults();
		// Match only stock copy, allowing textarea line-ending differences.
		$message = trim( str_replace( array( "\r\n", "\r" ), "\n", $options['message'] ) );
		if ( ! $options['dismissible'] && $message === $defaults['message'] ) {
			return self::required_message();
		}
		return $options['message'];
	}

	public static function options() {
		$saved = get_option( self::OPTION, array() );
		$saved = is_array( $saved ) ? $saved : array();
		$original = "Firefox can generate an AI summary of this recipe. That summary is not tested by this site.\n\nWe have tested our recipe, but whatever slop version Firefox generates, we have no way to test it. We don't want people making a terrible slop recipe with our name on it, and getting blamed for how it comes out.";
		// Textareas and platform exports can change line endings and outer whitespace.
		$stored_message = isset( $saved['message'] ) && is_string( $saved['message'] ) ? trim( str_replace( array( "\r\n", "\r" ), "\n", $saved['message'] ) ) : null;
		if ( $original === $stored_message ) {
			unset( $saved['message'] );
		}
		return self::sanitize_options( wp_parse_args( $saved, self::defaults() ) );
	}

	public static function admin_menu() {
		add_options_page( __( 'Recipe AI Summary Notice for Firefox', 'recipe-ai-summary-notice' ), __( 'Recipe AI Summary Notice for Firefox', 'recipe-ai-summary-notice' ), 'manage_options', 'recipe-warning', array( __CLASS__, 'settings_page' ) );
	}

	public static function register_settings() {
		register_setting( 'recipe_warning', self::OPTION, array(
			'type'              => 'array',
			'sanitize_callback' => array( __CLASS__, 'sanitize_options' ),
			'default'           => self::defaults(),
		) );
	}

	private static function limit_text( $text, $length ) {
		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $text, 0, $length, 'UTF-8' );
		}
		// Unicode matching keeps a truncated field valid without mbstring.
		if ( preg_match( '/^.{0,' . (int) $length . '}/us', $text, $matches ) ) {
			return $matches[0];
		}
		return '';
	}

	public static function sanitize_options( $input ) {
		$input = is_array( $input ) ? $input : array();
		$defaults = self::defaults();
		$title = isset( $input['title'] ) && is_string( $input['title'] ) ? sanitize_text_field( $input['title'] ) : '';
		$message = isset( $input['message'] ) && is_string( $input['message'] ) ? sanitize_textarea_field( $input['message'] ) : '';
		return array(
			'enabled' => isset( $input['enabled'] ) && in_array( $input['enabled'], array( true, 1, '1' ), true ),
			'dismissible' => ! array_key_exists( 'dismissible', $input ) || in_array( $input['dismissible'], array( true, 1, '1' ), true ),
			'title'   => '' !== trim( $title ) ? self::limit_text( $title, 180 ) : $defaults['title'],
			'message' => '' !== trim( $message ) ? self::limit_text( $message, 4000 ) : $defaults['message'],
		);
	}

	public static function action_links( $links ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'options-general.php?page=recipe-warning' ) ) . '">' . esc_html__( 'Settings', 'recipe-ai-summary-notice' ) . '</a>' );
		return $links;
	}

	public static function privacy_policy() {
		if ( function_exists( 'wp_add_privacy_policy_content' ) ) {
			wp_add_privacy_policy_content( __( 'Recipe AI Summary Notice for Firefox', 'recipe-ai-summary-notice' ), '<p>' . esc_html__( 'Suggested text: Recipe AI Summary Notice for Firefox stores an expiry timestamp in your browser local storage under recipe-warning-bypass-v1 only when you confirm that summaries are disabled. This suppresses the warning for seven days for this site origin in that browser. Expired entries are removed when the warning next checks storage; they may remain until then. You can remove it by clearing this site’s browser storage. The plugin does not set cookies, track visitors, or send visitor data to external services.', 'recipe-ai-summary-notice' ) . '</p>' );
		}
	}


	public static function admin_assets( $hook ) {
		if ( 'settings_page_recipe-warning' !== $hook || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$base = plugin_dir_url( __FILE__ );
		wp_enqueue_style( 'recipe-warning-admin', $base . 'assets/admin.css', array( 'wp-components' ), '1.4.0' );
		wp_enqueue_script( 'recipe-warning-admin', $base . 'assets/admin.js', array( 'wp-element', 'wp-components' ), '1.4.0', true );
		$config = array(
			'options' => self::options(),
			'defaults' => self::defaults(),
			'requiredMessage' => self::required_message(),
			'strings' => array(
				'visibility' => __( 'Warning', 'recipe-ai-summary-notice' ),
				'enable' => __( 'Enable on recipe pages', 'recipe-ai-summary-notice' ),
				'dismissible' => __( 'Allow readers to dismiss the notice', 'recipe-ai-summary-notice' ),
				'dismissibleHelp' => __( 'When off, Continue, close, Escape, and clicking outside cannot dismiss the notice. Readers can still copy the link or confirm that summaries are disabled to dismiss it for seven days. This does not verify browser settings or block AI summaries.', 'recipe-ai-summary-notice' ),
				'copyLink' => __( 'Copy link for another browser', 'recipe-ai-summary-notice' ),
				'bypass' => __( 'I’ve disabled summaries', 'recipe-ai-summary-notice' ),
				'limits' => __( 'This warning does not prevent AI summaries or verify browser settings.', 'recipe-ai-summary-notice' ),
				'copy' => __( 'Visitor wording', 'recipe-ai-summary-notice' ),
				'title' => __( 'Title', 'recipe-ai-summary-notice' ),
				'message' => __( 'Message', 'recipe-ai-summary-notice' ),
				'help' => __( 'Plain text. Blank fields restore the default wording. Title: 180 characters. Message: 4,000 characters.', 'recipe-ai-summary-notice' ),
				'save' => __( 'Save Changes', 'recipe-ai-summary-notice' ),
				'preview' => __( 'Wording preview', 'recipe-ai-summary-notice' ),
				'previewHelp' => __( 'Updates as you type. Save Changes to apply your wording. Appearance follows your site’s theme.', 'recipe-ai-summary-notice' ),
				'label' => __( 'A note for Firefox readers', 'recipe-ai-summary-notice' ),
				'proceed' => __( 'Continue to the original recipe', 'recipe-ai-summary-notice' ),
			),
		);
		wp_add_inline_script( 'recipe-warning-admin', 'window.rwAdmin = ' . wp_json_encode( $config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . ';', 'before' );
	}

	public static function settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$options = self::options();
		// This parameter only selects a read-only settings panel.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tab = isset( $_GET['tab'] ) && is_string( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'settings';
		$tabs = array( 'settings' => __( 'Settings', 'recipe-ai-summary-notice' ), 'context' => __( 'Context', 'recipe-ai-summary-notice' ), 'about' => __( 'About & privacy', 'recipe-ai-summary-notice' ) );
		if ( ! isset( $tabs[ $tab ] ) ) {
			$tab = 'settings';
		}
		?>
		<div class="wrap rw-admin">
			<h1><?php esc_html_e( 'Recipe AI Summary Notice for Firefox', 'recipe-ai-summary-notice' ); ?></h1>
			<nav class="nav-tab-wrapper" aria-label="<?php esc_attr_e( 'Recipe AI Summary Notice for Firefox settings', 'recipe-ai-summary-notice' ); ?>">
				<?php foreach ( $tabs as $key => $label ) : ?>
					<a class="nav-tab <?php echo $key === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( array( 'page' => 'recipe-warning', 'tab' => $key ), admin_url( 'options-general.php' ) ) ); ?>" <?php if ( $key === $tab ) { echo 'aria-current="page"'; } ?>><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
			</nav>
			<div class="rw-admin-content">
			<?php if ( 'settings' === $tab ) : ?>
			<div class="rw-intro">
			<p><?php esc_html_e( 'Show a warning to mobile Firefox visitors on recipe pages. Choose whether readers can dismiss it. Only confirmation that summaries are disabled is remembered for seven days on this site.', 'recipe-ai-summary-notice' ); ?></p>
			<p><?php esc_html_e( 'This warning does not prevent AI summaries or verify browser settings.', 'recipe-ai-summary-notice' ); ?></p>
			</div>
			<form action="options.php" method="post" class="rw-settings-form">
				<?php settings_fields( 'recipe_warning' ); ?>
				<div id="rw-admin-editor"></div>
				<div id="rw-admin-fallback" class="rw-admin-card">
				<p><?php esc_html_e( 'This warning does not prevent AI summaries or verify browser settings.', 'recipe-ai-summary-notice' ); ?></p>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Warning', 'recipe-ai-summary-notice' ); ?></th>
						<td><label><input type="checkbox" name="recipe_warning_options[enabled]" value="1" <?php checked( ! empty( $options['enabled'] ) ); ?>> <?php esc_html_e( 'Enable on recipe pages', 'recipe-ai-summary-notice' ); ?></label></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Dismissal', 'recipe-ai-summary-notice' ); ?></th>
						<td><input type="hidden" name="recipe_warning_options[dismissible]" value="0"><label><input type="checkbox" name="recipe_warning_options[dismissible]" value="1" <?php checked( $options['dismissible'] ); ?>> <?php esc_html_e( 'Allow readers to dismiss the notice', 'recipe-ai-summary-notice' ); ?></label><p class="description"><?php esc_html_e( 'When off, Continue, close, Escape, and clicking outside cannot dismiss the notice. Readers can still copy the link or confirm that summaries are disabled to dismiss it for seven days. This does not verify browser settings or block AI summaries.', 'recipe-ai-summary-notice' ); ?></p></td>
					</tr>
					<tr>
						<th scope="row"><label for="recipe-warning-title"><?php esc_html_e( 'Title', 'recipe-ai-summary-notice' ); ?></label></th>
						<td><input class="regular-text" id="recipe-warning-title" name="recipe_warning_options[title]" maxlength="180" type="text" value="<?php echo esc_attr( $options['title'] ); ?>"></td>
					</tr>
					<tr>
						<th scope="row"><label for="recipe-warning-message"><?php esc_html_e( 'Message', 'recipe-ai-summary-notice' ); ?></label></th>
						<td><textarea class="large-text" rows="7" id="recipe-warning-message" name="recipe_warning_options[message]" maxlength="4000"><?php echo esc_textarea( $options['message'] ); ?></textarea><p class="description"><?php esc_html_e( 'Plain text. Blank fields restore the default wording. Title: 180 characters. Message: 4,000 characters.', 'recipe-ai-summary-notice' ); ?></p></td>
					</tr>
				</table>
				<?php submit_button(); ?>
				</div>
			</form>
			<div class="rw-guidance">
			<p><?php esc_html_e( 'Recipe pages are detected through supported recipe shortcodes, recipe blocks, and Recipe structured data. Use the Recipe AI Summary Notice for Firefox checkbox in the post editor for other recipe formats.', 'recipe-ai-summary-notice' ); ?></p>
			<p><?php esc_html_e( 'Review the visitor wording for your site and test on staging before enabling it on a live site. Do not claim a recipe has been tested unless that is true.', 'recipe-ai-summary-notice' ); ?></p>
			</div>
			<?php elseif ( 'context' === $tab ) : ?>
			<section class="rw-admin-card rw-reading">
			<h2><?php esc_html_e( 'Why this plugin exists', 'recipe-ai-summary-notice' ); ?></h2>
			<p><?php esc_html_e( 'Firefox offers optional, user-controlled AI summaries on supported mobile devices. A summary can differ from its source. This plugin gives recipe publishers a way to explain that distinction with an optional dismissible mode.', 'recipe-ai-summary-notice' ); ?></p>
			<p><?php esc_html_e( 'In its October 1, 2026 article, Mozilla describes recipe-specific prompts and routing through recipe metadata to improve summary completeness. This does not establish that every summary is wrong, or that any particular summary is safe or complete.', 'recipe-ai-summary-notice' ); ?></p>
			<p><a href="<?php echo esc_url( 'https://blog.mozilla.org/en/firefox/firefox-ai/prompt-tuning-firefox-shake-to-summarize-recipes/' ); ?>"><?php esc_html_e( 'Read Mozilla’s recipe-summary implementation article', 'recipe-ai-summary-notice' ); ?></a></p>
			<p><a href="https://support.mozilla.org/en-US/kb/summarize-pages-android"><?php esc_html_e( 'Mozilla’s Android summary settings', 'recipe-ai-summary-notice' ); ?></a> | <a href="https://support.mozilla.org/en-US/kb/summarize-pages-ios"><?php esc_html_e( 'Mozilla’s iOS summary settings', 'recipe-ai-summary-notice' ); ?></a></p>
			<p><?php esc_html_e( 'Turning off the shake gesture alone may leave summaries available through other controls. The visitor confirmation is not a browser setting check.', 'recipe-ai-summary-notice' ); ?></p>
			<p><?php esc_html_e( 'Feature availability and browser behavior can change. Browser identification is approximate and cannot tell whether a visitor has enabled summaries.', 'recipe-ai-summary-notice' ); ?></p>
			</section>
			<?php else : ?>
			<section class="rw-admin-card rw-reading">
			<h2><?php esc_html_e( 'About', 'recipe-ai-summary-notice' ); ?></h2>
			<p><?php esc_html_e( 'Credit for the original idea goes to Don Marti, whose proposal prompted this plugin. This attribution is not an endorsement. Recipe AI Summary Notice for Firefox is independently developed and is not affiliated with or endorsed by Mozilla, Firefox, WordPress, or Don Marti. Product names identify the relevant products and remain the property of their respective owners.', 'recipe-ai-summary-notice' ); ?></p>
			<p><?php esc_html_e( 'The plugin adds a notice that is dismissible by default. Publishers can disable general dismissal; visitors can still copy the link or confirm that summaries are disabled. It leaves the original recipe content unchanged, does not block summarization, and cannot detect or change browser summary settings. The “I’ve disabled summaries” button records the visitor’s statement, not a verified browser setting.', 'recipe-ai-summary-notice' ); ?></p>
			<p><?php esc_html_e( 'Firefox and Mozilla are trademarks of the Mozilla Foundation in the United States and other countries.', 'recipe-ai-summary-notice' ); ?></p>
			</section>
			<section class="rw-admin-card rw-reading">
			<h2><?php esc_html_e( 'Privacy and storage', 'recipe-ai-summary-notice' ); ?></h2>
			<p><?php esc_html_e( 'Only confirming that summaries are disabled stores an expiry timestamp under recipe-warning-bypass-v1 in browser local storage. The warning is suppressed for seven days for this site origin in that browser. Expired entries are removed when the warning next checks storage; they may remain until then. Clear this site’s browser storage to remove the preference. Continuing to the recipe does not store a preference.', 'recipe-ai-summary-notice' ); ?></p>
			<p><?php esc_html_e( 'The plugin sets no cookies, performs no tracking, and makes no external network requests. Copying the link uses the browser clipboard when available. These statements describe this plugin, not other plugins, the site, or the browser. Review the suggested text in WordPress Privacy Policy Guide for your site policy.', 'recipe-ai-summary-notice' ); ?></p>
			</section>
			<section class="rw-admin-card rw-reading">
			<h2><?php esc_html_e( 'License and limitations', 'recipe-ai-summary-notice' ); ?></h2>
			<p><?php esc_html_e( 'Licensed under GPL version 2 or later. Provided without warranty to the fullest extent permitted by applicable law, including implied warranties of merchantability or fitness for a particular purpose. Nothing here excludes rights or liabilities that applicable law does not allow to be excluded.', 'recipe-ai-summary-notice' ); ?></p>
			<p><?php esc_html_e( 'The plugin does not guarantee the accuracy, completeness, ownership, or safety of any recipe or generated summary. Its notices are not food safety, medical, or legal advice. Publishers remain responsible for their content and for reviewing their visitor notices and privacy disclosures.', 'recipe-ai-summary-notice' ); ?></p>
			</section>
			<?php endif; ?>
			</div>
		</div>
		<?php
	}

	public static function add_meta_boxes() {
		foreach ( get_post_types( array( 'public' => true ) ) as $post_type ) {
			if ( 'attachment' !== $post_type ) {
				add_meta_box( 'recipe-warning', __( 'Recipe AI Summary Notice for Firefox', 'recipe-ai-summary-notice' ), array( __CLASS__, 'meta_box' ), $post_type, 'side' );
			}
		}
	}

	public static function meta_box( $post ) {
		wp_nonce_field( 'recipe_warning_meta', 'recipe_warning_nonce' );
		?>
		<p><label><input type="checkbox" name="recipe_warning_force" value="1" <?php checked( '1', get_post_meta( $post->ID, self::META, true ) ); ?>> <?php esc_html_e( 'Treat this as a recipe page', 'recipe-ai-summary-notice' ); ?></label></p>
		<p><?php esc_html_e( 'Use when the recipe is not detected automatically. The warning appears only for mobile Firefox visitors.', 'recipe-ai-summary-notice' ); ?></p>
		<?php
	}

	public static function save_meta( $post_id ) {
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}
		if ( ! isset( $_POST['recipe_warning_nonce'] ) || ! is_string( $_POST['recipe_warning_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['recipe_warning_nonce'] ) ), 'recipe_warning_meta' ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( isset( $_POST['recipe_warning_force'] ) && '1' === $_POST['recipe_warning_force'] ) {
			update_post_meta( $post_id, self::META, '1' );
		} else {
			delete_post_meta( $post_id, self::META );
		}
	}

	public static function recipe_hint( $post ) {
		if ( '1' === get_post_meta( $post->ID, self::META, true ) ) {
			return true;
		}
		// A syntactic check also works when a recipe plugin is temporarily inactive.
		if ( preg_match( '/\[(?:wprm-recipe|wp-recipe-maker|tasty-recipe|recipe-card)(?=[\s\]\/])/', $post->post_content ) ) {
			return true;
		}
		foreach ( array( 'wp-recipe-maker/recipe', 'wprm/recipe', 'wp-tasty/tasty-recipe', 'tasty-recipes/tasty-recipe', 'wpzoom-recipe-card/block-recipe-card' ) as $block ) {
			if ( has_block( $block, $post ) ) {
				return true;
			}
		}
		return false;
	}

	public static function enqueue() {
		$options = self::options();
		if ( empty( $options['enabled'] ) || ! is_singular() || is_feed() || is_embed() || post_password_required() ) {
			return;
		}
		$post = get_queried_object();
		if ( ! ( $post instanceof WP_Post ) ) {
			return;
		}
		$base = plugin_dir_url( __FILE__ );
		wp_enqueue_style( 'recipe-warning', $base . 'assets/recipe-warning.css', array(), '1.4.0' );
		wp_enqueue_script( 'recipe-warning', $base . 'assets/recipe-warning.js', array(), '1.4.0', true );
		$config = array(
			'enabled'    => true,
			'title'      => $options['title'],
			'message'    => self::visitor_message( $options ),
			'dismissible' => $options['dismissible'],
			'recipeHint' => self::recipe_hint( $post ),
			'storageKey' => 'recipe-warning-bypass-v1',
			'ttl'        => 604800000,
			'strings'    => array(
				'label'        => __( 'A note for Firefox readers', 'recipe-ai-summary-notice' ),
				'close' => __( 'Close notice', 'recipe-ai-summary-notice' ),
				'preferenceLabel' => __( 'Already changed your Firefox settings?', 'recipe-ai-summary-notice' ),
				'requiredNote' => __( 'To dismiss this notice, confirm that you have disabled summaries. This records your statement, not a verified browser setting.', 'recipe-ai-summary-notice' ),
				'copy'         => __( 'Copy link for another browser', 'recipe-ai-summary-notice' ),
				'bypass'       => __( 'I’ve disabled summaries', 'recipe-ai-summary-notice' ),
				'proceed'      => __( 'Continue to the original recipe', 'recipe-ai-summary-notice' ),
				'note'         => __( 'Your choice is remembered on this device for 7 days when you confirm summaries are disabled.', 'recipe-ai-summary-notice' ),
				'linkLabel'    => __( 'Recipe link to copy', 'recipe-ai-summary-notice' ),
				'copied'       => __( 'Link copied. Paste it into another browser.', 'recipe-ai-summary-notice' ),
				'copyFallback' => __( 'Copy the selected link, then paste it into another browser.', 'recipe-ai-summary-notice' ),
			),
		);
		wp_add_inline_script( 'recipe-warning', 'window.rwConfig = ' . wp_json_encode( $config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . ';', 'before' );
	}
}

Recipe_Warning::init();
