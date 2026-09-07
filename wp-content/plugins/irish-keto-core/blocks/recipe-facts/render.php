<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id = $block->context['postId'] ?? get_the_ID();

if ( ! $post_id || 'recipe' !== get_post_type( $post_id ) ) {
	return;
}

$facts = [
	[ 'label' => __( 'Net carbs', 'irish-keto-core' ), 'value' => get_post_meta( $post_id, 'net_carbs_g', true ), 'unit' => 'g' ],
	[ 'label' => __( 'Protein', 'irish-keto-core' ), 'value' => get_post_meta( $post_id, 'protein_g', true ), 'unit' => 'g' ],
	[ 'label' => __( 'Fat', 'irish-keto-core' ), 'value' => get_post_meta( $post_id, 'fat_g', true ), 'unit' => 'g' ],
	[ 'label' => __( 'Calories', 'irish-keto-core' ), 'value' => get_post_meta( $post_id, 'calories', true ), 'unit' => 'kcal' ],
];
?>
<div <?php echo get_block_wrapper_attributes( [ 'class' => 'irish-keto-recipe-facts' ] ); ?>>
	<ul class="irish-keto-recipe-facts__list">
		<?php foreach ( $facts as $fact ) : ?>
			<li class="irish-keto-recipe-facts__item">
				<span class="irish-keto-recipe-facts__label"><?php echo esc_html( $fact['label'] ); ?></span>
				<span class="irish-keto-recipe-facts__value"><?php echo esc_html( (string) $fact['value'] ); ?><?php echo esc_html( ' ' . $fact['unit'] ); ?></span>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
