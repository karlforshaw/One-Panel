<?php

	class PostInfoFeature extends OnePanelFeature {
	
		protected $title = 'Post Info';
		protected $help_text = 'Display post information under the post title, enabling the post date, the author link and how many comments the post has received.';
		
		public function Render() {
			return $this->RenderOnOff();
		}
		
	}
	
	class AuthorGravatarFeature extends OnePanelFeature {
		
		protected $title = 'Post Author Gravatar';
		protected $help_text = 'Enable Author Gravatar to display a unique avatar image next to each blog entry.';
		
		public function Render() {
			return $this->RenderOnOff();
		}
		
	}

	class PostHeader extends OnePanelModule {

		protected $title = 'Post Header';
		protected $help_text = 'Display post information under the post title, enabling the post date, the author link and how many comments the post has received. Also enables better formatting of Gravatar.';
		protected $description = 'Use the Post Header module to enable/disable the post information area above each blog entry. Not forgetting Gravatar; Post Header enables you to use those eyecathing avatars that we seem to see everywhere.';
		protected $short_description = 'Display more information on your posts by activating post information. Also enables better formatting of Gravatar.';
		protected $keywords = array( 'post header', 'post', 'header', 'post info', 'info', 'author gravatar', 'gravatar', 'gravatars', 'author' );
		protected $categories = array( 'Appearance' );
		
		public function Render() {
			$this->GenericRender();
		}
		
		public function RegisterFeatures() {
			
			$this->RegisterFeature( 'PostInfoFeature' );
			$this->RegisterFeature( 'AuthorGravatarFeature' );
			
		}
		
		public function BuildChunks() {
			return true;
		}
		
	}
	
	