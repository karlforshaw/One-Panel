<?php

	class OnePanelThumbnailType {
		
		private $identifier;
		private $custom_field;
		private $height = 116;
		private $width = 116;
		private $help_text = '<p>Default help text for thumbnails.</p>';
		
		public function __construct( $identifier, $custom_field ) {
			
			if (is_string( $identifier )) { 
				$this->identifier = $identifier; 
			}
			else {
				if (OnePanelConfig::UsingDebug()) {
					die( 'You did not specify an identifier for your thumbnail type.' );
				}
			}
			
			if (is_string( $custom_field )) { 
				$this->custom_field = $custom_field; 
			}
			else {
				if (OnePanelConfig::UsingDebug()) {
					die( 'You did not specify a custom field for your thumbnail type.' );
				}
			}
			
		}
		
		public function GetIdentifier() {
			return $this->identifier;
		}
		
		public function GetCustomField() {
			return $this->custom_field;
		}
		
		public function GetHelpText() {
			return $this->help_text;
		}
		
		public function GetHeight() {
			return $this->height;
		}
		
		public function GetWidth() {
			return $this->width;
		}
		
		public function SetHeight( $height ) {
			if (is_int( $height )) $this->height = $height;
		}
		
		public function SetWidth( $width ) {
			if (is_int( $width )) $this->width = $width;
		}
		
		public function SetHelpText( $text ) {
			
			if (is_string( $text )) {
				$this->help_text = $text;
			}
			
		}
		
	}