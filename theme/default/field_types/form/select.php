<?php
    $value = get_user_meta($user_id, '__pp_field_'.$field->ID, true);
?>

<div class="field-label">
	<label class="meta-fields-label" for="__pp_field_<?php echo $field->ID ?>"><?php echo $field->post_title ?></label>
</div>
<div class="field-value">
	<select name="__pp_field_<?php echo $field->ID ?>" id="__pp_field_<?php echo $field->ID ?>" class="field-input <?php echo $options->__field_type ?><?php echo !empty($options->__field_input_class) ? ' '.$options->__field_input_class : '' ?>">
		<?php 
			
            $value = ($value ? $value : $options->__field_default_value);

            foreach ($options->__field_options as $option) echo '<option value="'.$option['key'].'" '.selected($value, $option['key'], false).'>'.$option['value'].'</option>'; ?>
	</select>
</div>
