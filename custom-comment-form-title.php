<?php
/*
Plugin Name: Custom Comment Form Title
Plugin URI: http://custom-comment-form-title.media-cairn.com/
Description: Create custom Comment Form Titles for individual posts.
Version: 2.0.1
Author: MediaCairn Design Studio
Author URI: http://www.media-cairn.com/
License: GPLv2 or later
*/

/*  Copyright 2014 MediaCairn Design Studio

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

/*
--------------------
INTERNATIONALIZATION
--------------------
*/

add_action('init', 'ccft_ap_action_init');
function ccft_ap_action_init() {
	load_plugin_textdomain('ccft-lang', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

/* 
------------------------------
SETUP THE ADMIN OPTIONS SCREEN
------------------------------
*/

add_action('admin_init', 'ccftoptions_init' );
add_action('admin_menu', 'ccftoptions_add_page');

/* Init plugin options to white list our options */
function ccftoptions_init(){
	register_setting( 'ccftoptions_options', 'custom_comment_form_title', 'ccftoptions_validate' );
}

/* Add menu page */
function ccftoptions_add_page() {
	add_options_page('Custom Comment Form Title', 'Custom Comment Form Title', 'manage_options', 'ccftoptions', 'ccftoptions_do_page');
}

/* Draw the menu page itself */
function ccftoptions_do_page() {
	?>
	<div class="wrap" style="max-width:550px;">
		<h2><?php _e('Custom Comment Form Title', 'ccft-lang' ) ?></h2>
		<form method="post" action="options.php">
			<?php settings_fields('ccftoptions_options'); ?>
			<?php $options = get_option('custom_comment_form_title'); ?>

			<div class="form-element">
				<h3><?php _e('Default Comment Form Title', 'ccft-lang' ) ?></h3>
				<p><?php _e('Choose a default title to be used for all comment forms. This can be overridden for individual posts on the "Edit Post" screen.', 'ccft-lang' ) ?>
				<p><input type="text" size="50" name="custom_comment_form_title[default_title]" value="<?php echo $options['default_title']; ?>" />
                <input type="submit" class="button-primary" value="<?php _e('Save Changes', 'ccft-lang' ) ?>" />
		</form>
        
        <div style="clear:both;border-top:1px solid #CCC;margin:20px 0;"></div>
            <h4><?php _e('A note about Framework and Theme Compatibility:', 'ccft-lang' ) ?></h4>
            <p><?php _e('Some frameworks and themes replace the ', 'ccft-lang') ?><em>comment_form_defaults();</em><?php _e(' function with a new, custom function. This plugin is known to work with the following frameworks:', 'ccft-lang' ) ?>
            <ul>
                <li>&bull; Genesis</li>
                <li>&bull; Thematic</li>
            </ul>
            <p><?php _e('If this plugin does not work with your theme, head over to the forum and leave some information about your framework or theme. With your help, I can work on updating the plugin to work with a wider variety of frameworks and themes.', 'ccft-lang' ) ?>
            <h4><?php _e('A note about other comment system plugins:', 'ccft-lang' ) ?></h4>
            <p><?php _e('Some comment systems replace the Wordpress Comment Form all together. Special consideration must be taken to hook this plugin\'s custom titles back into the page before the new comment system. This plugin has been designed to work with the following comment systems:', 'ccft-lang' ) ?>
            <ul>
                <li>&bull; Disqus</li>
            </ul>
            <p><?php _e('If the commment system you use is not listed above, head over to the forum and leave some information about what you are using. With your help, I can work on updating the plugin to work with a wider variety of comment systems.', 'ccft-lang' ) ?>
        </div>
	</div>
	<?php	
}

/* Sanitize and validate input. Accepts an array, return a sanitized array. */
function ccftoptions_validate($input) {	

	/* Our default title must be safe text with no HTML tags */
	$input['default_title'] =  wp_filter_nohtml_kses($input['default_title']);
	
	return $input;
}

/* 
----------------------------
SETUP THE POST/PAGE META BOX
----------------------------
*/

add_action( 'add_meta_boxes', 'ccft_add_post_meta_boxes' );

// backwards compatible (before WP 3.0)
// add_action( 'admin_init', 'ccft_add_post_meta_boxes', 1 );

/* Do something with the data entered */
add_action( 'save_post', 'ccft_save_post_comment_title_meta' );

/* Adds a box to the main column on the Post and Page edit screens */
function ccft_add_post_meta_boxes() {
    $screens = array( 'post', 'page' );
    foreach ($screens as $screen) {
        add_meta_box(
            'ccft-post-comment-title',
            __( 'Custom Comment Form Title', 'ccft-lang' ),
            'ccft_inner_custom_box',
            $screen
        );
    }
}

/* Prints the box content */
function ccft_inner_custom_box( $post ) {

  // Use nonce for verification
  wp_nonce_field( plugin_basename( __FILE__ ), 'ccft_post_comment_title_nonce' );

  // The actual fields for data entry
  // Use get_post_meta to retrieve an existing value from the database and use the value for the form
  $value = get_post_meta( $post->ID, 'ccft_post_comment_title', true );
  echo '<input class="widefat" type="text" id="ccft-post-comment-title" name="ccft-post-comment-title" value="'.esc_attr($value).'" size="30" />';
}

/* When the post is saved, saves our custom data */
function ccft_save_post_comment_title_meta( $post_id ) {

  // First we need to check if the current user is authorised to do this action. 
  if( 'page' == $_POST['post_type'] ) {
    if( ! current_user_can( 'edit_page', $post_id ) )
        return;
  } else{
    if( ! current_user_can( 'edit_post', $post_id ) )
        return;
  }

  // Secondly we need to check if the user intended to change this value.
  if( ! isset( $_POST['ccft_post_comment_title_nonce'] ) || ! wp_verify_nonce( $_POST['ccft_post_comment_title_nonce'], plugin_basename( __FILE__ ) ) )
      return;

  // Thirdly we can save the value to the database

  //if saving in a custom table, get post_ID
  $post_ID = $_POST['post_ID'];
  //sanitize user input
  $mydata = sanitize_text_field( $_POST['ccft-post-comment-title'] );

  // Do something with $mydata 
  // either using 
  add_post_meta($post_ID, 'ccft_post_comment_title', $mydata, true) or
    update_post_meta($post_ID, 'ccft_post_comment_title', $mydata);
  // or a custom table (see Further Reading section below)
}

/*
----------------------------------------------------
SET THE NEW CUSTOM COMMENT FORM TITLE, IF ONE EXISTS
----------------------------------------------------
*/

/* Filter the post class hook with our custom post class function. */
add_filter( 'comment_form_defaults', 'ccft_post_comment_title' );

/* Framework filters */
$theme_data = wp_get_theme();
$template = $theme_data->Template;

// Genesis
if( $template = "genesis" ) {
	remove_filter( 'genesis_comment_form_args', 'custom_comment_form_args' );
	add_filter( 'genesis_comment_form_args', 'ccft_post_comment_title' );
}

// Thematic
if( $template = "thematic" ) {
	remove_filter( 'thematic_comment_form_args', 'custom_comment_form_args' );
	add_filter( 'thematic_comment_form_args', 'ccft_post_comment_title' );
}

function ccft_post_comment_title( $arg ) {

	$ccft_admin_options = get_option( 'custom_comment_form_title' );
	$default_title = esc_attr( $ccft_admin_options['default_title'] );
	$post_id = get_the_ID();
	
	if( !empty( $post_id ) ) {

		$post_comment_title = get_post_meta( $post_id, 'ccft_post_comment_title', true );
		if( !empty( $post_comment_title ) )
			$arg['title_reply'] = sanitize_text_field( $post_comment_title );
		elseif ( !empty( $default_title ) )
			$arg['title_reply'] = $default_title;
	}
	return $arg;
}

/*
----------------------------------------------------------------------
OUTPUT THE CUSTOM COMMENT FORM TITLE FOR NON-WORDPRESS COMMENT SYSTEMS
This includes Disqus (and hopefully more to come).
----------------------------------------------------------------------
*/

add_action( 'plugins_loaded', 'ccft_other_systems_check' );
function ccft_other_systems_check() {
	
	// Disqus
	if( function_exists( 'dsq_comments_template' ) ) {
		
		$disqus_active = get_option('disqus_active');
		if( $disqus_active == '1' ) {
			
			add_action( 'comments_template', 'ccft_comment_form_before', 0 );
			function ccft_comment_form_before() {
								
				$ccft_admin_options = get_option( 'custom_comment_form_title' );
				$default_title = esc_attr( $ccft_admin_options['default_title'] );
				$post_id = get_the_ID();
				
				if( !empty( $post_id ) ) {
					$post_comment_title = get_post_meta( $post_id, 'ccft_post_comment_title', true );
					if ( !empty( $post_comment_title ) )
						$post_comment_title_clean = sanitize_text_field( $post_comment_title );
				}
				
				if( !empty( $post_comment_title_clean ) )
					$post_comment_title_singular = $post_comment_title_clean;
				
				elseif ( !empty( $default_title ) )
					$post_comment_title_singular = $default_title;
					
				if( !empty( $post_comment_title_singular ) )
					echo '<div id="respond" class="comment-respond"><h3 id="reply-title" class="comment-reply-title">' . $post_comment_title_singular . '</h3></div>';
									
			}
							
		}
	
	}
	// end Disqus

}

?>