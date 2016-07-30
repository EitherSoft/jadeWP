<?php

namespace jadeWP\wpUtils;

class wpThemeoptions {

    public $options;

    public function __construct($options) {
        $this->options = $options;
    }

    public function loadOptions() {

        global $wpdb, $shortname;

        $themeOptions = array();

        if(!empty($this->options)) {
            foreach ($this->options as $option) {
                if (isset($option["id"]) && $option["id"] != '' && $option["id"] != $shortname . '_code')
                    $themeOptions[$option["id"]] = "";
            }
            $themeKeys = '"';
            $themeKeys .= join('","', array_keys($themeOptions));
            $themeKeys .= '"';
            $optionValues = $wpdb->get_results('SELECT option_name, option_value FROM wp_options WHERE option_name IN (' . $themeKeys . ')');


            foreach ($optionValues as $optionValue) {
                $themeOptions[$optionValue->option_name] = $optionValue->option_value;
            }

            $themeOptions['feed'] = get_feed_link();
            $themeOptions['site_name'] = get_bloginfo('name');
            $themeOptions['site_link'] = esc_url(home_url('/'));

            wp_reset_query();
            $wpdb->flush();

            return $themeOptions;
        }
    }
}