<?php
/**
     * User custom fields
     *
     * @package  	ProfilePress
     * @author      Rahul Aryan <rah12@live.com>
     * @license  	http://www.opensource.org/licenses/gpl-license.php GPL v3.0 (or later)
     * @link     	http://wp3.in
     */


/**
 * Default pp field types
 * @return array
 */
function pp_field_types()
{
    $default_types = array(
        'text'             => __('Text', 'pp'),        
        'select'           => __('Select', 'pp'),
        'checkbox'         => __('Checkbox', 'pp'),
        'radio'            => __('Radio', 'pp'),
        'textarea'         => __('Textarea', 'pp'),
        'upload'           => __('Upload', 'pp'),
        'editor'           => __('Editor', 'pp'),
        'text_url'         => __('URL', 'pp'),
        );

    /** 
     * FILTER: pp_field_types
     * Can be used to override the default field types.
     */
    return apply_filters('pp_field_types', $default_types);
}

/**
 * Default pp visibility
 * @return array
 */
function pp_visibilities()
{
    $default_visibilities = array(
        'public'            => __('Public', 'pp'),        
        'me'                => __('Only Me', 'pp'),        
        'registered'        => __('Registered', 'pp'),
        'admin'             => __('Administrator', 'pp'),
        );

    /** 
     * FILTER: pp_field_types
     * Can be used to override the default field types.
     */
    return apply_filters('pp_visibilities', $default_visibilities);
}

function pp_field_locations()
{
    $default = array(
        'card_about' => __('User card - about'),
        'card_links' => __('User card - links')
        );

    /** 
     * FILTER: pp_field_locations
     * Can be used to override the default field locations.
     */
    return apply_filters('pp_field_locations', $default);
}


/**
 * Return all options of a field
 * @param  int $field_id post ID
 * @return stdClass
 */
function pp_get_field_options($field_id)
{
    $metas = get_post_meta($field_id);
    $options = new stdClass;

    if (is_array($metas))
        foreach ($metas as $k => $meta)
            if (strpos($k, '__field_') !== false) {
                if (count($meta) > 1)
                    $options->$k = maybe_unserialize($meta);
                else
                    $options->$k = maybe_unserialize($meta[0]);
            }

            return $options;
        }

/**
 * Extracts custom pp fields from user form
 * @return integer | boolean
 */
function pp_get_pp_fields_post_form() {

    
    $fields = array();
    
    foreach ($_POST as $k => $f) {
        if (strpos($k, '__pp_field_') !== false)
            $fields[$k] = $f;
    }

    return $fields;
}

/**
 * Get field by id
 * @param  integer $field_id post id
 * @return object
 */
function pp_get_field($field_id)
{
    $field = get_post($field_id);

    if ($field->post_type != 'pp_field') {
        return false;
    }

    $options = pp_get_field_options($field_id);

    return (object) array_merge((array) $field, (array) $options);
}

/**
 * Get fields by group specified
 * @param  boolean | string $group_name Term slug or false for non grouped
 * @return array
 */
function pp_get_fields_by_group($group_name = false)
{
    $args = array(
        'post_type' => 'pp_field',
        'orderby' => 'ID',
        'tax_query' => array(
            array('taxonomy' => 'pp_group'),
            )
        );

    if (!$group_name) {
        $args['tax_query'][0]['operator'] = 'NOT EXISTS';
    } else {
        $args['tax_query'][0]['field'] = 'slug';
        $args['tax_query'][0]['terms'] = $group_name;
    }

    return get_posts($args);
}

/**
 * Return all fields
 * @return array
 */
function pp_get_all_fields()
{
    return get_posts(array('post_type' => 'pp_field', 'orderby' => 'ID'));
}


/**
 * @param string $location
 */
function pp_fields_by_location($location)
{
    return get_posts(array('post_type' => 'pp_field', 'orderby' => 'meta_value ID', 'meta_key' => '__pp_field_show', 'meta_value' => $location));
}

/**
 * Output form of a field type
 * @param  object $field
 * @return void
 */
function pp_field_type_form($field, $user_id)
{
    $options = pp_get_field_options($field->ID);
    $html = pp_get_theme_location('field_types/form/'.$options->__field_type.'.php');
    
    echo '<div class="pp-field-type-form clearfix">';

    if (file_exists($html)) {
        include $html;
    } else {
        printf(__('HTML template for %1$s does not exists. Create a file called field_types/form/%1$s.php in your active theme directory.', 'pp'), $options->__field_type);
    }

    echo '</div>';
}

/**
 * Output view of a field type
 * @param  object $field
 * @return void
 */
function pp_field_type_view($field, $user_id)
{
    $options = pp_get_field_options($field->ID);
    $html = pp_get_theme_location('field_types/view/'.$options->__field_type.'.php');
    
    echo '<div class="pp-field-type-view clearfix">';

    if (file_exists($html)) {
        include $html;
    } else {
        printf(__('HTML template for %1$s does not exists. Create a file called field_types/view/%1$s.php in your active theme directory.', 'pp'), $options->__field_type);
    }

    echo '</div>';
}

function pp_all_fields_form()
{
    $field_groups = get_terms('pp_group', array('hide_empty' => true));

    // for ungroup fields
    $field_groups[] = false;

    foreach ($field_groups as $group) {
        
        if (!$group) {
            echo '<h3>'.__('Non grouped', 'pp').'</h3>';
        } else {
            echo '<h3>'.$group->name.'</h3>';
        }

        $fields = pp_get_fields_by_group($group->slug);

        if ($fields) {
            foreach ($fields as $field) {
                $options = pp_get_field_options($field->ID);
            }
            pp_field_type_form($field);
        }
    }
}

/**
 * Sanitize and update pp field of user
 * @param  int          $user_id
 * @param  int          $field_id
 * @return boolean
 */
function pp_update_user_field($user_id, $field_id, $value)
{
    $field = pp_get_field($field_id);

    if ('text' == $field->__field_type || 'checkbox' == $field->__field_type || 'radio' == $field->__field_type || 'select' == $field->__field_type) 
    {
        $value = sanitize_text_field($value);
        return update_user_meta($user_id, '__pp_field_'.$field->ID, $value);
    } elseif ('textarea' == $field->__field_type) 
    {
        $value = esc_textarea($value);
        return update_user_meta($user_id, '__pp_field_'.$field->ID, $value);
    } elseif ('text_url' == $field->__field_type) 
    {
        $value = esc_url($value);
        return update_user_meta($user_id, '__pp_field_'.$field->ID, $value);
    } elseif ('editor' == $field->__field_type) 
    {
        $value = wp_kses($value, pp_allowed_editor_tags());
        return update_user_meta($user_id, '__pp_field_'.$field->ID, $value);
    }


    /**
     * FILTER: pp_update_user_field
     * Action to when saving a pp field
     */
    do_action('pp_update_user_field', $user_id, $field_id, $value);
}
