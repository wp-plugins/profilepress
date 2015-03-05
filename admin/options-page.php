<?php
class PP_Options_Page
{
    static public function add_option_groups() {
        $settings = pp_opt();
        
        // Register general settings
        pp_register_option_group('general', __('General', 'pp') , array(
            array(
                'name'           => 'pp_opt[base_page]',
                'label'           => __('Base page', 'pp') ,
                'description'           => __('Select page for displaying anspress.', 'pp') ,
                'type'           => 'page_select',
                'value'           => @$settings['base_page'],
            ) ,
            array(
                'name'           => 'pp_opt[author_credits]',
                'label'           => __('Hide author credits', 'pp') ,
                'description'           => __('Show your love by showing link to AnsPress project site.', 'pp') ,
                'type'           => 'checkbox',
                'value'           => @$settings['author_credits'],
                'order'           => '1',
            ) ,
            
            array(
                'name'           => 'pp_opt[allow_private_posts]',
                'label'           => __('Allow private posts', 'pp') ,
                'description'           => __('Allow users to create private question and answer.', 'pp') ,
                'type'           => 'checkbox',
                'value'           => @$settings['allow_private_posts'],
            ) ,
        ));
        
        $cpt_group = array();
        
        foreach (get_post_types(array('public' => true )) as $post_type) {
            if ($post_type != 'attachment' && $post_type != 'revision' && $post_type != 'nav_menu_item') {
                $cpt_group[$post_type]['label']           = $post_type;
                $cpt_group[$post_type]['name']           = 'pp_opt[favorite_cpt][' . $post_type . ']';
                $cpt_group[$post_type]['value']           = @$settings['favorite_cpt'][$post_type];
            }
        }
        
        // Favorite
        pp_register_option_group('favorite', __('Favorite', 'pp') , array(
            
            array(
                'name' => 'pp_opt[favorite_cpt]',
                'label' => __('Post type user can favorite', 'pp') ,
                'description' => __('Check the cpt user can add to their favorite list.', 'pp') ,
                'type' => 'checkbox',
                'value' => @$settings['favorite_cpt'],
                'group' => $cpt_group,
            )
        ));
    }
}
