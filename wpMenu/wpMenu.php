<?php

namespace jadeWP\wpMenu;

class wpMenu {

    public $menuname;

    public function __construct($menuname='menu') {
        $this->menuname = $menuname;
        add_action( 'init', array($this, 'registerMenu') );
    }

    public function registerMenu() {
        global $themename;
        $menuname=$this->menuname;
        register_nav_menus(array($menuname => $menuname));
        if (!term_exists($menuname, 'nav_menu')) {
            $menu = wp_insert_term($menuname, 'nav_menu', array('slug' => $menuname));
        }
    }

    public function getMenu($level) {
        $menuLevel = $this->getMenuLevel($level);
        return $menuLevel;
    }

    private function getMenuLevel($level=0)
    {
        global $wpdb;

        $menuname=$this->menuname;

        if ($menuname) {
            $menuQuery = "SELECT DISTINCT p.post_title, p.post_excerpt, p.ID, pm.meta_value AS menu_type, ppm.meta_value AS parent, (SELECT count(*) FROM wp_postmeta AS c WHERE c.meta_value = p.ID AND c.meta_key = '_menu_item_menu_item_parent') AS child_count FROM wp_posts AS p
        INNER JOIN wp_postmeta AS pm ON (pm.post_id = p.ID AND pm.meta_key = '_menu_item_type')
        INNER JOIN wp_postmeta AS ppm ON (ppm.post_id = p.ID AND ppm.meta_key = '_menu_item_menu_item_parent' )
        INNER JOIN wp_terms AS t ON(t.name = '$menuname')
        INNER JOIN wp_term_taxonomy AS tt ON (tt.term_id = t.term_id)
        INNER JOIN wp_term_relationships AS tr ON(tr.term_taxonomy_id = tt.term_taxonomy_id)
        WHERE p.post_type= 'nav_menu_item' AND p.post_status = 'publish'
        AND ppm.meta_value = ".$level."
        AND p.ID = tr.object_id
        ORDER BY p.menu_order";
        } else {
            $menuQuery = "SELECT DISTINCT p.post_title, p.post_excerpt, p.ID, pm.meta_value AS menu_type, ppm.meta_value AS parent, (SELECT count(*) FROM wp_postmeta AS c WHERE c.meta_value = p.ID AND c.meta_key = '_menu_item_menu_item_parent') AS child_count FROM wp_posts AS p
        INNER JOIN wp_postmeta AS pm ON (pm.post_id = p.ID)
        INNER JOIN wp_postmeta AS ppm ON (ppm.post_id = p.ID)
        WHERE p.post_type= 'nav_menu_item' AND p.post_status = 'publish' AND pm.meta_key = '_menu_item_type' AND ppm.meta_key = '_menu_item_menu_item_parent'
        AND ppm.meta_value = ".$level."
        ORDER BY p.menu_order";
        }

        $menuResult = $wpdb->get_results($menuQuery);
        $menuItems = $menuResult;

        wp_reset_query();
        $wpdb->flush();

        foreach ($menuItems as $nav_item) {
            $nav_item = $this->buildNavItem($nav_item);
        }
        return $menuItems;
    }

    private function buildNavItem($nav_item) {

        $nav_item->class = '';
        $nav_item->megaclass = '';
        $nav_item->mega_menu = '';

        if ($nav_item->child_count) {
            $nav_item->class = 'submenu';
        }

        if (!empty($nav_item->post_excerpt)) {
            $nav_item->class = 'submenu';
            $nav_item->megaclass = 'mega_menu_parent';
            $nav_item->mega_menu = $nav_item->post_excerpt;
        }

        switch ($nav_item->menu_type) {
            case 'custom':
                $nav_item->title = $nav_item->post_title;
                $cLink = get_post_meta($nav_item->ID, '_menu_item_url', true);
                $nav_item->link = $cLink;
                break;
            case 'post_type':
                $nID = get_post_meta($nav_item->ID, '_menu_item_object_id', true);
                if (!empty($nav_item->post_title) && $nav_item->post_title != get_the_title($nID)) {
                    $nav_item->title = $nav_item->post_title;
                } else {
                    $nav_item->title = get_the_title($nID);
                }
                $nav_item->link = get_the_permalink($nID);
                break;
            case 'taxonomy':
                $tID = get_post_meta($nav_item->ID, '_menu_item_object_id');
                $tType = get_post_meta($nav_item->ID, '_menu_item_object');
                $term = get_term_by('id', $tID[0], $tType[0]);
                if (!empty($nav_item->post_title) && $nav_item->post_title != $term->name) {
                    $nav_item->title = $nav_item->post_title;
                } else {
                    $nav_item->title = $term->name;
                }
                $nav_item->link = get_term_link($term);
                break;
            case 'post_type_archive':
                $nav_item->title = $nav_item->post_title;
                $post_type = get_post_meta($nav_item->ID, '_menu_item_object', true);
                $nav_item->link = get_post_type_archive_link($post_type);
                break;
        }

        if($this->isCurrentLink($nav_item->link)) {
            $nav_item->class .= ' current';
        }

        $thumbnail = get_post_meta($nav_item->ID, '_thumbnail_id');

        if(!empty($thumbnail)) {
            $nav_item->thumbnail = $thumbnail[0];
        }

        wp_reset_query();

        return $nav_item;
    }

    private function isCurrentLink($url) {
        global $wp;
        $current_url = home_url().$_SERVER['REQUEST_URI'];

        if((esc_url($url, 'http') == esc_url($current_url, 'http'))
            || (esc_url($url, 'http') == esc_url($current_url.'/', 'http'))
            || (esc_url($url.'/', 'http') == esc_url($current_url, 'http'))
            || (esc_url(home_url().$url) == $current_url)
            || (home_url().$url.'/' == $current_url)) {

            return true;
        }
        return false;
    }
}