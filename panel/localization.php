<?php

	class LocalizationTool extends OnePanelFeature {
		
		protected $title = 'Tool';
		protected $help_text = 'Use the Localization module to edit the language of the theme you currently have enabled.';
		
		public function Render() {
			
			$default_language = OnePanelLanguage::GetDefaultLanguage(); // TODO this needs to change in 2.1
			if (! $default_language) trigger_error( 'One Panel Error: No default language.', E_WARNING );
			
			// Search
			$response  = '<div class="Localization">' . "\n";
			
			$response .= '<div class="LocalSearchTitle"><div class="LocalSearchTitleStroke">Language Search &darr;</div></div>' . "\n";
			$response .= '<div class="LocalSearch"><div class="LocalSearchStroke">';
			$response .= '<input id="localization-search-input" onpaste="op_admin.Localization.Search()" onkeydown="op_admin.Localization.Search()" onblur="if(this.value==\'\'){this.value=\'Search for a language term...\'}" onfocus="if(this.value==\'Search for a language term...\'){this.value=\'\'}" value="Search for a language term..." type="text" />' . "\n";
			$response .= '</div></div>';
			$response .= '<div class="SearchShadow"></div>';
			
			$response .= '<div style="clear:both;"></div>' . "\n";
			
			// Results
			$response .= '<div class="LocalResultsTitle"><div class="LocalResultsTitleStroke"><div style="float:left;">Language Results &darr;</div></div></div>';
			$response .= '<div id="localization-search-status"></div><div style="clear:both;"></div>' . "\n";
			$response .= '<div class="LocalResults"><div class="LocalResultsStroke">';
			$response .= '<div id="localization-search-results"></div>' . "\n";
			$response .= '</div></div>';
			$response .= '<div class="ResultsShadow"></div>';
			
			$response .= '<div style="clear:both;"></div>' . "\n";
			
			// Edit
			$response .= '<div class="LocalEditTitle"><div class="LocalEditTitleStroke"><div style="float:left;">Your Replacement &darr;</div></div></div>';
			$response .= '<div id="localization-edit-status"></div><div style="clear:both;"></div>' . "\n";
			$response .= '<div class="LocalEdit"><div class="LocalEditStroke">';
			$response .= '<div id="localization-edit-container"></div>' . "\n";
			$response .= '</div></div>';
			$response .= '<div class="EditShadow"></div>';
			
			$response .= '<div style="clear:both;"></div>' . "\n";
			
			$response .= '</div>';
			
			return $response;
		}
		
	}

	class LocalizationModule extends OnePanelModule {
		
		protected $title = 'Localization';
		protected $help_text = 'Use the Localization module to search and replace language terms that appear in your theme.';
		protected $description = 'To easily edit the language of your theme, use the Localization module to search and replace any word(s) with your own.';
		protected $short_description = 'Use the Localization module to search and replace language terms that appear in your theme.';
		protected $keywords = array( 'language', 'localisation', 'localization', 'terms', 'text' );
		protected $categories = array( 'Misc' );
		
		public function Render() {
			$this->GenericRender();			
		}
		
		public function BuildChunks() {
			return true;
		}
		
		protected function RegisterFeatures() {
			$this->RegisterFeature( 'LocalizationTool' );
		}
		
		public function Search() {
			
			$response = '';
			
			if (! isset( $_POST['search_term'] )) {
				$response .= 'One Panel Error: Invalid Post';
			}
			else {
				
				$search_term = $_POST['search_term'];
				
				$editing_language = OnePanelLanguage::GetDefaultLanguage(); // TODO this needs to change in 2.1
				if (! $editing_language) trigger_error( 'One Panel Error: No default language.', E_WARNING );
				
				$language_data = OnePanel::GetLanguageData( $editing_language );
				
				// Search the language data for matching strings
				$matches = array();
				foreach ($language_data as $key => &$string) {
					if (preg_match( '/' . $search_term . '/i', $string )) {
						
						$matches[$key] = $string;
						$response .= '<div style="clear:both;height:2px;"></div>';
						$response .= '<div class="input_o">';
						$response .= '<div class="radio_op">';
						$response .= '<input name="localization_key" onclick="op_admin.Localization.Edit( this.value )" type="radio" value="' . $key . '" /></div>';
						$response .= '<div class="radio_c">' . stripslashes( $string ) . ' (<b>Key:</b> <span style="color:#5089c5;">' . $key . '</span>)</div>'; // TODO use GetTerm()
						$response .= '</div>';
						$response .= '<div style="clear:both;height:2px;"></div>';
						
					}
				}
				
				if (count( $matches ) == 0) {
					$response .= '<div class="popup_no_results"><div class="module_error_stroke">No Results found for &quot;' . $search_term . '&quot;</div></div>';
				}
				
			}
			
			die( $response );
		}
		
		public function Edit() {
			
			$response = '';
			
			if (! isset( $_POST['key'] )) {
				$response .= '<div class="popup_no_results"><div class="module_error_stroke">One Panel Error: Invalid Post</div></div>';
			}
			else {
				
				$key = $_POST['key'];
				
				$editing_language = OnePanelLanguage::GetDefaultLanguage(); // TODO this needs to change in 2.1
				if (! $editing_language) trigger_error( 'One Panel Error: No default language.', E_WARNING );
				
				$language_data = OnePanel::GetLanguageData( $editing_language );
				
				if (! isset( $language_data[$key] )) {
					$response .= '<div class="popup_no_results"><div class="module_error_stroke">One Panel Error: Invalid Language Term</div></div>';
				}
				else {
					
					$text = stripslashes($language_data[$key]);
					$response .= '<textarea id="localization_edit_input" onkeydown="op_admin.Localization.Save( \'' . $key . '\' )" rows="5" cols="45">' . $text . '</textarea>';
					
				}
				
			}
			
			die( $response );
			
		}
		
		public function Save() {
			
			$response = '';
			
			if ((! isset( $_POST['key'] )) || (! isset( $_POST['text'] ))) {
				$response .= '<div class="popup_no_results"><div class="module_error_stroke">One Panel Error: Invalid Post</div></div>';
			}
			else {
				
				$key = $_POST['key'];
				$text = $_POST['text'];
				
				$editing_language = OnePanelLanguage::GetDefaultLanguage(); // TODO this needs to change in 2.1
				if (! $editing_language) trigger_error( 'One Panel Error: No default language.', E_WARNING );
				
				$language_data = &OnePanel::GetLanguageData( $editing_language );
				
				if (! isset( $language_data[$key] )) {
					$response .= '<div class="popup_no_results"><div class="module_error_stroke">One Panel Error: Invalid Language Term</div></div>';
				}
				else {
					
					$language_data[$key] = $text;
					OnePanel::PackData();
					
					$response = true;
					
				}
				
			}
			
			return $response;
			
		}
		
	}