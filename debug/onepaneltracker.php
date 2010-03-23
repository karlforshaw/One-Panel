<?php

	class OnePanelTracker extends OnePanelEntry {
		
		private $status;
		
		public function __construct( $message, $status=null ) {
			
			$this->status = $status;
			$this->message = $message;
			
			return $this;
		}
		
		public function Affirm() {
			$this->status = true;
		}
		
		public function Fail() {
			$this->status = false;
		}
		
		public function GetStatus() {
			return $this->status;
		}
		
		public function Report() {
			
			$return = $this->message . '... ';
			
			if ($this->status === null) 
				$return .= 'INCOMPLETE';
				
			elseif ($this->status === false)
				$return .= 'FAIL';
				
			elseif ($this->status === true) 
				$return .= 'OK';
				
			return $return;
			
		}
		
	}