<?php
declare(strict_types=1);

namespace Irish_Keto_Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SVG uploads are blocked by WordPress core because a malicious SVG can
 * carry embedded script. We only need this for our own hand-authored brand
 * art, so it's gated to admins rather than opened up for every uploader.
 */
class Media {

	public static function init(): void {
		add_filter( 'upload_mimes', [ __CLASS__, 'allow_svg_for_admins' ] );
	}

	public static function allow_svg_for_admins( array $mimes ): array {
		if ( current_user_can( 'manage_options' ) ) {
			$mimes['svg'] = 'image/svg+xml';
		}

		return $mimes;
	}
}
