<?php

	/*
	 * ONE THEME LANGUAGE ENGINE
	 * 
	 * Provides access to the language terms globally.
	 * 
	 * @todo There are things in here that are only for 2.0 and yet to be released.
	 * 	such as the need for multiple languages and the tiny amount of smart strings.
	 */

	class OnePanelLanguage {

		private static $instance = null;
		private static $data = null;
		private static $default_language = null;
		private static $hooks; // For wordpress functions and the like

		
		private function __construct() {
			
			$success = OnePanelDebug::Track( 'Starting Language Engine' );
			
			// Build the hooks array
			self::SetHooks();
			
			$success->Affirm();
			
		}
		
		
		/**
	     * Singleton Protection
	     */
	    public function __clone() {
	    	return false;
	    }
	    
		
		public static function Start() {
			
			if (! is_object( self::$instance )) {
				self::$instance = new OnePanelLanguage();
			}
			
		}
		
		public static function GetInstance() {
			
			self::Start();
			return self::$instance;
			
		}
		
		public static function GetConfigData() {
			return self::$data;
		}
		
		public static function CleanupData( $user_data ) {
			
			// I'm just going to run this when debug is on
			if (! OnePanelConfig::UsingDebug()) {
				return true;
			}
			else {

				// TODO just for 2.0
				$laguage_name = self::$default_language;
				
				if (class_exists( 'OnePanel' ))
					$user_language_data = &OnePanel::GetLanguageData( $laguage_name );
				elseif ( class_exists( 'OnePanelTheme' ))
					$user_language_data = &OnePanelTheme::GetLanguageData( $laguage_name );
				else {
					trigger_error( 'One Panel Error: No One Panel object present.', E_ERROR );
					die;
				}

				$config_file_data = &self::$data[$laguage_name];
				if (empty( $config_file_data )) {
					trigger_error( 'One Panel Error: No language data in the config file.', E_ERROR );
					die;
				}

				$change_flag = false;
				
				// Lets check the config data for and additions
				foreach ( $config_file_data as $key => &$term ) {
					
					// Is it new?
					if (! isset( $user_language_data[$key] )) {
						$change_flag = true;
						$user_language_data[$key] = $term;
					}
						
				}
				
				// Now lets check for removals
				foreach ( $user_language_data as $key => &$term ) {
					
					// Has it gone?
					if (! isset( $config_file_data[$key] )) {
						$change_flag = true;
						unset( $user_language_data[$key] );
					}
					
				}
				
				// All done, pack it up and lets get out of here
				if ($change_flag == true) {
					if (class_exists( 'OnePanel' )) 
						OnePanel::PackData();
					elseif (class_exists( 'OnePanelTheme' ))
						OnePanelTheme::PackData();
				}

				
				return true;

			}
			
		}
		
		public static function LoadTerms( $language_name, $terms_array, $default=null ) {
			
			if (OnePanelConfig::UsingDebug()) {
				if (! is_string( $language_name )) die( 'Language Name must be a string' );
				if (! is_array( $terms_array )) die( 'Language Terms must be an array' );
			}
			
			self::$data[$language_name] = $terms_array;
			
			if (self::$default_language == null) {
				self::$default_language = $language_name;
			}
			elseif ($default == true) {
				self::$default_language = $language_name;
			}
			
		}
		
		private static function SetHooks() {
			
			// go go go 
			self::$hooks = array(
				'%RSS_LINK%' => 'OnePanelLanguage::GetRSSLink',
				'%AUTHOR_PROFILE_LINK%' => 'OnePanelLanguage::GetAuthorProfileLink'
			);
			
		}
		
		public static function GetDefaultLanguage() {
			
			if (! is_null( self::$default_language ))
				return self::$default_language;
			else 
				return false;
								
		}
		
		public static function GetText( $term ) {
			
			self::Start();
			
			if (class_exists( 'OnePanelTheme' )) {
				$language_data = OnePanelTheme::GetLanguageData( self::$default_language );
			}
			
			if (empty( $language_data )) $language_data = &self::$data[self::$default_language];
			
			if (! isset( $language_data[$term] )) return false;
			
			// Get the term content
			$content = $language_data[$term];
			
			// check for %THE_HOOKS% with regex
			$matches = array();
			$number_of_matches = preg_match_all( '/%([A-Z_]*)%/', $content, $matches );
			
			if ($number_of_matches > 0) {
				
				$hooks_to_use = &$matches[0];
				
				foreach ($hooks_to_use as $key => &$hook) {
					
					if (array_key_exists( $hook, self::$hooks )) {
						
						$function = self::$hooks[$hook];
						$return_value = call_user_func( $function );
						$content = preg_replace( '/' . $hook . '/', $return_value, $content );
						
					}
					
				}
				
				
			}
			
			$content = stripslashes( $content );
			
			return $content;
			
		}
		
		static public function GetRSSLink() {
			return '<a class="rss-anchor" href="' . get_bloginfo( 'rss2_url', 'display' ) . '">RSS</a>';
		}
		
		static public function GetAuthorProfileLink() {
			
			global $authordata;
			$return = sprintf(
				'<a class="author-profile-anchor" href="%1$s" title="%2$s">%3$s</a>',
				get_author_posts_url( $authordata->ID, $authordata->user_nicename ),
				sprintf( __( 'Posts by %s' ), attribute_escape( get_the_author() ) ),
				get_the_author()
			);
			
			return ($return);
			
		}
		
	}
