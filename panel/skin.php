<?php

	// Features
	class SkinFeatureSwitcher extends OnePanelFeature {
		
		protected $title = 'Skin Switcher';
		protected $help_text =  'Allow your readers to change the color scheme on your blog.';
		
		public function Render() {
			// Generic Activation Render
			return $this->RenderOnOff();
		}
		
	}
	
	class DefaultSkinFeature extends OnePanelFeature {
		
		protected $title = 'Default Skin';
		protected $help_text = 'Choose the default color scheme for your blog.';
		
		private $available_skins = null;
		private $default_skin = null;
		
		public function SetSkins( $skins ) {
			$this->available_skins = $skins;
		}
		
		public function &GetDefaultSkin() {
			return $this->default_skin;
		}
		
		public function SetDefaultSkin( $default_skin ) {
			
			unset( $this->default_skin );
			$default_skin->SetAsActive();
			$this->default_skin = &$default_skin;
			
		}
		
		public function Render() {
			
			$response  = '<div class="PopUp_F">';
			$response .= '<div class="F-default">';
			$response .= '<div class="Title">Default Skin</div>';
			$response .= '<div class="Desc">Select the skin you wish to make the site default &rarr;</div>';
			$response .= '<div class="ThumbActive" style="border:1px solid #b7cad7;"><div class="Default_Thumb"/></div></div>';
			$response .= '<div class="DefaultDropDown"><select class="select" onchange="op_admin.Skins.SwitchDefault()" name="popup_skin_default_select" id="popup_skin_default_select">';
			
			foreach ($this->available_skins as $key => &$feature) {
				$response .= '<option' . (($this->default_skin === $feature) ? ' selected="selected" ' : '') . '>' . $feature->GetTitle() . '</option>';
			}
			
			$response .= '</select>';
			$response .= '</div>';
			$response .= '</div>';
			$response .= '</div>';
			
			return( $response );
		}
		
	}
	
	
	class SkinFeature extends OnePanelFeature {
		
		protected $title = null;
		protected $help_text = null;
		protected $images = array();
		protected $stylesheets = array();
		
		public function AddImage( $key, $path ) {
			$this->images[$key] = $path;
		}
		
		public function AddStyle( $identifier, $file_path ) {
			$this->stylesheets[$identifier] = $file_path;
		}
		
		public function GetImages() {
			return $this->images;
		}
		
		public function RemoveImage( $key ) {
			unset( $this->images[$key] );
		}
		
		public function RemoveStyle( $key ) {
			unset( $this->stylesheets[$key] );
		}
		
		public function UpdateImage( $key, $file_path ) {
			if ($this->ImageExists( $key )) {
				$this->images[$key] = $file_path;
			}
		}
		
		public function ImageExists( $key ) {
			if (isset( $this->images[$key] )) return true;
			else return false;
		}
		
		public function StyleExists( $key ) {
			if (isset( $this->stylesheets[$key] )) return true;
			else return false;
		}
		
		public function GetManagableImage( $key ) { // Needs to match the config skin accessor method
			if (isset( $this->images[$key] )) return $this->images[$key];
		}
		
		public function GetStyle( $identifier ) {
			return $this->stylesheets[$identifier];	// TODO check for null value?
		}
		
		public function GetStyles() {
			return $this->stylesheets;
		}
		
		public function GetName() { // TODO legacy for config skins, consider changing OnePanelSkin to bring it in line with $title
			return $this->title;
		}
		
		public function Render() {
			
			if (OnePanel::GetDefaultSkin() == $this) {
				$response  = $this->RenderNoDeactivate();
			}
			else {
				$response  = $this->RenderOnOff();
			}
			
			// Get WordPress' uploads data TODO turn this in to a function
			$wp_uploads_data = wp_upload_dir();
			$upload_directory = $wp_uploads_data['path'];
			
			if (is_writable( $upload_directory )) {
				foreach ($this->images as $name => &$image) {
					
					$response .=	'<div class="skin_title">' . $name . ' &darr;</div>';
					$response .=	'<div class="skin_desc"><strong>Permalink:</strong> <a target="_blank" href="'. $image . '">URL</a></div>'; // TODO
					$response .= 	'<form action="admin-ajax.php" method="post" target="upload_target" enctype="multipart/form-data">';
					$response .=	'<input type="hidden" name="action" value="opcp_SkinModuleDoUpload"/>';
					$response .=	'<div class="skin_content">';
					$response .=		'<div id="upload_preview_' . str_replace( ' ', '_', $name) . '"><img src="'. $image . '" class="popup_upload_preview" /></div>';
					$response .=		'<div class="upload_form" align="center">';
	    			$response .=			'<input type="hidden" name="MAX_FILE_SIZE" value="1000000"/>';
	    			$response .=			'<input type="hidden" name="feature_key" value="' . SkinModule::GetSkinKey( $this->title ) . '"/>';
	    			$response .=			'<input type="hidden" name="image_key" value="' . $name . '"/>';
	    			$response .=			'<label for="image_upload">Image File &rarr;&nbsp;&nbsp;&nbsp;</label>';
	    			$response .=			'<input name="userfile" type="file"/>';
					$response .=		'</div>';
					$response .=	'<div class="buttons" align="center">';
					$response .=		'<input class="upload_file" type="submit" value="&nbsp;"/>';
	 				$response .=		'<input class="use_default_file" type="button" value="&nbsp;" onclick="op_admin.Skins.ResetImage( \'' . SkinModule::GetSkinKey( $this->title ) . '\', \'' . $name . '\' )"/>';
					$response .=	'</div>';
					$response .=	'<div style="clear:both;"></div>';
					$response .=	'</div>';
					
					$response .=	'<div class="DropShadowBlue"></div>';
					// TODO Needs Work First $response .=	'<div style="float:right;"><span class="ie_top"><a class="popup_top" href="#top" onclick="backToTop(); return false">Top &uarr;</a></span></div>';
					$response .=	'</form>';
					$response .=	'<div style="clear:both;height:10px;"></div>';
					
				}
			}
			else {
				// LEE - don't remove the dot ;)
				$response .= '<div class="module_error"><div class="module_error_stroke" style="line-height:22px;"><strong>NOTE: wp-content/uploads is not currently writable...</strong><br />'. OnePanel::GetLicenseeName() .', your uploads directory doesn\'t seem to be writable at the moment. This means that One Panel isn\'t allowed to add files to your server. Please make the "wp-content/uploads" directory writable and return to this page.</div></div>';
				
			}
			
			return $response;
		}
		
		public function RenderNoDeactivate() {
		
			$alternate_key = $this->alternate_key;
			
			$return  = '<div class="PopUp_F">';
			$return .= 		'<div id="popup_' . (is_null( $alternate_key ) ? get_class( $this ) : $alternate_key) . '_container" class="' . (($this->active) ? 'F-active' : 'F-inactive') . '">';
			$return .= 			'<div class="Title">' . $this->title . '</div>';
			$return .=  		'<div id="popup_' . (is_null( $alternate_key ) ? get_class( $this ) : $alternate_key) . '_help" class="Desc" >You cannot deactivate your default skin.</div>';
			$return .=			'<div id="popup_' . (is_null( $alternate_key ) ? get_class( $this ) : $alternate_key) . '_thumb" class="' . (($this->active) ? 'ThumbActive' : 'ThumbInActive') . '"><div class="' . (is_null( $alternate_key ) ? get_class( $this ) : 'Generic_Thumb') . '" id="' . (is_null( $alternate_key ) ? get_class( $this ) : $alternate_key) . '"></div></div>';
			$return .=			'<div id="popup_' . (is_null( $alternate_key ) ? get_class( $this ) : $alternate_key) . '_info" class="Feature' . (($this->active) ? 'Active' : 'InActive') . 'Info"> Feature is ' . (($this->active) ? 'active' : 'inactive') . '.</div>';
			$return .=			'<div id="popup_' . (is_null( $alternate_key ) ? get_class( $this ) : $alternate_key) . '_image" class="' . (($this->active) ? 'Disable' : 'Enable') . '"></div>';
			$return .=		'</div>';
			$return .= '</div>';
			
			return $return;
			
		}
		
	}
	
	
	// Module
	class SkinModule extends OnePanelModule {
		
		protected $title = 'Skins';
		protected $help_text = 'Upload theme dependent images, change the color scheme or enable various skin effecting features.';
		protected $description = 'Personalise the blog theme to suit you by taking advantage of the Skins module. Depending on the theme you currently have active; you can use the Skins module to manage everything from image uploads, to choosing the color scheme of your website.';
		protected $short_description = 'Upload theme dependent images, change the color scheme or enable various skin effecting features.';
		protected $keywords = array( 'skin', 'skins', 'skin switcher', 'switcher', 'switch' );
		protected $categories = array( 'Appearance' );
				
		public function Render() {
			
			// Increase the view count
			$this->IncreaseViewCount();
			
			$skin_features = &$this->GetSkinFeatures();
			$response['content'] = '';
			
			// Print the selector for the skins
			if (count( $skin_features ) > 1) {
				$response['content'] .= '<a id="top"></a><div class="SkinDrop">';
				$response['content'] .= '<div class="SkinDropTitle left_side">';
				$response['content'] .= '<label for="popup_skin_select"><span class="BB">Please select a Skin to edit &rarr;</span></label>';
				$response['content'] .= '</div>';
				$response['content'] .= '<div class="right_side">';
				$response['content'] .= '<select class="select" onchange="op_admin.Skins.SwitchSkin()" id="popup_skin_select">';
			
				foreach ($skin_features as $key => &$feature) {
					$response['content'] .= '<option>' . $feature->GetTitle() . '</option>';
				}
				
				$response['content'] .= '</select>';
				$response['content'] .= '</div>';
				$response['content'] .= '</div>';
				$response['content'] .= '<div class="DropShadowOrange"></div>';
				$response['content'] .= '<div style="clear:both"></div>';
				
				// Print the on and off for the skin switcher
				$response['content'] .= $this->features['SkinFeatureSwitcher']->Render();
				$response['content'] .= $this->features['DefaultSkinFeature']->Render();
			
			}
			else {
				$first_skin = current( $skin_features );
				$response['content'] .= '<input type="hidden" value="' . $first_skin->GetTitle() . '" id="popup_skin_select">';
			}
			
			
			// Print the management for each skin
			// Get the first one
			reset( $skin_features );
			$first_skin = current( $skin_features );
			
			$response['content'] .= '<div id="popup_skin_container">';
			$response['content'] .= $first_skin->Render();
			$response['content'] .= '</div>';
			
			// Fake iframe
			$response['content'] .= '<iframe id="upload_target" name="upload_target" src="#" style="width:0;height:0;border:0px solid #fff;"></iframe>';
			
			$response['title'] = $this->title;
			$response['info'] = $this->help_text;
			
			die( json_encode( $response ) );
			
		}
		
		protected function RegisterFeatures() {
			
			// Simple ones
			$this->RegisterFeature( 'SkinFeatureSwitcher' );
			$this->RegisterFeature( 'DefaultSkinFeature' );
		
			// Less simple ones
			$config_skins 	= &OnePanelConfig::GetSkins();
			$config_default_skin = &OnePanelConfig::GetDefaultSkin();
			
			// Other required modules
			$default_selector_feature = &$this->features['DefaultSkinFeature'];
			
			if (is_array( $config_skins )) { 
				
				foreach ($config_skins as $key => &$config_skin) {
					
					// The config may have changed so run garbage collection routines
					$skin_key = self::GetSkinKey( $config_skin->GetName() );
					$stored_skin = &$this->features[ $skin_key ];
		
					$config_images = $config_skin->GetManagableImages();
					$config_styles = $config_skin->GetStyles();
			
					if (is_null( $stored_skin )) { // There is no feature in the save map so set everything up from the config

						$new_skin = new SkinFeature();
						$new_skin->SetTitle( $config_skin->GetName() );
						$new_skin->Enable();
						$new_skin->SetAsActive();
						$new_skin->SetAlternateKey( $skin_key );
						
						// Add all those lovely images from the config file
						foreach ($config_images as $key => $image) {
							$new_skin->AddImage( $key, $image );
						}
						
						// Same for the styles
						foreach ($config_styles as $key => $style_sheet) {
							$new_skin->AddStyle( $key, $style_sheet );
						}
						
						
						/*
						 * Register the feature
						 * 
						 * We don't use a refrence here as the source object will be replaced 
						 * after this iteration.
						 */
						$this->features[ $skin_key ] = $new_skin;
						
						// We now refrence the permanent object previously created.
						$this->registered_features[] = &$this->features[ $skin_key ];
						
						// Make sure we can decipher the enabled state. 
						$this->enabled_features[] = $skin_key;
						
						
						// Is this the default? If so, notify the DefaultSkinFeature.
						if ($config_default_skin->GetName() == $config_skin->GetName()) {
							$default_selector_feature->SetDefaultSkin( $new_skin );
						}
						
					}
					else {
						
						// Make sure its enabled
						$stored_skin->Enable();
						
						// Garbage collection and enabled state
						$this->registered_features[] = &$stored_skin;
						$this->enabled_features[] = $skin_key;
						
						// Check all the images for garbage - will will have to check on the key and not the value!
						// Need this incase of changes to the config
						$feature_images = $stored_skin->GetImages();
						$config_keys = array_keys( $config_images );
						
						foreach ($feature_images as $feature_key => &$value) {
							if (! in_array( $feature_key, $config_keys )) {
								
								// Remove any images that are in the data but not in the config
								$stored_skin->RemoveImage( $feature_key );
								
							}
						}
						
						// Add new images
						foreach ($config_images as $key => &$image) {
							if (! $stored_skin->ImageExists( $key )) {
								$stored_skin->AddImage( $key, $image );
							}
						}
						
						// Now the same again for the stylesheets
						$feature_styles = $stored_skin->GetStyles();
						$config_style_keys = array_keys( $config_styles );
						
						foreach ($feature_styles as $feature_key => &$value) {
							if (! in_array( $feature_key, $config_style_keys )) {
								
								// Remove any images that are in the data but not in the config
								$stored_skin->RemoveStyle( $feature_key );
								
							}
							else {
								
								// Check to see if the location of the file has changed and revert to config if necessary
								if ($value != $config_styles[$feature_key]) {
									
									$stored_skin->RemoveStyle( $feature_key );
									$stored_skin->AddStyle( $feature_key, $config_styles[$feature_key] );
									
								}
								
							}
						}
						
						// Add new styles
						foreach ($config_styles as $key => &$style) {
							if (! $stored_skin->StyleExists( $key )) {
								$stored_skin->AddStyle( $key, $style );
							}
						}
						
					}
					
					// Set up ajax for it
					add_action( 'wp_ajax_opcp_' . self::GetSkinKey( $config_skin->GetName() ) . 'Activate', array( $this->features[ self::GetSkinKey( $config_skin->GetName() ) ], 'Activate' ) );
					add_action( 'wp_ajax_opcp_' . self::GetSkinKey( $config_skin->GetName() ) . 'Deactivate', array( $this->features[ self::GetSkinKey( $config_skin->GetName() ) ], 'Deactivate' ) );
					
				}
				
			}
			
			/*
			 * Make sure the Default Selector feature has access to the skins
			 * 
			 * We need to do this after we have registered the features, otherwise
			 * we may just be populating the selector with an empty array. 
			 */ 
			$default_selector_feature->SetSkins( $this->GetSkinFeatures() );
			
		}
		
		public function BuildChunks() {
			
			// Get the skin switcher chunks
			$enabled = $this->features['SkinFeatureSwitcher']->IsActive();
			
			if ($enabled == false) {
				return false;
			}
			
			// Get the skins
			$skins = &$this->GetSkinFeatures();
			$active_skin = &OnePanelTheme::GetActiveSkin();
			
			
			// Do the select
			$select = '<select class="select" onchange="OnePanelTheme.SwitchSkin( this.value )">' . "\n";
		

			foreach ($skins as $key => &$skin) {
				if ($skin->IsActive()) {
					$select .= '<option value="' . $skin->GetName() . '">'. $skin->GetName() .'</option>' . "\n";
				}
			}
			
			$select .= '</select>' . "\n";
			
			$this->chunks['Select'] = &$select;
			
			// Do the list
			$list = '<ul>' . "\n";
			
			foreach ($skins as $key => &$skin) {
				if ($skin->IsActive()) {
					$list .= '<li class="' . $skin->GetName() . ' ' . ($active_skin->GetName() == $skin->GetName() ? 'active' : 'inactive') .  '"><a href="javascript:;" onclick="OnePanelTheme.SwitchSkin( \'' . $skin->GetName() . '\' )">' . $skin->GetName() . '</a></li>' . "\n";
				}
			}
			
			$list .= '</ul>' . "\n";
			
			$this->chunks['List'] = &$list;
			
			
		}
		
		
		static public function GetSkinKey( $skin_name ) {
			return 'Skin' . $skin_name . 'Feature';
		}
		
		
		public function GetSkin( $identifier ) {
			
			$skins = $this->GetSkinFeatures();
			$skin_to_return = $skins[ self::GetSkinKey( $identifier ) ];
			if (is_object( $skin_to_return )) return $skin_to_return;
			
		}
		
		public function GetSkinFeatures() {
			
			$return_array = array();
			
			foreach ($this->features as $key => &$feature) {
				
				if ($feature instanceof SkinFeature) {
					$return_array[$key] = &$feature;
				}
				
			}
			
			return $return_array;
			
		}
		
		
		
		// Ajax Stuff
		public function SwitchSkin() {
			
			$skin_name = mysql_real_escape_string( $_POST['skin_name'] );
			$skin = &$this->features[ self::GetSkinKey( $skin_name ) ];
			
			if (is_object( $skin )) {
				die( $skin->Render() );
			}
			else {
				die( 'No Skin by the name' . $skin_name );
			}
			
		}
		
		public function SwitchDefault() {
			
			/*
			 * TODO 
			 * 
			 * The response stuff can probably be removed when we change the way this module renders
			 * to a more pagniated layout. 
			 *  
			 */
			$viewing_skin_name = 	mysql_real_escape_string( $_POST['viewing_skin_name'] );
			$viewing_skin_key =		self::GetSkinKey( $viewing_skin_name ); 
			$viewing_skin = 		&$this->features[ $viewing_skin_key ];
			
			$skin_name = 			mysql_real_escape_string( $_POST['skin_name'] );
			$skin = 				&$this->features[ self::GetSkinKey( $skin_name ) ];
			
			if (is_object( $skin )) {
				
				$this->features['DefaultSkinFeature']->SetDefaultSkin($skin);
				OnePanel::PackData();
				
				$response = array();
				$response['module'] = self::GetSkinKey( $skin_name );
				$response['viewing_module'] = $viewing_skin_key;
				$response['container_class'] = 'F-active';
				$response['thumb_class'] = 'ThumbActive';
				$response['info_class'] = 'FeatureActiveInfo';
				$response['info_content'] = 'Feature is active.'; // TODO make a generic function for generating this responses it shouldnt be in three places
				$response['help_text'] = 'You cannot deactivate your default skin.';
				$response['button_text'] = '<a href="javascript:;" onclick="op_admin.AjaxOnOff(\'opcp_' . $viewing_skin_key . 'Deactivate\')"><img src="' . get_option('home') . '/wp-content/plugins/one-panel/images/default/pop_content/disable.gif" border="0" /></a>';
				
				$response = json_encode( $response );
				die($response);
				
			}
			else {
				die( 'No Skin by the name' . $skin_name );
			}
			
		}
		
		public function DoUpload() {

			// Get WordPress' uploads data
			$wp_uploads_data = wp_upload_dir();
			$upload_directory = $wp_uploads_data['path'];
			
			if (is_writable( $upload_directory )) {

				if (! empty( $_POST )) {
		    
				    $file = $_FILES['userfile'];
				    $feature_key = $_POST['feature_key'];
				    $image_key = $_POST['image_key'];
				    
				    $upload_target = $upload_directory . '/' . basename($file['name']);
				    $actual_src = $wp_uploads_data['url'] . '/' . basename($file['name']);
				    
				    // Check how the upload went
				    switch ($file['error']) {
					    case UPLOAD_ERR_FORM_SIZE:
					    	$error = 'The file you posted is too big!';
					    break;
					    
					    case UPLOAD_ERR_INI_SIZE:
					    	$error = 'The file you posted is too big!';
					    break;
					    
					    case UPLOAD_ERR_NO_FILE:
					    	$error = 'You did not upload a file';
					    break;
				    }
				    
				    // Try to move it to the correct dir
				    if (move_uploaded_file( $file['tmp_name'] , $upload_target )) {
				    	
				    	$feature = &$this->features[ $feature_key ];
				    	
				    	if (is_object( $feature )) {
				    		$feature->UpdateImage( $image_key, $actual_src );
				    		OnePanel::PackData();
				    	}
				    	else {
				    		$error = 'There is no skin with that name to update.';
				    	}
				    	
				    }
				    
				    /*
				     * Prepare the response
				     * 
				     * ['status'] bool
				     * ['preview_id'] str (for the javascript to update the preview)
				     * 
				     * ['error'] optional str
				     * ['new_image'] optional str
				     * 
				     */
				    if (isset( $error )) { 
				    	
				    	$response['status'] = false;
				    	$response['error'] = $error;
				    	
				    }
				    else {
				    	
				    	$response['status'] = true;
				    	$response['new_image'] = $actual_src;
				    	$response['preview_id'] = 'upload_preview_' . str_replace( ' ', '_', $image_key);
				    	
				    }
				    
				    // Send the response back to the taget window
				    echo '<script language="javascript" type="text/javascript">';
					echo 	'window.top.window.op_admin.Skins.Update(' . json_encode( $response ) . ');';
					echo '</script>';
				    die;
			    }
				
			}
			
		}
		
		public function ResetImage() {
			
			$feature_key = $_POST['feature_key'];
			$image_key = $_POST['image_key'];
			$skin_name = $_POST['skin_name'];
			
			$feature = $this->features[$feature_key];
				    	
	    	if (is_object( $feature )) {
	    		
	    		$config_skins 	= &OnePanelConfig::GetSkins();
	    		$config_images = $config_skins[$skin_name]->GetManagableImages();
	    		
	    		$default = $config_images[$image_key];
	    		
	    		$feature->UpdateImage( $image_key, $default );
	    		OnePanel::PackData();
	    		
	    		$response['new_image'] = $default;
				$response['preview_id'] = 'upload_preview_' . str_replace( ' ', '_', $image_key);
	    	}
	    	else {
	    		die( 'Nothing by the name ' . $feature_key );
	    	}
	    	
	    	// TODO what if it fails?
			die( json_encode( $response ) );
		}
		
	}
