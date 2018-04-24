<?php

add_filter( 'mr_csp_configure_policy', function ( $csp ) {
	$csp->upgrade_insecure_requests();
	$csp->support_google_tag_manager();

	return $csp;
}, 10, 1 );
