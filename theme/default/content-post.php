<?php
/**
 * posts loop template
 *
 * @link http://wp3.in
 * @since 0.0.1
 *
 * @package ProfilePress
 */

?>
<div class="user-post clearfix">
	<a class="user-post-comment" href="<?php comments_link(); ?>" title="<?php _e('Responce on this post', 'pp') ?>"><?php comments_number( '0', '1', '%d'); ?></a>
	<?php if(get_query_var('pp_cpt') == '') :  $type = get_post_type() ?>
		<span class="user-post-type <?php echo $type ?>"><?php echo $type ?></span>
	<?php endif; ?>
	<a href="<?php the_permalink() ?>" class="user-post-title"><?php the_title(); ?></a>
	<time><?php echo get_the_date( 'M d \'y' ); ?></time>
</div>