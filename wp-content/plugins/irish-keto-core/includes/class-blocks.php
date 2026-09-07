<?php
declare(strict_types=1);

namespace Irish_Keto_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Blocks {

	public static function init(): void {
		add_action( 'init', [ __CLASS__, 'register' ] );
	}

	public static function register(): void {
		register_block_type( IRISH_KETO_CORE_DIR . 'blocks/recipe-facts' );
		register_block_type( IRISH_KETO_CORE_DIR . 'blocks/meal-planner' );
	}
}
