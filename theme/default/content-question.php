<?php
/**
 * posts loop template
 *
 * @link http://wp3.in
 * @since 0.0.1
 *
 * @package ProfilePress
 */
$type = get_post_type( );
?>
<div class="user-post question clearfix">
	<a class="user-qa-type <?php echo $type ?>" href="<?php the_permalink() ?>"><?php echo $type == 'question' ? __('Q', 'pp') : __('A', 'pp') ?></a>
	<span class="user-qa-vote" title="<?php _e('Votes', 'pp') ?>"><?php echo ap_net_vote_meta(); ?></span>
	<a href="<?php the_permalink() ?>" class="user-post-title"><?php the_title(); ?></a>
	<time><?php echo get_the_date( 'M d \'y' ); ?></time>
</div>