<?php 

namespace VOS\Providers;

class TableProvider {
    
    public $database_services;
    public $database_objects;
    public $database_version;
    public $database_tables;

    const version_slug = 'vos_database_version';
    const table_slug = 'vos_database_table';

    public function __construct($database_services,$database_version){
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $this->database_services = $database_services;
        $this->database_version = $database_version;
        register_activation_hook(STORINA_FILE, [$this,"install_database"]);
        register_uninstall_hook(STORINA_FILE, [__CLASS__,"uninstall_database"]);
        add_action('plugins_loaded',[$this,'check_database']);
    }

    public function boot_database_services(){
        foreach($this->database_services as $class => $path){
            require_once $path;
            $this->database_objects[] = new $class;
            $this->database_tables[] = $class::table;
        }
        return $this;
    }

    public function reset_database($current_tables){
        if(empty($current_tables)){
            return $this;
        }
        global $wpdb;
        $tables = implode(",",$current_tables);
        $sql = "DROP TABLE IF EXISTS $tables";
        $wpdb->query($sql);
        return $this;
    }

    public function install_database(){
        $database_objects = $this->database_objects ?: $this->boot_database_services()->database_objects;
        foreach($database_objects as $table_object){
            $table_object->up();
        }
        update_option(self::version_slug,$this->database_version);
        update_option(self::table_slug,$this->database_tables);
        return $this;
    }

    public static function uninstall_database(){
        global $wpdb;
        $current_tables = $current_tables = get_option(self::table_slug) ?? [];
        if(empty($current_tables)) return;
        $tables = implode(",",$current_tables);
        $sql = "DROP TABLE IF EXISTS $tables";
        $wpdb->query($sql);
    }

    public function check_database(){
        $current_version = get_option(self::version_slug) ?? 0;
        if($this->database_version <= $current_version){
            return;
        }
        $current_tables = get_option(self::table_slug) ?? [];
        $this->boot_database_services()->reset_database($current_tables)->install_database();
    }

}
