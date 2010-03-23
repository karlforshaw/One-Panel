<?php

	final class AdBlockFeature extends OnePanelFeature {
		
		/**
		 * Ad Mode
		 *
		 * Determines the mode of the advertisement, current allowed values:
		 * 
		 * (int) 2		Code Mode
		 * (int) 3		AdSense Mode
		 * 
		 * @var int
		 */
		private $ad_mode = 3;
		
		private $ad_code = null;
		private $adsense_id = null;
		private $adsense_channel = null;
		private $show_header = true; // TODO?
		
		protected $help_text = 'Start displaying Google Adsense or any other advertisement by using the Advertising module.';
		
		public function SetAdsenseId( $id ) {
			
			if (is_string( $id )) {
				$this->adsense_id = $id;
			}
			
		}
		
		public function SetAdsenseChannel( $channel ) {
			
			if (is_string( $channel )) {
				$this->adsense_channel = $channel;
			}
			
		}
		
		public function SetAdCode( $code ) {
			
			if (is_string( $code )) {
				$this->ad_code = $code;
			}
			
		}
		
		public function SetAdMode( $mode ) {
			
			if (is_int( $mode )) {
				$this->ad_mode = $mode;
			}
			
			
		}
		
		public function GetChunk() {
			
			if ($this->IsActive()) {
				$title = str_replace( ' ', '_', $this->title );
				$return = '<div class="adblock-' . strtolower( $title ) . '">'. "\n";
				
				if ($this->ad_mode == 2) {
					$return .= stripcslashes( $this->ad_code );
				}
				elseif ($this->ad_mode == 3) {
					
					$the_block = &OnePanelConfig::GetAdBlock( $this->title );
					$the_palette  = &$the_block->GetPalette( OnePanelTheme::GetActiveSkin()->GetName() );
					
					$return .= '<script type="text/javascript"><!--' . "\n";
					$return .= 'google_ad_client = "' . $this->adsense_id . '";' . "\n";
					$return .= 'google_ad_width = "' . $the_block->GetWidth() . '";' . "\n";
					$return .= 'google_ad_height = "' . $the_block->GetHeight() . '";' . "\n";
					$return .= 'google_ad_format = "' . $the_block->GetWidth() . 'x' . $the_block->GetHeight() . '_as";' . "\n";
					$return .= 'google_ad_type = "text_image";' . "\n";
					$return .= 'google_color_border = "' . $the_palette->GetBorderColor() . '";' . "\n";
					$return .= 'google_color_bg = "' . $the_palette->GetBackgroundColor() . '";' . "\n";
					$return .= 'google_color_link = "' . $the_palette->GetLinkColor() . '";' . "\n";
					$return .= 'google_color_text = "' . $the_palette->GetTextColor() . '";' . "\n";
					$return .= 'google_color_url = "' . $the_palette->GetUrlColor() . '";' . "\n";
					$return .= is_null($this->adsense_channel) ? '' : 'google_ad_channel = "' . $this->adsense_channel . '";' . "\n";
					$return .= '//--></script>' . "\n";
					$return .= '<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js">' . "\n";
					$return .= '</script>' . "\n";
					
				}
				
				$return .= '</div>'. "\n";
				
				return $return;
			}
		}
		
		public function RenderAdsenseEntry() {
			$response = '<div id="popup_adsense_id_status"></div>';
			$response .= '<label for="popup_adsense_id"><strong>Adsense ID &darr;</strong></label>';
			$response .= '<input id="popup_adsense_id" name="popup_adsense_id"  onpaste="op_admin.Advertising.SavePubID()" onkeydown="op_admin.Advertising.SavePubID()" type="text" maxlength="34" value="' . $this->adsense_id . '" size="40" />';
			
			$response .= '<div id="popup_adsense_channel_status"></div>';
			$response .= '<label for="popup_adsense_channel"><strong>Adsense Channel &darr;</strong></label>';		
			$response .= '<input id="popup_adsense_channel" name="popup_adsense_channel"  onpaste="op_admin.Advertising.SaveChannel()" onkeydown="op_admin.Advertising.SaveChannel()" maxlength="34" type="text" value="' . $this->adsense_channel . '" size="40" />';
			
			return $response;
		}
		
		public function RenderHTMLEntry() {
			//$response .= '<label for="popup_adblock_html">Ad HTML</label><div id="popup_adsense_code_status"></div>';
			//$response .= '<textarea id="popup_ad_code" rows="5" cols="60" name="popup_ad_code" onkeydown="op_admin.Advertising.SaveCode()">' . $this->ad_code . '</textarea>';
			$response  = '<div style="display:block;">';
			$response .= '<div style="float:left;">';
			$response .= '<label for="popup_adblock_html">Ad HTML &darr;</label>';
			$response .= '</div>';
			$response .= '<div id="popup_adsense_code_status"></div>';
			$response .= '<textarea id="popup_ad_code" rows="5" cols="60" name="popup_ad_code" onpaste="op_admin.Advertising.SaveCode()" onkeydown="op_admin.Advertising.SaveCode()">' . htmlspecialchars( $this->ad_code ) . '</textarea>';
			$response .= '</div>';
			
			return $response;
		}
		
		public function Render() {
			
			if ($this->ad_mode == 2) {
				$code_tab = 'TabActive';
				$adsense_tab = 'TabInActive';
				$entry_code = $this->RenderHTMLEntry();
			}
			elseif ($this->ad_mode == 3) {
				$code_tab = 'TabInActive';
				$adsense_tab = 'TabActive';
				$entry_code = $this->RenderAdsenseEntry();
			}
			
			$response  =  $this->RenderOnOff();
			$response .=	'<div class="AdTabs">';
			$response .=	'<div id="advertising-adsense-tab" class="' . $adsense_tab . '"><a href="javascript:;" onclick="op_admin.Advertising.SwitchEntryMode(\'adsense\')">Adsense</a></div>';
			$response .=	'<div id="advertising-html-tab" class="' . $code_tab . '"><a href="javascript:;" onclick="op_admin.Advertising.SwitchEntryMode(\'html\')">HTML</a></div>';
			$response .= 	'<div class="TabText">Choose between Adsense or HTML &rarr;</div>';
			$response .=	'</div>';
			$response .=	'<div class="AdContent">';
			$response .=	'<div style="clear:both;"></div>';
			$response .=		'<div id="popup_adblock_entry_area">' . $entry_code . '</div>';
			$response .=	'</div>';

			return $response;
		}
		
	}


	class AdvertisingModule extends OnePanelModule {
		
		protected $title = 'Advertising';
		protected $description = 'Start earning revenue from your blog today with the Advertising module. Insert Google Adsense, CPM ads or any custom banner with ease.';
		protected $short_description = 'Start displaying Google Adsense or any other advertisement by using the Advertising module.';
		protected $help_text = 'Start displaying Google Adsense or any other advertisement by using the Advertising module.';
		protected $keywords = array( 'advertising', 'adsense', 'ads', 'google', 'advertisement', 'advertisements', 'sponsors', 'sponsor', 'ad block', 'adblock', 'cpm', 'cpc', 'affiliate' );
		protected $categories = array( 'Advertising' );
		
		public function Render() {
			
			// Increase the view count
			$this->IncreaseViewCount();
			
			// Print the selector menu
			$response['content']  = '<div class="AdDrop">';
			$response['content'] .= '<div class="AdDropTitle left_side">';
			$response['content'] .= '<label for="popup_ablock_select"><span class="BB">Please select an AdBlock &rarr;</span></label>';
			$response['content'] .= '</div>';
			$response['content'] .= '<div class="right_side">';
			$response['content'] .= '<select onchange="op_admin.Advertising.SwitchBlock()" id="popup_ablock_select">';
			
			foreach ($this->features as $key => &$feature) {
				$response['content'] .= '<option>' . $feature->GetTitle() . '</option>';
			}
			
			$response['content'] .= '</select>';
			$response['content'] .= '</div>';
			$response['content'] .= '</div>';
			$response['content'] .= '<div class="DropShadowGreen"></div>';
			$response['content'] .= '<div style="clear:both"></div>';
			
			// Get the first adblock and print the management console for it.
			reset( $this->features );
			$first_block = current( $this->features );
			$response['content'] .= '<div id="popup_adblock_container">';
			$response['content'] .= 	$this->PrintBlockManager( $first_block );
			$response['content'] .= '</div>';
			
			$response['title'] = $this->title;
			$response['info'] = $this->help_text;
			
			die( json_encode( $response ) );
		}
		
		public function BuildChunks() {
			
			// Does this want to cycle the features or the adblocks?
			foreach ($this->features as $key => $feature) {
				$this->chunks[$key] = $feature->GetChunk();
			}
			
		}
		
		/**
		 * Register Features
		 * 
		 * A very customised version of this normally simple function
		 * 
		 */
		public function RegisterFeatures() {
			
			$ad_blocks 	= &OnePanelConfig::GetAdBlocks();
			
			// Dont do anything if they havent added any adblocks to the config
			if (is_array( $ad_blocks )) {
				
				foreach ($ad_blocks as $key => &$block) {
					
					if (! isset( $this->features[ self::GetBlockKey( $block->GetName() ) ] )) {
						
						$new_block = new AdBlockFeature();
						$new_block_key = self::GetBlockKey( $block->GetName() );
						
						$new_block->SetTitle( $block->GetName() );
						$new_block->Enable();
						$new_block->SetAlternateKey( $new_block_key );
						
						/*
						 * We don't use a refrence here as the source object will be replaced 
						 * after this iteration.
						 */
						$this->features[ $new_block_key ] = $new_block;
						
						// We now refrence the permanent object previously created.
						$this->registered_features[] = &$this->features[ $new_block_key ];
						$this->enabled_features[] = $new_block_key;
						
					}
					else {
						
						$the_block_key = self::GetBlockKey( $block->GetName() );
						$the_block = &$this->features[$the_block_key];
						
						// Make sure its enabled
						$the_block->Enable();
						
						// Garbage collection and enabled state
						$this->registered_features[] = &$the_block;
						$this->enabled_features[] = $the_block_key;
						
					}
					
					// Set up ajax for it
					add_action( 'wp_ajax_opcp_' . self::GetBlockKey( $block->GetName() ) . 'Activate', array( $this->features[ self::GetBlockKey( $block->GetName() ) ], 'Activate' ) );
					add_action( 'wp_ajax_opcp_' . self::GetBlockKey( $block->GetName() ) . 'Deactivate', array( $this->features[ self::GetBlockKey( $block->GetName() ) ], 'Deactivate' ) );
					
				}
				
			}
			
		}
		
		static public function GetBlockKey( $blockname ) {
			return 'AdBlock' . $blockname . 'Feature';
		}
		
		private function PrintBlockManager( $block ) {
			
			$response =	$block->Render();
			return $response;
			
		}
		
		
		// Ajax Stuff
		public function SwitchBlock() {
			
			$block_name = mysql_real_escape_string( $_POST['block_name'] );
			$block = &$this->features[ self::GetBlockKey( $block_name ) ];
			
			if (is_object( $block )) {
				die( $this->PrintBlockManager( $block ) );
			}
			else {
				die( 'No Adblock by the name' . $block_name );
			}
			
		}
		
		public function SwitchEntryMode() {
		
			$block_name = mysql_real_escape_string( $_POST['block_name'] );
			$entry_mode = mysql_real_escape_string( $_POST['entry_mode'] );
			
			$block = &$this->features[ self::GetBlockKey( $block_name ) ];
			
			if (is_object( $block )) {
				if ($entry_mode == 'html') {
					
					$block->SetAdMode( 2 );
					OnePanel::PackData();
					
					die( $block->RenderHTMLEntry() );
					
				}
				elseif ($entry_mode == 'adsense') {
					
					$block->SetAdMode( 3 );
					OnePanel::PackData();
					
					die( $block->RenderAdsenseEntry() );
					
				}
			}
			else {
				die( 'No Adblock by the name' . $block_name );
			}
			
		}
		
		public function SavePubID() {
			
			$adsense_id = mysql_real_escape_string( $_POST['id'] );
			$block_name = mysql_real_escape_string( $_POST['block_name'] );
			
			if (is_string( $adsense_id )) {
				
				$feature = &$this->features[ $this->GetBlockKey( $block_name ) ];
				
				if (is_object( $feature )) {
					
					$feature->SetAdsenseId( $adsense_id );
					OnePanel::PackData();
					
					die(true);
				}
				else {
					die( 'No Adblock by the name' . $block_name );
				}
			}
			
		}
		
		public function SaveChannel() {
			
			$adsense_channel = mysql_real_escape_string( $_POST['channel'] );
			$block_name = mysql_real_escape_string( $_POST['block_name'] );
			
			if (is_string( $adsense_channel )) {
				
				$feature = &$this->features[ $this->GetBlockKey( $block_name ) ];
				
				if (is_object( $feature )) {
					
					$feature->SetAdsenseChannel( $adsense_channel );
					OnePanel::PackData();
					
					die(true);
				}
				else {
					die( 'No Adblock by the name' . $block_name );
				}
			}
			
		}
		
		public function SaveCode() {
			
			$ad_code = stripslashes( $_POST['code'] );
			$block_name = mysql_real_escape_string( $_POST['block_name'] );
			
			if (is_string( $ad_code )) {
				
				$feature = &$this->features[ $this->GetBlockKey( $block_name ) ];
				
				if (is_object( $feature )) {
					
					$feature->SetAdCode( $ad_code );
					OnePanel::PackData();
					
					die(true);
				}
				else {
					die( 'No Adblock by the name' . $block_name );
				}
			}
			
		}
		
		
	}