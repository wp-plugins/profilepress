<?php

/**
 * ProfilePress options page
 * @link http://wp3.in/anspress
 * @since 0.0.1
 * @package ProfilePress
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

$settings = pp_opt();

if (!isset($_REQUEST['settings-updated'])) {
    $_REQUEST['settings-updated'] = false;
}
// This checks whether the form has just been submitted. ?>

<div class="wrap">
	<?php screen_icon(); echo '<h2>'.__('ProfilePress Options').'</h2>';
    // This shows the page's name and an icon if one has been provided ?>
			
	<?php if (false !== $_REQUEST['settings-updated']) : ?>
	<div class="updated fade"><p><strong><?php _e('Options saved', 'ap'); ?></strong></p></div>
	<?php endif; // If the form has just been submitted, this shows the notification ?>
	
	<div class="anspress-options">
		<div class="option-nav-tab">
			<?php pp_options_nav(); ?>
		</div>
		<div class="pp-group-options">
			<?php pp_option_group_fields(); ?>
		</div>
	</div>
	
</div>