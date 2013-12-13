=== Custom Comment Form Title ===
Contributors: dwrippe
Tags: comments, comment form, comment form title
Requires at least: 3.5
Tested up to: 3.8
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Engage your visitors and initiate discussion with more meaningful comment form titles, created on a post-by-post basis!

== Description ==

The problem with the default comment form in Wordpress is that the title "Leave A Reply" doesn't really engage readers or encourage them to start a discussion or join the conversation. With the Custom Comment Form Title plugin, you can quickly change the default title to something more engaging, then set a custom comment form title on a post-by-post basis to <em>really</em> engage your readers. Ask a compelling question, make a bold statement, or leave an empty threat. Say whatever you want!

<strong>A note about Framework & Theme Compatibility:</strong>

Some frameworks and themes replace the <em>comment_form_defaults</em> function with a new, unique function. This plugin has been tested with, and is known to work with, the following frameworks:

<ul>
<li>Genesis</li>
<li>Thematic</li>
</ul>

If your framework isn't listed above, that doesn't mean the plugin won't work for you. Give it a try and find out. If it doesn't work, head over to the forum and leave some information about your framework or theme. With your help, I can work on updating the plugin to work with a wider variety of frameworks and themes.

<strong>A note about other comment system plugins:</strong>

Some comment systems (such as Disqus) replace the Wordpress Comment Form all together. This plugin simply updates variables within the built-in comment form, and if this form is removed and replaced with a completely different system, this plugin will no longer work.

== Installation ==

1. Download 'custom-comment-form-title.zip'
2. Extract the ZIP file and upload the 'custom-comment-form-title" folder to the '/wp-content/plugins/' directory
3. Activate the plugin through the 'Plugins' menu in Wordpress
4. Set the default comment form title in the 'Custom Comment Form Title' admin screen in the 'Settings' menu
5. Set post-specific comment form titles in the 'Create/Edit Post' page 

== Frequently Asked Questions ==

= My custom titles aren't displaying. =

Some frameworks overwrite the <em>comment_form_defaults</em> function with a new, unique function. Check the plugin settings page for a list of frameworks this plugin has been developed to work with.

= What if my framework isn't listed? =

Try the plugin and see if it works. Your framework may not replace the default <em>comment_form_defaults</em> function. If the plugin doesn't work, leave a comment in the forum and, with your help, I can work on updating the plugin to work with a wider variety of frameworks and themes.

= I'm not using the <em>comment_form()</em> function, can I modify my comments.php file so this plugin will work with my theme? =

Yes! Somewhere in your comments.php file you should see a line of code that looks similar to this:

`<?php comment_form_title( __('Leave a Reply'), __('Leave a Reply for %s') ); ?>`

If you replace that line of code with the follow snippet you should be able to use Custom Comment Form Titles with your website:

`<?php
	$post_id = get_the_ID();

	if ( !empty( $post_id ) ) {
		$arg = get_post_meta( $post_id, 'ccft_post_comment_title', true );
	}

	if ( empty( $arg ) ) {
		$ccft_admin_options = get_option( 'custom_comment_form_title' );
		$arg = esc_attr( $ccft_admin_options['default_title'] );
	}

	echo '<h3>' . $arg . '</h3>';
?>`

== Screenshots ==

1. The plugin settings page with a new default comment form title.
2. The new default comment form title in action.
3. The custom comment form title box on the Edit Post screen.
4. The new custom comment form title in action.

== Changelog ==

= 1.1 =
* Added custom Comment Form Title functionality to Pages (previously only available for Posts).
* Added additional FAQs

= 1.01 =
* Updated text in the readme.txt file to include a note about other comment system plugins.
* No functionality changes.

= 1.0 =
* Plugin release

== Upgrade Notice ==

= 1.1 =
* Custom Comment Form Title functionality is now available for Pages!

= 1.01 =
* Updated some text in the readme.txt file. No functionality changes.

= 1.0 =
Plugin release.