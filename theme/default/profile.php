<?php
/**
 * Use dasboard template
 *
 * User pp landing page
 *
 * @link http://wp3.in
 * @since 0.0.1
 *
 * @package ProfilePress
 */

?>
<?php include pp_get_theme_location('user-card.php'); ?>
<div id="pp-page" class="es-main-wrapper clearfix" data-id="<?php echo get_current_user_id(); ?>">
	<div class="pp-head clearfix">

	</div>
	<div class="row">
		<div class="left-navbar col-md-3">
			<?php pp_navigation() ?>
		</div>
		<div class="pp-page_c col-md-9">
			<?php
                pp_blocks(); 
            ?>
		</div>
	</div>
</div>