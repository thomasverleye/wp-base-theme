<?php

global $post;

$default_sitename = get_bloginfo('name');
$default_title = get_bloginfo('name');
$default_description = get_bloginfo('description');

$twitter_handle = '@wearemrhenry';

$public = true;

// `public` defaults to true
// so we don't fuck up the SEO by forgetting
// to set the ENVIRONMENT constant
if (!empty($_ENV['ENVIRONMENT']) && $_ENV['ENVIRONMENT'] !== 'production') {
	$public = false;
}

// Don't index .a.mrhenry & .herokuapp
if (
	   strpos($_SERVER['HTTP_HOST'], '.a.mrhenry') > 0
	|| strpos($_SERVER['HTTP_HOST'], 'herokuapp') > 0
) {
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

function convert_aws_to_imgix($src) {
	return preg_replace("/(https?:)?\/\/wp.assets.sh\/uploads/i", "https://wp-assets-sh.imgix.net", $src);
}

add_action('wp_head', function () use ($defaults) {
	echo "\r\n";

	global $post;
	$post_id = get_the_ID();

	$parsed = new I18nPost($post_id);
	$custom = !empty($parsed->translations['seo']) ? $parsed->translations['seo'] : array();

	// Default title
	// Can only set the default inside the wp_head hook,
	// because we don't know the $post outside the hook
	if (!is_front_page()) {
		$default_title = get_the_title($post_id) . ' | ' . $defaults['seo:title'];
		$defaults['seo:title'] = $default_title;
	}

	// Default image
	$hero = get_field('hero_image', $post_id);
	$hero = convert_aws_to_imgix($hero);

	if (!empty($hero)) {
		$defaults['og:image'] = $hero;
		$defaults['twitter:image'] = $hero;
	}

	$canonical = wp_get_canonical_url($post_id);

	echo '<link rel="canonical" href="' . $canonical . '">' . "\r\n";
	$defaults['og:url'] = $canonical;

	$meta_tags = array();

	foreach ($defaults as $property => $content) {
		if (!empty($custom[$property])) {
			$content = $custom[$property];
		}

		$property = str_replace('seo:', '', $property);

		$meta_tags[$property] = $content;
	}

	// Fallbacks for description and title
	$meta_tags['og:title'] = empty($meta_tags['og:title']) ? $meta_tags['title'] : $meta_tags['og:title'];
	$meta_tags['twitter:title'] = empty($meta_tags['twitter:title']) ? $meta_tags['title'] : $meta_tags['twitter:title'];
	$meta_tags['og:description'] = empty($meta_tags['og:description']) ? $meta_tags['description'] : $meta_tags['og:description'];
	$meta_tags['twitter:description'] = empty($meta_tags['twitter:description']) ? $meta_tags['description'] : $meta_tags['twitter:description'];

	foreach ($meta_tags as $property => $content) {

		if ($property === 'title') {
			echo '<title>' . $content . '</title>' . "\r\n";
			continue;
		}

		if ($property === 'og:image') {
			$content = convert_aws_to_imgix($content);
			$content = $content . '?w=1200&h=630&auto=format%2Ccompress&fit=crop&crop=faces%2Centropy';

			echo '<meta property="og:image:width" content="1200">' . "\r\n";
			echo '<meta property="og:image:height" content="630">' . "\r\n";
		}

		if ($property === 'twitter:image') {
			$content = convert_aws_to_imgix($content);

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
