<div id="comments">
	<?php if ( post_password_required() ) : ?>
		<p class="nopassword"><?php _e( 'This post is password protected. Enter the password to view any comments.', 'comment_pub' ); ?></p>
	</div><!-- #comments -->
	<?php
			/* Stop the rest of comments.php from being processed,
			 * but don't kill the script entirely -- we still have
			 * to fully load the template.
			 */
			return;
		endif;
	?>

		<h2 id="comments-title">
			<?php
				printf( _n( 'One thought on &ldquo;%2$s&rdquo;', '%1$s thoughts on &ldquo;%2$s&rdquo;', get_comments_number(), 'comment_pub' ),
					number_format_i18n( get_comments_number() ), '<span>' . get_the_title() . '</span>' );
			?>
		</h2>

		<nav class="nav-single">
			<h3 class="assistive-text"><?php _e( 'Post navigation', 'twentytwelve' ); ?></h3>
			<span class="nav-previous"><?php previous_post_link( '%link', '<span class="meta-nav">' . _x( '&larr;', 'Previous post link', 'twentytwelve' ) . '</span> %title' ); ?></span>
			<span class="nav-next"><?php next_post_link( '%link', '%title <span class="meta-nav">' . _x( '&rarr;', 'Next post link', 'twentytwelve' ) . '</span>' ); ?></span>
		</nav><!-- .nav-single -->

		<ol class="commentlist">
	<?php
	
		//Gather comments for a specific page/post 
		$comments = get_comments(array(
			'post_id' => $post->ID
		));

		//Display the list of comments
		wp_list_comments(array(
			'reverse_top_level' => false, //Show the latest comments at the top of the list
			'callback' => 'comment_pub_comments' // Call back function 
		), $comments);
	
		function comment_pub_comments($comment, $args, $depth) {
			$GLOBALS['comment'] = $comment;
			switch ( $comment->comment_type ) :
				case 'pingback' :
				case 'trackback' :
			?>
			<li class="post pingback">
				<p><?php _e( 'Pingback:', 'comment_pub' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __( 'Edit', 'comment_pub' ), '<span class="edit-link">', '</span>' ); ?></p>
			<?php
					break;
				default :
			?>
			<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
				<article id="comment-<?php comment_ID(); ?>" class="comment">
					<footer class="comment-meta">
						<div class="comment-author vcard">
                        
							<?php
								
								global $avatar_size, $comment;
								$comment_pub = get_comment_meta( $comment->comment_ID, 'comment_pub', true );
								
								if( !empty( $comment_pub['url'] ) ){
									
									$options = get_option( 'nmg_comment_pub' );
									$imgw = $options[0];
									$imgh = $options[1];
									
									$comment_pub_image = '<span class="comment-pub">';
										$comment_pub_image .= '<img class="avatar" src="' . $comment_pub['url'] . '" alt="" width="'.$imgw.'" height="'.$imgh.'" />';
									$comment_pub_image .= '</span><!-- /.comment-pub -->';
									
									echo $comment_pub_image;
									
									
								} //if they have a gratar and didn't upload a comment image
								elseif( get_avatar( $comment, $avatar_size ) && empty( $comment_pub['url'] ) ){
									
									$options = get_option( 'nmg_comment_pub' );
									$imgw = $options[0];
									
									if ( '0' != $comment->comment_parent )
										$avatar_size = 39;
			
									echo get_avatar( $comment, $avatar_size );
										
								}//if no comment image or gravatar
								else{
									
									$options = get_option( 'nmg_comment_pub' );
									$imgw = $options[0];
									$imgh = $options[1];
									
									$img_path = plugins_url( 'comment-pub/img/anonymous.jpg' );
									
									$comment_pub_image = '<span class="comment-pub">';
										$comment_pub_image .= '<img class="avatar" src="'.$img_path.'" alt="" width="'.$imgw.'" height="'.$imgh.'" />';
									$comment_pub_image .= '</span><!-- /.comment-pub -->';
									
									echo $comment_pub_image;
										
								}
								
							?>
		
							<?php edit_comment_link( __( 'Edit', 'comment_pub' ), '<span class="edit-link">', '</span>' ); ?>
						</div><!-- .comment-author .vcard -->
		
						<?php if ( $comment->comment_approved == '0' ) : ?>
							<em class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'comment_pub' ); ?></em>
							<br />
						<?php endif; ?>
		
					</footer>
		
					<div class="comment-content"><?php comment_text(); ?></div>
		
					<div class="reply">
						<?php comment_reply_link( array_merge( $args, array( 'reply_text' => __( 'Reply <span>&darr;</span>', 'comment_pub' ), 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
					</div><!-- .reply -->
				</article><!-- #comment-## -->
		
			<?php
					break;
			endswitch;
        }
	
	?>
		</ol>

		<nav class="nav-single">
			<h3 class="assistive-text"><?php _e( 'Post navigation', 'twentytwelve' ); ?></h3>
			<span class="nav-previous"><?php previous_post_link( '%link', '<span class="meta-nav">' . _x( '&larr;', 'Previous post link', 'twentytwelve' ) . '</span> %title' ); ?></span>
			<span class="nav-next"><?php next_post_link( '%link', '%title <span class="meta-nav">' . _x( '&rarr;', 'Next post link', 'twentytwelve' ) . '</span>' ); ?></span>
		</nav><!-- .nav-single -->
        
	<?php
	/*
	* We are only changing one field so there is no real need to rewrite an entire form
	*/	
			$options = get_option( 'nmg_comment_pub' );
	 
	 		if( $options[5] == 'active' && is_page_template('comment_pub_page.php') && is_page() ){
				
				/*
				* Since we are using comment pub as a Guest Book we need to 
				* include the filter to include the upload function 
				*/
				
				add_action( 'comment_form' , 'add_image_upload_form' );
				
				/**
				 * Adds the comment image upload form to the comment form.
				 *
				 * @param	$post->ID	The ID of the post on which the comment is being added.
				 */
				function add_image_upload_form( $post ) {
					// Create the label and the input field for uploading an image
					$options = get_option( 'nmg_comment_pub' );
							
					// Create the label and the input field for uploading an image
					$html = '<div id="comment-pub-wrapper">';
						$html .= '<p id="comment-pub-error">';
							$html .= __( '<strong>Heads up!</strong> You are attempting to upload an invalid image. If saved, this image will not display with your comment.', 'nmg_comment_pub' );
						$html .= '</p>';
						 $html .= "<label for='comment_pub_$post->ID'>";
							$html .= __( 'Select an image for your comment (GIF, PNG, JPG, JPEG), <br />images are automatically resized', 'nmg_comment_pub' );
						 $html .= "</label>";
						 $html .= "<input type='file' name='comment_pub_$post->ID' id='comment_pub' />";
					 $html .= '</div><!-- #comment-pub-wrapper -->';
					 
					if( $options[6] == 1 ){
						 $html .= '<a class="loveLink" href="http://commentpub.com" target="_blank">Comment Pub</a>';
					}
					 echo $html;
					 
				} // end add_image_upload_form
				
				
				// now that we have registered the filter and now its active add it to the comment form
				comment_form();
				 
			}else{
				
				//If we are adding comment_pub in all comment forms
				comment_form();
				 
			}
	 
	 ?>
     
     

</div><!-- #comments -->
