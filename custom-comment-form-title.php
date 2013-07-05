<?php
/*
Plugin Name: Custom Comment Form Title
Plugin URI: http://custom-comment-form-title.media-cairn.com/
Description: Create custom Comment Form Titles for individual posts.
Version: 1.0
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
            <h4><?php _e('A Note about Framework & Theme Compatibility:') ?></h4>
            <p><?php _e('Some frameworks and themes replace the <em>comment_form_defaults();</em> function with a new, custom function. This plugin is known to work with the following frameworks:') ?>
            <ul>
                <li>&bull; Genesis</li>
                <li>&bull; Thematic</li>
            </ul>
            <p><?php _e('If this plugin isn\'t working with your theme, head over to the forum and leave some information about your framework or theme. With your help, I can work on updating the plugin to work with a wider variety of frameworks and themes.') ?>
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

/* Setup the meta box setup function on the post editor screen. */
add_action( 'load-post.php', 'ccft_post_meta_boxes_setup' );
add_action( 'load-post-new.php', 'ccft_post_meta_boxes_setup' );

/* Meta box setup function. */
function ccft_post_meta_boxes_setup() {

	/* Add meta boxes on the 'add_meta_boxes' hook. */
	add_action( 'add_meta_boxes', 'ccft_add_post_meta_boxes' );

	/* Save post meta on the 'save_post' hook. */
	add_action( 'save_post', 'ccft_save_post_comment_title_meta', 10, 2 );
}

/* Create one or more meta boxes to be displayed on the post editor screen. */
function ccft_add_post_meta_boxes() {

	add_meta_box(
		'ccft-post-comment-title',						// Unique ID
		esc_html__( 'Custom Comment Form Title' ),		// Title
		'ccft_post_comment_title_meta_box',				// Callback function
		'post',											// Admin page (or post type)
		'normal',										// Context
		'default'										// Priority
	);
}

/* Display the post meta box. */
function ccft_post_comment_title_meta_box( $object, $box ) { ?>
	<?php wp_nonce_field( basename( __FILE__ ), 'ccft_post_comment_title_nonce' ); ?>
	<p><input class="widefat" type="text" name="ccft-post-comment-title" id="ccft-post-comment-title" value="<?php echo esc_attr( get_post_meta( $object->ID, 'ccft_post_comment_title', true ) ); ?>" size="30" /></p>
<?php }

/* Save the meta box's post metadata. */
function ccft_save_post_comment_title_meta( $post_id, $post ) {

	/* Verify the nonce before proceeding. */
	if ( !isset( $_POST['ccft_post_comment_title_nonce'] ) || !wp_verify_nonce( $_POST['ccft_post_comment_title_nonce'], basename( __FILE__ ) ) )
		return $post_id;

	/* Get the post type object. */
	$post_type = get_post_type_object( $post->post_type );

	/* Check if the current user has permission to edit the post. */
	if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
		return $post_id;

	/* Get the posted data and sanitize it for use as a text string. */
	$new_meta_value = ( isset( $_POST['ccft-post-comment-title'] ) ? sanitize_text_field( $_POST['ccft-post-comment-title'] ) : '' );

	/* Get the meta key. */
	$meta_key = 'ccft_post_comment_title';

	/* Get the meta value of the custom field key. */
	$meta_value = get_post_meta( $post_id, $meta_key, true );

	/* If a new meta value was added and there was no previous value, add it. */
	if ( $new_meta_value && '' == $meta_value )
		add_post_meta( $post_id, $meta_key, $new_meta_value, true );

	/* If the new meta value does not match the old value, update it. */
	elseif ( $new_meta_value && $new_meta_value != $meta_value )
		update_post_meta( $post_id, $meta_key, $new_meta_value );

	/* If there is no new meta value but an old value exists, delete it. */
	elseif ( '' == $new_meta_value && $meta_value )
		delete_post_meta( $post_id, $meta_key, $meta_value );
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