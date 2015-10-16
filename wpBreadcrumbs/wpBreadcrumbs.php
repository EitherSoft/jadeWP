<?php

namespace jadeWP\wpBreadcrumbs;

use jadeWP\wpQueries\wpTaxonomies;

class wpBreadcrumbs {

    public $texdomain;
    public $home_icon;
    public $delimeter;
    public $post_types = array();

    public function __construct($home_icon = '<i class="fa fa-home"></i>', $delimeter = '<i class="fa fa-angle-right"></i>',
                                $post_types = array('decoration'=>'decoration-type',
                                    'product'=>'product_cat',
                                    'master'=>'master-type',
                                    'project'=>'design-type'))
    {

        global $themename;
        $this->textdomain = $themename;
        $this->home_icon = $home_icon;
        $this->delimeter = $delimeter;
        $this->post_types = $post_types;

    }

    public function getBreadcrumbs() {
        $breadcrumbs = $this->buildBreadcrumbs();
        return $breadcrumbs;
    }


    private function buildBreadcrumbs()
    {

        $textdomain = $this->textdomain;
        $home_icon = $this->home_icon;
        $delimeter = $this->delimeter;

        global $post, $themename, $exclude_categories;
        $wp_query = $GLOBALS['wp_query'];

        $breadcrumbs = array();
        $homePageText = __('Home', $textdomain);
        $homeLink = get_bloginfo('url');

        $i = 0;

        if (is_home() || is_front_page()) {

            $breadcrumbs[$i]['text'] = $homePageText;
            $breadcrumbs[$i]['link'] = '';
            $breadcrumbs[$i]['icon'] = $home_icon;
            $i++;

        } else {

            $breadcrumbs[$i]['text'] = $homePageText;
            $breadcrumbs[$i]['link'] = $homeLink;
            $breadcrumbs[$i]['icon'] = $home_icon;
            $i++;

            if (is_category()) {

                $term = get_category(get_query_var('cat'), false);
                $breadcrumbs = $this->getTermBreadcrumbs( $breadcrumbs, $i, $term);
                $i = sizeof($breadcrumbs);

            } elseif (is_tax()) {

                $term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));

                foreach($this->post_types as $type=>$taxonomy) {
                    if(get_query_var('taxonomy') == $taxonomy) {
                        $breadcrumbs = $this->getArchiveCrumb($breadcrumbs, $i, $type);
                        $i = sizeof($breadcrumbs);
                    }
                }

                $breadcrumbs = $this->getTermBreadcrumbs( $breadcrumbs, $i, $term);
                $i = sizeof($breadcrumbs);

            } elseif (is_search()) {

                $breadcrumbs[$i]['text'] = __('Search results for :', $textdomain) . ' ' . get_search_query();
                $breadcrumbs[$i]['link'] = '';
                $breadcrumbs[$i]['icon'] = $delimeter;
                $i++;

            } elseif (is_day()) {

                $breadcrumbs[$i]['text'] = get_the_time('Y');
                $breadcrumbs[$i]['link'] = get_year_link(get_the_time('Y'));
                $breadcrumbs[$i]['icon'] = $delimeter;
                $i++;

                $breadcrumbs[$i]['text'] = get_the_time('F');
                $breadcrumbs[$i]['link'] = get_month_link(get_the_time('Y'), get_the_time('m'));
                $breadcrumbs[$i]['icon'] = $delimeter;
                $i++;

            } elseif (is_month()) {

                $breadcrumbs[$i]['text'] = get_the_time('Y');
                $breadcrumbs[$i]['link'] = get_year_link(get_the_time('Y'));
                $breadcrumbs[$i]['icon'] = $delimeter;
                $i++;

                $breadcrumbs[$i]['text'] = get_the_time('F');
                $breadcrumbs[$i]['link'] = '';
                $breadcrumbs[$i]['icon'] = $delimeter;
                $i++;


            } elseif (is_year()) {

                $breadcrumbs[$i]['text'] = get_the_time('Y');
                $breadcrumbs[$i]['link'] = '';
                $breadcrumbs[$i]['icon'] = $delimeter;
                $i++;

            } elseif (is_single() && !is_attachment()) {

                $title = get_the_title();

                if (get_post_type() != 'post') {

                    $breadcrumbs = $this->getArchiveCrumb($breadcrumbs, $i);
                    $i = sizeof($breadcrumbs);

                    $taxonomies = get_object_taxonomies($post);

                    foreach($this->post_types as $type=>$taxonomy) {
                        if(in_array($taxonomy,$taxonomies)) {
                            $terms = get_the_terms($post->id, $taxonomy);
                            $breadcrumbs = $this->getTermBreadcrumbs( $breadcrumbs, $i, $terms[0], true, $taxonomy);
                            $i = sizeof($breadcrumbs);
                        }
                    }

                    $breadcrumbs[$i]['text'] = $title;
                    $breadcrumbs[$i]['link'] = '';
                    $breadcrumbs[$i]['icon'] = $delimeter;
                    $i++;

                } else {

                    $id = get_the_ID();

                    $taxonomies = new wpTaxonomies('');
                    $category = $taxonomies->getTaxonomies($id, array(), array('category'));

                    $breadcrumbs = $this->getTermBreadcrumbs( $breadcrumbs, $i, $category[0], true, 'category');
                    $i = sizeof($breadcrumbs);

                    $breadcrumbs[$i]['text'] = $title;
                    $breadcrumbs[$i]['link'] = '';
                    $breadcrumbs[$i]['icon'] = $delimeter;
                    $i++;

                }

            } elseif (!is_single() && !is_page() && get_post_type() != 'post' && !is_404()) {

                $post_type_name = get_post_type();
                $post_type = get_post_type_object($post_type_name);
                $post_type_link = get_post_type_archive_link($post_type_name);

                $breadcrumbs[$i]['text'] = $post_type->labels->name;
                $breadcrumbs[$i]['link'] = $post_type_link;
                $breadcrumbs[$i]['icon'] = $delimeter;
                $i++;

            } elseif (is_page() && !$post->post_parent) {

                $title = get_the_title();

                $breadcrumbs[$i]['text'] = $title;
                $breadcrumbs[$i]['link'] = '';
                $breadcrumbs[$i]['icon'] = $delimeter;
                $i++;

            } elseif (is_page() && $post->post_parent) {

                $parent_id = $post->post_parent;

                while ($parent_id) {

                    $parent = get_post($parent_id);
                    $breadcrumbs[$i]['text'] = get_the_title($parent->ID);
                    $breadcrumbs[$i]['link'] = get_permalink($parent->ID);
                    $breadcrumbs[$i]['icon'] = $delimeter;
                    $i++;
                    $parent_id = $parent->post_parent;
                    unset($parent);
                }

                unset($parent);
                $title = get_the_title();

                $breadcrumbs[$i]['text'] = $title;
                $breadcrumbs[$i]['link'] = '';
                $breadcrumbs[$i]['icon'] = $delimeter;
                $i++;

            } elseif (is_tag()) {

                $breadcrumbs[$i]['text'] = single_tag_title('', false);
                $breadcrumbs[$i]['link'] = '';
                $breadcrumbs[$i]['icon'] = $delimeter;
                $i++;

            } elseif (is_author()) {
                global $author;
                $userdata = get_userdata($author);

                $breadcrumbs[$i]['text'] = $userdata->display_name;
                $breadcrumbs[$i]['link'] = '';
                $breadcrumbs[$i]['icon'] = $delimeter;
                $i++;

            } elseif (is_404()) {

                $breadcrumbs[$i]['text'] = __('Error 404', $this->textdomain);
                $breadcrumbs[$i]['link'] = '';
                $breadcrumbs[$i]['icon'] = $delimeter;
                $i++;

            }

            if (get_query_var('paged')) {

                $breadcrumbs[$i]['text'] = __('Page') . ' ' . get_query_var('paged');;
                $breadcrumbs[$i]['link'] = '';
                $breadcrumbs[$i]['icon'] = $delimeter;
                $i++;

            }
        }

        return $breadcrumbs;
    }

    private function getTermBreadcrumbs($breadcrumbs, $i, $term, $has_child = false, $taxonomy = 'category') {

        $parents = array();
        $parent = $term->parent;

        if(get_query_var('taxonomy')) {
            $taxonomy = get_query_var('taxonomy');
        }

        while (!empty($parent)) {
            $parents[] = $parent;
            $new_parent = get_term_by('id', $parent, $taxonomy);
            $parent = $new_parent->parent;
        }

        if (!empty($parents)) {
            $parents = array_reverse($parents);
            foreach ($parents as $parent) {
                $item = get_term_by('id', $parent, $taxonomy);
                $breadcrumbs[$i]['text'] = $item->name;
                $breadcrumbs[$i]['link'] = get_term_link($item->slug, $taxonomy);
                $breadcrumbs[$i]['icon'] = $this->delimeter;
                $i++;
            }
        }

        if($has_child) {
            $breadcrumbs[$i]['text'] = $term->name;
            $breadcrumbs[$i]['link'] = get_term_link($term);
            $breadcrumbs[$i]['icon'] = $this->delimeter;
        } else {
            $breadcrumbs[$i]['text'] = $term->name;
            $breadcrumbs[$i]['link'] = '';
            $breadcrumbs[$i]['icon'] = $this->delimeter;
        }
        return $breadcrumbs;
    }

    private function getArchiveCrumb($breadcrumbs, $i, $post_type = 'post') {

        global $post;
        $wp_query = $GLOBALS['wp_query'];

        $post_type_name = get_post_type();

        if(!$post_type_name) {
            $post_type_name = $post_type;
        }
        $post_type = get_post_type_object($post_type_name);
        $post_type_link = get_post_type_archive_link($post_type_name);

        $breadcrumbs[$i]['text'] = $post_type->labels->name;
        $breadcrumbs[$i]['link'] = $post_type_link;
        $breadcrumbs[$i]['icon'] = $this->delimeter;

        return $breadcrumbs;

    }

}