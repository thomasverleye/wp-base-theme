<?php

add_filter('get_twig', 'add_youtube_to_twig');

function add_youtube_to_twig ($twig) {
	$twig->addExtension(new Twig_Extension_StringLoader());

	$twig->addFilter(new Twig_SimpleFilter('youtube', function ($shortcode) {
    	return '<iframe src="https://www.youtube.com/embed/' . $shortcode . '?color=white&controls=2&disablekb=1&modestbranding=1&rel=0&showinfo=0" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen width="480" height="270"></iframe>';
	}));

    return $twig;
}
