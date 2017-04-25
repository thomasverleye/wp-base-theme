<?php

add_filter('get_twig', 'add_imgix_to_twig');

function add_imgix_to_twig ($twig) {
	$twig->addExtension(new Twig_Extension_StringLoader());

	$twig->addFilter(new Twig_SimpleFilter('imgix', function ($src, array $options = array()) {

		$attributes = array('alt' => '');
		$src = preg_replace("/(https?:)?\/\/wp.assets.sh\/uploads/i", "https://wp-assets-sh.imgix.net", $src);

		$append = '';

		if (!empty($options[3])) {
			$append = '&' . $options[3];
		}

		if (!empty($options[0])) {
			$attributes['class'] = $options[0];
		}

		$crop_settings = '&fit=crop&crop=faces%2Centropy';
		$settings = '&auto=format%2Ccompress&ch=DPR%2CSave-Data&q=60';

		if (!empty($options[1])) {
			$attributes['sizes'] = $options[1];

			$aspect_ratio = 0;

			if (!empty($options[2])) {
				$aspect_ratio = explode(':', $options[2]);

				if (count($aspect_ratio) === 2) {
					$aspect_ratio = (float) (intval($aspect_ratio[0]) / intval($aspect_ratio[1]));
				} else {
					$aspect_ratio = floatval($aspect_ratio[0]);
				}
			}

			// Sizes are set, generate srcset
			$srcset = array();
			// Generate the following breakpoints for the srcset
			$breakpoints = array(256, 384, 512, 640, 768, 1024, 1280, 1536, 1792, 2304);

			// Default options
			foreach ($breakpoints as $breakpoint) {
				if ($aspect_ratio > 0) {
					$height = round($breakpoint / $aspect_ratio);
					array_push($srcset, $src . '?w=' . $breakpoint . '&h=' . $height . $crop_settings . $settings . $append . ' ' . $breakpoint . 'w');
				} else {
					array_push($srcset, $src . '?w=' . $breakpoint . '&fit=max' . $settings . $append . ' ' . $breakpoint . 'w');
				}
			}

			$attributes['srcset'] = implode(', ', $srcset);
			$src = explode(' ', $srcset[1])[0];
		} else {
			$src = $src . '?w=360&h=240' . $crop_settings . $settings . $append;
		}

		$attributes['src'] = $src;

		$string_attributes = array_reduce(array_keys($attributes), function ($string, $attr) use ($attributes) {
			return $attr . '="' . $attributes[$attr] . '" ' . $string;
		}, '');

    	return '<img ' . $string_attributes . ' />';
	}, array('is_variadic' => true)));

    return $twig;
}
