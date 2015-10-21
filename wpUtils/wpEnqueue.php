<?php

namespace jadeWP\wpUtils;

class wpEnqueue {

    public $css_files;
    public $js_files;
    public $folder;


    public function __construct($css_files=array(),$js_files=array()) {
        $this->css_files = $css_files;
        $this->js_files = $js_files;
        add_action('wp_enqueue_scripts', array($this,'addFiles'));
    }

    public function addFiles() {
        if (!empty($this->css_files) && is_array($this->css_files) && sizeof($this->css_files) > 0) {
            foreach($this->css_files as $file) {
                $footer = false;
                if(isset($file['footer']) && $file['footer'] == true) {
                    $footer = true;
                }
                wp_register_style($file['key'], get_template_directory_uri() . $file['url'], $file['deps'], $file['ver'], $file['media']);
                wp_enqueue_style($file['key']);
            }
        }
        if (!empty($this->js_files) && is_array($this->js_files) && sizeof($this->js_files) > 0) {
            foreach($this->js_files as $file) {
                $footer = false;
                if(isset($file['footer']) && $file['footer'] == true) {
                    $footer = true;
                }
                wp_register_script($file['key'], get_template_directory_uri() . $file['url'], $file['deps'], $file['ver'], $footer);
                wp_enqueue_script($file['key']);
                if(!empty($file['localize']) && is_array($file['localize']) && sizeof($file['localize'] > 0)) {

                    if (is_day()) {
                        $currentDate = get_the_time('Y-m-d');
                    } else {
                        $currentDate = '';
                    }

                    $file['localize']['current_date'] = $currentDate;

                    wp_localize_script($file['key'], 'WPURLS', $file['localize']);
                }
            }
        }
    }
}