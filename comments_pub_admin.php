<?php  
/*
 * Handling data
 */
 
 	$db_results = get_option( 'nmg_comment_pub' );
	if( isset( $_POST['nmg_cp_hidden'] ) && $_POST['nmg_cp_hidden'] == 'Y' ) {  
		
		//when user tries to save the form
	
        //save img width
        $imgw = $_POST['nmg_cp_imgw']; 
	
        //save img height
        $imgh = $_POST['nmg_cp_imgh']; 
		
		// make sure that the width and height of the image are integeres
		// if they are not then reset values to 0 in order to prevent DB infections. 
		if( !absint( $imgw ) || !absint( $imgh ) ){
			
			if( !absint( $imgw ) ){
				$imgw = 0;
				$imgfail = ' width ';
			}
			
			if( !absint( $imgh ) ){
				$imgh = 0;
				$imgfail = ' height ';
			}
			
			if( !absint( $imgw ) &&  !absint( $imgh ) ){
				$imgfail = ' width & height ';
			}
			
			$html = '<div id="comment-pub-notice" class="error">';
				$html .= '<p>';
					$html .= __( '<strong>Comment Pub Notice:</strong> Image '.$imgfail.' needs to be a possitive integer, ie( 100 x 100 or 2000 x 1500 or 1 x 1) .', 'nmg_comment_pub' );
				$html .= '</p>';
			 $html .= '</div><!-- /#comment-pub-notice -->';
			 
			 echo $html;
			
		}
		
		//Save Stand-in Email
		
		if( !empty( $_POST['nmg_cp_stnd_email'] ) ){
        
			$stndEmail = strip_tags( $_POST['nmg_cp_stnd_email'] ); 
			
		}else{
			$stndEmail = ''; 
		}
		
		//leave no trace
		global $noTrace;
		$noTrace = $_POST['nmg_cp_no_trace'];
		
		if( empty( $noTrace ) || !absint( $noTrace ) ){
			$noTrace = 0;
		}else{
			
			$html = '<div id="comment-pub-notice" class="error">';
				$html .= '<p>';
					$html .= __( '<strong>Notice:</strong> Everything created by <strong>Comment Pub</strong> will be deleted. This includes images that were uploaded using Comment Pub. ', 'nmg_comment_pub' );
				$html .= '</p>';
			 $html .= '</div><!-- /#comment-pub-notice -->';
			 
			 echo $html;
			
			$stndEmail = '';
				
		}
		
		//Use as local avatars
		
		$lAvatars =  !empty( $_POST["nmg_cp_lavatars"] ) ? mysql_real_escape_string( $_POST["nmg_cp_lavatars"] ) : " inactive";
		
		$pageTemp =  !empty( $_POST["nmg_cp_pageTemp"] ) ? mysql_real_escape_string( $_POST["nmg_cp_pageTemp"] ) : " inactive";
		
		//leave no trace
		
		global $loveShare;
		$loveShare = $_POST['nmg_cp_luv_shr'];
		
		if( empty( $loveShare ) || !absint( $loveShare ) ){
			
			$html = '<div id="comment-pub-notice" class="error">';
				$html .= '<p>';
					$html .= __( '<strong></strong> If there is any way we can make it better please don\'t hesitate to let us know. <strong>Thank You.</strong> ', 'nmg_comment_pub' );
				$html .= '</p>';
			 $html .= '</div><!-- /#comment-pub-notice -->';
			 
			 echo $html;
			
			$loveShare = 0;
		}else{
			
			$html = '<div id="comment-pub-notice" class="updated">';
				$html .= '<p>';
					$html .= __( '<strong>Thank you</strong>  for your love. We appreciate it. ', 'nmg_comment_pub' );
				$html .= '</p>';
			 $html .= '</div><!-- /#comment-pub-notice -->';
			 
			 echo $html;
			
			$loveShare = 1;
				
		}
		
		
		$comments_pub_all_ops = array( $imgw, $imgh, $stndEmail, $noTrace, $lAvatars, $pageTemp, $loveShare );
		
		update_option('nmg_comment_pub', $comments_pub_all_ops);
		
		?>
		<div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div> 
		<?php  
		
    	} 
		if( empty( $db_results ) ) {  
			//When page loads before update
			
			$imgw = 55;
			$imgh = 55;
			$stndEmail = $noTrace = $lAvatars = $pageTemp = '';
			
			$comments_pub_all_ops = array( $imgw, $imgh, $stndEmail, $noTrace, $lAvatars, $pageTemp, $loveShare );
			
			update_option('nmg_comment_pub', $comments_pub_all_ops);
			
		}
		else {  
			//When page loads and settings have been set in the database
			
			// get wp database values stored under nmg_comment_pub
			// This is a simple indexed array 
			$options = get_option( 'nmg_comment_pub' );
			
			//If the form was ever filled in get the old values into the form
			$imgw = $options[0];
			$imgh = $options[1];
			$stndEmail = $options[2]; 
			$noTrace = $options[3];
			$lAvatars = $options[4];
			$pageTemp = $options[5];
			$loveShare = $options[6];
				
	}  
?>
	<style type="text/css">
    	strong{ letter-spacing: .01em; padding: 3px 0; }
    	strong.description{ color: #21759B; float: right; width: 55%; }
		h3{ font-size: 1.7em; }
		hr, br{ background-color: #efefef; border: none; width: 100%; clear: both; }
		.br-invis{ background-color: transparent; }
		.love{ padding: 10px; background-color: #FFF6F4; }
    </style>
    <div class="wrap">  
        <?php    echo "<h2 class='title'>" . __( 'Comment Pub Options', 'nmg_comment_pub' ) . "</h2>"; ?>  
        <form name="nmg_cp_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
            <input type="hidden" name="nmg_cp_hidden" value="Y">
            
            <?php    echo "<h3>" . __( 'Basic Settings', 'nmg_comment_pub' ) . "</h3>"; ?> 
            
	            <?php //Use comment pub for specific pages or all comment forms?>
                <p><?php _e("Use <strong>Comment Pub</strong> as Guest Book? " ); ?><input type="checkbox" name="nmg_cp_pageTemp" value="active" <?php if($pageTemp == 'inactive' ){}elseif( $pageTemp == 'active' ){ echo 'checked="checked"'; }?>>
                
                <strong class="description">Works only as Page Template: this will add a page template (based on Twenty Eleven Theme) to your current theme. Please use "GuestBook - Comment Pub" as a page template.</strong>
                <br class="br-invis" />
                </p>
                
	            <?php //Add image Width and Height?>
                <p><?php _e("Image Width x Height: " ); ?><input type="number" name="nmg_cp_imgw" value="<?php echo $imgw; ?>" min="1" max="10000">x<input type="number" name="nmg_cp_imgh" value="<?php echo $imgh; ?>"  min="1" max="10000" ><?php _e(" ex: 55x55" ); ?>
                
                <strong class="description"><span class="important">Warwning: Changing size after uploads may distort images</span></strong>
                <br class="br-invis" />
                </p>   
            
            <hr/> 
			
            <?php    echo "<h3>" . __( 'Local Avatars', 'nmg_comment_pub' ) . "</h3>"; ?> 
                <p><?php _e("Use Comment Pub as Local Avatars: " ); ?><input type="checkbox" name="nmg_cp_lavatars" value="active" <?php if($lAvatars == 'inactive' ){}elseif( $lAvatars == 'active' ){ echo 'checked="checked"'; }?>>
                
                <strong class="description"><span class="important">Warwning: Using comment pub as local avatars does not mean they exist beyond this site.</span></strong>
                
                <br class="br-invis" />
                </p>
            
            <hr/> 
			
			<?php    echo "<h3>" . __( 'Comment Email Overwrite', 'nmg_comment_pub' ) . "</h3>"; ?>  
            	<?php //Add Stand-in Email?>
                
                <p><br /><?php _e("Stand-in Email: " ); ?><input type="email" name="nmg_cp_stnd_email" value="<?php echo $stndEmail; ?>" size="30">
                 <strong class="description"><span class="important">When adding a comment without being a registered user Wordpress will requiere an email.</span> <br/>Please provide a stand-in email if you don't want a comment form to require an email</strong>
                 
                 <br class="br-invis" />
                 </p> 
                
            <hr/> 
			
			<?php    echo "<h3>" . __( 'Plugin Clean Up', 'nmg_comment_pub' ) . "</h3>"; ?>  
            	<?php //Add Stand-in Email?>
            
                <p>Do you want to delete everything when uninstalling? <input type="radio" name="nmg_cp_no_trace" value="1"  <?php if($noTrace == 1){ echo 'checked';}?>> YES | <input type="radio" name="nmg_cp_no_trace" value="0" <?php if($noTrace == 0){ echo 'checked';}?>> NO </p>
                
            <hr/> 
			
            <div class="love">
			<?php    echo "<h3>" . __( 'Link Love', 'nmg_comment_pub' ) . "</h3>"; ?>  
            	<?php //Add Stand-in Email?>
            
                <p>Share your love for Comment Pub? <input type="radio" name="nmg_cp_luv_shr" value="1"  <?php if($loveShare == 1){ echo 'checked';}?>> YES | <input type="radio" name="nmg_cp_luv_shr" value="0" <?php if($loveShare == 0){ echo 'checked';}?>> NO 
                 <strong class="description"><span class="important">This will add a link back to our site below the upload input.</span></strong>
                 
                 <br class="br-invis" />
                 </p> 
            </div>
                
            <p class="submit">  
            <input type="submit" name="Submit" value="<?php _e('Update Options', 'nmg_comment_pub' ) ?>" />  
            </p>  
        </form>  
    </div>  
