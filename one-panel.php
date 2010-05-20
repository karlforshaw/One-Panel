<?php
/*
Plugin Name: One Panel
Plugin URI: http://www.one-theme.com
Description: One Panel is a control panel by One Theme LTD. Designed originally for use with One Theme, One Panel is now supported by a wider range of themes accross the WordPress Themes market.
Author: One Theme LTD
Author URI: http://www.one-theme.com/
Version: 2.0.2
*/


	/**
	 .d88888b.                       8888888b.                            888 
	d88P" "Y88b                      888   Y88b                           888 
	888     888                      888    888                           888 
	888     888 88888b.   .d88b.     888   d88P 8888b.  88888b.   .d88b.  888 
	888     888 888 "88b d8P  Y8b    8888888P"     "88b 888 "88b d8P  Y8b 888 
	888     888 888  888 88888888    888       .d888888 888  888 88888888 888 
	Y88b. .d88P 888  888 Y8b.        888       888  888 888  888 Y8b.     888 
	 "Y88888P"  888  888  "Y8888     888       "Y888888 888  888  "Y8888  888 
	                                                                          
								  By One Theme LTD
	
	_____________________________-= VERSION 2.0.2 =-__________________________
	
	 * Thank you for using One Panel, please do not tamper with the 
	 * files, it will invalidate your warranty. One Panel is designed
	 * for use with WORDPRESS 2.5 and above. 
	 * 
	 * This page sets up the running environment for One Panel.
	 * It includes the files needed for the config files, and 
	 * loads the proper object depending on whether we are on 
	 * the front end or backend.
	 * 
	 * @author Karl Forshaw <karlforshaw@gmail.com>
	 * @copyright One Theme LTD 2008
	 * @license One Theme License <http://www.one-theme.com/TOS/>
	 * @see README.html || http://www.one-theme.com/
	 */

	/* 
	 * Set Running Mode
	 * 
	 * 0 - No reporting (production mode)
	 * 1 - Report anomalies
	 * 2 - Report all logs
	 * 3 - Report anomalies (store in DB) // TODO
	 * 4 - Report all logs (store in DB) // TODO
	 * 
	 */
	define( 'ONE_PANEL_DEBUG_MODE', 2 );
	
	
	// Define Constants
	define( 'ROOTDIR', get_bloginfo( 'stylesheet_directory' ) );	
	define( 'ONE_PANEL_VERSION', '2.1' );
    define( 'ONE_PANEL_VERSION_DATE', 1250544340 );
	define( 'ONE_PANEL_DIR', realpath( ABSPATH . '/wp-content/plugins/one-panel/' ) );
	define( 'ONE_PANEL_ACTIVE', true ); // XXX Remove?
	define( 'ONE_PANEL_MAX_SEARCH_RESULTS', 4 );
	define( 'ONE_PANEL_AJAX_PREFIX', 'opcp_' );
	
	// Start the test harness
    require_once realpath( ONE_PANEL_DIR . 'debug/onepaneldebug.php' );
    require_once realpath( ONE_PANEL_DIR . 'debug/onepanelentry.php' );
    require_once realpath( ONE_PANEL_DIR . 'debug/onepaneltracker.php' );
	OnePanelDebug::Start();

	/*
	 * Load OnePanelLib
	 * This will be the last time we use require_once in favor of
	 * OnePanelLib::RequireFileOnce
	 * 
	 */ 
	require_once realpath( ONE_PANEL_DIR . '/onepanellib.php' );
	
	// Include Installer File
	OnePanelLib::RequireFileOnce( ONE_PANEL_DIR . '/onepanelinstaller.php' );
		
	/*
	 *  Include Language File
	 *  No need to start it here, it will be started and put into use when required
	 *  TODO consider allowing language terms of One Panel to be editable in future,
	 *  the object will need to be started here at that point.
	 */
	OnePanelLib::RequireFileOnce( ONE_PANEL_DIR . '/language.php' );
    
    // Include the config class, and create the OnePanelConfig Object
    OnePanelLib::RequireFileOnce( ONE_PANEL_DIR .'/onepanelconfig.php' );
    OnePanelConfig::Start();
    
    // Include Externals
    //OnePanelLib::RequireFileOnce( ONE_PANEL_DIR .'/onepanelexternals.php' );
    //OnePanelExternals::AddActions();
    // THESE ARE BROKEN
    
	/*
	 * Create one of two objects depending on which environment we appear
	 * to be using. The OnePanelTheme has methods that should be available to
	 * theme developers, but do not need to be present in the backend.
	 */
    if ((is_admin()) || (OnePanelLib::InAjaxMode())) {
    	
    	// Log
    	OnePanelDebug::Info( 'Running non AJAX mode in OnePanel.' );
    
	   	// Instantiate the OnePanel Object
	    OnePanelLib::RequireFileOnce( ONE_PANEL_DIR . '/panel/onepanel.php' );
	    OnePanel::Start();
		    
	    
    }
    else {
    	
    	/*
    	 * TODO There is no reason to load the OnePanelTheme object if a config file
    	 * isnt present in the theme folder.
    	 */
    	
    	// Instantiate the OnePanelTheme Object
    	OnePanelLib::RequireFileOnce( ONE_PANEL_DIR .'/onepaneltheme.php' );
    	OnePanelTheme::Start();
    	
    }
	