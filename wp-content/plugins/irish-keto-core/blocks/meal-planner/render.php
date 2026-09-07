<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Irish_Keto_Core\Bmr_Calculator;

$submitted = isset( $_GET['irish_keto_planner'] );

$values = [
	'sex'       => isset( $_GET['sex'] ) && 'female' === $_GET['sex'] ? 'female' : 'male',
	'age'       => isset( $_GET['age'] ) ? sanitize_text_field( wp_unslash( $_GET['age'] ) ) : '',
	'height_cm' => isset( $_GET['height_cm'] ) ? sanitize_text_field( wp_unslash( $_GET['height_cm'] ) ) : '',
	'weight_kg' => isset( $_GET['weight_kg'] ) ? sanitize_text_field( wp_unslash( $_GET['weight_kg'] ) ) : '',
	'activity'  => isset( $_GET['activity'] ) ? sanitize_text_field( wp_unslash( $_GET['activity'] ) ) : '',
	'diet'      => isset( $_GET['diet'] ) ? sanitize_text_field( wp_unslash( $_GET['diet'] ) ) : '',
	'goal'      => isset( $_GET['goal'] ) ? sanitize_text_field( wp_unslash( $_GET['goal'] ) ) : '',
	'likes'     => isset( $_GET['likes'] ) ? sanitize_text_field( wp_unslash( $_GET['likes'] ) ) : '',
	'dislikes'  => isset( $_GET['dislikes'] ) ? sanitize_text_field( wp_unslash( $_GET['dislikes'] ) ) : '',
];

$gp_consent = $submitted && isset( $_GET['gp_consent'] ) && '1' === $_GET['gp_consent'];

$results        = null;
$missing_consent = false;
$invalid_inputs  = false;
$matching_recipes = [];

if ( $submitted ) {
	if ( ! $gp_consent ) {
		$missing_consent = true;
	} else {
		$results = Bmr_Calculator::calculate(
			[
				'sex'       => $values['sex'],
				'age'       => $values['age'],
				'height_cm' => $values['height_cm'],
				'weight_kg' => $values['weight_kg'],
				'activity'  => $values['activity'],
				'diet'      => $values['diet'],
				'goal'      => $values['goal'],
			]
		);

		if ( null === $results ) {
			$invalid_inputs = true;
		} else {
			$dislikes = array_filter( array_map( 'trim', explode( ',', strtolower( $values['dislikes'] ) ) ) );

			$query = new WP_Query(
				[
					'post_type'      => 'recipe',
					'posts_per_page' => 6,
					'tax_query'      => [
						[
							'taxonomy' => 'diet_type',
							'field'    => 'slug',
							'terms'    => $values['diet'],
						],
					],
				]
			);

			foreach ( $query->posts as $recipe ) {
				$haystack = strtolower( $recipe->post_title . ' ' . $recipe->post_excerpt );
				$excluded = false;

				foreach ( $dislikes as $dislike ) {
					if ( '' !== $dislike && str_contains( $haystack, $dislike ) ) {
						$excluded = true;
						break;
					}
				}

				if ( ! $excluded ) {
					$matching_recipes[] = $recipe;
				}
			}

			wp_reset_postdata();
		}
	}
}

$diet_options     = Bmr_Calculator::diet_options();
$activity_options = Bmr_Calculator::activity_options();
$goal_options     = Bmr_Calculator::goal_options();
?>
<div <?php echo get_block_wrapper_attributes( [ 'class' => 'irish-keto-meal-planner' ] ); ?>>

	<form method="get" class="irish-keto-meal-planner__form">
		<input type="hidden" name="irish_keto_planner" value="1" />

		<fieldset>
			<legend><?php esc_html_e( 'About you', 'irish-keto-core' ); ?></legend>

			<div class="irish-keto-meal-planner__row">
				<label>
					<input type="radio" name="sex" value="male" <?php checked( $values['sex'], 'male' ); ?> />
					<?php esc_html_e( 'Male', 'irish-keto-core' ); ?>
				</label>
				<label>
					<input type="radio" name="sex" value="female" <?php checked( $values['sex'], 'female' ); ?> />
					<?php esc_html_e( 'Female', 'irish-keto-core' ); ?>
				</label>
			</div>

			<div class="irish-keto-meal-planner__row">
				<label for="ikmp-age"><?php esc_html_e( 'Age', 'irish-keto-core' ); ?></label>
				<input type="number" id="ikmp-age" name="age" min="16" max="100" value="<?php echo esc_attr( $values['age'] ); ?>" required />

				<label for="ikmp-height"><?php esc_html_e( 'Height (cm)', 'irish-keto-core' ); ?></label>
				<input type="number" id="ikmp-height" name="height_cm" min="120" max="230" value="<?php echo esc_attr( $values['height_cm'] ); ?>" required />

				<label for="ikmp-weight"><?php esc_html_e( 'Weight (kg)', 'irish-keto-core' ); ?></label>
				<input type="number" id="ikmp-weight" name="weight_kg" min="35" max="250" value="<?php echo esc_attr( $values['weight_kg'] ); ?>" required />
			</div>

			<div class="irish-keto-meal-planner__row">
				<label for="ikmp-activity"><?php esc_html_e( 'Activity level', 'irish-keto-core' ); ?></label>
				<select id="ikmp-activity" name="activity" required>
					<option value=""><?php esc_html_e( 'Choose one', 'irish-keto-core' ); ?></option>
					<?php foreach ( $activity_options as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $values['activity'], $key ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</fieldset>

		<fieldset>
			<legend><?php esc_html_e( 'Your plan', 'irish-keto-core' ); ?></legend>

			<div class="irish-keto-meal-planner__row">
				<label for="ikmp-diet"><?php esc_html_e( 'Diet', 'irish-keto-core' ); ?></label>
				<select id="ikmp-diet" name="diet" required>
					<option value=""><?php esc_html_e( 'Choose one', 'irish-keto-core' ); ?></option>
					<?php foreach ( $diet_options as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $values['diet'], $key ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>

				<label for="ikmp-goal"><?php esc_html_e( 'Goal', 'irish-keto-core' ); ?></label>
				<select id="ikmp-goal" name="goal" required>
					<option value=""><?php esc_html_e( 'Choose one', 'irish-keto-core' ); ?></option>
					<?php foreach ( $goal_options as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $values['goal'], $key ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="irish-keto-meal-planner__row">
				<label for="ikmp-likes"><?php esc_html_e( 'Foods you enjoy (comma separated)', 'irish-keto-core' ); ?></label>
				<input type="text" id="ikmp-likes" name="likes" value="<?php echo esc_attr( $values['likes'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. eggs, salmon, black pudding', 'irish-keto-core' ); ?>" />

				<label for="ikmp-dislikes"><?php esc_html_e( 'Foods to avoid (comma separated)', 'irish-keto-core' ); ?></label>
				<input type="text" id="ikmp-dislikes" name="dislikes" value="<?php echo esc_attr( $values['dislikes'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. mushrooms, liver', 'irish-keto-core' ); ?>" />
			</div>
		</fieldset>

		<div class="irish-keto-meal-planner__gp-gate">
			<label>
				<input type="checkbox" name="gp_consent" value="1" <?php checked( $gp_consent ); ?> required />
				<?php esc_html_e( "I understand this is general information, not medical advice, and I will consult my GP before changing my diet.", 'irish-keto-core' ); ?>
			</label>
			<?php if ( $missing_consent ) : ?>
				<p class="irish-keto-meal-planner__error"><?php esc_html_e( 'Please tick the box above before we can show you a plan.', 'irish-keto-core' ); ?></p>
			<?php endif; ?>
		</div>

		<button type="submit"><?php esc_html_e( 'Calculate my plan', 'irish-keto-core' ); ?></button>
	</form>

	<div class="irish-keto-meal-planner__results" data-irish-keto-planner-results <?php echo ( null === $results ) ? 'hidden' : ''; ?>>
		<?php if ( $invalid_inputs ) : ?>
			<p class="irish-keto-meal-planner__error"><?php esc_html_e( 'Please fill in every field with a realistic value.', 'irish-keto-core' ); ?></p>
		<?php elseif ( $results ) : ?>
			<h3><?php esc_html_e( 'Your estimated numbers', 'irish-keto-core' ); ?></h3>
			<ul class="irish-keto-meal-planner__stats">
				<li><strong><?php echo esc_html( (string) $results['bmr'] ); ?></strong> <?php esc_html_e( 'kcal — resting (BMR)', 'irish-keto-core' ); ?></li>
				<li><strong><?php echo esc_html( (string) $results['tdee'] ); ?></strong> <?php esc_html_e( 'kcal — maintenance (TDEE)', 'irish-keto-core' ); ?></li>
				<li><strong><?php echo esc_html( (string) $results['target_calories'] ); ?></strong> <?php esc_html_e( 'kcal — daily target for your goal', 'irish-keto-core' ); ?></li>
			</ul>
			<h4><?php esc_html_e( 'Suggested daily macros', 'irish-keto-core' ); ?></h4>
			<ul class="irish-keto-meal-planner__macros">
				<li><?php esc_html_e( 'Net carbs', 'irish-keto-core' ); ?>: <strong><?php echo esc_html( (string) $results['macros']['carbs_g'] ); ?> g</strong></li>
				<li><?php esc_html_e( 'Protein', 'irish-keto-core' ); ?>: <strong><?php echo esc_html( (string) $results['macros']['protein_g'] ); ?> g</strong></li>
				<li><?php esc_html_e( 'Fat', 'irish-keto-core' ); ?>: <strong><?php echo esc_html( (string) $results['macros']['fat_g'] ); ?> g</strong></li>
			</ul>

			<?php if ( ! empty( $matching_recipes ) ) : ?>
				<h4><?php esc_html_e( 'Recipes to get you started', 'irish-keto-core' ); ?></h4>
				<ul class="irish-keto-meal-planner__recipes">
					<?php foreach ( $matching_recipes as $recipe ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $recipe ) ); ?>"><?php echo esc_html( $recipe->post_title ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php elseif ( $values['diet'] ) : ?>
				<p><?php esc_html_e( "We don't have a matching recipe published yet — check back soon.", 'irish-keto-core' ); ?></p>
			<?php endif; ?>

			<p class="irish-keto-meal-planner__note">
				<?php esc_html_e( 'These are general estimates for educational purposes, not medical advice, and nothing you enter here is saved or sent anywhere — it stays in your browser.', 'irish-keto-core' ); ?>
			</p>
		<?php endif; ?>
	</div>
</div>
