<?php
/**
 * Created by PhpStorm.
 * User: Eugen
 * Date: 16.10.2015
 * Time: 16:08
 */

namespace jadeWP\wpQueries;

use jadeWP\wpQueries\wpTaxonomies;

class wpRelatedPosts {

    public $limit;
    public $taxonomies;

    public function __construct($limit = 10, $taxonomies= array('')) {
        $this->limit = $limit;
        $this->taxonomies = $taxonomies;
    }

    function queryRelatedPosts() {
        global $wpdb, $post;
        $taxonomies = new wpTaxonomies('');
        $taxonomies = $taxonomies->getTaxonomies($post->ID, array(), array($this->taxonomies));
        $taxonomy_ids = '';
        $i = 0;

        foreach($taxonomies AS $taxonomy) {
            if($i) {
                $taxonomy_ids .= ',';
            }
            $taxonomy_ids .= $taxonomy->term_id;
            $i++;
        }

        $subquery = 'SELECT COUNT(st.term_id) FROM wp_posts AS sp LEFT JOIN wp_term_relationships AS str ON (sp.ID = str.object_id)
                     LEFT JOIN wp_term_taxonomy AS stt ON (str.term_taxonomy_id = stt.term_taxonomy_id)
                     LEFT JOIN wp_terms AS st ON (st.term_id = stt.term_id)
                     WHERE st.term_id IN ('.$taxonomy_ids.') AND sp.ID = p.ID';

        $query = 'SELECT DISTINCT p.ID, p.post_title, p.post_date, thumb.ID AS image, miniature.meta_value AS miniature, t.term_id, t.name';
        $query .= ', ('.$subquery.') as equals';
        $query .= ' FROM wp_posts AS p';
        $query .= ' LEFT JOIN wp_posts AS thumb  ON ( p.ID = thumb.post_parent AND thumb.post_type = "attachment" AND thumb.post_mime_type LIKE "image%")';

        $query .= ' LEFT JOIN wp_postmeta AS miniature ON (miniature.meta_key = "_thumbnail_id" and miniature.post_id = p.ID)';
        $query .= ' LEFT JOIN wp_term_relationships AS tr ON (p.ID = tr.object_id)
    LEFT JOIN wp_term_taxonomy AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
    LEFT JOIN wp_terms AS t ON (t.term_id = tt.term_id)';
        $query .= ' WHERE p.post_type="'.$post->post_type.'" AND p.post_status = "publish"';
        $query .= ' AND t.term_id IN ('.$taxonomy_ids.')';
        $query .= ' AND p.ID != '.$post->ID.'';
        $query .= ' GROUP BY p.ID';
        $query .= ' ORDER BY equals DESC, p.post_date DESC ';
        $query .= ' LIMIT 0,'.$this->limit;

        $postsQuery = $wpdb->get_results($query);
        $posts = $postsQuery;
        wp_reset_query();
        unset($postsQuery);
        return $posts;
    }
}