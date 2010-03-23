<?php

	class FeedBurnerToggle extends OnePanelFeature {
		
		protected $title = 'FeedBurner';
		protected $help_text = 'Use the Feedburner feature to redirect your blog\'s RSS traffic to your Feedburner feed.';
		
		private $url = null;
		private $id = null;
		
		public function GetFeedburnerID() {
			return $this->id;
		}
		
		public function GetFeedBurnerURL() {
			return $this->url;
		}
		
		public function SetFeedburnerID( $id ) {
			
			if (is_string( $id )) {
				$this->id = $id;
			}
			
		}
		
		public function SetFeedBurnerURL( $url ) {
			
			if (is_string( $url )) {
				$this->url = $url;
			}
			
		}
		
		public function Render() {
			
		  $response  = $this->RenderOnOff();
		  $response .=	'<div class="FeedBurnerContent">';
		  $response .=		'<h2>FeedBurner Settings &darr;</h2>';
		  $response .=		'<div id="popup_feedburner_entry_area">';
		  
		  $response .= 			'<label for="popup_feedburner_id"><strong>FeedBurner ID</strong></label>';
		  $response .=		'<div style="padding:0 0 10px 0;">If you\'re using a Google FeedBurner account please leave this field blank.</div>';
		  $response .=			'<div style="display:block;width:670px;">';
		  $response .=				'<div style="width:90%;float:left;">';
		  $response .= 					'<input style="width:605px;" id="popup_feedburner_id" name="popup_feedburner_id" onpaste="op_admin.FeedBurner.SaveID()" onkeydown="op_admin.FeedBurner.SaveID()" type="text" maxlength="63" value="' . $this->id . '" size="40" />';
		  $response .=				'</div>';
		  $response .=			'<div style="float:left;">';
		  $response .=				'<div id="popup_feedburner_id_status"></div>';
		  $response .=			'</div>';
		  $response .=		'</div>';
		  
		  $response .=		'<div style="clear:both;"></div>';
		  
		  $response .= 			'<label for="popup_feedburner_url"><strong>FeedBurner URL</strong></label>';
		  $response .=		'<div style="padding:0 0 10px 0;"><strong style="color:green;">Feedburner Example:</strong> http://feeds.feedburner.com/OneThemeDemo &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong style="color:green;">Google Example:</strong> OneThemeDemo</div>';
		  $response .=			'<div style="display:block;width:670px;">';
		  $response .=				'<div style="width:90%;float:left;">';
		  $response .= 			'<input style="width:605px;" id="popup_feedburner_url" name="popup_feedburner_url" onpaste="op_admin.FeedBurner.SaveUrl()" onkeydown="op_admin.FeedBurner.SaveUrl()" maxlength="63" type="text" value="' . $this->url . '" size="40" />';
		  $response .=				'</div>';
		  $response .=				'<div style="float:left;">';
		  $response .=					'<div id="popup_feedburner_url_status"></div>';
		  $response .=				'</div>';
		  $response .=			'</div>';
		  
		  $response .=		'</div>';
		  $response .=	'</div>';
			
			return $response;
		}
		
		public function FeedburnerFilter() {
	
			// Check this is active 
			if (! $this->IsActive()) {
				return false;
			}
			
			// Bounce Feed Burner
			if (preg_match( '/feedburner/i', $_SERVER['HTTP_USER_AGENT'] )) {
				return false;
			}
			
			// Do nothing if not a feed
			if (! is_feed()) {
				return false;
			}
			
			// Do nothing if not configured
			if (empty( $this->url )) {
				return false;
			}
		
			if ((empty( $this->id )) && (! empty( $this->url ))) { // Probably a Google Feedburner account
				header( 'Location: http://feeds2.feedburner.com/' . $this->url );
			}
			else {
				header( 'Location: ' . $this->url );
			}
			die;
		}
		
	}

	class FeedBurner extends OnePanelModule {
		
		protected $title = 'Feedburner';
		protected $help_text = 'Use the Feedburner feature to redirect your blog\'s RSS traffic to your Feedburner feed.';
		protected $description ='By entering your Feedburner ID you will have the ability to use Feedburner with your wordpress blog. The feature will automatically re-direct your RSS traffic to your unique Feedburner.com page.';
		protected $short_description ='Use the Feedburner feature to redirect your blog\'s RSS traffic to your Feedburner feed.';
		protected $keywords = array( 'feedburner', 'feed', 'burner', 'rss' );
		protected $categories = array( 'Misc' );
		
		public function Render() {
			$this->GenericRender();
		}
		
		public function RegisterFeatures() {
			$this->RegisterFeature( 'FeedBurnerToggle' );
		}
		
		public function BuildChunks() {
			
			$feature = &$this->features['FeedBurnerToggle'];
			
			// The Subscribe Box
			if ($feature->IsActive()) {
			
				$url = $feature->GetFeedBurnerURL(); 
				$id = $feature->GetFeedBurnerID();
				
				$this->chunks['Header'] = '<div class="title">' . OnePanelLanguage::GetText( 'subscribe' ) . '</div>' . "\n";
				
				if ((empty( $id )) && (! empty( $url ))) { // Probably a Google Feedburner account

					$chunk  = 	'<form action="http://feedburner.google.com/fb/a/mailverify" method="post" target="popupwindow" onsubmit="window.open(\'http://feedburner.google.com/fb/a/mailverify?uri=' . $url . '\', \'popupwindow\', \'scrollbars=yes,width=550,height=520\');return true">';
					$chunk .= 		OnePanelLanguage::GetText( 'subscribe_detail' ) . "\n";
					$chunk .=		'<input type="text" style="width:140px" name="email" value="' . OnePanelLanguage::GetText( 'subscribe_email' ) . '" onfocus="if(this.value==\'' . OnePanelLanguage::GetText( 'subscribe_email' ) . '\'){this.value=\'\'}" onblur="if(this.value==\'\'){this.value=\'' . OnePanelLanguage::GetText( 'subscribe_email' ) . '\'}" />';
					$chunk .=		'<input type="hidden" value="' . $url . '" name="uri"/><input type="hidden" name="loc" value="en_US"/>';
					$chunk .= 		'<input type="submit" value="' . OnePanelLanguage::GetText( 'subscribe' ) . '" />';
					$chunk .=	'</form>';
					
				}
				elseif (! empty( $id )) { // Probably an old Feedburner account
					
					$chunk  = 	'<form action="http://www.feedburner.com/fb/a/emailverify" method="post" target="popupwindow" onsubmit="window.open(\'http://www.feedburner.com/fb/a/emailverifySubmit?feedId=' . $id . '\', \'popupwindow\', \'scrollbars=yes,width=550,height=520\');return true" >' . "\n";
					$chunk .= 		OnePanelLanguage::GetText( 'subscribe_detail' ) . "\n";
					$chunk .= 		'<input type="text" id="subbox" name="email" value="' . OnePanelLanguage::GetText( 'subscribe_email' ) . '" onfocus="if(this.value==\'' . OnePanelLanguage::GetText( 'subscribe_email' ) . '\'){this.value=\'\'}" onblur="if(this.value==\'\'){this.value=\'' . OnePanelLanguage::GetText( 'subscribe_email' ) . '\'}" /><input type="submit" value="' . OnePanelLanguage::GetText( 'subscribe' ) . '" />' . "\n";
					$chunk .= 		'<input type="hidden" value="http://feeds.feedburner.com/~e?ffid=' . $id  . '" name="uri"/>' . "\n";
					$chunk .= 		'<input type="hidden" value="'.get_option('blogname').'" name="title" />' . "\n";
					$chunk .= 	'</form>' . "\n";
					
				}
				
				$this->chunks['Form'] = &$chunk;
			
			}
			
		}
		
		public function SaveID() {
			
			$feedburner_id = mysql_real_escape_string( $_POST['id'] );
			
			if (is_string( $feedburner_id )) {
				
				$feature = &$this->features[ 'FeedBurnerToggle' ];
				
				if (is_object( $feature )) {
					
					$feature->SetFeedBurnerID( $feedburner_id );
					OnePanel::PackData();
					
					die(true);
				}
				else {
					die( 'No Feature by the name FeedBurnerToggle' );
				}
			}
			
		}
		
		public function SaveURL() {
			
			$feedburner_url = mysql_real_escape_string( $_POST['url'] );
			
			if (is_string( $feedburner_url )) {
				
				$feature = &$this->features[ 'FeedBurnerToggle' ];
				
				if (is_object( $feature )) {
					
					$feature->SetFeedBurnerURL( $feedburner_url );
					OnePanel::PackData();
					
					die(true);
				}
				else {
					die( 'No Feature by the name FeedBurnerToggle' );
				}
			}
			
		}
		
	}