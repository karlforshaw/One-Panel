<?php

	class OnePanelInstaller {
		
		public static function PrintHeader() {
			
			echo '<div id="one_panel_installer_container">' . "\n";
		
			echo	'<style type="text/css">
					body{margin:0 auto;float:none;width:100%;}
					#one_panel_installer_container{border:3px solid #e1e1e1;font-family:Geneva, Arial, sans-serif;font-size:12px;line-height:22px;margin:20px auto;float:none;width:100%;padding:2em;width:800px;-moz-border-radius:12px;-webkit-border-radius:12px;background:#FFF url(http://www.one-theme.com/images/onepanel/icons/containerbg.gif) no-repeat top;}
					#ts-container{background:#f8f8f8;padding:15px;border:double #EEE;-moz-border-radius:6px;-webkit-border-radius:6px;}
					#ts-container P{padding-left:15px;max-width:690px;}
					#ts-header{background:url(http://www.one-theme.com/images/onepanel/icons/blue-logo.png) no-repeat left center;color:#555;font-size:20px;font-weight:bold;margin:0;padding:15px 0 26px 82px;font-family:\'Lucida Sans Unicode\', \'Lucida Grande\', Arial, sans-serif;}
					.ts-not-ok{font-weight:bold;border-bottom:1px dashed #a1241e;color:#a1241e;padding:0 1px;}
					.ts-ok{font-weight:bold;border-bottom:1px dashed #558502;color:#558502;padding:0 1px;}
					h2{border:1px solid #eee;background:#FFF url(http://www.one-theme.com/images/onepanel/icons/title_bg.png) no-repeat right;padding:8px;font-size:14px;color:#555;-moz-border-radius:3px;-webkit-border-radius:3px;margin-top:0;}
					.ts-check{background:url(http://www.one-theme.com/images/onepanel/icons/tick.png) no-repeat 725px 55px;min-height:100px;}
					.dead{background:url(http://www.one-theme.com/images/onepanel/icons/cross.png) no-repeat 725px 55px;min-height:100px;}
				</style>'. "\n";
			
			echo '<h1 id="ts-header">One Panel Installer</h1>'. "\n";
			echo '<p style="line-height:22px;font-style:italic;" id="ts-intro-text">You are viewing this page because One Panel cannot currently run on your server. You should however be able to get it up and running in no time by following the steps below.</p>'. "\n";
			
		}
		
		public function PrintSimpleHeader() {
			
			echo '<div id="one_panel_installer_container">' . "\n";
		
			echo '<style type="text/css">
					body{margin:0 auto;float:none;width:100%;}
					#one_panel_installer_container{border:3px solid #e1e1e1;font-family:Geneva, Arial, sans-serif;font-size:12px;line-height:22px;margin:20px auto;float:none;width:100%;padding:2em;width:800px;-moz-border-radius:12px;-webkit-border-radius:12px;background:#FFF url(http://www.one-theme.com/images/onepanel/icons/containerbg.gif) no-repeat top;}
					#ts-container{background:#f8f8f8;padding:15px;border:double #EEE;-moz-border-radius:6px;-webkit-border-radius:6px;}
					#ts-container P{padding-left:15px;max-width:690px;}
					#ts-header{background:url(http://www.one-theme.com/images/onepanel/icons/blue-logo.png) no-repeat left center;color:#555;font-size:20px;font-weight:bold;margin:0;padding:15px 0 26px 82px;font-family:\'Lucida Sans Unicode\', \'Lucida Grande\', Arial, sans-serif;}
					.ts-not-ok{font-weight:bold;border-bottom:1px dashed #a1241e;color:#a1241e;padding:0 1px;}
					.ts-ok{font-weight:bold;border-bottom:1px dashed #558502;color:#558502;padding:0 1px;}
					h2{border:1px solid #eee;background:#FFF url(http://www.one-theme.com/images/onepanel/icons/title_bg.png) no-repeat right;padding:8px;font-size:14px;color:#555;-moz-border-radius:3px;-webkit-border-radius:3px;margin-top:0;}
					.ts-check{background:url(http://www.one-theme.com/images/onepanel/icons/tick.png) no-repeat 725px 55px;min-height:100px;}
					.dead{background:url(http://www.one-theme.com/images/onepanel/icons/cross.png) no-repeat 725px 55px;min-height:100px;}
				</style>'. "\n";
			
			echo '<h1 id="ts-header">One Panel</h1>'. "\n";
			
		}
		
		public static function PrintFooter() {
			echo 	'</div>'. "\n";
		}
			
		
	}