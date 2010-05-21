<?php

	class HighlightFeature extends OnePanelFeature {

		/**
		 * Data Source Type
		 * 
		 * Determines the nature of the the highlight and how to obtain its data.
		 *
		 * (int) 2 		Featured Post
		 * (int) 3		Featured Category
		 * (int) 4		TODO Featured Page
		 * (int) 5		TODO Featured Author
		 * (int) 6 		TODO Featured Link
		 * 
		 * @var int
		 */
		private $source_type = null;
		private $source_id = null; // Used for Featured Posts and Categories
		
		protected $detail_chunks = null;
		
		private $content_limit = null;
		private $title_limit = null;
		protected $help_text = 'Use the Highlights module to feature your posts or categories.';

		/*
		 * TODO move all that messy shit from RegisterFeatures into here
		 * and do all that crap in the constructor if we can
		 */
		private $config_module = null;
		
		
		
		public function SetSourceType( $source_type ) {
			
			if (is_int( $source_type )) {
				$this->source_type = $source_type;
			}
			
		}
		
		
		public function SetSourceID( $source_id ) {
			
			if (is_int( $source_id )) {
				$this->source_id = $source_id;
			}
			
		}
		
		
		public function SetContentLimit( $limit ) {
			
			if (is_int( $limit )) {
				$this->content_limit = $limit;
			}
			
		}
		
		public function SetTitleLimit( $limit ) {
			
			if (is_int( $limit )) {
				$this->title_limit = $limit;
			}
			
		}
		
		public function SetConfigModule( OnePanelHighlight $config_module ) {
			$this->config_module = &$config_module;
		}
		
		
		
		public function GetChunk() {
			
			switch ($this->source_type) {
				
				case 2: // Feature a Post
					return $this->GetChunkFromPost();
				break;
				
				case 3: // Feature a Category
					return $this->GetChunkFromCategory();
				break;
				
				default:
					return 'Unknown Highlight Source:' .$this->source_type  . '.';
				break;
				
			}
			
		}
		
		public function GetDetailedChunk( $chunk_name ) { // TODO sort this out, the parent has different ideas about these methods
			if (isset($this->detail_chunks[$chunk_name])) return $this->detail_chunks[$chunk_name];
		}
		
		
		private function GetChunkFromPost() {

			// TODO Fix the default behavior
			if (is_null( $this->source_id )) {
				$query = 'showposts=1';
			}
			else {
				$query = 'p=' . $this->source_id;
			}
			
			$recent = new WP_Query($query);
			
			// Build the chunks
			while($recent->have_posts()) {
			
				$recent->the_post(); // Sets the global $post object to our query object so that the functions can work
				global $post;
				
				// Check for behavioural modifications on the thumbnail image.
				$default_thumb = get_post_meta($post->ID, "Thumbnail", true);
				$behavioural_params = OnePanelConfig::GetBehaviorAlteration( $this->config_module, 'UseAlternateThumbnail' );
				
				if (! $behavioural_params) {
					$thumbnail = $default_thumb;
				}
				else {
					
					$alternate_thumb_object = &$behavioural_params[1];
					$custom_field = $alternate_thumb_object->GetCustomField();
					
					$thumbnail = get_post_meta($post->ID, $custom_field, true);
					
					if (empty($thumbnail)) {
						$thumbnail = get_post_meta($post->ID, 'Thumbnail', true);
					}
					
				}
				
				// TODO remove any whitespace from the title or the classes
				$this->detail_chunks['Image']  = '<div class="' . $this->title . '-image">';
				$this->detail_chunks['Image'] .= 	'<a href="' . apply_filters('the_permalink', get_permalink()) . '">';
				$this->detail_chunks['Image'] .= 		'<img alt="' . get_the_title() . '" src="' . $thumbnail . '" />'; // TODO allow for behavioural modification
				$this->detail_chunks['Image'] .= 	'</a>'; 
				$this->detail_chunks['Image'] .= '</div>';
				
				$this->detail_chunks['Title']  = '<div class="title"><a href="' . apply_filters('the_permalink', get_permalink()) . '">' . (((! is_null( $this->title_limit)) && ( strlen( get_the_title() ) > $this->title_limit  )) ? substr(get_the_title(), 0, $this->title_limit) . '...' : get_the_title()) . '</a></div>';
				
				$this->detail_chunks['AuthorDetails'] = '<div class="' . $this->title . '-author-details">By ' . OnePanelLanguage::GetAuthorProfileLink() . ' on ' . get_the_time(OnePanelLanguage::GetText( 'date_format' )) . '</div>';
				
				// Number of comments
				global $id;
				$comments_number = get_comments_number($id);
			
				if ( $comments_number > 1 )			$comments_output = str_replace('%', $comments_number, __('% Comments'));
				elseif ( $comments_number == 0 ) 	$comments_output = OnePanelLanguage::GetText( 'no_comments' );
				else 								$comments_output = OnePanelLanguage::GetText( '1_comments' );
			
				$this->detail_chunks['NumberOfComments'] = '<div class="' . $this->title . '-comments-number"><a href="#comment-top">' . apply_filters('comments_number', $comments_output, $comments_number) . '</a></div>' . "\n";

				// Content
				$content = get_the_content( OnePanelLanguage::GetText( 'read_more' ) );
				$content = apply_filters('the_content', $content);
				$content = str_replace(']]>', ']]&gt;', $content);
				
				// Check to see if the content limit is behaviourally modified
				$behavioural_params = OnePanelConfig::GetBehaviorAlteration( $this->config_module, 'LimitContent' );
				
				if ($behavioural_params) {
					if (is_int( $behavioural_params[1] )) {
						$content_limit = $behavioural_params[1];
					}
				}
				else {
					$content_limit = $this->content_limit;
				}
				
				if (! is_null( $content_limit )) {
					$content = strip_tags( $content );
					$content = substr( $content, 0, $content_limit ) . '...';
					$content .= ' <div class="read-more"><a href="' . apply_filters('the_permalink', get_permalink()) . '">' . OnePanelLanguage::GetText( 'read_more' ) . '</a></div>';
				}
				
				$this->detail_chunks['Content'] = &$content;
				
			}
			
			return $this->detail_chunks['Image'] . $this->detail_chunks['Title'] . $this->detail_chunks['AuthorDetails'] . $this->detail_chunks['Content'];
			
		}
		
		
		private function GetChunkFromCategory() {

			if (is_null( $this->source_id )) {
				$this->source_id = 1;
			}
			
			// Check for ShowNumberOfPosts behaviour if the requirements are met
			$default_query = 'cat=' . $this->source_id . '&showposts=1';
			$behavioural_params = OnePanelConfig::GetBehaviorAlteration( $this->config_module, 'ShowNumberOfPosts' );	
			
			if (! $behavioural_params) {
				$query = $default_query;
			}
			else {
				$query = 'cat=' . $this->source_id . '&showposts=' . $behavioural_params[1];
			}
								
			
			$recent = new WP_Query( $query );
			global $post;
			
			$i = 1;
			
			while($recent->have_posts()) {
			
				$recent->the_post();
				
				// Declare Variables
				if (! isset( $this->detail_chunks[ 'Title' ] )) $this->detail_chunks[ 'Title' ];
				if (! isset( $this->detail_chunks[ 'Image' . (($i > 1) ? $i: '') ] )) $this->detail_chunks[ 'Image' . (($i > 1) ? $i: '') ] = '';
				if (! isset( $this->detail_chunks[ 'SubTitle'. (($i > 1) ? $i: '') ] )) $this->detail_chunks[ 'SubTitle'. (($i > 1) ? $i: '') ] = '';
				if (! isset( $this->detail_chunks[ 'Content'. (($i > 1) ? $i: '') ] )) $this->detail_chunks[ 'Content'. (($i > 1) ? $i: '') ] = '';
				if (! isset( $this->detail_chunks[ 'NumberOfComments'. (($i > 1) ? $i: '') ] )) $this->detail_chunks[ 'NumberOfComments'. (($i > 1) ? $i: '') ] = '';
				
				// Set up pointers
				$title_chunk = 		&$this->detail_chunks[ 'Title' ];
				$image_chunk = 		&$this->detail_chunks[ 'Image' . (($i > 1) ? $i: '') ];
				$subtitle_chunk = 	&$this->detail_chunks[ 'SubTitle'. (($i > 1) ? $i: '') ];
				$content_chunk = 	&$this->detail_chunks[ 'Content'. (($i > 1) ? $i: '') ];
				$comments_chunk = 	&$this->detail_chunks[ 'NumberOfComments'. (($i > 1) ? $i: '') ];
				
				// Do the title (Category Name)
				if ($i == 1) {
					$title_chunk  = '<div class="' . $this->title . '-title">';
					$title_chunk .= 	'<a href="' . get_category_link( $this->source_id ) . '" title="' . get_cat_name( $this->source_id ). '">';
					$title_chunk .= 		(((! is_null( $this->title_limit)) && (strlen( get_cat_name( $this->source_id ) > $this->title_limit ))) ? substr(get_cat_name( $this->source_id ), 0, $this->title_limit) . '...' : get_cat_name( $this->source_id ));
					$title_chunk .= 	'</a>';
					$title_chunk .= '</div>';
				}
				
				// Comments, ripped off a load of wordpress code for this
				global $id;
				$comments_number = get_comments_number($id);
			
				if ( $comments_number > 1 )			$comments_output = str_replace('%', $comments_number, __('% Comments'));
				elseif ( $comments_number == 0 ) 	$comments_output = OnePanelLanguage::GetText( 'no_comments' );
				else 								$comments_output = OnePanelLanguage::GetText( '1_comments' );
			
				$comments_chunk = '<div class="' . $this->title . '-comments-number">' . apply_filters('comments_number', $comments_output, $comments_number) . '</div>' . "\n";
				
				
				// Check to see if we are supposed to use an alternate thumb
				$default_thumb = get_post_meta($post->ID, 'Thumbnail', true);
				$behavioural_params = OnePanelConfig::GetBehaviorAlteration( $this->config_module, 'UseAlternateThumbnail' );
				
				if (! $behavioural_params) {
					$thumbnail = $default_thumb;
				}
				else {
					
					$alternate_thumb_object = &$behavioural_params[1];
					$custom_field = $alternate_thumb_object->GetCustomField();

					$thumbnail = get_post_meta($post->ID, $custom_field, true);
					
					if (empty($thumbnail)) {
						$thumbnail = get_post_meta($post->ID, 'Thumbnail', true);
					}
				}
				
				$image_chunk  = '<div class="' . $this->title . '-image">';
				$image_chunk .= 	'<a href="' . apply_filters('the_permalink', get_permalink()) . '">';
				$image_chunk .= 		'<img alt="' . get_the_title() . '" src="' . $thumbnail . '" />'; // TODO check for behaviour modification
				$image_chunk .= 	'</a>';
				$image_chunk .= '</div>';
				
				$subtitle_chunk  = '<div class="' . $this->title . '-subtitle">';
				$subtitle_chunk .= '<a href="' . apply_filters('the_permalink', get_permalink()) . '">';
				$subtitle_chunk .= (((! is_null( $this->title_limit)) && (strlen( get_the_title() ) > $this->title_limit)) ? substr(get_the_title(), 0, $this->title_limit) . '...' : get_the_title());
				$subtitle_chunk .= '</a>';
				$subtitle_chunk .= '</div>';
				
				$content = get_the_content( OnePanelLanguage::GetText( 'read_more' ) );
				$content = apply_filters('the_content', $content);
				$content = str_replace(']]>', ']]&gt;', $content);
				
				// Check to see if the content limit is behaviourally modified
				$behavioural_params = OnePanelConfig::GetBehaviorAlteration( $this->config_module, 'LimitContent' );
				
				if ($behavioural_params) {
					if (is_int( $behavioural_params[1] )) {
						$content_limit = $behavioural_params[1];
					}
				}
				else {
					$content_limit = $this->content_limit;
				}
				
				if (! is_null( $content_limit )) {
					$content = strip_tags( $content );
					$content = substr( $content, 0, $content_limit );
					$content .= ' <div class="read-more"><a href="' . apply_filters('the_permalink', get_permalink()) . '">' . OnePanelLanguage::GetText( 'read_more' ) . ' </a></div>';
				}
				
				$content_chunk = $content;
				
				$i++;
				
			}
			
			$response = $this->detail_chunks['Title'] . $this->detail_chunks['Image'] . $this->detail_chunks['SubTitle'] . $this->detail_chunks['Content'];
			
			if ($i < 1) {
			
				for ($j = 2; $j <= $i; $j++) {
					$response .= $this->detail_chunks['Image' . $j] . $this->detail_chunks['SubTitle' . $j] . $this->detail_chunks['Content' . $j];
				}
				
			}
			
			return $response;
			
		}
		
		public function RenderPostEntry() {
			
			// We're going to need a chunk to show whats currenty active
			$this->GetChunk();
			
			$response  = '<div class="generic_content">';
			$response  = '<div id="popup_highlights_post_status"></div>';
			$response .= '<div style="display:block;"><h2 style="padding-bottom:8px;">Search for a featured article &darr;</h2></div>';
			$response .= '<div style="clear:both;"></div>';
			$response .= '<div style="width:680px;display:block;">';
			$response .= '<div style="float:left;width:50%;">';
			$response .= '<input style="width:667px;" maxlength="70" name="popup_highlight_post_entry" id="popup_highlight_post_entry" onpaste="op_admin.Highlights.SearchPosts()" onkeydown="op_admin.Highlights.SearchPosts()" size="45" value="Enter a few keywords..." onfocus="if(this.value==\'Enter a few keywords...\'){this.value=\'\'}" onblur="if(this.value==\'\'){this.value=\'Enter a few keywords...\'}"/>';
			$response .= '</div>';
			$response .= '</div>';
			$response .= '<div style="clear:both;"></div>';
			$response .= 	'<div id="popup_highlight_search_results"></div>';
			$response .= '<div style="clear:both;"></div>';
			$response .= '<h2 style="border-color:#eee;">Current Post &darr;</h2>';
			$response .= '<div id="popup_highlights_post_preview">';
			$response .= 	'<div class="highlight_title">' . $this->detail_chunks['Title'] . '</div>';
			$response .= 	'<div class="highlight_content">' . $this->detail_chunks['Content'] . '</div>';
			$response .=	'</div>';
			$response .= '</div>';
			$response .= '<div style="clear:both;height:10px;"></div>';
			
			return $response;
		}
		
		
		
		public function RenderCategoryEntry() {
			
			// We're going to need a chunk to show whats currenty actuve
			$this->GetChunk();
			
			// Also we need to know which categories are available
			$category_data = get_categories();
			
			$response  = '<div class="generic_content">';
			$response .= '<div id="popup_highlights_category_status"></div>';
			$response .= '<div style="display:block;"><h2 style="padding-bottom:8px;">Select a category to feature &darr;</h2></div>';
			$response .= '<div class="category_select">';
			$response .= '<div style="width:680px;"><select class="select" style="padding:4px;width:676px;background:#f3f7fa;border:1px solid #d4e2ef !important;" onchange="op_admin.Highlights.SetCategory()" id ="popup_highlight_category_entry" name="popup_highlight_category_entry">';
					    
		    foreach ($category_data as $category) {
		    	
		    	if ($category->parent == 0) {
		    		
				    $response .= '<option value="' . $category->term_id . '" ' . (($this->source_id == $category->term_id) ? 'selected="selected"' : '') . '>';
				    $response .= $category->name;
				    $response .= '</option>' . "\n";
		    		
		   		}
		    }
			
		    $response .= '</select></div>';
			$response .= '</div>';
			$response .= '<h2 style="border-color:#eee;">Current Category &darr;</h2>';
			$response .= '<div id="popup_highlights_category_preview">';
			$response .= 	'<div class="title">' . $this->detail_chunks['Title'] . '</div>';
			$response .= 	'<div>' . $this->detail_chunks['Content'] . '</div>';
			$response .= '</div>';
			$response .= '</div>';
			$response .= '<div style="clear:both;height:10px;"></div>';
			
			return $response;
			
		}
		
		public function Render() {
			
			$response  = $this->RenderOnOff();
			
			$response .= '<div id="popup_highlight_container">';
			
			switch ($this->source_type) {
				
				case 2: 
					$response .= $this->RenderPostEntry();
				break;
				
				case 3: 
					$response .= $this->RenderCategoryEntry();
				break;
				
				
			}
			
			$response .= '</div>';
			
			return $response;
		}
		
	}

	class HighlightsModule extends OnePanelModule {
		
		protected $title = 'Highlights';
		protected $help_text = 'Use the Highlights module to feature posts/categories on your homepage.';
		protected $description = 'Put your favorite posts in the spotlight with the Highlights module. The Highlights module allows you to feature any post / category on the homepage of your website with ease.';
		protected $short_description = 'Use the Highlights module to feature posts/categories on your homepage.';
		protected $keywords = array( 'featured posts', 'featured', 'post', 'posts', 'featured categories', 'categories', 'pages', 'highlights', 'highlight', 'spotlight' );
		protected $categories = array( 'Featured' );
		
		public function Render() {
			
			// Increase the view count
			$this->IncreaseViewCount();
			
			// Print the selector menu
			$response['content']  = '<div class="HighlightDrop">';
			$response['content'] .= '<div class="HighlightDropTitle left_side">';
			$response['content'] .= '<label for="popup_highlight_select"><span class="BB">Please select a Highlight &rarr;</span></label>';
			$response['content'] .= '</div>';
			$response['content'] .= '<div class="right_side" id="label_highlight">';
			$response['content'] .= '<select class="select" onchange="op_admin.Highlights.SwitchHighlight()" id="popup_highlight_select">';
			
			foreach ($this->features as $key => &$feature) {
				$response['content'] .= '<option>' . $feature->GetTitle() . '</option>';
			}
			
			$response['content'] .= '</select>';
			$response['content'] .= '</div>';
			$response['content'] .= '</div>';
			$response['content'] .= '<div class="DropShadowBlue"></div>';
			$response['content'] .= '<div style="clear:both"></div>';
			
			// Get the first highlight and print the management console for it.
			reset( $this->features );
			$first_highlight = current( $this->features );
			$response['content'] .= '<div id="popup_highlight_container">';
			$response['content'] .= 	$first_highlight->Render();
			$response['content'] .= '</div>';
			$response['content'] = utf8_encode( $response['content'] );
			
			$response['title'] = $this->title;
			$response['info'] = $this->help_text;

			die( json_encode( $response ));
		}
		
		
		public function RegisterFeatures() {
			
			$highlights = OnePanelConfig::GetHighlights();
			
			if (is_array( $highlights )) {
				
				foreach ($highlights as $key => &$highlight) {

					// If the highlight does not already exist in this modules features
					if (! isset( $this->features[ self::GetHighlightKey( $highlight->GetName() ) ] )) {
						
						$new_highlight = new HighlightFeature();
						$new_highlight_key = self::GetHighlightKey( $highlight->GetName() );
						
						$new_highlight->SetTitle( $highlight->GetName() );
						$new_highlight->SetSourceType( $highlight->GetDefaultSourceType() );
						$new_highlight->SetContentLimit( $highlight->GetContentLimit() );
						$new_highlight->SetTitleLimit( $highlight->GetTitleLimit() );
						$new_highlight->SetAlternateKey( self::GetHighlightKey( $highlight->GetName() ) );
						$new_highlight->SetConfigModule( $highlight );
						$new_highlight->Enable();
						
						/*
						 * We don't use a refrence here as the source object will be replaced 
						 * after this iteration.
						 */
						$this->features[ $new_highlight_key ] = $new_highlight;
						
						// We now refrence the permanent object.
						$this->enabled_features[] = &$this->features[ $new_highlight_key ];
						$this->registered_features[] = $new_highlight;
						
						
					}
					else { // If it does exist in the features
						
						$stored_highlight_key = self::GetHighlightKey( $highlight->GetName() );
						$stored_highlight =  &$this->features[ $stored_highlight_key ];
						
						// Make sure its enabled
						$stored_highlight->Enable();
						
						// Set the config module 
						$stored_highlight->SetConfigModule( $highlight );
						
						/*
						 * This may change if we allow the users to set the content limit at a later date
						 * for now, any changes to the limit in the config fail need to take immediate effect 
						 */
						$stored_highlight->SetContentLimit( $highlight->GetContentLimit() );
						$stored_highlight->SetTitleLimit( $highlight->GetTitleLimit() );
						
						// Garbage collection and enabled state
						$this->registered_features[] = &$stored_highlight;
						$this->enabled_features[] = $stored_highlight_key;
						
					}
					
					// Set up ajax for it
					add_action( 'wp_ajax_opcp_' . self::GetHighlightKey( $highlight->GetName() ) . 'Activate', array( $this->features[ self::GetHighlightKey( $highlight->GetName() ) ], 'Activate' ) );
					add_action( 'wp_ajax_opcp_' . self::GetHighlightKey( $highlight->GetName() ) . 'Deactivate', array( $this->features[ self::GetHighlightKey( $highlight->GetName() ) ], 'Deactivate' ) );
					
				}
				
			}
			
		}
		
		
		public function BuildChunks() {
		
			// Does this want to cycle the features or the highlights?
			foreach ($this->features as $key => $feature) {
				$this->chunks[$key] = $feature->GetChunk();
			}
			
		}
		
		
		static public function GetHighlightKey( $identifier ) {
			return 'Highlight' . $identifier . 'Feature';
		}
		
		
		// Ajax Stuff
		public function SwitchHighlight() {
			
			$highlight_name = mysql_real_escape_string( $_POST['highlight_name'] );
			$highlight = &$this->features[ self::GetHighlightKey( $highlight_name ) ];
			
			if (is_object( $highlight )) {
				die( $highlight->Render() );
			}
			else {
				die( 'No Highlight by the name' . $highlight_name );
			}
			
		}
		
		public function SearchPosts() {
			
			if (empty( $_POST['search_term'] )) {
		    	$output = '<div class="popup_no_results"><div class="module_error_stroke">Please enter a search term.</div></div>';
		    	die($output);
		    }
		    
		    global $wpdb;
		    $wpdb->charset = 'utf8';
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
				echo '<div style="clear:both;"></div>';
		    	echo '<div class="input_option">';
				echo '<div class="radio_option"><input type="radio" onclick="op_admin.Highlights.SetPost()" name="popup_highlights_featured_post_radio" value="' . $row['ID'] . '"></div>';
				echo '<div class="radio_content">' . $row['post_title'] . '</div></div>';
				echo '<div style="clear:both;height:5px;"></div>' . "\n";
		    }
		    
		    die;
				
		}
		
		public function SetPost() {
			
			$highlight_name = mysql_real_escape_string( $_POST['highlight_name'] );
			$highlight = &$this->features[ self::GetHighlightKey( $highlight_name ) ];
			
			$new_post_id = (int) mysql_real_escape_string( $_POST['id'] );
			
			$highlight->SetSourceID( $new_post_id );
			OnePanel::PackData();
			
			$highlight->GetChunk(); // Refresh the chunk data
			
			$response = '';
			
			if (is_object( $highlight )) {
				$response .= '<div class="title">' . $highlight->GetDetailedChunk( 'Title' ) . '</div>';
				$response .= '<div>' . $highlight->GetDetailedChunk( 'Content' ) . '</div>';
			}
			
			die($response);			
		}
		
		
		
		public function SetCategory() {
			
			$highlight_name = mysql_real_escape_string( $_POST['highlight_name'] );
			$highlight = &$this->features[ self::GetHighlightKey( $highlight_name ) ];
			
			$new_cat_id = (int) mysql_real_escape_string( $_POST['id'] );
			
			$highlight->SetSourceID( $new_cat_id );
			OnePanel::PackData();
			
			$highlight->GetChunk(); // Refresh the chunk data
			
			$response = '';
			
			if (is_object( $highlight )) {
				$response .= '<div class="title">' . $highlight->GetDetailedChunk( 'Title' ) . '</div>';
				$response .= '<div>' . $highlight->GetDetailedChunk( 'Content' ) . '</div>';
			}
			
			die($response);			
		}
		
		
	}