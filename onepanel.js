op_admin = {

	Editing: false,
	run_news: false,

	AjaxRender: function(action) {
		
		new Ajax.Request( 'admin-ajax.php', 
	 	{
		    method:'post',
		    postBody: 'action=' + action,
		    
		    onLoading: function() {
		    	$('overlay').show();
		    },
		    
		    onSuccess: function(transport){
		      
		      var response = transport.responseText || "no response text";
		      response = eval('(' + response + ')'); 
		      
		      $('action-title').update( response.title + '<div>' + response.title + '</div>' );
		      $('action-content').update( response.content );
		      $('action-frame').appear();
		      
		    },
		    
		    onFailure: function(){ alert('Something went wrong...') }
	 	});
	 	
	}, 	
	
	AjaxOnOff: function(action) {
		
		new Ajax.Request('admin-ajax.php',
	 	{
		    method:'post',
		    postBody: 'action=' + action,
		    onSuccess: function(transport){
		    
		      var response = transport.responseText || "no response text";
		      response = eval('(' + response + ')'); 
			  
			  // Change the container class.
			  op_admin.ClearClasses('popup_' + response.module + '_container');
			  $('popup_' + response.module + '_container').addClassName( response.container_class );
			  
			  // Change the thumbnail class?
			  op_admin.ClearClasses('popup_' + response.module + '_thumb');
			  $('popup_' + response.module + '_thumb').addClassName( response.thumb_class );
			  
			  // Change the info class
			  op_admin.ClearClasses('popup_' + response.module + '_info');
			  $('popup_' + response.module + '_info').addClassName( response.info_class );
			  
			  // Change the info text
			  $('popup_' + response.module + '_info').update( response.info_content );
			  
			  // Change the button
			  $('popup_' + response.module + '_image').update( response.button_text );
			  
			  op_admin.FireHook( action );
			  
		    },
		    onFailure: function(){ alert('Something went wrong...') }
	 	});
	 	
	 },
	 
	 FireHook: function(hook) {
	
		switch(hook) {
			case 'opcp_FeaturedVideoFeatureActivate': 
				this.FeaturedVideo.RefreshPreview();
			break;
			
			case 'opcp_FeaturedVideoFeatureDeactivate':
				this.FeaturedVideo.RefreshPreview();
			break;
			
			default:
				return true;
			break;
		}
		
	},
	 
	 ToolTip: {
	 
	 	Show: function( module ) {
	 		
			new Ajax.Request('admin-ajax.php',
		 	{
				method:'post',
			    postBody: 'action=opcp_PopulateToolTip&module=' + module,
			    
			    onLoading: function() {
			    	// TODO add something
			    },
			    
			    onSuccess: function(transport){
			      
			      var response = transport.responseText || "no response text";
			      response = eval('(' + response + ')'); 
			      
			       var classArray = $('tt_image').classNames().toArray();
		           for (var index = 0, len = classArray.size(); index < len; ++index) {
		           		$('tt_image').removeClassName(classArray[index]);
		           }
            
            	  $('tt_image').addClassName(module);
			      $('tt_title').update( response.title );
			      $('tt_inner_content').update( response.content );

			      $('ToolTip').setStyle({
			      		left: (op_admin.Mouse.x_pos + 25) + 'px', 
			      		top: (op_admin.Mouse.y_pos - 20) + 'px'
			      });
			      $('ToolTip').appear();
			      
			    },
			    
			    onFailure: function(){ alert('Something went wrong...') }
		 	});

	 	},
	 	
	 	Hide: function() {
	 		Effect.DropOut('ToolTip');
	 	}
	 
	 },
	 
	 ContentControl: {
	 
	 	ToggleLimitMode: function( value ) {

			if (value == 3) {
				$('limit_mode_3').disabled = '';
			}
			else if (value != 3) {
				$('limit_mode_3').disabled = 'disabled';
			}
			
			new Ajax.Request('admin-ajax.php',
	 		{
			    method:'post',
			    postBody: 'action=opcp_ContentControlFeatureSwitch&limit_mode=' + value + '&limit=' + $F('limit_mode_3'),
			    onFailure: function(){ alert('Something went wrong...') }
	 		});
		}
	 },
	 
	 Search: {
	 
	 	DoSearch: function( value, page ) {
	 	
	 		if (page) {
	 			var post_body = 'action=opcp_SearchModules&keywords=' + value + '&page=' + page;
	 		}
	 		else {
	 			var post_body = 'action=opcp_SearchModules&keywords=' + value;
	 		}
	 	
	 		new Ajax.Request('admin-ajax.php',
	 		{
			    method:'post',
			    postBody: post_body,
			    
			    onLoading: function() {
			    	$( 'SearchResults' ).fade({
						duration: 0.5,
						afterFinish: function() {
							$( 'SearchResults' ).update( '<div class="search-loading"></div>' );
							$( 'SearchResults' ).appear({duration: 0.5});
						}
					});
			    },
			    
			    onFailure: function(){ alert('Something went wrong...'); },
			    
			    onSuccess: function( transport ) { 
					var response = transport.responseText || "no response text";
					
					$( 'SearchResults' ).fade({
						duration: 0.5,
						afterFinish: function() {
							$( 'SearchResults' ).update( response );
							$( 'SearchResults' ).appear({duration: 0.5});
						}
					});

				}
	 		});
	 		
	 	},
	 	
	 	DoCategory: function( value, page ) {
	 	
	 		if (page) {
	 			var post_body = 'action=opcp_SearchCategory&category=' + value + '&page=' + page;
	 		}
	 		else {
	 			var post_body = 'action=opcp_SearchCategory&category=' + value;
	 		}
	 		
	 		new Ajax.Request('admin-ajax.php',
	 		{
			    method:'post',
			    postBody: post_body,
			    
			    onLoading: function() {
			    	$( 'SearchResults' ).fade({
						duration: 0.5,
						afterFinish: function() {
							$( 'SearchResults' ).update( '<div class="search-loading"></div>' );
							$( 'SearchResults' ).appear({duration: 0.5});
						}
					});
			    },
			    
			    onFailure: function(){ alert('Something went wrong...'); },
			    
			    onSuccess: function( transport ) { 
					var response = transport.responseText || "no response text";
					
					$( 'SearchResults' ).fade({
						duration: 0.1,
						delay: 1,
						afterFinish: function() {
							$( 'SearchResults' ).update( response );
							$( 'SearchResults' ).appear({duration: 0.5});
						}
					});
					
				}
	 		});
	 		
	 	}
	 
	 },
	 
	 Stats: {
	 	
	 	Save: function() {

	 		if (op_admin.Editing != false) {
				op_admin.Editing.stop();
			}
			
			op_admin.ClearClasses('popup_stats_status');
			$('popup_stats_status').addClassName('status-typing');
			
			op_admin.Editing = new PeriodicalExecuter(function() {
				
				var code = encodeURIComponent($F('popup_stats_code'));
				
				new Ajax.Request('admin-ajax.php',
			 	{
				    method:'post',
				    postBody: 'action=opcp_StatsFeatureSave&code=' + code,
				    onLoading: function() {
				    	
				    	op_admin.Editing.stop();
				    	
				    	op_admin.ClearClasses('popup_stats_status');
			      		$('popup_stats_status').addClassName('status-saving');
			      	
				    },
				    
				    onSuccess: function(transport){
				    
				      op_admin.ClearClasses('popup_stats_status');
				      $('popup_stats_status').addClassName('status-saved');
				      
				    },
				    onFailure: function(){ alert('Something went wrong...') }
			 	});
			 	
		 	}, 1);	
	 		
	 	}
	 	
	 },
	 
	 Advertising: {
	 	
	 	SwitchBlock: function() {
	 		
	 		new Ajax.Request('admin-ajax.php',
	 		{
			    method:'post',
			    postBody: 'action=opcp_AdvertisingSwitchBlock&block_name=' + $F('popup_ablock_select'),
			    onFailure: function(){ alert('Something went wrong...'); },
			    
			    onSuccess: function( transport ) {
			    	var response = transport.responseText || "no response text"; 
					$('popup_adblock_container').update( response );
				}
	 		});
	 		
	 	},
	 	
	 	SwitchEntryMode: function( mode ) {
	 	
	 		new Ajax.Request('admin-ajax.php',
	 		{
			    method:'post',
			    postBody: 'action=opcp_AdvertisingSwitchEntryMode&entry_mode=' + mode + '&block_name=' + $F('popup_ablock_select'),
			    onFailure: function(){ alert('Something went wrong...'); },
			    
			    onSuccess: function( transport ) {
			    	
			    	var response = transport.responseText || "no response text"; 
					$('popup_adblock_entry_area').update( response );
					
					
					if (mode == 'html') {
						op_admin.ClearClasses( 'advertising-adsense-tab' );
						$( 'advertising-adsense-tab' ).addClassName( 'TabInActive' );
						    	
						op_admin.ClearClasses( 'advertising-html-tab' );
						$( 'advertising-html-tab' ).addClassName( 'TabActive' );
					}
					else if (mode == 'adsense') {
						op_admin.ClearClasses( 'advertising-html-tab' );
						$( 'advertising-html-tab' ).addClassName( 'TabInActive' );
						    	
						op_admin.ClearClasses( 'advertising-adsense-tab' );
						$( 'advertising-adsense-tab' ).addClassName( 'TabActive' );
					}
					
				}
	 		});
	 		
	 	},
	 	
	 	SavePubID: function() {
	 		
	 		if (op_admin.Editing != false) {
				op_admin.Editing.stop();
			}
			
			op_admin.ClearClasses('popup_adsense_id_status');
			$('popup_adsense_id_status').addClassName('status-typing');
			
			op_admin.Editing = new PeriodicalExecuter(function() {
			
				new Ajax.Request('admin-ajax.php',
			 	{
				    method:'post',
				    postBody: 'action=opcp_AdvertisingSavePubID&id=' + $F('popup_adsense_id') + '&block_name=' + $F('popup_ablock_select'),
				    onLoading: function() {
				    	
				    	op_admin.Editing.stop();
				    	
				    	op_admin.ClearClasses('popup_adsense_id_status');
			      		$('popup_adsense_id_status').addClassName('status-saving');
			      	
				    },
				    
				    onSuccess: function(transport){
				    
				      op_admin.ClearClasses('popup_adsense_id_status');
				      $('popup_adsense_id_status').addClassName('status-saved');
				      
				    },
				    onFailure: function(){ alert('Something went wrong...') }
			 	});
			 	
		 	}, 1);	
	 		
	 	},
	 		
	 	SaveChannel: function() {
	 	
	 		if (op_admin.Editing != false) {
				op_admin.Editing.stop();
			}
			
			op_admin.ClearClasses('popup_adsense_channel_status');
			$('popup_adsense_channel_status').addClassName('status-typing');
			
			op_admin.Editing = new PeriodicalExecuter(function() {
			
				new Ajax.Request('admin-ajax.php',
			 	{
				    method:'post',
				    postBody: 'action=opcp_AdvertisingSaveChannel&channel=' + $F('popup_adsense_channel') + '&block_name=' + $F('popup_ablock_select'),
				    onLoading: function() {
				    	
				    	op_admin.Editing.stop();
				    	
				    	op_admin.ClearClasses('popup_adsense_channel_status');
			      		$('popup_adsense_channel_status').addClassName('status-saving');
			      	
				    },
				    
				    onSuccess: function(transport){
				    
				      op_admin.ClearClasses('popup_adsense_id_status');
				      $('popup_adsense_channel_status').addClassName('status-saved');
				      
				    },
				    onFailure: function(){ alert('Something went wrong...') }
			 	});
			 	
		 	}, 1);	
	 	
	 	},
	 	
	 	SaveCode: function() {
	 		
	 		if (op_admin.Editing != false) {
				op_admin.Editing.stop();
			}
			
			op_admin.ClearClasses('popup_adsense_code_status');
			$('popup_adsense_code_status').addClassName('status-typing');
			
			op_admin.Editing = new PeriodicalExecuter(function() {
			
				new Ajax.Request('admin-ajax.php',
			 	{
				    method:'post',
				    postBody: 'action=opcp_AdvertisingSaveCode&code=' + encodeURIComponent($F('popup_ad_code')) + '&block_name=' + $F('popup_ablock_select'),
				    onLoading: function() {
				    	
				    	op_admin.Editing.stop();
				    	
				    	op_admin.ClearClasses('popup_adsense_code_status');
			      		$('popup_adsense_code_status').addClassName('status-saving');
			      	
				    },
				    
				    onSuccess: function(transport){
				    
				      op_admin.ClearClasses('popup_adsense_code_status');
				      $('popup_adsense_code_status').addClassName('status-saved');
				      
				    },
				    onFailure: function(){ alert('Something went wrong...') }
			 	});
			 	
		 	}, 1);	
		 	
	 	}
	 	
	 },
	 
	 FeedBurner: {
	 	
	 	SaveID: function() {
	 	
	 		if (op_admin.Editing != false) {
				op_admin.Editing.stop();
			}
			
			op_admin.ClearClasses('popup_feedburner_id_status');
			$('popup_feedburner_id_status').addClassName('status-typing');
			
			op_admin.Editing = new PeriodicalExecuter(function() {
			
				new Ajax.Request('admin-ajax.php',
			 	{
				    method:'post',
				    postBody: 'action=opcp_FeedBurnerSaveID&id=' + $F('popup_feedburner_id'),
				    onLoading: function() {
				    	
				    	op_admin.Editing.stop();
				    	
				    	op_admin.ClearClasses('popup_feedburner_id_status');
			      		$('popup_feedburner_id_status').addClassName('status-saving');
			      	
				    },
				    
				    onSuccess: function(transport){
				    
				      op_admin.ClearClasses('popup_feedburner_id_status');
				      $('popup_feedburner_id_status').addClassName('status-saved');
				      
				    },
				    onFailure: function(){ alert('Something went wrong...') }
			 	});
			 	
		 	}, 1);	
	 	
	 	},
	 	
	 	SaveUrl: function() {
	 		
	 		if (op_admin.Editing != false) {
				op_admin.Editing.stop();
			}
			
			op_admin.ClearClasses('popup_feedburner_url_status');
			$('popup_feedburner_url_status').addClassName('status-typing');
			
			op_admin.Editing = new PeriodicalExecuter(function() {
			
				new Ajax.Request('admin-ajax.php',
			 	{
				    method:'post',
				    postBody: 'action=opcp_FeedBurnerSaveURL&url=' + $F('popup_feedburner_url'),
				    onLoading: function() {
				    	
				    	op_admin.Editing.stop();
				    	
				    	op_admin.ClearClasses('popup_feedburner_url_status');
			      		$('popup_feedburner_url_status').addClassName('status-saving');
			      	
				    },
				    
				    onSuccess: function(transport){
				    
				      op_admin.ClearClasses('popup_feedburner_url_status');
				      $('popup_feedburner_url_status').addClassName('status-saved');
				      
				    },
				    onFailure: function(){ alert('Something went wrong...') }
			 	});
			 	
		 	}, 1);	
		 	
	 	}
	 	
	 },
	 
	 Highlights: {
	 	
	 	SwitchHighlight: function() {
	 		
	 		new Ajax.Request('admin-ajax.php',
	 		{
			    method:'post',
			    postBody: 'action=opcp_HighlightsSwitchHighlight&highlight_name=' + $F('popup_highlight_select'),
			    onFailure: function(){ alert('Something went wrong...'); },
			    
			    onSuccess: function( transport ) {
			    	var response = transport.responseText || "no response text"; 
					$('popup_highlight_container').update( response );
				}
	 		});
	 		
	 	},
	 	
	 	SearchPosts: function() {
	 	
	 		if (op_admin.Editing != false) {
				op_admin.Editing.stop();
			}
			
			op_admin.ClearClasses('popup_highlights_post_status');
			$('popup_highlights_post_status').addClassName('status-typing');
			
			op_admin.Editing = new PeriodicalExecuter(function() {
			
				new Ajax.Request('admin-ajax.php',
			 	{
				    method:'post',
				    postBody: 'action=opcp_HighlightsSearchPosts&search_term=' + $F('popup_highlight_post_entry'),
				    onLoading: function() {
				    	
				    	op_admin.Editing.stop();
				    	$('popup_highlight_search_results').update('Searching Posts...');
				    	
				    	op_admin.ClearClasses('popup_highlights_post_status');
			      		$('popup_highlights_post_status').addClassName('status-searching');
			      	
				    },
				    onSuccess: function(transport){
				    
				      var response = transport.responseText || "no response text";
				      $('popup_highlight_search_results').update(response);
				      
				      op_admin.ClearClasses('popup_highlights_post_status');
				      
				    },
				    onFailure: function(){ alert('Something went wrong...') }
			 	});
			 	
		 	}, 1);	
		 	
	 	},
	 	
	 	SetPost: function() {
	 		
	 		new Ajax.Request('admin-ajax.php',
		 	{
			    method:'post',
			    postBody: 'action=opcp_HighlightsSetPost&highlight_name=' + $F('popup_highlight_select') + '&id=' + $$('input:checked[type="radio"][name="popup_highlights_featured_post_radio"]').pluck('value'),
			    onLoading: function() {
			    	$('popup_highlights_post_preview').update('<div id="gen-preview"></div>');
			    	
			    	op_admin.ClearClasses('popup_highlights_post_status');
			      	$('popup_highlights_post_status').addClassName('status-saving');
			      	
			    },
			    onSuccess: function(transport){
			      var response = transport.responseText || "no response text";
			      $('popup_highlights_post_preview').update(response);
			      $('popup_highlight_search_results').update();
			      
			      op_admin.ClearClasses('popup_highlights_post_status');
			      $('popup_highlights_post_status').addClassName('status-saved');
			      
			    },
			    onFailure: function(){ alert('Something went wrong...') }
		 	});
	 		
	 	},
	 	
	 	SetCategory: function() {
	 		
	 		new Ajax.Request('admin-ajax.php',
		 	{
			    method:'post',
			    postBody: 'action=opcp_HighlightsSetCategory&highlight_name=' + $F('popup_highlight_select') + '&id=' + $F('popup_highlight_category_entry'),
			    onLoading: function() {
			    
			    	$('popup_highlights_category_preview').update('<div id="gen-preview"></div>');
			    	
			    	op_admin.ClearClasses('popup_highlights_category_status');
			        $('popup_highlights_category_status').addClassName('status-saving');
			        
			    },
			    onSuccess: function(transport){
			      var response = transport.responseText || "no response text";
			      $('popup_highlights_category_preview').update(response);
			      
			      op_admin.ClearClasses('popup_highlights_category_status');
			      $('popup_highlights_category_status').addClassName('status-saved');
			      
			    },
			    onFailure: function(){ alert('Something went wrong...') }
		 	});
	 		
	 	}
	 	
	 },
	 
	 Thumbnails: {
	 	
	 	SwitchMode: function( mode) {
			
			if(mode) {
	 			
	 			if( mode == 'general' ) {
	 				
	 				new Ajax.Request('admin-ajax.php',
			 		{
					    method:'post',
					    postBody: 'action=opcp_ThumbnailsSwitchMode&mode=' +  mode,
					    onFailure: function(){ alert('Something went wrong...'); },
					    
					    onSuccess: function( transport ) {
					    	
					    	var response = transport.responseText;
					    	$('thumbnail-container').update( response );
					    	
					    	op_admin.ClearClasses( 'thumbnails-tool-tab' );
					    	$( 'thumbnails-tool-tab' ).addClassName( 'TabInActive' );
					    	
					    	op_admin.ClearClasses( 'thumbnails-general-tab' );
					    	$( 'thumbnails-general-tab' ).addClassName( 'TabActive' );
					    	
						}
						
			 		});
	 				
	 			}
	 			else if( mode == 'tool' ) {
	 				
	 				new Ajax.Request('admin-ajax.php',
			 		{
					    method:'post',
					    postBody: 'action=opcp_ThumbnailsSwitchMode&mode=' +  mode,
					    onFailure: function(){ alert('Something went wrong...'); },
					    
					    onSuccess: function( transport ) {
					    
					    	var response = transport.responseText;
					    	$('thumbnail-container').update( response );
					    	
					    	op_admin.ClearClasses( 'thumbnails-general-tab' );
					    	$( 'thumbnails-general-tab' ).addClassName( 'TabInActive' );
					    	
					    	op_admin.ClearClasses( 'thumbnails-tool-tab' );
					    	$( 'thumbnails-tool-tab' ).addClassName( 'TabActive' );
					    	
						}
						
			 		});
			 	}
			}
	 	},
	 	
	 	
	 	SwitchThumbToGen: function( value ) {
	 		$('thumb_to_generate').value = value;
	 	},
	 	
	 	
	 	SearchPosts: function() {
	 		
	 		if (op_admin.Editing != false) {
				op_admin.Editing.stop();
			}
			
			op_admin.ClearClasses('popup_thumbnails_post_status');
			$('popup_thumbnails_post_status').addClassName('status-typing');
			
			op_admin.Editing = new PeriodicalExecuter(function() {
			
				new Ajax.Request('admin-ajax.php',
			 	{
				    method:'post',
				    postBody: 'action=opcp_ThumbnailsSearchPosts&search_term=' + $F('popup_thumbnails_post_entry'),
				    onLoading: function() {
				    	
				    	op_admin.Editing.stop();
				    	$('popup_thumbnails_post_status').update('Searching Posts...');
				    	
				    	op_admin.ClearClasses('popup_highlights_post_status');
			      		$('popup_thumbnails_post_status').addClassName('status-searching');
			      	
				    },
				    onSuccess: function(transport){
				    
				      var response = transport.responseText || "no response text";
				      $('popup_thumbnails_search_results').update(response);
				      
				      op_admin.ClearClasses('popup_thumbnails_post_status');
				      
				    },
				    onFailure: function(){ alert('Something went wrong...') }
			 	});
			 	
		 	}, 1);	
	 	},
	 	
	 	SetPost: function( post_id ) {
	 		
	 		new Ajax.Request('admin-ajax.php',
	 		{
			    method:'post',
			    postBody: 'action=opcp_ThumbnailsSetPost&post_id=' +  post_id,
			    onFailure: function(){ alert('Something went wrong...'); },
			    
			    onSuccess: function( transport ) {
			    	var response = transport.responseText;
			    	$('popup_thumbnails_manager_container').update( response );
				}
				
	 		});
	 		
	 	},
	 	
	 	ScanPost: function( post_id ) {
	 		
	 		new Ajax.Request('admin-ajax.php',
	 		{
			    method:'post',
			    postBody: 'action=opcp_ThumbnailsScanPost&post_id=' +  post_id,
			    onFailure: function(){ alert('Something went wrong...'); },
			    
			    onSuccess: function( transport ) {
			    	var response = transport.responseText;
			    	$('thumbnails-scan-results').update( response );
				}
				
	 		});
	 		
	 	},
	 	
	 	ImagePreview: function( src ) {
	 		$('thumbnails-scan-preview').update( '<div style="padding:1px;border:1px solid #ccc;float:left;margin:10px 10px 0 0;"><img style="width:50px;" src="' + src + '" /></div><div style="clear:both;"></div>' );
	 	},
	 	
	 	RipImage: function( src, post_id ) {

	 		new Ajax.Request('admin-ajax.php',
	 		{
			    method:'post',
			    postBody: 'action=opcp_ThumbnailsRipImage&url=' + src + '&post_id=' + post_id + '&thumb_to_generate=' + $F( 'thumb_to_generate' ) + '&overwrite_existing=' + $('overwrite').checked,
			    onFailure: function(){ alert('Something went wrong...'); },
			    
			    onSuccess: function( transport ) {
			    	var response = transport.responseText;
			    	$('popup_thumbnails_manager_container').update( response );
				}
				
	 		});
	 		
	 	},
	 	
	 	Done: function() {
	 		$('popup_thumbnails_manager_container').update( '<div>All done! <div class="ThumbSubmit"><a href="javascript:;" onclick="op_admin.Thumbnails.SwitchMode(\'tool\')">Start Over</a></div></div>' );
	 	},
	 	
	 	HadError: function( error ) {
	 		$('popup_thumbnails_manager_container').update( '<div>' + error + '</div>' );
	 	}

	 },
	 
	 FeaturedVideo: {
	 
	 	SaveYouTube: function() {
	 		
	 		if (op_admin.Editing != false) {
				op_admin.Editing.stop();
			}
			
			op_admin.ClearClasses('popup_featured_video_status');
			$('popup_featured_video_status').addClassName('status-typing');
			
			op_admin.Editing = new PeriodicalExecuter(function() {
			
				new Ajax.Request('admin-ajax.php',
			 	{
				    method:'post',
				    postBody: 'action=opcp_FeaturedVideoSaveYouTube&url=' + $F('YouTube_URL'),
				    onLoading: function() {
				    	
				    	op_admin.Editing.stop();
				    	op_admin.ClearClasses('popup_featured_video_status');
						$('popup_featured_video_status').addClassName('status-saving');
			      	
				    },
				    onSuccess: function(transport){
				    
				      var response = transport.responseText || "no response text";
				      
				      op_admin.ClearClasses('popup_featured_video_status');
					  $('popup_featured_video_status').addClassName('status-saved');
				      
				      op_admin.FeaturedVideo.RefreshPreview();
				      
				    },
				    onFailure: function(){ alert('Something went wrong...') }
			 	});
			 	
		 	}, 1);	
	 	},
	 	
	 	SaveEmbed: function() {
	 	
	 		if (op_admin.Editing != false) {
				op_admin.Editing.stop();
			}
			
			op_admin.ClearClasses('popup_featured_video_status');
			$('popup_featured_video_status').addClassName('status-typing');
			
			op_admin.Editing = new PeriodicalExecuter(function() {
			
				new Ajax.Request('admin-ajax.php',
			 	{
				    method:'post',
				    postBody: 'action=opcp_FeaturedVideoSaveEmbed&code=' + encodeURIComponent($F('ot_embed_video')),
				    onLoading: function() {
				    	
				    	op_admin.Editing.stop();
				    	op_admin.ClearClasses('popup_featured_video_status');
						$('popup_featured_video_status').addClassName('status-saving');
			      	
				    },
				    onSuccess: function(transport){
				    
				      var response = transport.responseText || "no response text";
				      
				      op_admin.ClearClasses('popup_featured_video_status');
					  $('popup_featured_video_status').addClassName('status-saved');
				      
				      op_admin.FeaturedVideo.RefreshPreview();
				      
				    },
				    onFailure: function(){ alert('Something went wrong...') }
			 	});
			 	
		 	}, 1);	
		 	
	 	},
	 
	 	SwitchEntryMode: function( mode ) {
	 	
	 		new Ajax.Request('admin-ajax.php',
	 		{
			    method:'post',
			    postBody: 'action=opcp_FeaturedVideoSwitchEntryMode&entry_mode=' + mode,
			    onFailure: function(){ alert('Something went wrong...'); },
			    
			    onSuccess: function( transport ) {
			    	var response = transport.responseText || "no response text"; 
					$('popup_video_container').update( response );
					
					op_admin.FeaturedVideo.RefreshPreview();
					
					
					if (mode == 'youtube') {
						op_admin.ClearClasses( 'featured_video_code_tab' );
				    	$( 'featured_video_code_tab' ).addClassName( 'TabInActive' );
				    	
				    	op_admin.ClearClasses( 'featured_video_youtube_tab' );
				    	$( 'featured_video_youtube_tab' ).addClassName( 'TabActive' );
				    }
				    else if (mode == 'embed') {
				    	op_admin.ClearClasses( 'featured_video_youtube_tab' );
				    	$( 'featured_video_youtube_tab' ).addClassName( 'TabInActive' );
				    	
				    	op_admin.ClearClasses( 'featured_video_code_tab' );
				    	$( 'featured_video_code_tab' ).addClassName( 'TabActive' );
				    }
					
				}
	 		});
	 		
	 	},
	 	
	 	RefreshPreview: function() {

	 		new Ajax.Request('admin-ajax.php',
	 		{
			    method:'post',
			    postBody: 'action=opcp_FeaturedVideoGetPreview',
			    onFailure: function(){ alert('Something went wrong...'); },
			    
			    onSuccess: function( transport ) {
			    	var response = transport.responseText || "no response text"; 
					$('popup_video_preview').update( response );
				}
	 		});
	 		
	 	}
	 
	 },
	 
	 Skins: {
	 
	 	SwitchSkin: function() {
	 		
	 		new Ajax.Request('admin-ajax.php',
	 		{
			    method:'post',
			    postBody: 'action=opcp_SkinModuleSwitchSkin&skin_name=' + $F('popup_skin_select'),
			    onFailure: function(){ alert('Something went wrong...'); },
			    
			    onSuccess: function( transport ) {
			    	var response = transport.responseText || "no response text"; 
					$('popup_skin_container').update( response );
				}
	 		});
	 		
	 	},
	 	
	 	SwitchDefault: function() {
	 		
	 		new Ajax.Request('admin-ajax.php',
	 		{
			    method:'post',
			    postBody: 'action=opcp_SkinModuleSwitchDefault&skin_name=' + $F('popup_skin_default_select') + '&viewing_skin_name=' + $F('popup_skin_select'),
			    onFailure: function(){ alert('Something went wrong...'); },
			    
			    onSuccess: function( transport ) {
			    	var response = transport.responseText || "no response text"; 
					response = eval('(' + response + ')');
					 
					if($F('popup_skin_default_select') == $F('popup_skin_select')) {
						
						// Change the container class.
						  op_admin.ClearClasses('popup_' + response.module + '_container');
						  $('popup_' + response.module + '_container').addClassName( response.container_class );
						  
						  // Change the thumbnail class?
						  op_admin.ClearClasses('popup_' + response.module + '_thumb');
						  $('popup_' + response.module + '_thumb').addClassName( response.thumb_class );
						  
						  // Change the info class
						  op_admin.ClearClasses('popup_' + response.module + '_info');
						  $('popup_' + response.module + '_info').addClassName( response.info_class );
						  
						  // Change the info text
						  $('popup_' + response.module + '_info').update( response.info_content );
						  
						  // Change the button
						  $('popup_' + response.module + '_image').update();
						  
						  // Change the help text
						  $('popup_' + response.module + '_help').update( response.help_text );
						  
					}
					else {
					
						 $('popup_' + response.viewing_module + '_help').update();
						 $('popup_' + response.viewing_module + '_image').update( response.button_text );
						
						 
					}
				}
	 		});
	 		
	 	},
	 	
	 	Update: function( response ) {
	 	
	 		if (response.status == false) {
	 			alert(response.error);
	 		}
	 		else {
	 			$(response.preview_id).update('<img src="' + response.new_image + '" class="popup_upload_preview" />');
	 		}
	 		
	 	},
	 	
	 	ResetImage: function( feature_key, image_key ) {
	 		
	 		new Ajax.Request('admin-ajax.php',
	 		{
			    method:'post',
			    postBody: 'action=opcp_SkinModuleResetImage&skin_name=' + $F('popup_skin_select') + '&feature_key=' + feature_key + '&image_key=' + image_key,
			    onFailure: function(){ alert('Something went wrong...'); },
			    
			    onSuccess: function( transport ) {
			    	var response = transport.responseText || "no response text";
			    	response = eval('(' + response + ')');
			    	
					$(response.preview_id).update( '<img src="' + response.new_image + '" class="popup_upload_preview" />' );
					
				}
	 		});
	 		
	 	}
	 	
	 },
	 
	 HomeLayouts: {
	 	
	 	SwitchDefault: function() {

	 		new Ajax.Request('admin-ajax.php',
	 		{
			    method:'post',
			    postBody: 'action=opcp_HomePageLayoutModuleSwitchLayout&layout=' + $F('popup_layout_default_select'),
			    onFailure: function(){ alert('Something went wrong...'); }
	 		});
	 		
	 	}
	 	
	 },
	 
	 MenuLinks: {
	 
	 	SwitchLink: function( state, menu_name, link_id ) {
	 		
	 		new Ajax.Request('admin-ajax.php',
	 		{
			    method:'post',
			    postBody: 'action=opcp_MenuLinksModuleSwitchLink&id=' + link_id + '&menu=' + menu_name + '&state=' + state,
			    onFailure: function(){ alert('Something went wrong...'); }
	 		});
	 		
	 	}
	 
	 },
	 
	 License: {
	 	
	 	Validate: function() {
	 	
	 		new Ajax.Request('admin-ajax.php',
	 		{
			    method:'post',
			    postBody: 'action=opcp_ValidateLicense&license=' + $F('one_panel_license_key'),
			    onFailure: function(){ alert('Something went wrong...'); },
			    
			    onSuccess: function( transport ) {
			    	
			    	var response = transport.responseText || "no response text";
			    	response = eval('(' + response + ')');
			    	
					$('one_panel_license_entry').update( response.content );
					$( 'one_panel_license_icon' ).update( response.icon );
					
				}
	 		});
	 		
	 	}
	 	
	 },
	 
	 Localization: {
	 	
	 	Search: function() {
	 	
	 		op_admin.ClearClasses('localization-search-status');
			$('localization-search-status').addClassName('status-typing');
	 	
	 		if (op_admin.Editing != false) {
				op_admin.Editing.stop();
			}
	 		
	 		op_admin.Editing = new PeriodicalExecuter(function() {
		 		new Ajax.Request('admin-ajax.php',
		 		{
				    method:'post',
				    postBody: 'action=opcp_LocalizationSearch&search_term=' + $F( 'localization-search-input' ),
				    
				    onLoading: function() {
				    	
				    	op_admin.Editing.stop();
				    	
				    	op_admin.ClearClasses('localization-search-status');
				    	$('localization-search-status').addClassName('status-searching');
				    	
				    },
				    
				    onFailure: function(){ alert('Something went wrong...'); },
				    
				    onSuccess: function( transport ) {
				    
				    	op_admin.ClearClasses('localization-search-status');
				    	
				    	var response = transport.responseText;
						$('localization-search-results').update( response );
						
					}
		 		});
		 	}, 2);
	 		
	 	},
	 	
	 	Edit: function( key ) {
	 	 	
	 	 	new Ajax.Request('admin-ajax.php',
	 		{
			    method:'post',
			    postBody: 'action=opcp_LocalizationEdit&key=' +  key,
			    
			    onLoading: function() {
			    	op_admin.ClearClasses('localization-edit-status');
				    $('localization-edit-status').addClassName('status-searching');
			    },
			    
			    onFailure: function(){ alert('Something went wrong...'); },
			    
			    onSuccess: function( transport ) {
			    
			    	var response = transport.responseText;
			    	op_admin.ClearClasses('localization-edit-status');
			    	$('localization-edit-container').update( response );
			    	
				}
				
	 		});
	 		
	 	},
	 	
	 	Save: function( key ) {
	 		
	 		if (op_admin.Editing != false) {
				op_admin.Editing.stop();
			}
			
			op_admin.ClearClasses('localization-edit-status');
			$('localization-edit-status').addClassName('status-typing');
			
	 		op_admin.Editing = new PeriodicalExecuter(function() {
		 		new Ajax.Request('admin-ajax.php',
		 		{
				    method:'post',
				    postBody: 'action=opcp_LocalizationSave&key=' + key + '&text=' + $F('localization_edit_input'),
				    
				    onLoading: function() {
				    
				    	op_admin.Editing.stop();
				    	
				    	op_admin.ClearClasses('localization-edit-status');
				    	$('localization-edit-status').addClassName('status-saving');
				    	
				    },
				    
				    onFailure: function(){ alert('Something went wrong...'); },
				    
				    onSuccess: function( transport ) {
				    
				    	op_admin.ClearClasses('localization-edit-status');
				    	$('localization-edit-status').addClassName('status-saved');
						
					}
		 		});
		 	}, 2);
	 		
	 	}
	 	
	 },
	 
	 Mouse: {
	 		
 		x_pos: null,
 		y_pos: null
	 		
	 },
	 
	 NewsTicker: {
	 
	 	Update: function() {
		 
		 	if (op_admin.run_news == false) return true;
	 		
	 		new Ajax.Request('admin-ajax.php',
	 		{
			    method:'post',
			    postBody: 'action=opcp_GetNews',
			    onFailure: function(){ alert('Something went wrong...'); },
			    
			    onSuccess: function( transport ) {
			    
			    	var response = transport.responseText;
			    	
			    	$('news-ticker-content').fade({
			    		afterFinish: function () {
			    			$('news-ticker-content').update( response );
			    			$('news-ticker-content').appear({delay:1});
			    		}
			    	});
			    	
				}
				
	 		});
	 		
	 	}
	 
	 },
	 
	 ExportData: {
	 	
	 	DoExport: function() {
	 		
	 		new Ajax.Request('admin-ajax.php',
	 		{
			    method:'post',
			    postBody: 'action=opcp_DoExport',
			    onFailure: function(){ alert('Something went wrong...'); },
			    
			    onSuccess: function( transport ) {
			    
			    	var response = transport.responseText;
			    	response = eval('(' + response + ')');
			    	
			    	if(response.status == false) {
			    		$('backup-area').update( response.error );
			    	}
			    	else {
			    		$('backup-area').update( response.content );
			    	}
			    	
				}
				
	 		});
	 		
	 	}
	 	
	 },
	 
	 ImportData: {
	 
	 	SwitchMode: function( mode ) {
	 		
	 		if(mode) {
	 			if( mode == 'browse' ) {
	 				
	 				new Ajax.Request('admin-ajax.php',
			 		{
					    method:'post',
					    postBody: 'action=opcp_ImportSwitchMode&mode=' +  mode,
					    onFailure: function(){ alert('Something went wrong...'); },
					    
					    onSuccess: function( transport ) {
					    
					    	var response = transport.responseText;
					    	$('import-container').update( response );
					    	
					    	op_admin.ClearClasses( 'import_upload_tab' );
				    		$( 'import_upload_tab' ).addClassName( 'TabInActive' );
				    	
				    		op_admin.ClearClasses( 'import_browse_tab' );
				    		$( 'import_browse_tab' ).addClassName( 'TabActive' );
						}
						
			 		});
	 				
	 			}
	 			else if( mode == 'upload' ) {
	 				
	 				new Ajax.Request('admin-ajax.php',
			 		{
					    method:'post',
					    postBody: 'action=opcp_ImportSwitchMode&mode=' +  mode,
					    onFailure: function(){ alert('Something went wrong...'); },
					    
					    onSuccess: function( transport ) {
					    
					    	var response = transport.responseText;
					    	$('import-container').update( response );
					    	
					    	op_admin.ClearClasses( 'import_browse_tab' );
				    		$( 'import_browse_tab' ).addClassName( 'TabInActive' );
				    	
				    		op_admin.ClearClasses( 'import_upload_tab' );
				    		$( 'import_upload_tab' ).addClassName( 'TabActive' );
				    		
						}
						
			 		});
	 				
	 			}
	 		}
	 		
	 	},
	 
	 	UpdateFileInfo: function( for_theme, version, date, link_text, file_name ) {
	 	
	 		$('import-file-info').update( 
					"<ul>" +
						"<li><strong>Theme</strong>: " + for_theme + "</li>" +
						"<li><strong>One Panel Version</strong>: " + version + "</li>" +
						"<li><strong>Export Date</strong>: " + date + "</li>" +
					"</ul>" +
					'<a href="javascript:;" onclick="op_admin.ImportData.DoImport(\'' + file_name + '\')">' + link_text + '</a>'
			);
	 	
	 	},
	 	
	 	DoImport: function( file_name ) {
			
			var is_sure = confirm( 'Discard current One Panel data and import from this file?' );
			
			if (is_sure) {

				new Ajax.Request('admin-ajax.php',
			 		{
					    method:'post',
					    postBody: 'action=opcp_DoImport&file=' +  file_name,
					    
					    onLoading: function() {
					    	$('import-container').update( '<div class="importingdata">Importing Data..</div>' );
					    },
					    
					    onFailure: function(){ alert('Something went wrong...'); },
					    
					    onSuccess: function( transport ) {
					    
					    	// TODO - CHECK RESPONSE FOR ERROR
					    	alert('Your data was imported successfully.');
					    	window.location = window.location;
						}
						
			 		});
				
			}	 		

	 	},
	 	
	 	HandleUpload: function( response ) {
	 	
	 		if (response.status == true) {
	 			alert('Your data was imported successfully.');
	 			window.location = window.location;
	 		}
	 		else if (response.status == false) {
				$('import_upload_status').update( response.error );
	 		}
	 		else {
	 			alert( 'One Panel Error: Unexpected response from Import Uploader. Please report this at http://www.one-theme.com/beta' );
	 		}
	 	
	 	}
	 
	 },
	 
	 // MISC FUNCTIONS
	 ClearClasses: function( id ) {
	 
	 	var classArray = $(id).classNames().toArray();
	 	
        for (var index = 0, len = classArray.size(); index < len; ++index) {
        	$(id).removeClassName(classArray[index]);
        }
        
	 },
	 
	 // TODO can remove a lot of stuff from the incompatible page
	 Backup: function( popup ) {
	 	
	 	new Ajax.Request('admin-ajax.php',
	 		{
			    method:'post',
			    postBody: 'action=opcp_BackupData',
			    
			    onLoading: function() {
			    	$('backup-area').update( 'Creating backup file..' );
			    },
			    
			    onFailure: function(){ alert('Something went wrong...'); },
			    
			    onSuccess: function( transport ) {
			    	
			    	var response = transport.responseText || "no response text";
			    	response = eval('(' + response + ')');
					
					if (popup) {
						$('backup-area').update( response.file_link );
					}
					else {
						$('backup-area').update( response.file_link + response.continue_link );
					}
						
				}
	 		});
	 	
	 },
	 
	 FlushData: function( theme_name, keep_license ) {
	 	
	 	if(theme_name) {
	 		var message = 'Are you sure you want to discard your One Panel Data for ' + theme_name + '?';
	 	}
	 	else {
	 		var message = 'Are you sure you want to discard your One Panel Data?';
	 	}
	 	
	 	var answer = confirm( message );
	 	
	 	if(answer) {
	 	
		 	if (keep_license == true) {
		 		var post_body = 'action=opcp_FlushData&keep_license=true';
		 	}
		 	else {
		 		var post_body = 'action=opcp_FlushData';
		 	}
	 	
		 	new Ajax.Request('admin-ajax.php',
		 		{
				    method:'post',
				    postBody: post_body,
				    onFailure: function(){ alert('Something went wrong...'); },
				    
				    onSuccess: function( transport ) {
				    	window.location = window.location;
					}
		 		});
		 }
		 
	 },
	 
	 ForceFlushData: function( keep_license ) {
	 	
	 	if (keep_license == true) {
	 		var post_body = 'action=opcp_FlushData&keep_license=true';
	 	}
	 	else {
	 		var post_body = 'action=opcp_FlushData';
	 	}
	 	
	 	new Ajax.Request('admin-ajax.php',
 		{
		    method:'post',
		    postBody: post_body,
		    onFailure: function(){ alert('Something went wrong...'); },
		    
		    onSuccess: function( transport ) {
		    	window.location = window.location;
			}
 		});
	 
	 },
	 
	 HidePopup: function() {
	 	$('action-frame').fade({
        	afterFinish: function() { $('overlay').hide() }
        });
	 }
	 
}


// Events
document.observe("dom:loaded", function() {

	Event.observe( document, 'mousemove', 
		function(event){ 
			op_admin.Mouse.x_pos = Event.pointerX(event); 
			op_admin.Mouse.y_pos = Event.pointerY(event); 
		}
	);
	
	// Set the news ticker going
	new PeriodicalExecuter( op_admin.NewsTicker.Update, 15 );
	
});

document.observe('keypress', function(event){
    if(event.keyCode == Event.KEY_ESC) {
         $('action-frame').fade({
         	afterFinish: function() { $('overlay').hide() }
        });
    }
});

function MM_popupMsg(msg) {
	alert(msg);
}