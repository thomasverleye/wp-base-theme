<?php

// Include Timber classes with i18n override
require_once 'classes/Post.php';
require_once 'classes/Menu.php';
require_once 'classes/MenuItem.php';

// Add {{ i18n.current_language }} etc to Timber context
add_filter('timber_context', 'add_i18n_to_context');

function add_i18n_to_context($data) {
	$data['i18n'] = array(
		'languages' => I18N_SUPPORTED_LANGUAGES,
		'is_multilang' => I18N_MULTILANG,
		'default_language' => I18N_DEFAULT_LANGUAGE,
		'current_language' => I18N_CURRENT_LANGUAGE
	);

	return $data;
}
