<?php

/**
 * Common theme and template functions
 *
 * @link http://wp3.in
 * @since 0.0.1
 * @package ProfilePress
 */

/**
 * Output pp navigation
 * Extract menu from registered user pages
 * @return void
 * @since 2.0.1
 */
function pp_navigation() {
    global $user_pages, $pp_navigation;
    
    $userid             = pp_get_current_user();
    $user_page          = get_query_var('pp_page');
    $user_page          = $user_page ? $user_page : 'pp';
    
    $menus              = array();
    
    foreach ($user_pages as $k                  => $args) {
        $link               = pp_get_link_to($k, $userid);
        $class              = $user_page == $k ? 'active' : '';
        $menus[$k]                    = array(
            'title'                    => $args['title'],
            'link'                    => $link,
            'order'                    => 10,
            'class'                    => $class
            );
    }
    
    /**
     * FILTER: pp_navigation
     * filter is applied before showing user menu
     * @var array
     * @since  0.0.1
     */
    $pp_navigation = apply_filters('pp_navigation', $menus);
    
    $pp_navigation = wp3_sort_array_by_order($pp_navigation);
    
    include (pp_get_theme_location('navigation.php'));
}

/**
 * Output current pp page
 * @return void
 * @since 2.0.0-beta
 */
function pp_page() {
    global $user_pages, $wp;

    $current_page = get_query_var('pp_page');
    
    if ($current_page == '') {
        $current_page = 'profile';
    }
    
    if (pp_get_current_user() == 0) {
        echo '<div class="login-message">' . sprintf(__('Please %slogin%s to see access your profile', 'pp') , '<a href="' . wp_login_url(pp_get_link_to('profile')) . '">', '</a>') . '</div>';
        return;
    }
    
    if (isset($user_pages[$current_page]['func'])) {
        call_user_func($user_pages[$current_page]['func']);
    } 
    else {
        include (pp_get_theme_location('404.php'));
    }
}

function pp_about_me() {
    return pp_current_user_meta('description', true);
}

function pp_user_name() {
    $pp_user_obj = pp_current_user_object();
    
    $user_name = $pp_user_obj->data->user_login;
    
    return apply_filters('pp_user_name', $user_name);
}

function pp_display_all_metas() {
    $user_id          = pp_get_current_user();
    $pp_user_obj = pp_current_user_object();
    
    $fields           = pp_get_all_fields();
    
    $default_fields   = array();
    
    $default_fields['display_name']                  = array(
        'label'                  => __('Disply name', 'pp') ,
        'value'                  => $pp_user_obj->data->display_name
        );
    $default_fields['name']                  = array(
        'label'                  => __('Name', 'pp') ,
        'value'                  => pp_current_user_meta('first_name', true) . ' ' . pp_current_user_meta('last_name', true)
        );
    $default_fields['nickname']                  = array(
        'label'                  => __('Nick name', 'pp') ,
        'value'                  => pp_current_user_meta('nickname', true)
        );
    $default_fields['description']                  = array(
        'label'                  => __('Description', 'pp') ,
        'value'                  => pp_current_user_meta('description', true)
        );
    
    $field_groups     = get_terms('pp_group', array(
        'hide_empty'                  => true
        ));
    
    // for ungroup fields
    $field_groups[]                  = false;
    
    echo '<h3 class="meta-group-title">' . __('Basic', 'pp') . '</h3>';
    echo '<div id="meta_group_basic" class="meta-group"><div class="meta-group-fields">';
    foreach ($default_fields as $k => $value) {
        if (!empty($value) || is_my_pp()) {
            ?>  
            <div data-cont="field_<?php
            echo $k
            ?>" class="meta-field">
            <span class="meta-fields-label"><?php
                echo $value['label'] ?></span>
                <div class="meta-values">

                 <?php
                 if (pp_user_can_edit_field($user_id)): ?>
                 <a class="btn-edit-pp-field" href="#" data-action="edit_pp_field" data-query="field=<?php
                 echo $k
                 ?>&pp_ajax_action=edit_pp_field"><?php
                 _e('Edit', 'pp') ?></a>
                 <?php
                 endif; ?>

                 <div class="user-field-form">
                  <span class="meta-field-value"><?php
                    echo $value['value'] ?></span>
                </div>
            </div>
        </div>
        <?php
    }
}
echo '</div></div>';

foreach ($field_groups as $group) {
    if (pp_user_can_see_group($group->term_id)) {
        $fields = pp_get_fields_by_group($group->slug);

        if (!$fields) {
            return;
        }

        echo '<div id="meta_group_' . $group->slug . '" class="meta-group">';

        if (!$group) {
            echo '<h3 class="meta-group-title">' . __('Non grouped', 'pp') . '</h3>';
        } 
        else {
            echo '<h3 class="meta-group-title">' . $group->name . '</h3>';
        }

        echo '<div class="meta-group-fields">';

        foreach ($fields as $field) {
            $options = pp_get_field_options($field->ID);
            $value   = pp_current_user_meta('__pp_field_' . $field->ID, true);

            if (!empty($value) || is_my_pp()) {
                echo '<div data-cont="field_' . $field->ID . '" class="clearfix">';

                pp_field_type_view($field, $user_id);

                echo '</div>';
            }
        }

        echo '</div></div>';
    }
}
}

/**
 * Return the current page title
 * @return string
 */
function pp_page_title() {
    global $user_pages;
    
    $userid       = pp_get_current_user();
    $user_page    = get_query_var('pp_page');
    $user_page    = $user_page != '' ? sanitize_text_field($user_page) : 'profile';

    $title_prefix = $userid == get_current_user_id() ? __('My ', 'pp') : pp_display_name($userid) . __('\'s ', 'pp');
    
    if (isset($user_pages[$user_page]['title'])) {
        return $title_prefix . $user_pages[$user_page]['title'];
    }
    
    return __('Page not found', 'pp');
}

/**
 * user about card
 * @return void
 */
function pp_user_about_card($user_id = false) {

    if (!$user_id) {
        $user_id = pp_get_current_user();
    }
    
    $things  = array();
    
    $things['name']         = '<h2 class="user-card-name">' . pp_display_name() . '<span class="user-name">@' . pp_user_name() . '</span></h2>';
    $things['about_me']         = '<div class="bio">' . pp_about_me() . '</div>';
    
    $fields  = pp_fields_by_location('card_about');
    
    if ($fields) {
        foreach ($fields as $field) {
            $options = pp_get_field_options($field->ID);
        }
        $value   = get_user_meta($user_id, '__pp_field_' . $field->ID, true);
        
        if ($options->__field_type == 'text_url') {
            $value   = '<a href="' . $value . '" rel="nofollow">' . $field->post_title . '</a>';
        }
        
        $things['__pp_field_' . $field->ID]         = $value;
    }
    
    /**
     * FILTER: pp_user_about_card
     * Used to filter user about card
     */
    $things  = apply_filters('pp_user_about_card', $things, $user_id);
    
    if (!empty($things) && is_array($things)) {
        foreach ($things as $name     => $display) {
            echo $display;
        }
    }
}

/**
 * user about card
 * @return void
 */
function pp_user_link_card($user_id  = false) {

    if (!$user_id) $user_id  = pp_get_current_user();
    
    $things   = array();
    
    $user_url = get_user_meta($user_id, 'url', true);
    
    if (!empty($user_url)) $things['url']          = '<a class="pp-btn btn-my-website" href="' . $user_url . '" rel="nofollow">' . __('My website', 'pp') . '</a>';
    
    $user_obj = pp_current_user_object();
    
    $things['memeber_for']          = '<span class="user-pp-hit ppicon-clock">' . sprintf(__('Memeber for %s', 'pp') , human_time_diff(strtotime($user_obj->user_registered) , current_time('timestamp'))) . '</span>';
    
    $views    = get_user_meta($user_id, '__pp_views', true);
    $things['hit']          = '<span class="user-pp-hit ppicon-eye">' . sprintf(_n('1 profile view', '%d profile views', $views, 'pp') , $views) . '</span>';
    
    $fields   = pp_fields_by_location('card_links');
    
    if ($fields) {
        foreach ($fields as $field) {
            $options  = pp_get_field_options($field->ID);
        }
        $value    = get_user_meta($user_id, '__pp_field_' . $field->ID, true);
        
        if ($options->__field_type == 'text_url') {
            $value    = '<a href="' . $value . '" rel="nofollow">' . $field->post_title . '</a>';
        }
        
        $things['__pp_field_' . $field->ID]          = $value;
    }
    
    /**
     * FILTER: pp_user_link_card
     * Used to filter user about card
     */
    $things   = apply_filters('pp_user_link_card', $things, $user_id);
    
    if (!empty($things) && is_array($things)) {
        foreach ($things as $name    => $display) {
            echo $display;
        }
    }
}

function pp_blocks($user_id = false) {
    do_action('pp_blocks', $user_id);
}

function pp_avatar_upload_form() {
    if (is_my_pp()) {
        ?>
        <form method="POST" enctype="multipart/form-data" data-action="pp_upload_form">
            <div class="pp-btn pp-upload-o">
                <span><?php
                    _e('Upload avatar', 'pp'); ?></span>
                    <input type="file" name="thumbnail" class="pp-upload-input" data-action="pp-upload-field">
                </div>
                <input type='hidden' value='<?php
                echo wp_create_nonce('pp_upload'); ?>' name='__nonce' />
                <input type="hidden" name="pp_ajax_action" id="action" value="pp_upload">
                <input type="hidden" name="action" id="action" value="pp_ajax">
            </form>
            <?php
        }
    }

/**
 * Output favorite btn HTML
 * @return string
 * @since 0.0.2
 */
function wp3_favorite_btn($post_id    = false) {
    if(is_profilepress())
        return;
    
    if (!$post_id) $post       = get_the_ID();
    
    $total_favs = wp3_post_favorites_count($post_id);
    $favorited  = wp3_is_user_favorited($post_id);
    
    $nonce      = wp_create_nonce('favorite_' . $post_id);
    $title      = (!$favorited) ? (__('Add to favorite', 'pp')) : (__('Remove favorite', 'pp'));
    
    return '<a id="favorite_' . $post_id . '" href="#" class="ppicon-star pp-btn favorite-btn' . ($favorited ? ' active ' : ' ') . '" data-query="pp_ajax_action=favorite&p_id=' . $post_id . '&__nonce=' . $nonce . '" data-action="favorite"><span>' . $title . '</span></a>';
}

function pp_user_favorite_cpt_links() {    

    $cpts = pp_opt('favorite_cpt');
    $active = get_query_var('pp_cpt');
    
    echo '<a '.($active == '' ? ' class="active" ' : '').'href="' . pp_get_link_to(array( 'pp_page' => 'favorites' )) . '">' . __('All', 'pp') . '</a>';
    foreach ($cpts as $k => $value) echo '<a '.($active == $k ? ' class="active" ' : '').'href="' . pp_get_link_to(array( 'pp_page' => 'favorites', 'pp_cpt' => $k )) . '">' . ucfirst($k) . '</a>';
}
