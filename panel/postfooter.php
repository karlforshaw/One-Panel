<?php

	class AuthorProfileLinkFeature extends OnePanelFeature  {
		
		protected $title = 'Author Profile Link';
		protected $help_text = 'This module will display a Author Profile link beneath each blog entry.';
		
		public function Render() {
			return $this->RenderOnOff();
		}
		
	}
	
	class RSSLinkFeature extends OnePanelFeature {
		
		protected $title = 'RSS Link';
		protected $help_text = 'Enable RSS Link to display a "Subscribe" link beneath blog entires.';
		
		public function Render() {
			return $this->RenderOnOff();
		}
		
	}
	
	class RelatedArticlesFeature extends OnePanelFeature {
		
		protected $title = 'Related Articles';
		protected $help_text = 'Enable Related Articles to display on-topic blog entires beneath your articles.';
		
		public function Render() {
			return $this->RenderOnOff();
		}
		
		public function GetChunk() {
			
			global $post;
			$post_id = $post->ID;
			$post_title = $post->post_title;
			
			$keywords = explode( ' ', $post_title );
			$word_count = count( $keywords );
			$overusedwords = array( '', 'a', 'an', 'the', 'and', 'of', 'i', 'to', 'is', 'in', 'with', 'for', 'as', 'that', 'on', 'at', 'this', 'my', 'was', 'our', 'it', 'you', 'we', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '10', 'about', 'after', 'all', 'almost', 'along', 'also', 'amp', 'another', 'any', 'are', 'area', 'around', 'available', 'back', 'be', 'because', 'been', 'being', 'best', 'better', 'big', 'bit', 'both', 'but', 'by', 'c', 'came', 'can', 'capable', 'control', 'could', 'course', 'd', 'dan', 'day', 'decided', 'did', 'didn', 'different', 'div', 'do', 'doesn', 'don', 'down', 'drive', 'e', 'each', 'easily', 'easy', 'edition', 'end', 'enough', 'even', 'every', 'example', 'few', 'find', 'first', 'found', 'from', 'get', 'go', 'going', 'good', 'got', 'gt', 'had', 'hard', 'has', 'have', 'he', 'her', 'here', 'how', 'if', 'into', 'isn', 'just', 'know', 'last', 'left', 'li', 'like', 'little', 'll', 'long', 'look', 'lot', 'lt', 'm', 'made', 'make', 'many', 'mb', 'me', 'menu', 'might', 'mm', 'more', 'most', 'much', 'name', 'nbsp', 'need', 'new', 'no', 'not', 'now', 'number', 'off', 'old', 'one', 'only', 'or', 'original', 'other', 'out', 'over', 'part', 'place', 'point', 'pretty', 'probably', 'problem', 'put', 'quite', 'quot', 'r', 're', 'really', 'results', 'right', 's', 'same', 'saw', 'see', 'set', 'several', 'she', 'sherree', 'should', 'since', 'size', 'small', 'so', 'some', 'something', 'special', 'still', 'stuff', 'such', 'sure', 'system', 't', 'take', 'than', 'their', 'them', 'then', 'there', 'these', 'they', 'thing', 'things', 'think', 'those', 'though', 'through', 'time', 'today', 'together', 'too', 'took', 'two', 'up', 'us', 'use', 'used', 'using', 've', 'very', 'want', 'way', 'well', 'went', 'were', 'what', 'when', 'where', 'which', 'while', 'white', 'who', 'will', 'would', 'your');
			
			if ($word_count == 0) {
				return false;
			}
			
			 
			global $wpdb;
			$sql = "SELECT ID AS id, post_title AS post_title, post_content AS post_content FROM " . DB_NAME . ".{$wpdb->prefix}posts WHERE ";
			
			for ($i=0; $i < $word_count; $i++) {
				
				$keyword = mysql_real_escape_string($keywords[$i]);
				
				if (! in_array( $keyword, $overusedwords )) {
					$sql .= "post_title LIKE '%{$keyword}%' AND ID != {$post_id} AND post_type='post' AND post_status='publish' ";
					if ($i + 1 != $word_count) $sql .= "OR ";
				}
				 
			}
			
			$sql .= 'LIMIT 3';
			$result = mysql_query( $sql );
			
			$error = '<div id="related-articles">    
						<div class="error">There are no related articles.</div>
					</div>';
			
			$return = '';
			
			if (! $result) {
				return $error;
			}
			else {
				
				$num_rows = mysql_numrows( $result );
				$return .= '<div id="related-articles"><div class="title">' . OnePanelLanguage::GetText( 'related' ) . '</div>';  
				
				if ($num_rows > 0) {
					
					for ($i=0; $i < $num_rows; $i++) {
						
						$id = mysql_result( $result, $i, 'ID' );
						$title = mysql_result( $result, $i, 'post_title' );
						$content = mysql_result( $result, $i, 'post_content' );
						
						$return .= '<div class="related-article">'; 
						$return .= OnePanelTheme::GetThumbnail( $id );
						$return .= '<div class="title"><a href="' . get_permalink( $id ) .'">' . substr( $title, 0, 39 ) . '...</a></div>';
						$return .= substr(strip_tags( $content ), 0, 135) . '...';
						$return .= '</div>';
					}
					
					$return .= '</div>';
					return $return;
				
				}
				else {
					return $error;
				}
			}
		}
		
	}
	
	class SocialBookmarksFeature extends OnePanelFeature  {
		
		protected $title = 'Social Bookmarks';
		protected $help_text = 'Enable this module to allow readers to submit your posts to Social Bookmarking websites.';
		
		public function Render() {
			return $this->RenderOnOff();
		}
		
	}
	
	class TagsFeature extends OnePanelFeature {
		
		protected $title = 'Tags';
		protected $help_text = 'Enable this module to display tags beneath blog entires.';
		
		public function Render() {
			return $this->RenderOnOff();
		}
		
	}


	class PostFooter extends OnePanelModule {
		
		protected $title = 'Post Footer';
		protected $help_text = 'Use Post Footer to activate Related Articles, Tags, Social Bookmarks and more.';
		protected $description = 'Offer your readers more with a selection of extras that are accessible below your posts. Keep visitors interested with other Related Articles, or give them the ability to submit your articles to various social bookmarking websites.';
		protected $short_description = 'Use Post Footer to activate Related Articles, Tags, Social Bookmarks and more.';
		protected $keywords = array( 'post footer', 'post', 'footer', 'author profile link', 'author', 'profile', 'link', 'related articles', 'related posts', 'related', 'rss link', 'rss', 'social bookmarks', 'social', 'bookmarks', 'bookmarking', 'digg', 'stumble', 'delicious', 'reddit', 'yahoo', 'google', 'tags', 'tag', 'tagged' );
		protected $categories = array( 'Appearance' );
		
		public function Render() {
			$this->GenericRender();
		}
		
		public function RegisterFeatures() {
			
			$this->RegisterFeature( 'AuthorProfileLinkFeature' );
			$this->RegisterFeature( 'RSSLinkFeature' );
			$this->RegisterFeature( 'RelatedArticlesFeature' );
			$this->RegisterFeature( 'SocialBookmarksFeature' );
			$this->RegisterFeature( 'TagsFeature' );
			
		}
		
		public function BuildChunks() {
			
			// Author Profile Link
			$enabled = $this->features['AuthorProfileLinkFeature']->IsActive();
			
			if ($enabled) {
				$this->chunks['AuthorProfileLink'] = '<div id="author-profile-link">' . OnePanelLanguage::GetText( 'author_view' ) . '</div>'; 
			}
			
			
			// RSS Link
			$enabled = $this->features['RSSLinkFeature']->IsActive();
			
			if ($enabled) {
				$this->chunks['RSSLink'] = '<div id="rss-link">' . OnePanelLanguage::GetText( 'subscribe_via' ) . '</div>'; 
			}
			
			
			// Related Articles
			$enabled = $this->features['RelatedArticlesFeature']->IsActive();
			
			if ($enabled) {
				$this->chunks['RelatedArticles'] = $this->features['RelatedArticlesFeature']->GetChunk();
			}
			
			
			// Social Bookmarks
			$enabled = $this->features['SocialBookmarksFeature']->IsActive();
			
			if ($enabled) {
				
				$chunk  =  '<div id="social-bookmarks">';
                $chunk .= '<a class="digg" href="http://digg.com/submit?phase=2&url=' . get_permalink() . '&title=' . get_the_title() . '">Digg it</a>';
				$chunk .= '<a class="stumble" href="http://www.stumbleupon.com/submit?url=' . get_permalink() . '&title=' . get_the_title() . '">Stumble</a>';
				$chunk .= '<a class="delicious" href="http://del.icio.us/post?url=' . get_permalink() . '&title=' . get_the_title() . '"> del.icio.us</a>';
				$chunk .= '<a class="reddit" href="http://reddit.com/submit?url=' . get_permalink() . '&title=' . get_the_title() . '">reddit</a>';
				$chunk .= '<a class="yahoo" href="http://myweb2.search.yahoo.com/myresults/bookmarklet?t=' . get_the_title() . '&u=' . get_permalink() . '">Yahoo</a>';
				$chunk .= '<a class="google" href="http://www.google.com/bookmarks/mark?op=edit&bkmk=' . get_permalink() . '&title=' . get_the_title() . '">Google</a>';
				$chunk .= '</div>';
				
				$this->chunks['SocialBookmarks'] = &$chunk;
			}
			
			
			// Tags
			$enabled = $this->features['TagsFeature']->IsActive();
			
			if ($enabled) {
				
				global $post;
				
				$tags = get_the_term_list( $post->ID, 'post_tag', '<div class="tag">','', '</div>');
				
				if (! empty( $tags )) {
					$this->chunks['Tags'] = '<div id="tags"><div class="title">' . OnePanelLanguage::GetText( 'tags' ) . '</div>' .$tags . '</div>' . "\n"; 
				}
				else {
					$this->chunks['Tags'] = '<div id="no-tags">' . OnePanelLanguage::GetText( 'no_tags' ) . '</div>' . "\n";
				}
				
			}
			
		}
		
	}