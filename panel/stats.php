<?php

	class StatsFeature extends OnePanelFeature {
		
		protected $title = 'Stats & Tracking Code';
		protected $help_text = 'Use Stats & Tracking to insert tracking code from Google Analytics, Statcounter Etc.';
		
		protected $stats_code = null;
		
		public function Render() {
			
			$return  =	'<div class="module_title">';
			$return .= 		'<div class="popup_generic_title">' . $this->title .'</div>';
			$return .= 		'<div class="popup_generic_desc">' . $this->help_text .'</div>';
			$return .= 		'<div class="thumb_container">';
			$return .= 			'<div class="' . get_class( $this ) . '_thumb"></div>';
			$return .= 		'</div>';
			$return .=	'</div>';
			$return .=	'<div class="generic_content">';
			$return .=		'<div style="display:block;">';
			$return .=		'<div style="font-weight:bold;padding:5px 0 10px 0;color:#555;font-size:13px;float:left;width:22%;">Enter Tracking Code &darr;</div>';
			$return .=			'<div id="popup_stats_status"></div>';
			$return .=		'</div>';
			$return .=		'<div style="clear:both;"></div>';
			$return .=		'<div style="display:block;">';
			$return .=		'<textarea class="stats_textarea" rows="5" cols="60" id="popup_stats_code" name="op_tracking_code"  onpaste="op_admin.Stats.Save()" onkeydown="op_admin.Stats.Save()">' . htmlspecialchars( $this->stats_code ) . '</textarea>';
			$return .=		'</div>';
			$return .=	'</div>';
			
			return $return;
		}
		
		public function Save() {
			
			$code = $_POST['code'];
			
			$this->stats_code = stripslashes($code);
			OnePanel::PackData();
			
			die( true );
			
		}
		
		public function GetStatsCode() {
			return $this->stats_code;
		}
		
	}

	class StatsModule extends OnePanelModule {

		protected $title = 'Stats & Tracking';
		protected $help_text = 'Use Stats & Tracking to insert tracking code from Google Analytics, Statcounter Etc.';
		protected $description = 'Start tracking your website visitors today with the Stats & Tracking module. Easily insert custom code from websites like Google Analytics or Statcounter.';
		protected $short_description = 'Use Stats & Tracking to insert tracking code from Google Analytics, Statcounter Etc.';
		protected $keywords = array( 'stats', 'tracking', 'code', 'analytics', 'statcounter' );
		protected $categories = array( 'Misc' );
		
		public function Render(){
			$this->GenericRender();
		}
		
		public function BuildChunks() {
			
			// Stats Code
			$this->chunks['StatsCode'] = stripcslashes( $this->features['StatsFeature']->GetStatsCode() );
			
		}
		
		protected function RegisterFeatures() {
			$this->RegisterFeature( 'StatsFeature' );
		}
		
	}