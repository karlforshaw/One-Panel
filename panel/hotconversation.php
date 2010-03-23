<?php

	// FEATURES
	class HotConversationToggle extends OnePanelFeature {
		
		protected $title = 'Hot Conversation';
		protected $help_text = 'Feature the most commented posts on your site by activating Hot Conversation.';
		
		public function Render() {
			return $this->RenderOnOff();
		}
		
	}

	// MODULE
	class HotConversation extends OnePanelModule {
		
		protected $title = 'Hot Conversation';
		protected $help_text = 'Feature the most commented posts on your site by activating Hot Conversation.';
		protected $description ='Take advantage of One Panel\'s unique Feature "Hot Conversation" and show off the most commented posts your blog has to offer in a well presented image format.';
		protected $short_description ='Feature the most commented posts on your site by activating Hot Conversation.';
		protected $keywords = array( 'featured', 'hot conversation', 'hot', 'conversation', 'comments', 'talked about', 'thumbnail', 'thumbnails', 'popular' );
		protected $categories = array( 'Featured' );
		
		public function Render() {
			
			if ($this->CanBeActive()) {
				$this->GenericRender();
			}
			else {
				$this->IncreaseViewCount();
				$this->NoActivateMessage();
			}
			
		}
		
		public function NoActivateMessage() {
			
			$number_of_posts = OnePanelConfig::GetNumberOfHotConversationPosts();
			if (is_null( $number_of_posts )) $number_of_posts = 4;
			
			$response['content'] = '<div class="no_activate" style="padding:10px;"><strong style="font-size:14px;">Please Note...</strong><br /><br />'. OnePanel::GetLicenseeName() .', Hot Conversation cannot be activated until you have ' . $number_of_posts . ' posts with thumbnails, and the Thumbnails module active.</div>';
			$response['content'] = utf8_encode( $response['content'] ); 
			$response['title'] = $this->title;
			$response['info'] = $this->help_text;
			
			die( json_encode( $response ) );
			
		}
		
		public function RegisterFeatures() {
			$this->RegisterFeature( 'HotConversationToggle' );
			if (! self::CanBeActive()) $this->features['HotConversationToggle']->OverrideActivation();
		}
		
		public function BuildChunks() {

			$enabled = $this->features['HotConversationToggle']->IsActive();

			if( $enabled == false) {
				return false;
			}
			
			// How many posts should we show? TODO add a behaviour hook for this
			$config_limit = OnePanelConfig::GetNumberOfHotConversationPosts();
			if (is_int( $config_limit )) {
				$limit = $config_limit;
			}
			else {
				$limit = 4;
			}
		
			global $wpdb;
				
			$sql = 'SELECT pm.meta_value AS file, p.post_title, p.ID 
					FROM ' . DB_NAME . '.' . $wpdb->prefix . 'posts p 
					LEFT JOIN ' . DB_NAME . '.' . $wpdb->prefix . 'postmeta pm on p.ID = pm.post_id 
					WHERE pm.meta_key = "Thumbnail" 
					ORDER BY p.comment_count DESC 
					LIMIT ' . $limit;
		
			$result = mysql_query( $sql );
			
			if (! $result ) return false;
			
			$thumbs = array();
				
			while ($row = mysql_fetch_assoc( $result )) {
				$thumbs[] = $row;
			}
				
			if(count($thumbs) < 2) {
				return false;
			}
			elseif ((count( $thumbs ) < 2) && (count( $thumbs ) > 4)) {
				while ( (count($thumbs) % 2) != 0 ) {
					array_pop( $thumbs );
				}
			}
				

			$this->chunks['Header'] = '<div class="title">' . OnePanelLanguage::GetText( 'hot_conversation' ) . '</div>' . "\n";
			
			$this->chunks['Thumbnails'] = '<ul>' . "\n";
			
			foreach ($thumbs as $key => $thumb) {
				$this->chunks['Thumbnails'] .= '<li><a title="' . $thumb['post_title'] . '" href="' .  get_permalink($thumb['ID']) . '"><img alt="' . $thumb['post_title'] . '" src=" ' . $thumb['file'] . '" /></a></li>';
			}
			
			$this->chunks['Thumbnails'] .= '</ul>' . "\n";
			
		}
		
		
		public static function CanBeActive() {

			global $wpdb;
			
			// Number of posts?
			$number_of_posts = OnePanelConfig::GetNumberOfHotConversationPosts();
			if (is_null( $number_of_posts )) $number_of_posts = 4;
				
			$sql = 'SELECT pm.meta_value AS file, p.post_title, p.ID 
					FROM ' . DB_NAME . '.' . $wpdb->prefix . 'posts p 
					LEFT JOIN ' . DB_NAME . '.' . $wpdb->prefix . 'postmeta pm on p.ID = pm.post_id 
					WHERE pm.meta_key = "Thumbnail" 
					ORDER BY p.comment_count DESC 
					LIMIT ' . $number_of_posts;
		
			$result = mysql_query( $sql );
			if (! $result)
				return false;
			
			if (mysql_num_rows( $result ) < $number_of_posts) return false;
			else return true;
			
		}
		
	}