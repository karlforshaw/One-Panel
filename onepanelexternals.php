<?php

	class OnePanelExternals {
		
		public static function AddActions() {
			
			add_action( 'save_post', array( 'OnePanelExternals', 'AutoGenThumbs' ) );
			
		}
		
		
		
		
		/**
		 * 
		 * @param $post
		 * @return unknown_type
		 * @todo anything in the html that has dodgy characters in it will make DOM unhappy. 
		 * Probably best to hold back errors.
		 */
		public static function AutoGenThumbs( $post ) {
			
			$tracker = OnePanelDebug::Track( 'Trying to autogenerate thumbnails.' );
			
			//　Wordpress is crazy
			$post_id = wp_is_post_revision($post);
			OnePanelDebug::Info( 'Post id: ' . $post_id );
			
			// Create thumbnail module object?
			OnePanelLib::RequireFileOnce( ONE_PANEL_DIR . '/panel/module.php' );
			OnePanelLib::RequireFileOnce( ONE_PANEL_DIR . '/panel/feature.php' );
			OnePanelLib::RequireFileOnce( ONE_PANEL_DIR . '/panel/thumbnails.php' );
			$thumbnail_feature_object = new Thumbnails();
			
			// Scan the post for images 
			$dom_doc = new DOMDocument();
			@$dom_doc->loadHTML( $_POST['content'] );
			
			// Grab the first image
			$first_image = $dom_doc->getElementsByTagName( 'img' )->item(0);
			
			if (is_null( $first_image )) {
				OnePanelDebug::Info( 'No images found in post' );
				return true;
			}
			
			// Get the location of the image
			
			$src = str_replace( '"', '' ,$first_image->getAttribute( 'src' ) );
			$src = str_replace( '\\', '', $src );
			
			// Get the real path
			$src = str_replace( get_option('siteurl'), '', $src) ;
			$location = ABSPATH . $src;
			$location = str_replace( '//' , '/', $location );
			
			// Generate
			OnePanelDebug::Info( 'Calling CreateThumbs with ' . $location . ' ' . $post_id );
			$thumbnail_feature_object->CreateThumbs( $location, $post_id, 'All', false );
			
			// All done
			$tracker->Affirm();
			return true;
			
		}
		
	}