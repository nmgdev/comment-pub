<?php
/*
Template Name: GuestBook - Comment Pub
*/
get_header(); ?>

		<div id="primary">
			<div id="content" role="main">
				<?php while ( have_posts() ) : the_post(); ?>

                        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                            <header class="entry-header">
                                <h1 class="entry-title"><?php the_title(); ?></h1>
                            </header><!-- .entry-header -->
                        
                            <div class="entry-content">
                                <?php the_content(); ?>
                                <?php wp_link_pages( array( 'before' => '<div class="page-link"><span>' . __( 'Pages:', 'comment_pub' ) . '</span>', 'after' => '</div>' ) ); ?>
                            </div><!-- .entry-content -->
                            <footer class="entry-meta">
                                <?php edit_post_link( __( 'Edit', 'comment_pub' ), '<span class="edit-link">', '</span>' ); ?>
                            </footer><!-- .entry-meta -->
                        </article><!-- #post-<?php the_ID(); ?> -->

					<?php 
					
						$comment_form = ABSPATH .'wp-content/plugins/comment-pub/comments_pub_comments.php';
						include_once $comment_form;
					?>


				<?php endwhile; // end of the loop. ?>

			</div><!-- #content -->
		</div><!-- #primary -->

<?php get_footer(); ?>