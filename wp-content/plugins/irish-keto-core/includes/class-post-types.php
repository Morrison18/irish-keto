<?php
declare(strict_types=1);

namespace Irish_Keto_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Post_Types {

	public static function init(): void {
		add_action( 'init', [ __CLASS__, 'register_recipe' ] );
		add_action( 'init', [ __CLASS__, 'register_product' ] );
	}

	public static function register_recipe(): void {
		register_post_type(
			'recipe',
			[
				'labels'       => [
					'name'          => __( 'Recipes', 'irish-keto-core' ),
					'singular_name' => __( 'Recipe', 'irish-keto-core' ),
					'add_new_item'  => __( 'Add New Recipe', 'irish-keto-core' ),
					'edit_item'     => __( 'Edit Recipe', 'irish-keto-core' ),
					'all_items'     => __( 'Recipes', 'irish-keto-core' ),
				],
				'public'       => true,
				'show_in_rest' => true,
				'menu_icon'    => 'dashicons-carrot',
				'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
				'has_archive'  => true,
				'rewrite'      => [ 'slug' => 'recipes' ],
			]
		);
	}

	public static function register_product(): void {
		register_post_type(
			'product',
			[
				'labels'       => [
					'name'          => __( 'Products', 'irish-keto-core' ),
					'singular_name' => __( 'Product', 'irish-keto-core' ),
					'add_new_item'  => __( 'Add New Product', 'irish-keto-core' ),
					'edit_item'     => __( 'Edit Product', 'irish-keto-core' ),
					'all_items'     => __( 'Products', 'irish-keto-core' ),
				],
				'public'       => true,
				'show_in_rest' => true,
				'menu_icon'    => 'dashicons-cart',
				'supports'     => [ 'title', 'editor', 'thumbnail' ],
				'has_archive'  => true,
				'rewrite'      => [ 'slug' => 'products' ],
			]
		);
	}
}
