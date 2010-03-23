<?php

	class HomePageLayoutFeature extends OnePanelFeature {
		
		protected $title = 'Home Page Layouts';
		protected $help_text = 'Change the look and feel of your site homepage by selecting a new premade layout &rarr;';
		
		private $available_layouts = null;
		private $default_layout = null;
		
		public function Render() {
			
			$response  = '<div class="PopUp_F">';
			$response .= '<div class="F-default">';
			$response .= '<div class="Title">Default Layout</div>';
			$response .= '<div class="Desc">Select the layout you wish to make the site default &rarr;</div>';
			$response .= '<div class="ThumbActive" style="border:1px solid #b7cad7;"><div class="HomeLayoutThumb"/></div></div>';
			$response .= '<div class="DefaultDropDown"><select onchange="op_admin.HomeLayouts.SwitchDefault()" name="popup_layout_default_select" id="popup_layout_default_select">';
			
			foreach ($this->available_layouts as $key => &$feature) {
				$response .= '<option' . (($this->default_layout == $feature) ? ' selected="selected" ' : '') . '>' . $feature->GetName() . '</option>';
			}
			
			$response .= '</select>';
			$response .= '</div>';
			$response .= '</div>';
			$response .= '</div>';
			
			return( $response );
			
		}
		
		/** 
		 * Set Available Layouts
		 * 
		 * Check to see if each layout is already in the property array, 
		 * if it is then check the file path for changes and action appropriately
		 * if not add from the config file
		 * 
		 * @param array[OnePanelHomePageLayout] $layouts
		 */
		public function SetAvailableLayouts( $layouts ) {
			
			if (is_array( $layouts )) {
				
				foreach ($layouts as &$config_layout) {
						
					// Check for existence
					if (isset( $this->available_layouts[$config_layout->GetName()] )) {
						
						// Any changes to the config? (dynamic or static) TODO check for name changes also 
						if ($config_layout->GetLocation() != $this->available_layouts[$config_layout->GetName()]->GetLocation()) {
							
							// Default to the config layout
							$this->available_layouts[$config_layout->GetName()] = &$config_layout; 
							
						}
						
					}
					else {
						
						$this->available_layouts[$config_layout->GetName()] = &$config_layout;
						
					}
					
				}
				
			}
			
		}
		
		public function SetDefaultLayout( $default ) {
			
			if (is_string( $default )) {
				
				unset( $this->default_layout );
				
				$target = $this->available_layouts[$default];
				
				if (is_object( $target )) {
					$this->default_layout = $target;
				}
				else {
					die( 'No object to act on' );
				}
				
			}
			
		}
		
		public function GetDefaultLayout() {
			return $this->default_layout;
		}
		
	}

	class HomePageLayoutModule extends OnePanelModule {
		
		protected $title = 'Home Layouts';
		protected $help_text = 'Use the Home Layouts module to change the look and feel of your site homepage by selecting a new premade layout.';
		protected $description = 'Use the Home Layouts module to change the look and feel of your site homepage by selecting a new premade layout.';
		protected $short_description = 'Change the look and feel of your site homepage by selecting a new premade layout.';
		protected $keywords = array( 'home page', 'home layout', 'layouts', 'layout', 'home' );
		protected $categories = array( 'Appearance' );
		
		public function Render() {
			$this->GenericRender();
		}
		
		public function BuildChunks() {
			return true;
		}
		
		protected function RegisterFeatures() {

			// Get the name of the default from the config
			$config_default = OnePanelConfig::GetDefaultHomePageLayout();
			
			if (! isset( $this->features['HomePageLayoutFeature'] )) {

				if (! is_object( $config_default )) return false;
				
				$config_default_name = $config_default->GetName();
				
				$feature = new HomePageLayoutFeature;
				$this->features['HomePageLayoutFeature'] = &$feature;
				$feature->SetAvailableLayouts( OnePanelConfig::GetHomePageLayouts() );
				$feature->SetDefaultLayout( $config_default_name );
				
			}
			else {
				
				$this->features['HomePageLayoutFeature']->SetAvailableLayouts( OnePanelConfig::GetHomePageLayouts() );
				
				// Check for file path changes in the default config layout rectify if we need to.
				$our_default = $this->features['HomePageLayoutFeature']->GetDefaultLayout();
				$config_layouts = OnePanelConfig::GetHomePageLayouts();
				$config_equiv = $config_layouts[$our_default->GetName()];
				
				if (! is_object( $config_equiv )) return false;
				
				if ($config_equiv->GetLocation() != $our_default->GetLocation()) {
					$this->features['HomePageLayoutFeature']->SetDefaultLayout( $config_default->GetName() );
				} 
				
			}
			
			if (OnePanelConfig::FeatureIsEnabled( 'HomePageLayoutFeature' )) {
				
				$this->features['HomePageLayoutFeature']->Enable(); // Enable the feature
				$this->enabled_features[] = 'HomePageLayoutFeature';
				
			}
			else {
				$this->features['HomePageLayoutFeature']->Disable(); // Disable the feature
			}
			
		}
		
		public function GetDefaultLayout() {
			return $this->features['HomePageLayoutFeature']->GetDefaultLayout();
		}
		
		// AJAX STUFF
		public function SwitchLayout() {
			
			$layout = mysql_real_escape_string( $_POST['layout'] );
			$this->features['HomePageLayoutFeature']->SetDefaultLayout( $layout );
			
			OnePanel::PackData();
			die(true);
			
		}
		
	}