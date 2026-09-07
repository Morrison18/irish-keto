<?php
/**
 * Plugin Name: Irish Keto Core
 * Description: Custom post types, meta fields, structured data, and blocks for the Irish Keto site. All domain logic lives here so the theme can be swapped freely.
 * Version: 0.1.0
 * Requires PHP: 8.2
 * Text Domain: irish-keto-core
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'IRISH_KETO_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'IRISH_KETO_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once IRISH_KETO_CORE_DIR . 'includes/class-post-types.php';
require_once IRISH_KETO_CORE_DIR . 'includes/class-post-meta.php';
require_once IRISH_KETO_CORE_DIR . 'includes/class-recipe-schema.php';
require_once IRISH_KETO_CORE_DIR . 'includes/class-blocks.php';
require_once IRISH_KETO_CORE_DIR . 'includes/class-taxonomies.php';

Irish_Keto_Core\Post_Types::init();
Irish_Keto_Core\Post_Meta::init();
Irish_Keto_Core\Recipe_Schema::init();
Irish_Keto_Core\Blocks::init();
Irish_Keto_Core\Taxonomies::init();

register_activation_hook( __FILE__, [ 'Irish_Keto_Core\\Taxonomies', 'activate' ] );
