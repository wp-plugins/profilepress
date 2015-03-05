<?php
/**
 * Use card
 *
 * Display the user details in pp page
 *
 * @link http://wp3.in
 * @since 0.0.2
 *
 * @package ProfilePress
 */
?>
<div id="user-card" class="row">
	<div class="col-md-3">
		<div class="pp-main-avatar">
			<div class="block-center">
				<?php echo get_avatar(pp_get_current_user(), pp_opt('main_avatar_size')); ?>
				<?php pp_avatar_upload_form() ?>
			</div>
		</div>
	</div>
	<div class="col-md-9">
		<div class="col-md-8 about">
			<?php pp_user_about_card() ?>
		</div>
		<div class="col-md-4 user-links">
			<?php pp_user_link_card() ?>
		</div>
	</div>	
</div>