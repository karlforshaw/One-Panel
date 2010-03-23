<?php

	class OnePanelBehaviour {
		
		private $requirements = array();
		private $outcomes = array();
		
		public function AddRequirement( $requirement ) {
			$this->requirements[] = $requirement;
		}
		
		public function AddOutcome( $affected_module, $parameters  ) {
			$this->outcomes[] = new OnePanelOutcome( $affected_module, $parameters );
		}
		
		public function &GetOutcomes() {
			return $this->outcomes;
		}
		
		public function &GetRequirements() {
			return $this->requirements;
		}
		
		
		public function ModuleIsAffected( &$config_module ) {
		
			if (is_object( $config_module )) { // TODO remove this with type hinting when we implement OnePanelBehaviouralConfigModule
				
				foreach ($this->outcomes as &$outcome) {
					
					if ($outcome->GetAffectedModule() === $config_module)
						return true;
					
				}
				
			}
			
		}
		
	}