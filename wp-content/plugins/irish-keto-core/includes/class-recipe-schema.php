<?php
declare(strict_types=1);

namespace Irish_Keto_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hand-rolled Recipe JSON-LD. Deliberately not a plugin (WP Recipe Maker /
 * Tasty Recipes) so we control exactly what search engines see.
 */
class Recipe_Schema {

	public static function init(): void {
		add_action( 'wp_head', [ __CLASS__, 'output_json_ld' ] );
	}

	public static function output_json_ld(): void {
		if ( ! is_singular( 'recipe' ) ) {
			return;
		}

		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return;
		}

		$net_carbs    = get_post_meta( $post_id, 'net_carbs_g', true );
		$protein      = get_post_meta( $post_id, 'protein_g', true );
		$fat          = get_post_meta( $post_id, 'fat_g', true );
		$calories     = get_post_meta( $post_id, 'calories', true );
		$servings     = get_post_meta( $post_id, 'servings', true );
		$prep_minutes = get_post_meta( $post_id, 'prep_minutes', true );
		$cook_minutes = get_post_meta( $post_id, 'cook_minutes', true );
		$ingredients  = get_post_meta( $post_id, 'ingredients', true );

		$ingredient_lines = [];

		if ( is_array( $ingredients ) ) {
			foreach ( $ingredients as $ingredient ) {
				$line = trim(
					sprintf(
						'%s %s %s',
						$ingredient['quantity'] ?? '',
						$ingredient['unit'] ?? '',
						$ingredient['name'] ?? ''
					)
				);

				if ( '' !== $line ) {
					$ingredient_lines[] = $line;
				}
			}
		}

		$nutrition = self::remove_empty(
			[
				'@type'    => 'NutritionInformation',
				'calories' => $calories ? $calories . ' kcal' : null,
				// schema.org has no "net carbs" property. We deliberately map
				// carbohydrateContent to net carbs (not total) because that's
				// the number this audience searches for and EU labels already
				// report net-of-fibre — see CLAUDE.md content model notes.
				'carbohydrateContent' => $net_carbs ? $net_carbs . ' g' : null,
				'proteinContent'      => $protein ? $protein . ' g' : null,
				'fatContent'          => $fat ? $fat . ' g' : null,
			]
		);

		$schema = self::remove_empty(
			[
				'@context'         => 'https://schema.org',
				'@type'            => 'Recipe',
				'name'             => get_the_title( $post_id ),
				'description'      => wp_strip_all_tags( get_the_excerpt( $post_id ) ),
				'recipeYield'      => $servings ? $servings . ' servings' : null,
				'prepTime'         => $prep_minutes ? self::to_iso8601_duration( (int) $prep_minutes ) : null,
				'cookTime'         => $cook_minutes ? self::to_iso8601_duration( (int) $cook_minutes ) : null,
				'recipeIngredient' => $ingredient_lines,
				'nutrition'        => $nutrition ?: null,
			]
		);

		printf(
			'<script type="application/ld+json">%s</script>' . "\n",
			wp_json_encode( $schema )
		);
	}

	private static function to_iso8601_duration( int $minutes ): string {
		return sprintf( 'PT%dM', $minutes );
	}

	private static function remove_empty( array $data ): array {
		return array_filter(
			$data,
			static fn( $value ): bool => null !== $value && '' !== $value && [] !== $value
		);
	}
}
