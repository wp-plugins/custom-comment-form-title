<?php
/*
Plugin Name: Custom Comment Form Title
Plugin URI: http://custom-comment-form-title.media-cairn.com/
Description: Create custom Comment Form Titles for individual posts.
Version: 1.1
Author: MediaCairn Design Studio
Author URI: http://www.media-cairn.com/
License: GPLv2 or later
*/

/*  Copyright 2013 MediaCairn Design Studio

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

/* Setup the Options screen */
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
		<h2>Custom Comment Form Title</h2>
		<form method="post" action="options.php">
			<?php settings_fields('ccftoptions_options'); ?>
			<?php $options = get_option('custom_comment_form_title'); ?>

			<div class="form-element">
				<h3><?php _e('Default Comment Form Title') ?></h3>
				<p><?php _e('Choose a default title to be used for all comment forms. This can be overridden for individual posts on the "Edit Post" screen.') ?>
				<p><input type="text" size="50" name="custom_comment_form_title[default_title]" value="<?php echo $options['default_title']; ?>" />
                <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</form>
        
        <div style="clear:both;border-top:1px solid #CCC;margin:20px 0;"></div>
            <h4><?php _e('A note about Framework & Theme Compatibility:') ?></h4>
            <p><?php _e('Some frameworks and themes replace the <em>comment_form_defaults();</em> function with a new, custom function. This plugin is known to work with the following frameworks:') ?>
            <ul>
                <li>&bull; Genesis</li>
                <li>&bull; Thematic</li>
            </ul>
            <p><?php _e('If this plugin isn\'t working with your theme, head over to the forum and leave some information about your framework or theme. With your help, I can work on updating the plugin to work with a wider variety of frameworks and themes.') ?>
            <h4><?php _e('A note about other comment system plugins:') ?></h4>
            <p><?php _e('Some comment systems (such as Disqus) replace the Wordpress Comment Form all together. This plugin simply updates variables within the built-in comment form, and if this form is removed and replaced with a completely different system, this plugin will no longer work.') ?>
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

/* Filter the comment form defaults hook with our custom comment form default title function. */
add_filter( 'comment_form_defaults', 'ccft_default_title' );

/* Framework filters */
$theme_data = wp_get_theme();
$template = $theme_data->Template;

if( $template = "genesis" ) {
	remove_filter( 'genesis_comment_form_args', 'custom_comment_form_args' );
	add_filter( 'genesis_comment_form_args', 'ccft_default_title' );
}
if( $template = "thematic" ) {
	remove_filter( 'thematic_comment_form_args', 'custom_comment_form_args' );
	add_filter( 'thematic_comment_form_args', 'ccft_default_title' );
}

function ccft_default_title( $arg ) {
	$ccft_admin_options = get_option( 'custom_comment_form_title' );
	$default_title = esc_attr( $ccft_admin_options['default_title'] );

	if ( !empty( $default_title ) ) {
		$arg['title_reply'] = $default_title;
	}
	return $arg;
}

/* Define the custom box */

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
            __( 'Custom Comment Form Title', 'ccft_textdomain' ),
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
  if ( 'page' == $_POST['post_type'] ) {
    if ( ! current_user_can( 'edit_page', $post_id ) )
        return;
  } else {
    if ( ! current_user_can( 'edit_post', $post_id ) )
        return;
  }

  // Secondly we need to check if the user intended to change this value.
  if ( ! isset( $_POST['ccft_post_comment_title_nonce'] ) || ! wp_verify_nonce( $_POST['ccft_post_comment_title_nonce'], plugin_basename( __FILE__ ) ) )
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

/* Filter the post class hook with our custom post class function. */
add_filter( 'comment_form_defaults', 'ccft_post_comment_title' );

/* Framework filters */
$theme_data = wp_get_theme();
$template = $theme_data->Template;
if( $template = "genesis" ) {
	remove_filter( 'genesis_comment_form_args', 'custom_comment_form_args' );
	add_filter( 'genesis_comment_form_args', 'ccft_post_comment_title' );
}
if( $template = "thematic" ) {
	remove_filter( 'thematic_comment_form_args', 'custom_comment_form_args' );
	add_filter( 'thematic_comment_form_args', 'ccft_post_comment_title' );
}

function ccft_post_comment_title( $arg ) {

	/* Get the current post ID. */
	$post_id = get_the_ID();

	/* If we have a post ID, proceed. */
	if ( !empty( $post_id ) ) {

		/* Get the custom post class. */
		$post_comment_title = get_post_meta( $post_id, 'ccft_post_comment_title', true );

		/* If a post class was input, sanitize it and add it to the post class array. */
		if ( !empty( $post_comment_title ) )
			$arg['title_reply'] = sanitize_text_field( $post_comment_title );
	}
	return $arg;
}

?>