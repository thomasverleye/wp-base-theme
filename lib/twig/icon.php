<?php

$icon_spritesheet_is_rendered = false;

add_filter(
	'get_twig', function ( $twig ) {
		$twig->addExtension( new Twig_Extension_StringLoader() );

		$twig->addFilter(
			new Twig_SimpleFilter(
				'icon', function ( $icon ) {
					global $icon_spritesheet_is_rendered;

					$html = array();

					if ( ! $icon_spritesheet_is_rendered ) {
						$file        = get_stylesheet_directory() . '/public/assets/icons/icons.svg';
						$spritesheet = file_get_contents( $file );

						array_push( $html, $spritesheet );
						$icon_spritesheet_is_rendered = true;
					}

					array_push( $html, '<svg class="icon icon-' . $icon . '"><use xlink:href="#icon-' . $icon . '" /></svg>' );

					return implode( '', $html );
				}
			)
		);

		return $twig;
	}
);
