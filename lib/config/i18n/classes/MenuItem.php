<?php

class I18nMenuItem extends TimberMenuItem {

	public $PostClass = 'I18nPost';

	public function translations() {
		if (is_a($this->menu_object, $this->PostClass)) {
			return $this->menu_object->translations();
		}

		return array();
	}

	public function i18n() {
		if (is_a($this->menu_object, $this->PostClass)) {
			return $this->menu_object->translations();
		}

		return array();
	}

	public function translated() {
		if (is_a($this->menu_object, $this->PostClass)) {
			return $this->menu_object->translated();
		}

		return array();
	}

	public function title() {
		if (is_a($this->menu_object, $this->PostClass)) {
			$title = $this->menu_object->title();

			if (!empty($title)) {
				return $title;
			}
		}

		return parent::title();
	}

}
