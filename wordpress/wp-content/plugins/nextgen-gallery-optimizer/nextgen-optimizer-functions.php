<?php

/**********************************************************************
* remove nextgen gallery scripts [works on nextgen 1.6.2 and above]
**********************************************************************/

function nggo_remove_nextgen_js() {
	if (!is_admin()) {
		if (!defined('NGG_SKIP_LOAD_SCRIPTS')) {
			define('NGG_SKIP_LOAD_SCRIPTS', true);
		}
	}
}
add_action('init', 'nggo_remove_nextgen_js');



/**********************************************************************
* remove nextgen gallery styles
**********************************************************************/

function nggo_remove_nextgen_css() {
	if (!is_admin()) {
		wp_deregister_style('NextGEN');
		wp_deregister_style('shutter');
		wp_deregister_style('thickbox');
	}
}
add_action('wp_print_styles', 'nggo_remove_nextgen_css', 100);



/**********************************************************************
* check if post contains [nggallery id=x] shortcode
**********************************************************************/

function nggo_check_nggallery_shortcode() {

    global $post;
    global $nggo_options;
	global $nggo_nextgen_options;

 	if (!is_admin()) {
		
		if (have_posts()) {
			while (have_posts()) { 
				the_post();

    			$pattern = get_shortcode_regex();

    			if (preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches )
    			&& array_key_exists( 2, $matches )
    			&& in_array( 'nggallery', $matches[2] ) ) {
    

					if (isset($nggo_options['fancybox']) && ($nggo_options['fancybox'] == true)) {
		
						// see scripts-and-styles.php for functions
						add_action('wp_enqueue_scripts', 'nggo_load_jquery', 1000);
						add_action('wp_enqueue_scripts', 'nggo_load_fancybox_scripts', 1000);
						add_action('wp_print_styles', 'nggo_load_fancybox_styles', 1000);
						add_action('wp_head','nggo_fancybox_inline_js', 1000);
	
					}
					
					if (isset($nggo_nextgen_options['thumbEffect']) && ($nggo_nextgen_options['thumbEffect'] == 'shutter')) {
					
						// see scripts-and-styles.php for functions
						add_action('wp_enqueue_scripts', 'nggo_load_shutter_scripts', 1000);
						add_action('wp_print_styles', 'nggo_load_shutter_styles', 1000);
						add_action('wp_head','nggo_shutter_inline_js', 1000);
						
					}

					if (isset($nggo_nextgen_options['thumbEffect']) && ($nggo_nextgen_options['thumbEffect'] == 'thickbox')) {
					
						if (isset($nggo_options['jquery']) && ($nggo_options['jquery'] == 'google')) {
							add_action('wp_head','nggo_jquery_no_conflict_inline_js', 1000);
						}
						
						// see scripts-and-styles.php for functions
						add_action('wp_enqueue_scripts', 'nggo_load_jquery', 1000);
						add_action('wp_enqueue_scripts', 'nggo_load_thickbox_scripts', 1000);
						add_action('wp_print_styles', 'nggo_load_thickbox_styles', 1000);
						
					}
	
					add_action('wp_print_styles', 'nggo_load_nextgen_styles', 1000); // see scripts-and-styles.php for function

				}
			}
		}
	}
}
add_action( 'wp', 'nggo_check_nggallery_shortcode' );