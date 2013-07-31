<?php 
	if( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit ();
	
	//check if we need to delete the image as well
	$options = get_option( 'nmg_comment_pub' );
	$noTrace = $options[3];
	
	// Get the file path to the folder created when installing comment_pub	
	$upload_dir = wp_upload_dir();
	$dir = $upload_dir['basedir'].'/comment_pub' ;
	
	//This will check if the user wants to leave no trace behind
	if( $noTrace == 1 ){
		
		//Let make sure that it is a directory ( folder )
		if( !is_dir($dir) ){
			$remove_status_is_file = false;
		}// end of is directory
		
		//If its a directory and it has files we are going to try to delete them first
		foreach( scandir( $dir ) as $item ){
			
			//Skip the dots appear at the begining of the list
			if ($item == '.' || $item == '..') continue;
			$delete_path = $dir.'/'.$item;
			
			//delete the files inside the folder
			if( unlink($delete_path) ){
				$remove_status_images = true;
			}else{
				$remove_status_images = false;
			}
			
		}// end foreach item in the folder
		
		/* 
		 * Only Remove Directory when all images have been deleted
		 * Even though this already happens by php, We don't want to give crash errors to the user
		 * If the content of the folder was deleted before we could delete the folder 
		 * 
		 * $remove_status == true / when it was deleted succesfully
		 * $remove_status == false / when it fails
		 * $remove_status == '' / when comment images from the folder were deleted before this step
 		 */
		if( $remove_status_is_file == true && $remove_status_images == true || $remove_status_is_file == '' && $remove_status_images == '' ){
			rmdir($dir);
		}
	
		/*
		* make sure we can delete Meta
		*/
			
		//before we can delete comment meta we need to get comment_id's that have comment_pub as meta_key
		//First we check for comments that are viewable (Not in trash)
		$list_of_comments = get_comments();
		
		//print_r( $list_of_comments );
		foreach( $list_of_comments as $comment ){
			
			//print_r( 'ID = '.$comment->comment_ID.'<br />'  );
			$crnt_comm_ID = $comment->comment_ID;
			
			//We should have a comment list so now delete individuals comment meta when it matched comment_pub
			if( delete_comment_meta( $crnt_comm_ID, 'comment_pub' ) ){
				$remove_status_comment = true;	
			}else{
				$remove_status_comment = false;	
			}
			
		}//end of comment list excluding trash
		
		//Second we check comments that have been deleted (in trash)
		$list_of_comments = get_comments( array( 'status' => 'trash' ) );
		
		//print_r( $list_of_comments );
		foreach( $list_of_comments as $comment ){
			
			//print_r( 'ID deleted = '.$comment->comment_ID.'<br />' );
			$crnt_comm_ID = $comment->comment_ID;
			
			//We should have a comment list so now delete individuals comment meta when it matched comment_pub
			if( delete_comment_meta( $crnt_comm_ID, 'comment_pub' ) ){
				$remove_status_del_comment = true;	
			}else{
				$remove_status_del_comment = false;	
			}
			
		}//end of comment list only trashed comments
		
		/* 
		 * We are going to delete the comment_pub option from WordPress once we know we don't need it
		 * Therefore we need to make sure that all comments including the once in the trash are deleted
		 * We are also going to check for the possibility that comment_pub option was deleted before hand
		 * 
		 * $remove_status == true / when it was deleted succesfully
		 * $remove_status == false / when it fails
		 * $remove_status == '' / when comments never had comment_pub
		 */
		if( $remove_status_comment == true && $remove_status_del_comment == true || $remove_status_comment == '' && $remove_status_del_comment == '' ){
			delete_option( 'nmg_comment_pub' );
		}//end of delete option when we don't need it
		
	}//end of leave no trace behind when plugin is deleted
	
?>