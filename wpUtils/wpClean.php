<?php

namespace jadeWP\wpUtils;

class wpClean {

    public $css;
    public $js;
    public $actions;
    public $filters;


    public function __construct($css=array(),$js=array(),$actions = array(),$filters = array()) {
        $this->css = $css;
        $this->js = $js;
        $this->actions = $actions;
        $this->filters = $filters;
        if(!is_admin()) {
            add_action('wp_enqueue_scripts', array($this, 'cleanFrontend'));
            add_action('init', array($this, 'disableEmojis'));
            add_action('widgets_init', array($this, 'removeCommentsStyle'));
            $this->cleanFrontendActions();
            remove_action('wp_head', 'wp_generator');
            remove_action('wp_head', 'rsd_link');
            remove_action('wp_head', 'wlwmanifest_link');
        } else {
            add_action('wp_enqueue_scripts', array($this, 'cleanAdmin'));
            $this->cleanAdminActions();
        }
    }

    public function cleanFrontend() {
        if (!empty($this->css['frontend']) && is_array($this->css['frontend']) && sizeof($this->css['frontend']) > 0) {
            wp_dequeue_style($this->css['frontend']);
        }
        if (!empty($this->js['frontend']) && is_array($this->js['frontend']) && sizeof($this->js['frontend']) > 0) {
            wp_deregister_script($this->css['frontend']);
        }
    }

    private function cleanFrontendActions() {
        if (!empty($this->actions['frontend']) && is_array($this->actions['frontend']) && sizeof($this->actions['frontend']) > 0) {
            foreach ($this->actions['frontend'] as $key => $value) {
                remove_action($key, $value, 1000);
            }
        }
        if (!empty($this->filters['frontend']) && is_array($this->filters['frontend']) && sizeof($this->filters['frontend']) > 0) {
            foreach ($this->filters['frontend'] as $key => $value) {
                remove_filter($key, $value);
            }
        }
    }

    public function cleanAdmin() {
        if(!empty($this->css['admin']) && is_array($this->css['admin']) && sizeof($this->css['admin']) > 0) {
            wp_dequeue_style($this->css['admin']);
        }
        if(!empty($this->js['admin']) && is_array($this->js['admin']) && sizeof($this->js['admin']) > 0) {
            wp_deregister_script($this->css['admin']);
        }
    }

    private function cleanAdminActions() {
        if(!empty($this->actions['admin']) && is_array($this->actions['admin']) && sizeof($this->actions['admin']) > 0) {
            foreach($this->actions['admin'] as $key=>$value) {
                remove_action($key,$value);
            }
        }
        if(!empty($this->filters['admin']) && is_array($this->filters['admin']) && sizeof($this->filters['admin']) > 0) {
            foreach($this->filters['admin'] as $key=>$value) {
                remove_filter($key,$value);
            }
        }
    }

    public function disableEmojis()
    {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
    }

    public function removeCommentsStyle()
    {
        global $wp_widget_factory;
        remove_action('wp_head', array($wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style'));
    }

}