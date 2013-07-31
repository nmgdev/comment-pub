<?php
/*
Plugin Name: Comment Pub
Plugin URI: http://commentpub.com/
Description: Allow anyone to upload a PNG, GIF, JPG, and JPEG image to their comment and resize it, without having to sign up for other services. Create a guestbook or local avatars or make image unique comments. Supports gravatar and replace gravatar when image is uploaded.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="options-general.php?page=comment_pub_options">Settings Page</a>&nbsp;|&nbsp;<a href="http://commentpub.com/">Support</a>
Version: 1.0.0
Author: nowmediagroup 
Author URI: http://nowmediagroup.tv/
Author Email: andy@nowmediagroup.tv
Text Domain: comment-pub
*/

/*  Copyright 2012  Comment Pub  (email : info@nowmediagroup.tv)

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



class nmg_comment_pub {

	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/
	
	/**
	 * Initializes the plugin by setting localization, admin styles, and content filters.
	 */
	function __construct() {
	
		load_plugin_textdomain( 'comment-pub', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
	
		// Determine if the hosting environment can save files.
		if( $this->can_save_files() ) {
	
			// Add comment related stylesheets, scripts, form manipulation, and image serialization
			add_action( 'wp_enqueue_scripts', array( &$this, 'add_styles' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'add_admin_styles' ) );
			add_action( 'wp_enqueue_scripts', array( &$this, 'add_scripts' ) );
			
			add_filter( 'wp_insert_comment', array( &$this, 'save_comment_pub' ) );
			add_filter( 'comments_array', array( &$this, 'display_comment_pub' ) );
			
			$options = get_option( 'nmg_comment_pub' );
			
			// Check if they have decided to use uploaded images as local avatars
			if( $options[4] == 'active' ){
				add_filter( 'comments_template', array( &$this, 'comment_pub_comments' ) );
			}
			
			// If the stand in email is not empty then remove email input from form
			if( !empty( $options[2] ) ){
				add_filter( 'comment_form_field_email',  array( &$this, 'change_email') );
			}
			
			// if we can make folder run this
			// We need this folder to move the uploaded files here
			$uploads_dir = wp_upload_dir();
			
			if ( wp_mkdir_p($uploads_dir['basedir'].'/comment_pub' ) ) {
				wp_mkdir_p( $uploads_dir['basedir'].'/comment_pub' );
			}else{
				add_action( 'admin_notices', array( &$this, 'folder_error_notice' ) );	
			}
			
			/*
			* Check whether they have chosen to use comment pub as a guestbook
			*/
			if( $options[5] == 'active' ){
				
				/*
				* If its active then make a copy of comment pub page template 
				* and add it to the active theme.
				*/
				$current_foler = dirname( __FILE__ );
				
				if( $theme_path = get_template_directory() ){
					copy( $current_foler.'/comment_pub_page.php' , $theme_path.'/comment_pub_page.php' );
				}
				 
			}else{
				
				/*
				* Other wise we are going to attach comment pub uploader to all comment forms
				*/
				add_action( 'comment_form_after_fields' , array( &$this, 'add_image_upload_form' ) );
				
				/*
				* Other wise we are going to attach comment pub uploader to all comment forms
				* This only appear on admin
				*/
				add_action( 'comment_form_logged_in_after' , array( &$this, 'add_image_upload_form' ) );
				
				//This is a temporary fix until we know why image upload wont work
				//add_action( 'comment_form' , array( &$this, 'add_image_upload_form' ) );
				
				/*
				* we are going to add a link back to Comment_pub if we can
				*/
				add_action( 'comment_form' , array( &$this, 'love_link' ) );
				
				
			}
			
		// If we can't save files then show this warning to the admin
		} else {
		
			add_action( 'admin_notices', array( &$this, 'save_error_notice' ) );
			
		} // end if/else

	} // end constructor
	
	/*--------------------------------------------*
	 * Core Functions
	 *---------------------------------------------*/
	 /*
	  * Display a WordPress error to the administrator if the hosting environment does not support 'file_get_contents.'
	  */
	 function save_error_notice() {
		 
		 $html = '<div id="comment-pub-notice" class="error">';
		 	$html .= '<p>';
		 		$html .= __( '<strong>Comment Pub Notice:</strong> Unfortunately, your host does not allow uploads from the comment form. This plugin will not work for your host.', 'nmg_comment_pub' );
		 	$html .= '</p>';
		 $html .= '</div><!-- /#comment-pub-notice -->';
		 
		 echo $html;
		 
	 } // end save_error_notice
	 
	 /**
	  * Display a WordPress error to the administrator if the hosting environment does not allow a plug-in to create a folder
	  */
	 function folder_error_notice() {
		 
		 $html = '<div id="comment-pub-notice" class="error">';
		 	$html .= '<p>';
		 		$html .= __( '<strong>Comment Pub Notice:</strong> Unfortunately, your host does not allow a plugin to create a folder. Please Create "comment_pub" manually. ', 'nmg_comment_pub' );
		 	$html .= '</p>';
		 $html .= '</div><!-- /#comment-pub-notice -->';
		 
		 echo $html;
		 
	 } // end save_error_notice
	 
	 /**
	  * Adds the public stylesheet to the single post page.
	  */
	 function add_styles() {
	
		if( is_single() || is_page() ) {
			
			wp_register_style( 'nmg_comment_pub_css', plugins_url( '/comment-pub/css/plugin.css' ) );
			wp_enqueue_style( 'nmg_comment_pub_css' );
			
		} // end if
		
	} // end add_scripts
	 
	 /**
	  * Adds the public stylesheet to the single post page.
	  */
	 function add_admin_styles() {
			
			wp_register_style( 'nmg_comment_pub_admin_css', plugins_url( '/comment-pub/css/admin.css' ) );
			wp_enqueue_style( 'nmg_comment_pub_admin_css' );
		
	} // end add_scripts
	 
	/**
	 * Adds the public JavaScript to the single post page.
	 */ 
	function add_scripts() {
	
		if( is_single() || is_page() ) {
			
			wp_register_script( 'nmg_comment_pub_js', plugins_url( '/comment-pub/js/plugin.min.js' ), array( 'jquery' ) );
			wp_enqueue_script( 'nmg_comment_pub_js' );
			
		} // end if
		
	} // end add_scripts
	
	 
	/*
	 * this function will run when we know that comment pub is being added as local gravatars
	 * before we do anything like that we need to make sure that this is a singular page / post
	 * is_single, is_page or is_attachment but more importantly we need to make sure 
	 * that comments are open  for that page / post
	 *
	 * @return comment form using Comment Pub images as local gravatars
	 */
	function comment_pub_comments(){
		
		 global $post;
		 
		
		 // If comments are open, we have comments and its post then we should give admin's a warning
		 
		if ( is_singular() && ( have_comments() || $post->comment_status == 'open' ) ) {
			return dirname(__FILE__) . '/comments_pub_comments.php';
		 }elseif ( ! comments_open() && ! is_page() && post_type_supports( get_post_type(), 'comments' ) ) {
		?>
			<p class="nocomments"><?php _e( 'Comments are closed.', 'comment_pub' ); ?></p>
		<?php } 
		
	} // end comment_pub_comments
	
	
	/*
	 * Comment Form Email input Replacement
	 * This is going to check if there is a stand in e-mail
	 * if there is a stand-in email we are going to remove the e-mail section of the comment form
	 *
	 * @return hidden input in Comment Form
	 *
	 */
	function change_email(){
				
		// get wp database values stored under nmg_comment_pub
		$options = get_option( 'nmg_comment_pub' );
		//If the form was ever filled in get the old values into the form
		$stndEmail = $options[2];
		
		$new_email_inp .= '<div class="comment-form-email">';
		$new_email_inp .= '<input id="email" name="email" type="hidden" value="'.$stndEmail.'" size="30" tabindex="2" />';	
		$new_email_inp .= '<div class="clear"></div></div>';
		
		return $new_email_inp;
		
	}// end change_email
			
	/**
	 * Adds the comment image upload form to the comment form.
	 *
	 * @param	$post		The post information 
	 * @echo 				Upload form attached to Comment Form
	 *
	 */
 	public function add_image_upload_form( $post ) {
	 	// Create the label and the input field for uploading an image
		$options = get_option( 'nmg_comment_pub' );
		global $post;
	 	
	 	$html = '<div id="comment-pub-wrapper">';
		 	$html .= '<p id="comment-pub-error">';
		 		$html .= __( '<strong>Heads up!</strong> You are attempting to upload an invalid image. If saved, this image will not display with your comment.', 'nmg_comment_pub' );
		 	$html .= '</p>';
			 $html .= "<label for='comment_pub_$post->ID'>";
			 	$html .= __( 'Select an image for your comment (GIF, PNG, JPG, JPEG), <br />images are automatically resized to '.$options[0] .'x'. $options[1], 'nmg_comment_pub' );
			 $html .= "</label>";
			 $html .= "<input type='file' name='comment_pub_$post->ID' id='comment_pub' />";
		 $html .= '</div><!-- #comment-pub-wrapper -->';

		 echo $html;
		 
	} // end add_image_upload_form
	
	/*
	* This will add a link back to our site.
	*/
 	public function love_link( $post ) {
	 	// Create the label and the input field for uploading an image
		$options = get_option( 'nmg_comment_pub' );
		
		 if( $options[6] == 1 ){
		 	$html = '<a class="loveLink" href="http://commentpub.com" target="_blank">Comment Pub</a>';
		 }else{
			 $html = '';
		 }

		 echo $html;
		 
	} // end love_link
	
	
	/**
	 * Will upload the image and move it to comment-pub folder and save that as db path
	 *
	 * @param	$comment_id	The ID of the comment to which we're adding the image.
	 */
	function save_comment_pub( $comment_id ) {

		// The ID of the post on which this comment is being made
		$post->ID = $_POST['comment_post_ID'];
		
		// The key ID of the comment image
		$comment_pub_id = "comment_pub_$post->ID";
		
		// If the nonce is valid and the user uploaded an image, let's upload it to the server
		if( isset( $_FILES[ $comment_pub_id ] ) && ! empty( $_FILES[ $comment_pub_id ] ) ) {
			
			// Store the parts of the file name into an array
			$file_name_parts = explode( '.', $_FILES[$comment_pub_id]['name'] );
			
			// If the file is valid, upload the image, and store the path in the comment meta
			if( $this->is_valid_file_type( $file_name_parts[ count( $file_name_parts ) - 1 ] ) ) {;
			
				// Upload the comment image to the uploads directory
				$comment_pub_file_resize = wp_upload_bits( $_FILES[ $comment_pub_id ]['name'], null, file_get_contents( $_FILES[ $comment_pub_id ]['tmp_name'] ) );
				
				// Get uploaded Filename before moving
				$filename = explode( '/', $comment_pub_file_resize['file'] );
				$filename = $filename[ count( $filename ) - 1];
				$fullSizeFilename = $filename;
				
				global $fullSizeFilename;
				// Get the file path to uploads folder
				$upload_dir = wp_upload_dir();
				
				// "$upload_dir['path'].'/'.$filename" This is the file path of the uploaded file by upload_bits
				// "$upload_dir['basedir'].'/comment_pub/'.$filename"  This is the file path that we want
				if ( copy( $upload_dir['path'].'/'.$filename, $upload_dir['basedir'].'/comment_pub/'.$filename ) ) {
				  unlink( $upload_dir['path'].'/'.$filename );
				}
				
				// Save the new file path so we can use it in the resize function. 
				$current_file = $upload_dir['basedir'].'/comment_pub/'.$filename ;
				
				// Start renaming here
					$filename_xtndls = explode( '.', $filename );
					$random_int = rand();
					
					//we need this in order to add them to name 
					$options = get_option( 'nmg_comment_pub' );
					$imgw = $options[0];
					$imgh = $options[1];
				
					
					//Old file name and path
					$old_name = $current_file;
					
					//new file name
					$new_name =  $upload_dir['basedir'].'/comment_pub/'.strtolower( $filename_xtndls[ 0 ] ).$random_int.'-'.$imgw.'x'.$imgh.'.'.$filename_xtndls[ 1 ];
					
					
					//rename($old_name, $new_name);
					
					if( rename($old_name, $new_name) ){
						
						$current_file = $new_name;
						
					}else{
						
						$current_file = $old_name;
							
					}
					
				// end renaming here
				
				
				//Since WP 3.5 image_resize is deprecated
				if( function_exists( 'wp_get_image_editor' ) ){
					
					// get the current image sizes for the uploaded image
					// that were set in the admin panel
					$options = get_option( 'nmg_comment_pub' );
					$imgw = $options[0];
					$imgh = $options[1];
					
					$image = wp_get_image_editor( $current_file );
					if ( ! is_wp_error( $image ) ) {
						$image->resize( $imgw, $imgh, true );
							$file_destination = $image->save( $current_file );
						}
							
					// Get the actual filename 
					// we know the file name through wp_get_image_editor
					$filename = $file_destination['file'];
					
					//get the current uploads folder and attach the new file name
					$upload_dir = wp_upload_dir();
					//this is the URL that is attached to the comment in the DB
					$resized_file_url = $upload_dir['baseurl'].'/comment_pub/'.$filename;
					
					//create an array for update_comment_meta to accept and process
					//file = raw (server) file path
					//url = URL path
					//error = any errors 
					$resized_file_path = array(
						"file" => $file_destination['path'],
						"url" => $resized_file_url,
						"error" => ''
					);
							
					// Set post meta about this image. Need the comment ID and need the path.
					if( false == $comment_pub_file_resize['error'] ) {
						
						// Since we've already added the key for this, we'll just update it with the file.
						add_comment_meta( $comment_id, 'comment_pub', $resized_file_path );
						
					} // end if/else when there is no error
					
				}
				//if WP_Image_Editor class no exist then we use the old image_resize
				else{
					
					// get the current image sizes for the uploaded image
					// that were set in the admin panel
					$options = get_option( 'nmg_comment_pub' );
					$imgw = $options[0];
					$imgh = $options[1];
				
					$file_destination = image_resize( $current_file, $imgw, $imgh, true );
					
				
					if( $file_destination ){
						unlink( $upload_dir['basedir'].'/comment_pub/'.$fullSizeFilename );
					}
					
					// Get the actual filename 
					$filename = explode( '/', $file_destination );
					$filename = $filename[ count( $filename ) - 1];
					
					//get the current uploads folder and attach the new file name
					$upload_dir = wp_upload_dir();
					//this is the URL that is attached to the comment in the DB
					$resized_file_url = $upload_dir['baseurl'].'/comment_pub/'.$filename;
					
					//create an array for update_comment_meta to accept and process
					//file = raw (server) file path
					//url = URL path
					//error = any errors 
					$resized_file_path = array(
						"file" => $file_destination,
						"url" => $resized_file_url,
						"error" => ''
						
					);
					
					// Set post meta about this image. Need the comment ID and need the path.
					if( false == $comment_pub_file_resize['error'] ) {
						
						// Since we've already added the key for this, we'll just update it with the file.
						add_comment_meta( $comment_id, 'comment_pub', $resized_file_path );
						
					} // end if/else
					
				
				}// end if image_resize doesn't exist
						
			} // end if
 		
		} // end if
		
	} // end save_comment_pub
	
	
	/**
	 * Appends the image above the content of the comment.
	 *
	 * @param	$comment	The content of the comment.
	 */
	 
	function display_comment_pub( $comments ) {
		
		wp_reset_postdata();
		global $post;
		$com_arg = array( 'post_id' => $post->ID );
		$comments = get_comments( $com_arg );
		// Make sure that there are comments
		if( count( $comments ) > 0 ) {

			// Loop through each comment...
			foreach( $comments as $comment ) {
			
				// ...and if the comment has a comment image...
				if( true == get_comment_meta( $comment->comment_ID, 'comment_pub' ) ) {
			
					// ...get the comment image meta
					$comment_pub = get_comment_meta( $comment->comment_ID, 'comment_pub', true );
					
					$options = get_option('nmg_comment_pub');
					
					if($comment_pub['url']){
						// ...and render it in a span element appended to the comment
						
						/*
						* First we will save the original $comment->comment_content create get the attachment and make it a variable
						* then at the end of everything attach the original content at the end and make it the new $comment->comment_content
						* IMPORTANT $comment_pub_content = '' in order to clear the variable 
						* every time it loops and ensure there are no repeats;
						*/
						
						$comment_pub_original_content = $comment->comment_content;
						
						$options = get_option('nmg_comment_pub');
						if( $options[4] != 'active' ){
							
							$comment_pub_content = '';
							$comment_pub_content .= '<span class="comment-pub gravatar-on">';
								$comment_pub_content .= '<img src="' . $comment_pub['url'] . '" alt="" />';
							$comment_pub_content .= '</span><!-- /.comment-pub -->';
							$comment_pub_content .= $comment_pub_original_content;
							
							$comment->comment_content = $comment_pub_content;
							
						}// end if comment pub is being used as Local Gravatar
						
					}//if they have a gratar and didn't upload a comment image
					elseif( get_avatar( $comment, $avatar_size ) && !$comment_pub['url'] ){
							
						$options = get_option( 'nmg_comment_pub' );
						$imgw = $options[0];
							
						if ( '0' != $comment->comment_parent )
							$avatar_size = 39;
							echo get_avatar( $comment, $avatar_size );
										
					}
					//if no comment image or gravatar
					else{
						//call options in order to get image size
						$options = get_option( 'nmg_comment_pub' );
						$imgw = $options[0];
						$imgh = $options[1];
								
						$img_path = plugins_url( 'comment-pub/img/anonymous.jpg' );
							
						$comment_pub_original_content = $comment->comment_content;
							
						$comment_pub_content = '';
						$comment_pub_content .= '<span class="comment-pub gravatar-on">';
							$comment_pub_content .= '<img src="'.$img_path.'" alt="" width="'.$imgw.'" height="'.$imgh.'" />';
						$comment_pub_content .= '</span><!-- /.comment-pub -->';
							$comment_pub_content .= $comment_pub_original_content;
							
						$comment->comment_content = $comment_pub_content;
								
					}//end if an image was attached to the comment
				
				}  
				
			} // end foreach
			
		} // end if
		
		return $comments;

	} // end display_comment_pub
	
	/*--------------------------------------------*
	 * Utility Functions
	 *---------------------------------------------*/
	
	/**
	 * Determines if the specified type if a valid file type to be uploaded.
	 *
	 * @param	$type	The file type attempting to be uploaded.
	 * @return			Whether or not the specified file type is able to be uploaded.
	 */ 
	private function is_valid_file_type( $type ) { 
	
		$type = strtolower( trim ( $type ) );
		return $type == 'png' || $type == 'gif' || $type == 'jpg' || $type == 'jpeg';
		
	} // end is_valid_file_type
	
	/**
	 * Determines if the hosting environment allows the users to upload files.
	 *
	 * @return			Whether or not the hosting environment supports the ability to upload files.
	 */ 
	private function can_save_files() {
		return function_exists( 'file_get_contents' );
	} // end can_save_files
	
	
  
} // end class

class nmg_comment_admin{
	function __construct() {
			// Create an admin panel
			add_action('admin_menu', array(&$this, 'nmg_comment_pub_admin'));
	}
	function nmg_comment_pub_admin() {
		add_options_page('Comment Pub Options','Comment Pub Options','moderate_comments','comment_pub_options',array($this, 'comments_pub_admin'));
	}
	function  comments_pub_admin() {
		include('comments_pub_admin.php');  
	}
}


class nmg_comment_sorting{
	function __construct() {
			// Create an admin panel
			add_action( 'admin_menu', array( &$this, 'nmg_comment_pub_sorting' ) );
	}
	function nmg_comment_pub_sorting() {
		
		global $options_hook;
		$options_hook = add_submenu_page( 'edit-comments.php', 'Comment Pub', 'Comment Pub', 'moderate_comments', 'comment_pub_sorting', array( $this, 'comments_pub_sorting' ));
		
		
		
		
		add_action( "load-$options_hook", 'nmg_comment_pub_options' );
		
		function nmg_comment_pub_options(){
			
			$screen = $_REQUEST["page"];
			
			// get out of here if we are not on our settings page
			if( $screen != 'comment_pub_sorting' )
				return;
				
				//Comments per page 
				$args = array(
					'label' => __('Comments per page', 'comment_pub'),
					'default' => 10,
					'option' => 'nmg_comments_per_page'
				);
				
				//number of comments per page
				add_screen_option( 'per_page', $args  );
			
		}
        
		if ( isset($_POST['wp_screen_options']) && is_array($_POST['wp_screen_options']) ) {
			check_admin_referer( 'screen-options-nonce', 'screenoptionnonce' );
			
			global $user;
			if ( !$user = wp_get_current_user() )
				return;
				
				$option = $_POST['wp_screen_options']['option']; //This is a hidden field in the form name="wp_screen_options[option]"
				$value = $_POST['wp_screen_options']['value'];//This in the form  is name="wp_screen_options[value]"
				
				global $current_user;
				
				//print_r( '<br />'.'User id '.$current_user->ID.'<br />' );
				update_user_meta($current_user->ID, $option, $value);
        
		}
		
		
		
	}
	function comments_pub_sorting() {
		include('comments_pub_sorting.php');  
	}
}

/*
 * Create an instance for every class when used
*/ 
new nmg_comment_pub();
new nmg_comment_admin();
new nmg_comment_sorting();


/**
 * on uninstallation, remove the custom field from the users and delete the local avatars
 */

//register_uninstall_hook(  plugins_url( 'comment-pub' ), 'comment_pub_uninstall' );


function comment_pub_uninstall() {

	$options = get_option( 'nmg_comment_pub' );
	$noTrace = $options[3];
		
		$comment_pub = new nmg_comment_pub();
		$comment_pub_admin = new nmg_comment_admin();
		$comment_pub_sorting = new nmg_comment_sorting();
	
	if( $noTrace == 1 ){
		
		$upload_dir = wp_upload_dir();
		$dir = $uploads_dir['basedir'].'/comment_pub' ;
		
		if ( file_exists($dir) && is_dir($dir) ) {
			echo 'File name exist and is a directory. Delete it';
			rmdir($dir);
		}else{
			echo 'File doesn\'t exists or is not a directory. Do Nothing.';
		}
		
		delete_option( 'nmg_comment_pub' );
		delete_comment_meta( 'comment_pub' );
		
	}
}
?>