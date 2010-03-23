<?php

	/**
	 * Skin
	 * 
	 * This class allows the creation of additional manageable skins
	 * in the config file.
	 * 
	 * @author Karl Forshaw <karlforshaw@gmail.com>
	 * @copyright 2008-2009 One Theme Ltd
	 * @license One Theme License <http://www.one-theme.com/TOS/>
	 */


	class OnePanelSkin {
		
		protected $name = null;
		protected $stylesheets = null;
		protected $managable_images = null;
		
		public function __construct( $name ) {
			$this->name = $name;
		}
		
		public function GetName() {
			return $this->name;
		}
		
		public function AddStyle( $identifier, $file_path ) {
			$this->stylesheets[$identifier] = $file_path;
		}
		
		public function AddManagableImage( $identifier, $default_file_path ) {
			$this->managable_images[$identifier] = $default_file_path;
		}
		
		public function GetStyle( $identifier ) {
			return $this->stylesheets[$identifier];	// TODO check for null value?
		}
		
		public function GetStyles() {
			return $this->stylesheets;
		}
		
		public function GetManagableImage( $identifier ) {
			return $this->managable_images[$identifier]; // TODO check for null value?
		}
		
		public function GetManagableImages() {
			return $this->managable_images;
		}
		
		
	}