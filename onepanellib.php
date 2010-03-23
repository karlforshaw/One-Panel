<?php

	/**
	 * One Panel Lib
	 * 
	 * A library of misc functions that are required outside of the
	 * prevalent One Panel object (OnePanel, OnePanelTheme)
	 * 
	 * @author Karl Forshaw <http://www.karlforshaw.co.uk/>
	 */
	class OnePanelLib {
		
		
		/**
		 * RequireFileOnce
		 * 
		 * Uses OnePanelDebug and runs a require_once on the file path provided.
		 * 
		 * @param $file_path
		 * @return boolean
		 */
		public static function RequireFileOnce( $file_path ) {
			
			$success = OnePanelDebug::Track( 'Including file: ' . $file_path );
			
			if (file_exists( realpath( $file_path ))) {

				require_once realpath( $file_path );
				$success->Affirm();
				
			}
			else {
				OnePanelDebug::Error( 'The file ' . $file_path . ' does not exist' );
				$success->Fail();
			}
			
			
			return true;
			
		}
		
		
		/**
		 * InAjaxMode
		 * 
		 * Determines whether or not the current request is an AJAX request.
		 * 
		 * @return boolean
		 */
		public static function InAjaxMode() {
			
			if (defined( 'DOING_AJAX' )) {
				return true;
			}
			else {
				return false;
			}
				
		}
		
		
		/**
		 * InConsole
		 * 
		 * Determines whether the OnePanel admin page is being viewed.
		 * 
		 * @return boolean
		 */
		public static function InConsole() {
			
			if ((isset($_GET['page'])) && ($_GET['page'] == 'OnePanel')) {
				return true;
			}
			else {
				return false;
			}
		}
		
		
		/**
		 * ConvertBytes
		 * 
		 * Convert bytes to a human readable format. 
		 * 
		 * @param int $number
		 * @return string
		 */
		public static function ConvertBytes( $number )
		{
		    $len = strlen($number);
		    
		    if($len < 4)
		        return sprintf("%d b", $number);
		    if($len >= 4 && $len <=6)
		        return sprintf("%0.2f Kb", $number/1024);
		    if($len >= 7 && $len <=9)
		        return sprintf("%0.2f Mb", $number/1024/1024);
		    else
		    	return sprintf("%0.2f Gb", $number/1024/1024/1024);
		                           
		}
		
	}