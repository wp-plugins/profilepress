<?php

/**
 * Common ProfilePress functions
 *
 * @link http://wp3.in
 * @since 0.0.1
 * @package ProfilePress
 */

/**
 * Get slug of base page
 * @return string
 * @since 2.0.0-beta
 */
function pp_base_page_slug() {
    $base_page = get_post(pp_opt('base_page'));
    
    $slug      = $base_page->post_name;
    
    return apply_filters('pp_base_page_slug', $slug);
}

/**
 * Retrive permalink to base page
 * @return string URL to ProfilePress base page
 * @since 2.0.0-beta
 */
function pp_base_page_link() {
    return get_permalink(pp_opt('base_page'));
}

function pp_theme_list() {
    $themes = array();
    $dirs   = array_filter(glob(PROFILE_THEME_DIR . '/*') , 'is_dir');
    foreach ($dirs as $dir) $themes[basename($dir) ]        = basename($dir);
    return $themes;
}

function pp_get_theme() {
    $option = pp_opt('theme');
    if (!$option) return 'default';
    
    return pp_opt('theme');
}

/**
 * Get location to a file
 * First file is looked inside active WordPress theme directory /anspress.
 * @param   string      $file       file name
 * @param   mixed       $plugin     Plugin path
 * @return  string
 * @since   0.1
 */
function pp_get_theme_location($file, $plugin        = false) {

    // checks if the file exists in the theme first,
    // otherwise serve the file from the plugin
    if ($theme_file    = locate_template(array(
        'anspress/' . $file
        ))) {
        $template_path = $theme_file;
} 
elseif ($plugin !== false) {
    $template_path = $plugin . '/theme/' . $file;
} 
else {
    $template_path = PROFILE_THEME_DIR . '/' . pp_get_theme() . '/' . $file;
}
return $template_path;
}

/**
 * Get url to a file
 * Used for enqueue CSS or JS
 * @param       string  $file
 * @param       mixed $plugin
 * @return      string
 * @since       2.0
 */
function pp_get_theme_url($file, $plugin       = false) {

    // checks if the file exists in the theme first,
    // otherwise serve the file from the plugin
    if ($theme_file   = locate_template(array(
        'pp/' . $file
        ))) {
        $template_url = get_template_directory_uri() . '/pp/' . $file;
} 
elseif ($plugin !== false) {
    $template_url = $plugin . 'theme/' . $file;
} 
else {
    $template_url = PROFILE_THEME_URL . '/' . pp_get_theme() . '/' . $file;
}
return $template_url;
}

/**
 * @param string $sub
 */
function pp_get_link_to($sub, $user_id    = false) {

    if (!$user_id) $user_id    = pp_get_current_user();
    
    $user_login = get_the_author_meta('user_login');
    
    $base       = rtrim(get_permalink(pp_opt('base_page')) , '/');
    
    if (get_option('permalink_structure') != '') {
        $args       = '/' . $user_login;
        
        if (!is_array($sub)) $args.= $sub ? '/' . $sub : '';
        elseif (is_array($sub)) {
            $args.= '/';
            
            if (!empty($sub)) foreach ($sub as $s) $args.= $s . '/';
        }
        
        $link = $base;
    } 
    else {
        $args = '&pp_user=' . $user_id;
        
        if (!is_array($sub)) $args.= $sub ? '&pp_page=' . $sub : '';
        elseif (is_array($sub)) {
            $args.= '';
            
            if (!empty($sub)) foreach ($sub as $k    => $s) $args.= '&' . $k . '=' . $s;
        }
        
        $link = $base;
    }
    
    return $link . $args;
}

/**
 * Append array to global var
 * @param  string   $key
 * @param  array    $args
 * @param string    $var
 * @return void
 * @since 2.0.0-alpha2
 */
function pp_append_to_global_var($var, $key, $args, $group = false) {
    if (!isset($GLOBALS[$var])) $GLOBALS[$var]       = array();
    
    if (!$group) $GLOBALS[$var][$key]       = $args;
    else $GLOBALS[$var][$group][$key]       = $args;
}

/**
 * Register user page
 * @param  string $page_slug  slug for links
 * @param  string $page_title Page title
 * @param  callable $func Hook to run when shortcode is found.
 * @return void
 * @since 2.0.1
 */
function pp_register_user_page($page_slug, $page_title, $func) {
    pp_append_to_global_var('user_pages', $page_slug, array(
        'title' => $page_title,
        'func' => $func
        ));
}

/**
 * Check if current page pp base page
 * @return boolean
 * @since 0.0.1
 */
function is_profilepress() {
    if (get_the_ID() == pp_opt('base_page')) return true;
    return false;
}

/**
 * Get current user of pp page
 * @return intgere
 * @since 0.0.1
 */
function pp_get_current_user() {
    GLOBAL $pp_user_id;
    $pp_user_id = (int)get_query_var('pp_user');

    if (empty($pp_user_id)) return get_current_user_id();
    
    return $pp_user_id;
}

function pp_current_user_object() {
    global $pp_user_obj;
    
    $pp_user_obj = get_user_by('id', pp_get_current_user());
    return $pp_user_obj;
}

/**
 * Check if current pp is my
 * @return boolean
 * @since 0.0.1
 */
function is_my_pp() {

    if (!is_profilepress()) return false;
    
    if (pp_get_current_user() == get_current_user_id()) return true;
    
    return false;
}


/**
 * Register pp option tab and fields
 * @param  string   $group_slug     slug for links
 * @param  string   $group_title    Page title
 * @param  array    $fields         fields array.
 * @return void
 * @since 0.0.1
 */
function pp_register_option_group($group_slug, $group_title, $fields) {
    pp_append_to_global_var('pp_option_tabs', $group_slug, array(
        'title' => $group_title,
        'fields' => $fields
        ));
}

/**
 * Output option tab nav
 * @return void
 * @since 2.0.0-alpha2
 */
function pp_options_nav() {
    global $pp_option_tabs;
    $active = (isset($_REQUEST['option_page'])) ? $_REQUEST['option_page'] : 'general';
    
    $menus  = array();
    
    foreach ($pp_option_tabs as $k      => $args) {
        $link   = admin_url("admin.php?page=pp_options&option_page={$k}");
        $menus[$k]        = array(
            'title'        => $args['title'],
            'link'        => $link
            );
    }
    
    /**
     * FILTER: pp_option_tab_nav
     * filter is applied before showing option tab navigation
     * @var array
     * @since  0.0.1
     */
    $menus  = apply_filters('pp_option_tab_nav', $menus);
    
    $o      = '<ul id="pp_opt_nav" class="nav nav-tabs">';
    foreach ($menus as $k      => $m) {
        $class  = !empty($m['class']) ? ' ' . $m['class'] : '';
        $o.= '<li' . ($active == $k ? ' class="active"' : '') . '><a href="' . $m['link'] . '" class="ap-user-menu-' . $k . $class . '">' . $m['title'] . '</a></li>';
    }
    $o.= '</ul>';
    
    echo $o;
}

/**
 * Display fields group options. Uses AnsPress_Form to renders fields.
 * @return void
 * @since 0.0.1
 */
function pp_option_group_fields() {
    global $pp_option_tabs;
    $active = (isset($_REQUEST['option_page'])) ? sanitize_text_field($_REQUEST['option_page']) : 'general';
    
    if (empty($pp_option_tabs) && is_array($pp_option_tabs)) return;
    
    $fields = $pp_option_tabs[$active]['fields'];
    
    $fields[]        = array(
        'name'        => 'action',
        'type'        => 'hidden',
        'value'        => 'pp_save_options',
        );
    
    $args   = array(
        'name'        => 'options_form',
        'is_ajaxified'        => true,
        'submit_button'        => __('Save options', 'pp') ,
        'nonce_name'        => 'nonce_option_form',
        'fields'        => $fields,
        );
    
    $form   = new Wp3_Form($args);
    
    echo $form->get_form();
}

/**
 * Sort array by order value. Group array which have same order number and then sort them.
 * @param  array $array
 * @return array
 * @since 0.0.1
 */
if (!function_exists('wp3_sort_array_by_order')):
    function wp3_sort_array_by_order($array) {
        $new_array = array();
        if (!empty($array) && is_array($array)) {
            $group     = array();
            foreach ($array as $k         => $a) {
                $order     = $a['order'];
                $group[$order][]           = $a;
                $group[$order]['order']           = $order;
            }
            
            usort($group, function ($a, $b) {
                return $a['order'] - $b['order'];
            });
            
            foreach ($group as $a) {
                foreach ($a as $k => $newa) {
                    if ($k !== 'order') $new_array[]   = $newa;
                }
            }
            
            return $new_array;
        }
    }
    endif;

/**
 * Resize image and save it in upload dir
 * @param  integer $size
 * @param  boolean $default
 * @return string
 * @since  0.0.1
 */
function pp_get_avatar($user_id, $size       = 'thumbnail', $default    = false) {
    $upload_dir = wp_upload_dir();
    
    if ($default) {
        $image      = wp_get_attachment_image_src(pp_opt('default_avatar') , 'thumbnail');
    } 
    else {
        $image      = wp_get_attachment_image_src(get_user_meta($user_id, '__pp_avatar', true) , array(
            $size,
            $size
            ));
    }
    
    if ($image === false || !is_array($image) || empty($image[0])) {
        return false;
    }
    
    return $image[0];
}

/**
 * Get the meta of current user
 * @param  string $key meta key
 * @param  boolean $single return only single value instead of array
 * @return mixed
 * @since  0.0.1
 */
function pp_current_user_meta($key, $single = false) {
    global $pp_user_meta;

    $pp_user_meta = get_user_meta(pp_get_current_user());

    $meta   = (array)$pp_user_meta;
    
    /*if($key == 'followers')
    return @$meta[PROFILE_FOLLOWERS_META] ? $meta[ES_FOLLOWERS_META] : 0;
    
    elseif($key == 'following')
    return @$meta[ES_FOLLOWING_META] ? $meta[ES_FOLLOWING_META] : 0;*/
    
    if (isset($meta[$key])) {
        if ($single) return $meta[$key][0];
        
        return $meta[$key];
    }
    
    return false;
}

/**
 * For user display name
 * It can be filtered for adding cutom HTML
 * @param  mixed $args
 * @return string
 * @since 0.0.1
 */
function pp_display_name($args     = array()) {
    global $post;
    $defaults = array(
        'user_id'          => get_the_author_meta('ID') ,
        'html'          => false,
        'echo'          => false,
        'anonymous_label'          => __('Anonymous', 'pp') ,
        );
    
    if (!is_array($args)) {
        $defaults['user_id']          = $args;
        $args     = $defaults;
    } 
    else {
        $args     = wp_parse_args($args, $defaults);
    }
    
    extract($args);
    
    $return = '';
    
    if ($user_id > 0) {
        $user   = get_userdata($user_id);
        
        if (!$html) {
            $return = $user->display_name;
        } 
        else {
            $return = '<span class="who"><a href="' . pp_user_link($user_id) . '">' . $user->display_name . '</a></span>';
        }
    } 
    elseif ($post->post_type == 'question' || $post->post_type == 'answer') {
        $name   = get_post_meta($post->ID, 'anonymous_name', true);
        
        if (!$html) {
            if ($name != '') {
                $return = $name;
            } 
            else {
                $return = $anonymous_label;
            }
        } 
        else {
            if ($name != '') {
                $return = '<span class="who">' . $name . __(' (anonymous)', 'pp') . '</span>';
            } 
            else {
                $return = '<span class="who">' . $anonymous_label . '</span>';
            }
        }
    } 
    else {
        $return = '<span class="who">' . $anonymous_label . '</span>';
    }
    
    /**
     * FILTER: pp_display_name
     * Filter can be used to alter display name
     * @var string
     * @since 0.0.1
     */
    if (!$html) $return = apply_filters('pp_display_name', $return);
    else $return = apply_filters('pp_display_name_html', $return);
    
    if ($echo) {
        echo $return;
    } 
    else {
        return $return;
    }
}

/**
 * Return response with type and message
 * @param  string $id messge id
 * @param  boolean $only_message return message string instead of array
 * @return string
 * @since 2.0.0-alpha2
 */
function pp_responce_message($id, $only_message = false) {
    $msg          = array(
        'success'              => array(
            'type'              => 'success',
            'message'              => __('Current action executed successfully.', 'pp')
            ) ,
        'please_login'              => array(
            'type'              => 'warning',
            'message'              => __('You need to login before doing this action.', 'pp')
            ) ,
        'something_wrong'              => array(
            'type'              => 'error',
            'message'              => __('Something went wrong, last action failed.', 'pp')
            ) ,
        'no_permission'              => array(
            'type'              => 'warning',
            'message'              => __('You do not have permission to do this action.', 'pp')
            ) ,
        'form_error'              => array(
            'type'              => 'error',
            'message'              => __('Form is not submitted, check fields again.', 'pp')
            ) ,
        'updated_user_field'              => array(
            'type'              => 'success',
            'message'              => __('Successfully updated the field.', 'pp')
            ) ,
        'avatar_uploaded'              => array(
            'type'              => 'success',
            'message'              => __('Successfully updated your avatar.', 'pp')
            ) ,
        'added_to_favorite'              => array(
            'type'              => 'success',
            'message'              => __('Successfully added to your favorite list.', 'pp')
            ) ,
        'removed_from_favorite'              => array(
            'type'              => 'success',
            'message'              => __('Successfully removed from your favorite list.', 'pp')
            ) ,
        );

    /**
     * FILTER: pp_responce_message
     * Can be used to alter response messages
     * @var array
     * @since 2.0.1
     */
    $msg          = apply_filters('pp_responce_message', $msg);
    
    if (isset($msg[$id]) && $only_message) return $msg[$id]['message'];
    
    if (isset($msg[$id])) return $msg[$id];
    
    return false;
}

function pp_ajax_responce($results) {

    if (!is_array($results)) {
        $message_id    = $results;
        $results       = array();
        $results['message']               = $message_id;
    }
    
    $results['pp_responce']               = true;
    
    if (isset($results['message'])) {
        $error_message = pp_responce_message($results['message']);
        
        if ($error_message !== false) {
            $results['message']               = $error_message['message'];
            $results['message_type']               = $error_message['type'];
        }
    }
    
    /**
     * FILTER: pp_ajax_responce
     * Can be used to alter pp_ajax_responce
     * @var array
     * @since 2.0.1
     */
    $results       = apply_filters('pp_ajax_responce', $results);
    
    return $results;
}

function pp_send_json($results = array()) {
    $results['is_profilepress_ajax']         = true;
    
    wp_send_json(pp_ajax_responce($results));
}

function pp_have_fields() {
    $count = wp_count_posts('pp_field');
    
    if ($count->publish > 0) return true;
    
    return false;
}

/* retrieve the visibility of group */
function pp_get_label_visibility($term_id) {
    $tax_meta = get_option("pp_group_$term_id");
    
    return $tax_meta['visibility'];
}

function pp_user_favorite_post_query($user_id) {
    $cpts      = array_keys(pp_opt('favorite_cpt'));
    $post_type = sanitize_text_field(get_query_var('pp_cpt'));
    $post_type = in_array($post_type, $cpts) ? $post_type : $cpts;

    $args      = array(
        'author' => $user_id,
        'pp_query' => 'user_favorites',
        'post_type' => $post_type,
        'post_status' => 'publish',
        'paged' => $paged,
        'orderby' => 'date',
        'order' => 'DESC'
        );
    return new WP_Query($args);
}
