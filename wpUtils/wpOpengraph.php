<?php

namespace jadeWP\wpUtils;

use jadeWP\wpImage\wpImage;
use jadeWP\wpUtils\wpThemeoptions;

class wpOpengraph {

    public function __construct() {
        add_filter('language_attributes', array($this,'doctype_opengraph'));
    }

    public static function getOpengraphData() {

        global $themeOptions,$wp_query,$options;
        $post = $wp_query->get_queried_object();

        $wpImage = new wpImage('http://placehold.it/','');
        $wpThemeoptions = new wpThemeoptions($options);
        $themeOptions = $wpThemeoptions->loadOptions();

        $og = array();
        if(is_single()) {
            $og['image'] = $wpImage->getImage('large-thumb', '330x242', $post->ID);
            $og['title'] = $post->post_title;
            $og['url'] = get_the_permalink($post->ID);
            $og['description'] = str_replace('"', "", wp_trim_words(strip_shortcodes($post->post_content), 80));
            $og['site_name'] = $themeOptions['site_name'];
        }
        return $og;
    }

    public function doctype_opengraph($output) {
        return $output . '
    xmlns:og="http://opengraphprotocol.org/schema/"
    xmlns:fb="http://www.facebook.com/2008/fbml"';
    }
}