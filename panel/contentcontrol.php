<?php

	class PostTitleLimitFeature extends OnePanelFeature {
		
		protected $title = 'Post Title Limit';
		protected $help_text = 'If you enable this feature One Panel will limit your post titles to better suite the layout. Disable to stop post title limiting.';
		
		public function Render() {
			// Use Generic On and Off
			return $this->RenderOnOff();
		}
		
	}
	
	class ContentControlFeature extends OnePanelFeature  {
		
		protected $title = "Content Limiting";
		protected $limit_mode = 1;
		protected $content_limit = 400;
		
		public function Render() {
			
			ob_start();
			?>
			<div class="module_title">
				<div class="popup_generic_title">Content Limit &darr;</div>
                <div style="padding-top:8px;">Use Content Control to manage the amount of characters that will be displayed on blog excerpts.</div>
				<div class="thumb_container">
					<div class="ContentLimit_thumb"></div>
				</div>
				</div>
			
            <div class="generic_content">

			<div style="clear:both;"></div>

				<div class="input_option">
					<div class="radio_option">
						<input onclick="op_admin.ContentControl.ToggleLimitMode( this.value )" type="radio" name="ot_limit_mode" value="1" <?php echo $this->limit_mode == 1 ? 'checked' :'' ; ?> />
					</div>
				<div class="radio_content">
					WordPress Default <strong>(use Excerpts &amp; "more" tags)</strong>
				</div>
			</div>
            
            <div style="clear:both; height:5px;"></div>

			<div class="input_option">
				<div class="radio_option">
					<input onclick="op_admin.ContentControl.ToggleLimitMode( this.value )" type="radio" name="ot_limit_mode" value="2" <?php echo $this->limit_mode == 2 ? 'checked' :'' ; ?> />
				</div>
				<div class="radio_content">
					No Limit <strong>(show full posts)</strong>
				</div>
			</div>
            
            <div style="clear:both; height:5px;"></div>

			<div class="input_option">
				<div class="radio_option">
					<input onclick="op_admin.ContentControl.ToggleLimitMode( this.value )" type="radio" name="ot_limit_mode" value="3" <?php echo $this->limit_mode == 3 ? 'checked' :'' ; ?> />
				</div>
				<div class="radio_content">
					User Defined Limit<strong> (for best results)</strong>
				</div>
			</div>
            
          <div style="clear:both; height:5px;"></div>
                
			<div class="input">
				<select onchange="op_admin.ContentControl.ToggleLimitMode( 3 )" id="limit_mode_3" name="ot_content_limit" <?php echo $this->limit_mode == 3 ? '' :'disabled' ; ?> >
					<option <?php echo $this->content_limit == '100' ? 'selected="selected "' :''; ?>value="100">100 Characters</option>
					<option <?php echo $this->content_limit == '200' ? 'selected="selected "' :''; ?>value="200">200 Characters</option>
					<option <?php echo $this->content_limit == '300' ? 'selected="selected "' :''; ?>value="300">300 Characters</option>
					<option <?php echo $this->content_limit == '400' ? 'selected="selected "' :''; ?>value="400">400 Characters</option>
					<option <?php echo $this->content_limit == '500' ? 'selected="selected "' :''; ?>value="500">500 Characters</option>
					<option <?php echo $this->content_limit == '600' ? 'selected="selected "' :''; ?>value="600">600 Characters</option>
					<option <?php echo $this->content_limit == '700' ? 'selected="selected "' :''; ?>value="700">700 Characters</option>
					<option <?php echo $this->content_limit == '800' ? 'selected="selected "' :''; ?>value="800">800 Characters</option>
				</select>
			</div>
            
		</div>
			
			<?php
			$content = ob_get_contents();
			ob_end_clean();
			
			return $content;
		}
		
		public function ChangeMode() {
			
			$limit_mode = $_POST['limit_mode'];
			if (! is_numeric( $limit_mode )) die(false);
			$limit_mode = (int) $limit_mode;
		
			$this->limit_mode = $limit_mode;
			
			if ($limit_mode == 3) {
				
				$limit = $_POST['limit'];
				if (! is_numeric( $limit )) die(false);	
				$this->content_limit = (int) $limit;
				
			}
			
			OnePanel::PackData();			

			die(true);
		}
		
		public function GetLimitMode() {
			return $this->limit_mode;	
		}
		
		public function GetLimit() {
			return $this->content_limit;	
		}
		
	}
	
	

	class ContentControl extends OnePanelModule {
		
		protected $title = 'Content Control';
		protected $help_text = 'Use Content Control to manage the amount of characters that will be displayed on blog excerpts.';
		protected $description = 'Promote site visitors interest with the use of content control that allows you to specify the number of characters that will be shown in the excerpt text. ';
		protected $short_description = 'Use Content Control to manage the amount of characters that will be displayed on blog excerpts.';
		protected $keywords = array( 'post title','title','limit', 'content', 'content control', 'control', 'limiting');
		protected $categories = array( 'Appearance' );
				
		public function Render() {
			$this->GenericRender();
		}
		
		protected function RegisterFeatures() {
			
			$this->RegisterFeature( 'PostTitleLimitFeature' );
			$this->RegisterFeature( 'ContentControlFeature' );
			
		}
		
		public function BuildChunks() {
			return true;
		}
		
	}