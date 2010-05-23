<?php

/*
 * TODO need to check for updates!
 * 
 */

	final class OnePanel {
		
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
		 * 		[2] String Map (Language Data)
		 * 
		 * $trash
		 * If a foreign object is unserialized its name ends up in here to be purged
		 * 
		 * TODO PHPDoc this
		 */ 
		private static $instance = null; // Singleton
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
		 * __construct
		 * 
		 * Adds all important WP Actions and Ajax if necessary/allowed
		 * Includes required files, unpacks the op data, populates the 
		 * laguage data, sets running contsants, checks for invalid data
		 * or data upgrades, registers the modules.
		 * 
		 * Declared private for the singleton pattern to be effective.
		 * 
		 */
		private function __construct() {
			
			$success = OnePanelDebug::Track( 'Building OnePanel object.' );
			
			// Start by adding actions to keep WP happy
			OnePanelDebug::Info( 'Adding inital WP actions' );
			add_action( 'init', array( $this, 'CheckRedirect' )); // Check to see if anything warrants a redirect
			add_action('admin_menu', array( $this, 'AddAdminLink' )); // Add the One Panel page to WordPress Admin
			add_action( 'admin_head', array( $this, 'AddAdminStyles' )); // Add OnePanel styles
			
			
			// Can we break out here if we arent looking at OnePanel?	
			if ($this->IsPointless()) return true;
			
			// Include Panel Class Definitions
			$this->IncludeClasses();
			
			// Retrieve the operational data from the db and check for fresh install
		    if (! $this->ImplementWorkingData() ) {
		    	$this->Install();
		    }
			
		    
			// Set up the language
			$this->PopulateLanguageData();
			
			
			// Check for valid installation and define ONE_PANEL_RUNNING constant
			$this->DetermineRunningState();
			
		    
		    // Check the integrity of the theme data, and attempt upgrade
		    if (! self::DataIsCompatible()) 		$this->DetermineDataIntegrity();
		    elseif($this->DataUpgradeAvailable()) 	$upgraded = $this->AttemptUpgrade();

		    
		    // If the modules are missing from the op data create them, also register the ajax.
			if ($this->IsInstalled()) {
				$this->RegisterModules();
				$this->RegisterAjax();
			}

			
			// All done!
 			$success->Affirm();
 			
		}
		
		
		
		
		/**
		 * GetInstance
		 * 
		 * Singleton Function to get the One Panel Object
		 * 
		 * @return OnePanel
		 */
		public static function GetInstance()
	    {
	    	self::Start();
	        return self::$instance;
	    }
	    
	    
	    
	    
	    /**
	     * Start
	     * 
	     * Singleton shortcut to run the constructor without returning an object
	     * 
	     */
		public static function Start()
	    {
	    	
	    	OnePanelDebug::Info( 'Starting up OnePanel' );
	    	
	    	if (! is_object( self::$instance )) {
	    		
		        // Check for the existence of the OnePanelTheme object
				if (class_exists( 'OnePanelTheme' )) {
					if (is_object( OnePanelTheme::GetInstance() )) {
						
						OnePanelDebug::Error( 'OnePanelTheme instance detected. Possible Hack!' );
						return false;
						
					}
				}
				else {
	            	self::$instance = new OnePanel();
				}
	        }
	    }
	    
	    
	    
	    
	    /**
	     * __clone
	     * 
	     * Singleton Protection
	     * 
	     */
	    public function __clone() {
	    	return false;
	    }
	    
	    
	    
	    
	    /**
	     * IsPointless
	     * 
	     * Used by the constructor to determine whether we need to load up the full
	     * OnePanel environment.
	     * 
	     * @return boolean
	     */
	    private function IsPointless() {

	    	if (! OnePanelLib::InAjaxMode()) {
				if (! OnePanelLib::InConsole()) { 
					OnePanelDebug::Info( 'Breaking out of OnePanel, normal admin page.' );
					return true;
				}
			}
			else {
				if (isset( $_POST['action'] )) {
					
					// Set some useful variables
					$action_string = $_POST['action'];
					$action_has_op_prefix = (substr( $_POST['action'], 0, strlen( ONE_PANEL_AJAX_PREFIX ) ) == ONE_PANEL_AJAX_PREFIX); 
					
					if (! $action_has_op_prefix){
						
						OnePanelDebug::Info( 'Breaking out of OnePanel, normal admin ajax request.' );
						return true;
						
					}
					
				}
			}
			
			return false;
	    	
	    }
	    
	    

	    /**
	     * DetermineDataIntegrity
	     * 
	     * 
	     * @return unknown_type
	     */
	    private function DetermineDataIntegrity() {
	    	
	    	if (! isset( self::$operational_data[0]['theme_name'] )) {
	    		
	    		// Set the theme so we can detect changes, and make sure to pack it
	    		OnePanelDebug::Info( 'No theme saved in the operational data, setting and packing.' );
	    		self::$operational_data[0]['theme_name'] = get_option( 'template' );
	    		self::PackData();
	    		
	    	}
	    	else {
	    		
	    		// The Data is incompatible and we need to request an export.
	    		OnePanelDebug::Info( 'Incompatible data detected.' );
	    		define( 'ONE_PANEL_BAD_DATA', true );
	    		
	    	}
	    	
	    }
	    
	    
	    
	    
	    /**
	     * DetermineRunningState
	     * 
	     * If the operational data reports that the software is installed correctly the
	     * ONE_PANEL_RUNNING constant is set, otherwise it ensures that the operational
	     * data has the installed flag set.
	     *  
	     */
	    private function DetermineRunningState() {
	    	
	   		if ((isset( self::$operational_data[0]['installed'] )) && (self::$operational_data[0]['installed'] == true)) {
	   			
	   			OnePanelDebug::Info( 'OnePanel is running.' );
				define( 'ONE_PANEL_RUNNING', true );
				
			}
			else {
				
				// FAILSAFE
				OnePanelDebug::Info( 'OnePanel is not running.' );
				self::$operational_data[0]['installed'] = false;
				
			}
			
	    }
	    
	    
	    
	    
	    /**
	     * PopulateLanguageData
	     * 
	     * 
	     * @todo One Panel should include some default language sets that are
	     * external to the theme config files, if data exists in the config it
	     * should override the default set. Otherwise the default set should be
	     * used.
	     * @return boolean
	     */
	    private function PopulateLanguageData() {
	    	
	    	// Debug
	    	$success = OnePanelDebug::Track( 'Populating language data.' );
	    	$return_value = false;
	    	

	    	// Check to see if the data is there
	    	if (! isset( self::$operational_data[2] )) {
	    		
	    		$language_data = &self::$operational_data[2]; // Reference for easier reading
	    		$config_data = OnePanelLanguage::GetConfigData();
	    		
	    		
	    		// Run a check for the config data.
	    		if (empty( $config_data )) {
	    			
	    			OnePanelDebug::Error( 'No language data found in the theme config.' );
	    			$return_value = false;
	    			
	    		}
	    		else {
	    			
	    			// Ensure that the data contains an array
					if (is_array( $config_data )) {
						
						$language_data = $config_data; // TEST THIS
						OnePanelLanguage::CleanupData( $language_data );
						$return_value = true;
						
					}
					else {
						
						OnePanelDebug::Error( 'Corrupt language data found in the theme config.' );
	    				$return_value = false;
						
					}
					
	    		}
	    		
	    	}
	    	else {
	    		OnePanelLanguage::CleanupData( self::$operational_data[2] );
	    		$return_value = true;
	    	}
	    	
	    	
	    	// Check to see if we need to throw an error
    		if (defined('ONE_PANEL_RUNNING') && ($return_value == false)) {
    			
    			$success->Fail();
    			trigger_error( 'One Panel Error: No language data found in config file', E_USER_ERROR );
    			
    		}
    		elseif ($return_value == true)
    			$success->Affirm();
	    	
    		return $return_value;
	    	
	    }
	    
	    
	    
	    
	    /**
	     * AllowedToRunAjax
	     * 
	     * Determines whether the license information is properly validated or whether 
	     * the installation is valid depending on what kind of request this is.
	     * 
	     * @return boolean
	     */
	    private function IsInstalled() {
	    	
	    	OnePanelDebug::Info( 'Running functionality check.' );
	    	
    		if ((isset( self::$operational_data[0])) && (self::$operational_data[0]['installed'] === true))
    			return true;
    		else
    			return false;
	    			
	    	
	    }
		
	    
	    
	    
		/**
		 * Pack Data
		 * 
		 * Encrypts operational data. If return is passed it uses a copy
		 * of the operational data and returns the result. If return is not
		 * passed it uses the operational data and stores it in the database.
		 *
		 * @param bool $return 
		 * @return str if {@param $return} is true || null
		 */
    	public static function PackData( $return=false ) {
    	
    		//Debug 
    		$sucess = OnePanelDebug::Track( 'Packing One Panel data' );
    		if ($return == true) OnePanelDebug::Info( 'Export mode detected' );
    		
    		/*
    		 * If we have the return set to true, this is an export
    		 * so we need to get rid of any license data.
    		 * This makes it more portable, and less of a security risk.
    		 * Its important that we keep the theme name and data version however.
    		 * 
    		 */  
    		if ($return == true) {
    			
    			$data = self::$operational_data;
    			
    			unset( $data[0]['installed'] );
				//unset( $data[0]['newest_version'] );
				
				$data[0]['export_date'] = date( 'l jS \o\f F Y \a\t G:i:s' );
				
    		}
    		else {
    			$data = &self::$operational_data;
    		}

    		
	    	$pay_load = serialize( $data );
	    	$pay_load = base64_encode( $pay_load );
	    	$pay_load = strtr( 
	    		$pay_load, 
	    		'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/', 
	    		'mW05fEPtIw+Lajq7AS3Yb/Cdy9Uhovz1skMGB4RJNnQg6iZlOKe28xHTXpuDVcrF'
	    	);
	    	
	    	
	    	// Complete
	    	if ($return == true){
	    		
	    		$sucess->Affirm();
	    		return $pay_load;
	    		
	    	}
	    	else {
				
	    		update_option( 'one_panel_data', $pay_load );
				$sucess->Affirm();
				
	    	}
	    	    	
	    }
	    
	    
	    
	    
	    /**
	     * ImplementWorkingData
	     * 
	     * Takes the op data from the database, unpacks it and uses it as the
	     * working operational data.
	     * 
	     * @return boolean
	     */
	    private function ImplementWorkingData() {
	    	
	    	// Debug
	    	$success = OnePanelDebug::Track( 'Retrieving data from the DB and implementing' );
	    	
	    	// Set the failsafe
		   	ini_set('unserialize_callback_func', 'OnePanel::UnpackProtection');
	    	mysql_set_charset( 'utf8' ); // Unserialize throws a wobbler otherwise
		    	
		    // Get the data
		    $data = $this->UnpackData( get_option( 'one_panel_data' ) );
	    	
		    // Return the failsafe
		    ini_restore( 'unserialize_callback_func' );
		    
	   		if ($data === false) {
	    		return false;
	    	}
	    	else {
	    		
    			// Set the data and take out the trash
    			self::$operational_data = &$data;
    			
    			// Process any trash we may have encoutered
		    	if (count( self::$trash ) > 0) {
		    		
		    		OnePanelDebug::Warn( 'Trash found in OP data.' );
		    		self::EmptyTrash();
		    		
		    	}
		    	
		    	// All done
		    	$success->Affirm();
		    	return true;
	    		
	    	}
		    	
	    }
	    
	    
	    
	    
	    /**
	     * UnpackData
	     * 
	     * Unencrypts passed one panel data and unserializes it.
	     * 
	     * @param str $passed_data
	     * @return array || false
	     */
	    private function UnpackData( $data ) {
	    	
	    	// Debug
	    	$success = OnePanelDebug::Track( 'Unpacking OnePanel data' );
	    	
	    	// Check the input
	    	if ((! is_string( $data )) || empty( $data )) {
	    		OnePanelDebug::Error( 'Invalid data passed.' );
	    		return false;
	    	}
	    	
	    	// Unencrypt the data
	    	$data = strtr( 
	    		$data, 
	    		'mW05fEPtIw+Lajq7AS3Yb/Cdy9Uhovz1skMGB4RJNnQg6iZlOKe28xHTXpuDVcrF', 
	    		'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/'
	    	);

	    	// Enencode the data
	    	$data = base64_decode( $data );
	    	
	    	// Unserialize
	    	$data = @unserialize( $data );
	    	
	    	// Check integrity
	    	if ($data != false) { // Successfully unserialized
	    			
    			$success->Affirm();
    			return $data;
		    	
	    	}
	    	else {
	    		
	    		$success->Fail();
	    		return false;
	    		
	    	}
	    	
	    }
	    
	    
	    
	    
	    /**
	     * Unpack Protection
	     * 
	     * Used to protect us from nasty errors should a foreign object be found
	     * whilst unserializing the data. And foreign objects end up in the $trash
	     * property for cleaning up. This most likely would happen during development
	     * due to class renaming and such.
	     *
	     * @param str $class_name
	     */
	    private static function UnpackProtection($class_name) {
	    	self::$trash[] = $class_name;
	    }
	    
	    
	    /**
	     * Empty Trash
	     * 
	     * Runs after the operational data has been unpacked to purge it of any
	     * foreign object detected during unserialize.
	     *
	     */
	    private static function EmptyTrash() {
	    	
	    	$success = OnePanelDebug::Track( 'Taking out the trash' );
	    	
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
	    	$success->Affirm();
	    	
	    }
	    
	    
	    /**
	     * Add Admin Styles
	     * 
	     * Add the stylesheets and JavaScript to wp_head for the control panel.
	     *
	     */
		public function AddAdminStyles() {
    
	    	// Set Paths
		    $css_path = get_option( 'home' ) . '/wp-content/plugins/OnePanel';
		    $global_js_path = get_option( 'home' ) . '/wp-includes/js';
		    
		    // Add Admin Styles
		    echo '<link rel="stylesheet" href="' . $css_path . '/admin.css" type="text/css" />' . "\n";
		    
		    // Add Prototype & Scriptaculous
		    echo '<script src="' . $global_js_path . '/prototype.js" type="text/javascript"></script>' . "\n";
		    echo '<script src="' . $global_js_path . '/scriptaculous/scriptaculous.js" type="text/javascript"></script>' . "\n";
		    echo '<script src="' . $global_js_path . '/scriptaculous/effects.js" type="text/javascript"></script>' . "\n";
			
			// Add One Panel JavaScript
		    echo '<script src="' . $css_path . '/onepanel.js" type="text/javascript"></script>' . "\n";
		    if (OnePanelLib::InConsole()) echo '<script type="text/javascript">op_admin.run_news = true;</script>' . "\n";
		    
		    // Add IE6 Javascript
		    echo '<!--[if lt IE 7]>' . "\n";
			echo '<script src="http://ie7-js.googlecode.com/svn/version/2.0(beta3)/IE7.js" type="text/javascript"></script>' . "\n";
			echo '<![endif]-->' . "\n";
		    
			// Add IE7 Styles
			echo '<!--[if IE 7]>' . "\n";
			echo '<link href="' . $css_path . '/ie7.css" rel="stylesheet" type="text/css">' . "\n";
			echo '<![endif]-->' . "\n";
			
			// Add IE6 Styles
			echo '<!--[if IE 6]>' . "\n";
			echo '<link href="' . $css_path . '/ie6.css" rel="stylesheet" type="text/css">' . "\n";
			echo '<![endif]-->' . "\n";
			
    	}
    	
    	
    	
    	/**
    	 * Attempt Upgrade
    	 * 
    	 * Upgrades the user data if possible based on the data_version_date timestamp
    	 *
    	 * @return bool - true on upgrade, false if not upgraded.
    	 */
    	private function AttemptUpgrade() {
    		
    		// Debug
    		$success = OnePanelDebug::Track( 'Attempting data upgrade' );
    		
    		// Which versions can we upgrade from?
    		$upgradeable_versions = array( 
    			1224025200, // 2.0 Beta 2
    			1224111600,	// 2.0 Beta 3
    			1237093200	// 2.0
    		);
    		
    		$data_version_date = (int) self::$operational_data[0]['data_version_date'];
    		
    		if (! in_array( $data_version_date, $upgradeable_versions )) {
    			
				define( 'ONE_PANEL_DATA_NOT_UPGRADED', true ); // TODO do we ever use this?
				$success->Fail();
    			return false; // Cannot upgrade.
    			
    		}
    		else {
    			
    			// We can attempt an upgrade.
    			switch ( $data_version_date ) {
    				
    				case 1224025200:	// 2.0 Beta 2
    					
    					// Update the Localization Tool's meta information
    					if (is_object(self::$operational_data[1]['LocalizationModule'])) {
	    					$new_localization_module = new LocalizationModule();
	    					$new_localization_help_text = 			$new_localization_module->GetHelpText();
	    					$new_localization_description = 		$new_localization_module->GetDescription();
	    					$new_localization_short_description = 	$new_localization_module->GetShortDescription();
	    					$new_localization_keywords = 			$new_localization_module->GetKeywords();
	    					
	    					$our_localization_module = &self::$operational_data[1]['LocalizationModule'];
	    					$our_localization_module->SetHelpText( $new_localization_help_text );
	    					$our_localization_module->SetDescription( $new_localization_description );
	    					$our_localization_module->SetShortDescription( $new_localization_short_description );
	    					$our_localization_module->SetKeywords( $new_localization_keywords );
    					}
    					
    				case 1224111600:	// 2.0 Beta 3
    				
    					// Update the descriptions for the skins module.
    					if (is_object(self::$operational_data[1]['SkinModule'])) {
	    					$new_skins_module = new SkinModule();
	    					$new_skins_description = 				$new_skins_module->GetDescription();
	    					$new_skins_short_description = 		$new_skins_module->GetShortDescription();
	    					
	    					$our_skins_module = &self::$operational_data[1]['SkinModule'];
	    					$our_skins_module->SetDescription( $new_skins_description );
	    					$our_skins_module->SetShortDescription( $new_skins_short_description );
    					}
    					
    					break;
    				
    			}
    			
    			// Set the new data_version and data_version_date
    			self::$operational_data[0]['data_version'] = ONE_PANEL_VERSION;
    			self::$operational_data[0]['data_version_date'] = ONE_PANEL_VERSION_DATE;
    			
    			// Pack the new data
    			self::PackData();
    			
				define( 'ONE_PANEL_DATA_UPGRADED', true );
				$success->Affirm();
    			return true; // Upgrade Successful
    			
    		}
    		
    	}
    	
    	
    	
    	/**
    	 * Add Admin Page
    	 * 
    	 * Depending on whether any flags have been thrown up before this point. This method
    	 * will add the right page to the menu entry 'One Panel'
    	 *
    	 * @todo consider renaming this function
    	 */
    	public function AddAdminLink() {
    		add_menu_page('One Panel', 'One Panel', 8, 'OnePanel', array( $this, 'Redirect' ));
    	}
    	
    	public function Redirect() {
    		
    		if (defined( 'ONE_PANEL_NO_CONFIG' )) {
				$this->PrintNoConfig();
			}
		    elseif (defined( 'ONE_PANEL_BAD_DATA' )) {
		    	$this->PrintIncompatible();
		    }
		    elseif ((defined( 'ONE_PANEL_DATA_UPGRADED' )) || (defined( 'ONE_PANEL_DATA_NOT_UPGRADED' ))) {
		    	$this->PrintDataUpgraded();
		    }
    		else {
    			$this->PrintMain();
    		}
    		
    	}
    	
	    
	    
	    /**
	     * Check Redirect
	     * 
	     * Checks if we need to run any actions before WordPress sends the headers.
	     * The $_GET['op_action'] is the flag on which we operate.
	     *
	     */
	    public function CheckRedirect() {
	    	
	    	if (isset( $_GET['op_action'] )) {
	    		
		    	switch ($_GET['op_action']) {
		    		case 'download':
		    			$this->DownloadBackup();
		    		break;
		    		
		    	}
		    	
	    	}
	    }
	    
	    
	    /**
	     * Data is Compatible
	     * 
	     * A quick test to see if the operational data is compatible with the 
	     * current theme.
	     *
	     * @return bool
	     */
	    public static function DataIsCompatible() {
	    	
	    	$data_theme = false;
	    	if (isset(self::$operational_data[0]['theme_name'])) $data_theme = self::$operational_data[0]['theme_name'];
	    	
	    	$running_theme = get_option( 'template' ); 
	    	
	    	if ($data_theme == $running_theme) {
	    		return true;
	    	}
	    	else {
	    		return false;
	    	}
	    	
	    }
	    
	   
	    /**
	     * IncludeClasses
	     * 
	     * Includes all the class files for the modules and features.
	     *
	     * @return boolean
	     */
	    private function IncludeClasses() {
	    	
	    	$success = OnePanelDebug::Track( 'Including OnePanel integrals' );
	    	
		   	OnePanelLib::RequireFileOnce( ONE_PANEL_DIR . '/panel/feature.php' );
		    OnePanelLib::RequireFileOnce( ONE_PANEL_DIR . '/panel/module.php' );
		    OnePanelLib::RequireFileOnce( ONE_PANEL_DIR . '/panel/skin.php' );
		    OnePanelLib::RequireFileOnce( ONE_PANEL_DIR . '/panel/highlights.php' );
		    OnePanelLib::RequireFileOnce( ONE_PANEL_DIR . '/panel/contentcontrol.php' );
		    OnePanelLib::RequireFileOnce( ONE_PANEL_DIR . '/panel/thumbnails.php' );
		    OnePanelLib::RequireFileOnce( ONE_PANEL_DIR . '/panel/postheader.php' );
		    OnePanelLib::RequireFileOnce( ONE_PANEL_DIR . '/panel/postfooter.php' );
		    OnePanelLib::RequireFileOnce( ONE_PANEL_DIR . '/panel/hotconversation.php' );
		    OnePanelLib::RequireFileOnce( ONE_PANEL_DIR . '/panel/feedburner.php' );
		    OnePanelLib::RequireFileOnce( ONE_PANEL_DIR . '/panel/seo.php' );
		    OnePanelLib::RequireFileOnce( ONE_PANEL_DIR . '/panel/advertising.php' );
		    OnePanelLib::RequireFileOnce( ONE_PANEL_DIR . '/panel/featuredvideo.php' );
		    OnePanelLib::RequireFileOnce( ONE_PANEL_DIR . '/panel/menulinks.php' );
		    OnePanelLib::RequireFileOnce( ONE_PANEL_DIR . '/panel/stats.php' );
		    OnePanelLib::RequireFileOnce( ONE_PANEL_DIR . '/panel/homepagelayouts.php' );
		    OnePanelLib::RequireFileOnce( ONE_PANEL_DIR . '/panel/localization.php' );
		    
		    $success->Affirm();
		    return true;
		    
	    }
	    
	    
	   /**
	    * RegisterModules
	    * 
	    * Sets all the modules up for use
	    * 
	    * @return boolean
	    */
	    private function RegisterModules() {

	    	// Debug
	    	$success = OnePanelDebug::Track( 'Registering modules' );
	    	
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
		
			$success->Affirm();
			return true;
			
	    }
	    
	    
	    
	    
	    /**
	     * RegisterAjax
	     * 
	     * Adds all the ajax calls for One Panel using wordpress' add_action function. 
	     *
	     * @todo Move all the module specific ones to inside the module definitions.
	     * @todo Also we should probably not register them if the module is not enabled.
	     */
	    private function RegisterAjax() {
	    	
	    	$modules = &self::$operational_data[1];
	    	
	    	// One Panel Popups
	    	add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'LicenseInfoRender', array( $this, 'PrintLicenseInfo' ) );
	    	add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'ExportRender', array( $this, 'PrintExportWindow' ) );
	    	add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'ImportRender', array( $this, 'PrintImportWindow' ) );
	    	add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'ImportSwitchMode', array( $this, 'SwitchImportMode' ) );
	    	add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'DoImport', array( $this, 'DoImport' ) );
	    	add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'DoExport', array( $this, 'DoExport' ) );
	    	add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'ImportDoUpload', array( $this, 'ImportDoUpload' ) );

	    	// Search stuff
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'SearchModules', array( $this, 'PrintSearchResults' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'SearchCategory', array( $this, 'PrintCategoryResults' ) );
			
			// ToolTip stuff
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'PopulateToolTip', array( $this, 'GetToolTipContent' ) );
			
			// Backup and Restore
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'FlushData', array( $this, 'FlushData' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'BackupData', array( $this, 'CreateBackup' ) );
			
			// News Ticker
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'GetNews', array( $this, 'PrintNewsTick' ) );
	    	
			// Module specific
	    	add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'SkinModuleRender', array( $modules['SkinModule'], 'Render' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'SkinFeatureSwitcherActivate', array( $modules['SkinModule']->features['SkinFeatureSwitcher'], 'Activate' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'SkinFeatureSwitcherDeactivate', array( $modules['SkinModule']->features['SkinFeatureSwitcher'], 'Deactivate' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'SkinModuleSwitchSkin', array( $modules['SkinModule'], 'SwitchSkin' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'SkinModuleSwitchDefault', array( $modules['SkinModule'], 'SwitchDefault' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'SkinModuleDoUpload', array( $modules['SkinModule'], 'DoUpload' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'SkinModuleResetImage', array( $modules['SkinModule'], 'ResetImage' ) );
		    
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'ContentControlRender', array( $modules['ContentControl'], 'Render' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'PostTitleLimitFeatureActivate', array( $modules['ContentControl']->features['PostTitleLimitFeature'], 'Activate' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'PostTitleLimitFeatureDeactivate', array( $modules['ContentControl']->features['PostTitleLimitFeature'], 'Deactivate' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'ContentControlFeatureSwitch', array( $modules['ContentControl']->features['ContentControlFeature'], 'ChangeMode' ) );
			
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'AdvertisingModuleRender', array( $modules['AdvertisingModule'], 'Render' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'AdvertisingSwitchBlock', array( $modules['AdvertisingModule'], 'SwitchBlock' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'AdvertisingSwitchEntryMode', array( $modules['AdvertisingModule'], 'SwitchEntryMode' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'AdvertisingSavePubID', array( $modules['AdvertisingModule'], 'SavePubID' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'AdvertisingSaveChannel', array( $modules['AdvertisingModule'], 'SaveChannel' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'AdvertisingSaveCode', array( $modules['AdvertisingModule'], 'SaveCode' ) );
			
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'ThumbnailsRender', array( $modules['Thumbnails'], 'Render' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'ThumbnailsToggleActivate', array( $modules['Thumbnails']->features['ThumbnailsToggle'], 'Activate' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'ThumbnailsToggleDeactivate', array( $modules['Thumbnails']->features['ThumbnailsToggle'], 'Deactivate' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'ThumbnailsSwitchMode', array( $modules['Thumbnails'], 'SwitchMode' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'ThumbnailsSearchPosts', array( $modules['Thumbnails'], 'SearchPosts' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'ThumbnailsScanPost', array( $modules['Thumbnails'], 'ScanPost' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'ThumbnailsSetPost', array( $modules['Thumbnails'], 'SetPost' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'ThumbnailsDoUpload', array( $modules['Thumbnails'], 'DoUpload' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'ThumbnailsRipImage', array( $modules['Thumbnails'], 'RipImage' ) );

			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'PostHeaderRender', array( $modules['PostHeader'], 'Render' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'PostInfoFeatureActivate', array( $modules['PostHeader']->features['PostInfoFeature'], 'Activate' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'PostInfoFeatureDeactivate', array( $modules['PostHeader']->features['PostInfoFeature'], 'Deactivate' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'AuthorGravatarFeatureActivate', array( $modules['PostHeader']->features['AuthorGravatarFeature'], 'Activate' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'AuthorGravatarFeatureDeactivate', array( $modules['PostHeader']->features['AuthorGravatarFeature'], 'Deactivate' ) );
			
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'PostFooterRender', array( $modules['PostFooter'], 'Render' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'AuthorProfileLinkFeatureActivate', array( $modules['PostFooter']->features['AuthorProfileLinkFeature'], 'Activate' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'AuthorProfileLinkFeatureDeactivate', array( $modules['PostFooter']->features['AuthorProfileLinkFeature'], 'Deactivate' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'RSSLinkFeatureActivate', array( $modules['PostFooter']->features['RSSLinkFeature'], 'Activate' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'RSSLinkFeatureDeactivate', array( $modules['PostFooter']->features['RSSLinkFeature'], 'Deactivate' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'RelatedArticlesFeatureActivate', array( $modules['PostFooter']->features['RelatedArticlesFeature'], 'Activate' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'RelatedArticlesFeatureDeactivate', array( $modules['PostFooter']->features['RelatedArticlesFeature'], 'Deactivate' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'SocialBookmarksFeatureActivate', array( $modules['PostFooter']->features['SocialBookmarksFeature'], 'Activate' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'SocialBookmarksFeatureDeactivate', array( $modules['PostFooter']->features['SocialBookmarksFeature'], 'Deactivate' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'TagsFeatureActivate', array( $modules['PostFooter']->features['TagsFeature'], 'Activate' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'TagsFeatureDeactivate', array( $modules['PostFooter']->features['TagsFeature'], 'Deactivate' ) );			
			
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'HotConversationRender', array( $modules['HotConversation'], 'Render' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'HotConversationToggleActivate', array( $modules['HotConversation']->features['HotConversationToggle'], 'Activate' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'HotConversationToggleDeactivate', array( $modules['HotConversation']->features['HotConversationToggle'], 'Deactivate' ) );
			
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'FeedBurnerRender', array( $modules['FeedBurner'], 'Render' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'FeedBurnerToggleActivate', array( $modules['FeedBurner']->features['FeedBurnerToggle'], 'Activate' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'FeedBurnerToggleDeactivate', array( $modules['FeedBurner']->features['FeedBurnerToggle'], 'Deactivate' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'FeedBurnerSaveID', array( $modules['FeedBurner'], 'SaveID' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'FeedBurnerSaveURL', array( $modules['FeedBurner'], 'SaveURL' ) );
			
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'SEORender', array( $modules['SEO'], 'Render' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'FriendlySearchUrlFeatureActivate', array( $modules['SEO']->features['FriendlySearchUrlFeature'], 'Activate' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'FriendlySearchUrlFeatureDeactivate', array( $modules['SEO']->features['FriendlySearchUrlFeature'], 'Deactivate' ) );
			
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'HighlightsModuleRender', array( $modules['HighlightsModule'], 'Render' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'HighlightsSwitchHighlight', array( $modules['HighlightsModule'], 'SwitchHighlight' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'HighlightsSearchPosts', array( $modules['HighlightsModule'], 'SearchPosts' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'HighlightsSetPost', array( $modules['HighlightsModule'], 'SetPost' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'HighlightsSetCategory', array( $modules['HighlightsModule'], 'SetCategory' ) );
			
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'FeaturedVideoModuleRender', array( $modules['FeaturedVideoModule'], 'Render' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'FeaturedVideoFeatureActivate', array( $modules['FeaturedVideoModule']->features['FeaturedVideoFeature'], 'Activate' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'FeaturedVideoFeatureDeactivate', array( $modules['FeaturedVideoModule']->features['FeaturedVideoFeature'], 'Deactivate' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'FeaturedVideoSwitchEntryMode', array( $modules['FeaturedVideoModule'], 'SwitchEntryMode' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'FeaturedVideoSaveYouTube', array( $modules['FeaturedVideoModule']->features['FeaturedVideoFeature'], 'SaveYouTube' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'FeaturedVideoSaveEmbed', array( $modules['FeaturedVideoModule']->features['FeaturedVideoFeature'], 'SaveEmbed' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'FeaturedVideoGetPreview', array( $modules['FeaturedVideoModule'], 'GetPreview' ) );
			
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'MenuLinksModuleRender', array( $modules['MenuLinksModule'], 'Render' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'MenuLinksModuleSwitchLink', array( $modules['MenuLinksModule'], 'SwitchLink' ) );
			
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'StatsModuleRender', array( $modules['StatsModule'], 'Render' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'StatsFeatureSave', array( $modules['StatsModule']->features['StatsFeature'], 'Save' ) );
			
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'HomePageLayoutModuleRender', array( $modules['HomePageLayoutModule'], 'Render' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'HomePageLayoutModuleSwitchLayout', array( $modules['HomePageLayoutModule'], 'SwitchLayout' ) );
			
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'LocalizationModuleRender', array( $modules['LocalizationModule'], 'Render' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'LocalizationSearch', array( $modules['LocalizationModule'], 'Search' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'LocalizationEdit', array( $modules['LocalizationModule'], 'Edit' ) );
			add_action( 'wp_ajax_' . ONE_PANEL_AJAX_PREFIX . 'LocalizationSave', array( $modules['LocalizationModule'], 'Save' ) );
			
	    }
	    
	    
	    /**
	     * Register Module
	     * 
	     * Detects whether or not a module is already contained within the operational
	     * data. And creates the module if it is not.
	     * 
	     * This will not work for modules that there are supposed to be more than one of
	     * as it works on the class name.
	     *
	     * @param str $class_name
	     */
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
		 * Search for Module
		 * 
		 * Searches for modules that have keywords that match the keywords 
		 * in the $_POST['keywords'] variable.
		 *
		 * @uses $_POST['keywords']
		 * @return array
		 * @todo Code for BAD MATCH.
		 */
		private static function SearchForModule() {
 
			$search_term = mysql_real_escape_string( strtolower( $_POST['keywords'] ));
			$modules = &self::$operational_data[1];
			
			$matches = array();
			$good_matches = array();
			$ugly_matches = array();
			
			foreach ($modules as $key => &$module) {
				
				$module_keywords = $module->GetKeywords();

				if (! is_array( $module_keywords )) continue;
				
				// Check if any of the keywords match exactly (GOOD MATCH)
				if (in_array( $search_term, $module_keywords )) {
					
					if ((! in_array( $module, $matches ) && ($module->IsEnabled()) == true)) {
						
						$matches[] = &$module;
						$good_matches[] = &$module;
						
					}
					
				}
				
				// Check if any of the words in the term match (UGLY MATCH)
				$detailed_terms = explode( ' ', $search_term );
				
				foreach ($detailed_terms as $key => &$term) {
					
					if (in_array( $term, $module_keywords )) {
						
						if ((! in_array( $module, $matches ) && ($module->IsEnabled()) == true)) {
							
							$matches[] = &$module;
							$ugly_matches[] = &$module;
							
						}
						
					}
					
				}
				
				// Try correcting spelling and matching each keyword (BAD MATCH)
				
				
			}
			
			// Give back an array of matching modules
			unset($matches);
			return array_merge( $good_matches, $ugly_matches );
			
		}

		
		/**
		 * Search for Categories
		 * 
		 * Search for modules that have category properties matching 
		 * $_POST['category']
		 * 
		 * @return array
		 */
		private function SearchForCategories( $category=null ) {
			
			$modules = &self::$operational_data[1];
			
			$matches = array();
			
			foreach ($modules as $key => &$module) {
				
				$module_categories = $module->GetCategories();
						
				if (! is_array( $module_categories )) continue;
						
				if (in_array( $category, $module_categories )) {
									
					if ((! in_array( $module, $matches ) && ($module->IsEnabled()) == true)) {
						$matches[] = &$module;
					}
					
				}
				
			}
			
			return $matches;
			
		}
		
		
		
		/**
		 * Create Backup
		 * 
		 * Ajax method for 'Recent Theme Switch' that Creates a backup of the current operational 
		 * data in the uploads/OnePanelBackups directory if the directory 
		 * is writable. Then returns the HTML.
		 *
		 * @todo Use GenerateBackupFile()
		 */
		public function CreateBackup() {
			
			$data = self::PackData( true );
			
			$op_backup_dir = self::GetOnePanelBackupDir();
			$op_file_name = date('Y-m-d-H-i-s') . '.opd';
			$op_file_target = $op_backup_dir . '/' . $op_file_name;
			$op_file_url = self::GetOnePanelBackupUrl() . '/' . $op_file_name;
			
			if (! file_exists( $op_backup_dir )) {
				$created = mkdir( $op_backup_dir ); // Try and create it
			}
			
			if (is_writable( $op_backup_dir )) {
				
				$fp = fopen( $op_file_target, 'w' );
				
				if ($fp) {
					
					fwrite( $fp, $data );
					fclose( $fp );

					// Clear all data that we dont need and update the theme name.
					self::$operational_data[0]['theme_name'] = get_option( 'template' );
					unset( self::$operational_data[1] );
					self::PackData();
					
					if (function_exists( 'admin_url' )) $admin_url = admin_url();
					else $admin_url = get_option( 'home' ) . '/wp-admin/';
					
					$content['file_link']  = '<p><strong>Backup file created in:</strong> <a href="' . $admin_url . 'admin.php?page=OnePanel&op_action=download&file_name=' . $op_file_name . '"><span style="font-style:italic;">' . $op_file_url . '</span></a>.</p>' . "\n";
					$content['continue_link'] = '<p style="padding-top:10px;"><a class="continuebutton" href="' . $admin_url . 'admin.php?page=OnePanel"></a></p><div style="clear:both;"></div>' . "\n";
					
					die( json_encode( $content ) );
				}
				
			}
			
		}
		
		private function GenerateBackupFile() {
			
			$return_array = array();
			
			// Set up some variables
			$user_data = self::PackData( true );
			$op_backup_dir = self::GetOnePanelBackupDir();
			$opd_file_name = date('Y-m-d-H-i-s') . '.opd';
			$opd_file_target = $op_backup_dir . '/' . $opd_file_name;
			$opd_file_url = self::GetOnePanelBackupUrl() . '/' . $opd_file_name;
			
			// Make sure the backup directory exists
			if (! file_exists( $op_backup_dir )) {
				mkdir( $op_backup_dir ); // Try and create it
			}
			
			// Check we can write to the backup directory
			if (! is_writable( $op_backup_dir )) {
				$return_array['error'] = 'One Panel cannot write to the backup directory.';
				$return_array['status'] = false;
			}
			else {
				
				$fp = fopen( $opd_file_target, 'w' );
				
				if (! $fp) {
					// Nothing wrong with a double check
					$return_array['error'] = 'One Panel cannot write to the backup directory.';
					$return_array['status'] = false;
				}
				else {
					
					// Write to the file and close the file pointer
					fwrite( $fp, $user_data );
					fclose( $fp );
					
					// All done
					$return_array['status'] = true;
					$return_array['file_url'] = $opd_file_url;
					$return_array['file_path'] = $opd_file_target;
					
				}
				
			}
			
			return $return_array;
			
		}
		
		public function DoExport() {
			
			$response = array();
			$backup_result = $this->GenerateBackupFile();
			
			if (! isset( $backup_result['status'] )) {
				$response['status'] = false;
				$response['error'] = 'One Panel Error: The backup generator failed unexpectedly. Please report this at http://www.one-theme.com/beta.';
			}
			else {
				
				if ($backup_result['status'] == false) {
					$response['status'] = false;
					$response['error'] = $backup_result['error'];
				}
				else {
					
					$response['status'] = true;
					
					if (function_exists( 'admin_url' )) $admin_url = admin_url();
					else $admin_url = get_option( 'home' ) . '/wp-admin/';
					
					$file_name = basename( $backup_result['file_path'] );
					
					$response['content'] = '<p><strong>Backup file created in:</strong> <a href="' . $admin_url . 'admin.php?page=OnePanel&op_action=download&file_name=' . $file_name . '"><span style="font-style:italic;">' . $backup_result['file_url'] . '</span></a>.</p>' . "\n";
					
				}
				
				
			}
			
			die( json_encode( $response ));
			
		}
		
		/**
		 * Download Backup
		 * 
		 * Forces the browser to download a backup (.opd) file.
		 * Can only be called from CheckRedirect as will not work if the headers
		 * have already been sent by WordPress.
		 *
		 */
		public function DownloadBackup() {
			
			if (isset($_GET['file_name'])) {
				
				$op_backup_dir = self::GetOnePanelBackupDir();
				$op_file_name = $_GET['file_name'];
				$op_file_target = $op_backup_dir . '/' . $op_file_name;
				
				if (! substr( $op_file_name, -4 ) == '.opd') die;
				if (file_exists( $op_file_target )) {
				
					header('Content-type: application/force-download');
					header('Content-Disposition: attachment; filename="'.$op_file_name.'"');
					readfile($op_file_target);
					die;
				}
				else {
					die;
				}
			}
			else {
				die;
			}
		}
		
		
		/**
		 * Flush Data
		 * 
		 * Ajax function that clears all One Panel operational data. Used for debugging
		 * and theme switching.
		 *
		 */
		public function FlushData() {
			
			if (isset( $_POST['keep_license'] )) {
				
				unset( self::$operational_data[0]['theme_name'] );
				unset( self::$operational_data[1] );
				
				self::PackData();
				
			}
			else {
				
				global $wpdb;
				$sql = 'DELETE FROM ' . DB_NAME . '.' . $wpdb->prefix . 'options WHERE option_name="one_panel_data"';
				$result = mysql_query( $sql );
				
				if (! $result ) die( 'Database Error!' );
				else die( true );
				
			}
			
			die( true );
			
		}
			
			
		/*
		 * HTML RENDERING FUNCTIONS
		 * 
		 * All the functions that print something to the admin panel 
		 * appear below.
		 *  
		 */
		

		/**
		 * Print Main
		 *
		 * Prints the main One Panel interface. 
		 *
		 */
		public function PrintMain() {
	   		?>
			<div id="OnePanelContainer" align="center">
            <div id="OnePanelInner">
            <div id="Header">
            	<div id="Logo">
            		<img border="0" src="<?php echo get_option('home'); ?>/wp-content/plugins/OnePanel/images/default/logo.gif"/>
            	</div>
                <div id="TopLinks">
                	<ul>
                		<li><a href="http://www.one-theme.com/support/" target="_blank" style="margin:0;">Customer Support</a></li>
                    	<li><a href="http://members.one-theme.com" target="_blank">Members Area</a></li>
                	</ul>
                </div>
                <div id="ImpExp">
                	<ul>
                    	<li class="import"><a href="http://www.one-theme.com/beta/" target="_blank">Report Bug</a></li>
                    	<li class="export"><a href="javascript:;" onclick="op_admin.FlushData()">Uninstall</a></li>
                    	<li class="import"><a href="javascript:;" onclick="op_admin.AjaxRender('opcp_ImportRender')">Import</a></li>
                    	<li class="export"><a href="javascript:;" onclick="op_admin.AjaxRender('opcp_ExportRender')">Export</a></li>
                	</ul>
                </div>
            </div>
	   		<div id="Wrap">
				<div id="Sidebar">
            		<?php $this->PrintCategoryMenu(); ?>
            	</div>
             <div id="Content">
             	<div id="Recently_Used">
                	<div id="Heading">
                    	<div class="heading_img" style="border-right:1px solid #606060;"><img src="<?php echo get_option('home'); ?>/wp-content/plugins/OnePanel/images/default/panel/headings/recently.png" border="0" /></div>
                    </div>
                    <div id="Items">
                    	<?php $this->PrintUseful(); ?>
                    </div>
                </div>
             <div id="Right">
                	<div id="SearchTab">
                    	<div class="heading_img"><img src="<?php echo get_option('home'); ?>/wp-content/plugins/OnePanel/images/default/panel/headings/search.png" border="0" /></div>
                    </div>
                	<div id="SearchBar">
                        	<form action="javascript:;" onsubmit="op_admin.Search.DoSearch( $F('search_box'), 1 )">
								<input name="search_box" id="search_box" type="text" onblur="if(this.value==''){this.value='Type what feature you\'re looking for...'}" onfocus="if(this.value=='Type what feature you\'re looking for...'){this.value=''}" value="Type what feature you're looking for..." />
							</form>
                            <div class="SubmitSearch"><a href="javascript:;" onclick="op_admin.Search.DoSearch( $F('search_box') )">&nbsp;</a></div>
                    </div>
                    <div id="SearchRight">
                    </div>
                <div id="SearchWrapper">
                <div id="SearchResults" align="center">
                	<?php
                		echo $this->PrintCategoryResults( true, 'Appearance' );
                	?>
				</div>
			</div>
					
	   		<?php
	   		
	   		// Print the tooltip placeholder
	   		$this->PrintToolTip();
	   		
	   		?>
		</div>
		</div>
		</div>
           <?php
	   		
	   		// Print Footer
	   		$this->PrintFooter();
	   			   		
	   		// Print Action Frame
	   		?>
    </div>
    </div>
    <!-- Close Container-->
     
				<div id="overlay" class="overlay" style="display:none;"><div id="pop_loading"></div></div>
				<div id="action-frame" class="popup" style="display:none;">
					<img class="plogo" src="<?php echo get_option('home'); ?>/wp-content/plugins/OnePanel/images/default/panel/logo.gif" alt="OnePanel" /> <!-- Logo -->
					<a class="pbutton" href="javascript:;" onclick="op_admin.HidePopup()" href="#"><img src="<?php echo get_option('home'); ?>/wp-content/plugins/OnePanel/images/default/panel/button.gif" alt="X" /></a> <!-- Close Button -->
					<div id="action-title" class="ptitle"><div></div></div> <!-- Heading Text -->
					<div id="action-content" class="pcontent"><!-- Content --></div>
				</div>
				
				<!-- Error Console -->
				<?php $this->PrintDebugConsole(); ?>
	   		<?php
	   		
			
		}
		
		
		/**
		 * Print Category Menu
		 * 
		 * Prints the categories.
		 * 
		 * @todo Make this a bit smarter so that it searches the actual modules for their category properties.
		 */
		private function PrintCategoryMenu() {
			?>
					<div class="menu-title"><div class="heading_img"><img src="<?php echo get_option('home'); ?>/wp-content/plugins/OnePanel/images/default/panel/headings/categories.png" border="0" /></div></div>
					<ul>
						<li><a href="javascript:;" onclick="op_admin.Search.DoCategory( 'Appearance', 1 )">Appearance</a></li>
						<li><a href="javascript:;" onclick="op_admin.Search.DoCategory( 'SEO', 1 )">SEO</a></li>
						<li><a href="javascript:;" onclick="op_admin.Search.DoCategory( 'Featured', 1 )">Featured</a></li>
						<li><a href="javascript:;" onclick="op_admin.Search.DoCategory( 'Advertising', 1 )">Advertising</a></li>
						<li><a href="javascript:;" onclick="op_admin.Search.DoCategory( 'Menu Links', 1 )">Menu Links</a></li>
						<li><a href="javascript:;" onclick="op_admin.Search.DoCategory( 'Misc', 1 )">Misc</a></li>
					</ul>
			<?php
		}
		
		
		/**
		 * Print Useful
		 *
		 * Prints the thumbnails for each module that appears in the Recently Used area.
		 * 
		 * @todo Make this actually reference the modules that are recently used, and not
		 * just the ones we have chosen.
		 */
		private function PrintUseful() {
			
			$modules = self::$operational_data[1];
			
			// Sort the modules by view count.
			uasort( $modules, array( 'OnePanelModule', 'CompareViewcount' ) );
			
			$module_keys = array_keys( $modules );
			
			$i 			= 0; // Counts Modules Processed
			$printed 	= 0; // Counts Modules Printed
			
			while ($printed < 9) {
				
				$module = &$modules[$module_keys[$i]];
				if ($module->IsEnabled() == false) { 

					$i++;
					if (! isset( $modules[$module_keys[$i]])) break; 
					
				}
				else {
					?>
	                    <div class="feature-box">
							<div class="thumbnails" id="<?php echo get_class( $module ); ?>">
								<a href="javascript:;" onclick="op_admin.AjaxRender('opcp_<?php echo get_class( $module ); ?>Render')"></a>
								<div class="thumb_info"><a href="javascript:;" title="More Info..." onmouseout="op_admin.ToolTip.Hide()" onclick="op_admin.ToolTip.Show('<?php echo get_class( $module ); ?>')"><img border="0" src="<?php echo get_option('home'); ?>/wp-content/plugins/OnePanel/images/default/icons/spacer.gif"/></a></div>
							</div>
	                        <h2><?php echo $module->GetTitle(); ?></h2>
	                    </div>
					<?php
					$i++;
					$printed++;
				}
			}
		}
		
		
		/**
		 * Print Footer
		 * 
		 * Prints the footer of the panel.
		 *
		 */
		private function PrintFooter() {
			?>
				<div id="OP_Footer">
                	<div id="Footer_left"></div>
                	<div id="Footer_right"></div>
                	<div id="Footer_bg">
                		<img src="<?php echo get_option('home'); ?>/wp-content/plugins/OnePanel/images/default/icons/help_icon.png"/>
                		<div id="f_left"></div>
                		<div id="f_bg">
                        	<div id="news-ticker-content"><?php echo $this->GetNewsTick(); ?></div>
 						</div>
                		<div id="f_right"></div>
                	</div>
                	<div id="copyright">&copy; 2008 - 2009 One Theme LTD.</div>
				</div>
                
			<?php
		}
		
		
		/**
		 * Print No Config
		 * 
		 * This page is displayed instead of PrintMain if the configuration file 
		 * is missing from the active theme's folder.
		 *
		 */
		public function PrintNoConfig() {
			
			OnePanelInstaller::PrintHeader();
			
			echo '<div style="background:#f8f8f8;border:1px solid #eee;padding:15px;line-height:34px;-moz-border-radius:6px;-webkit-border-radius:6px;">'. "\n";
			echo 	'<h2 style="border:1px solid #eee;background:#FFF url(http://www.one-theme.com/images/onepanel/icons/title_bg.png) no-repeat right;font-size:14px;color:#555;-moz-border-radius:3px;-webkit-border-radius:3px;margin-top:0;height:22px;line-height:22px;">One Panel Theme</h2>'. "\n";
			echo 	'<p style="line-height:26px;">You do not currently have a One Panel compatible theme active. If you do however, and you are still seeing this screen; it is possible that the themes config file is missing, and restoring the theme\'s onepanel_config.php file should solve the issue.</p>' . "\n";
			echo '</div>' . "\n";
			
			OnePanelInstaller::PrintFooter();
			
		}
		
		
		/**
		 * Print News Tick
		 * 
		 * Ajax function to grab a piece of news.
		 *
		 */
		public function PrintNewsTick() {
			die( $this->GetNewsTick() );
		}
		
		
		/**
		 * Get the backup directory
		 * 
		 * This is due to inconsitencies in wp_upload_dir() between version 2.5 and 2.6+.
		 *
		 * @return str
		 */
		public static function GetOnePanelBackupDir() {
			
			$wp_uploads_data = wp_upload_dir(); 
			
			if (! isset( $wp_uploads_data['basedir'] )) { // This is from the 2.5 series
				
				$upload_root = str_replace( $wp_uploads_data['subdir'], '', $wp_uploads_data['path'] );
				$backup_directory = $upload_root . '/OnePanelBackups';
				
			}
			else {
				$upload_root = $wp_uploads_data['basedir'];
				$backup_directory = $wp_uploads_data['basedir'] . '/OnePanelBackups';
			}
			
			// Create it if it doesnt exist
			if (! file_exists( $backup_directory )) {
				if (is_writable( $upload_root )) {
					mkdir( $upload_root . '/OnePanelBackups' );
				}
			}
			
			
			return  $backup_directory;
		}
		
		/**
		 * Get the backup url
		 * 
		 * This is due to inconsitencies in wp_upload_dir() between version 2.5 and 2.6+.
		 *
		 * @return str
		 */
		public static function GetOnePanelBackupUrl() {
			
			$wp_uploads_data = wp_upload_dir(); 
			
			if (! isset( $wp_uploads_data['baseurl'] )) { // This is from the 2.5 series
				
				$upload_root = str_replace( $wp_uploads_data['subdir'], '', $wp_uploads_data['url'] );
				$backup_directory = $upload_root . '/OnePanelBackups';
				
			}
			else {
				$backup_directory = $wp_uploads_data['baseurl'] . '/OnePanelBackups';
			}
			
			
			return  $backup_directory;
		}
		
		/**
		 * Print Incompatible
		 * 
		 * This page is presented instead of PrintMain if the operational data is 
		 * incompatible with the active theme.
		 *
		 */
		public function PrintIncompatible() {

			OnePanelInstaller::PrintSimpleHeader();
			
			// Get WordPress' uploads data
			$backup_directory = self::GetOnePanelBackupDir();
			
			if (is_writable( $backup_directory )) {
			
				$content  = 'One Panel detected that you recently switched themes. Do you want to backup your One Panel Data for \'' . self::$operational_data[0]['theme_name'] . '\' now, or discard it?' . "\n";
				$content .= '<div id="backup-area" align="center"></div>' . "\n";
				$content .= '<div align="right" style="border-top:1px dashed #e1e1e1;padding:25px 0 0 0;margin:5px 0 0 0;">' . "\n";
				$content .= 	'<a class="backupbutton" href="javascript:;" onclick="op_admin.Backup()"></a>' . "\n";
				$content .= 	'<a class="discardbutton" href="javascript:;" onclick="op_admin.FlushData(\'' . self::$operational_data[0]['theme_name'] . '\', true)"></a>' . "\n";
				$content .=		'<div style="clear:both"></div>';
				$content .= '</div>' . "\n";
				
			}
			else {
				$content  = '<div class="module_error"><div class="module_error_stroke">One Panel detected that you recently switched themes. Your uploads directory is currently not writable, which means that we cannot backup your data right now. If you would like to backup your data, please make your uploads directory writable and refresh this page.</div></div>' . "\n";
				$content .= '<div align="right" style="margin-top:10px;">' . "\n";
				$content .= 	'<a class="flushbutton" href="javascript:;" onclick="op_admin.ForceFlushData( true )"></a>' . "\n";
				$content .= '</div>' . "\n";
				$content .= '<div style="clear:both;"></div>' . "\n";
				
			}
			
			echo $content;
			
			OnePanelInstaller::PrintFooter();
			
		}
		
		
		/**
		 * Print Tool Tip
		 * 
		 * This is the little div that appears when you click the tiny questions marks
		 * in the corner of each thumbnail in the Recently Used area.
		 *
		 */
		private function PrintToolTip() {
			?>
				<div id="ToolTip" style="position: absolute; display: none;">
					<div id="tt_container">
						<div id="tt_thumb"><div id="tt_image"></div></div>
						<div id="tt_content">
							<div id="tt_inner">
						    	<div id="op_logo"><img src="<?php echo get_option('home'); ?>/wp-content/plugins/OnePanel/images/default/tooltip/op_logo.gif" width="75" height="21" alt="One Panel" /></div>
						    	<div id="tt_title"></div>
						        <div id="tt_inner_content"></div>
						    </div>
						</div>
					</div>
				</div>
			<?php	
		}
		
		
		/**
		 * Print License Page
		 * 
		 * This page is displayed instead of Print Main if the license key doesn't 
		 * check out or is not present in the operational data.
		 *
		 */
		public function PrintLicensePage() {
			?>
				<div class="LicenseContainer">
                	<div class="LicenseContent">
						<div class="LicenseLogo">
                        <img border="0" src="<?php echo get_option('home'); ?>/wp-content/plugins/OnePanel/images/default/license/logo.png">
                        </div>
							<div class="LicenseForm">
								<form action="javascript:op_admin.License.Validate()">
									<input id="one_panel_license_key" type="text" maxlength="50" onblur="if(this.value==''){this.value=''}" onfocus="if(this.value==''){this.value=''}" value="" />
									<input onclick="op_admin.License.Validate()" type="button" value="Activate" />
								</form>
							</div>
                            <div id="one_panel_license_icon" class="LicenseIcon">
                           	 <img border="0" src="<?php echo get_option('home'); ?>/wp-content/plugins/OnePanel/images/default/license/icons/lock.gif">
                            </div>
					</div>
				</div>
                
                
         <div id="one_panel_license_entry" class="LicenseTxt">Thank you for choosing a One Panel enabled theme for your WordPress Blog. Please enter your license key above to get started.</div>
			<?php
			$this->PrintDebugConsole();
		}
		
		
		public function PrintDebugConsole() {
			
			$output  = '<div><a href="javascript:;" onclick="$(\'op-error-console\').show()">Debug Console</a></div>'; 
			$output .= '<div id="op-error-console" style="display:none;">';
			
			$output  .= '</div>';
			
			echo $output;
			
		}
		
		
		
		
		/**
		 * Print Search Results
		 * 
		 * Ajax function that prints the results of a search.
		 *
		 * @uses $_POST['keywords'], $_POST['page']
		 */
		public function PrintSearchResults( $return=null) {
			
			// Get start and stop vars
			$start = 1;
			if (isset($_POST['page'])) {
				$start = ONE_PANEL_MAX_SEARCH_RESULTS * ( (int) $_POST['page'] - 1 ) + 1;
			}
			$stop = $start + ( ONE_PANEL_MAX_SEARCH_RESULTS - 1 );
			
			
			// Get Matches
			$matches = self::SearchForModule();

			// Show Them
			if (count( $matches ) > 0) {

				$return = '<h1>' . count( $matches ) . ' Results Found for "<span class="keyword">' . stripcslashes( $_POST['keywords'] ) . '</span>"...</h1>';

				for ($i = $start; $i <= $stop; $i++) {
					
					$match = &$matches[$i - 1]; // Array index starts at zero
					if (! $match) continue;	// Cant operate on a non object
					
					$return .= '<div class="Result">';
					$return .= "\t" . '<a href="javascript:;" onclick="op_admin.AjaxRender(\'opcp_' . get_class( $match ) . 'Render\')"><div id="' . get_class( $match ) . '-small"></div></a>';
					$return .= "\t" . '<a href="javascript:;" onclick="op_admin.AjaxRender(\'opcp_' . get_class( $match ) . 'Render\')"><h3>' . $match->GetTitle() . '</h3></a>';
					$return .= "\t" . '<span>' . $match->GetShortDescription() . '</span>'; // TODO theres a diff between help text and decscription
					$return .= '</div>';
					$return .= '<div id="SearchShadow"></div>';
					
				}
				
				// Print Pagination
				if (count( $matches ) > ONE_PANEL_MAX_SEARCH_RESULTS) {
					
					$number_of_pages_decimal = (count($matches) / ONE_PANEL_MAX_SEARCH_RESULTS);
					$number_of_pages = round( $number_of_pages_decimal );
					
					if (isset( $_POST['page'] )) $on_page = (int) $_POST['page'];	
					else $on_page = 1;
					
					$next_page = $on_page + 1;
					$prev_page = $on_page - 1;
					
					$next_link = '<div class="next_search"><a onclick="op_admin.Search.DoSearch( \'' . $_POST['keywords'] . '\', \'' . $next_page . '\' )" href="javascript:;"></a></div><div style="clear:both;"></div>';
					$prev_link = '<div class="prev_search"><a onclick="op_admin.Search.DoSearch( \'' . $_POST['keywords'] . '\', \'' . $prev_page . '\' )" href="javascript:;"></a></div><div style="clear:both;"></div>';
					
					if ($on_page == 1) {
						$return .= $next_link;	
					}
					else {
						
						$return .= $prev_link;
						
						$next_page = $on_page + 1;
						if ($next_page <= $number_of_pages) $return .= $next_link;
						
					}
					
				}
				
				die( $return );
				
			}
			else {
				die( '<h1>No results found for "<span class="keyword">' . stripcslashes( $_POST['keywords'] ) . '</span>"...</h1>' );	
			}
		}
		
		
		
		
		
		/**
		 * Print Export Window
		 * 
		 * The popup window that appears when you click the 'Export' link in the top menu.
		 *
		 */
		public function PrintExportWindow() {

			$backup_directory = self::GetOnePanelBackupDir();
			
			if (is_writable( $backup_directory )) {

				$response['content']  = '<div class="ExportC">';
				$response['content'] .= '<div style="clear:both;"><div class="TabActive"><b>Export Data</b></div>';
				$response['content'] .= '<div class="TabText">Please backup your One Panel data below &darr;</div>';
				$response['content'] .= '<div style="clear:both;"></div>';
				$response['content'] .= '<div id="exportstroke">';
				$response['content'] .= '<div id="export-container" style="clear:both;">';
				$response['content'] .= '<div class="export-content">This feature allows you to pack up all the One Panel options you have saved into a portable <span class="txthightlight">OPD file</span>. The file will be saved in your <span class="txthightlight">upload directory</span> but you will also be given the opportunity to download it. Do you want to backup your One Panel Data for \'' . self::$operational_data[0]['theme_name'] . '\' now?</div>';
				$response['content'] .= 	'<div class="ThumbSubmit" style="float:right;margin:0px 10px 10px 10px;padding:5px 8px;"><a href="javascript:;" onclick="op_admin.ExportData.DoExport()">Backup OnePanel Data</a></div>' . "\n";
				$response['content'] .= '<div style="clear:both;"></div>' . "\n";
				$response['content'] .= '</div>';
				$response['content'] .= '</div>' . "\n";
				
				$response['content'] .= '<div id="backup-area" align="center"></div>' . "\n";
				
			}
			else {
				$response['content']  = '<div class="module_error"><div class="module_error_stroke"><strong>Oops...</strong><br /><span style="line-height:20px;">Your uploads directory is not currently writable, which means that we cannot backup your data right now. If you would like to backup your data, please make your uploads directory writable and re-open this window.</span></div></div>' . "\n";
			}
			
			$response['content'] = utf8_encode( $response['content'] );
			$response['title'] = 'Export One Panel Data';
			
			die( json_encode( $response ) );
			
		}
		
		
		public function PrintImportWindow() {
			
			$response['content']  = '<div style="width:690px;margin:0 auto;">';
			$response['content'] .= '<div style="clear:both;"><div id="import_browse_tab" class="TabActive"><a href="javascript:;" onclick="op_admin.ImportData.SwitchMode(\'browse\')">Browse for file</a></div><div id="import_upload_tab" class="TabInActive"><a href="javascript:;" onclick="op_admin.ImportData.SwitchMode(\'upload\')">Upload a File</a></div></div>';
			$response['content'] .= '<div class="TabText">Please select a tab &rarr;</div>';
			$response['content'] .= '<div style="clear:both;"></div>';
			$response['content'] .= '<div id="importstroke">';
			$response['content'] .= '<div id="import-container" style="clear:both;">';
			$response['content'] .= $this->GetImportBrowse(); 
			$response['content'] .= '</div>';
			$response['content'] .= '</div>';
			$response['content'] .= '</div>';
			$response['content'] = utf8_encode( $response['content'] );
			
			$response['title'] = 'Import One Panel Data';
			die( json_encode( $response ) );
		}
		
		public function SwitchImportMode() {
			
			if (isset( $_POST['mode'] )) {

				switch ($_POST['mode']){
					case 'upload':
						die( $this->GetImportUpload() );
					break;
					
					case 'browse':
						die( $this->GetImportBrowse() );	
					break;
				}
				
			}
			
		}
		
		private function ImportData( $file_path ) {
			
			if (! file_exists( $file_path )) {
				return '<div class="popup_no_results"><div class="module_error_stroke">The backup file at ' . $file_path . ' does not exist.</div></div>';
			}
			else {
						
				// Open the file and check out the data.
				$fp = fopen( $file_path, 'r' );
				if (! $fp)
					return '<div class="popup_no_results"><div class="module_error_stroke">Could not open the file <em>' . basename( $file_path ) . '</em> for writing, please check your permissions.</div></div>';
				
				$encrypted_data = fread( $fp, filesize( $file_path ) );
				fclose( $fp );
				
				
				$unencrypted_data = @$this->UnpackData( $encrypted_data );
				
				if (! is_array( $unencrypted_data )) {
					return '<div class="popup_no_results"><div class="module_error_stroke">Sorry, the backup file <em>' . basename( $file_path ) . '</em> is corrupt and cannot be imported.</div></div>';
				}
				else {
					
					// Check the data is compatible with the theme
					if ( $unencrypted_data[0]['theme_name'] != self::$operational_data[0]['theme_name'] )
						return '<div class="popup_no_results"><div class="module_error_stroke">Sorry, the backup file is not compatible with your current theme.</div></div>';
					
					// Remove the export date from the data if its present
					if (isset( $unencrypted_data[0]['export_date'] )) unset( $unencrypted_data[0]['export_date'] );
					
					// Quickly grab the version data we need for upgrading the data
					$data_version = $unencrypted_data[0]['data_version'];
					$data_version_date = $unencrypted_data[0]['data_version_date'];
					
					// Copy the license data from the existing data and merge it with the new.
					$unencrypted_data[0] = self::$operational_data[0];
					$unencrypted_data[0]['data_version'] = $data_version;
					$unencrypted_data[0]['data_version_date'] = $data_version_date;
				
					// Save into operational data
					self::$operational_data = $unencrypted_data;
					self::PackData();
					
					return true;
					
				}
				
			}
			
		}
		
		public function DoImport() {
			
			$response = '';
			
			if (! isset( $_POST['file'] )) {
				$response .= '<div class="popup_no_results"><div class="module_error_stroke">You did not upload a file.</div></div>';
			}
			else {

				$file_name = $_POST['file'];
				$target_path = self::GetOnePanelBackupDir() . '/' . $file_name;
				
				$error_string = $this->ImportData( $target_path );
				
				if (is_string( $error_string )) {
					$response .= $error_string;	
				}
				else {
					$response = true;
				}	
				
			}
			
			die( $response );
			
		}
		
		public function GetImportBrowse() {
			
			$backup_file_names = $this->GetCompatibleOPDs();
			
			if ($backup_file_names) {

				$response  = '<div class="backup_files">Your backup files &darr;</div>' . "\n";
				$response .= '<div class="backup_files_list">' . "\n";
				$response .= '<ul>';
				foreach ( $backup_file_names as $key => &$data ) {
					
					// Check the data for defects
					if (! isset( $data[0]['theme_name'] )) continue;
					if (! isset( $data[0]['data_version'] )) continue;
					if (! isset( $data[0]['export_date'] )) continue;
					
					$arguments = 	'\'' . $data[0]['theme_name'] . '\',' . 
									'\'' . $data[0]['data_version'] . '\',' . 
									'\'' . $data[0]['export_date'] . '\',' . 
									'\'' . 'Import this data' . '\',' .
									'\'' . $key . '\'';
					
					$response .= '<li><a href="javascript:;" onclick="op_admin.ImportData.UpdateFileInfo(' . $arguments . ')">' . $key . '</a></li>' . "\n";
					
				}
				$response .= '</ul>';
				$response .= '</div>' . "\n";
				$response .= '<div id="import-file-info">Please select a file.</div>';
				$response .= '<div style="clear:both;"></div>';
				
			}
			else {
				$response = '<div style="padding:8px;">One Panel did not detect any compatible backup files on your server.</div>' . "\n";
			}
			
			
			return $response;
			
		}
		
		public function GetImportUpload() {
			
			$response = '';
			$response .= '<div style="padding:10px;">';
			$response .= 	'<form action="admin-ajax.php" method="post" target="upload_target" enctype="multipart/form-data">';
			$response .=		'<input type="hidden" name="action" value="opcp_ImportDoUpload"/>';
			$response .=		'<input type="hidden" name="MAX_FILE_SIZE" value="1000000"/>';
			$response .=		'<label for="image_upload">OPD File:&nbsp;&nbsp;&nbsp;</label>';
	    	$response .=		'<input name="userfile" type="file"/>';
	    	$response .=		'<input class="ThumbSubmit" type="submit" value="Upload File" style="border:1px solid #9ABADB;"/>';
	    	$response .=	'</form>';
	    	$response .= 	'<iframe id="upload_target" name="upload_target" src="#" style="width:0;height:0;border:0px solid #fff;"></iframe>';
			$response .= '</div>';
			
			$response .= '<div id="import_upload_status"></div>';
			
			return $response;
		}
		
		public function ImportDoUpload() {
			
			$backup_dir = self::GetOnePanelBackupDir();
			$response = array();
			
			if (! is_writable( $backup_dir )) {
				$response['error'] = '<div class="popup_no_results" style="margin:5px;"><div class="module_error_stroke">Whoops! Your upload directory is not currently writable. Please change the folder permissions.</div></div>';
				$response['status'] = false;
			}
			else {
				
				$file = $_FILES['userfile'];
				$upload_target_path = $backup_dir . '/' . basename( $file['name'] );
				
				// Check the state of the upload
			 	switch ($file['error']) {
				    case UPLOAD_ERR_FORM_SIZE:
				    	$error = '<div class="popup_no_results" style="margin:0 5px 5px 5px;"><div class="module_error_stroke">The file you posted is too big!</div></div>';
				    break;
				    
				    case UPLOAD_ERR_INI_SIZE:
				    	$error = '<div class="popup_no_results" style="margin:0 5px 5px 5px;"><div class="module_error_stroke">The file you posted is too big!</div></div>';
				    break;
				    
				    case UPLOAD_ERR_NO_FILE:
				    	$error = '<div class="popup_no_results" style="margin:0 5px 5px 5px;"><div class="module_error_stroke">You did not upload a file</div></div>';
				    break;
			    }
			    
			    if (isset( $error )) {
			    	$response['error'] = $error;
					$response['status'] = false;
			    }
			    elseif (move_uploaded_file( $file['tmp_name'] , $upload_target_path )) {
				    	
			    	$import_error = $this->ImportData( $upload_target_path );
			    	
			    	if (is_string( $import_error )) {
			    		$response['error'] = $import_error;
			    		$response['status'] = false;
			    	}
			    	else {
			    		$response['status'] = true; // Success
			    	}
			    	
			    }
			    else {
			    	$response['error'] = '<div class="popup_no_results" style="margin:0 5px 5px 5px;"><div class="module_error_stroke">Whoops! One Panel could not copy the file to your backup directory.</div></div>';
					$response['status'] = false;
			    }
				
			}
			
			// Send the response for the iframe
		    echo '<script language="javascript" type="text/javascript">';
			echo 	'window.top.window.op_admin.ImportData.HandleUpload(' . json_encode( $response ) . ');';
			echo '</script>';
		    die;
			
		}
		
		
		/*
		 * TODO
		 */
		private function GetCompatibleOPDs() {

			// Get WordPress' uploads data
			$backup_directory = self::GetOnePanelBackupDir();
			
			if (file_exists($backup_directory)) {
					
				$backup_dir_iterator = new DirectoryIterator( $backup_directory );
				$opd_files = array();
				
				foreach ($backup_dir_iterator as $fileinfo) {
				    
					if (! $fileinfo->isDot()) {
				        
						// TODO check its an opd
						
						// Open the file and check out the data.
						$fp = fopen( $backup_directory . '/' . $fileinfo->getFilename(), 'r' ); // TODO check this is successful
						$encrypted_data = fread( $fp, filesize( $backup_directory . '/' . $fileinfo->getFilename() ) );
						fclose( $fp );
						
						$unencrypted_data = $this->UnpackData( $encrypted_data );
						
						if (is_array( $unencrypted_data )) {
							
							// Check for compatibility
							if ( $unencrypted_data[0]['theme_name'] == self::$operational_data[0]['theme_name'] ) {
								
								// WE have two choices now, we can just return the file names, and then get extra info onclick
								// or we can pass the whole array, and send the vars to javascript. The latter is less CPU intensive..
								$opd_files[ $fileinfo->getFilename() ] = $unencrypted_data;
								
							}
							
						}
						
				    }
				    
				}
				
				if (count($opd_files) > 0) {
					return $opd_files;
				}
				else {
					return false;
				}
				
			}
			else {
				return false;
			}
			
		}
		
		/**
		 * Print Category Results
		 * 
		 * Ajax function that prints the clicked categorie's modules.
		 * If the params are set it returns the response (for use with
		 * the inital load) rather than dying it.
		 * 
		 * @param bool $return
		 * @param str $category
		 * @uses $_POST['page'], $_POST['category']
		 */
		public function PrintCategoryResults( $return=null, $category=null ) {
			
			// Set the category
			if (! $category) $category = $_POST['category'];
			
			// Get start and stop vars
			$start = 1;
			if (isset($_POST['page'])) {
				$start = ONE_PANEL_MAX_SEARCH_RESULTS * ( (int) $_POST['page'] - 1 ) + 1;
			}
			$stop = $start + ( ONE_PANEL_MAX_SEARCH_RESULTS - 1 );
			
			
			// Get the results
			$matches = self::SearchForCategories( $category );

			
			// Show them
			if (count( $matches ) > 0) {

				$response = '<h1>Showing ' . count( $matches ) . ' modules in the "<span class="keyword">' . $category . '</span>" category...</h1>';

				for ($i = $start; $i <= $stop; $i++) {
					
					$match = &$matches[$i - 1]; // Array index starts at zero
					if (! $match) continue;	// Cant operate on a non object
					
					$response .= '<div class="Result">';
					$response .= "\t" . '<a href="javascript:;" onclick="op_admin.AjaxRender(\'opcp_' . get_class( $match ) . 'Render\')"><div id="' . get_class( $match ) . '-small"></div></a>';
					$response .= "\t" . '<a href="javascript:;" onclick="op_admin.AjaxRender(\'opcp_' . get_class( $match ) . 'Render\')"><h3>' . $match->GetTitle() . '</h3></a>';
					$response .= "\t" . '<span>' . $match->GetShortDescription() . '</span>';
					$response .= '</div>';
					$response .= '<div id="SearchShadow"></div>';
					
				}
				
				// Print Pagination
				if (count( $matches ) > ONE_PANEL_MAX_SEARCH_RESULTS) {
					
					$number_of_pages_decimal = (count($matches) / ONE_PANEL_MAX_SEARCH_RESULTS);
					$number_of_pages = round( $number_of_pages_decimal );
					
					if (isset( $_POST['page'] )) $on_page = (int) $_POST['page'];	
					else $on_page = 1;
					
					$next_page = $on_page + 1;
					$prev_page = $on_page - 1;
					
					$next_link = '<div class="next_search"><a onclick="op_admin.Search.DoCategory( \'' . $category . '\', \'' . $next_page . '\' )" href="javascript:;"></a></div><div style="clear:both;"></div>';
					$prev_link = '<div class="prev_search"><a onclick="op_admin.Search.DoCategory( \'' . $category . '\', \'' . $prev_page . '\' )" href="javascript:;"></a></div><div style="clear:both;"></div>';
					
					if ($on_page == 1) {
						$response .= $next_link;	
					}
					else {
						
						$response .= $prev_link;
						
						$next_page = $on_page + 1;
						if ($next_page <= $number_of_pages) $response .= $next_link;
						
					}
					
				}
				
				if (! $return) die( $response );
				else return $response;
				
			}
			else {
				
				$message = '<h1>No results found in "<span class="keyword">' . $category . '</span>"...</h1>';
				
				if (! $return) {
					die( $message );
				}
				else return $message;
				
			}
			
		}
		
		
		public function PrintDataUpgraded() {
				
			OnePanelInstaller::PrintSimpleHeader();
			
			echo '<p>One Panel detected that you recently upgraded, and attempted to bring your One Panel Data in line with the new version.</p>' . "\n";
			
			if (defined( 'ONE_PANEL_DATA_UPGRADED' )) {

				// Add continue link
				if (function_exists( 'admin_url' )) $admin_url = admin_url();
				else $admin_url = get_option( 'home' ) . '/wp-admin/';
				
				$content  = '<p>The data was successfully upgraded.</p> ' . "\n";
				$content .= '<p style="padding-top:10px;"><a class="continuebutton" href="' . $admin_url . 'admin.php?page=OnePanel"></a></p><div style="clear:both;"></div>' . "\n";
				echo $content;
				
			}
			elseif (defined( 'ONE_PANEL_DATA_NOT_UPGRADED' )) {
				
				$content  = '<p>Your data was very old, and as a result could not be upgraded. One Panel needs to flush your current data in order to continue.</p>' . "\n";
				$content .= '<div align="right" style="margin-top:10px;">' . "\n";
				$content .= 	'<a class="flushbutton" href="javascript:;" onclick="op_admin.ForceFlushData( true )"></a><div style="clear:both;"></div>' . "\n";
				$content .= '</div>' . "\n";
				echo $content;
				// TODO 2.1 add a backup button here.
						
			}
			
			OnePanelInstaller::PrintFooter();
		}
		
		
		/**
		 * Get Tool Tip Content
		 *
		 * Ajax function that gets the content for the little tooltip that 
		 * pops up when you click the little question mark on a module in 
		 * the Recently Used area. 
		 * 
		 * @uses $_POST['module']
		 */
		public function GetToolTipContent() {

			$module = self::$operational_data[1][$_POST['module']];
			
			if (is_object( $module )) {
			
				$return = array();
				$return['title'] = $module->GetTitle();
				$return['content'] = '<p>' . $module->GetDescription() . '</p>'; // <p> is important!
				
				die( json_encode( $return ) );
				
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
		
		
		/**
		 * Get News Tick
		 * 
		 * Grabs a piece of news from the updates server
		 *
		 */
		public function GetNewsTick() {
			
			$host_name = 'one-theme.com';
			$host_header = 'Host:updates.one-theme.com';
			$no_response_str = 'One Panel could not connect to the news server, sorry.';
			
			$sp = @fsockopen( $host_name, '80', $error_no, $error_str, 15 );
		    if (! $sp) 
		    	return( $no_response_str );
		    
		    fputs($sp, 'GET /news.php HTTP/1.1' . "\r\n");
		    fputs($sp, $host_header . "\r\n");
		    fputs($sp, "Connection:close\r\n\r\n");
		    
		    $response = '';
		    
		    while (! feof($sp)) {
		    	$response .= fgets($sp, 128);
		    }
		    
		    fclose($sp);
		    
		    $response = explode( "\r\n\r\n", $response ); 
		    
		    // Check for a bad response
		    if (empty($response[1])) 
		    	return( $no_response_str );
		    else 
		    	return( $response[1]);
			
		}
		
		
		/**
		 * Get Default Skin
		 * 
		 * Returns the current default skin object.
		 *
		 * @return OnePanelSkin
		 */
		public static function GetDefaultSkin() {
			
			$module = &self::$operational_data[1]['SkinModule'];
			$feature = &$module->features['DefaultSkinFeature'];
			return $feature->GetDefaultSkin();
			
		}
		
		
		/**
		 * Uninstall
		 * 
		 * Removes the license number and install flag from 
		 * the operational data.
		 *
		 */
		private function Uninstall() {
			self::$operational_data[0]['installed'] = false;
			self::$operational_data[0]['license_no'] = null;
			self::PackData();
		}
		
		
		/**
		 * Data Upgrade Available
		 * 
		 * A quick test to see whether the user data is out of date.
		 * 
		 * @uses assumes that if opdata[0] exists, so does the data version info
		 * @return bool
		 */
		private function DataUpgradeAvailable() {
			
			// Debug
			$track = OnePanelDebug::Track( 'Checking for possible data upgrades' );
			
			
			// Dont run if there isnt any data.
			if (empty( self::$operational_data )) {
				
				OnePanelDebug::Info( 'No operational data yet, aborting upgrade attempt.' );
				$track->Affirm();	
				return false;
				
			}
			
			
			// Check for corruption in the operational data.
			if (! isset( self::$operational_data[0]['data_version_date'] )) {
				
				// Throw an error, anomaly detected
				OnePanelDebug::Error( 'Checking for upgrades on corrupted data - missing data_version.' );	
				$track->Fail();
				return false;
				
			}
			else {
				
				// Check the data against the version date constant
				if ( ONE_PANEL_VERSION_DATE > (int) self::$operational_data[0]['data_version_date']) {
					
					OnePanelDebug::Info( 'Upgrade required.' );
					$track->Affirm(); 
					return true;
					
				}
				else {
					
					OnePanelDebug::Info( 'No upgrade required.' );
					$track->Affirm();
					return false;
					
				}
				
			}
			
		}
		
		
		
		
		/**
		 * SoftwareUpgradeAvailable
		 * 
		 * Determines whether a newer version of the software is available for installation
		 * 
		 * @return bool
		 */
		private function SoftwareUpgradeAvailable() {
			
			// Get the data version from the server
			$host_name = 'one-theme.com';
			$host_header = 'Host:updates.one-theme.com';
			
			$sp = @fsockopen( $host_name, '80', $error_no, $error_str, 15 );

			if ($sp) { 
		    
			    fputs($sp, 'GET /new_version.php HTTP/1.1' . "\r\n");
			    fputs($sp, $host_header . "\r\n");
			    fputs($sp, "Connection:close\r\n\r\n");
			    
			    $response = '';
			    
			    while (! feof($sp)) {
			    	$response .= fgets($sp, 128);
			    }
			    
			    fclose($sp);
			    
			    $response = explode( "\r\n\r\n", $response ); 
			    
			    // Check for a bad response or set var
			    if (! empty($response[1])) $newest_version = $response[1];
		    
			}
			
			
			// Make sure we have newest_version
			if (! isset( $newest_version )) {
				OnePanelDebug::Error( 'Could not connect to server for newest version.' );	
				return false;
			}
			else {
				
				// Check newest_version against our version constant
				if ( $newest_version > ONE_PANEL_VERSION_DATE) {
					OnePanelDebug::Info( 'New One Panel version available for download.' );
					return true;
				}
				
			}
			
			return false;
			
		}
		
		
		/**
		 * Install
		 *
		 * Sets the installed flag in the operational data.
		 * 
		 */
		private function Install() {
			
			self::$operational_data[0]['installed'] = true;
			
			// Make sure we dont change these on every license check, we use them to check for upgrades.
			if (! isset( self::$operational_data[0]['data_version_date'] )) self::$operational_data[0]['data_version_date'] = ONE_PANEL_VERSION_DATE; 
			if (! isset( self::$operational_data[0]['data_version'] )) 		self::$operational_data[0]['data_version'] = ONE_PANEL_VERSION;
			
			self::PackData();
			
		}
	}