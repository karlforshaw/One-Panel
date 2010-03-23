<?php

	class OnePanelOutcome {
		
		private $affected_module;
		private $params = array();
		
		public function __construct( $affected_module, $params ) {
			
			if (is_object( $affected_module )) {
				$this->affected_module = &$affected_module;
			}
			
			if (is_array( $params )) {
				$this->params = $params;	
			}
		}
		
		public function &GetAffectedModule() {
			return $this->affected_module;
		}
		
		public function &GetParameters() {
			return $this->params;
		}
		
	}