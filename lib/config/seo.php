<?php

global $post;

$default_sitename = get_bloginfo('name');
$default_title = get_bloginfo('name');
$default_description = get_bloginfo('description');

$twitter_handle = '@wearemrhenry';

$public = true;

// Don't index .a.mrhenry & .herokuapp
if (strpos($_SERVER['HTTP_HOST'], '.wp.mrhenry') > 0) {
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

	// Facebook
	'og:type' => 'website',
	'og:title' => '',
	'og:url' => '',
	'og:image' => '',
	'og:site_name' => $default_sitename,
	'og:description' => '',

	// Twitter
	'twitter:card' => 'summary_large_image',
	'twitter:site' => $twitter_handle,
	'twitter:title' => '',
	'twitter:description' => '',
	'twitter:image' => ''
);

function convert_gcs_to_imgix($src) {
	if (is_array($src) && !empty($src['url'])) {
		$src = $src['url'];
	}

	return preg_replace("/https:\/\/wp\.assets\.sh\/uploads/i", "https://wp-assets-sh.imgix.net", $src);
}

add_action('wp_head', function () use ($defaults) {
	echo "\r\n";

	global $post;
	$post_id = get_the_ID();

	$parsed = new I18nPost($post_id);
	$custom = !empty($parsed->translations['seo']) ? $parsed->translations['seo'] : array();

	// Fix $custom behavior for one-lang sites
	// @todo - Investigate
	if (empty($custom)) {
		foreach ($defaults as $key => $value) {
			$override = get_field($key, $parsed);

			if (!empty($override)) {
				$custom[$key] = $override;
			}
		}
	}

	if (is_404()) {
		// Override title for 404 page
		if (!empty(I18N_CURRENT_LANGUAGE) && I18N_CURRENT_LANGUAGE === 'en') {
			$default_title = 'Page not found';
		}  else {
			$default_title = 'Pagina niet gevonden';
		}

		$defaults['seo:title'] = $default_title;
	} else if (!is_front_page()) {
		// Default title
		// Can only set the default inside the wp_head hook,
		// because we don't know the $post outside the hook
		$default_title = get_the_title($post_id);
		$defaults['seo:title'] = $default_title;
	}

	// Default image
	$hero = get_field('hero_image', $post_id);
	$hero = convert_gcs_to_imgix($hero);

	if (!empty($hero)) {
		$defaults['og:image'] = $hero;
		$defaults['twitter:image'] = $hero;
	}

	$canonical = wp_get_canonical_url($post_id);

	echo '<link rel="canonical" href="' . $canonical . '">' . "\r\n";

	if (!empty(I18N_SUPPORTED_LANGUAGES)) {
		foreach ($parsed->translated() as $lang => $alternate) {
			if ($lang !== I18N_CURRENT_LANGUAGE) {
				echo '<link rel="alternate" hreflang="' . $lang . '" href="' . $alternate . '">' . "\r\n";
			}
		}
	}

	$defaults['og:url'] = $canonical;

	$meta_tags = array();

	foreach ($defaults as $property => $content) {
		if (!empty($custom[$property])) {
			$content = $custom[$property];
		}

		$property = str_replace('seo:', '', $property);

		if (!is_front_page() && $property === 'title') {
			$content = $content . ' | ' . get_bloginfo('name');
		}

		if ($property === 'description') {
			$content = htmlentities($content);
		}

		$meta_tags[$property] = $content;
	}

	// Fallbacks for description and title
	$meta_tags['og:title'] = empty($meta_tags['og:title']) ? $meta_tags['title'] : $meta_tags['og:title'];
	$meta_tags['twitter:title'] = empty($meta_tags['twitter:title']) ? $meta_tags['title'] : $meta_tags['twitter:title'];
	$meta_tags['og:description'] = empty($meta_tags['og:description']) ? $meta_tags['description'] : htmlentities($meta_tags['og:description']);
	$meta_tags['twitter:description'] = empty($meta_tags['twitter:description']) ? $meta_tags['description'] : htmlentities($meta_tags['twitter:description']);

	foreach ($meta_tags as $property => $content) {

		if ($property === 'title') {
			echo '<title>' . $content . '</title>' . "\r\n";
			continue;
		}

		if ($property === 'og:image') {
			$content = convert_gcs_to_imgix($content);
			$content = $content . '?w=1200&h=630&auto=format%2Ccompress&fit=crop&crop=faces%2Centropy';

			echo '<meta property="og:image:width" content="1200">' . "\r\n";
			echo '<meta property="og:image:height" content="630">' . "\r\n";
		}

		if ($property === 'twitter:image') {
			$content = convert_gcs_to_imgix($content);

			if (!empty($meta_tags['twitter:card'])) {
				if ($meta_tags['twitter:card'] === 'summary_large_image') {
					$content = $content . '?w=1200&h=630&auto=format%2Ccompress&fit=crop&crop=faces%2Centropy';
				}

				if ($meta_tags['twitter:card'] === 'summary') {
					$content = $content . '?w=480&h=480&auto=format%2Ccompress&fit=crop&crop=faces%2Centropy';
				}
			}
		}

		if (strpos($property, 'og:') === 0) {
			echo '<meta property="' . $property . '" content="' . $content . '">' . "\r\n";
		} else {
			echo '<meta name="' . $property . '" content="' . $content . '">' . "\r\n";
		}
	}

});
