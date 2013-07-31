<?php 
require_once( ABSPATH . 'wp-admin/admin.php');
if ( !current_user_can('edit_posts') )
	wp_die(__('Cheatin&#8217; uh?'));

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class comment_pub_table extends WP_List_Table {
	 
    /** ************************************************************************
     * Normally we would be querying data from a database and manipulating that
     * for use in your list table. For this example, we're going to simplify it
     * slightly and create a pre-built array. Think of this as the data that might
     * be returned by $wpdb->query().
     * 
     * @var array 
     **************************************************************************/
	 
	//declaring veriables for before php5
	var $query;
	var $nonce;
	var $action;
	var $comment_status;
	var $db_post_ID;
	var $option;
	var $value;
	var $item;
	//from WordPress
	var $wpdb;
	var $_wp_column_headers;
	
	function create_query(){
		
    global $wpdb, $_wp_column_headers;
	
			$query = " SELECT * FROM `$wpdb->comments` `$wpdb->commentmeta` WHERE (CONVERT(`comment_approved` USING utf8) NOT LIKE 'trash') ";
			
			/*
			* We are adding this in order to make table sortable
			* Its preffered that Prepare never has veriables code into it but in this case user doesn't see variables.
			* UNLESS sorting so when we are sorting the query we do it the WordPress way.
			* Until I can find a better solution.
			*/
			if( empty($orderby) && empty($order)){
				
			/* -- Ordering Parameters -- */	 
				//parameteres that are going to be used to order the result
				$orderby = !empty( $_GET["orderby"] ) ? mysql_real_escape_string( $_GET["orderby"] ) : " comment_date";
				$order = !empty( $_GET["order"] ) ? mysql_real_escape_string( $_GET["order"] ): "desc";
				
				$query .= " ORDER BY $orderby $order "; 	
				
			}else{
				
				$query .= " ORDER BY %s %s "; 
				
			}
			
			//If its a search do this query instead
			if( !empty( $_REQUEST["s"] ) ){
			
				$query = " SELECT * FROM $wpdb->comments, $wpdb->commentmeta WHERE $wpdb->comments.comment_ID = $wpdb->commentmeta.comment_ID ";
				$search = '%'.sanitize_text_field( mysql_real_escape_string( $_GET["s"] ) ).'%';
					
				$query .= " AND $wpdb->comments.comment_content LIKE %s "; 
			
				/*
				* We are adding this in order to make table sortable
				* Its preffered that Prepare never has veriables code into it but in this case user doesn't see variables.
				* UNLESS sorting so when we are sorting the query we do it the WordPress way.
				* Until I can find a better solution.
				*/
				if( empty($orderby) && empty($order)){
					
				/* -- Ordering Parameters -- */	 
					//parameteres that are going to be used to order the result
					$orderby = !empty( $_GET["orderby"] ) ? mysql_real_escape_string( $_GET["orderby"] ) : " comment_date ";
					$order = !empty( $_GET["order"] ) ? mysql_real_escape_string( $_GET["order"] ): "desc";
					
					$query .= " ORDER BY $orderby $order "; 	
					
				}else{
					
					$query .= " ORDER BY %s %s "; 
					
				}
			
			}//close search isn't empty
			
		/* -- Ordering Parameters -- */	 
			//parameteres that are going to be used to order the result
			$orderby = !empty( $_GET["orderby"] ) ? mysql_real_escape_string( $_GET["orderby"] ) : "comment_approved, comment_date";
			$order = !empty( $_GET["order"] ) ? mysql_real_escape_string( $_GET["order"] ): "desc";
			
			$db_query = $wpdb->get_results( $wpdb->prepare( $query, $orderby, $order ), ARRAY_A );
			
			
			if( !empty( $_REQUEST["s"] ) ){
				
				$search = '%'.sanitize_text_field( mysql_real_escape_string( $_GET["s"] ) ).'%';
				$db_query = $wpdb->get_results( $wpdb->prepare( $query, $search, $orderby, $order ), ARRAY_A );
				
			}
			
			//DeBug Query
			//print_r('<br /><strong>current query</strong> <br />'.$wpdb->prepare( $query, $orderby, $order ).'<br />');
		
		return  $db_query;
	 }
	 
    
    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'comment',     //singular name of the listed records
            'plural'    => 'comments',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
		
    }
	
    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title() 
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as 
     * possible. 
     * 
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     * 
     * For more detailed insight into how columns are handled, take a look at 
     * WP_List_Table::single_row_columns()
     * this case will come from db
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
		
        switch($column_name){
            case 'comment_date':
            case 'comment_author':
            case 'comment_approved':
            case 'comment_ID':
            case 'comment_post_ID':
            case 'comment_author_email':
            case 'comment_author_IP':
            case 'comment_content':
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }
	
	/*
	 * This will add comment status to the tr as a class and will also add comment_ID as the tr's ID
	 */
	 //First we will make an array of class' we want in each tr
	function comment_classes_array(){
		global $comment_alt, $comment_depth, $comment_thread_alt;
		
		// add comment to list
		$classes = ' comment ';
	
		if ( empty($comment_alt) )
			$comment_alt = 0;
		if ( empty($comment_depth) )
			$comment_depth = 1;
		if ( empty($comment_thread_alt) )
			$comment_thread_alt = 0;
	
		if ( $comment_alt % 2 ) {
			$classes .= ' odd ';
			$classes .=  'alt ';
		} else {
			$classes .= ' even ';
		}
	
		$comment_alt++;
	
		// Alt for top-level comments
		if ( 1 == $comment_depth ) {
			if ( $comment_thread_alt % 2 ) {
				$classes .= ' thread-odd ';
				$classes .= ' thread-alt ';
			} else {
				$classes .= ' thread-even ';
			}
			$comment_thread_alt++;
		}
	
		$classes .= " depth-$comment_depth ";
	
		return $classes; 
	}
	 
	function single_row( $item ) {
		
		$comment = $item;

		$the_comment_class = apply_filters( 'comment_class', wp_get_comment_status( $comment['comment_ID'] ).' '.$this->comment_classes_array() );

		$post = get_post( $comment['comment_post_ID'] );

		$this->user_can = current_user_can( 'edit_comment', $comment['comment_ID'] );
		
		echo "<tr id='comment-".$comment['comment_ID']."' class='$the_comment_class'>";
		echo $this->single_row_columns( $comment );
		echo "</tr>\n";
	}

	
	 /** ************************************************************************
	 * This method will return the formated date and time
	 * 
     * @param array $item DATE and TIME
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
	function column_comment_date($item){
		$db_date = $item['comment_date'];
		$date_stng = get_option('date_format');
		$time_stng = get_option('time_format');

		echo date($date_stng ,strtotime($db_date) );
		echo ' at '.date($time_stng ,strtotime($db_date) );
	}
	
	 /** ************************************************************************
	 * This method will return the post title based on post id
	 * 
     * @param array $item post title
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
	function column_comment_post_ID($item){
		$db_post_ID = $item['comment_post_ID'];
		
		echo "<a href=\"http://nowmedev.com/sandbox/snapkin/wp-admin/post.php?post=$db_post_ID&action=edit\">".get_the_title($db_post_ID).'</a>';
	}
	/*
	* This is going to change the length of the content of every comment
	* This has been done to prevent extreme page lengths
	* @param array $item post content
	*/
	function column_comment_content($item){
		$db_comment_content = $item['comment_content'];
		
		$getlength = strlen( $db_comment_content );
		$thelength = 100;
			
		echo substr($db_comment_content, 0, $thelength);
		if ($getlength > $thelength) echo "[ ... }";
	}
	
	 /** ************************************************************************
	 * This method will figure out if the comment has an attachment and retrive
	 * if it has one
	 * Those that don't will appear under wordpress (all) comments
	 * 
     * @param array $item post title
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
	function column_comment_attachment($item){
		
		$comment_id = $item['comment_ID'];
		
		if( true == get_comment_meta( $comment_id, 'comment_pub' ) ) {
			
			// ...get the comment image meta
				$comment_pub = get_comment_meta( $comment_id, 'comment_pub', true );
				
				if($comment_pub['url']){
					// ...and render it in a paragraph element appended to the comment
					$comment_att = '<a class="comment-pub" href="' . $comment_pub['url'] . '" target="_blank">';
						$comment_att .= '<img width="50" height="50" src="' . $comment_pub['url'] . '" alt="" />';
					$comment_att .= '</a><!-- /.comment-pub -->';	
				}
				return $comment_att;
			
		}
	}
        
    /** ************************************************************************
     * Check current status
     **************************************************************************/
     function column_comment_approved($item){
		global $comment_status;
	 	$comment_status = $item['comment_approved']; 
		
			switch ( $comment_status ) {
				
				case 0 :
					$comment_status = 'Unapproved';
				break;
				
				case 1 :
					$comment_status = 'Approved';
				break;
				
				case 'spam':
					$comment_status = 'Spam';
				break;
				
			}
		
		return $comment_status;
	 }
	
        
    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. Every time the class
     * needs to render a column, it first looks for a method named 
     * column_{$column_title} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     * 
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     * 
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_title($item){
        
        //Return the title contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ $item['comment_date'],
            /*$4%s*/ $item['comment_author'],
            /*$4%s*/ $item['comment_approved'],
            /*$2%s*/ $item['comment_ID'],
            /*$3%s*/ $item['comment_post_ID'],
            /*$5%s*/ $item['comment_author_email'],
            /*$6%s*/ $item['comment_author_IP'],
            /*$7%s*/ $item['comment_content']
        );
    }
    
    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['comment_ID']                //The value of the checkbox should be the record's id
        );
    }
    
    
    /** ************************************************************************
	 * This changes columns order
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value 
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     * 
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
	 
	
	
	 
    function get_columns(){
        $columns = array(
        'cb'=> '<input type="checkbox" />', //Render a checkbox instead of text
	    'comment_date'=>__('Date'),
	    'comment_author'=>__('Author'),
	    'comment_approved'=>__('Status'),
	    'comment_attachment'=>__('Image Attachment'),
	    'comment_post_ID'=>__('Post Name'),
	    'comment_author_email'=>__('Author Email'),
	    'comment_author_IP'=>__('Author IP'),
	    'comment_content'=>__('Content')
        );
        return $columns;
    }
    
    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here. This should return an array where the 
     * key is the column that needs to be sortable, and the value is db column to 
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     * 
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            'comment_date'     => array('comment_date',false),     //true means its already sorted
            'comment_approved'     => array('comment_approved',true),
            'comment_post_ID'     => array("comment_post_ID",false), 
            'comment_author'    => array('comment_author',false),
            'comment_author_email'    => array('comment_author_email',false),
            'comment_author_IP'  => array('comment_author_IP',false)
        );
        return $sortable_columns;
    }
    
    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     * 
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     * 
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
		
        $actions = array(
            'approved'    => 'Approve',
            'unapprove'    => 'Unapprove',
            'delete'    => 'Delete'
        );
        return $actions;
    }
    
    
    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     * 
     * @see $this->prepare_items()
     **************************************************************************/
    function process_bulk_action() {
		
		
		// before doing anything make sure that nonce is valid 
		$nonce = wp_create_nonce('comment_pub_field');
		$action = 'comment_pub_action';
		
		if (! wp_verify_nonce($nonce, 'comment_pub_field') ){
			
				 wp_die('Security verification failed in Comment Pub.');
			
			}else{
				
				global $wpdb;
				
				//first get the comment id's from the database to be able to do anything
				
				$doaction = $this->current_action();
				
				if( isset( $_REQUEST['comment'] ) ){
				
					$action_comment_ids = $_REQUEST['comment'];
					
					foreach ( $action_comment_ids as $comment_id ) { // Check the permissions on each
						if ( !current_user_can( 'edit_comment', $comment_id ) )
							continue;
				
						switch ( $doaction ) {
							case 'approved' :
								wp_set_comment_status( $comment_id, 'approve' );
								break;
							
							case 'unapprove' :
								wp_set_comment_status( $comment_id, 'hold' );
								break;
							
							case 'delete' :
								wp_set_comment_status( $comment_id, 'trash' );
								break;
						
						}
						
					}//close for each
				
				}//close if actions is not empty
			
			}
        
    }
    
    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     * 
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items() {
		
        /**
         * First, lets decide how many records per page to show
         */
        $per_page = $this->get_items_per_page('nmg_comments_per_page', 10);
	
		
        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        
		$columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        /**
         * REQUIRED. Finally, we build an array to be used by the class for column 
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
		 
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         * query db here
         */
		global $wpdb;
		
			$this->process_bulk_action();
			
        /**
         * Instead of querying a database, we're going to fetch the example data
         * property we created for use in this plugin. This makes this example 
         * package slightly different than one you might build on your own. In 
         * this example, we'll be using array manipulation to sort and paginate 
         * our data. In a real-world implementation, you will probably want to 
         * use sort and pagination data to build a custom query instead, as you'll
         * be able to use your precisely-queried data immediately.
         */
        $data = $this->create_query();
                
        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently 
         * looking at. We'll need this later, so you should always include it in 
         * your own package classes.
         */
        $current_page = $this->get_pagenum();
        
        /**
         * REQUIRED for pagination. Let's check how many items are in our data array. 
         * In real-world use, this would be the total number of items in your database, 
         * without filtering. We'll need this later, so you should always include it 
         * in your own package classes.
         */
        $total_items = count($data);
       // print_r($data);
        
        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to 
         */
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        
        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $this->items = $data;
        
        
        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
     
	
}


$wp_comment_table = new comment_pub_table();
$wp_comment_table->prepare_items();

	
?>

	<div class="wrap">
        
        <div id="icon-users" class="icon32"><br/></div>
        <h2>Comment Pub</h2>
        
        <?php 
		// Make sure that order and order by are set before making them into a variable
		if( isset( $_GET["orderby"] ) && isset( $_GET["order"] ) ){
			
			$sortingBy = $_GET["orderby"];
			$sortOrder = $_GET["order"];
			
		}
		
		$status_links = '<ul class="subsubsub">';
		
		
		// add current class to "all" link only when order and order by  are set
		
		if( !isset( $_GET["orderby"] ) && !isset( $_GET["order"] ) ){
			$status_links .= '<li class="all"><a class="current" href="edit-comments.php?page=comment_pub_sorting">All</a></li>&nbsp;|&nbsp;';
		}else{
			$status_links .= '<li class="all"><a href="edit-comments.php?page=comment_pub_sorting">All</a></li>&nbsp;|&nbsp;';
		}
		
		// make sure that order is set before actually adding current class to "Pending" & "Approved" links
		// else just return the "Pending" & "Approved" links
		if( isset( $sortOrder ) ){
			
			if($sortOrder == 'asc' ){
				$status_links .= '<li class="moderated"><a class="current" href="edit-comments.php?page=comment_pub_sorting&orderby=comment_approved&order=asc">Pending</a>&nbsp;|&nbsp;';
			}else{
				$status_links .= '<li class="moderated" ><a href="edit-comments.php?page=comment_pub_sorting&orderby=comment_approved&order=asc">Pending</a>&nbsp;|&nbsp;';
			}
			
			if( $sortOrder == 'desc' ){
				$status_links .= '&nbsp;<li class="approved"><a class="current" href="edit-comments.php?page=comment_pub_sorting&orderby=comment_approved&order=desc">Approved</a>';
			}else{
				$status_links .= '&nbsp;<li class="approved"><a href="edit-comments.php?page=comment_pub_sorting&orderby=comment_approved&order=desc">Approved</a>';
			}
			
		}else{
				$status_links .= '<li class="moderated" ><a href="edit-comments.php?page=comment_pub_sorting&orderby=comment_approved&order=asc">Pending</a>&nbsp;|&nbsp;';
				$status_links .= '&nbsp;<li class="approved"><a href="edit-comments.php?page=comment_pub_sorting&orderby=comment_approved&order=desc">Approved</a>';
			}
		
		$status_links .= '</ul>';
		echo apply_filters( 'comment_status_links', $status_links );
		
		?>

    <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="comment-filter" method="GET">
            
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
			
			<?php $wp_comment_table->search_box( __( 'Search Comment Pub' ), 'comment_pub' ); ?>
                
            <input type="hidden" name="comment_status" value="<?php if( $comment_status == ''){ $comment_status = 'all'; }; ?>" />
            <input type="hidden" name="pagegen_timestamp" value="<?php echo esc_attr(current_time('mysql', 1)); ?>" />
            
            <?php if ( isset($_REQUEST['paged']) ) { ?>
                <input type="hidden" name="paged" value="<?php echo esc_attr( absint( $_REQUEST['paged'] ) ); ?>" />
            <?php } ?>
            <!-- Now we can render the completed list table -->
            <?php $wp_comment_table->display(); ?>
        </form>
        
	</div>