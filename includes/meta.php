<?php
/**
    * All function related to ProfilePress meta
    *
    * @package   ProfilePress
        * @author    Rahul Aryan <admin@wp3.in>
    * @license   GPL-2.0+
        * @link      http://wp3.in
    * @copyright 2014 Rahul Aryan
    */
/* Add meta */
/**
* @param string $type
*/
if(!function_exists('wp3_add_meta')){
    /**
    * @param string $type
    */
    function wp3_add_meta($userid=false, $type=NULL, $actionid =NULL, $value=NULL, $param = NULL, $date = false){
        /* get current user id if not set */
        if(!$userid) {
                    $userid = get_current_user_id();
        }
        
        /* get current time in mysql format if not set */
        if(!$date) {
                    $date = current_time( 'mysql' );
        }
        
        global $wpdb;
        $row = $wpdb->insert(
            $wpdb->prefix . 'ap_meta',
            array(
                    'apmeta_userid'     => $userid,
                    'apmeta_type'       => $type,
                'apmeta_actionid'   => $actionid,
                    'apmeta_value'      => maybe_serialize($value),
                    'apmeta_param'      => maybe_serialize($param),
                    'apmeta_date'       => $date,
                ),
            array(
                '%d',
                '%s',
                '%d',
                '%s',
                '%s',
                '%s',
                )
            );
        if ($row === false)
            return false;
        
        return  $wpdb->insert_id;
    }
}
if (!function_exists('wp3_update_meta')) {
    function wp3_update_meta($data, $where) {
        global $wpdb;
        $meta_key = wp3_meta_key($where);
        $update = $wpdb->update(
                    $wpdb->prefix.'ap_meta', $data, $where
            );
        if ($update !== false)
            wp_cache_delete($meta_key, 'ap_meta');
        return $update;
    }
}
/**
* Delete wp3_meta row
* @param  false|array  $where wp_db where clause
    * @param  integer  $id    if meta id is known then it can be passed
        * @return boolean
*/
if (!function_exists('wp3_delete_meta')) {
        function wp3_delete_meta($where = false, $id = false) {
        global $wpdb;
        if ($id)
            $where = array('apmeta_id' => $id);
        $meta_key = wp3_meta_key($where);
        $delete = $wpdb->delete(
                $wpdb->prefix.'ap_meta', $where
            );
        if ($delete)
            wp_cache_delete($meta_key, 'ap_meta');
        return $delete;
    }
}
if (!function_exists('wp3_meta_key')) {
    function wp3_meta_key($where) {
        $meta_key = '';
        if (isset($where['apmeta_type']))
            $meta_key .= $where['apmeta_type'];
        if (isset($where['apmeta_userid']))
            $meta_key .= '_'.$where['apmeta_userid'];
        else
            $meta_key .= '_null';
        if (isset($where['apmeta_actionid']))
            $meta_key .= '_'.$where['apmeta_actionid'];
        else
            $meta_key .= '_null';
    }
}
if (!function_exists('wp3_get_meta')) {
    function wp3_get_meta($where) {
        global $wpdb;
        $where_string = '';
        $i = 1;
        foreach ($where as $k => $w) {
            $where_string .= $k.' = "'.$w.'" ';
            if (count($where) != $i)
                $where_string .= 'AND ';
            $i++;
        }
        $query = 'SELECT * FROM '.$wpdb->prefix.'ap_meta WHERE '.$where_string;
        $meta_key = md5($query);
        $cache = wp_cache_get($meta_key, 'ap_meta');
        if ($cache !== FALSE)
            return $cache;
        $row = $wpdb->get_row($query, ARRAY_A);
        wp_cache_set($meta_key, $row, 'ap_meta');
        return $row;
    }
}
/* get the total count by type and actionid */
/**
* @param string $type
*/
if(!function_exists('wp3_meta_total_count')){
    /**
    * @param string $type
    */
    function wp3_meta_total_count($type, $actionid=false, $userid = false, $group = false){
        global $wpdb;
        $where_query = '';
        $group_query = '';
        if($actionid) {
                    $where_query .= "apmeta_actionid = $actionid";
        }
        if($userid) {
                    $where_query .= " apmeta_userid = $userid";
        }
        if($group){
            $group_query .= 'GROUP BY '.$group;
        }
        $query = "SELECT IFNULL(count(*), 0) FROM " .$wpdb->prefix ."ap_meta where apmeta_type = '$type' and $where_query $group_query";
        $key = md5($query);
        $cache = wp_cache_get($key, 'count');
        if($cache !== FALSE) {
                    return $cache;
        }
        $count = $wpdb->get_var($query);
        wp_cache_set( $key, $count, 'count');
        return $count;
    }
}
/**
* @param string $type
*/
if(!function_exists('wp3_meta_user_done')){
    /**
    * @param string $type
    */
    function wp3_meta_user_done($type, $userid, $actionid, $value = false){
        global $wpdb;
        $where = "";
        /* check if type contains OR */
        if(strpos($type, '||') !== false){
            $or = explode('||', $type);
            $i = 1;
            foreach($or as $o){
                $where .= "apmeta_type = '".trim($o)."' ";
                if($i != count($or)) {
                                    $where .= " OR ";
                }
                $i++;
            }
        } else{
            $where .= "apmeta_type = '$type'";
        }
        $query = $wpdb->prepare('SELECT IFNULL(count(*), 0) FROM ' .$wpdb->prefix .'ap_meta where '.$where.' and apmeta_userid = %d and apmeta_actionid = %d ', $userid, $actionid);
        if($value) {
                    $query = $query. $wpdb->prepare('and apmeta_value = "%s"', $value);
        }
        $key = md5($query);
        $user_done = wp_cache_get($key, 'counts');
        if($user_done !== false) {
                    return $user_done;
        }
            $user_done = $wpdb->get_var($query);
        wp_cache_set($key, $user_done, 'counts');
        return $user_done;
    }
}
if (!function_exists('wp3_get_all_meta')) {
    function wp3_get_all_meta($args = false, $limit = 10, $query = false) {
        global $wpdb;
        $where_string = '';
        $group_string = '';
        $order_string = '';
        if (isset($args['where'])) {
            foreach ($args['where'] as $k => $a) {
                $compare = isset($a['compare']) ? $a['compare'] : '=';
                $relation = isset($a['relation']) ? $a['relation'] : 'AND';
                $where_string .= $relation.' ';
                if (is_array($a['value']))
                    $val = "('".implode("', '", $a['value'])."')";
                else
                    $val = "'".$a['value']."'";
                $where_string .= $k.' '.$compare.' '.$val.' ';
            }
        }
        if (isset($args['group'])) {
            $i = 1;
            foreach ($args['group'] as $k => $a) {
                $relation = isset($a['relation']) ? $a['relation'] : 'AND';
                if ($i != 1)
                    $group_string .= $relation.' ';
                $group_string .= $k.' ';
                $i++;
            }
            $group_string = 'GROUP BY '.$group_string;
        }
        if (isset($args['orderby'])) {
            $i = 1;
            foreach ($args['orderby'] as $k => $a) {
                $order = isset($a['order']) ? $a['order'] : 'ASC';
                $order_string .= $k.' '.$order;
                if ($i != count($args['orderby']))
                    $order_string .= ', ';
                $i++;
            }
            $order_string = 'ORDER BY '.$order_string;
        }
        if (!$query)
            $query = "SELECT *, UNIX_TIMESTAMP(apmeta_date) as unix_date FROM ".$wpdb->prefix."ap_meta where  1=1 $where_string $group_string $order_string LIMIT $limit";
        $query = apply_filters('wp3_pre_get_all_meta_query', $query, $args);
        $key = md5($query);
        $cache = wp_cache_get($key, 'object');
        if ($cache !== false)
            return $cache;
        $result = $wpdb->get_results($query);
        wp_cache_set($key, $result, 'object');
        return $result;
    }
}
/**
* @param string $type
* @param integer $actionid
*/
    function wp3_add_vote($userid, $type, $actionid){
    return wp3_add_meta($userid, $type, $actionid );
}
/**
* @param string $type
* @param integer $actionid
*/
function wp3_remove_vote($type, $userid, $actionid){
    return wp3_delete_meta(array('apmeta_type' => $type, 'apmeta_userid' => $userid, 'apmeta_actionid' => $actionid));
}
/**
* @param string $type
*/
function wp3_count_vote($userid = false, $type, $actionid =false, $value = 1){
    global $wpdb;
    if(!$userid){
        return wp3_meta_total_count($type, $actionid);
    } elseif($userid && !$actionid){
        return wp3_meta_total_count($type, false, $userid);
    }
}

function wp3_user_favorites_count($userid){
    return wp3_meta_total_count('favorite', false, $userid);
}
//check if user added post to subscribe
function wp3_is_user_favorited($postid){
    if(is_user_logged_in()){
        $userid = get_current_user_id();
        $done = wp3_meta_user_done('favorite', $userid, $postid);
        return $done > 0 ? true : false;
    }
    return false;
}
function wp3_post_favorites_count($postid = false) {
    //subscribe count
    global $post;
    $postid = $postid ? $postid : $post->ID;
    return wp3_meta_total_count('favorite', $postid);
}