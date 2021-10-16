<?php

namespace VOS\Providers;

class CronProvider {

    public $event_queue;
    const version = 1;
    const version_slug = 'vos_cron_version';
    const event_queue_slug = 'vos_cron_events';

    public function __construct($event_queue){
        $this->event_queue = $event_queue;
        register_activation_hook(VOS_FILE,[$this,'register_event_queue']);
        add_action('init',[$this,'update_event_queue']);
    }

    public function register_event_queue(){
        $event_queue = $this->event_queue;
        $tomorrow = get_gmt_from_date("tomorrow 00:00:01", "U");
        foreach($event_queue as $recurrence => $event){
            if(!wp_next_scheduled( $event )){
                wp_schedule_event($tomorrow, $recurrence, $event);
            }
        }
        return $this;
    }

    public function unregister_event_queue(){
        $event_queue = get_option(self::event_queue_slug) ?: [];
        foreach($event_queue as $event){
            if(wp_next_scheduled( $event )){
                wp_clear_scheduled_hook($event);
            }
        }
        return $this;
    }

    public function update_event_queue(){
        $new_version = self::version;
        $old_version = get_option(self::version_slug) ?: 0;
        if($new_version <= $old_version){
            return;
        }
        $this->unregister_event_queue()->register_event_queue();
        update_option(self::event_queue_slug,$this->event_queue);
        update_option(self::version_slug,$new_version);
    }

}