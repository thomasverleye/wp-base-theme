<?php

register_nav_menus(
	array(
		'primary_navigation' => 'Primary Navigation',
	)
);

add_filter( 'timber_context', function ( $data ) {
	$data['menus'] = array(
		'primary_navigation' => new TimberMenu( 'primary_navigation' ),
	);

	return $data;
});
