<?php
/**
 * Created by PhpStorm.
 * User: Eugen
 * Date: 16.10.2015
 * Time: 16:07
 */

namespace jadeWP\wpQueries;


class wpAuthors {

    public function __construct() {

    }

    public function getAuthors() {
        global $wpdb;

        $query = "SELECT u.ID, u.display_name AS name, ud.meta_value as bio, yim.meta_value as yim, COUNT(p.ID) as post_count, u.user_email, u.user_url FROM wp_users AS u
                  LEFT JOIN wp_usermeta AS ud ON (u.ID = ud.user_id AND ud.meta_key = 'description')
                  LEFT JOIN wp_usermeta AS yim ON (u.ID = yim.user_id AND yim.meta_key = 'yim')
                  LEFT JOIN wp_posts AS p ON (p.post_author = u.ID AND p.post_type='post' AND p.post_status = 'publish')
                  GROUP BY u.ID HAVING COUNT(p.ID) > 0 ORDER BY COUNT(p.ID) DESC";
        $authorsQuery = $wpdb->get_results($query);
        $authors = array();
        foreach($authorsQuery as $author) {
            $authors[$author->ID] = array();
            $authors[$author->ID]['avatar'] = get_avatar_url($author->ID, '150');
            $authors[$author->ID]['url'] = get_author_posts_url($author->ID);
            $authors[$author->ID]['name'] = $author->name;
            $authors[$author->ID]['bio'] = $author->bio;
            $authors[$author->ID]['count'] = $author->post_count;
            $authors[$author->ID]['feed'] = get_author_feed_link($author->ID);
            $authors[$author->ID]['email'] = $author->user_email;
            $authors[$author->ID]['website'] = $author->user_url;
            $authors[$author->ID]['yim'] = $author->yim;
        }
        wp_reset_query();
        return $authors;
    }

    public function getUser($name, $meta = array()) {
        global $wpdb;
        $meta_fields = '';

        if(sizeof($meta) > 0) {
            foreach($meta as $meta_item) {
                $meta_fields .= ",$meta_item.meta_value as value$meta_item";
            }
        }

        $query = "SELECT u.ID, u.display_name, ud.meta_value as bio, u.user_email, u.user_url $meta_fields FROM $wpdb->users AS u";

        $query .= " LEFT JOIN $wpdb->usermeta AS ud ON (u.ID = ud.user_id AND ud.meta_key = 'description')";

        if(sizeof($meta) > 0) {
            foreach($meta as $meta_item) {
                $query .= " LEFT JOIN $wpdb->usermeta AS $meta_item ON (u.ID = $meta_item.user_id AND $meta_item.meta_key = '$meta_item')";
            }
        }

        $query .= " WHERE u.display_name = $name";
        $query .= " GROUP BY u.ID";
        $authorQuery = $wpdb->get_results($query);
        $author_data = $authorQuery[0];
        $author = array();
        $author['avatar'] = get_avatar_url($author_data->ID, '150');
        $author['name'] = $author_data->display_name;
        $author['id'] = $author_data->ID;
        $author['bio'] = $author_data->bio;
        $author['email'] = $author_data->user_email;
        if(sizeof($meta) > 0) {
            foreach($meta as $meta_item) {
                $meta_name = 'value'.$meta_item;
                $author[$meta_item] = $author_data->$meta_name;
            }
        }
        wp_reset_query();
        return $author;
    }

    public function getUsersByRole($role) {
        global $wpdb;
        $query = "SELECT u.ID, u.display_name AS name, u.user_email FROM wp_users AS u
                  LEFT JOIN wp_usermeta AS role ON (u.ID = role.user_id AND role.meta_key = 'wp_capabilities')
                  WHERE role.meta_value LIKE '%$role%'
                  GROUP BY u.ID ";
        $authorsQuery = $wpdb->get_results($query);
        $authors = array();
        foreach($authorsQuery as $author) {
            $authors[$author->ID] = array();
            $authors[$author->ID]['name'] = $author->name;
            $authors[$author->ID]['email'] = $author->user_email;
        }
        wp_reset_query();
        return $authors;
    }

}