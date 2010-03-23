<?php

	/**
	 * One Panel Config Class
	 * 
	 * This class allows for the real time manipulation of 
	 * One Panel modules and features. It should be used by
	 * theme developers in the onepanel_config.php file in the 
	 * theme root.
	 *
	 */

	final class OnePanelConfig {
		
		// PROPERTIES
		private static $instance = null;
		
		private static $skins = array();
		private static $default_skin = null;
		
		private static $layouts = array();
		private static $default_layout = null;
		
		private static $highlights = array();
		
		private static $ad_blocks = array();
		
		private static $debug_mode = false;
		
		private static $features_enabled = array();
		
		private static $misc_options = null;
		
		private static $behaviours = array();
		
		private static $thumbnail_types = array();
		private static $thumbnail_height = 116;
		private static $thumbnail_width = 116;
		
		
		
		
		// METHODS
		
		/**
		 * GetInstance
		 * 
		 * Singleton Function allow global access to the OnePanelTheme Object
		 * 
		 * @return OnePanelConfig
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
	     * Includes all the necessary files for the config API
	     *
	     * @return boolean
	     */
		public static function Start()
	    {
	    	
	    	$success = OnePanelDebug::Track( 'Starting Config Engine' );
	    	
	        if (! is_object( self::$instance ))
	        {
	        	
	        	// Load the requirements
	        	OnePanelLib::RequireFileOnce( ONE_PANEL_DIR .'/theme/behaviouralconfigobject.php' );
	        	OnePanelLib::RequireFileOnce( ONE_PANEL_DIR .'/theme/onepanelskin.php' );
			    OnePanelLib::RequireFileOnce( ONE_PANEL_DIR .'/theme/onepanelhomepagelayout.php' );
			    OnePanelLib::RequireFileOnce( ONE_PANEL_DIR .'/theme/onepaneladblock.php' );
			    OnePanelLib::RequireFileOnce( ONE_PANEL_DIR .'/theme/adsensecolorpalette.php' );
			    OnePanelLib::RequireFileOnce( ONE_PANEL_DIR .'/theme/onepanelhighlight.php' );
			    OnePanelLib::RequireFileOnce( ONE_PANEL_DIR .'/theme/onepanelbehaviour.php' );
			    OnePanelLib::RequireFileOnce( ONE_PANEL_DIR .'/theme/onepaneloutcome.php' );
			    OnePanelLib::RequireFileOnce( ONE_PANEL_DIR .'/theme/onepanelthumbnailtype.php' );
	        	
			    // Create the object
	            self::$instance = new OnePanelConfig();
	            
	            /* 
	             * Try to load the active thees config file
	             * on failiure set the ONE_PANEL_NO_CONFIG flag for later evaluation
	             * TODO if this fails add a message asking to upgrade One Panel
	             */
	            $config_file = ABSPATH . 'wp-content/themes/' . get_option( 'template' ) . '/onepanel_config.php';
		
				if (file_exists( $config_file )) {
					
					$load_config =  OnePanelDebug::Track( 'Loading theme config' );
					OnePanelLib::RequireFileOnce( $config_file );
					$load_config->Affirm();
					
				}
				else  {
					
					OnePanelDebug::Info( 'No config file found, setting ONE_PANEL_NO_CONFIG' );
					define( 'ONE_PANEL_NO_CONFIG', true );
					
				}
					 
			            
			}
			
			$success->Affirm();
			return true;
			        
	    }
		
		
		/**
	     * Clone
	     * 
	     * Prevents the singleton from being cloned
	     * 
	     * @return boolean
	     */
	    public function __clone() {
	    	return false;
	    }
	    
	    
	    /**
	     * Constructor
	     * 
	     * @return boolean
	     */
	    private function __construct() {
	    	
	    	self::$misc_options = array();
	    	return true; 
	    	
	    }
	    
	    
	    /**
	     * Set Thumbnail Size
	     * 
	     * Sets the default thumbnail size.
	     * 
	     * @param int $width
	     * @param int $height
	     * @return bool
	     */
	    public static function SetThumbnailSize( $width, $height ) {
	    	
	    	// Log
	    	$success =  OnePanelDebug::Track( 'Setting default thumbnail sizes to ' . $width . 'x' . $height );
	    	
	    	/*
	    	 * Check the integrity of the input for each argument and
	    	 * set the data accordingly, if non integer value is passed the thumbnail 
	    	 * generator will not work as typecasting a string will return 0 in some cases.
	    	 * 
	    	 */ 
	    	
	    	// Width
	    	if (OnePanelDebug::IsPositiveInteger( $width ))
	    		self::$thumbnail_width = $width;
	    	else {
	    		$success->Fail();
	    		return false;
	    	}
	    	
	    	// Height
	    	if (OnePanelDebug::IsPositiveInteger( $height ))
	    		self::$thumbnail_height = $height;
	    	else {
	    		$success->Fail();
	    		return false;
	    	}
	    	
	    	$success->Affirm();
	    	return true;
	    	
	    }
	    
	    
	    /**
	     * GetThumbnailSize
	     * 
	     * Returns an array containing the default thumbnail dimensions.
	     * 
	     * @return array( 'Width', 'Height' )
	     */
	    public function GetThumbnailSize() {
	    	return array( 'Width' => self::$thumbnail_width, 'Height' => self::$thumbnail_height );
	    }
	    
	    
	    /**
	     * Set Youtube Video Size
	     * 
	     * The YouTube feature loads from a URL, meaning the video size is missing.
	     * This function allows the theme developer to set it manually.
	     * 
	     * @param $width
	     * @param $height
	     * @return unknown_type
	     */
	    public static function SetYoutubeVideoSize( $width, $height ) {
	    	
	    	$success =  OnePanelDebug::Track( 'Setting default YouTube video size to ' . $width . 'x' . $height );
	    	
	    	// Check width and height are positive (> zero) integers 
	    	if (OnePanelDebug::IsPositiveInteger( $width )) {
	    		self::$misc_options['YoutubeVideoWidth'] = $width; 
	    	}
	    	else {
	    		$success->Fail();
	    		return false;
	    	}
	    	
	    	if (OnePanelDebug::IsPositiveInteger( $height )) {
	    		self::$misc_options['YoutubeVideoHeight'] = $height;
	    	}
	    	else {
	    		$success->Fail();
	    		return false;
	    	}
	    	
	    	$success->Affirm();
	    	return true;
	    }
	    
	    
	    /**
	     * Set Number of Hot Conversation Posts
	     * 
	     * Allows the Theme Developer to specify the number of thumbails
	     * that occur in the Hot Conversation module.
	     * 
	     * @param int $posts_to_show
	     * @return boolean
	     */
	    public static function SetNumberOfHotConversationPosts( $posts_to_show ) {
	    	
	    	$success =  OnePanelDebug::Track( 'Setting number of Hot Conversation posts' );
	    	
	    	if (OnePanelDebug::IsPositiveInteger( $posts_to_show )) {
	    		self::$misc_options['HotConversationPostsNumber'] = $posts_to_show; 
	    	}
	    	else {
	    		
	    		$success->Fail();
	    		return false;
	    		
	    	}
	    	
	    	$success->Affirm();
	    	return true;
	    	
	    }
	    
	    
	    /**
	     * Get Number of Hot Conversation Posts
	     * 
	     * Used by One Panel to determine whether the default number
	     * of posts should be overridden. 
	     * 
	     * @return int
	     */
	    public static function GetNumberOfHotConversationPosts() {
	    	
	    	if (isset( self::$misc_options['HotConversationPostsNumber'] ))
	    		return self::$misc_options['HotConversationPostsNumber'];
	    	
	    }
	    
	    
	    /**
	     * Use Debug
	     * 
	     * Allows the theme developer to determine whether errors 
	     * in the config file should cause fatal errors.
	     * 
	     * @todo Consider removal
	     * @deprecated 2.0.2 due to the OnePanelDebug object
	     */
	    public static function UseDebug() {
	    	self::$debug_mode = true;
	    }
	    
	    
	    /**
	     * Using Debug
	     * 
	     * Determine whether errors in the config file should cause fatal errors.
	     * 
	     * @todo Consider removal
	     * @deprecated 2.0.2 due to the OnePanelDebug object 
	     * @return boolean
	     */
	    public static function UsingDebug() {
	    	return self::$debug_mode;
	    }
	    
	    
	    /**
	     * Add Skin
	     * 
	     * Used by the theme developer to add skins to the theme
	     * TODO add link to OnePanelSkin docs
	     * 
	     * @see OnePanelSkin
	     * @param OnePanelSkin $skin
	     * @param boolean $default
	     * @return boolean
	     */
	    public static function AddSkin( OnePanelSkin &$skin, $default=false ) {
	    	
	    	$success = OnePanelDebug::Track( 'Adding config skin [' . $skin->GetName() . ']' );
	    	
	    	// Add this skin to the array
	    	self::$skins[$skin->GetName()] = &$skin;
	    	
	    	// Determine whether we should make this the default
	    	if (self::$default_skin == null) {
	    		
	    		OnePanelDebug::Info( 'First skin [' . $skin->GetName() . '] added - setting as default' );
	    		self::$default_skin = &$skin;
	    			
	    	}
	    	elseif ($default == true) {
	    		
	    		OnePanelDebug::Info( 'Setting [' . $skin->GetName() . '] skin as default' );
	    		self::$default_skin = &$skin;
	    		
	    	}
	    	
	    	$success->Affirm();
	    	return true;
	    	
	    }
	    
	    
	    /**
	     * AddThumbnailType
	     * 
	     * Used by the theme developer to add new manageable thumbnail types 
	     * to the theme.
	     * 
	     * @see OnePanelThumbnailType
	     * @param OnePanelThumbnailType $thumbnail_type
	     * @return boolean
	     */
	    public static function AddThumbnailType( OnePanelThumbnailType &$thumbnail_type ) {
	    	
	    	$success = OnePanelDebug::Track( 'Adding thumbnail type [' . $thumbnail_type->GetIdentifier() . ']' );
	    	
	    	self::$thumbnail_types[] = &$thumbnail_type; 
	    	
	    	$success->Affirm();
	    	return true;
	    	
	    }
	    
	    
	    /**
	     * AddBehaviour
	     * 
	     * Allows the theme developer to add behavioural modifications to certain elements
	     * used by modules.
	     * 
	     * @todo add link to api
	     * @param OnePanelBehaviour $behaviour
	     * @return boolean
	     */
	    public static function AddBehaviour( OnePanelBehaviour &$behaviour ) {
	    	
	    	// Prepare some log infomation
	    	$message = OnePanelDebug::PrepareAddBehaviourOutput( $behaviour );
	    	$success = OnePanelDebug::Track( $message );
	    	
	    	// Add the behaviour
	    	self::$behaviours[] = &$behaviour;
	    	
	    	$success->Affirm();
	    	return true;
	    	
	    }

	    
	    /**
	     * BehaviourAdjusted
	     * 
	     * Used by OnePanel to discern whether or not a config module has had 
	     * its behaviour modified. 
	     * 
	     * @param $config_module
	     * @return boolean
	     */
	    public static function BehaviourAdjusted( OnePanelBehaviouralConfigObject &$config_module ) {
	    	
	    	// Debug 
	    	$success = OnePanelDebug::Track( 'Checking ' . get_class( $config_module ) . '[' . $config_module->GetName() . '] for behavioural adjustments' );
	    	
	    	// Set initial result
	    	$return = false;
	    	
	    	// Scan
	    	foreach (self::$behaviours as &$behaviour) {
	    		
	    		if ($behaviour->ModuleIsAffected( $config_module )) {
	    			
	    			OnePanelDebug::Info( 'Config module has had its behaviour adjusted.' );
	    			$return = true;
	    			break;
	    			
	    		}
	    		
	    	}
	    	
	    	$success->Affirm();
	    	return $return;
	    	
	    }
	    
	    
	    /**
	     * BehaviourIsActive
	     * 
	     * Used by OnePanelModule to discern whether the requirements have been 
	     * met for a particular behaviour.
	     * 
	     * @param $behaviour
	     * @return boolean
	     */
	    public static function BehaviourIsActive( OnePanelBehaviour &$behaviour ) {
	    	
			$requirements = &$behaviour->GetRequirements();
			
			foreach ($requirements as &$requirement) {
				
				if ($requirement instanceof OnePanelHomePageLayout) {
					
					if (class_exists( 'OnePanelTheme' )) {
						$active_layout = &OnePanelTheme::GetActiveLayout();

						if ($active_layout == $requirement) {
							return true;
						}
					}
					
				}
				else {
					// TODO ADD SKINS
				}
				
				return false;
			}
	    	
	    }
	    
	    
	    /**
	     * GetModuleBehaviours
	     * 
	     * Searches the behaviour array for behaviours that affect the
	     * passed config module.
	     * 
	     * @param $config_module
	     * @return array OnePanelBehavior || false
	     */
	    public static function &GetModuleBehaviours( OnePanelBehaviouralConfigObject &$config_module ) {
	    	
	    	// Debug
	    	$success = OnePanelDebug::Track( 'Getting module behaviours for ' . get_class( $config_module ) . '[' . $config_module->GetName() . ']' );
	    	
	    	$response = array();
	    	
	    	foreach (self::$behaviours as &$behaviour) {
	    		
	    		if ($behaviour->ModuleIsAffected( $config_module )) {
	    			 $response[] = &$behaviour;
	    		}
	    		
	    	}
	    	
	    	// Debug
	    	$success->Affirm();
	    	
	    	// Prepare response
	    	$no_of_behaviours = count( $response );
	    	
	    	if ($no_of_behaviours > 0) 
	    		OnePanelDebug::Info( $no_of_behaviours . 'behaviour ' . ($no_of_behaviours > 1 ? 's' : '' ) . ' found.' );
	    	
	    	else {
	    		
	    		OnePanelDebug::Info( 'No behaviours found.' );
	    		$response = false;
	    		
	    	}
	    	
	    	
	    	return $response;
	    	
	    }
	    
	    
	    /**
	     * GetBehaviorAlteration
	     * 
	     * Used by modules to determine if a behaviour is altered and get the paramters for
	     * the alteration
	     * 
	     * @todo REFACTOR THIS. The name is confusing and it serves two purposes.
	     * @param OnePanelBehaviouralConfigObject $possibly_affected_object
	     * @param string $behaviour_name
	     * @return unknown_type
	     */
	    public static function GetBehaviorAlteration( OnePanelBehaviouralConfigObject $possibly_affected_object, $behaviour_name ) {
	    	
	    	// Debug
	    	$success = OnePanelDebug::Track( 'Getting behavioural adjustments for ' . get_class( $possibly_affected_object ) . '[' . $possibly_affected_object->GetName() . '] on ' . $behaviour_name );
	    	
	    	// Set initial response
	    	$return = false;
	    	
   				
   			// Does this object have behavioural alterations?
			if (OnePanelConfig::BehaviourAdjusted( $possibly_affected_object )) { 

				// Get the behaviours for the config module
				$behaviours = &OnePanelConfig::GetModuleBehaviours( $possibly_affected_object );

				foreach ($behaviours as &$behaviour) {

					if (OnePanelConfig::BehaviourIsActive( $behaviour )) { 
						
						$outcomes = &$behaviour->GetOutcomes();
						
						// Find the one with the desired outcome
						foreach ($outcomes as &$outcome) {
							
							// If we find it alter the query
							$params = &$outcome->GetParameters();
							if (($params[0] == $behaviour_name) && ($outcome->GetAffectedModule() == $possibly_affected_object)) {
								
								$return = $params;
								break 2; // NASTY
								
							}
							
						}
					}
					else {
						$return = false;
					}
				}
				
			}
			else {
				$return = false;
			}
			
			
			$success->Affirm();
			return $return;
	    	
	    }
	    
	    
	    /**
	     * GetSKin
	     * 
	     * Returns a skin from the skins array based on its identifier.
	     * 
	     * @param str $identifier
	     * @return OnePanelSkin
	     */
		public static function GetSkin( $identifier ) {
			
			// Debug
			$success = OnePanelDebug::Track( 'Getting skin for ' . $identifier );
			
	    	if (isset(  self::$skins[$identifier] )) {
	    		$success->Affirm();
	    		return self::$skins[$identifier];
	    	}
	    	else {
	    		$success->Fail();
	    		return false;
	    	}
	    	
	    }
	    
	    
	    /**
	     * GetSkins
	     * 
	     * Returns the skins member array
	     * 
	     * @return array OnePanelSkin
	     */
	    public static function GetSkins() {
	    	return self::$skins;
	    }
	    
	    
	    /**
	     * GetYoutubeVideoSize
	     * 
	     * Retrurns an array containing the set YouTube video dimensions
	     * 
	     * @return array('YoutubeVideoWidth','YoutubeVideoHeight')
	     */
	    public static function GetYoutubeVideoSize() {
	    	
	    	// Debug
	    	OnePanelDebug::Info( 'Getting YouTube video dimensions.' );
	    	
	    	if ((isset( self::$misc_options['YoutubeVideoWidth'] )) && (isset( self::$misc_options['YoutubeVideoHeight'] )) ) {
				return( array( 'Width' => self::$misc_options['YoutubeVideoWidth'], 'Height' => self::$misc_options['YoutubeVideoHeight'] ));	    		
	    	}
	    	else {
	    		
	    		OnePanelDebug::Warn( 'No youtube dimensions found.' );
	    		return false;
	    		
	    	}
	    	
	    }
	    
	    
	    /**
	     * GetThumbnailTypes
	     * 
	     * Returns the thumbnail types member array
	     * 
	     * @return array OnePanelThumbnailType || false
	     */
	    public static function &GetThumbnailTypes() {
	    	
	    	// Debug
	    	OnePanelDebug::Info( 'Getting thumbnail types.' );
	    	
	    	if (count( self::$thumbnail_types ) > 0) {
	    		return self::$thumbnail_types;
	    	}
	    	else {
	    		
	    		OnePanelDebug::Info( 'No additional thumbnail types.' );
	    		return false;
	    		
	    	}
	    	
	    }
	    
	    
	    /**
	     * AddAdBlock
	     * 
	     * Adds an adblock to the member array
	     * 
	     * @todo don't allow duplicates
	     * @param OnePanelAdBlock $block
	     * @return boolean
	     */
	    public static function AddAdBlock( OnePanelAdBlock &$block ) {
	    	
	    	$success = OnePanelDebug::Track( 'Adding ad block [' . $block->GetName() . ']' );
	    	self::$ad_blocks[$block->GetName()] = &$block;
	    	
	    	$success->Affirm();
	    	return true;
	    	
	    }
	    
	    
	    /**
	     * AddHighlight
	     * 
	     * Adds a highlight to the member array
	     *
	     * @todo don't allow duplicates
	     * @param $highlight
	     * @return unknown_type
	     */
	    public static function AddHighlight( OnePanelHighlight &$highlight ) {
	    	
	    	$success = OnePanelDebug::Track( 'Adding highlight [' . $highlight->GetName() . ']' );
	    	
	   		self::$highlights[$highlight->GetName()] = &$highlight;
	   		
	   		$success->Affirm();
	   		return true;
	   		
	    }
	    
	    
	    /**
	     * AddPaletteToAllBlocks
	     * 
	     * Adds a color pallette to all adblocks for a specified skin
	     * 
	     * @todo add more information to debug output
	     * @param AdsenseColorPalette $palette
	     * @param string $skin_name
	     * @return boolean
	     */
	    public function AddPaletteToAllBlocks( AdsenseColorPalette &$palette, $skin_name ) {
	    	
	    	$success = OnePanelDebug::Track( 'Adding color pallette to all ad blocks.' );
	    	
	    	foreach (self::$ad_blocks as $key => &$block) {
	    		$block->AddColorPalette( $palette, $skin_name );
	    	}
	    	
	    	$success->Affirm();
	    	return true;
	    	
	    }
	    
	    
	    /**
	     * AddHomePageLayout
	     * 
	     * 
	     * @param OnePanelHomePageLayout $layout
	     * @param boolean $default
	     * @return boolean
	     */
	    public static function AddHomePageLayout( OnePanelHomePageLayout &$layout, $default=false ) {

	    	$success = OnePanelDebug::Track( 'Adding homepage layout [' . $layout->GetName() . ']');
	    	
	    	self::$layouts[$layout->GetName()] = &$layout;
	    	
	    	if (self::$default_layout == null) {
	    		
	    		self::$default_layout = &$layout;
	    		OnePanelDebug::Info( 'First layout added - [' . $layout->GetName() . '] set to default.' );
	    			
	    	}
	    	elseif ($default == true) {
	    		
	    		self::$default_layout = &$layout;
	    		OnePanelDebug::Info( $layout->GetName() . 'set to default.' );
	    		
	    	}
	    	
	    	$success->Affirm();
	    	return true;
	    	
	    }
	    
	    
	    /**
	     * GetHomePageLayouts
	     * 
	     * @return array &OnePanelHomePageLayout
	     */
	    public static function &GetHomePageLayouts() {
	    	return self::$layouts;
	    }
	    
	    
	    /**
	     * GetDefaultHomePageLayout
	     * 
	     * @return &OnePanelHomePageLayout
	     */
	    public static function &GetDefaultHomePageLayout() {
	    	return self::$default_layout;
	    }
	    
	    
	    /**
	     * GetDefaultSkin
	     * 
	     * @return &OnePanelSkin
	     */
		public static function &GetDefaultSkin() {
	    	return self::$default_skin;
	    }
	    
	    
	    /**
	     * GetHighlights
	     * 
	     * @return array &OnePanelHightlight
	     */
	    public static function &GetHighlights() {
	    	return self::$highlights;
	    }
	    
	    
	    /**
	     * GetAdBlocks
	     * 
	     * @return array OnePanelAdBlock
	     */
	    public static function &GetAdBlocks() {
	    	return self::$ad_blocks;
	    }
	    
	    
	    /**
	     * GetAdBlock
	     * 
	     * Retrieve an adblock based on its name
	     * 
	     * @param str $block_name
	     * @return &OnePanelAdBlock
	     */
	    public static function &GetAdBlock( $block_name ) {
	    	return self::$ad_blocks[$block_name];
	    }
	    
	    
		/**
		 * Check File Property
		 * 
		 * This function does nothing if the theme developer has not activated
		 * debug mode in the config file. If debug is enabled, this function will
		 * check for the existence of a file and throw an exception if one is not 
		 * found.
		 *
		 * @todo change this to work with the new debug subsystem
		 * @deprecated 
		 * @param string $value
		 * @return bool
		 */
		public static function CheckFileProperty( $value ) {
			
			if (self::UsingDebug()) {
				if ($value != null) {
					if (file_exists( $value )) {
						return true;
					}
					else {
						throw new Exception( 'Cannot use ' . $value . '. The file does not exist.' );
					}
				}
			}
			else {
				return true;
			}
			
		}
		
		
		/**
		 * UseFeature
		 * 
		 * Activates a feature in OnePanel based on its name. Not recommended for use
		 * by theme developers. Use the specific function for each feature instead.
		 * 
		 * @deprecated use EnableFeature instead
		 * @param string $feature_name
		 * @return true
		 */
		public static function UseFeature( $feature_name ) {
			
			// Remember that nothing can be checked at this point and therefore this could be garbage input
			self::$features_enabled[] = $feature_name;
			
			OnePanelDebug::Info( $feature_name . ' added to list of features to attempt to load.' );
			return true;
			
		}
		
		
		/**
		 * UseAllFeatures
		 * 
		 * Activate all One Panel features
		 * 
		 * @return boolean
		 */
		public static function UseAllFeatures() {
			
			$success = OnePanelDebug::Track( 'Activating all features.' );
			
			// This function has a massive scope for breakage, be careful!
			self::$features_enabled = array(
				'PostTitleLimitFeature',
				'ContentControlFeature', // Theres a bug if you comment this out it stays put TODO
				'FeaturedVideoFeature',
				'FeedBurnerToggle',
				'HotConversationToggle',
				'AuthorProfileLinkFeature',
				'RSSLinkFeature',
				'RelatedArticlesFeature',
				'SocialBookmarksFeature',
				'TagsFeature',
				'PostInfoFeature',
				'AuthorGravatarFeature',
				'FriendlySearchUrlFeature',
				'SkinFeatureSwitcher',
				'ThumbnailsToggle',
				'MenuLinksFeature',
				'StatsFeature',
				'HomePageLayoutFeature', // TODO should be enabled by default if more than one HPL is present.
				'LocalizationTool'
			);
			
			$success->Affirm();
			
		}
		
		
		/**
		 * EnableFeature
		 * 
		 * Activates a feature in OnePanel based on its name. Not recommended for use
		 * by theme developers. Use the specific function for each feature instead.
		 * 
		 * @param string $feature_name
		 * @return boolean
		 */
		public static function EnableFeature( $feature_name ) {
			
			$success = OnePanelDebug::Track( 'Flagging feature [' . $feature_name . '] for use.' );
			
			if (in_array( $feature_name, self::$features_enabled )) {
				OnePanelDebug::Info( 'Feature already enabled, skipped.' );
				$success->Fail();
				return false;
			}
			else {
				self::$features_enabled[] = $feature_name;
				$success->Affirm();
				return true;
			}
			
		}
		
		
		public static function EnablePostTitleLimiting() {
			self::EnableFeature( 'PostTitleLimitFeature' );
		}
		
		public static function EnableContentControl() {
			self::EnableFeature( 'ContentControlFeature' );
		}
		
		public static function EnableFeaturedVideo() {
			self::EnableFeature( 'FeaturedVideoFeature' );
		}
		
		public static function EnableFeedBurner() {
			self::EnableFeature( 'FeedBurnerToggle' );
		}
		
		public static function EnableHotConversation() {
			self::EnableFeature( 'HotConversationToggle' );
		}
		
		public static function EnableAuthorProfileLink() {
			self::EnableFeature( 'AuthorProfileLinkFeature' );
		}
		
		public static function EnableRSSLink() {
			self::EnableFeature( 'RSSLinkFeature' );
		}
		
		public static function EnableRelatedArticles() {
			self::EnableFeature( 'RelatedArticlesFeature' );
		}
		
		public static function EnableSocialBookmarks() {
			self::EnableFeature( 'SocialBookmarksFeature' );
		}
		
		public static function EnableTags() {
			self::EnableFeature( 'TagsFeature' );
		}
		
		public static function EnablePostInfo() {
			self::EnableFeature( 'PostInfoFeature' );
		}
		
		public static function EnableAuthorGravatar() {
			self::EnableFeature( 'AuthorGravatarFeature' );
		}
		
		public static function EnableFriendlySearchURLs() {
			self::EnableFeature( 'FriendlySearchUrlFeature' );
		}
		
		public static function EnableSkinSwitcher() {
			self::EnableFeature( 'SkinFeatureSwitcher' );
		}
		
		public static function EnableThumbnails() {
			self::EnableFeature( 'ThumbnailsToggle' );
		}
		
		public static function EnableLocalization() {
			self::EnableFeature( 'LocalizationTool' );
		}
		
		public static function EnableMenuLinks() {
			self::EnableFeature( 'MenuLinksFeature' );
		}
		
		public static function EnableStatsCode() {
			self::EnableFeature( 'StatsFeature' );
		}
		
		public static function EnableHomeLayouts() {
			self::EnableFeature( 'HomePageLayoutFeature' );
		}
		
		
		/**
		 * FeatureIsEnabled
		 * 
		 * Determine whether a feature is enabled.
		 * 
		 * @param string $feature_name
		 * @return boolean
		 */
		public static function FeatureIsEnabled( $feature_name ) { 
			
			$success = OnePanelDebug::Track( 'Checking if feature [' . $feature_name . '] is enabled' );
			
			if (! is_array( self::$features_enabled )) self::$features_enabled = array();
			
			if (in_array( $feature_name, self::$features_enabled )) {
				
				OnePanelDebug::Info( 'Feature is enabled' );
				$success->Affirm();
				return true;
				
			}
			else {
				
				OnePanelDebug::Info( 'Feature is not enabled' );
				$success->Affirm();
				return false;
				
			}
			
		}
		
	}