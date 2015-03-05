<?php
/**
 * form validation class
 * @link http://wp3.in
 * @license GPL 3+
 * @package ProfilePress
 */
if (!class_exists('Wp3_Validation')):
class Wp3_Validation
{
    private $args = array();

    private $errors = array();

    private $fields = array();

    /**
     * Initialize the class
     * @param array $args
     */
    public function __construct($args = array())
    {
        if (empty($args)) {
                    return;
        }

        $this->args = $args;

        $this->fields_to_include();

        $this->actions();
    }

    /**
     * Check fields to process
     * @return void
     * @since 2.0.1
     */
    private function fields_to_include()
    {
        foreach ($this->args as $field => $actions) {
            //if(isset($_REQUEST[$field]))
            $this->fields[$field] = @$_REQUEST[$field];
        }
    }

    /**
     * Check if field is empty or not set
     * @param  string $field
     * @return void
     * @since 2.0.1
     */
    public function required($field)
    {

        if (!isset($this->fields[$field]) || $this->fields[$field] == '') {
                    $this->errors[$field] = __('This field is required', 'pp');
        }
    }

    /**
     * Check for valid date input
     * @return boolean
     */
    public function check_if_date($string)
    {
        $d = DateTime::createFromFormat('m/d/Y', $string);
        return $d && $d->format('m/d/Y') == $string;
    }

    /**
     * Check if field is valid date
     * @param  string $field
     * @return void
     * @since 2.0.1
     */
    public function date($field)
    {
        if (!isset($this->fields[$field]) || $this->check_if_date($this->fields[$field])) {
                    $this->errors[$field] = __('This is not a valid date', 'pp');
        }
    }

    /**
     * Sanitize text fields
     * @param  string $field
     * @return void
     * @since 2.0.1
     */
    private function sanitize_text_field($field)
    {
        if (isset($this->fields[$field])) {
                    $this->fields[$field] = sanitize_text_field($this->fields[$field]);
        }
    }

    /**
     * Check length of a string, if less then specified then return error
     * @param  string $field
     * @param  string $param
     * @return void
     * @since  2.0
     */
    private function length_check($field, $param)
    {
        if (!isset($this->fields[$field]) || mb_strlen($this->fields[$field]) < $param) {
                    $this->errors[$field] = sprintf(__('Its too short, it must be minimum %d characters', 'pp'), $param);
        }
    }

    /**
     * Count comma separated strings
     * @param  string $field
     * @param  string $param
     * @return void
     * @since  2.0
     */
    private function comma_separted_count($field, $param)
    {
        if (!isset($this->fields[$field]) || count(explode(',', $this->fields[$field])) < $param) {
                    $this->errors[$field] = sprintf(__('It must be minimum %d characters', 'pp'), $param);
        }
    }

    /**
     * Sanitize as a boolean value
     * @param  string $field
     * @return void
     * @since 2.0.1
     */
    private function only_boolean($field)
    {

        $this->fields[$field] = (bool) $this->fields[$field];

    }

    /**
     * Sanitize as a integer value
     * @param  string $field
     * @return void
     * @since 2.0.1
     */
    private function only_int($field)
    {

        $this->fields[$field] = (int) $this->fields[$field];

    }

    /**
     * Sanitize field using wp_kses
     * @param  string $field
     * @return void
     * @since 2.0.1
     */
    private function wp_kses($field)
    {
        $this->fields[$field] = wp_kses($this->fields[$field], ap_form_allowed_tags());
    }

    /**
     * Remove wordpress read more tag
     * @param  string $field
     * @return void
     * @since 2.0.1
     */
    private function remove_more($field)
    {
        $this->fields[$field] = str_replace('<!--more-->', '', $this->fields[$field]);
    }

    /**
     * Stripe shortcode tags
     * @param  string $field
     * @return void
     * @since 2.0.1
     */
    private function strip_shortcodes($field)
    {
        $this->fields[$field] = strip_shortcodes($this->fields[$field]);
    }

    /**
     * Encode contents inside pre and code tag
     * @param  string $field
     * @return void
     * @since 2.0.1
     */
    private function encode_pre_code($field)
    {
        $this->fields[$field] = preg_replace_callback('/<pre.*?>(.*?)<\/pre>/imsu', array($this, 'pre_content'), $this->fields[$field]);
        $this->fields[$field] = preg_replace_callback('/<code.*?>(.*?)<\/code>/imsu', array($this, 'code_content'), $this->fields[$field]);
    }

    private function pre_content($matches)
    {
        return '<pre>'.esc_html($matches[1]).'</pre>';
    }

    private function code_content($matches)
    {
        return '<code>'.esc_html($matches[1]).'</code>';
    }

    /**
     * Strip all tags
     * @param  string $field
     * @return void       
     * @since  2.0
     */
    private function strip_tags($field)
    {
        $this->fields[$field] = strip_tags($this->fields[$field]); 
    }

    /**
     * Sanitize url
     * @param  string $field
     * @return void       
     * @since  2.0
     */
    private function sanitize_url($field)
    {
        $this->fields[$field] = esc_url($this->fields[$field]); 
    }

    /**
     * Check if given field is url or not
     * @param  string $field
     * @return void       
     * @since  2.0
     */
    private function is_url($field)
    {
        if (isset($this->fields[$field]) && (filter_var($this->fields[$field], FILTER_VALIDATE_URL) === FALSE)) {
                    $this->errors[$field] = __('Not a valid url, please correct it.', 'pp');
        }
    }

    /**
     * Santitize tags field
     * @param  string $field
     * @return void       
     * @since  2.0
     */
    private function sanitize_tags($field)
    {
        $this->fields[$field] = $this->fields[$field];

        $tags = explode(',', $this->fields[$field]);

        $sanitized_tags = '';

        if (is_array($tags)) {
            $count = count($tags);
            $i = 1;
            foreach ($tags  as $tag) {
                $sanitized_tags .= sanitize_text_field($tag);
                
                if ($count != $i) {
                                    $sanitized_tags .= ',';
                }
                
                $i++;
            }
        }

        $this->fields[$field] = $sanitized_tags;
    }

    /**
     * Sanitize field based on actions passed
     * @param  string $field
     * @param  array $actions
     * @return void
     * @since 2.0.1
     */
    private function sanitize($field, $actions)
    {
        foreach ($actions as $type) {
            
            switch ($type) {
                case 'sanitize_text_field':
                    $this->sanitize_text_field($field);
                    break;

                case 'only_boolean':                    
                    $this->only_boolean($field);
                    break;

                case 'only_int':                    
                    $this->only_int($field);
                    break;

                case 'wp_kses':                    
                    $this->wp_kses($field);
                    break;

                case 'remove_more':                    
                    $this->remove_more($field);
                    break;

                case 'strip_shortcodes':                    
                    $this->strip_shortcodes($field);
                    break;

                case 'encode_pre_code':                    
                    $this->encode_pre_code($field);
                    break;

                case 'strip_tags':                    
                    $this->strip_tags($field);
                    break;

                case 'sanitize_tags':                    
                    $this->sanitize_tags($field);
                    break;

                case 'url':                    
                    $this->sanitize_url($field);
                    break;

                
                default:
                    $this->fields[$field] = apply_filters('ap_validation_sanitize_field', $field, $actions);
                    break;
            }
        }
    }

    /**
     * Validate a field based on actions passed
     * @param  string $field   
     * @param  array $actions
     * @return void          
     * @since 2.0.1
     */
    private function validate($field, $actions)
    {

        foreach ($actions as $type => $param) {
            if (isset($this->errors[$field])) {
                            return;
            }

            switch ($type) {
                case 'required':
                    $this->required($field);
                    break;

                case 'length_check':
                    $this->length_check($field, $param);
                    break;

                case 'comma_separted_count':
                    $this->comma_separted_count($field, $param);
                    break;

                case 'date':
                    $this->date($field, $param);
                    break;

                case 'is_url':
                    $this->is_url($field);
                    break;
                
                default:
                    $this->errors[$field] = apply_filters('pp_validate_field_'.$type, $field, $actions);
                    break;
            }
        }
    }

    /**
     * Field is being checked and sanitized
     * @return void
     * @since 2.0.1
     */
    private function actions()
    {

        foreach ($this->args as $field => $actions) {
            if (isset($actions['sanitize'])) {
                            $this->sanitize($field, $actions['sanitize']);
            }
            if (isset($actions['validate'])) {
                            $this->validate($field, $actions['validate']);
            }
        }
            
    }

    /**
     * Check if fields have any error
     * @return boolean
     * @since 2.0.1
     */
    public function have_error() {
        if (count($this->errors) > 0) {
                    return true;
        }

        return false;
    }

    /**
     * Get all errors
     * @return array | boolean
     */
    public function get_errors() {
        if (count($this->errors) > 0) {
                    return $this->errors;
        }

        return false;
    }

    /**
     * Return all sanitized fields
     * @return array
     * @since 2.0.1
     */
    public function get_sanitized_fields()
    {
        return $this->fields;
    }
}
endif;