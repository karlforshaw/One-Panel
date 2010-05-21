<?php

	class FeaturedVideoFeature extends OnePanelFeature {
	
		protected 	$title = 'Featured Video';
		protected 	$help_text = 'Enable Featured Video by pasting embed code or by entering a YouTube URL.';
		
		/**
		 * Video Mode
		 *
		 * Accepts the following:
		 * 
		 * [2]	YouTube URL
		 * [3]	Embed Code
		 * 
		 * @var int
		 */
		private 	$video_mode = 2;
		private		$youtube_url = null;
		private		$embed_code = null;
		
		public function SaveYouTube() {
			
			$url = $_POST['url'];
			
			if (is_string( $url )) {
				
				$this->youtube_url = $url;
				OnePanel::PackData();
				die(true);
			}
			
		}
		
		public function SaveEmbed() {
			
			$code = stripslashes( $_POST['code'] );
			
			if (is_string( $code )) {
				
				$this->embed_code = $code;
				OnePanel::PackData();
				die(true);
			}
			
		}
		
		public function SetVideoMode( $mode ) {
			
			if (is_int( $mode )) {
				$this->video_mode = $mode;
			}
			
		}
		
		public function RenderYoutubeEntry() {
			
			$response  = '<div style="display:block;padding-top:10px;">' . "\n";
			$response .= 	'<div style="float:left;">' . "\n";
			$response .= 		'<label class="label" for="YouTube_URL">YouTube Video URL &darr;</label>' . "\n";
			$response .= 	'</div>' . "\n";
			$response .= 	'<div style="float:left;padding:0 0 0 5px;">' . "\n";
			$response .= 		'<div id="popup_featured_video_status"></div>' . "\n";
			$response .= 	'</div>' . "\n";
			$response .= '</div>' . "\n";
			$response .= '<input class="VidInput" maxlength="83" name="" id="YouTube_URL" type="text" value="' . $this->youtube_url . '"  onpaste="op_admin.FeaturedVideo.SaveYouTube()" onkeydown="op_admin.FeaturedVideo.SaveYouTube()" />' . "\n";
			
			return $response;
		}
		
				
		public function RenderCodeEntry() {
			
			$video_dims = OnePanelConfig::GetYoutubeVideoSize();	
			
			if( $video_dims ) {
				
				$response  = '<div style="float:right;width:33%;padding-top:10px;"><label class="label">Guideline Video Size</label>' . "\n";
				$response .= '<div class="html_video"><div class="html_videostroke" style="font-weight:normal;margin:0;text-align:justify;">' . "\n";
				$response .= '<div class="video-width">Width</div>Video width should be: <b>' . $video_dims['Width'] . '</b><br /><br />
	<div class="video-height">Height</div>Video height should be: <b>' . $video_dims['Height'] . '</b>' . "\n";
				$response .= '</div>' . "\n";
				$response .= '</div>' . "\n";
				$response .= '</div>' . "\n";
			}
			else {
				
				$response  = '<div style="float:right;width:33%;padding-top:10px;"><label class="label">Guideline Video Size</label>' . "\n";
				$response .= '<div class="html_video"><div class="html_videostroke" style="font-weight:normal;margin:0;text-align:justify;">' . "\n";
				$response .= '<div style="line-height:22px;">Sorry, the theme you have enabled doesn\'t have any guideline video dimensions.</div>' . "\n";
				$response .= '</div>' . "\n";
				$response .= '</div>' . "\n";
				$response .= '</div>' . "\n";
			}
			
			$response .= '<div class="vid_embed_box">' . "\n";
			$response .= '<div id="popup_featured_video_status"></div>' . "\n";
			$response .= '<label class="html_label label" for="ot_embed_video">Video Embed Code &darr;</label>' . "\n";
			$response .= '<div style="clear:both;"></div>';
			$response .= '<textarea class="ot_embed_video" rows="3" cols="43" id="ot_embed_video" name="ot_embed_video" onpaste="op_admin.FeaturedVideo.SaveEmbed()" onkeydown="op_admin.FeaturedVideo.SaveEmbed()"> ' . htmlspecialchars( $this->embed_code ) . '</textarea>' . "\n";
			
			$response .= '</div>' . "\n";
			$response .= '<div style="clear:both;"></div>' . "\n";
			
			return $response;
			
			}
		
		
		public function Render() {
			
			switch ($this->video_mode) {
				case 2: 
					$youtube_tab = 	'TabActive';
					$embed_tab = 	'TabInActive';
					$entry_mode = $this->RenderYoutubeEntry();	
				break;
				
				case 3:
					$youtube_tab = 	'TabInActive';
					$embed_tab = 	'TabActive';
					$entry_mode = $this->RenderCodeEntry();
				break;
			}
			
			$response  = $this->RenderOnOff();
			$response .= '<div class="generic_content" style="width:680px;margin:0 auto;">' . "\n";
			$response .= '<div id="popup_featured_video_container">' . "\n";	
			$response .= 	'<div style="clear:both;"><div id="featured_video_youtube_tab" class="' . $youtube_tab  . '"><a href="javascript:;" onclick="op_admin.FeaturedVideo.SwitchEntryMode(\'youtube\')">YouTube</a></div><div id="featured_video_code_tab" class="' . $embed_tab . '"><a href="javascript:;" onclick="op_admin.FeaturedVideo.SwitchEntryMode(\'embed\')">Embed Code</a></div></div>' . "\n";
			$response .= 	'<div class="TabText">Choose between YouTube or Embed Code &rarr;</div>' . "\n";
			
			$response .= '<div style="clear:both;">' . "\n";
			$response .= '<div id="popup_video_container">' . "\n";
			
			$response .= $entry_mode;
			
			$response .= '</div>' . "\n";
			
			$response .=	'<div class="vid_preview">' . "\n";
			$response .=	'<div class="label"><strong>Video Preview &darr;</strong></div>' . "\n";
			$response .=	'<div id="popup_video_preview">' . "\n";
			
			
			if (! $this->active) {
				$response .= $this->GetInactiveMessage();
			}
			else {
				
				$chunk = $this->GetChunk();
				if (empty($chunk)) $response .= $this->GetInactiveMessage();
				else $response .= $this->GetChunk();
				
			}
			
			$response .=	'</div>' . "\n";
			$response .=	'</div>' . "\n";
			$response .=	'<div style="clear:both;height:6px;"></div>' . "\n";
			
			
			$response .= '</div>' . "\n";
			$response .= '</div>' . "\n";
			
			return $response;
		}
		
		public function GetInactiveMessage() {
			return '<div class="popup_no_results"><div class="module_error_stroke" style="padding:7px;font-size:11px;">Featured Video is currently inactive.</div></div>' . "\n";
		}
		
		public function GetChunk() {
			if ($this->IsActive()) {
				
				if ($this->video_mode == 2) {
					
					if (empty( $this->youtube_url )) return; // TODO need decent return messages.
					
					$the_data = FeaturedVideoModule::GetYouTubeVideo( $this->youtube_url ); // TODO check the url is not empty
					$the_chunk = '';
					
					if($the_data['xml_response'] == 'Video not found') {
						$the_chunk .= '<div id="video-not-found">' . "\n";
						$the_chunk .= 'Sorry, the video has been removed.' . "\n";
						$the_chunk .= '</div>' . "\n";
					}
					else {
						
						$video_size = OnePanelConfig::GetYoutubeVideoSize();
						
						if (! is_array( $video_size )) {
							
							$video_size['Width'] = 216;
							$video_size['Height'] = 197;
							
						}
						
						$the_chunk .= '<div id="featured-video">' . "\n";
						$the_chunk .= '<object data="' . $the_data['url'] . '" type="' . $the_data['type'] . '" width="' . $video_size['Width'] . '" height="' . $video_size['Height'] . '">' . "\n";
						$the_chunk .= '	<param name="movie" value="' . $the_data['url'] . '"></param>'. "\n";
						$the_chunk .= '</object>'. "\n";
						$the_chunk .= '</div>' . "\n";
						
					}
					
					return $the_chunk;
					
				}
				elseif ($this->video_mode == 3) {
					
					if (empty( $this->embed_code )) return; // TODO need decent return messages.
					
					$the_chunk = '';
					
					$the_chunk .= '<div id="featured-video">' . "\n";
					$the_chunk .= stripcslashes( $this->embed_code );
					$the_chunk .= '</div>' . "\n";
					
					return $the_chunk;
					
				}
				else {
					if (OnePanelConfig::UsingDebug()) return $this->GetInactiveMessage();
				}
				
			}
		}
		
	}

	class FeaturedVideoModule extends OnePanelModule {
		
		protected $title 		= 'Featured Video';
		protected $help_text 	= 'Enable Featured Video by pasting embed code or by entering a YouTube URL.';
		protected $description 	= 'Dive into the new online video craze with Featued Video. Add embed code from your favourite video sharing websites or insert videos from YouTube by pasting in the URL.';
		protected $short_description = 'Enable Featured Video by pasting embed code or by entering a YouTube URL.';
		protected $keywords 	= array( 'featured', 'video', 'featured video', 'youtube' );
		protected $categories 	= array( 'Featured' );
		
		public function Render() {
			$this->GenericRender();
		}
		
		public function BuildChunks() {
			if ($this->features['FeaturedVideoFeature']->IsActive()) $this->chunks['Header'] = '<div class="title">' . OnePanelLanguage::GetText( 'featured_video' ) . '</div>' . "\n";
			$this->chunks['Video'] = $this->features['FeaturedVideoFeature']->GetChunk();
		}
		
		public function RegisterFeatures() {
				$this->RegisterFeature( 'FeaturedVideoFeature' );
		}
		
		
		public static function GetYouTubeVideo( $url=null ) {
			
			// Parse the URL
			if (is_null($url)) {
				return false;
			}
			else {
				$featured_video_url_segments = explode('=', $url);
			}
			
			// If they used a junky url
			if (isset($featured_video_url_segments[1]))	$featured_video_id = $featured_video_url_segments[1];
			else 										$featured_video_id = '';
			
			// Make the connection to YouTube
			if(! $yh = @fsockopen( 'gdata.youtube.com', '80', $error_no, $error_str, 30 )) {
				// Debug
				//die("Cannot connect to YouTube");
				return false;
			}
			
			// Send the request for our video information
			fwrite( $yh, "GET http://gdata.youtube.com/feeds/api/videos/{$featured_video_id}\n" );
			$xml_response = '';
			
			// Get YouTube's Response
			while (! feof( $yh )) {
				$xml_response .= fgets( $yh, 1024);
			}
			
			// Extract the row of data we need from the response
			preg_match( 
				"/\<media:content(.*)yt:format='5'\/\>/",
				$xml_response,
				$matches
			);
			
			// Clean it up and convert it to an array
			$end = preg_replace( 
				array(
					'/(<media:content )(.*)(\/\>)/',
					"/'/",
					'/\s/'
				),
				array(
					'$2',
					'',
					'&'
				),
				$matches[0]
			); 
			parse_str( $end, $the_data );
			
			$the_data['xml_response'] = $xml_response;
			return $the_data;
		}
		
		
		
		
		// AJAX STUFF
		
		public function SwitchEntryMode() {
		
			$entry_mode = mysql_real_escape_string( $_POST['entry_mode'] );
			
			$feature = &$this->features[ 'FeaturedVideoFeature' ];
			
			if (is_object( $feature )) {
				if ($entry_mode == 'youtube') {
					
					$feature->SetVideoMode( 2 );
					OnePanel::PackData();
					die( $feature->RenderYoutubeEntry() );
					
				}
				elseif ($entry_mode == 'embed') {
					
					$feature->SetVideoMode( 3 );
					OnePanel::PackData();
					die( $feature->RenderCodeEntry() );
					
				}
			}
			else {
				die( 'No feature by the name FeaturedVideoFeature' );
			}
			
		}
		
		
		public function GetPreview() {
			
			$feature = &$this->features['FeaturedVideoFeature'];
			
			if (is_object( $feature )) {
				
				if ($feature->IsActive() == true) {
					
					$response = $feature->GetChunk();
					if (empty( $response )) $response = $feature->GetInactiveMessage();
					
				}
				else {
					$response = $feature->GetInactiveMessage();
				}
				
				die( $response );
				
			}
			else {
				die( 'No feature by the name of FeaturedVideoFeature' );
			}
		}
		
	}