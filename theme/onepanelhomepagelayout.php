<?php

	/**
	 * Home Page Layout
	 *
	 * This class allows for the creation of a new manageable home page
	 * layout in the config file.
	 * 
	 * @author Karl Forshaw <karlforshaw@gmail.com>
	 * @copyright 2008-2009 One Theme Ltd
	 * @license One Theme License <http://www.one-theme.com/TOS/>
	 */

	class OnePanelHomePageLayout {
		
		private $name;
		private $file_path;
		
		public function __construct( $name, $file_path ) {
			
			$this->name = $name;
			if (OnePanelConfig::CheckFileProperty( $file_path )) $this->file_path = &$file_path;
			
		}
		
		public function GetLocation() {
			return $this->file_path;
		}
		
		public function GetName() {
			return $this->name;
		}
		
	}