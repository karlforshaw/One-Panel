<?php

	class MenuLinksFeature extends OnePanelFeature {
		
		protected $title = 'Menu Links';
		protected $help_text = 'Use the Menu Builder to better organize your website navigation. Excluding the links with less importantance.';
		
		protected $excluded_pages = array();
		protected $excluded_categories = array();
		protected $excluded_footer_pages = array();
		
		
		public function GetExcludedPages() {
			return $this->excluded_pages;
		}
		
		public function GetExcludedCategories() {
			return $this->excluded_categories;
		}
		
		public function GetExludedFooterPages() {
			return $this->excluded_footer_pages;
		}
		
		public function ToggleLink( $menu, $id, $state ) {
			
			switch ( $menu ) {
				case 'pages':
				
					if ((! in_array( $id, $this->excluded_pages )) && ($state == 'true')) {
						$this->excluded_pages[] = $id;
					}
					elseif ((in_array( $id, $this->excluded_pages )) && ($state == 'false')) {
						
						$key = array_search( $id, $this->excluded_pages );
						if (is_int($key)) {
							unset( $this->excluded_pages[$key] );
						}
						
					}
					else {
						die( print_r($this->excluded_pages, true) );
					}
					
				break;
				
				case 'categories':
				
					if ((! in_array( $id, $this->excluded_categories )) && ($state == 'true')) {
						$this->excluded_categories[] = $id;
					}
					elseif ((in_array( $id, $this->excluded_categories )) && ($state == 'false')) {
						
						$key = array_search( $id, $this->excluded_categories );
						if (is_int($key)) {
							unset( $this->excluded_categories[$key] );
						}
						
					}
					else {
						die( print_r($this->excluded_categories, true) );
					}
					
				break;
				
				case 'footer':
				
					if ((! in_array( $id, $this->excluded_footer_pages )) && ($state == 'true')) {
						$this->excluded_footer_pages[] = $id;
					}
					elseif ((in_array( $id, $this->excluded_footer_pages )) && ($state == 'false')) {
						
						$key = array_search( $id, $this->excluded_footer_pages );
						if (is_int($key)) {
							unset( $this->excluded_footer_pages[$key] );
						}
						
					}
					else {
						die( print_r($this->excluded_footer_pages, true) );
					}
					
				break;
			}
			
		}
		
		public function Render() {
			
			$page_data = get_pages();
			$category_data = get_categories();
			
			// Start with the pages menu			
			$content  = '<div class="module_title">';
			$content .= 		'<div class="popup_generic_title">Pages Menu</div>';
			$content .= 		'<div class="popup_generic_desc">If you have too many pages in your top menu consider excluding the least important ones here.</div>';
			$content .= 		'<div class="thumb_container">';
			$content .= 			'<div class="PageMenu_thumb"></div>';
			$content .= 		'</div>';
			$content .= '</div>';
			
			foreach ($page_data as $page) {

				if ($page->post_parent == 0) {
					
				    $content .= '<div class="input_option" style="margin-left:5px;">';
					$content .= '<div class="radio_option">';
					$content .= '<input onclick="op_admin.MenuLinks.SwitchLink( this.checked, \'pages\', \'' . $page->ID . '\' )" type="checkbox" name="ot_top_menu_links[]" value="' . $page->ID . '"';
				   	$content .= in_array( $page->ID, $this->excluded_pages ) ? 'checked="checked"' :'';
				    $content .= ' />' . "\n";
					$content .= '</div>';
					$content .= '<div class="radio_content" style="padding-left:10px;font-weight:bold;">';
				    $content .= '' . $page->post_title . '' . "\n";
				    $content .= '</div>' . "\n";
					$content .= '</div>' . "\n";
					$content .= '<div style="clear:both;height:5px;"></div>' . "\n";
				    
		   		}
			}
			
			// Now categories
			$content .= '<div class="module_title">';
			$content .= 		'<div class="popup_generic_title">Category Menu</div>';
			$content .= 		'<div class="popup_generic_desc">If you have too many categories in your top menu consider excluding the least important ones here.</div>';
			$content .= 		'<div class="thumb_container">';
			$content .= 			'<div class="CategoryMenu_thumb"></div>';
			$content .= 		'</div>';
			$content .='</div>';
			 
			foreach ($category_data as $category) {
			    if ($category->parent == 0) {
			    	
				    $content .= '<div class="input_option" style="margin-left:5px;">';
					$content .= '<div class="radio_option">';
					$content .= '<input onclick="op_admin.MenuLinks.SwitchLink( this.checked, \'categories\', \'' . $category->term_id . '\' )" type="checkbox" name="ot_sub_menu_links[]" value="' . $category->term_id . '"';
				    $content .= in_array( $category->term_id, $this->excluded_categories ) ? 'checked="checked"' :'';
				    $content .= ' />' . "\n";
					$content .= '</div>';
					$content .= '<div class="radio_content" style="padding-left:10px;font-weight:bold;">';
				    $content .= '' . $category->name . '' . "\n";
				    $content .= '</div>' . "\n";
				    $content .= '</div>' . "\n";
					$content .= '<div style="clear:both;height:5px;"></div>' . "\n";
				    
			    }
    		}
    		
    		// And then the footer
    		$content .= '<div class="module_title">';
			$content .= 		'<div class="popup_generic_title">Footer Menu</div>';
			$content .= 		'<div class="popup_generic_desc">Want a more professional looking footer area? Consider excluding the less important pages.</div>';
			$content .= 		'<div class="thumb_container">';
			$content .= 			'<div class="FooterMenu_thumb"></div>';
			$content .= 		'</div>';
			$content .='</div>';
			
			foreach ($page_data as $page) {

				if ($page->post_parent == 0) {
				    $content .= '<div class="input_option" style="margin-left:5px;">';
					$content .= '<div class="radio_option">';
					$content .= '<input onclick="op_admin.MenuLinks.SwitchLink( this.checked, \'footer\', \'' . $page->ID . '\' )" type="checkbox" name="ot_footer_links[]" value="' . $page->ID . '"';
				    $content .= in_array( $page->ID, $this->excluded_footer_pages ) ? 'checked="checked"' :'';
				    $content .= ' />' . "\n";
					$content .= '</div>';
					$content .= '<div class="radio_content" style="padding-left:10px;font-weight:bold;">';
				    $content .= '' . $page->post_title . '' . "\n";
				    $content .= '</div>' . "\n";
					$content .= '</div>' . "\n";
					$content .= '<div style="clear:both;height:5px;"></div>' . "\n";
			    }
		    
		    }
    
			return $content;
		}
		
	}

	class MenuLinksModule extends OnePanelModule {
		
		protected $title = 'Menu Builder';
		protected $description = 'Use the Menu Links module to better organize your website navigation. Quickly and easily stop certain links from displaying under your blog menus.';
		protected $short_description = 'Use the Menu Builder to better organize your website navigation.';
		protected $help_text = 'Use the Menu Builder to better organize your website navigation. Excluding the links with less importantance.';
		protected $keywords = array( 'menu links', 'menu', 'links', 'builder' );
		protected $categories = array( 'Menu Links' );
		
		public function Render() {
			$this->GenericRender();
		}
		
		public function RegisterFeatures() {
			$this->RegisterFeature( 'MenuLinksFeature' );
		}
		
		public function BuildChunks() {
			return true;
		}
		
		// AJAX STUFF
		public function SwitchLink() {
			
			$feature_object = &$this->features['MenuLinksFeature'];
			
			if (is_object( $feature_object )) {
				
				$feature_object->ToggleLink( $_POST['menu'], $_POST['id'], $_POST['state'] );
				OnePanel::PackData();
				die();
				
			}
			else {
				die( 'No object by the name: MenuLinksFeature' );
			}
			
		}
		
	}