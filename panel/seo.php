<?php

	class FriendlySearchUrlFeature extends OnePanelFeature {
		
		protected $title = 'SEO Friendly Search Permalinks';
		protected $short_description = 'Use the SEO module to make your website more search engine friendly.';
		protected $help_text = null;
		
		public function __construct() {
			$this->help_text = 'Your search page URL\'s will look like this if activated: ' . get_option('home') . '/search/this+is+a+search+example';
		}
		
		public function Render() {
			
			if ($this->CanBeActive()) {
				return $this->RenderOnOff();
			}
			else {
				return $this->RenderNoEnable();
			}
			
		}
		
		private function RenderNoEnable() {
			
			$response = '<div class="module_error"><div class="module_error_stroke"><div style="padding-bottom:8px;font-size:14px;display:block;"><b>Please Note...</b></div>
			<span style="font-weight:normal;">'. OnePanel::GetLicenseeName() .', you must be using custom permalinks to use this feature. Activate custom permalinks in WordPress settings.</span></div></div>';
			
			return $response;
		}
		
		public function CanBeActive() {
			
			$permalinks_enabled = get_option( 'permalink_structure' );
			
			if (empty( $permalinks_enabled )) {
				return false; 
			}
			else {
				return true;
			}
		}
		
	}

	class SEO extends OnePanelModule {
		
		protected $title = 'SEO';
		protected $help_text = 'Use the SEO module to make your website more search engine friendly.';
		protected $description = 'We\'re all after good rankings in big search engines like Google. Use One Panel\'s SEO tool to optimize your blog for better search engine results. More features coming soon!';
		protected $short_description = 'Use the SEO module to make your website more search engine friendly.';
		protected $keywords = array( 'seo', 'search', 'engine', 'optimisation', 'google', 'msn', 'yahoo', 'search friendly', 'search url', 'url', 'friendly' );
		protected $categories = array( 'SEO' );
		
		public function Render() {
			$this->GenericRender();
		}
		
		public function RegisterFeatures() {
			
			$this->RegisterFeature( 'FriendlySearchUrlFeature' );
			
			if (is_object( $this->features['FriendlySearchUrlFeature'] )) {
				$feature = &$this->features['FriendlySearchUrlFeature'];
				
				if (! $feature->CanBeActive()) {
					$feature->OverrideActivation();
				}
				
			}
			
		}
		
		public function BuildChunks() {
			return true;
		}
		
	}