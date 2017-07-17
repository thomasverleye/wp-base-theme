<?php

// This class overrides TimberPost
// and adds these properties:
//
// {{ post.translations }}
//    Nested array with all translations.
//    Field group with name 'en' (or language shortcode) gets merged at the top level
//    Other field groups are accessible through post.translations.my_field_group_name
//    {{ post.i18n }} is an alias
//
// {{ post.translated }}
//    A key-value array with links to the translated posts
//    'en' => 'https://www.mydomain.com/my-post',
//    'de' => 'httsp://www.mydomain.com/de/meinen-pfost'
class I18nPost extends TimberPost {

	public $PostClass = 'I18nPost';

	private $_translations;
	private $_translated;

	public function translations() {
		$id = !empty($this->object_id) ? $this->object_id : $this->id;

		// Cache the results to run the heavy handling only once
		if (!$this->_translations) {
			$language = empty(I18N_CURRENT_LANGUAGE) ? I18N_DEFAULT_LANGUAGE : I18N_CURRENT_LANGUAGE;

			$all_fields = get_fields($id);
			$translations = array();

			if (!$all_fields) {
				return array();
			}

			if ($language !== I18N_DEFAULT_LANGUAGE) {
				// Copy over fallback fields when they exist in the default language
				// This cover the edge cases where a language has been added
				// in the ACF field groups, but the post hasn't been saved since
				// (ACF creates the correct keys only on save of the post)

				// Find all keys for the default language
				$fallback_fields = array_filter($all_fields, function ($key) {
					return strpos($key, I18N_DEFAULT_LANGUAGE) === 0;
				}, ARRAY_FILTER_USE_KEY);

				foreach ($fallback_fields as $key => $value) {
					// Translate the default language key to the current language
					$translated_key = $language . substr($key, strlen(I18N_DEFAULT_LANGUAGE));

					// If the translated version does not exist, copy over the fallback fields
					if (!isset($all_fields[$translated_key])) {
						$all_fields[$translated_key] = $value;
					}
				}
			}

			// Loop over all known custom fields for this post
			foreach (array_keys($all_fields) as $group) {

				// Field group with name `shortcode` gets copied to the root array
				if ($group === I18N_CURRENT_LANGUAGE) {
					$data = $all_fields[$group];
					$fallback = $all_fields[I18N_DEFAULT_LANGUAGE];
					$target = &$translations;
				} elseif (strpos($group, I18N_CURRENT_LANGUAGE . '_') === 0) {
					$data = $all_fields[$group];
					$group_without_prefix = substr($group, strlen(I18N_CURRENT_LANGUAGE) + 1);
					$fallback = $all_fields[I18N_DEFAULT_LANGUAGE . '_' . $group_without_prefix];
					$translations[$group_without_prefix] = array();
					$target = &$translations[$group_without_prefix];
				} else {
					if (
						   is_array($all_fields[$group])
						&& !empty($all_fields[$group][0])
						&& in_array('acf_fc_layout', array_keys($all_fields[$group][0]))
					) {
						$flexible_content = array();

						foreach ($all_fields[$group] as $entry) {
							array_push($flexible_content, array_reduce(array_keys($entry), function($carry, $field_name) use ($entry) {
								$acf_fc_layout = $entry['acf_fc_layout'];
								$value = $entry[$field_name];

								$languages = implode('|', array_keys(I18N_SUPPORTED_LANGUAGES));
								$is_translatable_field = preg_match("/^(" . $languages . ")_.*$/i", $field_name);

								if ($is_translatable_field) {
									if (strpos($field_name, I18N_CURRENT_LANGUAGE . '_') === 0) {
										$field_name_without_prefix = substr($field_name, strlen(I18N_CURRENT_LANGUAGE) + 1);
										$fallback = $entry[I18N_DEFAULT_LANGUAGE . '_' . $field_name_without_prefix];
										$trimmed_field_name = preg_replace("/^" . $acf_fc_layout . "_/i", '', $field_name_without_prefix);
										$carry[$trimmed_field_name] = (empty($value) && !empty($fallback)) ? $fallback : $value;
									}
								} else {
									$trimmed_field_name = preg_replace("/^" . $acf_fc_layout . "_/i", '', $field_name);
									$carry[$trimmed_field_name] = $value;
								}

								return $carry;
							}, array( 'acf_fc_layout' => $entry['acf_fc_layout'] )));
						}

						$translations[$group] = $flexible_content;
					}

					continue;
				}

				foreach ($data as $key => $value) {
					if (empty($value) && !empty($fallback[$key])) {
						$value = $fallback[$key];
					}

					if (is_a($value, 'WP_Post')) {
						$value = new I18nPost($value);
					}

					$target[$key] = $value;
				}
			}

			$this->_translations = $translations;
		}

		return $this->_translations;
	}

	public function title() {
		if (!empty($this->translations()['title'])) {
			return $this->translations()['title'];
		}

		return $this->post_title;
	}

	public function content($page = 0, $len = -1) {
		if (!empty($this->translations()['content'])) {
			return $this->translations()['content'];
		}

		return $this->post_content;
	}

	public function translated($exclude_current = false) {
		if (!$this->_translated) {
			$translated = array();

			$relative_path = str_replace(get_home_url(), '', $this->link());

			foreach (I18N_SUPPORTED_LANGUAGES as $language => $root_path) {
				if ($exclude_current && $language === I18N_CURRENT_LANGUAGE) {
					continue;
				}

				$translated[$language] = $root_path . $relative_path;
			}

			$this->_translated = $translated;
		}

		return $this->_translated;
	}

	public function i18n() {
		return $this->_translations;
	}

}
