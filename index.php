<?php

$context = Timber::get_context();

$context['page'] = $page = new TimberPost();

Timber::render( array( 'page-' . $page->post_name . '.twig', 'page.twig' ), $context );
