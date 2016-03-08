<?php

namespace jadeWP\wpQueries;

class wpPosts {

    public $exclude;
    public $timeformat;

    public function __construct($exclude='',$timeformat = false) {
        $this->exclude = $exclude;
        $this->timeformat = $timeformat;
    }

    public function queryPosts(
        $fileds = array('p.post_title'),
        $conditions=array(
            'limit'=>10,
            'offset'=>0,
            'order_type'=>'DESC',
            'order_by'=>'p.post_date',
            'post_type'=>'post',
            'tax'=>'news',
            'taxonomy'=>'category',
            'author'=>false,
            'main'=>false,
            'current_post'=>false,
            'require_image'=>false)) {

        global $wpdb,$shortname;

        $selectFields = 'p.ID';
        $image = false;
        $home = false;

        foreach($fileds as $field) {

            if($field == 'p.post_date' && !empty($this->timeformat)) {
                $field = 'DATE_FORMAT(p.post_date, "'.$this->timeformat.'") as post_date';
            }
            $selectFields .= ','.$field;
            if($field == 'image.meta_value as image') {
                $image = true;
            }
            if($field == 'home.meta_value') {
                $home = true;
            }
        }

        $query = 'SELECT DISTINCT '.$selectFields.' FROM wp_posts AS p';

        if($conditions['taxonomy']) {
            $query .= ' INNER JOIN wp_term_relationships AS tr ON (p.ID = tr.object_id)
            INNER JOIN wp_term_taxonomy AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = "' . $conditions['taxonomy'] . '")
            INNER JOIN wp_terms AS t ON (t.term_id = tt.term_id)';
            if($conditions['taxonomy2'] ) {
                $query .= ' INNER JOIN wp_term_relationships AS tr2 ON (p.ID = tr2.object_id)
                INNER JOIN wp_term_taxonomy AS tt2 ON (tr2.term_taxonomy_id = tt2.term_taxonomy_id AND tt2.taxonomy = "' . $conditions['taxonomy2'] . '")
                INNER JOIN wp_terms AS t2 ON (t2.term_id = tt2.term_id)';
            }
        }

        if($conditions['author']) {
            $query .= ' LEFT JOIN wp_users AS u ON (p.post_author = u.ID)';
            $query .= ' LEFT JOIN wp_postmeta AS meta_site ON (p.ID = meta_site.post_id AND meta_site.meta_key = "name_site")';
            $query .= ' LEFT JOIN wp_postmeta AS meta_site_url ON (p.ID = meta_site_url.post_id AND meta_site_url.meta_key = "rel_site_url")';
            $query .= ' LEFT JOIN wp_postmeta AS author_select ON (p.ID = author_select.post_id AND author_select.meta_key = "author_select")';
            $query .= ' LEFT JOIN wp_postmeta AS author_name ON (p.ID = author_name.post_id AND author_name.meta_key = "author_name")';
        }

        if($conditions['main']) {
            $query .= ' LEFT JOIN wp_postmeta AS meta_main ON (p.ID = meta_main.post_id AND meta_main.meta_key = "'.$shortname.'_main")';
        }

        if($home) {
            $query .= ' LEFT JOIN wp_postmeta AS home ON (p.ID = home.post_id AND home.meta_key = "'.$shortname.'_home")';
        }

        if($image) {
            $query .= ' LEFT JOIN wp_postmeta AS image ON (image.meta_key = "_thumbnail_id" and image.post_id = p.ID)';
        }

        if($conditions['meta'] && is_array($conditions['meta']) && sizeof($conditions['meta']) > 0 ) {
            foreach($conditions['meta'] as $meta) {
                $query .= ' LEFT JOIN wp_postmeta AS '.$meta.' ON ('.$meta.'.meta_key = "'.$meta.'" and '.$meta.'.post_id = p.ID)';
            }
        }

        $query .= ' WHERE p.post_status = "publish"';

        if($conditions['post_type']) {
            $query .= ' AND p.post_type="' . $conditions['post_type'] . '" ';
        }

        if($conditions['tax'] && $conditions['taxonomy']) {
            $query .= ' AND t.slug = "' . $conditions['tax'] . '"';
            if($conditions['parent']) {
                $query .= ' OR (SELECT parent.slug FROM wp_terms AS parent WHERE parent.term_id = tt.parent) = "' . $conditions['tax'] . '"';
            }
            if($conditions['tax2'] && $conditions['taxonomy2']) {
                $query .= ' AND t2.slug = "' . $conditions['tax2'] . '"';
            }
        }

        if($conditions['main'] == 'include') {
            $query .= ' AND meta_main.meta_value = "true"';
        } else if($conditions['main'] == 'exclude') {
            $query .= ' AND (meta_main.meta_value != "true" OR meta_main.meta_value IS NULL)';
        }

        if($home) {
            $query .= ' AND home.meta_value = "true"';
        }

        if($conditions['current_post'] && intval($conditions['current_post']) > 0){
            $query .= ' AND p.ID !='.intval($conditions['current_post']);
        }

        if($conditions['date']){
            $query .= ' AND DATE_FORMAT(p.post_date, "%Y-%m-%d") = "'.$conditions['date'].'" ';
        }

        if($image && $conditions['require_image']) {
            $query .= ' AND image.meta_value != ""';
        }

        if($conditions['exclude_tax'] && sizeof($conditions['exclude_tax'] > 0)) {

            $exclude_categories = '';
            $e = 0;
            foreach($conditions['exclude_tax'] as $exclude_category) {
                 if(!$e) {
                     $exclude_categories .= "WHERE t.slug = '".$exclude_category."'";
                 } else {
                     $exclude_categories .= " OR t.slug = '".$exclude_category."'";
                 }
                $e++;
            }

            $query .= " AND p.ID NOT IN (SELECT p.ID FROM $wpdb->posts AS p JOIN $wpdb->term_relationships AS tr ON (p.ID = tr.object_id) JOIN $wpdb->term_taxonomy AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id) JOIN $wpdb->terms AS t ON (t.term_id = tt.term_id) ".$exclude_categories.")";
        }

        $query .= ' GROUP BY p.ID';
        $query .= ' ORDER BY ' . $conditions['order_by'] . ' ' . $conditions['order_type'] . ' ';
        $query .= ' LIMIT ' . $conditions['offset'] . ', ' . $conditions['limit'] . ' ';

        $postsQuery = $wpdb->get_results($query);
        $posts = $postsQuery;

        wp_reset_query();
        $wpdb->flush();

        unset($postsQuery);
        return $posts;

    }

    public function queryPost(
        $fileds = array('p.post_title'),
        $ID = 1,
        $attachments = array()){

        global $wpdb;
        $selectFields = 'p.post_date';
        $image = false;

        foreach($fileds as $field) {

            if($field == 'p.post_date' && !empty($this->timeformat)) {
                $field = 'DATE_FORMAT(p.post_date, "'.$this->timeformat.'") as post_date';
            }
            $selectFields .= ','.$field;
            if($field == 'image.meta_value as image') {
                $image = true;
            }
        }

        $query = 'SELECT DISTINCT '.$selectFields.' FROM wp_posts AS p';

        if($image) {
            $query .= ' LEFT JOIN wp_postmeta AS image ON (image.meta_key = "_thumbnail_id" and image.post_id = p.ID)';
        }

        if(sizeof($attachments) > 0 ) {
            foreach($attachments as $attachment) {
                $query .= " LEFT JOIN $wpdb->postmeta as meta_$attachment ON(meta_$attachment.post_id = $ID AND meta_$attachment.post_id = $ID AND meta_$attachment.meta_key = '$attachment')";
                $query .= " LEFT JOIN $wpdb->postmeta as $attachment ON ($attachment.post_id = meta_$attachment.meta_value AND $attachment.meta_key = '_wp_attached_file')";
            }
        }

        $query .= " WHERE p.post_status = 'publish' AND p.ID = $ID";

        $postQuery = $wpdb->get_results($query);
        $post = $postQuery;

        wp_reset_query();
        $wpdb->flush();

        unset($postsQuery);
        return $post[0];

    }

    public function getMetaAttachment($key, $ID) {

        global $wpdb;

        $query = "SELECT attachment.meta_value as file FROM $wpdb->postmeta AS pm";
        $query .= " JOIN $wpdb->postmeta as attachment ON (attachment.post_id = pm.meta_value AND attachment.meta_key = '_wp_attached_file')";
        $query .= " WHERE pm.post_id = $ID";
        $query .= " AND pm.meta_key = '$key'";
        $attachment = $wpdb->get_results($query);

        wp_reset_query();
        $wpdb->flush();
        return get_site_url().'/wp-content/uploads/'.$attachment[0]->file;
    }

}