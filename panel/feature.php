<?php

	abstract class OnePanelFeature {
		
		protected $title;
		protected $help_text;
		protected $active = false; // Default value
		protected $enabled = false; // Default value
		protected $detail_chunks = null;
		
		protected $alternate_key = null;
		
		abstract public function Render();
		
		public function IsActive() {
			return $this->active;
		}
		
		public function SetTitle( $title ) {
			if (is_string( $title )) $this->title = $title;
		}
		
		public function SetAlternateKey( $key ) {
			if (is_string( $key )) $this->alternate_key = $key;
		}
		
		public function GetTitle() {
			return $this->title;
		}
		
		/**
		 * Generic Activate
		 * 
		 * @return str span_id|on 
		 */
		public function Activate() {
			
			$this->active = true;
			OnePanel::PackData();	
			
			$response = array();
			$response['module'] = (is_null($this->alternate_key) ? get_class( $this ) : $this->alternate_key);
			$response['container_class'] = 'F-active';
			$response['thumb_class'] = 'ThumbActive';
			$response['info_class'] = 'FeatureActiveInfo';
			$response['info_content'] = 'Feature is ' . (($this->active) ? 'active' : 'inactive') . '.';
			$response['button_text'] = '<a href="javascript:;" onclick="op_admin.AjaxOnOff(\'opcp_' . (is_null($this->alternate_key) ? get_class( $this ) : $this->alternate_key) . 'Deactivate\')"><img src="' . get_option('home') . '/wp-content/plugins/OnePanel/images/default/pop_content/disable.gif" border="0" /></a>';
			
			$response = json_encode( $response );
			die($response);
			
		}
		
		/**
		 * Generic Dectivate
		 * 
		 * @return str span_id|off
		 */
		public function Deactivate() {
			
			$this->active = false;
			OnePanel::PackData();
			
			$response = array();
			$response['module'] = (is_null($this->alternate_key) ? get_class( $this ) : $this->alternate_key);
			$response['container_class'] = 'F-inactive';
			$response['thumb_class'] = 'ThumbInActive';
			$response['info_class'] = 'FeatureInActiveInfo';
			$response['info_content'] = 'Feature is inactive.';
			$response['button_text'] = '<a href="javascript:;" onclick="op_admin.AjaxOnOff(\'opcp_' . (is_null($this->alternate_key) ? get_class( $this ) : $this->alternate_key) . 'Activate\')"><img src="' . get_option('home') . '/wp-content/plugins/OnePanel/images/default/pop_content/enable.gif" border="0" /></a>';
			
			$response = json_encode( $response );
			die($response);
			
		}
		
		public function OverrideActivation() {
			$this->active = false;
		}
		
		public function SetAsActive() {
			$this->active = true;
		}
		
		/**
		 * Enable
		 * 
		 * Allows One Panel management of the feature
		 *
		 */
		public function Enable() {
			$this->enabled = true;
		}
		
		public function Disable() {
			$this->enabled = false;
		}
		
		public function IsEnabled() {
			return $this->enabled;
		}
		
		public function RenderOnOff() {

			$alternate_key = $this->alternate_key;
			
			$return  = '<div class="PopUp_F">';
			$return .= 		'<div id="popup_' . (is_null( $alternate_key ) ? get_class( $this ) : $alternate_key) . '_container" class="' . (($this->active) ? 'F-active' : 'F-inactive') . '">';
			$return .= 			'<div class="Title">' . $this->title . '</div>';
			$return .=  		'<div id="popup_' . (is_null( $alternate_key ) ? get_class( $this ) : $alternate_key) . '_help" class="Desc">' . $this->help_text . '</div>';
			$return .=			'<div id="popup_' . (is_null( $alternate_key ) ? get_class( $this ) : $alternate_key) . '_thumb" class="' . (($this->active) ? 'ThumbActive' : 'ThumbInActive') . '"><div class="' . (is_null( $alternate_key ) ? get_class( $this ) : 'Generic_Thumb') . '" id="' . (is_null( $alternate_key ) ? get_class( $this ) : $alternate_key) . '"></div></div>';
			$return .=			'<div id="popup_' . (is_null( $alternate_key ) ? get_class( $this ) : $alternate_key) . '_info" class="Feature' . (($this->active) ? 'Active' : 'InActive') . 'Info"> Feature is ' . (($this->active) ? 'active' : 'inactive') . '.</div>';
			$return .=			'<div id="popup_' . (is_null( $alternate_key ) ? get_class( $this ) : $alternate_key) . '_image" class="' . (($this->active) ? 'Disable' : 'Enable') . '"><a href="javascript:;" onclick="' . ($this->IsActive() ? 'op_admin.AjaxOnOff(\'opcp_' . (is_null( $alternate_key ) ? get_class( $this ) : $alternate_key) . 'Deactivate\')"><img src="' . get_option('home') . '/wp-content/plugins/OnePanel/images/default/pop_content/disable.gif" border="0" />' : 'op_admin.AjaxOnOff(\'opcp_' . (is_null( $alternate_key ) ? get_class( $this ) : $alternate_key) . 'Activate\')"><img src="' . get_option('home') . '/wp-content/plugins/one-panel/images/default/pop_content/enable.gif" border="0" />') . '</a></div>';
			$return .=		'</div>';
			$return .= '</div>';
			
			return $return;
		}
		
		public function GetChunk( $chunk_name ) {
			return $this->detail_chunks[$chunk_name];
		}
		
	}