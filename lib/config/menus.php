<?php

register_nav_menus(array(
	'primary_navigation' => 'Primary Navigation'
));

add_filter('timber_context', 'add_menus_to_context');

function add_menus_to_context($data) {
	$data['menus'] = array(
		'primary_navigation' => new TimberMenu('primary_navigation')
	);

	return $data;
}
