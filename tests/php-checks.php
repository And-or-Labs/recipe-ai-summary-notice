<?php
/** Run against a disposable WordPress installation: wp eval-file tests/php-checks.php */
if ( ! defined( 'ABSPATH' ) || ! class_exists( 'Recipe_Warning' ) ) {
	throw new RuntimeException( 'Load WordPress with Recipe Warning active before running these checks.' );
}
require_once ABSPATH . 'wp-admin/includes/user.php';

$checks = 0;
$post_ids = array();
$user_ids = array();
$original_user = get_current_user_id();
$original_post_data = $_POST;
$check = static function ( $condition, $message ) use ( &$checks ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
	++$checks;
};
$make_post = static function ( $content ) use ( &$post_ids ) {
	$id = wp_insert_post( array( 'post_title' => 'Recipe Warning regression fixture', 'post_content' => $content, 'post_status' => 'draft' ), true );
	if ( is_wp_error( $id ) ) {
		throw new RuntimeException( $id->get_error_message() );
	}
	$post_ids[] = $id;
	$post = get_post( $id );
	// Recipe plugins may rewrite shortcode content during save. Test hint parsing
	// against the supplied syntax while retaining a real ID for metadata checks.
	$post->post_content = $content;
	return $post;
};

try {
	$defaults = Recipe_Warning::defaults();
	$options = Recipe_Warning::sanitize_options( array( 'enabled' => '1', 'title' => '<b>My recipe</b>', 'message' => "<b>First line</b>\nSecond line" ) );
	$check( true === $options['enabled'], 'Enabled setting must persist.' );
	$check( 'My recipe' === $options['title'], 'Title must strip HTML.' );
	$check( "First line\nSecond line" === $options['message'], 'Message must strip HTML and retain line breaks.' );
	$options = Recipe_Warning::sanitize_options( array( 'title' => '', 'message' => '   ' ) );
	$check( false === $options['enabled'], 'Missing checkbox must disable the warning.' );
	$check( $defaults['title'] === $options['title'] && $defaults['message'] === $options['message'], 'Blank copy must restore defaults.' );
	$options = Recipe_Warning::sanitize_options( array( 'title' => array( 'bad' ), 'message' => array( 'bad' ) ) );
	$check( $defaults['title'] === $options['title'] && $defaults['message'] === $options['message'], 'Malformed copy must restore defaults without a type error.' );
	$check( false === Recipe_Warning::sanitize_options( 'malformed' )['enabled'], 'Malformed option must be handled safely.' );

	foreach ( array( 'wprm-recipe', 'wp-recipe-maker', 'tasty-recipe', 'recipe-card' ) as $shortcode ) {
		$check( Recipe_Warning::recipe_hint( $make_post( '[' . $shortcode . ' id="12"]' ) ), 'Missing shortcode hint: ' . $shortcode );
	}
	$check( ! Recipe_Warning::recipe_hint( $make_post( '[recipe-cardigan]' ) ), 'Shortcode prefix must not be mistaken for a recipe.' );
	foreach ( array( 'wp-recipe-maker/recipe', 'wprm/recipe', 'wp-tasty/tasty-recipe', 'tasty-recipes/tasty-recipe', 'wpzoom-recipe-card/block-recipe-card' ) as $block ) {
		$check( Recipe_Warning::recipe_hint( $make_post( '<!-- wp:' . $block . ' /-->' ) ), 'Missing block hint: ' . $block );
	}
	$post = $make_post( 'An ordinary article.' );
	$check( ! Recipe_Warning::recipe_hint( $post ), 'Ordinary posts must have no server recipe hint.' );
	update_post_meta( $post->ID, Recipe_Warning::META, '1' );
	$check( Recipe_Warning::recipe_hint( $post ), 'Manual override must identify recipe content.' );
	delete_post_meta( $post->ID, Recipe_Warning::META );

	foreach ( array( 'administrator', 'subscriber' ) as $role ) {
		$id = wp_insert_user( array( 'user_login' => 'rw-test-' . $role . '-' . wp_generate_password( 10, false ), 'user_pass' => wp_generate_password( 30 ), 'role' => $role ) );
		if ( is_wp_error( $id ) ) {
			throw new RuntimeException( $id->get_error_message() );
		}
		$user_ids[ $role ] = $id;
	}
	wp_set_current_user( $user_ids['administrator'] );
	$_POST = array( 'recipe_warning_force' => '1' );
	Recipe_Warning::save_meta( $post->ID );
	$check( '' === get_post_meta( $post->ID, Recipe_Warning::META, true ), 'Missing nonce must reject writes.' );
	$_POST['recipe_warning_nonce'] = 'invalid';
	Recipe_Warning::save_meta( $post->ID );
	$check( '' === get_post_meta( $post->ID, Recipe_Warning::META, true ), 'Invalid nonce must reject writes.' );
	$_POST['recipe_warning_nonce'] = array( 'invalid' );
	Recipe_Warning::save_meta( $post->ID );
	$check( '' === get_post_meta( $post->ID, Recipe_Warning::META, true ), 'Malformed nonce must reject writes safely.' );
	wp_set_current_user( $user_ids['subscriber'] );
	$_POST['recipe_warning_nonce'] = wp_create_nonce( 'recipe_warning_meta' );
	Recipe_Warning::save_meta( $post->ID );
	$check( '' === get_post_meta( $post->ID, Recipe_Warning::META, true ), 'Valid nonce without edit capability must reject writes.' );
	wp_set_current_user( $user_ids['administrator'] );
	$_POST['recipe_warning_nonce'] = wp_create_nonce( 'recipe_warning_meta' );
	Recipe_Warning::save_meta( $post->ID );
	$check( '1' === get_post_meta( $post->ID, Recipe_Warning::META, true ), 'Authorized checked override must save.' );
	$_POST = array();
	Recipe_Warning::save_meta( $post->ID );
	$check( '1' === get_post_meta( $post->ID, Recipe_Warning::META, true ), 'Unrelated saves must preserve existing override.' );
	$_POST = array( 'recipe_warning_nonce' => wp_create_nonce( 'recipe_warning_meta' ) );
	Recipe_Warning::save_meta( $post->ID );
	$check( '' === get_post_meta( $post->ID, Recipe_Warning::META, true ), 'Authorized unchecked override must clear.' );

	echo 'PASS: ' . $checks . " WordPress runtime checks.\n";
} finally {
	$_POST = $original_post_data;
	wp_set_current_user( $original_user );
	foreach ( $post_ids as $id ) {
		wp_delete_post( $id, true );
	}
	foreach ( $user_ids as $id ) {
		wp_delete_user( $id );
	}
}
