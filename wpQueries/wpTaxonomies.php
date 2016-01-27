<?php

namespace jadeWP\wpQueries;

class wpTaxonomies {

    public $exclude_categories;

    public function __construct($exclude_categories) {
        $this->exclude_categories = $exclude_categories;
    }

    public function getTaxonomies($postID = 1, $taxonomy = false, $exclude = array(), $exclude_taxonomy = array()) {

        global $wpdb;

        $query = 'SELECT t.term_id, t.name, tt.taxonomy, t.slug, tt.description FROM wp_term_relationships AS tr';
        $query .= ' LEFT JOIN wp_posts AS p ON (p.ID = tr.object_id)';
        $query .= ' LEFT JOIN wp_term_taxonomy AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)';
        $query .= ' LEFT JOIN wp_terms AS t ON (t.term_id = tt.term_id)';
        $query .= ' WHERE p.ID = '.$postID;
        if(!empty($this->exclude_categories)) {
            $query .= ' AND t.slug NOT IN (' . $this->exclude_categories . ')';
        }

        if(is_array($exclude) && sizeof($exclude) > 0 ) {
            foreach ($exclude as $slug) {
                $query .= ' AND t.slug != "'.$slug.'"';
            }
        }

        if(is_array($exclude_taxonomy) && sizeof($exclude_taxonomy) > 0 ) {
            foreach ($exclude_taxonomy as $taxonomy) {
                $query .= ' AND tt.taxonomy != "'.$taxonomy.'"';
            }
        }

        if($taxonomy && is_array($taxonomy) && sizeof($taxonomy) > 0 ) {
            foreach ($taxonomy as $tax) {
                $query .= ' AND tt.taxonomy = "'.$tax.'"';
            }
        }

        $query .= ' GROUP BY t.term_id';

        $categories = $wpdb->get_results($query);

        $tax = $categories;

        wp_reset_query();
        $wpdb->flush();

        return $tax;

    }

    public function getTaxonomiesIds($postID = 1, $taxonomy = false) {

        global $wpdb;

        $query = 'SELECT t.term_id FROM wp_term_relationships AS tr';
        $query .= ' LEFT JOIN wp_posts AS p ON (p.ID = tr.object_id)';
        $query .= ' LEFT JOIN wp_term_taxonomy AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)';
        $query .= ' LEFT JOIN wp_terms AS t ON (t.term_id = tt.term_id)';
        $query .= ' WHERE p.ID = '.$postID;
        if(!empty($this->exclude_categories)) {
            $query .= ' AND t.slug NOT IN (' . $this->exclude_categories . ')';
        }


        if($taxonomy && is_array($taxonomy) && sizeof($taxonomy) > 0 ) {
            $i=0;
            foreach ($taxonomy as $tax) {
                if(!empty($i)) {
                    $query .= ' OR tt.taxonomy = "' . $tax . '"';
                } else {
                    $query .= ' AND (tt.taxonomy = "' . $tax . '"';
                }
                $i++;
                if($i == sizeof($taxonomy)) $query .= ') ';
            }
        }

        $query .= ' GROUP BY t.term_id';

        $categories = $wpdb->get_results($query);
        $i = 0;
        $taxonomy_ids = '';

        foreach($categories AS $category) {
            if(!empty($i)) {
                $taxonomy_ids .= ',';
            }
            $taxonomy_ids .= $category->term_id;
            $i++;
        }

        wp_reset_query();
        $wpdb->flush();

        return $taxonomy_ids;

    }

    public function getChilds($slug, $taxonomy) {
        global $wpdb;

        $query = "SELECT t.term_id, t.slug, t.name FROM $wpdb->term_taxonomy AS tt";
        $query .= " LEFT JOIN $wpdb->terms AS t ON (t.term_id = tt.term_id)";
        $query .= " LEFT JOIN $wpdb->term_taxonomy AS ptt ON (ptt.term_id = tt.parent)";
        $query .= " LEFT JOIN $wpdb->terms AS pt ON (pt.term_id = ptt.term_id)";
        $query .= " WHERE pt.slug = '$slug'";
        $query .= " AND tt.taxonomy = '$taxonomy'";
        $query .= " GROUP BY tt.term_id";
        $categories = $wpdb->get_results($query);

        wp_reset_query();
        $wpdb->flush();

        return $categories;

    }

}