<?php
/**
 * ProfilePress field type Text form template
 */
$value = get_user_meta($user_id, '__pp_field_'.$field->ID, true);
$value = $value ? $value : $options->__field_default_value;
?>
<div class="meta-field">
	<span class="meta-fields-label"><?php echo $field->post_title ?></span>
	<div class="meta-values">
	
		<?php if (pp_user_can_edit_field(get_current_user_id())): ?>
			<a class="btn-edit-pp-field" href="#" data-action="edit_pp_field" data-query="field=<?php echo $field->ID ?>&pp_ajax_action=edit_pp_field"><?php _e('Edit', 'pp') ?></a>
		<?php endif; ?>
		
		<div class="user-field-form">
			<span class="meta-field-value"><a href="<?php echo $value ?>" rel="nofollow"><?php echo $value ?></a></span>
		</div>
	</div>
</div>