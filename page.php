<?php

$context = Timber::get_context();

$page            = new TimberPost();
$context['page'] = $page;

Timber::render(
	array(
		'page-' . $page->post_name . '.twig',
		pathinfo( get_page_template(), PATHINFO_FILENAME ) . '.twig',
		'page.twig',
	), $context
);
