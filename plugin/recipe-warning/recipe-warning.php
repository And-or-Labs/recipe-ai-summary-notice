<?php
/**
 * Plugin Name: Recipe Warning
 * Description: Shows mobile Firefox visitors a recipe summary warning with a choice to continue to the original recipe.
 * Version: 1.0.0
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
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'save_post', array( __CLASS__, 'save_meta' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	public static function defaults() {
		return array(
			'enabled' => true,
			'title'   => 'Keep the original recipe',
			'message' => "Firefox can generate an AI summary of this recipe. That summary is not tested by this site.\n\nWe have tested our recipe, but whatever slop version Firefox generates, we have no way to test it. We don't want people making a terrible slop recipe with our name on it, and getting blamed for how it comes out.",
		);
	}

	public static function options() {
		$saved = get_option( self::OPTION, array() );
		return wp_parse_args( is_array( $saved ) ? $saved : array(), self::defaults() );
	}

	public static function admin_menu() {
		add_options_page( 'Recipe Warning', 'Recipe Warning', 'manage_options', 'recipe-warning', array( __CLASS__, 'settings_page' ) );
	}

	public static function register_settings() {
		register_setting( 'recipe_warning', self::OPTION, array(
			'type'              => 'array',
			'sanitize_callback' => array( __CLASS__, 'sanitize_options' ),
			'default'           => self::defaults(),
		) );
	}

	public static function sanitize_options( $input ) {
		$input = is_array( $input ) ? $input : array();
		$defaults = self::defaults();
		$title = isset( $input['title'] ) && is_string( $input['title'] ) ? sanitize_text_field( $input['title'] ) : '';
		$message = isset( $input['message'] ) && is_string( $input['message'] ) ? sanitize_textarea_field( $input['message'] ) : '';
		return array(
			'enabled' => ! empty( $input['enabled'] ),
			'title'   => '' !== trim( $title ) ? $title : $defaults['title'],
			'message' => '' !== trim( $message ) ? $message : $defaults['message'],
		);
	}

	public static function settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$options = self::options();
		?>
		<div class="wrap">
			<h1>Recipe Warning</h1>
			<p>Show a warning to mobile Firefox visitors on recipe pages. Visitors can continue to the original recipe. Only confirmation that summaries are disabled is remembered for seven days on this site.</p>
			<p>This warning does not prevent AI summaries or verify browser settings.</p>
			<form action="options.php" method="post">
				<?php settings_fields( 'recipe_warning' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">Warning</th>
						<td><label><input type="checkbox" name="recipe_warning_options[enabled]" value="1" <?php checked( ! empty( $options['enabled'] ) ); ?>> Enable on recipe pages</label></td>
					</tr>
					<tr>
						<th scope="row"><label for="recipe-warning-title">Title</label></th>
						<td><input class="regular-text" id="recipe-warning-title" name="recipe_warning_options[title]" type="text" value="<?php echo esc_attr( $options['title'] ); ?>"></td>
					</tr>
					<tr>
						<th scope="row"><label for="recipe-warning-message">Message</label></th>
						<td><textarea class="large-text" rows="7" id="recipe-warning-message" name="recipe_warning_options[message]"><?php echo esc_textarea( $options['message'] ); ?></textarea><p class="description">Plain text. Blank fields restore the default wording.</p></td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
			<p>Recipe pages are detected through supported recipe shortcodes, recipe blocks, and Recipe structured data. Use the Recipe Warning checkbox in the post editor for other recipe formats.</p>
			<p>Warning wording contributed by Don Marti.</p>
		</div>
		<?php
	}

	public static function add_meta_boxes() {
		foreach ( get_post_types( array( 'public' => true ) ) as $post_type ) {
			if ( 'attachment' !== $post_type ) {
				add_meta_box( 'recipe-warning', 'Recipe Warning', array( __CLASS__, 'meta_box' ), $post_type, 'side' );
			}
		}
	}

	public static function meta_box( $post ) {
		wp_nonce_field( 'recipe_warning_meta', 'recipe_warning_nonce' );
		?>
		<p><label><input type="checkbox" name="recipe_warning_force" value="1" <?php checked( '1', get_post_meta( $post->ID, self::META, true ) ); ?>> Treat this as a recipe page</label></p>
		<p>Use when the recipe is not detected automatically. The warning appears only for mobile Firefox visitors.</p>
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
		wp_enqueue_style( 'recipe-warning', $base . 'assets/recipe-warning.css', array(), '1.0.0' );
		wp_enqueue_script( 'recipe-warning', $base . 'assets/recipe-warning.js', array(), '1.0.0', true );
		wp_localize_script( 'recipe-warning', 'rwConfig', array(
			'enabled'    => true,
			'title'      => $options['title'],
			'message'    => $options['message'],
			'recipeHint' => self::recipe_hint( $post ),
			'storageKey' => 'recipe-warning-bypass-v1',
			'ttl'        => 604800000,
		) );
	}
}

Recipe_Warning::init();
