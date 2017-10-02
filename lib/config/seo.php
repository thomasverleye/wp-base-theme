<?php

global $post;

$default_sitename = get_bloginfo( 'name' );
$default_title = get_bloginfo( 'name' );
$default_description = get_bloginfo( 'description' );

$twitter_handle = '@wearemrhenry';

$public = true;

// Don't index the .wp.mrhenry.eu domain.
$host = isset( $_SERVER['HTTP_HOST'] ) ? wp_unslash( $_SERVER['HTTP_HOST'] ) : '';

if ( strpos( $host, '.wp.mrhenry' ) > 0 ) {
	$public = false;
}

$defaults = array(
	'seo:title' => $default_title,
	'seo:description' => $default_description,
	'seo:author' => $default_sitename,
	'seo:publisher' => 'Mr. Henry',
	'seo:copyright' => $default_sitename,
	'seo:robots' => $public ? 'index,follow' : 'noindex,nofollow',
	'seo:distribution' => $public ? 'Global' : 'IU',

	'og:type' => 'website',
	'og:title' => '',
	'og:url' => '',
	'og:image' => '',
	'og:site_name' => $default_sitename,
	'og:description' => '',

	'twitter:card' => 'summary_large_image',
	'twitter:site' => $twitter_handle,
	'twitter:title' => '',
	'twitter:description' => '',
	'twitter:image' => '',
);

function convert_aws_to_imgix( $src ) {
	return preg_replace( '/https:\/\/wp\.assets\.sh\/uploads/i', 'https://wp-assets-sh.imgix.net', $src );
}

add_action('wp_head', function () use ( $defaults ) {
	echo "\r\n";

	global $post;
	$post_id = get_the_ID();

	$custom = array();

	if ( class_exists( 'I18nPost' ) ) {
		$parsed = new I18nPost( $post_id );

		if ( ! is_null( $parsed ) && $parsed->ID && ! empty( $parsed->translations()['seo'] ) ) {
			$custom = $parsed->translations()['seo'];
		}
	}

	if ( empty( $custom ) && $post_id ) {
		$custom = get_fields( $post_id );
	}

	if ( is_404() ) {
		// Override title for 404 page.
		if ( defined( 'I18N_CURRENT_LANGUAGE' ) && ! empty( I18N_CURRENT_LANGUAGE ) ) {
			if ( I18N_CURRENT_LANGUAGE === 'en' ) {
				$default_title = 'Page not found';
			} elseif ( I18N_CURRENT_LANGUAGE === 'fr' ) {
				$default_title = 'Page non trouvée';
			} elseif ( I18N_CURRENT_LANGUAGE === 'nl' ) {
				$default_title = 'Pagina niet gevonden';
			} elseif ( I18N_CURRENT_LANGUAGE === 'de' ) {
				$default_title = 'Seite nicht gefunden';
			}
		} else {
			$default_title = 'Page not found';
		}

		$defaults['seo:title'] = $default_title;
	} elseif ( ! is_front_page() ) {
		// Default title
		// Can only set the default inside the wp_head hook,
		// because we don't know the $post outside the hook.
		$default_title = get_the_title( $post_id );
		$defaults['seo:title'] = $default_title;
	}

	$hero = get_field( 'hero_image', $post_id );
	$hero = convert_aws_to_imgix( $hero );

	if ( ! empty( $hero ) ) {
		$defaults['og:image'] = $hero;
		$defaults['twitter:image'] = $hero;
	}

	$canonical = wp_get_canonical_url( $post_id );

	echo '<link rel="canonical" href="' . esc_attr( $canonical ) . '">' . "\r\n";

	if ( isset( $parsed ) && ! empty( I18N_SUPPORTED_LANGUAGES ) ) {
		foreach ( $parsed->translated() as $lang => $alternate ) {
			if ( I18N_CURRENT_LANGUAGE !== $lang ) {
				echo '<link rel="alternate" hreflang="' . esc_attr( $lang ) . '" href="' . esc_attr( $alternate ) . '">' . "\r\n";
			}
		}
	}

	$defaults['og:url'] = $canonical;

	$meta_tags = array();

	foreach ( $defaults as $property => $content ) {
		if ( ! empty( $custom[ $property ] ) ) {
			$content = $custom[ $property ];
		}

		$property = str_replace( 'seo:', '', $property );

		if ( ! is_front_page() && 'title' === $property ) {
			$content = $content . ' | ' . get_bloginfo( 'name' );
		}

		if ( 'description' === $property ) {
			$content = esc_attr( $content );
		}

		$meta_tags[ $property ] = $content;
	}

	$meta_tags['og:title'] = empty( $meta_tags['og:title'] ) ? $meta_tags['title'] : $meta_tags['og:title'];
	$meta_tags['twitter:title'] = empty( $meta_tags['twitter:title'] ) ? $meta_tags['title'] : $meta_tags['twitter:title'];
	$meta_tags['og:description'] = empty( $meta_tags['og:description'] ) ? $meta_tags['description'] : htmlentities( $meta_tags['og:description'] );
	$meta_tags['twitter:description'] = empty( $meta_tags['twitter:description'] ) ? $meta_tags['description'] : htmlentities( $meta_tags['twitter:description'] );

	foreach ( $meta_tags as $property => $content ) {

		if ( 'title' === $property ) {
			echo '<title>' . $content . '</title>' . "\r\n";
			continue;
		}

		if ( 'og:image' === $property && ! empty( $content ) ) {
			$content = convert_aws_to_imgix( $content );
			$content = $content . '?w=1200&h=630&auto=format%2Ccompress&fit=crop&crop=faces%2Centropy';

			echo '<meta property="og:image:width" content="1200">' . "\r\n";
			echo '<meta property="og:image:height" content="630">' . "\r\n";
		}

		if ( 'twitter:image' === $property && ! empty( $content ) ) {
			$content = convert_aws_to_imgix( $content );

			if ( ! empty( $meta_tags['twitter:card'] ) ) {
				if ( 'summary_large_image' === $meta_tags['twitter:card'] ) {
					$content = $content . '?w=1200&h=630&auto=format%2Ccompress&fit=crop&crop=faces%2Centropy';
				}

				if ( 'summary' === $meta_tags['twitter:card'] ) {
					$content = $content . '?w=480&h=480&auto=format%2Ccompress&fit=crop&crop=faces%2Centropy';
				}
			}
		}

		if ( strpos( $property, 'og:' ) === 0 ) {
			echo '<meta property="' . esc_attr( $property ) . '" content="' . $content . '">' . "\r\n";
		} else {
			echo '<meta name="' . esc_attr( $property ) . '" content="' . $content . '">' . "\r\n";
		}
	}
});
