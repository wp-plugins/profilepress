<?php



function wp3_insert_pp_views() {
    if (!is_profilepress() || pp_get_current_user() == 0)
        return;

    $userid = get_current_user_id();
    
    $row = wp3_add_meta($userid, 'pp_view', pp_get_current_user(), $_SERVER['REMOTE_ADDR']);

    $view = wp3_get_views_db($data_id);
    $view = $view + 1;
    update_user_meta(pp_get_current_user(), '__pp_views', apply_filters('wp3_insert_pp_views', $view));
    
    do_action('wp3_insert_pp_views', pp_get_current_user(), $view);
}

function wp3_get_pp_views($user_id = false) {  
    if (!$user_id) $user_id = get_current_user_id();
    
    $views = get_user_meta($user_id, '__pp_views', true);    
    $views = empty($views) ? 0 : $views;
    
    return apply_filters('wp3_get_pp_views', $views);
}

function wp3_get_views_db($user_id) {
    return wp3_meta_total_count('pp_view', $user_id);
}

function wp3_is_already_viewed($user_id, $data_id, $type = 'question') {

    $ip = sanitize_text_field($_SERVER['REMOTE_ADDR']);
    
    $done = wp3_meta_user_done('pp_view', $user_id, $data_id, $ip);
    
    return $done > 0 ? true : false;
}

