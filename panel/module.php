<?php

	/**
	 * TODO in order to stop someone from activating the functions
	 * after they are checked, the license will have to be checked
	 * in each render function
	 */
	abstract class OnePanelModule {
		
		
		protected $title;
		protected $help_text;
		protected $description;
		protected $short_description;
		public $features = null;
		protected $chunks;
		protected $keywords;
		protected $categories;
		
		protected $view_count = 0;
		
		
		/*
		 * Management Enabled 
		 * 
		 * Whether or not this module should present it's self 
		 * for management in One Panel
		 * 
		 */
		protected $enabled = false;
		protected $enabled_features = null; // Must be null when woken up
		protected $registered_features = null; // Must be null when woken up
		
		
		abstract public function Render();
		abstract public function BuildChunks();
		abstract protected function RegisterFeatures();
		
		
		
		final public function __construct() {
			if(
				is_null( $this->title ) ||
				is_null( $this->help_text )
			) {
				die( 'Module properties not set.' );
			}
			else {
				$this->RegisterFeatures();
				$this->DecipherEnabledState();
			}
		}
		
		final public function __wakeup() {

			/*
			 * These two have to be null on wakeup
			 * For enabled_features this is to allow for changes to the config file
			 * For registered_features this is because its used to destroy garbage
			 */ 
			$this->enabled_features = null;
			$this->registered_features = null;
			
			$this->RegisterFeatures();
			$this->DestroyGarbage();
			$this->DecipherEnabledState();
			
		}
		
		public function GetTitle() {
			return $this->title;
		}
		
		public function SetTitle( $title ) {
			if (is_string( $title )) $this->title = $title;
		}
		
		public function GetHelpText() {
			return $this->help_text;
		}
		
		public function SetHelpText( $help_text ){
			if (is_string( $help_text )) $this->help_text = $help_text; 
		}
		
		public function GetDescription() {
			return $this->description;
		}
		
		public function SetDescription( $description ) {
			if (is_string( $description )) $this->description = $description;
		}
		
		public function GetShortDescription() {
			return $this->short_description;
		}
		
		public function SetShortDescription( $short_description ) {
			if (is_string( $short_description )) $this->short_description = $short_description;
		}
		
		public function GetKeywords() {
			return $this->keywords;
		}
		
		public function SetKeywords( $keywords ) {
			if (is_array( $keywords )) $this->keywords = $keywords;
		}
		
		public function GetCategories() {
			return $this->categories;
		}
		
		public function SetCategories( $categories ) {
			if (is_array( $categories )) $this->categories = $categories;
		}
		
		/**
		 * Destroy Garbage
		 * 
		 * Remove unregistered features from the feature array.
		 * 
		 * @uses $this->features
		 * @uses $this->registered_features
		 * @todo Thus far un-tested
		 */
		protected function DestroyGarbage() {
			
			/*
			 * Its important not to do anything if by some chance
			 * the registered_features array didnt get populated when
			 * registering features.
			 */ 
			if (is_array( $this->registered_features )) {
				
				foreach ($this->features as $key => &$feature) {
					if (! in_array( $feature, $this->registered_features )) { // This is where refrences become very important!
						unset($this->features[$key]);
					}
				}
				
			}
			
		}
		
		protected function DecipherEnabledState() {
			
			if (is_array( $this->enabled_features )) {
				if (count( $this->enabled_features) > 0) {
					$this->enabled = true;
					return true;
				}
				else {
					$this->enabled = false;
				}
			}
			else {
				$this->enabled = false;
				return false;
			}
			
		}
		
		public function IsEnabled() {
			return $this->enabled;
		}
		
		protected function RegisterFeature( $classname ) {
			
			// If there isnt a feature in the data with this classname
			if (! isset( $this->features[$classname] )) {
				
				// Then create it
				$this->features[$classname] = new $classname();
			}
			
			
			// Important! Make sure the garbage collector does't get it.
			$this->registered_features[] = &$this->features[$classname];
			
			
			// Set the enabled state of the feature
			if (OnePanelConfig::FeatureIsEnabled( $classname )) {
				
				$this->features[$classname]->Enable(); // Enable the feature
				$this->enabled_features[] = $classname;
				
			}
			else {
				$this->features[$classname]->Disable(); // Disable the feature
			}
			
		}
		
		
		/**
		 * Generic Render
		 * 
		 * Returns a JSON string of an array with the following format:
		 * 
		 * ['title']
		 * ['content']
		 * ['info']
		 *
		 */
		public function GenericRender() {
			
			// Increase the view count
			$this->IncreaseViewCount();
			
			$response['content'] = '';
			
			foreach ($this->features as $key => &$feature) {
				if ($feature->IsEnabled()) {
					$response['content'] .= $feature->Render();
				}
			}
			
			$response['title'] = $this->title;
			$response['info'] = $this->help_text;
			$response['content'] = utf8_encode( $response['content'] );
			
			die( json_encode( $response ) );
			
		}
		
		public function GetChunk( $chunk_name ) {
			if (isset( $this->chunks[$chunk_name] )) return $this->chunks[$chunk_name];
		}
		
		public function GetDetailedChunk( $feature, $chunk_name ) {
			
			$feature = &$this->features[$feature];
			
			if (! is_null( $feature )) {
				
				$chunk = &$feature->GetDetailedChunk( $chunk_name );
				if (! empty($chunk)) {
					return $chunk;
				}
				else {
					return false;
				}
				
			}
			else {
				return false;
			}
			
		}
		
		protected function IncreaseViewCount() {
			$this->view_count++;
			OnePanel::PackData();
		}
		
		public function GetViewCount() {
			return $this->view_count;
		}
		
		static public function CompareViewCount( $a, $b ) {
			
			$a_count = $a->GetViewCount();
			$b_count = $b->GetViewCount();
			
			if ($a_count == $b_count) return 0;
			return ($a_count > $b_count) ? -1 : 1;
			
		}
		
	}
