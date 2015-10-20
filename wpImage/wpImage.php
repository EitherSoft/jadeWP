<?php
/**
 * Created by PhpStorm.
 * User: Eugen
 * Date: 16.10.2015
 * Time: 16:07
 */

namespace jadeWP\wpImage;

class wpImage {

    public $siteurl;
    public $imageclass;

    public function __construct($siteurl, $imageclass) {

        $this->siteurl = $siteurl;
        $this->imageclass = $imageclass;

        if(!empty($imageclass)) {
            add_filter('image_send_to_editor', array($this, 'addImageClass'), 10, 8);
        }
    }

    public function getImage($thumb, $size, $pid)
    {

        global $wpdb;

        $imageQuery = $wpdb->get_results("SELECT meta_value FROM $wpdb->postmeta where meta_key = '_thumbnail_id' and post_id = '$pid' LIMIT 0,1");
        $image_id = $imageQuery[0]->meta_value;

        if($image_id) {
            $image_url = wp_get_attachment_image_src($image_id, $thumb);
            $src = $image_url[0];
            if($this->isFile($src)) {
                return $src;
            }
        }

        $query = 'SELECT p.ID FROM wp_posts AS p WHERE p.post_type = "attachment" AND p.post_mime_type LIKE "image%" AND p.post_parent = '.$pid.' ORDER BY p.ID ASC LIMIT 0,1';
        $imageQuery = $wpdb->get_results($query);
        $image_key = $imageQuery[0]->ID;

        if($image_key) {
            $image_url = wp_get_attachment_image_src($image_key, $thumb);
            $src = $image_url[0];
            if($this->isFile($src)) {
                return $src;
            }
        }

        $src = $this->siteurl . $size;

        wp_reset_query();
        $wpdb->flush();

        return $src;

    }


    public function getImageSource($thumb, $size, $iid)
    {

        if($iid) {
            $image_url = wp_get_attachment_image_src($iid, $thumb);
            $src = $image_url[0];
            if($this->isFile($src)) {
                return $src;
            }
        }

        $src = $this->siteurl . $size;
        return $src;
    }

    private function isFile($file) {
        $file_headers = @get_headers($file);
        if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
            return false;
        }
        return true;
    }

    public function addImageClass($html, $id, $caption, $title, $align, $url, $size, $alt = '')
    {
        $classes = $this->imageclass;

        if (preg_match('/<a.*? class=".*?">/', $html)) {
            $html = preg_replace('/(<a.*? class=".*?)(".*?>)/', '$1 ' . $classes . '$2', $html);
        } else {
            $html = preg_replace('/(<a.*?)>/', '$1 class="' . $classes . '" >', $html);
        }
        return $html;
    }

}