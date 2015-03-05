<?php
/**
             * ProfilePress field type URL form template
             */
$value = get_user_meta($user_id, '__pp_field_'.$field->ID, true);

?>
<div class="field-label">
	<label class="meta-fields-label" for="__pp_field_<?php echo $field->ID ?>"><?php echo $field->post_title ?></label>
</div>
<div class="field-value">
	<input id="__pp_field_<?php echo $field->ID ?>" name="__pp_field_<?php echo $field->ID ?>" class="field-input <?php echo $options->__field_type ?><?php echo !empty($options->__field_input_class) ? ' '.$options->__field_input_class : '' ?>" value="<?php echo $value ? $value : $options->__field_default_value ?>" placeholder="<?php echo $options->__field_placeholder ?>" /> 
</div>
