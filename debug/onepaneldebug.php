<?php

	/**
	 * One Panel Debug
	 * 
	 * TODO class description
	 * 
	 * @author Karl Forshaw <karlforshaw@gmail.com>
	 * 
	 */
	class OnePanelDebug {
		
		private static $instance = null;
		private static $entries = array();
		private static $start_time;
		private static $finish_time;
		private static $start_mu;
		private static $finish_mu;
		
		
		
		private function __construct() {
			
			// Start timer
			self::$start_time = microtime( true );
			
			// Record memory usage
			self::$start_mu = memory_get_usage();
			
			// Report instantiation
			$success = self::Track( 'Starting Log' );
			
			// Register the script shutdown function
			register_shutdown_function( array( $this, 'Shutdown' ) );
			
			$success->Affirm();
		}
		
		
		private function __clone() {
			return false;
		}
		
		
		public static function Start() {
			
			if (is_null( self::$instance ))
				return new OnePanelDebug();
			else 
				return self::$instance;
			
		}
		
		
		public static function AddEntry( OnePanelEntry &$entry ) {
			self::$entries[] = &$entry;
		}
		
		
		public static function &Track( $message, $status=null ) {
			
			$tracker = new OnePanelTracker( $message, $status ); 
			self::AddEntry( $tracker );
			return $tracker;
			 
		}
		
		
		public static function Info( $message ) {
			
			self::AddEntry( new OnePanelEntry( 'INFO: ' . $message) );
			return true;
			
		}
		
		
		public static function Warn( $message ) {
			
			self::AddEntry( new OnePanelEntry( 'WARN: ' . $message ) );
			return true;
			
		}
		
		
		public static function Error( $message ) {
			
			self::AddEntry( new OnePanelEntry( 'ERROR: ' . $message ) );
			return true;
			
		}
		
		
		
		
		public static function Shutdown() {
	
			/*
			 * Break out of this function if we do not need to run it.
			 * This is important when we are not in AJAX mode and the 
			 * user is not looking at OnePanel
			 * 
			 */
			if (! OnePanelLib::InConsole()) return true;
			
			/*
			 * TODO
			 * Ive got a feeling that printing <script> tags outside the html tags is a bad idea
			 * perhaps this function should only be used as a last ditch attempt to scrape data in
			 * the case of a fatal php error and another shutdown function should be run after the
			 * plugin has completed whatever hook is being called.
			 * 
			 */
			
			
			// Discern whether a fatal error occured TODO handle non fatals
			$last_error = error_get_last();
			
   			if (($last_error['type'] === E_ERROR) || 
   				($last_error['type'] === E_USER_ERROR) ||
   				($last_error['type'] === E_COMPILE_ERROR) ||
   				($last_error['type'] === E_CORE_ERROR) ||  
   				($last_error['type'] === E_RECOVERABLE_ERROR)) 
   			{
   				
   				// Fail the last entry
   				self::FailIncompleteTrackers();
			
				// Store the log if necessary
				
				// Stop the clock
				self::$finish_time = microtime( true );
				
				// Record memory usage
				self::$finish_mu = memory_get_usage();
				
				// Output the apppropriate data depending on running environment (AJAX or normal)
				echo self::GetRawOutput();
			
   			}
   			else {
   				
   				// Stop the clock
				self::$finish_time = microtime( true );
				
				// Record memory usage
				self::$finish_mu = memory_get_usage();
   				
   				// Script completed. Send the jacascript to populate the error console
   				if (! OnePanelLib::InAjaxMode()) {
   					echo self::GetOutput(); // TODO, devise a strategy to append the return ajax data with the error console data.
   				}
   				
   			}
   			
   			return true;
			
		}
		
		
		private static function FailIncompleteTrackers() {
			
			foreach ( self::$entries as $key => &$entry ) {
				
				if (($entry instanceof OnePanelTracker) && (is_null( $entry->GetStatus() )))
					$entry->Fail();
				
			}
			
		}
		
		
		public static function Store() {
			// TODO
		}
		
		
		public static function GetRawOutput() {
			
			$html  = '';
			
			foreach ( self::$entries as $key => &$entry ) {
				$html .= $entry->Report() . '<br />';
			} 
			
			$html .= '------<br />';
			$html .= 'Script complete in ' . round( (self::$finish_time - self::$start_time), 2 ) . ' seconds<br />';
			$html .= 'Starting memory usage: ' . OnePanelLib::ConvertBytes( self::$start_mu ) .  '<br />';
			$html .= 'Ending memory usage: ' . OnePanelLib::ConvertBytes( self::$finish_mu ).  '<br />';
			$html .= 'Plugin initiation memory usage ' . OnePanelLib::ConvertBytes( (self::$finish_mu - self::$start_mu) ) . '<br />';
			
			return $html;
		}
		
		public static function GetOutput() {
			
			// Generate the HTML
			$html = self::GetRawOutput();
			
			// Print output
			$output  = '<script type="text/javascript">';
			$output .= "\t" . '$("op-error-console").update( "' . $html . '" );';
			$output .= '</script>';
			
			return $output;
			
		}
		
		
		/*
		 * 
		 * TESTING FUNCTIONS
		 * 
		 */
		
		/**
		 * IsPositiveInteger
		 * 
		 * Check that a passed argument is either of type int, or can be sucessfully
		 * converted into a positive (+ more than zero) integer. 
		 * 
		 * @param $value
		 * @return boolean
		 */
		public static function IsPositiveInteger( $value ) {
			
			if (is_int( $value ) && ($value > 0)) {
	    		return true; 
			}
	    	else {
	    		
	    		$value = (int) $value;
	    		
	    		if ($value == 0) 
	    			return false;
	    		
	    	}
			
		}
		
		
		/*
		 * 
		 * LOG OUTPUT PREPARATION FUNCTIONS
		 * 
		 */
		
		
		/**
		 * PrepareAddBehaviourOutput
		 * 
		 * Generates the log output for when a behaviour is added in the config file. 
		 * 
		 * @param OnePanelBehaviour $behaviour
		 * @return string
		 */
		public static function PrepareAddBehaviourOutput( OnePanelBehaviour &$behaviour ) {
			
			// TODO only do this if the log level warrants it.
			$outcomes = $behaviour->GetOutcomes();
			
			if (count( $outcomes ) > 0) {
				
	    		$message = 'Adding a behavioural modification to ';
	    	
		    	foreach ($outcomes as $key => &$outcome) {
		    		
		    		$affected_object = &$outcome->GetAffectedModule();
		    		
		    		/*
		    		 * BREAKAGE WARNING
		    		 *  TODO this next line of code might be iffy if we later
		    		 *  decide to allow behavioural modifications to anything 
		    		 *  other than highlights.
		    		 *  Suggested having a BehaviouralElement(?) class that we 
		    		 *  can use for extension.
		    		 */
		    		$message .= '[' . get_class($affected_object) . ':' . $affected_object->GetName() . ']';
		    	}
		    	
		    	return $message;
	    		
	    	}
	    	else
	    		return 'Skipping behaviour with no outcome';
	    	
			
		}
		
	}