<?php
/** Run on a disposable WordPress site: wp eval-file recipe-warning-release-checks.php */
if ( ! defined( 'ABSPATH' ) || ! class_exists( 'Recipe_Warning' ) ) {
	throw new RuntimeException( 'Load WordPress with Recipe Warning active before running these checks.' );
}

$checks = 0;
$sentinel = new stdClass();
$original_options = get_option( Recipe_Warning::OPTION, $sentinel );
$sanitize_hook = 'sanitize_option_' . Recipe_Warning::OPTION;
$sanitize_callback = array( 'Recipe_Warning', 'sanitize_options' );
$sanitize_priority = has_filter( $sanitize_hook, $sanitize_callback );
$globals_before = array();
foreach ( array( 'wp_query', 'post', 'wp_scripts', 'wp_styles' ) as $key ) {
	$globals_before[ $key ] = array( array_key_exists( $key, $GLOBALS ), $GLOBALS[ $key ] ?? null );
}
$check = static function ( $condition, $message ) use ( &$checks ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
	++$checks;
};
// Write deliberately malformed database values without the normal settings sanitizer.
if ( false !== $sanitize_priority ) {
	remove_filter( $sanitize_hook, $sanitize_callback, $sanitize_priority );
}
$read_fixture = static function ( $value ) {
	update_option( Recipe_Warning::OPTION, $value );
	return Recipe_Warning::options();
};

try {
	$defaults = Recipe_Warning::defaults();
	foreach ( array( 'corrupt', 42, false, new stdClass() ) as $value ) {
		$check( $defaults === $read_fixture( $value ), 'Malformed stored options must merge safe defaults.' );
	}
	$check( $defaults === $read_fixture( array() ), 'Empty stored options must preserve default enabled state.' );
	$check( false === $read_fixture( array( 'enabled' => false ) )['enabled'], 'Explicit disabled state must survive defaults merging.' );
	$check( true === $read_fixture( array( 'title' => 'Custom heading' ) )['enabled'], 'Missing enabled field must retain enabled default on reads.' );
	foreach ( array( true, 1, '1' ) as $value ) {
		$check( true === $read_fixture( array( 'enabled' => $value ) )['enabled'], 'Supported enabled values must remain boolean true.' );
	}
	foreach ( array( false, 0, '0', 'false', array( 1 ), new stdClass() ) as $value ) {
		$check( false === $read_fixture( array( 'enabled' => $value ) )['enabled'], 'Malformed or disabled flags must not enable the warning.' );
	}
	$options = $read_fixture( array( 'enabled' => false, 'title' => array( 'bad' ), 'message' => new stdClass(), 'extra' => 'ignored' ) );
	$check( $defaults['title'] === $options['title'] && $defaults['message'] === $options['message'], 'Malformed stored text must use default copy.' );
	$check( array( 'enabled', 'title', 'message' ) === array_keys( $options ), 'Read schema must discard unexpected option keys.' );
	$original_stock = "Firefox can generate an AI summary of this recipe. That summary is not tested by this site.\n\nWe have tested our recipe, but whatever slop version Firefox generates, we have no way to test it. We don't want people making a terrible slop recipe with our name on it, and getting blamed for how it comes out.";
	$options = $read_fixture( array( 'enabled' => false, 'title' => 'Custom heading', 'message' => $original_stock ) );
	$check( $defaults['message'] === $options['message'], 'Exact original stock message must migrate to neutral copy.' );
	$check( false === $options['enabled'] && 'Custom heading' === $options['title'], 'Stock migration must preserve disabled state and custom title.' );
	$check( $original_stock === get_option( Recipe_Warning::OPTION )['message'], 'Read migration must leave stored original data unchanged.' );
	foreach ( array( str_replace( "\n", "\r\n", $original_stock ) . "\n", " \t" . str_replace( "\n", "\r", $original_stock ) . "\r " ) as $formatted_stock ) {
		$options = $read_fixture( array( 'enabled' => false, 'message' => $formatted_stock ) );
		$check( $defaults['message'] === $options['message'] && false === $options['enabled'], 'Stock copy with platform line endings and outer whitespace must migrate while preserving disabled state.' );
		$check( $formatted_stock === get_option( Recipe_Warning::OPTION )['message'], 'Formatted-stock read migration must preserve stored data.' );
	}
	$formatted_custom = str_replace( "\n", "\r\n", $original_stock ) . " Publisher addition.\n";
	$check( sanitize_textarea_field( $formatted_custom ) === $read_fixture( array( 'message' => $formatted_custom ) )['message'], 'Custom copy with platform line endings must remain custom.' );
	$custom = $original_stock . ' Publisher addition.';
	$check( $custom === $read_fixture( array( 'message' => $custom ) )['message'], 'Edited legacy copy must not migrate.' );
	$check( 'Independent custom notice.' === $read_fixture( array( 'message' => 'Independent custom notice.' ) )['message'], 'Custom notice must remain intact.' );

	foreach ( array( 'x', 'é', '🍲' ) as $character ) {
		$options = $read_fixture( array( 'title' => str_repeat( $character, 181 ), 'message' => str_repeat( $character, 4001 ) ) );
		$check( str_repeat( $character, 180 ) === $options['title'], 'Title limit must preserve Unicode code points.' );
		$check( str_repeat( $character, 4000 ) === $options['message'], 'Message limit must preserve Unicode code points.' );
		$check( false !== wp_json_encode( $options ), 'Truncated fields must remain valid UTF-8 JSON.' );
	}
	$options = $read_fixture( array( 'title' => '<script>alert(1)</script><b>Safe</b>', 'message' => "<img src=x onerror=alert(1)>Line one\nLine two" ) );
	$check( 'Safe' === $options['title'], 'Stored title XSS must be stripped on read.' );
	$check( "Line one\nLine two" === $options['message'], 'Stored message XSS must be stripped while preserving newlines.' );

	// Exercise the actual enqueue method with a singular recipe query and fresh registries.
	$GLOBALS['post'] = new WP_Post( (object) array( 'ID' => 0, 'post_content' => '[wprm-recipe id="1"]', 'post_password' => '', 'post_type' => 'post', 'post_status' => 'publish' ) );
	$GLOBALS['wp_query'] = new WP_Query();
	$GLOBALS['wp_query']->is_single = true;
	$GLOBALS['wp_query']->is_singular = true;
	$GLOBALS['wp_query']->queried_object = $GLOBALS['post'];
	$GLOBALS['wp_query']->queried_object_id = 0;
	$GLOBALS['wp_scripts'] = new WP_Scripts();
	$GLOBALS['wp_styles'] = new WP_Styles();
	$read_fixture( array( 'enabled' => true, 'title' => 'Quote " and ampersand &', 'message' => 'Plain message' ) );
	$check( true === Recipe_Warning::options()['enabled'], 'Enqueue fixture must enable the plugin.' );
	$check( is_singular() && ! is_feed() && ! is_embed(), 'Enqueue fixture must be a singular non-feed, non-embed query.' );
	$check( ! post_password_required(), 'Enqueue fixture must not require a password.' );
	$check( get_queried_object() instanceof WP_Post, 'Enqueue fixture must expose a WP_Post queried object.' );
	Recipe_Warning::enqueue();
	// Core may retain a false placeholder when casting absent script data to an array.
	$before = array_values( array_filter( (array) wp_scripts()->get_data( 'recipe-warning', 'before' ), 'is_string' ) );
	$check( is_array( $before ) && 1 === count( $before ), 'Enqueue must create one configuration block; actual: ' . wp_json_encode( $before ) );
	$check( 1 === preg_match( '/^window\.rwConfig = (.*);$/s', $before[0], $match ), 'Configuration must use the typed JSON assignment.' );
	$config = json_decode( $match[1], true, 512, JSON_THROW_ON_ERROR );
	$check( true === $config['enabled'] && true === $config['recipeHint'], 'Frontend enabled and recipe hint must be boolean true.' );
	$check( 604800000 === $config['ttl'], 'Frontend expiry must be a numeric seven-day duration.' );
	$check( 'recipe-warning-bypass-v1' === $config['storageKey'], 'Storage key must match the documented key.' );
	$check( 'Quote " and ampersand &' === $config['title'], 'JSON escaping must preserve configured text.' );
	$expected_strings = array(
		'label' => 'A note for Firefox readers',
		'copy' => 'Copy link for another browser',
		'bypass' => 'I’ve disabled summaries',
		'proceed' => 'Continue to the original recipe',
		'note' => 'Your choice is remembered on this device for 7 days when you confirm summaries are disabled.',
		'linkLabel' => 'Recipe link to copy',
		'copied' => 'Link copied. Paste it into another browser.',
		'copyFallback' => 'Copy the selected link, then paste it into another browser.',
	);
	foreach ( $expected_strings as $key => $value ) {
		$check( isset( $config['strings'][ $key ] ) && __( $value, 'recipe-warning' ) === $config['strings'][ $key ], 'Frontend dictionary must include translated label: ' . $key );
	}
	echo 'PASS: ' . $checks . " WordPress release checks.\n";
} finally {
	if ( $sentinel === $original_options ) {
		delete_option( Recipe_Warning::OPTION );
	} else {
		update_option( Recipe_Warning::OPTION, $original_options );
	}
	if ( false !== $sanitize_priority ) {
		add_filter( $sanitize_hook, $sanitize_callback, $sanitize_priority, 3 );
	}
	foreach ( $globals_before as $key => $previous ) {
		if ( $previous[0] ) {
			$GLOBALS[ $key ] = $previous[1];
		} else {
			unset( $GLOBALS[ $key ] );
		}
	}
}
