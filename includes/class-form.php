<?php

/**
 * Form class
 *
 * @package     ProfilePress
 * @author      Rahul Aryan <rah12@live.com>
 * @license     http://www.opensource.org/licenses/gpl-license.php GPL v3.0 (or later)
 * @link        http://wp3.in
 */
if (!class_exists('Wp3_Form')):
    class Wp3_Form
    {
        
        private $name;
        
        private $args   = array();
        
        private $output = '';
        
        private $field;
        
        private $errors;
        
        /**
         * Initiate the class
         * @param array $args
         */
        public function __construct($args       = array()) {
            
            // Default args
            $defaults   = array(
                'method'            => 'POST',
                'action'            => '',
                'is_ajaxified'            => false,
                'class'            => 'pp-form',
                'submit_button'            => __('Submit', 'ap') ,
                'form'            => true,
            );
            
            // Merge defaults args
            $this->args = wp_parse_args($args, $defaults);
            
            // set the name of the form
            $this->name = $this->args['name'];
            
            global $pp_errors;
            $this->errors = $pp_errors;
            
            $this->add_default_in_field();
            
            $this->order_fields();
        }
        
        private function add_default_in_field() {
            if (!isset($this->args['fields'])) {
                return;
            }
            
            foreach ($this->args['fields'] as $k => $field) {
                if (!isset($field['order'])) {
                    $this->args['fields'][$k]['order']   = 10;
                }
                
                if (!isset($field['show_desc_tip'])) {
                    $this->args['fields'][$k]['show_desc_tip']   = true;
                }
            }
        }
        
        /**
         * Order fields
         * @return void
         * @since 2.0.1
         */
        private function order_fields() {
            if (!isset($this->args['fields'])) {
                return;
            }
            
            $this->args['fields'] = wp3_sort_array_by_order($this->args['fields']);
        }
        
        /**
         * Build the form
         * @return void
         * @since 2.0.1
         */
        public function build() {
            $this->form_head();
            $this->form_fields();
            
            if ($this->args['form']) {
                $this->hidden_fields();
            }
            
            $this->form_footer();
        }
        
        /**
         * FORM element
         * @return void
         * @since 2.0.1
         */
        private function form_head() {
            $attr = '';
            
            if ($this->args['is_ajaxified']) {
                $attr.= ' data-action="pp_ajax_form"';
            }
            
            if (!empty($this->args['class'])) {
                $attr.= ' class="' . $this->args['class'] . '"';
            }
            
            ob_start();
            
            /**
             * ACTION: pp_form_before_[form_name]
             * action for hooking before form
             * @since 2.0.1
             */
            do_action('pp_form_before_' . $this->name);
            $this->output.= ob_get_clean();
            
            if ($this->args['form']) {
                $this->output.= '<form id="' . $this->args['name'] . '" method="' . $this->args['method'] . '" action="' . $this->args['action'] . '"' . $attr . '>';
            }
        }
        
        /**
         * FORM footer
         * @return void
         * @since 2.0.1
         */
        private function form_footer() {
            ob_start();
            
            /**
             * ACTION: pp_form_bottom_[form_name]
             * action for hooking captcha and extar fields
             * @since 2.0.1
             */
            do_action('pp_form_bottom_' . $this->name);
            $this->output.= ob_get_clean();
            
            if ($this->args['form']) {
                $this->output.= '<button type="submit" class="pp-btn pp-submit-btn">' . $this->args['submit_button'] . '</button>';
                $this->output.= '</form>';
            }
        }
        
        private function nonce() {
            $nonce_name = isset($this->args['nonce_name']) ? $this->args['nonce_name'] : $this->name;
            $this->output.= wp_nonce_field($nonce_name, '__nonce', true, false);
        }
        
        /**
         * Form hidden fields
         * @return void
         * @since 2.0.1
         */
        private function hidden_fields() {
            if ($this->args['is_ajaxified']) {
                $this->output.= '<input type="hidden" name="pp_ajax_action" value="' . $this->name . '">';
            }
            
            $this->output.= '<input type="hidden" name="pp_form_action" value="' . $this->name . '">';
            
            $this->nonce();
        }
        
        /**
         * form field label
         * @return void
         * @since 2.0.1
         */
        private function label($field = false) {
            if (!$field) {
                $field = $this->field;
            }
            
            if ($field['label'] && !$field['show_desc_tip']) {
                $this->output.= '<label class="pp-form-label" for="' . @$field['name'] . '">' . @$field['label'] . '</label>';
            } 
            elseif ($field['label']) {
                $this->output.= '<label class="pp-form-label" for="' . @$field['name'] . '">' . @$field['label'];
                $this->desc();
                $this->output.= '</label>';
            }
        }
        
        /**
         * Output placeholder attribute of current field
         * @return string
         * @since 2.0.1
         */
        private function placeholder($field = false) {
            if (!$field) {
                $field = $this->field;
            }
            
            return !empty($field['placeholder']) ? ' placeholder="' . $field['placeholder'] . '"' : '';
        }
        
        /**
         * Output description of a form fields
         * @return void
         * @since 2.0.1
         */
        private function desc($field = false) {
            
            if (!$field) {
                $field = $this->field;
            }
            
            if (!$field['show_desc_tip']) {
                $this->output.= (!empty($field['desc']) ? '<p class="pp-field-desc">' . $field['desc'] . '</p>' : '');
            } 
            else {
                $this->output.= (!empty($field['desc']) ? '<span class="pp-tip pp-field-desc" data-tipposition="right" title="' . esc_html($field['desc']) . '">?</span>' : '');
            }
        }
        
        /**
         * Output text fields
         * @param       array  $field
         * @return      void
         * @since       2.0
         */
        private function text_field($field = false) {
            if (!$field) {
                $field = $this->field;
            }
            
            if (isset($field['label'])) {
                $this->label($field);
            }
            
            $placeholder  = $this->placeholder($field);
            $autocomplete = isset($field['autocomplete']) ? ' autocomplete="off"' : '';
            if (!isset($field['repeatable']) || !$field['repeatable']) {
                
                $this->output.= '<input id="' . @$field['name'] . '" type="text" class="pp-form-control" value="' . @$field['value'] . '" name="' . @$field['name'] . '"' . $placeholder . ' ' . @$field['attr'] . $autocomplete . ' />';
            } 
            else {
                if (!empty($field['value']) && is_array($field['value'])) {
                    $this->output.= '<div id="pp-repeat-c-' . @$field['name'] . '" class="pp-repeatbable-field">';
                    foreach ($field['value'] as $k => $rep_f) {
                        $this->output.= '<div id="pp_text_rep_' . @$field['name'] . '_' . $k . '" class="pp-repeatbable-field"><input id="' . @$field['name'] . '_' . $k . '" type="text" class="pp-form-control pp-repeatable-text" value="' . @$rep_f . '" name="' . @$field['name'] . '[' . $k . ']"' . $placeholder . ' ' . @$field['attr'] . $autocomplete . ' />';
                        $this->output.= '<button data-action="pp_delete_field" type="button" data-toggle="' . @$field['name'] . '_' . $k . '">' . __('Delete') . '</button>';
                        $this->output.= '</div>';
                    }
                    $this->output.= '</div>';
                    
                    $this->output.= '<div id="pp-repeatbable-field-' . @$field['name'] . '" class="pp-reapt-field-copy">';
                    $this->output.= '<div id="pp_text_rep_' . @$field['name'] . '_#" class="pp-repeatbable-field"><input id="' . @$field['name'] . '_#" type="text" class="pp-form-control pp-repeatable-text" value="" name="' . @$field['name'] . '[#]"' . $placeholder . ' ' . @$field['attr'] . $autocomplete . ' />';
                    $this->output.= '<button data-action="pp_delete_field" type="button" data-toggle="' . @$field['name'] . '_#">' . __('Delete') . '</button>';
                    $this->output.= '</div></div>';
                    $this->output.= '<button data-action="pp_add_field" type="button" data-field="pp-repeat-c-' . @$field['name'] . '" data-copy="pp-repeatbable-field-' . @$field['name'] . '">' . __('Add more') . '</button>';
                }
            }
            
            $this->error_messages();
        }
        
        /**
         * Output text type="number"
         * @param       array  $field
         * @return      void
         * @since       2.0.0-alpha2
         */
        private function number_field($field        = array()) {
            if (isset($field['label'])) {
                $this->label();
            }
            
            $placeholder  = $this->placeholder();
            $autocomplete = isset($field['autocomplete']) ? ' autocomplete="off"' : '';
            $this->output.= '<input id="' . @$field['name'] . '" type="number" class="pp-form-control" value="' . @$field['value'] . '" name="' . @$field['name'] . '"' . $placeholder . ' ' . @$field['attr'] . $autocomplete . ' />';
            $this->error_messages();
        }
        
        /**
         * Checkbox field
         * @param  array  $field
         * @return void
         * @since 2.0.1
         */
        private function checkbox_field($field = array()) {
            if (isset($field['label'])) {
                $this->label();
            }
            
            if (!empty($field['desc'])) {
                $this->output.= '<div class="pp-checkbox-withdesc clearfix">';
            }
            
            if (isset($field['group'])) {
                foreach ($field['group'] as $checkbox) $this->output.= '<label><input id="' . @$checkbox['name'] . '" type="checkbox" class="pp-form-control" value="1" name="' . @$checkbox['name'] . '" ' . checked((bool)$checkbox['value'], true, false) . ' ' . @$checkbox['attr'] . ' />' . $checkbox['label'] . '</label>';
            } 
            else {
                $this->output.= '<input id="' . @$field['name'] . '" type="checkbox" class="pp-form-control" value="1" name="' . @$field['name'] . '" ' . checked((bool)$field['value'], true, false) . ' ' . @$field['attr'] . ' />';
            }
            $this->desc();
            $this->error_messages();
            
            if (!empty($field['desc'])) {
                $this->output.= '</div>';
            }
        }
        
        /**
         * output select field options
         * @param  array  $field
         * @return void
         * @since 2.0.1
         */
        private function select_options($field = array()) {
            foreach ($field['options'] as $k     => $opt) {
                $this->output.= '<option value="' . $k . '" ' . selected($k, $field['value'], false) . '>' . $opt . '</option>';
            }
        }
        
        /**
         * Select fields
         * @param  array  $field
         * @return void
         * @since 2.0.1
         */
        private function select_field($field = array()) {
            if (isset($field['label'])) {
                $this->label();
            }
            
            $this->output.= '<select id="' . @$field['name'] . '" class="pp-form-control" value="' . @$field['value'] . '" name="' . @$field['name'] . '" ' . @$field['attr'] . '>';
            $this->output.= '<option value=""></option>';
            $this->select_options($field);
            $this->output.= '</select>';
            $this->error_messages();
        }
        
        /**
         * output select field options
         * @param  array  $field
         * @return void
         * @since 2.0.1
         */
        private function taxonomy_select_options($field      = array()) {
            $taxonomies = get_terms($field['taxonomy'], 'orderby=count&hide_empty=0&hierarchical=0');
            
            if ($taxonomies) {
                foreach ($taxonomies as $tax) {
                    $this->output.= '<option value="' . $tax->term_id . '" ' . selected($tax->term_id, $field['value'], false) . '>' . $tax->name . '</option>';
                }
            }
        }
        
        /**
         * Taxonomy select field
         * @param  array  $field
         * @return void
         * @since 2.0.1
         */
        private function taxonomy_select_field($field = array()) {
            if (isset($field['label'])) {
                $this->label();
            }
            
            $this->output.= '<select id="' . @$field['name'] . '" class="pp-form-control" value="' . @$field['value'] . '" name="' . @$field['name'] . '" ' . @$field['attr'] . '>';
            $this->output.= '<option value=""></option>';
            $this->taxonomy_select_options($field);
            $this->output.= '</select>';
            $this->error_messages();
        }
        
        /**
         * Page select field
         * @param  array  $field
         * @return void
         * @since 2.0.0-alpha2
         */
        private function page_select_field($field = array()) {
            if (isset($field['label'])) {
                $this->label();
            }
            
            $this->output.= wp_dropdown_pages(array(
                'selected' => @$field['value'],
                'name' => @$field['name'],
                'post_type' => 'page',
                'echo' => false
            ));
            $this->error_messages();
        }
        
        /**
         * Jquery date picker field
         * @param  array  $field
         * @return void
         * @since 2.0.0-alpha2
         */
        private function date_picker($field = array()) {
            if (isset($field['label'])) {
                $this->label();
            }
            
            if (!isset($field['date_fromat'])) {
                $field['date_fromat']       = 'm/d/yy';
            }
            
            if (!isset($field['show_month'])) {
                $field['show_month']       = 'true';
            }
            
            if (!isset($field['show_year'])) {
                $field['show_year']       = 'true';
            }
            
            if (isset($field['year_range'])) {
                $field['year_range']       = ' data-yearrange="' . $field['year_range'] . '"';
            }
            
            $this->output.= '<input type="date" id="' . @$field['name'] . '" name="' . @$field['name'] . '" value="' . @$field['value'] . '" class="pp-form-control pp-datepicker" data-format="' . @$field['date_fromat'] . '" data-month="' . @$field['show_month'] . '" data-year="' . @$field['show_year'] . '"' . @$field['year_range'] . ' />';
            
            $this->error_messages();
        }
        
        /**
         * textarea fields
         * @param       array  $field
         * @return      void
         * @since       2.0
         */
        private function textarea_field($field       = array()) {
            if (isset($field['label'])) {
                $this->label();
            }
            
            $placeholder = $this->placeholder();
            $this->output.= '<textarea id="' . @$field['name'] . '" rows="' . @$field['rows'] . '" class="pp-form-control" name="' . @$field['name'] . '"' . $placeholder . ' ' . @$field['attr'] . '>' . @$field['value'] . '</textarea>';
            $this->error_messages();
        }
        
        /**
         * Create wp_editor field
         * @param  array  $field
         * @return void
         * @since 2.0.1
         */
        private function editor_field($field    = array()) {
            if (isset($field['label'])) {
                $this->label();
            }
            
            /**
             * FILTER: pp_pre_editor_settings
             * Can be used to mody wp_editor settings
             * @var array
             * @since 2.0.1
             */
            $field['settings']['tinymce']          = array(
                'content_css'          => pp_get_theme_url('css/editor.css')
            );
            $settings = apply_filters('pp_pre_editor_settings', $field['settings']);
            
            // Turn on the output buffer
            ob_start();
            echo '<div class="pp-editor">';
            wp_editor($field['value'], $field['name'], $settings);
            echo '</div>';
            $this->output.= ob_get_clean();
            $this->error_messages();
        }
        
        /**
         * For creating hidden input fields
         * @param  array  $field
         * @return void
         * @since 2.0.1
         */
        private function hidden_field($field = array()) {
            $this->output.= '<input type="hidden" value="' . @$field['value'] . '" name="' . @$field['name'] . '" ' . @$field['attr'] . ' />';
        }
        
        private function custom_field($field = array()) {
            $this->output.= $field['html'];
        }
        
        /**
         * Check if current field have any error
         * @return boolean
         * @since 2.0.1
         */
        private function have_error() {
            if (isset($this->errors[$this->field['name']])) {
                return true;
            }
            
            return false;
        }
        private function error_messages() {
            if (isset($this->errors[$this->field['name']])) {
                $this->output.= '<div class="pp-form-error-messages">';
                
                foreach ($this->errors[$this->field['name']] as $error) {
                    $this->output.= '<p class="pp-form-error-message">' . $error . '</p>';
                }
                
                $this->output.= '</div>';
            }
        }
        
        /**
         * Out put all form fields based on on their type
         * @return void
         * @since  2.0
         */
        private function form_fields() {
            
            /**
             * FILTER: pp_pre_form_fields
             * Provide filter to add or override form fields before output.
             * @var array
             * @since 2.0.1
             */
            $this->args['fields']             = apply_filters('pp_pre_form_fields', $this->args['fields']);
            
            foreach ($this->args['fields'] as $field) {
                
                $this->field = $field;
                
                $error_class = $this->have_error() ? ' pp-have-error' : '';
                
                switch ($field['type']) {
                    case 'text':
                        $this->output.= '<div class="pp-form-fields' . $error_class . '">';
                        $this->text_field($field);
                        $this->output.= '</div>';
                        break;

                    case 'number':
                        $this->output.= '<div class="pp-form-fields' . $error_class . '">';
                        $this->number_field($field);
                        $this->output.= '</div>';
                        break;

                    case 'checkbox':
                        $this->output.= '<div class="pp-form-fields' . $error_class . '">';
                        $this->checkbox_field($field);
                        $this->output.= '</div>';
                        break;

                    case 'select':
                        $this->output.= '<div class="pp-form-fields' . $error_class . '">';
                        $this->select_field($field);
                        $this->output.= '</div>';
                        break;

                    case 'taxonomy_select':
                        $this->output.= '<div class="pp-form-fields' . $error_class . '">';
                        $this->taxonomy_select_field($field);
                        $this->output.= '</div>';
                        break;

                    case 'page_select':
                        $this->output.= '<div class="pp-form-fields' . $error_class . '">';
                        $this->page_select_field($field);
                        $this->output.= '</div>';
                        break;

                    case 'date_picker':
                        $this->output.= '<div class="pp-form-fields' . $error_class . '">';
                        $this->date_picker($field);
                        $this->output.= '</div>';
                        break;

                    case 'textarea':
                        $this->output.= '<div class="pp-form-fields' . $error_class . '">';
                        $this->textarea_field($field);
                        $this->output.= '</div>';
                        break;

                    case 'editor':
                        $this->output.= '<div class="pp-form-fields' . $error_class . '">';
                        $this->editor_field($field);
                        $this->output.= '</div>';
                        break;

                    case 'hidden':
                        $this->hidden_field($field);
                        break;

                    case 'custom':
                        $this->custom_field($field);
                        break;

                    default:
                        
                        /**
                         * FILTER: pp_form_fields_[type]
                         * filter for custom form field type
                         */
                        $this->output.= apply_filters('pp_form_fields_' . $field['type'], $field);
                        break;
                }
            }
        }
        
        /**
         * Output form
         * @return string
         * @since 2.0.1
         */
        public function get_form() {
            if (empty($this->args['fields'])) {
                return __('No fields found', 'ap');
            }
            
            $this->build();
            
            return $this->output;
        }
    }
endif;

