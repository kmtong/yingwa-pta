<?php
/*
Plugin Name: NextGEN Gallery Optimizer
Description: <strong><a href="http://www.markstechnologynews.com/nextgen-gallery-optimizer-premium/?ref=plugins">*** UPGRADE TO NEXTGEN GALLERY OPTIMIZER PREMIUM ***</a> to add support for ALL TEN NextGEN shortcodes and more!</strong>   NextGEN Gallery Optimizer improves your site's page load speed by ensuring NextGEN Gallery's scripts and styles ONLY load on posts with the [nggallery id=x] shortcode. Also includes and integrates the fantastic Fancybox lightbox script, so now you can have gorgeous galleries AND a speedy site!
Author: Mark Jeldi
Version: 1.1

Author URI: http://www.markstechnologynews.com

Copyright 2012 Mark Jeldi | mark@markstechnologynews.com

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/


/**************************************************
* global variables
**************************************************/

$nggo_options = get_option('nextgen_optimizer_settings');
$nggo_nextgen_options = get_option('ngg_options');

define( 'NGGO_VERSION', '1.1' );
define( 'NGGO_FANCYBOX_VERSION', '1.3.4' );
define( 'NGGO_JQUERY_VERSION', '1.7.2' );



/**************************************************
* includes
**************************************************/

include('nextgen-optimizer-functions.php'); // plugin functionality
include('nextgen-optimizer-options.php'); // the plugin options page HTML, linked CSS and save functions
include('nextgen-optimizer-scripts-and-styles.php'); // script and stylesheet include functions



/**************************************************
* add options page
**************************************************/

// call our stylesheet
function nextgen_optimizer_load_styles() {
	wp_enqueue_style('nextgen_optimizer_styles', plugin_dir_url( __FILE__ ) . 'css/nextgen-optimizer-options.css');
}

// attach the above wp_enqueue_style so our stylesheet only loads on the options page we're building
function nextgen_optimizer_add_options_page() {
	$nextgen_optimizer_options_page = add_options_page('NextGEN Gallery Optimizer', 'NextGEN Optimizer', 'manage_options', 'nextgen_optimizer_options', 'nextgen_optimizer_options_page');
	add_action('admin_print_styles-' . $nextgen_optimizer_options_page, 'nextgen_optimizer_load_styles');
}

// create options page complete with attached css file and link in admin menu. 
add_action('admin_menu', 'nextgen_optimizer_add_options_page');



/**************************************************
* save settings
**************************************************/

// create our settings in the options table
function nextgen_optimizer_register_settings() {
	register_setting('nextgen_optimizer_settings_group', 'nextgen_optimizer_settings');
}
add_action('admin_init', 'nextgen_optimizer_register_settings');



/**************************************************
* add settings & donate links on plugins page
**************************************************/

function nextgen_optimizer_settings_link($links, $file) {
	if ($file == plugin_basename(__FILE__)) {
		$links[] = '<a href="'.admin_url('options-general.php?page=nextgen_optimizer_options').'">Settings</a>';
		$links[] = '<a href="http://wordpress.org/tags/nextgen-gallery-optimizer?forum_id=10">Support Forum</a>';
		$links[] = '<a href="http://wordpress.org/extend/plugins/nextgen-gallery-optimizer">Rate this plugin</a>';
		$links[] = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=YGS3ANA29BJ2W">Donate</a>';
	}
	return $links;
}
add_filter('plugin_row_meta', 'nextgen_optimizer_settings_link', 10, 2);



/**********************************************************************
* define default option settings on first activation
**********************************************************************/

function nggo_add_default_values() {
	
	// global $nggo_options doesn't work here. Maybe too early?
	$nggo_options = get_option('nextgen_optimizer_settings'); 
    	
    if (!is_array($nggo_options)) {  // set defaults for new users only
		
		$nggo_default_values = array(
				"theme" => "Default Styles",
				"css" => "",
				"fancybox" => "1",
				"do_redirect" => "yes",
				"show_message" => "yes"
				);
				
		update_option('nextgen_optimizer_settings', $nggo_default_values);
		
	}
}
register_activation_hook(__FILE__, 'nggo_add_default_values');



/********************************************************************************
* define extra default options after update from earlier versions
********************************************************************************/

function nggo_add_extra_default_options() {
	
	global $nggo_options;
	
	if (!isset($nggo_options['jquery'])) {
	
		$nggo_options['jquery'] = 'wordpress'; // add value to array
		update_option('nextgen_optimizer_settings', $nggo_options); // update option array
	}
}
add_action('admin_init', 'nggo_add_extra_default_options');



/**********************************************************************
* redirect users to settings page on first activation
**********************************************************************/

function nggo_redirect_to_settings() {

    global $nggo_options;
		
	if (isset($nggo_options['do_redirect']) && ($nggo_options['do_redirect'] == 'yes')) {
        	        	
        wp_redirect(admin_url('options-general.php?page=nextgen_optimizer_options', __FILE__));
			
		// we only want to redirect to the settings page on first activation
		// so we'll update the value of "do_redirect" to "no"

		$nggo_options['do_redirect'] = 'no'; // amend value in array
		update_option('nextgen_optimizer_settings', $nggo_options); // update option array

	}		
}
add_action('admin_init', 'nggo_redirect_to_settings');



/**********************************************************************
* display thank you message on first activation
**********************************************************************/

function nggo_thanks_for_downloading() {
	
	if (isset($_GET['page']) && $_GET['page'] == 'nextgen_optimizer_options') {
		
		global $nggo_options;

    	if (isset($nggo_options['show_message']) && ($nggo_options['show_message'] == 'yes')) {
        	        	
			echo '
			<div id="message" class="updated">
			<p>Thanks for downloading NextGEN Gallery Optimizer! Please review the steps below to complete the installation.</p>
			</div>
			';
			
			// we only want to show this message once on first activation
			// so we'll update the value of "show_message" to "no"

			$nggo_options['show_message'] = 'no'; // amend value in array
			update_option('nextgen_optimizer_settings', $nggo_options); // update option array
	
		}
	}		
}
add_action('admin_notices', 'nggo_thanks_for_downloading');



/********************************************************************************
* Fix for Fancybox on IE6 & IE8
* Microsoft.AlphaImageLoader CSS requires absolute file paths.
* We'll run a regex (on activation and update) to write in the correct urls.
********************************************************************************/

function nggo_fancybox_stylesheet_regex() {
	
	global $nggo_options;
	
	if (is_admin()) {
	
		if (!isset($nggo_options['version']) ||
		isset($nggo_options['version']) && $nggo_options['version'] != NGGO_VERSION) {

			$nggo_css_filename = WP_PLUGIN_DIR."/nextgen-gallery-optimizer/css/jquery.fancybox-1.3.4.css";
			$nggo_image_path = plugins_url( 'fancybox/' , __FILE__);
			$nggo_data = file_get_contents($nggo_css_filename);

			// the regex
			$nggo_patterns = array();
			$nggo_patterns[0] = '/\(src=\'(.*?)fancybox\//';
			$nggo_patterns[1] = '/url\(\'(.*?)fancybox\//';
			$nggo_replacements = array();
			$nggo_replacements[0] = '(src=\'' . $nggo_image_path; 
			$nggo_replacements[1] = 'url(\'' . $nggo_image_path;
			$nggo_update_css = preg_replace($nggo_patterns, $nggo_replacements, $nggo_data);

			// update css
			if (is_writable($nggo_css_filename)) {

				if (!$handle = fopen($nggo_css_filename, 'w+')) {
				add_action( 'admin_notices', 'nggo_file_not_writable_error' );
				exit;
    			}

    			if (fwrite($handle, $nggo_update_css) === FALSE) {
    			add_action( 'admin_notices', 'nggo_file_not_writable_error' );
				exit;
				}

			// we only want to run this regex on first activation or after auto-update
			// so we'll insert a "version" option to check against
			
			$nggo_options['version'] = NGGO_VERSION; // insert field or update value in array
			update_option('nextgen_optimizer_settings', $nggo_options); // update option array

			fclose($handle);


			} else {
	
				add_action( 'admin_notices', 'nggo_file_not_writable_error' );
			
			}
		}
	}
}
add_action('admin_init', 'nggo_fancybox_stylesheet_regex');


function nggo_file_not_writable_error() {
	global $pagenow;
	
	// admin error message
	
	if ($pagenow == 'plugins.php' ||
	isset($_GET['page']) && $_GET['page'] == 'nextgen_optimizer_options') {

	$nggo_css_filename = WP_PLUGIN_DIR."/nextgen-gallery-optimizer/css/jquery.fancybox-1.3.4.css"; // global doesn't seem to work here

	echo '<div class="error"><p>The file ' . $nggo_css_filename . ' is not writable. Please change its permissions to 766.</p></div>';

	}
}



/********************************************************************************
* automatic fancybox installation
* saves original values on Gallery --> Options --> Effects page
* updates ngg_options with **class="myfancybox" rel="%GALLERY_NAME%"**
* reverts to previous values on deactivation
********************************************************************************/

function nggo_fancybox_auto_install() {
	
	global $nggo_options;
	$nggo_nextgen_options = get_option('ngg_options');
	
	if (is_admin()) {
	
		if (!isset($nggo_options['original_nextgen_thumbEffect']) || ($nggo_options['original_nextgen_thumbEffect'] == 'none')) {
			
			// capture original values for nextgen thumbEffect and thumbCode
				
			$nggo_options['original_nextgen_thumbEffect'] = $nggo_nextgen_options['thumbEffect']; // insert field or update value in array
			$nggo_options['original_nextgen_thumbCode'] = $nggo_nextgen_options['thumbCode']; // insert field or update value in array
			update_option('nextgen_optimizer_settings', $nggo_options); // update option array
		}
			
		if (isset($nggo_options['fancybox']) && ($nggo_options['fancybox'] == true)) {
			if (!isset($nggo_options['auto_fancybox_install']) || ($nggo_options['auto_fancybox_install'] != 'installed')) {
				
				// update nextgen for fancybox integration
				$nggo_nextgen_options['thumbEffect'] = 'custom'; // insert field or update value in array
				$nggo_nextgen_options['thumbCode'] = 'class=\"myfancybox\" rel=\"%GALLERY_NAME%\"'; // insert field or update value in array
				update_option('ngg_options', $nggo_nextgen_options); // update option array
			
				// set an option so we only run the install once
				$nggo_options['auto_fancybox_install'] = 'installed'; // insert field or update value in array
				update_option('nextgen_optimizer_settings', $nggo_options); // update option array
			
			}
		}	
			
		if (isset($nggo_options['fancybox']) && ($nggo_options['fancybox'] == true)) {	
			
			if (($nggo_nextgen_options['thumbEffect'] != 'custom') ||
			($nggo_nextgen_options['thumbCode'] != 'class=\"myfancybox\" rel=\"%GALLERY_NAME%\"')) {	
	
				// if nextgen's effects settings are accidentally changed while optimizer is activated and fancybox checked
				// update fancybox integration and show notification message

				$nggo_nextgen_options['thumbEffect'] = 'custom'; // insert field or update value in array
				$nggo_nextgen_options['thumbCode'] = 'class=\"myfancybox\" rel=\"%GALLERY_NAME%\"'; // insert field or update value in array
				update_option('ngg_options', $nggo_nextgen_options); // update option array
			
				add_action('admin_notices', 'nggo_please_uncheck_fancybox');
				
			}
		}

	}
}
add_action('admin_init', 'nggo_fancybox_auto_install');


function nggo_please_uncheck_fancybox() {
    	
	echo '
	<div id="message" class="updated">
	<p>
	To use a different gallery effect, please deactivate Fancybox on the 
	<a href="' . admin_url('options-general.php?page=nextgen_optimizer_options', __FILE__) . '" target="_blank">
	NextGEN Optimizer settings page</a> and return to 
	<a href="' . admin_url( 'admin.php?page=nggallery-options#effects' , __FILE__) . '" target="_blank">
	Gallery --> Options --> Effects</a> to make your changes.
	</p>
	</div>
	';
	
}


function nggo_fancybox_auto_uninstall() {
	global $nggo_options;
	$nggo_nextgen_options = get_option('ngg_options');
	
	if (is_admin()) {
	
		if (isset($nggo_options['fancybox']) && ($nggo_options['fancybox'] == true)) {
		
			if (isset($nggo_options['original_nextgen_thumbEffect']) && isset($nggo_options['original_nextgen_thumbCode'])) {

				// switch nextgen back to original values on deactivation
				$nggo_nextgen_options['thumbEffect'] = $nggo_options['original_nextgen_thumbEffect']; // insert field or update value in array
				$nggo_nextgen_options['thumbCode'] = $nggo_options['original_nextgen_thumbCode']; // insert field or update value in array
				update_option('ngg_options', $nggo_nextgen_options); // update option array
						
			}
		}
	
		// empty our settings so we can run again on reactivation
		$nggo_options['original_nextgen_thumbEffect'] = 'none';
		$nggo_options['original_nextgen_thumbCode'] = 'none';
		$nggo_options['auto_fancybox_install'] = 'uninstalled';
		update_option('nextgen_optimizer_settings', $nggo_options); // update option array
	
	}
}
register_deactivation_hook(__FILE__, 'nggo_fancybox_auto_uninstall');