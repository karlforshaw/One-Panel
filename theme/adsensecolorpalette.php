<?php

	final class AdsenseColorPalette {
		
		private $border_color = 'ffffff';
		private $bg_color = 'ffffff';
		private $link_color = '424242';
		private $text_color = '696969';
		private $url_color = '424242';
		
		public function __construct( $border, $background, $link, $text, $url ) {
			
			// Make sure they pass valid color codes
			$arguments = func_get_args();
			
			foreach ($arguments as $key => &$arg) {
				if (! preg_match( '/^[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/', $arg )) {
					die( 'Invalid color code passed to ' . get_class( $this ) . ' for argument ' . $key );
				}
			}
			
			$this->border_color = $border;
			$this->bg_color = $background;
			$this->link_color = $link;
			$this->text_color = $text;
			$this->url_color = $url;
			
		}
		
		public function GetBorderColor() {
			return $this->border_color;
		}
		
		public function GetBackgroundColor() {
			return $this->bg_color;
		}
		
		public function GetLinkColor() {
			return $this->link_color;
		}
		
		public function GetTextColor() {
			return $this->link_color;
		}
		
		public function GetUrlColor() {
			return $this->url_color;
		}
		
	}