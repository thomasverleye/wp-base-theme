<?php

// Include Timber classes with i18n override
require_once 'classes/Post.php';
require_once 'classes/Menu.php';
require_once 'classes/MenuItem.php';

define("I18N_SUPPORTED_LANGUAGES", array(
	"en" => 'https://' . $_SERVER["HTTP_HOST"],
));

define("I18N_DEFAULT_LANGUAGE", "en");
define("I18N_MULTILANG", false);

// Detect language
$matches = array();
// preg_match("/^\/(nl)($|[\/\?](.*)$)/", $_SERVER['REQUEST_URI'], $matches);

if (!empty($matches)) {
	define("I18N_CURRENT_LANGUAGE", $matches[1]);
} else {
	define("I18N_CURRENT_LANGUAGE", I18N_DEFAULT_LANGUAGE);
}

header("Content-Language: " . I18N_CURRENT_LANGUAGE);

add_filter('option_home', function ($config_wp_home) {
	return I18N_SUPPORTED_LANGUAGES[I18N_CURRENT_LANGUAGE];
}, 0);

add_filter('option_siteurl', function ($config_wp_siteurl) {
	return I18N_SUPPORTED_LANGUAGES[I18N_CURRENT_LANGUAGE];
}, 0);

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
