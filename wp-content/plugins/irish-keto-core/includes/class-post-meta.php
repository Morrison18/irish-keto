<?php
declare(strict_types=1);

namespace Irish_Keto_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Post_Meta {

	public static function init(): void {
		add_action( 'init', [ __CLASS__, 'register_recipe_meta' ] );
		add_action( 'init', [ __CLASS__, 'register_product_meta' ] );
	}

	public static function register_recipe_meta(): void {
		$number_fields = [
			'net_carbs_g'  => __( 'Net carbs (g)', 'irish-keto-core' ),
			'protein_g'    => __( 'Protein (g)', 'irish-keto-core' ),
			'fat_g'        => __( 'Fat (g)', 'irish-keto-core' ),
			'calories'     => __( 'Calories (kcal)', 'irish-keto-core' ),
			'servings'     => __( 'Servings', 'irish-keto-core' ),
			'prep_minutes' => __( 'Prep time (minutes)', 'irish-keto-core' ),
			'cook_minutes' => __( 'Cook time (minutes)', 'irish-keto-core' ),
		];

		foreach ( $number_fields as $meta_key => $label ) {
			register_post_meta(
				'recipe',
				$meta_key,
				[
					'type'              => 'number',
					'label'             => $label,
					'single'            => true,
					'show_in_rest'      => true,
					'default'           => 0,
					'sanitize_callback' => 'floatval',
					'auth_callback'     => static fn(): bool => current_user_can( 'edit_posts' ),
				]
			);
		}

		// Structured so each ingredient can optionally link to a `product` post
		// for cross-referencing price/carbs data, per the site's content model.
		register_post_meta(
			'recipe',
			'ingredients',
			[
				'type'          => 'array',
				'label'         => __( 'Ingredients', 'irish-keto-core' ),
				'single'        => true,
				'show_in_rest'  => [
					'schema' => [
						'type'  => 'array',
						'items' => [
							'type'       => 'object',
							'properties' => [
								'name'       => [ 'type' => 'string' ],
								'quantity'   => [ 'type' => 'string' ],
								'unit'       => [ 'type' => 'string' ],
								'product_id' => [ 'type' => 'integer' ],
							],
						],
					],
				],
				'default'       => [],
				'auth_callback' => static fn(): bool => current_user_can( 'edit_posts' ),
			]
		);
	}

	public static function register_product_meta(): void {
		register_post_meta(
			'product',
			'retailer',
			[
				'type'              => 'string',
				'label'             => __( 'Retailer', 'irish-keto-core' ),
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => static fn(): bool => current_user_can( 'edit_posts' ),
			]
		);

		register_post_meta(
			'product',
			'brand',
			[
				'type'              => 'string',
				'label'             => __( 'Brand', 'irish-keto-core' ),
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => static fn(): bool => current_user_can( 'edit_posts' ),
			]
		);

		register_post_meta(
			'product',
			'pack_size_g',
			[
				'type'              => 'number',
				'label'             => __( 'Pack size (g)', 'irish-keto-core' ),
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'floatval',
				'auth_callback'     => static fn(): bool => current_user_can( 'edit_posts' ),
			]
		);

		// EU labels report carbohydrate net of fibre already, unlike US labels.
		// This value is entered as-is from the pack and must not be adjusted.
		register_post_meta(
			'product',
			'carbs_per_100g',
			[
				'type'              => 'number',
				'label'             => __( 'Net carbs per 100g', 'irish-keto-core' ),
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'floatval',
				'auth_callback'     => static fn(): bool => current_user_can( 'edit_posts' ),
			]
		);

		register_post_meta(
			'product',
			'price_eur',
			[
				'type'              => 'number',
				'label'             => __( 'Price (EUR)', 'irish-keto-core' ),
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'floatval',
				'auth_callback'     => static fn(): bool => current_user_can( 'edit_posts' ),
			]
		);

		register_post_meta(
			'product',
			'last_verified',
			[
				'type'              => 'string',
				'label'             => __( 'Last verified (YYYY-MM-DD)', 'irish-keto-core' ),
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => static fn(): bool => current_user_can( 'edit_posts' ),
			]
		);
	}
}
