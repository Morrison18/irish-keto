<?php
declare(strict_types=1);

namespace Irish_Keto_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mifflin-St Jeor BMR + activity-adjusted TDEE, with per-diet macro splits.
 * General educational estimates only — never framed as medical advice, and
 * gated behind the GP-consult acknowledgement at the call site.
 */
class Bmr_Calculator {

	private const ACTIVITY_MULTIPLIERS = [
		'sedentary' => 1.2,
		'light'     => 1.375,
		'moderate'  => 1.55,
		'active'    => 1.725,
		'very_active' => 1.9,
	];

	private const GOAL_ADJUSTMENTS = [
		'lose'     => 0.8,
		'maintain' => 1.0,
		'build'    => 1.1,
	];

	// [ carb %, protein %, fat % ] of total daily calories.
	private const DIET_MACRO_SPLITS = [
		'keto'                   => [ 0.05, 0.20, 0.75 ],
		'carnivore'              => [ 0.01, 0.35, 0.64 ],
		'low-carb'               => [ 0.20, 0.30, 0.50 ],
		'high-fat-high-protein'  => [ 0.05, 0.40, 0.55 ],
	];

	/**
	 * @param array{sex:string,age:float,height_cm:float,weight_kg:float,activity:string,diet:string,goal:string} $input
	 * @return array{bmr:float,tdee:float,target_calories:float,macros:array{carbs_g:float,protein_g:float,fat_g:float}}|null
	 */
	public static function calculate( array $input ): ?array {
		$sex       = $input['sex'] ?? '';
		$age       = (float) ( $input['age'] ?? 0 );
		$height    = (float) ( $input['height_cm'] ?? 0 );
		$weight    = (float) ( $input['weight_kg'] ?? 0 );
		$activity  = $input['activity'] ?? '';
		$diet      = $input['diet'] ?? '';
		$goal      = $input['goal'] ?? '';

		if ( $age <= 0 || $height <= 0 || $weight <= 0 ) {
			return null;
		}

		if ( ! isset( self::ACTIVITY_MULTIPLIERS[ $activity ] ) ) {
			return null;
		}

		if ( ! isset( self::GOAL_ADJUSTMENTS[ $goal ] ) ) {
			return null;
		}

		if ( ! isset( self::DIET_MACRO_SPLITS[ $diet ] ) ) {
			return null;
		}

		$sex_offset = ( 'female' === $sex ) ? -161 : 5;
		$bmr        = ( 10 * $weight ) + ( 6.25 * $height ) - ( 5 * $age ) + $sex_offset;
		$tdee       = $bmr * self::ACTIVITY_MULTIPLIERS[ $activity ];
		$target     = $tdee * self::GOAL_ADJUSTMENTS[ $goal ];

		[ $carb_pct, $protein_pct, $fat_pct ] = self::DIET_MACRO_SPLITS[ $diet ];

		return [
			'bmr'             => round( $bmr ),
			'tdee'            => round( $tdee ),
			'target_calories' => round( $target ),
			'macros'          => [
				'carbs_g'   => round( ( $target * $carb_pct ) / 4 ),
				'protein_g' => round( ( $target * $protein_pct ) / 4 ),
				'fat_g'     => round( ( $target * $fat_pct ) / 9 ),
			],
		];
	}

	public static function activity_options(): array {
		return [
			'sedentary'    => __( 'Sedentary (little or no exercise)', 'irish-keto-core' ),
			'light'        => __( 'Light exercise 1–3 days/week', 'irish-keto-core' ),
			'moderate'     => __( 'Moderate exercise 3–5 days/week', 'irish-keto-core' ),
			'active'       => __( 'Active exercise 6–7 days/week', 'irish-keto-core' ),
			'very_active'  => __( 'Very active (physical job or 2x training)', 'irish-keto-core' ),
		];
	}

	public static function goal_options(): array {
		return [
			'lose'     => __( 'Lose weight', 'irish-keto-core' ),
			'maintain' => __( 'Maintain weight', 'irish-keto-core' ),
			'build'    => __( 'Build muscle', 'irish-keto-core' ),
		];
	}

	public static function diet_options(): array {
		return [
			'keto'                  => __( 'Keto', 'irish-keto-core' ),
			'carnivore'             => __( 'Carnivore', 'irish-keto-core' ),
			'low-carb'              => __( 'Low Carb', 'irish-keto-core' ),
			'high-fat-high-protein' => __( 'High Fat High Protein', 'irish-keto-core' ),
		];
	}
}
