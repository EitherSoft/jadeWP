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

}