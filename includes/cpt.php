<?php
/**
     * ProfilePress fields CPT
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

class PP_CPT
{

    /**
     * Initialize the class
     */
    public function __construct()
    {
        //Register Custom Post types and taxonomy
        add_action('init', array($this, 'register_cpt'), 0);
        add_action('init', array($this, 'register_taxonomy'), 0);
        add_action('pp_group_edit_form_fields', array($this, 'pp_group_edit_fields'));
        add_action('pp_group_add_form_fields', array($this, 'pp_group_fields'));
        
        // save extra category extra fields hook
        add_action('edited_pp_group', array($this, 'save_pp_group'));
        add_action('created_pp_group', array($this, 'save_pp_group'));
        add_filter('manage_edit-pp_group_columns', array($this, 'add_pp_group_columns'));
        add_filter('manage_pp_group_custom_column', array($this, 'add_pp_group_column_content'), 10, 3);

    }

    /**
     * Register ProfilePress fields CPT
     * @return void
     * @since 0.0.1
     */
    public function register_cpt() {
        
        // question CPT labels
        $labels = array(
            'name'              => _x('ProfilePress fields', 'Post Type General Name', 'pp'),
            'singular_name'     => _x('ProfilePress field', 'Post Type Singular Name', 'pp'),
            'menu_name'         => __('ProfilePress fields', 'pp'),
            'parent_item_colon' => __('Parent pp field:', 'pp'),
            'all_items'         => __('All pp fields', 'pp'),
            'view_item'         => __('View pp field', 'pp'),
            'add_new_item'      => __('Add new pp field', 'pp'),
            'add_new'           => __('New pp field', 'pp'),
            'edit_item'         => __('Edit pp field', 'pp'),
            'update_item'       => __('Update pp field', 'pp'),
            'search_items'      => __('Search pp field', 'pp'),
            'not_found'         => __('No pp field found', 'pp'),
            'not_found_in_trash' => __('No pp fields found in trash', 'pp')
        );
        
        /**
         * FILTER: pp_pp_field_cpt_labels
         * filter is called before registering pp_field CPT
         */
        $labels = apply_filters('pp_pp_field_cpt_labels', $labels);

        // question CPT arguments
        $args   = array(
            'label' => __('ProfilePress fields', 'pp'),
            'description' => __('Fields for pp', 'pp'),
            'labels' => $labels,
            'supports' => array(''),
            'hierarchical' => false,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_nav_menus' => false,
            'show_in_admin_bar' => true,
            'menu_icon' => 'ppicon-pp_logo',
            'can_export' => true,
            'has_archive' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'capability_type' => 'post',
            'rewrite' => false,
            'query_var' => 'pp_field',
        );

        /**
         * FILTER: pp_pp_field_cpt_args 
         * filter is called before registering pp_field CPT
         */
        $args = apply_filters('pp_pp_field_cpt_args', $args);

        // register CPT question
        register_post_type('pp_field', $args);
    }

    public function register_taxonomy() {
        $labels = array(
            'name'              => _x('ProfilePress field group', 'taxonomy general name', 'pp'),
            'singular_name'     => _x('ProfilePress field group', 'taxonomy singular name', 'pp'),
            'search_items'      => __('Search pp field group', 'pp'),
            'all_items'         => __('All pp field group', 'pp'),
            'parent_item'       => __('Parent pp field group', 'pp'),
            'parent_item_colon' => __('Parent pp field group:', 'pp'),
            'edit_item'         => __('Edit pp field group', 'pp'),
            'update_item'       => __('Update pp field group', 'pp'),
            'add_new_item'      => __('Add new pp field group', 'pp'),
            'new_item_name'     => __('New pp field group', 'pp'),
            'menu_name'         => __('ProfilePress field group', 'pp'),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => 'pp_group',
            'rewrite'           => false,
        );

        register_taxonomy('pp_group', array('pp_field'), $args);
    }

    public function pp_group_edit_fields($tax) {
        $t_id = $tax->term_id;
        $tax_meta = get_option("pp_group_$t_id");
    ?>
            
        <tr class="form-field ap-label-visibility-field">
            <th>
                <label for="pp_group_field"><?php _e('Visibility', 'pp'); ?></label>
            </th>
            <td>
                <select type="text" name="pp_group[visibility]" id="pp_group_field">  
                        <?php 
                            foreach (pp_visibilities() as $k => $type) {
                                                            echo '<option value="'.$k.'" '.selected($tax_meta['visibility'], $k, false).'>'.$type.'</option>';
                            }
                        ?>
                </select> 
                <br />
            </td>
        </tr>
            
        
    <?php
    }

    public function pp_group_fields($tax) {
        $t_id = $tax->term_id;
        $tax_meta = get_option("pp_group_$t_id");

    ?>
            
        <tr class="form-field ap-label-visibility-field">
            <th>
                <label for="pp_group_field"><?php _e('Visibility', 'pp'); ?></label>
            </th>
            <td>
                <select type="text" name="pp_group[visibility]" id="pp_group_field">  
                        <?php 
                            foreach (pp_visibilities() as $k => $type) {
                                                            echo '<option value="'.$k.'" '.selected($tax_meta['visibility'], $k, false).'>'.$type.'</option>';
                            }
                        ?>
                </select> 
                <br />
                <br />
            </td>
        </tr>           
        
    <?php
    }

    // save extra category extra fields callback function
    public function save_pp_group($term_id) {
        if (isset($_POST['pp_group'])) {
            $t_id = $term_id;
            $tax_meta = get_option("pp_group_$t_id");
            $tax_keys = array_keys($_POST['pp_group']);
                foreach ($tax_keys as $key) {
                if (isset($_POST['pp_group'][$key])) {
                    $tax_meta[$key] = sanitize_text_field($_POST['pp_group'][$key]);
                }
            }
            //save the option array
            update_option("pp_group_$t_id", $tax_meta);
        }
    }

    function add_pp_group_columns($columns) {
        $columns['visibility'] = __('Visibility', 'pp');
        return $columns;
    }
    
     
    function add_pp_group_column_content($content, $column_name, $term_id) {
        $visibility = pp_get_label_visibility($term_id);
        $content .= $visibility;
        return $content;
    }

   
}
