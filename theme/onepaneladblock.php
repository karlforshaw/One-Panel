<?php

	final class OnePanelAdBlock {
		
		private $name;
		private $width = null;
		private $height = null;
		private $palettes;
		
		public function __construct( $name, $width, $height ) {
			
			$this->palettes = array();
			
			if (is_string( $name )) $this->name = $name; else die( 'Must pass a string value as the adblock name.' );
			if (is_numeric( $width )) $this->width = $width; else die( 'Must pass a numeric value as the adblock width.' );
			if (is_numeric( $height )) $this->height = $height; else die( 'Must pass a numeric value as the adblock height.' );
			
		}
		
		public function AddColorPalette( AdsenseColorPalette $palette, $skin_name ) {
			
			// If debug, check for the existence of the skin
			if (is_string( $skin_name )) {
				if (OnePanelConfig::UsingDebug()) {
					
					$skins = &OnePanelConfig::GetSkins();
					
					if (! array_key_exists( $skin_name, $skins )) {
						die( 'You are trying to add a color palette for a skin that has not been added.' );
					}
					
				}
			}
			else {
				die( 'You must pass a valid string name to AddColorPalette' );
			}
			
			$this->palettes[$skin_name] = &$palette;
			
		}
		
		public function GetName() {
			return $this->name;
		}
		
		public function GetHeight() {
			return $this->height;
		}
		
		public function GetPalettes() {
			return $this->palettes;
		}
		
		public function GetPalette( $skin_name ) {
			if (is_string( $skin_name )) return $this->palettes[$skin_name];
			else return false;
		}
		
		public function GetWidth() {
			return $this->width;
		}
		
	}