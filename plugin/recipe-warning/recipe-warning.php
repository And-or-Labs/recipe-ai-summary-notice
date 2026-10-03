<?php
/**
 * Plugin Name: Recipe Warning
 * Description: Shows mobile Firefox visitors a recipe summary warning with a choice to continue to the original recipe.
 * Version: 1.1.0
 * Author: And/or Labs Inc.
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * License: GPL-2.0-or-later
 * Text Domain: recipe-warning
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
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( __CLASS__, 'action_links' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'save_post', array( __CLASS__, 'save_meta' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	public static function defaults() {
		return array(
			'enabled' => true,
			'title'   => __( 'Keep the original recipe', 'recipe-warning' ),
			'message' => __( "Firefox offers optional AI summaries on some mobile devices. A generated summary can differ from the original recipe.\n\nYou can continue to the original recipe or copy this link to use another browser.", 'recipe-warning' ),
		);
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
		add_options_page( __( 'Recipe Warning', 'recipe-warning' ), __( 'Recipe Warning', 'recipe-warning' ), 'manage_options', 'recipe-warning', array( __CLASS__, 'settings_page' ) );
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
			'title'   => '' !== trim( $title ) ? self::limit_text( $title, 180 ) : $defaults['title'],
			'message' => '' !== trim( $message ) ? self::limit_text( $message, 4000 ) : $defaults['message'],
		);
	}

	public static function action_links( $links ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'options-general.php?page=recipe-warning' ) ) . '">' . esc_html__( 'Settings', 'recipe-warning' ) . '</a>' );
		return $links;
	}

	public static function privacy_policy() {
		if ( function_exists( 'wp_add_privacy_policy_content' ) ) {
			wp_add_privacy_policy_content( __( 'Recipe Warning', 'recipe-warning' ), '<p>' . esc_html__( 'Suggested text: Recipe Warning stores an expiry timestamp in your browser local storage under recipe-warning-bypass-v1 only when you confirm that summaries are disabled. This suppresses the warning for seven days for this site origin in that browser. Expired entries are removed when the warning next checks storage; they may remain until then. You can remove it by clearing this site’s browser storage. The plugin does not set cookies, track visitors, or send visitor data to external services.', 'recipe-warning' ) . '</p>' );
		}
	}

	public static function settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$options = self::options();
		// This parameter only selects a read-only settings panel.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tab = isset( $_GET['tab'] ) && is_string( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'settings';
		$tabs = array( 'settings' => __( 'Settings', 'recipe-warning' ), 'context' => __( 'Context', 'recipe-warning' ), 'about' => __( 'About & privacy', 'recipe-warning' ) );
		if ( ! isset( $tabs[ $tab ] ) ) {
			$tab = 'settings';
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Recipe Warning', 'recipe-warning' ); ?></h1>
			<nav class="nav-tab-wrapper" aria-label="<?php esc_attr_e( 'Recipe Warning settings', 'recipe-warning' ); ?>">
				<?php foreach ( $tabs as $key => $label ) : ?>
					<a class="nav-tab <?php echo $key === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( array( 'page' => 'recipe-warning', 'tab' => $key ), admin_url( 'options-general.php' ) ) ); ?>" <?php if ( $key === $tab ) { echo 'aria-current="page"'; } ?>><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
			</nav>
			<?php if ( 'settings' === $tab ) : ?>
			<p><?php esc_html_e( 'Show a warning to mobile Firefox visitors on recipe pages. Visitors can continue to the original recipe. Only confirmation that summaries are disabled is remembered for seven days on this site.', 'recipe-warning' ); ?></p>
			<p><?php esc_html_e( 'This warning does not prevent AI summaries or verify browser settings.', 'recipe-warning' ); ?></p>
			<form action="options.php" method="post">
				<?php settings_fields( 'recipe_warning' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Warning', 'recipe-warning' ); ?></th>
						<td><label><input type="checkbox" name="recipe_warning_options[enabled]" value="1" <?php checked( ! empty( $options['enabled'] ) ); ?>> <?php esc_html_e( 'Enable on recipe pages', 'recipe-warning' ); ?></label></td>
					</tr>
					<tr>
						<th scope="row"><label for="recipe-warning-title"><?php esc_html_e( 'Title', 'recipe-warning' ); ?></label></th>
						<td><input class="regular-text" id="recipe-warning-title" name="recipe_warning_options[title]" maxlength="180" type="text" value="<?php echo esc_attr( $options['title'] ); ?>"></td>
					</tr>
					<tr>
						<th scope="row"><label for="recipe-warning-message"><?php esc_html_e( 'Message', 'recipe-warning' ); ?></label></th>
						<td><textarea class="large-text" rows="7" id="recipe-warning-message" name="recipe_warning_options[message]" maxlength="4000"><?php echo esc_textarea( $options['message'] ); ?></textarea><p class="description"><?php esc_html_e( 'Plain text. Blank fields restore the default wording. Title: 180 characters. Message: 4,000 characters.', 'recipe-warning' ); ?></p></td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
			<p><?php esc_html_e( 'Recipe pages are detected through supported recipe shortcodes, recipe blocks, and Recipe structured data. Use the Recipe Warning checkbox in the post editor for other recipe formats.', 'recipe-warning' ); ?></p>
			<p><?php esc_html_e( 'Review the visitor wording for your site and test on staging before enabling it on a live site. Do not claim a recipe has been tested unless that is true.', 'recipe-warning' ); ?></p>
			<?php elseif ( 'context' === $tab ) : ?>
			<h2><?php esc_html_e( 'Why this plugin exists', 'recipe-warning' ); ?></h2>
			<p><?php esc_html_e( 'Firefox offers optional, user-controlled AI summaries on supported mobile devices. A summary can differ from its source. This plugin gives recipe publishers a way to explain that distinction while allowing visitors to continue to the original recipe.', 'recipe-warning' ); ?></p>
			<p><?php esc_html_e( 'In its October 1, 2026 article, Mozilla describes recipe-specific prompts and routing through recipe metadata to improve summary completeness. This does not establish that every summary is wrong, or that any particular summary is safe or complete.', 'recipe-warning' ); ?></p>
			<p><a href="<?php echo esc_url( 'https://blog.mozilla.org/en/firefox/firefox-ai/prompt-tuning-firefox-shake-to-summarize-recipes/' ); ?>"><?php esc_html_e( 'Read Mozilla’s recipe-summary implementation article', 'recipe-warning' ); ?></a></p>
			<p><a href="https://support.mozilla.org/en-US/kb/summarize-pages-android"><?php esc_html_e( 'Mozilla’s Android summary settings', 'recipe-warning' ); ?></a> | <a href="https://support.mozilla.org/en-US/kb/summarize-pages-ios"><?php esc_html_e( 'Mozilla’s iOS summary settings', 'recipe-warning' ); ?></a></p>
			<p><?php esc_html_e( 'Turning off the shake gesture alone may leave summaries available through other controls. The visitor confirmation is not a browser setting check.', 'recipe-warning' ); ?></p>
			<p><?php esc_html_e( 'Feature availability and browser behavior can change. Browser identification is approximate and cannot tell whether a visitor has enabled summaries.', 'recipe-warning' ); ?></p>
			<?php else : ?>
			<h2><?php esc_html_e( 'About', 'recipe-warning' ); ?></h2>
			<p><?php esc_html_e( 'Inspired by a discussion with Don Marti about supporting recipe creators. This attribution is not an endorsement. Recipe Warning is independently developed and is not affiliated with or endorsed by Mozilla, Firefox, WordPress, or Don Marti. Product names identify the relevant products and remain the property of their respective owners.', 'recipe-warning' ); ?></p>
			<p><?php esc_html_e( 'The plugin adds a dismissible notice. It leaves the original recipe content unchanged, does not block summarization, and cannot detect or change browser summary settings. The “I’ve disabled summaries” button records the visitor’s statement, not a verified browser setting.', 'recipe-warning' ); ?></p>
			<p><?php esc_html_e( 'Firefox and Mozilla are trademarks of the Mozilla Foundation in the United States and other countries.', 'recipe-warning' ); ?></p>
			<h2><?php esc_html_e( 'Privacy and storage', 'recipe-warning' ); ?></h2>
			<p><?php esc_html_e( 'Only confirming that summaries are disabled stores an expiry timestamp under recipe-warning-bypass-v1 in browser local storage. The warning is suppressed for seven days for this site origin in that browser. Expired entries are removed when the warning next checks storage; they may remain until then. Clear this site’s browser storage to remove the preference. Continuing to the recipe does not store a preference.', 'recipe-warning' ); ?></p>
			<p><?php esc_html_e( 'The plugin sets no cookies, performs no tracking, and makes no external network requests. Copying the link uses the browser clipboard when available. These statements describe this plugin, not other plugins, the site, or the browser. Review the suggested text in WordPress Privacy Policy Guide for your site policy.', 'recipe-warning' ); ?></p>
			<h2><?php esc_html_e( 'License and limitations', 'recipe-warning' ); ?></h2>
			<p><?php esc_html_e( 'Licensed under GPL version 2 or later. Provided without warranty to the fullest extent permitted by applicable law, including implied warranties of merchantability or fitness for a particular purpose. Nothing here excludes rights or liabilities that applicable law does not allow to be excluded.', 'recipe-warning' ); ?></p>
			<p><?php esc_html_e( 'The plugin does not guarantee the accuracy, completeness, ownership, or safety of any recipe or generated summary. Its notices are not food safety, medical, or legal advice. Publishers remain responsible for their content and for reviewing their visitor notices and privacy disclosures.', 'recipe-warning' ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	public static function add_meta_boxes() {
		foreach ( get_post_types( array( 'public' => true ) ) as $post_type ) {
			if ( 'attachment' !== $post_type ) {
				add_meta_box( 'recipe-warning', __( 'Recipe Warning', 'recipe-warning' ), array( __CLASS__, 'meta_box' ), $post_type, 'side' );
			}
		}
	}

	public static function meta_box( $post ) {
		wp_nonce_field( 'recipe_warning_meta', 'recipe_warning_nonce' );
		?>
		<p><label><input type="checkbox" name="recipe_warning_force" value="1" <?php checked( '1', get_post_meta( $post->ID, self::META, true ) ); ?>> <?php esc_html_e( 'Treat this as a recipe page', 'recipe-warning' ); ?></label></p>
		<p><?php esc_html_e( 'Use when the recipe is not detected automatically. The warning appears only for mobile Firefox visitors.', 'recipe-warning' ); ?></p>
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
		wp_enqueue_style( 'recipe-warning', $base . 'assets/recipe-warning.css', array(), '1.1.0' );
		wp_enqueue_script( 'recipe-warning', $base . 'assets/recipe-warning.js', array(), '1.1.0', true );
		$config = array(
			'enabled'    => true,
			'title'      => $options['title'],
			'message'    => $options['message'],
			'recipeHint' => self::recipe_hint( $post ),
			'storageKey' => 'recipe-warning-bypass-v1',
			'ttl'        => 604800000,
			'strings'    => array(
				'label'        => __( 'A note for Firefox readers', 'recipe-warning' ),
				'copy'         => __( 'Copy link for another browser', 'recipe-warning' ),
				'bypass'       => __( 'I’ve disabled summaries', 'recipe-warning' ),
				'proceed'      => __( 'Continue to the original recipe', 'recipe-warning' ),
				'note'         => __( 'Your choice is remembered on this device for 7 days when you confirm summaries are disabled.', 'recipe-warning' ),
				'linkLabel'    => __( 'Recipe link to copy', 'recipe-warning' ),
				'copied'       => __( 'Link copied. Paste it into another browser.', 'recipe-warning' ),
				'copyFallback' => __( 'Copy the selected link, then paste it into another browser.', 'recipe-warning' ),
			),
		);
		wp_add_inline_script( 'recipe-warning', 'window.rwConfig = ' . wp_json_encode( $config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . ';', 'before' );
	}
}

Recipe_Warning::init();
