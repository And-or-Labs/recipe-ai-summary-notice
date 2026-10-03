<?php
// Test content only. Existing fixture IDs are reused on subsequent runs.
global $fixtures;
$fixtures = get_option('recipe_warning_test_fixtures', array());
function rw_fixture($key, $title, $content, $extra = array()) {
    global $fixtures;
    $post = array_merge(array('post_title' => $title, 'post_content' => $content, 'post_status' => 'publish', 'post_type' => 'post'), $extra);
    if (!empty($fixtures[$key]) && get_post($fixtures[$key])) $post['ID'] = $fixtures[$key];
    $id = wp_insert_post($post, true);
    if (is_wp_error($id)) throw new Exception($id->get_error_message());
    $fixtures[$key] = $id;
    return $id;
}
$recipe = '<!-- wp:paragraph --><p>Recipe Warning test fixture. This page contains structured recipe data for browser testing.</p><!-- /wp:paragraph --><!-- wp:heading --><h2 class="wp-block-heading">Ingredients</h2><!-- /wp:heading --><!-- wp:list --><ul class="wp-block-list"><li>1 cup oats</li><li>2 cups water</li></ul><!-- /wp:list --><!-- wp:heading --><h2 class="wp-block-heading">Instructions</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Combine and simmer for 5 minutes.</p><!-- /wp:paragraph -->';
$schema = '<script type="application/ld+json">{"@context":"https://schema.org","@graph":[{"@type":["Recipe"],"name":"Test kitchen oats","recipeIngredient":["1 cup oats","2 cups water"],"recipeInstructions":"Combine and simmer for 5 minutes."}]}</script>';
// Trusted local fixture markup deliberately includes JSON-LD.
remove_filter('content_save_pre', 'wp_filter_post_kses');
rw_fixture('recipe', 'Test kitchen oats', $recipe . $schema);
rw_fixture('plain', 'A note from the test kitchen', '<!-- wp:paragraph --><p>This is an ordinary article with no recipe data. No warning should appear.</p><!-- /wp:paragraph -->');
$manual = rw_fixture('manual', 'Manually marked recipe', $recipe);
update_post_meta($manual, '_recipe_warning_enabled', '1');
rw_fixture('password', 'Protected recipe', $recipe . $schema, array('post_password' => 'fixture-only'));
if (class_exists('WPRM_Recipe_Manager')) {
    $rid = rw_fixture('wprm_recipe', 'WP Recipe Maker test oats', '', array('post_type' => 'wprm_recipe'));
    update_post_meta($rid, 'wprm_ingredients', array(array('name' => '', 'ingredients' => array(array('uid'=>0,'amount'=>'1','unit'=>'cup','name'=>'oats','notes'=>'')))));
    update_post_meta($rid, 'wprm_instructions', array(array('name'=>'','instructions'=>array(array('uid'=>0,'text'=>'Combine and simmer for 5 minutes.','image'=>0)))));
    update_post_meta($rid, 'wprm_servings', '1');
    rw_fixture('wprm', 'WP Recipe Maker integration', '[wprm-recipe id="' . $rid . '"]');
}
update_option('recipe_warning_test_fixtures', $fixtures);
update_option('blogdescription', 'Local plugin test kitchen');
echo wp_json_encode($fixtures) . "\n";
