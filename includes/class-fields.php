<?php
/**
     * ProfilePress fields class
     *
     * @package   ProfilePress
     * @author    Rahul Aryan <admin@rahularyan.com>
     * @license   GPL-2.0+
     * @link      http://rahularyan.com
     * @copyright 2014 Rahul Aryan
     */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class PP_Custom_Fields
{
    public $user_fields;

    public function __construct()
    {

        add_action('show_user_pp', array($this, 'custom_fields'));
        add_action('edit_user_pp', array($this, 'custom_fields'));

        add_action('personal_options_update', array($this, 'save_custom_fields'));
        add_action('edit_user_pp_update', array($this, 'save_custom_fields'));
    }


    public function custom_fields($user)
    { 
        pp_all_fields_form($user->ID);
    }

    public function save_custom_fields($user_id)
    {
        $submitted_fields = pp_get_pp_fields_post_form();

        if (!pp_have_fields() || $submitted_fields === false) {
            return;
        }

        $fields = pp_get_all_fields();

        foreach ($fields as $f) {

            if (isset($submitted_fields['__pp_field_'.$f->ID])) {
                pp_update_user_field($user_id, $f->ID, $submitted_fields['__pp_field_'.$f->ID]);
            }
        }

    }

}