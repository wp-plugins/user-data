<?php
/**
 * Plugin Name: CC User Data
 * Plugin URI: http://circlecube.com
 * Description: Adds additional fields to each user profile and adds a template file for displaying a list of site authors.
 * Author: Evan Mullins
 * Author URI: http://circlecube.com
 * Version: 1.1.1
 */

// register with hook 'wp_print_styles'
add_action('wp_print_styles', 'add_cc_user_data_stylesheet');

// Register style-file, if it exists.
function add_cc_user_data_stylesheet() {
    $myStyleUrl = plugins_url('cc-user-data.css', __FILE__);
    $myStyleFile = WP_PLUGIN_DIR . '/cc-user-data/cc-user-data.css';
    if ( file_exists($myStyleFile) ) {
        wp_register_style('cc-user-data-css', $myStyleUrl, false, '0.1.1', 'all' );
		//enque css file
    	wp_enqueue_style( 'cc-user-data-css');
    }
}

function array_orderby() {
	$args = func_get_args();
	$data = array_shift($args);
	foreach ($args as $n => $field) {
		if (is_string($field)) {
			$tmp = array();
			foreach ($data as $key => $row)
				$tmp[$key] = $row[$field];
				$args[$n] = $tmp;
			}
	}
	$args[] = &$data;
	call_user_func_array('array_multisort', $args);
	return array_pop($args);
}

//tell wordpress to register the shortcode
// [cc-user-data-list]
add_shortcode("cc-user-data-list", "cc_user_data_list_handler");

function cc_user_data_list_handler( $atts ) {
	
	
	//run function that actually does the work of the plugin
	$cc_user_data = cc_user_data_function($atts);
	//send back text to replace shortcode in post
	return $cc_user_data;
}

function cc_user_data_function( $atts ) {
	extract( shortcode_atts( array(
      'show_thumbs' => 'true',
      'show_picture'  => 'true',
      'show_bio'  => 'true',
      'show_title'  => 'true',
      'show_email'  => 'true',
      'show_name'  => 'true',
      'show_posts'  => 'true',
      'show_social'  => 'true'
      ), $atts ) );
	  
	global $wpdb;
	

	//process plugin
	$cc_user_data_output = '';

	//getting author list (loop)
	$authors = $wpdb->get_results('SELECT DISTINCT id FROM wp_users');

	//sort
	$authors_ar = array();
	if($authors) {
		foreach($authors as $author) {
			$include = get_the_author_meta('wp_capabilities', $author->id);
			$sort_id = get_the_author_meta('sort', $author->id);
			if ($sort_id == "") { $sort_id = $author->id; }
			$author_ar = array(	'id' => $author->id, 
								'sort_id' => $sort_id,
								);
			// do not include users listed as subscribers
			if ( !$include['subscriber'] == 1 ) {
				array_push($authors_ar, $author_ar);
			}
		}
	}
	else {
		return "<p>No data from database!</p>";	
	}			
				
	$sorted_authors = array_orderby($authors_ar, 'sort_id');
	
	//print_r($sorted_authors);
	$i = 0;
	if($sorted_authors && $show_thumbs != 'false') {
		$cc_user_data_output .=  "<div class='authors_thumbs'>";
		foreach($sorted_authors as $author) {
			if($sorted_authors[$i][sort_id] >= 0 && get_the_author_meta('user_login', $sorted_authors[$i][id])) { 
				if ($show_name != 'false') {
					$name = get_the_author_meta('user_login', $sorted_authors[$i][id]);
				}
				$cc_user_data_output .=  "<a class='author_thumb " . str_replace('.', '-', $name) . "_thumb' href='#" . get_the_author_meta('user_login', $sorted_authors[$i][id]) . "'>";
				if ( get_the_author_meta('thumbnail', $sorted_authors[$i][id]) != "" ) {
					$cc_user_data_output .= "<img class='author_thumb_img' src='" . get_the_author_meta('thumbnail', $sorted_authors[$i][id]) . "' alt='" . get_the_author_meta('display_name', $sorted_authors[$i][id]) . "' />";
				}
				else {
                      	//display the author's avatar
						$cc_user_data_output .= get_avatar(get_the_author_meta('user_email', $sorted_authors[$i][id]));
                }
				
					if ($show_name != 'false') { $cc_user_data_output .=  "<div class='author_name'>". get_the_author_meta('display_name', $sorted_authors[$i][id]) . "</div>"; }
					if ($show_title != 'false') { $cc_user_data_output .=  "<div class='author_title'>". get_the_author_meta('title', $sorted_authors[$i][id]) . "</div>"; }
				$cc_user_data_output .=  "</a>";
            }
			$i++;
		}
	$cc_user_data_output .= '</div><br clear="both" />';
	}
    $i = 0;
	if($sorted_authors) {
		foreach($sorted_authors as $author) {
			//$sorted_authors[$i][id];			
			if($sorted_authors[$i][sort_id] >= 0 && get_the_author_meta('user_login', $sorted_authors[$i][id])) {
				$cc_user_data_output .= "<div class='author_info author_sort_" . get_the_author_meta('sort', $sorted_authors[$i][id]) . "' id='" . get_the_author_meta('user_login', $sorted_authors[$i][id]) . "'>";
				
					if ($show_name != 'false') { $cc_user_data_output .=  "<h2 class='author_name'>" . get_the_author_meta('display_name', $sorted_authors[$i][id]) . "</h2>"; }
                    if ($show_title != 'false') { $cc_user_data_output .=  "<h3 class='author_title'>" . get_the_author_meta('title', $sorted_authors[$i][id]) . "</h3>"; }
                                 
                    if ( get_the_author_meta('picture', $sorted_authors[$i][id]) != "" && $show_picture != 'false') { 
    	           		$cc_user_data_output .= "<img src='" . get_the_author_meta('picture', $sorted_authors[$i][id]) ."' alt='" . get_the_author_meta('display_name', $sorted_authors[$i][id]) . "' class='author_picture' />";
                    }
                    elseif ( get_the_author_meta('picture', $sorted_authors[$i][id]) == "" ) { 
                      	//display the author's avatar
						$cc_user_data_output .= get_avatar(get_the_author_meta('user_email', $sorted_authors[$i][id]));
                    }

                    if ( get_the_author_meta('htmlbio', $sorted_authors[$i][id]) && $show_bio != 'false' ) {
                    	$cc_user_data_output .= "<div class='author_bio'>" . apply_filters('archive_meta', get_the_author_meta('htmlbio', $sorted_authors[$i][id])) . "</div>";		
					}
                    if ($show_email != 'false') { $cc_user_data_output .=  "<p><a class='author_email' title='" . antispambot(get_the_author_meta('user_email', $sorted_authors[$i][id])) . "' href='mailto:" . antispambot(get_the_author_meta('user_email', $sorted_authors[$i][id])) . "'>Email: " . get_the_author_meta('display_name', $sorted_authors[$i][id]) . "</a></p>"; }
					
                    if ($show_social != 'false' &&
					get_the_author_meta('twitter', $sorted_authors[$i][id]) != "" ||
					get_the_author_meta('facebook', $sorted_authors[$i][id]) != "" ||
					get_the_author_meta('gootle', $sorted_authors[$i][id]) != "" ||
					get_the_author_meta('linkedin', $sorted_authors[$i][id]) != "" ||
					get_the_author_meta('youtube', $sorted_authors[$i][id]) != "" ) { 
						$cc_user_data_output .=  "<div id='author-social'>Social Media Links for " . get_the_author_meta('first_name', $sorted_authors[$i][id]) . ":<ul>";
						if (get_the_author_meta('twitter', $sorted_authors[$i][id]) != ""){
							list($http, $null, $domain, $hashbang, $username) = explode("/", get_the_author_meta('twitter', $sorted_authors[$i][id]));
							$cc_user_data_output .=  "<li><a target='_blank' rel='nofollow' href='" . get_the_author_meta('twitter', $sorted_authors[$i][id]) . "' class='author_twitter'>@" . $username . "</a></li>";
						}
						if (get_the_author_meta('facebook', $sorted_authors[$i][id]) != ""){
							$cc_user_data_output .=  "<li><a target='_blank' rel='nofollow' href='" . get_the_author_meta('facebook', $sorted_authors[$i][id]) . "' class='author_facebook'>Facebook</a></li>";
						}
						if (get_the_author_meta('google', $sorted_authors[$i][id]) != ""){
							$cc_user_data_output .=  "<li><a target='_blank' rel='nofollow' href='" . get_the_author_meta('google', $sorted_authors[$i][id]) . "' class='author_google'>Google+</a></li>";
						}
						if (get_the_author_meta('linkedin', $sorted_authors[$i][id]) != ""){
							$cc_user_data_output .=  "<li><a target='_blank' rel='nofollow' href='" . get_the_author_meta('linkedin', $sorted_authors[$i][id]) . "' class='author_linkedin'>LinkedIn</a></li>";
						}
						if (get_the_author_meta('youtube', $sorted_authors[$i][id]) != ""){
							$cc_user_data_output .=  "<li><a target='_blank' rel='nofollow' href='" . get_the_author_meta('youtube', $sorted_authors[$i][id]) . "' class='author_youtube'>Youtube</a></li>";
						}
						$cc_user_data_output .=  "</ul></div>";
					}
                                
                    $recentPost = new WP_Query('author='.$sorted_authors[$i][id].'&showposts=3');
                    if ($recentPost->have_posts() && $show_posts != 'false'){
                        $cc_user_data_output .= "<div class='author-recent-posts'><div>Recent Posts by <a href='" . get_author_posts_url(get_the_author_meta('id', $sorted_authors[$i][id])) ."' title='" . __('View all posts by ', 'thematic') . get_the_author_meta('display_name', $sorted_authors[$i][id]) ."'>" . get_the_author_meta('display_name', $sorted_authors[$i][id]) . "</a>: <ul>"; 
                        while($recentPost->have_posts()) {
                            $recentPost->the_post();
                            $cc_user_data_output .= "<li><a href='" . get_permalink() . "'>" . get_the_title() ."</a></li>";
                        }
                        $cc_user_data_output .= "</ul></div></div>";
                    } 
					$cc_user_data_output .= "</div><!-- #author-info -->";
				}
				$i++;
            } 
		}
		else { 
			return "<p>No sorted details!</p>";
		}
	//send back text to calling function
	return $cc_user_data_output;
}



/////////////////////////////////////////////////////////////////////////////////
// Author / user profile - filters and extra fields
//

//remove formatting the description for the user bios.
//remove_filter('pre_user_description', 'wp_filter_kses');

//adding extra fields to user bio
add_action( 'show_user_profile', 'cc_user_data_add_user_fields' );
add_action( 'edit_user_profile', 'cc_user_data_add_user_fields' );
function cc_user_data_add_user_fields( $user ) { ?>

	<h3>Extra User Data</h3>

	<table class="form-table">

		<tr>
			<th><label for="htmlbio">HTML Bio</label></th>

			<td>
				<textarea name="htmlbio" id="htmlbio" cols="30" rows="10"><?php echo esc_attr( get_the_author_meta( 'htmlbio', $user->ID ) ); ?></textarea><br />
				<span class="description">Please enter your bio (html supported).</span>
			</td>
		</tr>

		<tr>
			<th><label for="title">Title</label></th>

			<td>
				<input type="text" name="title" id="title" value="<?php echo esc_attr( get_the_author_meta( 'title', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">Please enter your job title.</span>
			</td>
		</tr>

		<tr>
			<th><label for="phone">Phone Number</label></th>

			<td>
				<input type="text" name="phone" id="phone" value="<?php echo esc_attr( get_the_author_meta( 'phone', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">Please enter your phone number. (ex: 404.523.2606)</span>
			</td>
		</tr>

		<tr>
			<th><label for="hire-date">Start Date</label></th>

			<td>
				<input type="text" name="hire-date" id="hire-date" value="<?php echo esc_attr( get_the_author_meta( 'hire-date', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">Please enter your hire date or birth date. (ex: MM/DD/YYYY 05/27/2011)</span>
			</td>
		</tr>

		<tr>
			<th><label for="skills">Skills/Interests</label></th>

			<td>
				<textarea name="skills" id="skills" cols="30" rows="2"><?php echo esc_attr( get_the_author_meta( 'skills', $user->ID ) ); ?></textarea><br />
				<span class="description">Please enter your skills or interests in a comma separated list.</span>
			</td>
		</tr>

		<tr>
			<th><label for="sort">Sort Order</label></th>

			<td>
				<input type="text" name="sort" id="sort" value="<?php echo esc_attr( get_the_author_meta( 'sort', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">Please enter your sorting order, this is the order you show up on the team page.</span>
			</td>
		</tr>
        
        <tr>
			<th><label for="upload_image">Select Profile Image</label></th>

			<td>
            	<input id="upload_image" name="upload_image" type="text" value="<?php echo esc_attr( get_the_author_meta( 'picture', $user->ID ) ); ?>" />
                <input id="upload_image_button" class="upload_image_button" value="Get image" type="button" onclick="cc_user_data_media_popup_handler();" />
				<div class="description">Enter picture url or click the button to upload/select from media gallery, then hit insert button. If no image specified your avatar will be used.</div>
   			</td>
       </tr>
        
        <tr>
			<th><label for="upload_image_thumb">Select Thumbnail Image</label></th>

			<td>
            	<input id="upload_image_thumb" name="upload_image_thumb" type="text" width="300" value="<?php echo esc_attr( get_the_author_meta( 'thumbnail', $user->ID ) ); ?>" />
                <input id="upload_image_thumb_button" class="upload_image_button" value="Get image" type="button" onclick="cc_user_data_media_popup_handler();" />
				<div class="description">Enter thumbnail url or click the button to upload/select from media gallery, then hit insert button. If no image specified your avatar will be used.</div>
   			</td>
       </tr>
  
<script type="text/javascript">
// Deals with calling the WordPress Media popup box
function cc_user_data_media_popup_handler() {
	jQuery(document).ready(function() {
		jQuery('.upload_image_button').click(function() {
			//set id for this to prev element (the field before the button).
			formfield = jQuery(this).prev().attr('id');
			tb_show('', '<?php echo admin_url(); ?>media-upload.php?type=image&TB_iframe=1&width=640&height=290');
			return false;
		});
		window.send_to_editor = function(html) {
			imgurl = jQuery('img',html).attr('src');
			jQuery("#"+formfield).val(imgurl);
			tb_remove();
		}
	});
}
</script>
	</table>
    <h3>Extra User Data: Social Media Links</h3>

	<table class="form-table">

		<tr>
			<th><label for="twitter">Twitter</label></th>

			<td>
				<input type="text" name="twitter" id="twitter" value="<?php echo esc_attr( get_the_author_meta( 'twitter', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">Please enter your twitter url. (ex: http://twitter.com/#!/thejonesgrp)</span>
			</td>
		</tr>

		<tr>
			<th><label for="linkedin">LinkedIn</label></th>

			<td>
				<input type="text" name="linkedin" id="linkedin" value="<?php echo esc_attr( get_the_author_meta( 'linkedin', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">Please enter your linkedIn url. (ex: http://www.linkedin.com/company/the-jones-group)</span>
			</td>
		</tr>

		<tr>
			<th><label for="facebook">Facebook</label></th>

			<td>
				<input type="text" name="facebook" id="facebook" value="<?php echo esc_attr( get_the_author_meta( 'facebook', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">Please enter your facebook url. (ex: http://www.facebook.com/pages/The-Jones-Group/80777356435)</span>
			</td>
		</tr>

		<tr>
			<th><label for="youtube">YouTube</label></th>

			<td>
				<input type="text" name="youtube" id="youtube" value="<?php echo esc_attr( get_the_author_meta( 'youtube', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">Please enter your youtube url. (ex: http://www.youtube.com/jonesgroup97)</span>
			</td>
		</tr>

		<tr>
			<th><label for="google">Google+</label></th>

			<td>
				<input type="text" name="google" id="google" value="<?php echo esc_attr( get_the_author_meta( 'google', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">Please enter your google+ url. (ex: https://plus.google.com/u/0/101287711820309204128/)</span>
			</td>
		</tr>
        
    </table>
<?php }
add_action( 'personal_options_update', 'cc_user_data_save_user_fields' );
add_action( 'edit_user_profile_update', 'cc_user_data_save_user_fields' );

function cc_user_data_save_user_fields( $user_id ) {

	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;

	/* Copy and paste this line for additional fields. Make sure to change 'twitter' to the field ID. */
	update_usermeta( $user_id, 'title', $_POST['title'] );
	update_usermeta( $user_id, 'htmlbio', $_POST['htmlbio'] );
	update_usermeta( $user_id, 'sort', $_POST['sort'] );
	update_usermeta( $user_id, 'skills', $_POST['skills'] );
	update_usermeta( $user_id, 'phone', $_POST['phone'] );
	update_usermeta( $user_id, 'hire-date', $_POST['hire-date'] );
	update_usermeta( $user_id, 'picture', $_POST['upload_image'] );
	update_usermeta( $user_id, 'thumbnail', $_POST['upload_image_thumb'] );
	update_usermeta( $user_id, 'twitter', $_POST['twitter'] );
	update_usermeta( $user_id, 'facebook', $_POST['facebook'] );
	update_usermeta( $user_id, 'linkedin', $_POST['linkedin'] );
	update_usermeta( $user_id, 'google', $_POST['google'] );
	update_usermeta( $user_id, 'youtube', $_POST['youtube'] );
}

function cc_user_data_admin_scripts() {
	wp_enqueue_script('media-upload');
	wp_enqueue_script('thickbox');
}

function cc_user_data_admin_styles() {
	wp_enqueue_style('thickbox');
}

//if (isset($_GET['page']) && $_GET['page'] == 'my_plugin_page') {
add_action('admin_print_scripts', 'cc_user_data_admin_scripts');
add_action('admin_print_styles', 'cc_user_data_admin_styles');
//}


//http://scribu.net/wordpress/custom-sortable-columns.html
// Register the column
function cc_user_data_sort_column_register( $columns ) {
	$columns['sort'] = 'Sort';
	return $columns;
}
add_filter( 'manage_users_columns', 'cc_user_data_sort_column_register', 15, 1 );
// Display the column content
function cc_user_data_sort_column_display($value, $column_name, $id ) {
	if ( 'sort' != $column_name )
		return;
 
	$sort = get_usermeta($id, 'sort');
	if ( !$sort )
		$sort = '<em>' . 'undefined' . '</em>';
 
	return $sort;
}
add_action( 'manage_users_custom_column', 'cc_user_data_sort_column_display', 15, 3 );

// Register the column as sortable
function cc_user_data_sort_column_register_sortable( $columns ) {
	$columns['sort'] = 'sort';
	return $columns;
}
add_filter( 'manage_users_sortable_columns', 'cc_user_data_sort_column_register_sortable' );

//sort the column
function cc_user_data_sort_column_orderby( $vars ) {
	if ( isset( $vars['orderby'] ) && 'sort' == $vars['orderby'] ) {
		$vars = array_merge( $vars, array(
			'meta_key' => 'sort',
			'orderby' => 'meta_value_num'
		) );
	}
	return $vars;
}
add_filter( 'request', 'cc_user_data_sort_column_orderby' );


// Add function to widgets_init that'll load our widget
add_action( 'widgets_init', 'cc_user_data_load_widgets' );

// Register our widget
function cc_user_data_load_widgets() {
	register_widget( 'User_Data_Widget' );
}

// User Data Widget class
// This class handles everything that needs to be handled with the widget:the settings, form, display, and update.

class User_Data_Widget extends WP_Widget {

	// Widget setup.
	function User_Data_Widget() {
		// Widget settings
		$widget_ops = array( 'classname' => 'cc_user_data_widget', 'description' => __('A widget that displays a random user profile.') );

		// Widget control settings
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'cc_user_data_widget' );

		// Create the widget
		$this->WP_Widget( 'cc_user_data_widget', __('User Data Widget'), $widget_ops, $control_ops );
	}

	// How to display the widget on the screen
	function widget( $args, $instance ) {
		extract( $args );
		
		// Our variables from the widget settings
		$title = apply_filters('widget_title', $instance['title'] );
		$list_url = $instance['list_url'];
		$show_title = isset( $instance['show_title'] ) ? $instance['show_title'] : false;
		$show_name = isset( $instance['show_name'] ) ? $instance['show_name'] : false;
		$show_thumbnail = isset( $instance['show_thumbnail'] ) ? $instance['show_thumbnail'] : false;
		
		// Before widget (defined by themes)
		echo $before_widget;
		
		// Display the widget title if one was input (before and after defined by themes)
		if ( $title )
			echo $before_title . $title . $after_title;
		
		global $wpdb;
		
		//process plugin
		$cc_user_data_output = '';
		$total_authors = 0;
		//getting author list (loop)
		$authors = $wpdb->get_results('SELECT DISTINCT id FROM wp_users');
	
		//sort
		$authors_ar = array();
		if($authors) {
			foreach($authors as $author) {
				$include = get_the_author_meta('wp_capabilities', $author->id);
				$sort_id = get_the_author_meta('sort', $author->id);
				if ($sort_id == "") { $sort_id = $author->id; }
				$author_ar = array(	'id' => $author->id, 
									'sort_id' => $sort_id,
									);
				// do not include users listed as subscribers
				if ( !$include['subscriber'] == 1 ) {
					array_push($authors_ar, $author_ar);
					$total_authors++;
				}
			}
		}
		else {
			return "<p>No data from database!</p>";	
		}
						
					
		$sorted_authors = array_orderby($authors_ar, 'sort_id');
		
		$i = rand(0, $total_authors);
		//echo "<p>total authors: " . $total_authors . "random value: " . $i . "</p>";
		if($sorted_authors) {
			$cc_user_data_output .=  "<div class='authors_spotlight'>";
			$author = $sorted_authors[$i];
			//echo "<p>" . $author[id] . " " . $author[sort_id] . " " . get_the_author_meta('user_login', $author[id]) . "</p>";
			if($author[sort_id] >= 0 && get_the_author_meta('user_login', $author[id])) { 	
				$display_name = get_the_author_meta('display_name', $author[id]);	
				$thumbnail = get_the_author_meta('thumbnail', $author[id]);	
				$name = get_the_author_meta('user_login', $author[id]);	
				$title = get_the_author_meta('title', $author[id]);	
				$display_name = get_the_author_meta('display_name', $author[id]);
					
				if ($show_thumbnail) { $cc_user_data_output .=  "<a class='" . str_replace('.', '-', $name) . "_thumb' href='/" . $list_url ."#" . $name . "'><img src='" . $thumbnail . "' alt='" . $display_name . "' /></a>"; 
				}
				if ($show_name) { $cc_user_data_output .=  "<div class='author_name'>Hello, my name is <a href='/" . $list_url ."#" . $name . "'>". $display_name . "</a>!</div>"; }
				if ($show_title) { $cc_user_data_output .=  "<div class='author_title'>I'm ". $title . " at " . get_bloginfo() . ".</div>"; }
			}		
		$cc_user_data_output .= '</div>';
		}
		//send back text to calling function
		echo  $cc_user_data_output;
	
		// After widget (defined by themes)
		echo $after_widget;
	}

	// Update the widget settings.
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		// Strip tags for title and name to remove HTML (important for text inputs).
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['list_url'] = strip_tags( $new_instance['list_url'] );

		// No need to strip tags for sex and show_sex.
		$instance['show_title'] = $new_instance['show_title'];
		$instance['show_thumbnail'] = $new_instance['show_thumbnail'];
		$instance['show_name'] = $new_instance['show_name'];

		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => __('User Spotlight'), 'list_url' => __('author'), 'show_title' => true, 'show_thumbnail' => true, 'show_name' => true );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>

		<!-- URL: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'list_url' ); ?>"><?php _e('Page slug (with plugin shortcode) to link spotlight to :'); ?></label>
			<input id="<?php echo $this->get_field_id( 'list_url' ); ?>" name="<?php echo $this->get_field_name( 'list_url' ); ?>" value="<?php echo $instance['list_url']; ?>" style="width:100%;" />
		</p>

		<!-- Show Title? Checkbox -->
		<p>
			<input class="checkbox" type="checkbox" <?php if( $instance['show_title'] == true) { echo 'checked'; } ?> id="<?php echo $this->get_field_id( 'show_title' ); ?>" name="<?php echo $this->get_field_name( 'show_title' ); ?>" /> 
			<label for="<?php echo $this->get_field_id( 'show_title' ); ?>"><?php _e('Display title?'); ?></label>
		</p>

		<!-- Show Thumbnail? Checkbox -->
		<p>
			<input class="checkbox" type="checkbox" <?php if( $instance['show_thumbnail'] == true) { echo 'checked'; } ?> id="<?php echo $this->get_field_id( 'show_thumbnail' ); ?>" name="<?php echo $this->get_field_name( 'show_thumbnail' ); ?>" /> 
			<label for="<?php echo $this->get_field_id( 'show_thumbnail' ); ?>"><?php _e('Display thumbnail?'); ?></label>
		</p>

		<!-- Show Name? Checkbox -->
		<p>
			<input class="checkbox" type="checkbox" <?php if( $instance['show_name'] == true) { echo 'checked'; } ?> id="<?php echo $this->get_field_id( 'show_name' ); ?>" name="<?php echo $this->get_field_name( 'show_name' ); ?>" /> 
			<label for="<?php echo $this->get_field_id( 'show_name' ); ?>"><?php _e('Display name?'); ?></label>
		</p>

	<?php
	}
}

?>