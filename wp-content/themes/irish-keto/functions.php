<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'after_setup_theme',
	static function (): void {
		add_theme_support( 'editor-styles' );
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'post-thumbnails' );
	}
);

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		$theme_uri = get_stylesheet_directory_uri();
		$theme_dir = get_stylesheet_directory();

		wp_enqueue_style(
			'irish-keto-fonts',
			$theme_uri . '/assets/css/fonts.css',
			[],
			(string) filemtime( $theme_dir . '/assets/css/fonts.css' )
		);

		wp_enqueue_style(
			'irish-keto-cards',
			$theme_uri . '/assets/css/cards.css',
			[ 'irish-keto-fonts' ],
			(string) filemtime( $theme_dir . '/assets/css/cards.css' )
		);
	}
);
