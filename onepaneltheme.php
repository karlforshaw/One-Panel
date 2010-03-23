<?php

	final class OnePanelTheme {
		
		/*
		 * PROPERTIES
		 * 
		 * $instance
		 * When running, this contains the only instance of OnePanel due to the private constructor. 
		 * See http://en.wikipedia.org/wiki/Singleton_pattern
		 * 
		 * $operational_data
		 * Contains all the operational data for OnePanel:
		 * 		[0] License and Runtime data
		 * 		[1] Module Data
		 * 
		 * $trash
		 * If a foreign object is unserialized its name 
		 * ends up in here to be purged
		 * 
		 */ 
		private static $instance = null;
		private static $operational_data;
		private static $trash = array();
		
		
		
		
		/*
		 * METHODS
		 * 
		 * All One Panel internals follow.
		 * HTML Rendering Functions are further down the file.
		 * 
		 */ 
		
		
		/**
		 * GetInstance
		 * 
		 * Singleton Function allow global access to the OnePanelTheme Object
		 * 
		 */
		public static function GetInstance()
	    {
			self::Start();
	        return self::$instance;
	    }
	    
	    
	    /**
	     * Start
	     * 
	     * Singleton shortcut to run the constructor without returning the instance
	     * 
	     */
		public static function Start()
	    {
	        if (! is_object( self::$instance ))
	        {
	        	// Check for the existence of the OnePanel object
	       		if (class_exists( 'OnePanel' )) {
					if (is_object( OnePanel::GetInstance() )) {
						return false;
					}
				}
				else {
	            	self::$instance = new OnePanelTheme();
				}
	        }
	        
	    }
	    
	    
	    /**
	     * __clone
	     * 
	     * Prevents the singleton from being cloned
	     * 
	     */
	    public function __clone() {
	    	return false;
	    }
	    
	    
	    
	    private function __construct() {
	    	
	    	// Include Panel Class Definitions
			$this->IncludeClasses();
			
			// Retrieve the operational data from the db
			$this->UnpackData();
			
			// Set up the language
			$this->PopulateLanguageData();
			OnePanelLanguage::CleanupData( self::$operational_data[2] );
			
	    	// Check for valid installation and define ONE_PANEL_RUNNING constant
			if ((isset( self::$operational_data[0] )) && (self::$operational_data[0]['installed'] == true)) {
				define( 'ONE_PANEL_RUNNING', true );
			}
			else {
				// Failsafe
				self::$operational_data[0]['installed'] = false;
			}
			
		 	// Is this a fresh install?
		    if (is_null( self::$operational_data )) {
		    	define( 'ONE_PANEL_FRESH_INSTALL', true );
		    }
		    
		    // Register Modules to make use of the defaults.
			$this->RegisterModules();
			
			// Add the action for the FeedBurner Redirect
			add_action( 'template_redirect', array( self::$operational_data[1]['FeedBurner']->features['FeedBurnerToggle'], 'FeedburnerFilter' ));

	    }
	    
	    
	    
	    public static function DataIsCompatible() {
	    	
	    	$data_theme = false;
	    	
	    	if (isset( self::$operational_data[0]['theme_name'] ))
	    		$data_theme = self::$operational_data[0]['theme_name'];

	    		$running_theme = get_option( 'template' ); 
	    	
	    	if ($data_theme == $running_theme) {
	    		return true;
	    	}
	    	else {
	    		return false;
	    	}
	    	
	    }
	    
	    
	    
		private function IncludeClasses() {
	    	
		    require_once ONE_PANEL_DIR . '/panel/feature.php';
		    require_once ONE_PANEL_DIR . '/panel/module.php';
		    require_once ONE_PANEL_DIR . '/panel/skin.php';
		    require_once ONE_PANEL_DIR . '/panel/highlights.php';
		    require_once ONE_PANEL_DIR . '/panel/contentcontrol.php';
		    require_once ONE_PANEL_DIR . '/panel/thumbnails.php';
		    require_once ONE_PANEL_DIR . '/panel/postheader.php';
		    require_once ONE_PANEL_DIR . '/panel/postfooter.php';
		    require_once ONE_PANEL_DIR . '/panel/hotconversation.php';
		    require_once ONE_PANEL_DIR . '/panel/feedburner.php';
		    require_once ONE_PANEL_DIR . '/panel/seo.php';
		    require_once ONE_PANEL_DIR . '/panel/advertising.php';
		    require_once ONE_PANEL_DIR . '/panel/featuredvideo.php';
		    require_once ONE_PANEL_DIR . '/panel/menulinks.php';
		    require_once ONE_PANEL_DIR . '/panel/stats.php';
		    require_once ONE_PANEL_DIR . '/panel/homepagelayouts.php';
		    require_once ONE_PANEL_DIR . '/panel/localization.php';
		    
	    }
	    
	    
		private function RegisterModules() {

			$this->RegisterModule( 'HighlightsModule' );
			$this->RegisterModule( 'SkinModule' );
			$this->RegisterModule( 'Thumbnails' );
			$this->RegisterModule( 'HotConversation' );
			$this->RegisterModule( 'ContentControl' );
			$this->RegisterModule( 'FeedBurner' );
			$this->RegisterModule( 'AdvertisingModule' );
			$this->RegisterModule( 'PostFooter' );
			$this->RegisterModule( 'PostHeader' );
			$this->RegisterModule( 'SEO' );
			$this->RegisterModule( 'FeaturedVideoModule' );
			$this->RegisterModule( 'MenuLinksModule' );
			$this->RegisterModule( 'StatsModule' );
			$this->RegisterModule( 'HomePageLayoutModule' );
			$this->RegisterModule( 'LocalizationModule' );
		
	    }
	    
		private function RegisterModule( $class_name ) { 
			
			$modules = &self::$operational_data[1];
			
			if (defined( 'ONE_PANEL_FRESH_INSTALL' )) { // If there is no $op_data
				
				$module = new $class_name();
				$modules[$class_name] = &$module;
				
			}
			elseif (! isset( $modules[$class_name] )) { // If the module is a new addition

				$module = new $class_name();
				$modules[$class_name] = &$module;
				
			}
			
		}
		
	    
		    
		/**
		 * Encrypts the operational data and stores it in the database
		 *
		 */
    	public static function PackData() {
    	
	    	$pay_load = serialize( self::$operational_data );
	    	$pay_load = base64_encode( $pay_load );
	    	$pay_load = strtr( 
	    		$pay_load, 
	    		'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/', 
	    		'mW05fEPtIw+Lajq7AS3Yb/Cdy9Uhovz1skMGB4RJNnQg6iZlOKe28xHTXpuDVcrF'
	    	);
			update_option( 'one_panel_data', $pay_load );    	
	    }
	    
	    
	    /**
	     * Retrives the operational data from the database and unencrypts it.
	     *
	     */
	 	private function UnpackData() {
	    	
	    	// Set the failsafe
	    	ini_set('unserialize_callback_func', 'OnePanel::UnpackProtection');
	    	
	    	$data = get_option( 'one_panel_data' );
	    	if ($data === false) {
	    		return null;
	    	}
	    	else { 
		    	$data = strtr( 
		    		$data, 
		    		'mW05fEPtIw+Lajq7AS3Yb/Cdy9Uhovz1skMGB4RJNnQg6iZlOKe28xHTXpuDVcrF', 
		    		'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/'
		    	);
		    	$data = base64_decode( $data );
		    	$data = @unserialize( $data );
		    	
		    	self::$operational_data = &$data;
		    	
		    	// Take out the trash
		    	if (count( self::$trash ) > 0) self::EmptyTrash();
		    	
	    	}
	    	
	    }
	    
	    private static function UnpackProtection($class_name) {
	    	self::$trash[] = $class_name;
	    }
	    
	    private static function EmptyTrash() {
	    	
	    	$modules = &self::$operational_data[1];

	    	foreach ($modules as $key => &$module) {
	    		
	    		if (in_array( $key, self::$trash )) {
	    			unset( $modules[$key] );
	    		}
	    		else {
	    			
	    			$features = &$module->features;
	    			
	    			foreach ($features as $feature_key => &$feature) {
	    				if (in_array( $feature_key, self::$trash )) unset( $features[$feature_key] );
	    			}
	    			
	    		}
	    		
	    	}
	    	
	    	self::PackData();
	    	
	    }
	    
	    
		private function PopulateLanguageData() {
	    	
	    	if (! isset( self::$operational_data[2] )) {
	    		
	    		$config_data = OnePanelLanguage::GetConfigData();
	    		
	    		// TODO what should we do if it is missing?
	    		if (empty( $config_data )) {
	    			if (defined( 'ONE_PANEL_RUNNING' )) trigger_error( 'One Panel Error: No language data found in config file', E_USER_ERROR );
	    		}
	    		else {
					if (is_array( $config_data )) {
						
						self::$operational_data[2] = $config_data;
						return true;
						
					}
					else {
						if (defined( 'ONE_PANEL_RUNNING' )) trigger_error( 'One Panel Error: No language data found in config file', E_USER_ERROR );
					}
	    		}
	    		
	    	}
	    	else {
	    		return true;
	    	}
	    	
	    }
	    
	    
		public static function &GetLanguageData( $language=null ) {

			if ($language == null) {
				
				if (! isset( self::$operational_data[2] )) return false;
				else {
					$to_return = &self::$operational_data[2];
					return( $to_return );
				}
				
			}
			else {
				
				if (! isset( self::$operational_data[2][$language] )) return false;
				else {
					$to_return = &self::$operational_data[2][$language];
					return( $to_return );
				}
				
			}
			
		}
	    
	    
	    public static function FeatureIsActive( $module, $feature ) {
	    	
	    	$module_object = self::$operational_data[1][$module];
	    	
	    	if (is_object( $module_object )) {
	    		
	    		if (is_object( $module_object->features[$feature] )) {
	    			return $module_object->features[$feature]->IsActive();
	    		}
	    		else {
	    			if (OnePanelConfig::UseDebug()) die( $feature . ' Is not an object!' );
	    		}
	    		
	    	}
	    	else{
	    		if (OnePanelConfig::UseDebug()) die( $module . ' Is not an object!' );
	    	}
	    	
	    }
	    
	    
	   /*
		 * HTML RENDERING FUNCTIONS
		 * 
		 * All the functions that print something to the admin panel 
		 * appear below.
		 *  
		 */
	    
	    /**
	     * Get Layout Location
	     * 
	     * Returns the location of the CSS file for the chosen layout.
	     *
	     */
	    public static function GetLayoutLocation() {
	    	
	    	// Look to the layouts module data
	    	$layout_module = self::$operational_data[1]['HomePageLayoutModule'];
	    	
	    	if (is_object( $layout_module )) {
	    		
	    		$layout = &$layout_module->GetDefaultLayout();
	    		if (is_object( $layout )) return $layout->GetLocation();
	    		
	    	}
	    	
	    }
	    
		public static function &GetActiveLayout() {
	    	
			$layout_module = &self::$operational_data[1]['HomePageLayoutModule'];
	    	
	    	if (is_object( $layout_module )) {
	    		
	    		$return = &$layout_module->GetDefaultLayout();
	    		return $return;
	    		
	    	}
	    	
	    }
	    
	    
	    /**
	     * Returns a managable image that is specific to the current, active theme.
	     *
	     * @param str $identifier
	     */
	   	public static function GetSkinSpecificImage( $identifier ) {
	   		$skin = &self::GetActiveSkin();
	   		return $skin->GetManagableImage( $identifier );
	   	}
	   	
	   	
	   	/**
	   	 * Returns a stylesheet that is specific to the current, active theme.
	   	 *
	   	 * @param str $identifier
	   	 */
		public static function GetSkinSpecificStylesheet( $identifier ) {
	   		$skin = &self::GetActiveSkin();
	   		return $skin->GetStyle( $identifier ); // TODO check for null value
	   	}
	   	
	   	
	   	public static function GetActiveSkin() {
	   		
	   		// First check the session data
	   		if (isset( $_SESSION['one_panel_skin'] )) {
	   		
	   			$skins_module = self::$operational_data[1]['SkinModule'];
	   			$session_skin = $skins_module->GetSkin( $_SESSION['one_panel_skin'] );
	   			
	   			// Check that the requested skin is actually available for use.	   			
	   			if (is_object( $session_skin )) {
	   				return( $session_skin );
	   			}
	   			else {
	   				$default_skin = $skins_module->features['DefaultSkinFeature']->GetDefaultSkin();
		   			return( $default_skin );	
	   			}
	   			
	   		}
	   		else {
	   			
	   			$skins_module = self::$operational_data[1]['SkinModule'];
	   			$default_skin = $skins_module->features['DefaultSkinFeature']->GetDefaultSkin();
	   			
	   			return( $default_skin );
	   		}
	   		
	   	}
	   	
	    
	    public static function GetChunk( $module_name, $chunk_name ) {

	    	$module = &self::$operational_data[1][$module_name];
	    	
	    	if (OnePanelConfig::UsingDebug()) {
	    		if ($module == null) die( 'The module ' . $module . ' does not exist.');
	    	}
	    		
	    	$chunk = &$module->GetChunk( $chunk_name );
	    	
	    	if ($chunk == null) {
				$module->BuildChunks();
				$chunk = &$module->GetChunk( $chunk_name );
	    	}
	    	
	    	return $chunk;
	    }
	    
		public static function GetDetailedChunk( $module_name, $feature_name, $chunk_name ) {

	    	$module = &self::$operational_data[1][$module_name];
	    	
	    	if (OnePanelConfig::UsingDebug()) {
	    		if ($module == null) die( 'The module ' . $module . ' does not exist.');
	    	}
	    	
	    	$chunk = &$module->GetDetailedChunk( $feature_name, $chunk_name );
	    	
	    	if ($chunk == null) {
				$module->BuildChunks();
				$chunk = &$module->GetDetailedChunk( $feature_name, $chunk_name );
	    	}
	    	
	    	return $chunk;
	    }
	    
	    
	    
	    public static function PrintHotConversation() {

	    	$enabled = self::FeatureIsActive( 'HotConversation', 'HotConversationToggle' );
	    	
			if ($enabled == false) return false;
			
	    	echo '<div id="hot-conversation">' . "\n";
	    	self::PrintChunk( 'HotConversation', 'Header' );
	    	self::PrintChunk( 'HotConversation', 'Thumbnails' );
	    	echo '</div>' . "\n";
	    }
	    
	    
	    public static function PrintSkinSwitcher() {
	    	
	    	$enabled = self::FeatureIsActive( 'SkinModule', 'SkinFeatureSwitcher' );
			if ($enabled == false) return false;
			
	    	echo '<div id="skin-switcher">' . "\n";
	    	echo self::GetChunk( 'SkinModule', 'List' );
	    	echo '</div>' . "\n";
	    }
	    
	    
	    public static function PrintStatsCode() {
	    	self::PrintChunk( 'StatsModule', 'StatsCode' );
	    }
	    
	    
		public static function PrintPageMenu() {
			
			$page_data = get_pages();
			
			if (is_object(self::$operational_data[1]['MenuLinksModule'])) {
				$links = self::$operational_data[1]['MenuLinksModule']->features['MenuLinksFeature']->GetExcludedPages();
			}
			
			if (! empty( $links )) {
			
				$include_string = '';
				
				foreach ( $links as $link_id ) {
					
					$include_string .= $link_id . ',';
					
					// also add the children
					$children = get_page_children( $link_id, $page_data );
					foreach ( $children as $child ) {
						$include_string .= $child->ID . ',';
					}
					
				}
				wp_list_pages( array( 'exclude' => $include_string, 'title_li' => 0, 'depth' => 2 ) );
				
			}
			else {
				wp_list_pages( array( 'title_li' => 0, 'depth' => 2 ) );
			}
			
		}
	
	
		public static function PrintCategoryMenu() {
			
			$cat_data = get_pages();
			
			if (is_object(self::$operational_data[1]['MenuLinksModule'])) {
				$links = self::$operational_data[1]['MenuLinksModule']->features['MenuLinksFeature']->GetExcludedCategories(); // TODO REDUCE THIS
			}
			
			if (! empty( $links )) {
			
				$include_string = '';
				
				foreach ( $links as $link_id ) {

                $include_string[] = $link_id;

                // also add the children
                $children_str = get_category_children( $link_id );
                
                if (! empty($children_str)) {
                	
                    $children = explode( '/', $children_str );

                    foreach ( $children as $child ) {
                        $include_string[] = $child;
                    }
                }

            }

            $include_string = join(',', $include_string);
            wp_list_categories( array( 'exclude' => $include_string, 'title_li' => 0, 'depth' => 2 ) );
				
			}
			else {
				wp_list_categories( array( 'title_li' => 0, 'depth' => 2 ) );
			}
			
		}
	    
	    
	    
	    
	    
	    
	    public static function PrintSubCategoryMenu() {
	    	
	    	if (is_category()) {
	    		
	    		global $cat;
	    		 
				if ( get_category_children( $cat ) != "" ) {
					echo "<div class=\"page-children article\"><ul>";
					wp_list_categories( 'orderby=name&show_count=0&title_li=&child_of='.$cat );
					echo "</ul>
					</div>";
				}
	    	}
	    	
	    }
	    
	    public static function PrintSubPageMenu() {
	    	
	    	global $post;
			$children = wp_list_pages('title_li=&child_of='.$post->ID.'&echo=0');
			
			if ($children) { 
				?>
					<div class="page-children article">
						<ul>
							<?php echo $children; ?>
						</ul>
					</div>
				<?php 
			}
				
	    }
	    
	    
	    public static function PrintSkinSpecificImage( $identifier ) {
	    	echo self::GetSkinSpecificImage( $identifier );
	    }
	    
	 	public static function PrintSkinSpecificStylesheet( $identifier ) {
	    	echo self::GetSkinSpecificStylesheet( $identifier );
	    }
	    
	    
	    public static function GetThumbnail( $post_id=null, $custom_field=null ) {
	    	
	    	if ($custom_field == null) $custom_field = 'Thumbnail';
	    	
	    	$enabled = self::FeatureIsActive( 'Thumbnails', 'ThumbnailsToggle' );
			if ($enabled == false) return false;
			
			global $post;
			
	    	if ($post_id == null) {
	    		$post_id = get_the_ID();
	    	}
	    	
	    	$link = get_permalink( $post_id );
			
			$return  = 	'<div class="thumbnail">';
			$return .= 		'<a href="' . $link . '">';
			$return .= 			'<img alt="' . get_the_title( $post_id ). '" src="' . get_post_meta( $post_id , $custom_field, true) . '" title="' . get_the_title( $post_id ) .'" />';
			$return .= 		'</a>';
			$return .= 	'</div>';
			
			return $return;
			
	    }
	    
	    public static function PrintThumbnail( $post_id=null ) {
	    	echo self::GetThumbnail( $post_id );
	    }
	    
	   public static function PrintAlternateThumbnail( $custom_field=null ) {
	   		echo self::GetThumbnail( null, $custom_field );
	   }
	    
	    
	    public static function PrintTitle( $limit = 50 ) {
	    	
	    	$enabled = self::FeatureIsActive( 'ContentControl', 'PostTitleLimitFeature' );
		
			// Don't limit if disabled
			if ($enabled == false) {
				
				the_title();
				return true;
				
			}

			// Get the content withouth editing it
			$title = get_the_title();
			
			if (strlen( $title ) > $limit) {
				$title = substr( $title, 0, $limit ) . '...';
			}
			
			echo $title;
		
	    }
	    
	    
	    public static function PrintChunk( $module_name, $chunk_name ) {
	    	echo self::GetChunk( $module_name, $chunk_name );
	    }
	    
	    
	    public static function PrintFeaturedVideo() {
	    	
	    	if (self::FeatureIsActive( 'FeaturedVideoModule', 'FeaturedVideoFeature' )) {
		    	echo '<div id="featured-video-wrapper">' . "\n";
		    	self::PrintChunk( 'FeaturedVideoModule', 'Header' );
		    	self::PrintChunk( 'FeaturedVideoModule', 'Video' );
		    	echo '</div>' . "\n";
	    	}
	    	
	    }
	    
	    
	    public static function PrintFeedburnerSubscribe() {
	    	
	    	echo '<div id="fedburner-subscribe">' . "\n";
	    	self::PrintChunk( 'FeedBurner', 'Header' );
	    	self::PrintChunk( 'FeedBurner', 'Form' );
	    	echo '</div>' . "\n";
	    	
	    }
	    
	    
	    public static function PrintFooterMenu() {

			$page_data = get_pages();
			
	    	if (is_object(self::$operational_data[1]['MenuLinksModule'])) {
				$footer_links = self::$operational_data[1]['MenuLinksModule']->features['MenuLinksFeature']->GetExludedFooterPages();
			}
			
			if (! empty( $footer_links )) {
			
				$include_string = '';
				
				foreach ( $footer_links as $link_id ) {
					$include_string .= $link_id . ',';
					
					$children = get_page_children( $link_id, $page_data );
					
					foreach ( $children as $child ) {
						$include_string .= $child->ID . ',';
					}
				}
				
				wp_list_pages( array( 'exclude' => $include_string, 'depth' => 1, 'title_li' => 0 ) );
				
			}
			else {
				wp_list_pages( array( 'depth' => 1, 'title_li' => 0 ) );
			}
	    	
	    }
	    
	    
		public static function PrintFriendlySearch() {
			
			if (self::FeatureIsActive( 'SEO', 'FriendlySearchUrlFeature' )) {
		    	echo 'onsubmit="location.href=\'';
				bloginfo('home');
				echo '/search/\' + encodeURIComponent(this.s.value).replace(/%20/g, \'+\'); return false;"';	
	    	}
	    	
		}
	    
	    
	    public static function PrintCategorySubscribe() { // TODO?
	    	/*<div class="category-subscribe"> <?php if (is_category()) { ?><?php $cat_obj = $wp_query->get_queried_object(); $cat_id = $cat_obj->cat_ID; echo '<a href="'; get_category_rss_link(true, $cat, ''); echo '">' . OnePanelLanguage::GetText( 'subscribe_section' ) . '</a>'; ?> <?php } ?></div>*/
	    	return true;
	    }
	    
	    
	    public static function PrintPosts() {
	    	
		    // Get the options
			$limit_mode 	= self::$operational_data[1]['ContentControl']->features['ContentControlFeature']->GetLimitMode();
			$content_limit 	= self::$operational_data[1]['ContentControl']->features['ContentControlFeature']->GetLimit();
			
			// What to do
			if ($limit_mode == 1) { // Use Wordpress Defaults
					
				if (has_excerpt( get_the_ID() )) {
					the_excerpt();
				}
				else {
					the_content();
				}
				
			}
			elseif (($limit_mode == 2) || ($limit_mode == 3)) {	// Use Content Limiting
				
				// Stop this from screwing feeds up
				if (is_feed()) {
					return true;
				}
				
				global $pages, $page;
				$content = $pages[$page-1];
				
				$content = apply_filters('the_content', $content);
				$content = str_replace(']]>', ']]&gt;', $content);
				
				$permalink = get_permalink();
			
				if ($limit_mode == 3) { // Best results
					$final_output = substr(strip_tags( preg_replace('!<script[^>]*>(.*?)</script>!ism', '', $content) ), 0, $content_limit ) . '...';
					$final_output .= ' <div class="read-more"><a href="' . $permalink . '">' . OnePanelLanguage::GetText( 'read_more' ) . ' </a></div>'; // TODO replace this with lang
				}
				else {
					$final_output = &$content;
				}
				
				echo $final_output;
				
			}
	    }
	    
	    
		static public function PrintPagination() {
			
			// Some info
			global $paged;
			$on_page = intval($paged);
			$no_prev = false;
			$no_next = false;
			
			// Fix page
			if ($on_page == 0) {
				$on_page = 1;
			}
			
			// Get the max number of pages
			global $wp_the_query;
			$max_pages = $wp_the_query->max_num_pages;
			
			// Are we near the start?
			$start_pos = $on_page - 2;
			if ($start_pos <=  0) {
				$amt_from_zero = ($start_pos * $start_pos) + 1; // to account for 0
				$start_pos = 1;
			}
			
			// Are we near the end?
			$end_pos = $on_page + 2;
			if ($end_pos >= $max_pages) {
				$amt_from_end = $end_pos - $max_pages;
				$end_pos = $max_pages; 
			}
			
			// How does this affect the range?
			if (isset( $amt_from_zero ) && isset( $amt_from_end )) {
			
			// Leave them both alone
			}
			elseif(isset( $amt_from_zero )) { // try and add the remainder on to the end_pos
				
				$end_pos = $end_pos + $amt_from_zero;
				
				if ($end_pos >= $max_pages) {
					$end_pos = $max_pages;
				}
				
			}
			elseif(isset( $amt_from_end )) { // try and subtract the remainder from the start_pos
				
				$start_pos = $start_pos - $amt_from_end;
				
				if ($start_pos <=  0) {
					$start_pos = 1;
				}
				
			}
			
			// Sort out next and prev
			if (($on_page - 1) <= 0) $no_prev = true;
			if (($on_page + 1) > $max_pages) $no_next = true;
			
			// How many pages do we have?
			$links_to_print = ($end_pos - $start_pos) + 1;
			
			// print the list
			echo '<div id="pagination">' . "\n";
			echo 	'<ul>' . "\n";
			echo		'<li class="extreme"><a href="' . clean_url(get_pagenum_link(1)) . '">&laquo;</a></li>';
			echo 		$no_prev ? '<li class="inactive">' . OnePanelLanguage::GetText( 'newer' ) . '</li>' : '<li><a href="' . clean_url(get_pagenum_link($on_page - 1)) . '">' . OnePanelLanguage::GetText( 'newer' ) . '</a></li>';
			
			for ($i = $start_pos; $i <= $end_pos; $i++) {
				
				if ($i == $on_page) {
					echo '<li class="active">';
				} else {
					echo '<li>';
				}
				
				echo '<a href="' . clean_url(get_pagenum_link($i)) . '">' . $i . '</a></li>'; 
				
			}
			
			echo 		$no_next ? '<li class="inactive">' . OnePanelLanguage::GetText( 'older' ) . '</li>' : '<li><a href="' . clean_url(get_pagenum_link($on_page + 1)) . '">' . OnePanelLanguage::GetText( 'older' ) . '</a></li>';
			echo		'<li class="extreme"><a href="' . clean_url(get_pagenum_link($max_pages)) . '">&raquo;</a></li>';
			echo 	'</ul>' . "\n";
			echo '</div>' . "\n";
			
		}
	    
	    
	    public static function PrintAuthorGravatar( $size=40 ) {
	    	
			$enabled = self::FeatureIsActive( 'PostHeader', 'AuthorGravatarFeature' );
			if ($enabled == false) return false;
			
			global $authordata;
			$gravatar_url = 'http://www.gravatar.com/avatar/' . md5($authordata->user_email) . '.jpg?s=' . $size;
			
			// TODO ADD THIS TO ONEPANELCONFIG
			//$default = urlencode( get_option('home') . "/wp-content/themes/one-theme/img/default.jpg" );
			//$gravatar_url .= '&amp;default=' . $default;

			// Add the gravatar image
			echo '<div class="gravatar"><img height="' . $size . '" width="' . $size . '" src="' . $gravatar_url . '" alt="' . $authordata->user_nicename . '" /></div>';
			
	    }
	    
	    public static function PrintDetailedChunk( $module_name, $feature_name, $chunk_name ) {
	    	echo self::GetDetailedChunk( $module_name, $feature_name, $chunk_name );    	
	    }

	    public static function PrintAdBlock( $blockname ) {
	    	self::PrintChunk( 'AdvertisingModule', AdvertisingModule::GetBlockKey( $blockname ) );
	    }

	    public static function PrintPostInfo( $gravatar_size=40 ) {

	    	$enabled = self::FeatureIsActive( 'PostHeader', 'PostInfoFeature' );
			if ($enabled == false) return false;

			?> 
			<div id="post-info">
				<?php OnePanelTheme::PrintAuthorGravatar( $gravatar_size ); ?>
	  			<span class="written-by"><?php echo OnePanelLanguage::GetText( 'written_by' ); ?></span> <span class="author-post-link"><?php the_author_posts_link() ?></span> <span class="comments-link">
	  				<?php  
	  					if (! is_single()) {
	  						comments_popup_link(''. OnePanelLanguage::GetText( 'no_comments' ). '', ''. OnePanelLanguage::GetText( '1_comments' ). '', '% Comments' );
	  					}
	  					else {
	  						?>
	  						<a href="#comments"><?php comments_number(''. OnePanelLanguage::GetText( 'no_comments' ). '', ''. OnePanelLanguage::GetText( '1_comments' ). '', '% Comments' );?></a>
	  						<?php
	  					}
	  				?>
	  			</span>
				<span class="edit-link"><?php edit_post_link( OnePanelLanguage::GetText( 'edit_this' ) ); ?></span>
				<br />
				<span class="last-updated"><?php echo OnePanelLanguage::GetText( 'last_updated' ); ?></span> <span class="last-updated"><?php the_time(OnePanelLanguage::GetText( 'date_format' )) ?></span>
			</div>
			<?php

	    }

	    public static function PrintPostFooter() {

	    	$content = null;

	    	$enabled = self::FeatureIsActive( 'PostFooter', 'AuthorProfileLinkFeature' );
	    	if ($enabled){

	    		if ($content == null) $content = '<div id="post-footer">';
	    		$content .= '<div id="author-profile-link-wrapper">';
	    		$content .= self::GetChunk( 'PostFooter', 'AuthorProfileLink' );
	    		$content .= '</div>';

	    	}

		    $enabled = self::FeatureIsActive( 'PostFooter', 'RSSLinkFeature' );
	    	if ($enabled){

	    		if ($content == null) $content = '<div id="post-footer">';
	    		$content .= '<div id="rss-link-wrapper">';
	    		$content .= self::GetChunk( 'PostFooter', 'RSSLink' );
	    		$content .= '</div>';

	    	}

	    	$enabled = self::FeatureIsActive( 'PostFooter', 'TagsFeature' );
	    	if ($enabled){

	    		if ($content == null) $content = '<div id="post-footer">';
	    		$content .= self::GetChunk( 'PostFooter', 'Tags' );

	    	}

	    	$enabled = self::FeatureIsActive( 'PostFooter', 'SocialBookmarksFeature' );
	    	if ($enabled){

	    		if ($content == null) $content = '<div id="post-footer">';
	    		$content .= '<div id="social-bookmarks-wrapper">';
	    		$content .= self::GetChunk( 'PostFooter', 'SocialBookmarks' );
	    		$content .= '</div>';

	    	}

	    	$enabled = self::FeatureIsActive( 'PostFooter', 'RelatedArticlesFeature' );
	    	if ($enabled){

	    		$content .= '<div id="related-articles-wrapper">';
	    		if ($content == null) $content = '<div id="post-footer">';
	    		$content .= self::GetChunk( 'PostFooter', 'RelatedArticles' );
	    		$content .= '</div>';

	    	}

			if ($content != null) $content .= '</div>';
			if ($content != null) echo $content;
	    }
	    	
	}