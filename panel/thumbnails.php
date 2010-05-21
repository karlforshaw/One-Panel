<?php

	class ThumbnailsToggle extends OnePanelFeature {
		
		protected $title = 'Post Thumbnails';
		protected $help_text = 'Add Thumbnail images to your blog posts by activating the Thumbnails feature.';
		
		
		public function Render() {
			// Generic Activation Render
			return $this->RenderOnOff();
		}
		
	}
	
	class ThumbnailGenerator extends OnePanelFeature {
		
		protected $title = 'Turbo Thumbnail Generator';
		protected $help_text = 'A quick and easy tool for generating thumbnails on your website.';
		
		public function Render() {
			
			if (! defined( 'GD_VERSION' )) {
				$response = '<div style="height:10px;clear:both;"></div><div class="popup_no_results"><div class="module_error_stroke">Sorry, your server is incapable of resizing images. Please ask your host to install the GD library on your server.</div></div>';
			}
			else {
				
				$response  = '<div id="popup_thumbnails_manager_container">';
				$response .= 	'<div id="popup_thumbnails_post_status"></div>';
				$response .= 	'<div class="labelfloat">Search for a post to add Thumbnails &darr;</div>';
				$response .=	'<div style="clear:both;"></div>';
				$response .= 	'<div style="width:650px;padding:0;">';
				$response .= 		'<div class="ThumbSearch">';
				$response .= 			'<input maxlength="65" name="popup_thumbnails_post_entry" id="popup_thumbnails_post_entry" onpaste="op_admin.Thumbnails.SearchPosts()" onkeydown="op_admin.Thumbnails.SearchPosts()" size="45" value="Enter a few keywords..." onfocus="if(this.value==\'Enter a few keywords...\'){this.value=\'\'}" onblur="if(this.value==\'\'){this.value=\'Enter a few keywords...\'}"/>';
				$response .= 		'</div>';
				$response .= 	'</div>';
				$response .= 		'<div style="clear:both;"></div>';
				$response .= 	'<div id="popup_thumbnails_search_results"></div>';
				$response .= '</div>';
				
			}
			
			return $response;
			
		}
		
		public function RenderThumbnailManager( $post_id ) {
			
				
			$post = get_post( $post_id );
			
			if ( is_object( $post )) {
				
				$response = '<div class="ThumbMana"><strong>Managing Thumbnails for:&nbsp;&nbsp; <span style="color:#d44d4d;">'. $post->post_title .'</span></strong></div>';
				
				// Print a drop down with each thumbnail type in it
				$thumbnail_types = OnePanelConfig::GetThumbnailTypes();
				if ($thumbnail_types) {
					
					$response .= '<form action="admin-ajax.php" method="post" target="upload_target" enctype="multipart/form-data">';
					$response .= '<div class="ThumbDrop">';
					$response .= 	'<div class="overwrite">';
					$response .= 		'<label for="overwrite">Would you like to overwrite existing Thumbnail(s)? </label>';
					$response .= 		'<input type="checkbox" id="overwrite" name="overwrite" checked="checked" />';
					$response .= 	'</div>';
					$response .= 	'<select onchange="op_admin.Thumbnails.SwitchThumbToGen( this.value )">';
					
					if (count($thumbnail_types) > 0) {
						$response .= 	'<option value="All">All Thumbnail Types</option>';
					}
					
					$response .= 	'<option value="Thumbnail">Thumbnail</option>';
					
					foreach ( $thumbnail_types as $key => &$thumbnail_type ) {
						$response .= '<option value="' . $thumbnail_type->GetCustomField() . '">' . $thumbnail_type->GetIdentifier() . '</option>';
					}
					
					$response .= '</select>';
					$response .= '</div>';
				}
				
				/*
				 * Print the different options for creating a thumbnail
				 * 
				 */

				$response .= '<div style="height:10px;clear:both;"></div>';
				$response .= '<div style="font-weight:bold;padding-top:5px">Please choose between the three thumbnail options below &darr;</div>';
				
				$response .= '<div style="height:15px;"></div>';
				
				$response .= '<div class="ThumbTitle">Option 1. Upload an Image</div>';
				$response .= '<div class="ThumbContainer">';
				$response .= 	'<input type="hidden" name="action" value="opcp_ThumbnailsDoUpload"/>';
				$response .= 	'<input type="hidden" name="MAX_FILE_SIZE" value="1000000"/>';
				$response .= 	'<input type="hidden" name="post_id" value="' . $post_id . '"/>';
				$response .= 	'<input type="hidden" name="thumb_to_generate" id="thumb_to_generate" value="All" />';
				$response .= 	'<label>Image File <span style="font-weight:normal;">&rarr;</span>&nbsp;&nbsp;</label>';
				$response .= 	'<input name="userfile" id="userfile" type="file" />';
				$response .=	'<input type="submit" value="Upload Image" />';
				$response .= 	'</form>';
				$response .= 	'<iframe id="upload_target" name="upload_target" src="#" style="width:0;height:0;border:0;"></iframe>';
				$response .= '</div>';
				
				$response .= '<div style="height:15px;"></div>';
				
				$response .= '<div class="ThumbTitle">Option 2. Scan Post for Images</div>';
				$response .= '<div class="ThumbContainer">';
				$response .= 'Scan the post for images to be used as thumbnails: <div class="ThumbSubmit"><a href="javascript:;" onclick="op_admin.Thumbnails.ScanPost(' . $post_id . ')">Scan Post</a></div>';
				$response .= '<div id="thumbnails-scan-results"></div>';
				$response .= '<div id="thumbnails-scan-preview"></div>';
				$response .= '</div>';
				
				$response .= '<div style="height:15px;"></div>';
				
				$response .= '<div class="ThumbTitle">Option 3. Grab Image from URL</div>';
				$response .= '<div class="ThumbContainer" style="margin:0;">';
				$response .= '<label>Image URL <span style="font-weight:normal;">&rarr;</span>&nbsp;&nbsp</label>';
				$response .= '<input type="text" id="thumbnail-image-from-url" />';
				$response .= '<input type="button" value="Use Image"  onclick="op_admin.Thumbnails.RipImage( $F(\'thumbnail-image-from-url\'), \'' . $post_id . '\' )" />';
				$response .= '</div>';
				
			}
				
			return( $response );
			
		}
		
	}

	class Thumbnails extends OnePanelModule {
		
		protected $title = 'Thumbnails';
		protected $help_text = 'Add Thumbnail images to your blog posts by activating the Thumbnails feature.';
		protected $description = 'Fancy a smart looking image next to each post? With Thumbnails you can turn a standard looking site into eye candy. Take advantage of One Panel\'s quick and easy to use Thumbnail features to achieve just that!';
		protected $short_description = 'Add Thumbnail images to your blog posts by activating the Thumbnails feature.';
		protected $keywords = array( 'thumbnail', 'thumbnails', 'post thumbnails', 'thumbs', 'pictures', 'picture', 'images', 'image' );
		protected $categories = array( 'Appearance' );
		
		
		public function Render() {
			
			// First things first.
			$this->IncreaseViewCount();
			
			// Now we need the container with the tabs general options and thumbnail gen
			$response['content']  = '<div class="ThumbTabs" style="width:670px;">';
			$response['content'] .= '<div style="clear:both;"><div id="thumbnails-general-tab" class="TabActive"><a href="javascript:;" onclick="op_admin.Thumbnails.SwitchMode(\'general\')">General Options</a></div><div id="thumbnails-tool-tab" class="TabInActive"><a href="javascript:;" onclick="op_admin.Thumbnails.SwitchMode(\'tool\')">Thumbnail Generator</a></div></div>';
			$response['content'] .= '<div>';
			$response['content'] .= '<div class="TabText">Please select a tab &rarr;</div>';
			$response['content'] .= '<div id="thumbnail-container">';
			$response['content'] .= $this->features['ThumbnailsToggle']->Render(); 
			$response['content'] .= '</div>';
			$response['content'] .= '</div>';
			$response['content'] .= '</div>';
			$response['content'] = utf8_encode( $response['content'] );
			
			$response['title'] = 'Thumbnails';
			die( json_encode( $response ) );
			
		}
		
		public function RegisterFeatures() {
			$this->RegisterFeature( 'ThumbnailsToggle' );
			$this->RegisterFeature( 'ThumbnailGenerator' );
		}
		
		public function BuildChunks() {
			return true;
		}
		
		public function SwitchMode() {
			
			$mode = $_POST['mode'];
			
			switch ($mode) {
				case 'general':
					$response = $this->features['ThumbnailsToggle']->Render();
				break;
				
				case 'tool':
					$response = $this->features['ThumbnailGenerator']->Render();
				break;
			}
			
			die($response);
			
		}
		
		public function SearchPosts() {
			
			if (empty( $_POST['search_term'] )) {
		    	$output = '<div class="popup_no_results"><div class="module_error_stroke">Please enter a search term.</div></div>';
		    	die($output);
		    }
		    
		    global $wpdb;
		    
		    $sql = "SELECT ID, post_title FROM " . DB_NAME . ".{$wpdb->prefix}posts 
		    WHERE post_content LIKE '%{$_POST['search_term']}%' AND post_status = 'publish' AND post_type='post' 
		    OR post_title LIKE '%{$_POST['search_term']}%' AND post_status = 'publish' AND post_type='post' ";
		
		    $result = mysql_query( $sql );
		    
		    if ((! $result) || (mysql_numrows($result) == 0 )) {
		   		$output = '<div class="popup_no_results"><div class="module_error_stroke">Sorry, your search didn\'t return any results.</div></div>'; 
		    	die($output);
		    }
		    
		    $rows = array();
		    while ($row = mysql_fetch_assoc($result)) {
		    	$rows[] = $row;
		    }
		    
		    foreach ($rows as $row) {
				echo '<div style="clear:both;height:5px;"></div>';
				echo '<div class="input_option">';
				echo '<div class="radio_option">';
		    	echo '<input type="radio" onclick="op_admin.Thumbnails.SetPost(' . $row['ID'] . ')" name="popup_thumbnails_post_radio" id="popup_thumbnails_post_radio" value="' . $row['ID'] . '"></div>';
				echo '<div class="radio_content">' . $row['post_title'] . '</div>';
				echo '</div>';
		    }
		    
		    die;
		}
		
		public function ScanPost() {
			
			// Whats going to be sent back?
			$response['content'] = '';
			
			if (isset( $_POST['post_id'] )) {
				
				$post = get_post( $_POST['post_id'] );
				
				$post_images = array();
				$pattern = "/\< *[img][^\>]*[src] *= *[\"\']{0,1}([^\"\'\ >]*)/i";
				preg_match_all( $pattern, $post->post_content, $post_images );
				
				$post_images = $post_images[1];
				
				if (count($post_images) > 0) {
					foreach ($post_images as &$image_src) {
						
						$exploded = explode( '/', $image_src );
						$file_name = end($exploded);

						$response['content'] .= '<div style="padding-top:8px;">' . "\n";
						$response['content'] .= 	'<a href="javascript:;" onclick="op_admin.Thumbnails.ImagePreview( \''. $image_src . '\' )">' . $file_name . '</a>' . "\n";
						$response['content'] .= 	'<div class="ThumbSubmit"><a href="javascript:;" onclick="op_admin.Thumbnails.RipImage( \''. $image_src . '\', \'' . $post->ID . '\' )">Use This Image</a></div>' . "\n";
						$response['content'] .= '</div>' . "\n";
						
					}
				}
				else {
					$response['content'] .= '<div class="popup_no_results"><div class="module_error_stroke">The post does not contain any images.</div></div>';
				}
			}
			else {
				$response['content'] .= '<div class="popup_no_results"><div class="module_error_stroke">One Panel Error: Invalid POST..</div></div>';				
			}
			
			die( $response['content'] );
			
		}
		
		public function SetPost() {
				
			if (isset( $_POST['post_id'] )) {
				die( $this->features['ThumbnailGenerator']->RenderThumbnailManager( $_POST['post_id'] ) );
			}
			
		}
		
		public function RipImage() {
			
			// Initiate response
			$response['content'] = '';
			
			if ((isset( $_POST['url'] )) && (isset( $_POST['post_id'] )) && (isset( $_POST['thumb_to_generate'] )) && (isset( $_POST['overwrite_existing'] ))) {
				
				/*
				 * Set some variables
				 * 
				 */
				$file_name = basename( $_POST['url'] );
				
				$wp_uploads_data = wp_upload_dir();
				$upload_directory = $wp_uploads_data['path'];
				$destination = $upload_directory . '/' . $file_name;
				
				$post_id = (int) $_POST['post_id'];
				$thumb_to_generate = $_POST['thumb_to_generate'];
				
				if ($_POST['overwrite_existing'] == 'true') 		$overwrite = true;
				elseif ($_POST['overwrite_existing'] == 'false') 	$overwrite = false;
				
				/*
				 * Try and copy the image to our server
				 * 
				 */
				if(! @copy( $_POST['url'], $destination )) {
				
				    $response['content'] .= '<div class="popup_no_results"><div class="module_error_stroke">Sorry, that image could not be copied.</div></div>';
				    
				    /*
				     * Debug Only
				     *
				    $errors= error_get_last();
				    $response['content'] .= 'One Panel Error: ' .$errors['type'];
				    $response['content'] .= '<br />' . "\n" .$errors['message'];
					*/
				    
				} 
				else {
				    
					/*
					 * Use the copied image location to generate the thumbnails
					 */
					$error = @$this->CreateThumbs( $destination, $post_id, $thumb_to_generate, $overwrite );
					
					if (is_string( $error )) {
						$response['content'] .= $error;
					}
					else {
						$response['content'] .= '<div>All done! <div class="ThumbSubmit"><a href="javascript:;" onclick="op_admin.Thumbnails.SwitchMode(\'tool\')">Start Over</a></div></div>';
					}
					
				}
				
			}
			else {
				$response['content'] .= '<div class="popup_no_results"><div class="module_error_stroke">One Panel Error: Invalid POST..</div></div>';
			}
			
			die( $response['content'] );
			
		}
		
		
		/**
		 * Create Thumbnails
		 *
		 * @param str $source_file
		 * @param int $post_id
		 * @param str $thumb_to_generate
		 * @param bool $overwrite_existing
		 * @return Error string on error, TRUE on success
		 * @todo this function is ridiculous, chop it up
		 */
		public function CreateThumbs( $source_file, $post_id, $thumb_to_generate, $overwrite_existing=false ) {
			
			// Debug
			$success = OnePanelDebug::Track( 'Creating thumbnails: ' . $thumb_to_generate );
			
			// Get WordPress' uploads data
			$wp_uploads_data = wp_upload_dir();
			$upload_directory = $wp_uploads_data['path'];
			
			if (is_writable( $upload_directory )) {
	    
				OnePanelDebug::Info( 'Upload dir is writable.' );
				
		    	// Figure out how many thumbs we are generating
		    	$actual_thumbs_to_gen = array();
		    	$config_thumbs = OnePanelConfig::GetThumbnailTypes();
		    	if ($config_thumbs == false) OnePanelDebug::Info( 'No additional thumbnail types passed from config.' );
		    	
		    	// Set up catch all.
		    	if ($thumb_to_generate == 'All') {
		    		
		    		$actual_thumbs_to_gen[] = 'Thumbnail';
		    	
		    		foreach ( $config_thumbs as $key => &$thumbnail_type ) {
						$actual_thumbs_to_gen[] = $thumbnail_type->GetCustomField();
					}
		    		
		    	}
		    	else { // Just the passed thumbnail type.
		    		// TODO check that the passed ttg is in the config
		    		$actual_thumbs_to_gen[] = $thumb_to_generate;
		    	}
		    	
		    	
		    	// Create the thumbs we need.
		    	foreach ( $actual_thumbs_to_gen as &$custom_field_name ) {
		    		
		    		// Debug
		    		OnePanelDebug::Info(  'Attempting to build thumbnail for ' . $custom_field_name );
		    		
		    		// Check for an existing thumb
		    		$existing = get_post_meta( $post_id, $custom_field_name );
		    		if(empty( $existing )) $existing = false;
		    		else OnePanelDebug::Info( 'Thumb already exists ' . ($overwrite_existing ? 'attempting overwrite' : 'skipping') );
		    		
		    		// Dont do anything if overwrite is off and theres an existing thumb
					if (($existing != false) && ($overwrite_existing == false)) continue;
		    		
					// Dims are set differently for the standard thumbnails
		    		if ($custom_field_name == 'Thumbnail') {
		    			
		    			$default_thumbnail_dims = OnePanelConfig::GetThumbnailSize();
		    			
		    			$width = $default_thumbnail_dims['Width'];
		    			$height = $default_thumbnail_dims['Height'];
		    		}
		    		
		    		// Get the dims for this thumbnail type and try and resize it
		    		foreach ($config_thumbs as $key => &$config_thumb) {
		    			
		    			if ( $config_thumb->GetCustomField() == $custom_field_name) {
		    				
		    				$width = $config_thumb->GetWidth();
		    				$height = $config_thumb->GetHeight();
		    				
		    			}
		    			
		    		}
		    		
		    		// Can we create the resized image?
		    		OnePanelDebug::Info( 'Attempting to create thumbnail ' . $source_file . ' ' . $width . 'x' . $height );
		    		
		    		$new_thumbnail_path = image_resize( $source_file, $width, $height, true  ); // TODO this really shouldnt be here, if this is a html returning function
		    		if ((is_wp_error( $new_thumbnail_path )) || ($new_thumbnail_path == false)) {
		    			
		    			if (is_wp_error( $new_thumbnail_path )) OnePanelDebug::Error( $new_thumbnail_path->get_error_message() );
		    			OnePanelDebug::Error( 'Unable to create thumbnail, moving to next iteration.' );
		    			$error = '<div class="popup_no_results"><div class="module_error_stroke">One Panel could not resize the image for ' . $custom_field_name . '. <a href="javascript:;" onclick="op_admin.Thumbnails.SwitchMode(\'tool\')">Please try another.</a></div></div>';
		    			continue;
		    			
		    		}
		    		else OnePanelDebug::Info( 'Thumbnail created successfully with path ' . $new_thumbnail_path );
		    				
    				// Get the url for the one we just created
    				$new_thumbnail_url = str_replace( ABSPATH , get_option( 'siteurl' ) . '/', $new_thumbnail_path );
    				
    				
    				// Add the custom field to the post
    				if (($existing) && ($overwrite_existing == true)) {
    					delete_post_meta( $post_id, $custom_field_name );
    				}
    				
    				add_post_meta( $post_id, $custom_field_name, $new_thumbnail_url );
    				OnePanelDebug::Info( 'Custom field added with' . $new_thumbnail_url );
			    	
			    }
			    
			    // Prepare the return value
			    if (isset( $error )) {
			    	$return = $error;
			    }
			    else {
			    	$return = true;
			    }
				
			}
			else { // Upload path is not writable
				$return = '<div class="popup_no_results"><div class="module_error_stroke">The image path is not currently writable. Please chmod the directory first.</div></div>';
			}
			
			$success->Affirm();
			return( $return );
			
		}
		
		
		
		
		public function DoUpload() {
			
			// Get WordPress' uploads data
			$wp_uploads_data = wp_upload_dir();
			$upload_directory = $wp_uploads_data['path'];
			$upload_url = $wp_uploads_data['url'];
			
			if (is_writable( $upload_directory )) {

				if ((isset( $_POST['post_id'] )) && (isset( $_POST['thumb_to_generate'] ))) {
		    
					/*
					 * Set up the variables for this
					 * 
					 */
				    $file = $_FILES['userfile'];
				    
				    $post_id = (int) $_POST['post_id']; 
				    $thumb_to_generate = $_POST['thumb_to_generate'];
				    
				    if (isset( $_POST['overwrite'] )) $overwrite_existing = true;
				    else $overwrite_existing = false;
				    
				    $upload_target = $upload_directory . '/' . basename( $file['name'] );
				    
				    
				    /*
				     * Make sure the upload went ok
				     * 
				     */
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
				    
				    
				    
				    /*
				     * See if we can move the file to its target location
				     * 
				     */
				    if (! isset($error)) {
					    if (move_uploaded_file( $file['tmp_name'], $upload_target )) {
					    	
					    	$thumbnail_error = $this->CreateThumbs( $upload_target, $post_id, $thumb_to_generate, $overwrite_existing );
					    	if (is_string( $thumbnail_error )) $error = $thumbnail_error;
					    	
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
				    	
				    }
				    
				    // Send the response back to the taget window
				    echo '<script language="javascript" type="text/javascript">';
					if ( $response['status'] == true ) 
						echo 'window.top.window.op_admin.Thumbnails.Done();';
					else 
						echo 'window.top.window.op_admin.Thumbnails.HadError(\'' . $response['error'] . '\');';
					echo '</script>';
				    die;
			    }
				
			}
			
		}
		
	}