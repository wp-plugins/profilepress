<?php
/**
 * My answer
 *
 * User answers list
 *
 * @link http://wp3.in
 * @since 0.0.1
 *
 * @package ProfilePress
 */

?>

<div id="my-answers-page" class="clearfix" data-id="<?php echo get_current_user_id(); ?>">
	<div class="row">
		<div class="left-navbar col-md-3">
			<?php pp_navigation() ?>
		</div>
		<div class="pp-page_c col-md-9">
			<?php
			$args = array(
				'post_type'	=> 'answer',
				'author'	=> pp_get_current_user(),
				);

			$posts = new WP_Query($args);

			if($posts->have_posts()){
				?>
				<h3 class="pp-list-head">
					<?php the_title() ?>
					<span class="user-post-count">(<?php echo count_user_posts( pp_get_current_user() , 'answer' ) ?>)</span>
				</h3>
				<?php
				while ( $posts->have_posts() ) : $posts->the_post();
				include pp_get_theme_location('content-post.php');
				endwhile;
			}else{
				_e('No question answered by this user.', 'pp');
			}

			wp_reset_postdata();
			?>
		</div>
	</div>
</div>