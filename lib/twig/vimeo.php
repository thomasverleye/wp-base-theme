<?php

add_filter('get_twig', 'add_vimeo_to_twig');

function add_vimeo_to_twig ($twig) {
	$twig->addExtension(new Twig_Extension_StringLoader());

	$twig->addFilter(new Twig_SimpleFilter('vimeo', function ($shortcode) {
    	return '<iframe src="https://player.vimeo.com/video/' . $shortcode . '?badge=0&byline=0&color=ffffff&portrait=0&title=0&wmode=transparent" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen width="480" height="270"></iframe>';
	}));

    return $twig;
}
