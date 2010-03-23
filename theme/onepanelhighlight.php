<?php

	final class OnePanelHighlight extends OnePanelBehaviouralConfigObject {
		
		private $identifier;
		private $default_type = 2;
		private $default_limit = null;
		private $title_limit = null;
		
		
		public function __construct( $identifier ) {

			if (is_string( $identifier )) {
				
				$this->identifier = $identifier;
				return true;
				
			}
			else return false;
			
		}
		
		public function GetName() {
			return $this->identifier;
		}
		
		public function GetDefaultSourceType() {
			return $this->default_type;
		}
		
		public function GetContentLimit() {
			return $this->default_limit;
		}
		
		public function GetTitleLimit() {
			return $this->title_limit;
		}
		
		public function DefaultToPost() {
			$this->default_type = 2;
		}
		
		public function DefaultToCategory() {
			$this->default_type = 3;
		}
		
		public function LimitContentByDefault( $limit ) {
			
			if (is_int( $limit )) {
				$this->default_limit = $limit;
			}
			
		}
		
		public function LimitTitles( $limit ) {
			
			if (is_int( $limit )) {
				$this->title_limit = $limit;
			}
			
		}
		
	}