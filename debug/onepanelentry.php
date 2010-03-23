<?php

	class OnePanelEntry {
		
		protected $message;
		
		public function __construct( $message ) {
			$this->message = $message;
			return true;
		}
		
		public function GetMessage() {
			return $this->message;
		}
		
		public function Report() {
			return $this->message;
		}
		
	}

?>