<?php

add_filter('get_twig', 'add_sanitize_to_twig');

function add_sanitize_to_twig ($twig) {
	$twig->addExtension(new Twig_Extension_StringLoader());

	$twig->addFilter(new Twig_SimpleFilter('sanitize', function ($string) {
		return sanitize_title($string);
	}));

	return $twig;
}
