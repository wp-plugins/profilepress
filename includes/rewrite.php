<?php
class PP_Rewrite
{
    public function __construct() {
        add_action('generate_rewrite_rules', array(
            $this,
            'rewrites'
            ) , 1);
        add_filter('parse_request', array( $this, 'add_query_var' ));
    }
    
    public function rewrites() {
        global $wp_rewrite;
        
        $base_page_id      = pp_opt('base_page');
        
        $base_page         = get_post($base_page_id);
        
        $slug              = pp_base_page_slug() . '/';
        
        $new_rules         = array(
            $slug . "([^/]+)/favorites/([^/]+)/page/?([0-9]{1,})/?$" => "index.php?page_id=" . $base_page_id . "&pp_user_login=" . $wp_rewrite->preg_index(1). "&pp_page=favorites&pp_cpt=" . $wp_rewrite->preg_index(2). "&paged=" . $wp_rewrite->preg_index(3) ,

            $slug . "([^/]+)/favorites/([^/]+)/?" => "index.php?page_id=" . $base_page_id . "&pp_user_login=" . $wp_rewrite->preg_index(1). "&pp_page=favorites&pp_cpt=" . $wp_rewrite->preg_index(2) ,

            $slug . "([^/]+)/([^/]+)/page/?([0-9]{1,})/?$" => "index.php?page_id=" . $base_page_id . "&pp_user_login=" . $wp_rewrite->preg_index(1). "&pp_page=" . $wp_rewrite->preg_index(2) . "&paged=" . $wp_rewrite->preg_index(3),
            
            $slug . "([^/]+)/([^/]+)/?" => "index.php?page_id=" . $base_page_id . "&pp_user_login=" . $wp_rewrite->preg_index(1). "&pp_page=" . $wp_rewrite->preg_index(2) ,

            $slug . "([^/]+)/page/?([0-9]{1,})/?$" => "index.php?page_id=" . $base_page_id . "&pp_user_login=" . $wp_rewrite->preg_index(1) . "&paged=" . $wp_rewrite->preg_index(3) ,      

            $slug . "([^/]+)/?" => "index.php?page_id=" . $base_page_id . "&pp_user_login=" . $wp_rewrite->preg_index(1) ,
            );

$pp_rules     = $new_rules;

return $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
}

function add_query_var($wp) {
    if(!empty($wp->query_vars['pp_user_login'])){
        $user = get_user_by('login', sanitize_text_field($wp->query_vars['pp_user_login']));
        $wp->set_query_var('pp_user', $user->ID);
    }elseif(isset($wp->query_vars['pp_user'])){
        $user = get_user_by('id', (int)$wp->query_vars['pp_user']);
        $wp->set_query_var('pp_user_login', $user->ID);
    }
}
}
