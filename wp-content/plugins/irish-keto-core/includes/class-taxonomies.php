<?php
declare(strict_types=1);

namespace Irish_Keto_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Taxonomies {

	private const DEFAULT_TERMS = [ 'Keto', 'Carnivore', 'Low Carb', 'High Fat High Protein' ];

	public static function init(): void {
		add_action( 'init', [ __CLASS__, 'register_diet_type' ] );
	}

	public static function activate(): void {
		self::register_diet_type();
		self::ensure_default_terms();
		flush_rewrite_rules();
	}

	public static function register_diet_type(): void {
		register_taxonomy(
			'diet_type',
			[ 'post', 'recipe' ],
			[
				'labels'       => [
					'name'          => __( 'Diet Types', 'irish-keto-core' ),
					'singular_name' => __( 'Diet Type', 'irish-keto-core' ),
					'menu_name'     => __( 'Diet Types', 'irish-keto-core' ),
				],
				'hierarchical' => true,
				'public'       => true,
				'show_in_rest' => true,
				'rewrite'      => [ 'slug' => 'diet' ],
			]
		);
	}

	private static function ensure_default_terms(): void {
		foreach ( self::DEFAULT_TERMS as $term ) {
			if ( ! term_exists( $term, 'diet_type' ) ) {
				wp_insert_term( $term, 'diet_type' );
			}
		}
	}
}
