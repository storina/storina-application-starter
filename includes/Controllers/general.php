<?php

namespace STORINA\Controllers;

use \STORINA\Controllers\General;

defined('ABSPATH') || exit;

class General {

    public $log_id;
    public $service_container;

    public function __construct($service_container) {
        $this->service_container = $service_container;
        require_once( ABSPATH . "wp-load.php" );
        add_action('init', array($this, 'initial'));
    }

    public function initial() {
        
    }

    public function get_attr_setting($attribute_id, $key = null) {
        $settings = osa_get_option("_jcaa_attr_settings_{$attribute_id}");

        if ($settings && isset($settings[$key])) {
            return $settings[$key];
        } elseif ($settings && $key == null) {
            return $settings;
        }

        return false;
    }

    public function pre_get_items() {
        global $wpdb, $sessionRecord;
        $googleID = $_POST['googleID'];
        $cart = array();
        $table = $wpdb->prefix . 'OSA_cart';
        $sessionRecord = $wpdb->get_row("SELECT * FROM $table WHERE googleID = '$googleID'");
        $cart = json_decode(@$sessionRecord->cart, true);
        $cart_validate = $this->check_cart_item_stock($cart);
        $cart_diff = array_diff($cart_validate, $cart);
        if (!empty($cart_diff)) {
            $wpdb->update(
                    $table,
                    array('cart' => json_encode($cart_validate, JSON_FORCE_OBJECT)),
                    array('googleID' => $googleID),
                    array('%s'),
                    array('%s')
            );
        }
        return array(
            'cart_old' => $cart,
            'cart_new' => $cart_validate,
            'cart_diff' => $cart_diff,
        );
    }

    public function get_items() {
        $cart_array = $this->pre_get_items();
        return $cart_array['cart_new'];
    }

    public function check_cart_item_stock($cart) {
        $cart_new = $cart;
        if (!isset($cart)) {
            return $cart;
        }
        foreach ($cart as $key => $details) {
            $product_id = (isset($details['variation_id'])) ? $details['variation_id'] : $details['product_id'];
            $product = wc_get_product($product_id);
            if(false == $product instanceof WC_Product){
                unset($cart_new[$key]);
                continue;
            }
            $quantity = $details['quantity'];
            if (!$product->is_in_stock()) {
                unset($cart_new[$key]);
            }
            if (!$product->managing_stock() || $product->backorders_allowed()) {
                continue;
            }
            if ($product->get_stock_quantity() < $quantity) {
                unset($cart_new[$key]);
            }
        }
        return $cart_new;
    }

    public function layeredCategories() {
        $cats = osa_get_option('appProCats');
        $args = array(
            'taxonomy' => 'product_cat',
            'include' => $cats,
            'hide_empty' => false,
        );
        $terms = get_terms('product_cat', $args);
        $data = array();
        foreach ($terms as $term) {
            $thumbnail_id = get_term_meta($term->term_id, 'thumbnail_id', true);
            $catThumb = wp_get_attachment_image_src($thumbnail_id, 'medium');
            if ($catThumb[0] == "") {
                $catThumb[0] = OSA_PLUGIN_URL . "/assets/images/notp.png";
            }
            $tmp = array();
            $tmp['parent'] = array(
                'id' => $term->term_id,
                'title' => $term->name,
                'image' => $catThumb[0]
            );
            $childArgs = array(
                'taxonomy' => 'product_cat',
                'parent' => $term->term_id,
                'hide_empty' => false,
            );
            $childs = get_terms('product_cat', $childArgs);
            $arrayFilteredChilds = array();
            foreach ($childs as $child) {
                $filteredChilds['term_id'] = $child->term_id;
                $filteredChilds['name'] = $child->name;
                $thumbnail_id = get_term_meta($child->term_id, 'thumbnail_id', true);
                $catThumb = wp_get_attachment_image_src($thumbnail_id, 'medium');
                if ($catThumb[0] == "") {
                    $catThumb[0] = OSA_PLUGIN_URL . "/assets/images/notp.png";
                }

                $filteredChilds['image'] = $catThumb[0];
                $arrayFilteredChilds[] = apply_filters("osa_layeredCategories_filterdChilds", $filteredChilds);
            }
            $tmp['childs'] = $arrayFilteredChilds;
            $data[] = $tmp;
        }
        $result = array(
            'status' => true,
            'data' => $data
        );

        return ( $result );
    }

    public function singleComment() {
        $id = $product_id = $_POST['id'];
        $data = array(
            'orderByDate' => array(),
            'orderByLike' => array()
        );
        $page = ( isset($_POST['page']) ) ? $_POST['page'] : 1;
        $count = ( osa_get_option('Archive_product_count') ) ? osa_get_option('Archive_product_count') : 8;
        $offset = ( $page - 1 ) * $count;
        $args = array(
            'post_id' => $id,
            'status' => 'approve',
            'offset' => $offset,
            'number' => $count,
        );
        $comments = get_comments($args);
        foreach ($comments as $comment) {
            $new = strtotime($comment->comment_date);
            $date = ( is_rtl() ) ? OSA_JDate::jdate('Y-m-d h:i:s', $new) : date('Y-m-d h:i:s', $new);

            $like_count = get_comment_meta($comment->comment_ID, 'cld_like_count', true);
            if (empty($like_count)) {
                $like_count = 0;
            }

            $dislike_count = get_comment_meta($comment->comment_ID, 'cld_dislike_count', true);
            if (empty($dislike_count)) {
                $dislike_count = 0;
            }

            $data['orderByDate'][] = array(
                'id' => intval($comment->comment_ID),
                'comment_author' => $comment->comment_author,
                'comment_author_email' => $comment->comment_author_email,
                'comment_date' => $date,
                'comment_content' => $comment->comment_content,
                'cld_like_count' => intval($like_count),
                'cld_dislike_count' => intval($dislike_count),
            );
        }
        $likeArgs = array(
            'post_id' => $id,
            'status' => 'approve',
            'offset' => $offset,
            'number' => $count,
            'meta_query' => array(
                'relation' => 'OR',
                array(//check to see if date has been filled out
                    'key' => 'cld_like_count',
                    'compare' => '>=',
                    'value' => 0
                ),
                array(//if no date has been added show these posts too
                    'key' => 'cld_like_count',
                    'value' => '',
                    'compare' => 'NOT EXISTS'
                )
            ),
            'orderby' => 'meta_value',
            'meta_key' => 'cld_like_count',
            'order' => 'DESC',
        );
        $comments = get_comments($likeArgs);
        foreach ($comments as $comment) {
            $new = strtotime($comment->comment_date);
            $date = ( is_rtl() ) ? OSA_JDate::jdate('Y-m-d h:i:s', $new) : date('Y-m-d h:i:s', $new);

            $like_count = get_comment_meta($comment->comment_ID, 'cld_like_count', true);
            if (empty($like_count)) {
                $like_count = 0;
            }

            $dislike_count = get_comment_meta($comment->comment_ID, 'cld_dislike_count', true);
            if (empty($dislike_count)) {
                $dislike_count = 0;
            }

            $data['orderByLike'][] = array(
                'id' => $comment->comment_ID,
                'comment_author' => $comment->comment_author,
                'comment_author_email' => $comment->comment_author_email,
                'comment_date' => $date,
                'comment_content' => $comment->comment_content,
                'cld_like_count' => intval($like_count),
                'cld_dislike_count' => intval($dislike_count),
            );
        }
        $likeArgs = array(
            'post_id' => $id,
            'status' => 'approve',
            'meta_query' => array(
                array(//if no date has been added show these posts too
                    'key' => 'cld_like_count',
                    'value' => '',
                    'compare' => 'NOT EXISTS'
                )
            ),
            'order' => 'DESC',
            'offset' => $offset,
            'number' => $count,
        );
        $comments = get_comments($likeArgs);
        foreach ($comments as $comment) {
            $new = strtotime($comment->comment_date);
            $date = ( is_rtl() ) ? OSA_JDate::jdate('Y-m-d h:i:s', $new) : date('Y-m-d h:i:s', $new);

            $like_count = get_comment_meta($comment->comment_ID, 'cld_like_count', true);
            if (empty($like_count)) {
                $like_count = 0;
            }

            $dislike_count = get_comment_meta($comment->comment_ID, 'cld_dislike_count', true);
            if (empty($dislike_count)) {
                $dislike_count = 0;
            }

            $data['orderByLike'][] = array(
                'id' => intval($comment->comment_ID),
                'comment_author' => $comment->comment_author,
                'comment_author_email' => $comment->comment_author_email,
                'comment_date' => $date,
                'comment_content' => $comment->comment_content,
                'cld_like_count' => intval($like_count),
                'cld_dislike_count' => intval($dislike_count),
            );
        }
        $final['comments'] = $data;
        $product = wc_get_product($product_id);
        if (is_object($product)) {
            $final['average_rating'] = $product->get_average_rating();
            $final['rating_count'] = intval($product->get_rating_count());
        }
        $result = array(
            'status' => true,
            'data' => $final
        );

        return ( $result );
    }

    public function insertComment() {
        $userToken = $_POST['userToken'];
        $postId = $_POST['post_ID'];
        $rating = $_POST['rating'];
        $comment = $_POST['comment'];
        $author = $_POST['name'];
        $time = current_time('mysql');
        global $wpdb;
        $table_name = $wpdb->prefix . 'usermeta';
        $query = "SELECT *
		FROM $table_name
		WHERE meta_key = 'userToken' AND meta_value = '$userToken'";
        $check_exist = $wpdb->get_row($query);
        $user_id = $check_exist->user_id;
        if ($user_id) {
            $user = get_userdata($user_id);
            $comment_moderation = filter_var(get_option('comment_moderation'),FILTER_VALIDATE_BOOLEAN);
            $data = array(
                'comment_post_ID' => $postId,
                'comment_author' => ( $author ) ? $author : $user->display_name,
                'comment_author_email' => $user->user_email,
                'comment_author_url' => $user->user_url,
                'comment_content' => $comment,
                'comment_type' => '',
                'comment_parent' => 0,
                'user_id' => $user_id,
                'comment_date' => $time,
                'comment_approved' => $comment_moderation,
            );

            if ($commentId = wp_insert_comment($data)) {
                update_comment_meta($commentId, 'rating', $rating);
                $final = array(
                    'status' => true,
                    'data' => array(
                        'comment_approved' => $comment_moderation,
                    )
                );
            } else {

                $final = array(
                    'status' => false,
                    'error' => array(
                        'message' => __('Error in insert comment.', 'onlinerShopApp'),
                        'errorCode' => - 13,
                    )
                );
            }
        } else {
            $user_email = sanitize_email($_POST['user_emaill']);
            $user_url = esc_url($_POST['user_url']);
            $data = array(
                'comment_post_ID' => $postId,
                'comment_author' => $author,
                'comment_author_email' => $user_email,
                'comment_author_url' => $user_url,
                'comment_content' => $comment,
                'comment_type' => '',
                'comment_parent' => 0,
                'comment_date' => $time,
                'comment_approved' => ( osa_get_option('comment_moderation') ) ? 0 : 1,
            );
            if ($commentId = wp_insert_comment($data)) {
                update_comment_meta($commentId, 'rating', $rating);
                $final = array(
                    'status' => true,
                    'data' => array(
                        'comment_approved' => ( osa_get_option('comment_moderation') ) ? "false" : "true",
                    )
                );
            } else {
                $final = array(
                    'status' => false,
                    'error' => array(
                        'message' => __('Error in insert comment.', 'onlinerShopApp'),
                        'errorCode' => - 13,
                    )
                );
            }
        }

        return ( $final );
    }

    public function faq() {
        wp_reset_query();
        $faq_shortcode_id = osa_get_option('app_faq_shortcode_id');
        if (!$faq_shortcode_id) {
            $result = array(
                'status' => true,
                'data' => array(
                    array(
                        'question' => 'سوالی هنوز درج نشده است',
                        'answer' => 'ابتدا سوالات متداول را ثبت و سپس آی دی شرت کد مربوطه را در تنظیمات اپلیکیشن ثبت نمایید.'
                    )
                )
            );

            return ( $result );
        }
        $final = array();
        $count = - 1;
        $wp_query = new WP_Query();
        $faq_args = array('p' => $faq_shortcode_id, 'post_type' => 'sp_easy_accordion');
        $wp_query->query(apply_filters("osa_faq_faq_query_args", $faq_args));
        if ($wp_query->have_posts()):
            while ($wp_query->have_posts()):
                $wp_query->the_post();
                $post_id = get_the_id();
                $upload_data = get_post_meta($post_id, 'sp_eap_upload_options', true);
                if (empty($upload_data)) {
                    return;
                }
                $accordion_type = isset($upload_data['eap_accordion_type']) ? $upload_data['eap_accordion_type'] : '';
                $content_sources = $upload_data['accordion_content_source'];
                foreach ($content_sources as $content_source) {
                    $final[] = array(
                        'question' => $content_source['accordion_content_title'],
                        'answer' => $content_source['accordion_content_description']
                    );
                }

            endwhile;
        endif;

        $result = array(
            'status' => true,
            'data' => $final
        );

        return ( $result );
    }

    public function aboutUS() {
        $final = array(
            'top_logo' => str_replace('https://', 'http://', osa_get_option('app_aboutlogo')),
            'app_slogan' => ( osa_get_option('app_slogan') ) ? osa_get_option('app_slogan') : __('Your shop slogan here.', 'onlinerShopApp'),
            'app_Email' => ( osa_get_option('app_Email') ) ? osa_get_option('app_Email') : 'yorEmail@gmail.com',
            'app_telegramID' => ( osa_get_option('app_telegramID') ) ? osa_get_option('app_telegramID') : 'TelegramID',
            'app_phone' => ( osa_get_option('app_phone') ) ? osa_get_option('app_phone') : '09152222222',
            'app_copyright' => ( osa_get_option('app_copyright') ) ? osa_get_option('app_copyright') : __('All right reserved.', 'onlinerShopApp'),
            'app_privacyLink' => osa_get_option('app_privacyLink'),
            'app_termsLink' => osa_get_option('app_termsLink'),
            'app_aboutLink' => osa_get_option('app_aboutLink'),
            'customButton' => array(
                'text' => osa_get_option('app_aboutButtonText'),
                'link' => osa_get_option('app_aboutButtonLink'),
            )
        );
        $result = array(
            'status' => true,
            'data' => $final
        );

        return ( $result );
    }

    public function Announcements() {
        $posts = array();
        $page = $_POST['page'];
        $count = ( osa_get_option('Archive_product_count') ) ? osa_get_option('Archive_product_count') : 8;
        $offset = ( $page - 1 ) * $count;
        global $wp_query;
        $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
        wp_reset_query();
        $tmp_query = $wp_query;
        $wp_query = new WP_Query();
        $announcements_args = array(
            'post_type' => 'Announcements',
            'offset' => $offset,
            'paged' => $paged,
            'posts_per_page' => $count
        );
        $wp_query->query(apply_filters("osa_Announcements_Announcements_query_args", $announcements_args));
        if ($wp_query->have_posts()):
            while ($wp_query->have_posts()):
                $wp_query->the_post();
                if (has_post_thumbnail()) {
                    $img_id = get_post_thumbnail_id(get_the_id());
                    $src = wp_get_attachment_image_src($img_id, 'medium')[0];
                } else {
                    $src = OSA_PLUGIN_URL . "/assets/images/notp.png";
                }
                $posts[] = array(
                    'id' => get_the_id(),
                    'title' => get_the_title(),
                    'excerpt' => html_entity_decode(wp_strip_all_tags(strip_shortcodes(get_the_excerpt()))),
                    'content' => html_entity_decode(wp_strip_all_tags(strip_shortcodes(get_the_content()))),
                    'image' => $src,
                    'date' => get_the_time('Y/m/d', get_the_ID())
                );
            endwhile;
        endif;
        $wp_query = $tmp_query;
        $result = array(
            'status' => true,
            'data' => $posts
        );

        return ( $result );
    }

    public function blogArchive() {
        $posts = array();
        $cats = ( isset($_POST['id']) ) ? stripslashes($_POST['id']) : osa_get_option('appBlog');
        $page = $_POST['page'];
        $count = ( osa_get_option('Archive_product_count') ) ? osa_get_option('Archive_product_count') : 8;
        $offset = ( $page - 1 ) * $count;
        global $wp_query;
        $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
        wp_reset_query();
        $tmp_query = $wp_query;
        $wp_query = new WP_Query();
        $blog_archive_args = array(
            'cat' => $cats,
            'offset' => $offset,
            'paged' => $paged,
            'posts_per_page' => $count
        );
        $wp_query->query(apply_filters("osa_blogArchive_blogArchive_query_args", $blog_archive_args));
        if ($wp_query->have_posts()):
            while ($wp_query->have_posts()):
                $wp_query->the_post();
                if (has_post_thumbnail()) {
                    $img_id = get_post_thumbnail_id(get_the_id());
                    $src = wp_get_attachment_image_src($img_id, 'medium')[0];
                } else {
                    $src = OSA_PLUGIN_URL . "/assets/images/notp.png";
                }
                $posts[] = array(
                    'id' => get_the_id(),
                    'title' => html_entity_decode(get_the_title()),
                    'excerpt' => html_entity_decode(wp_strip_all_tags(strip_shortcodes(get_the_excerpt()))),
                    'content' => html_entity_decode(wp_strip_all_tags(strip_shortcodes(get_the_content()))),
                    'image' => $src,
                    'date' => get_the_time('Y/m/d', get_the_id())
                );
            endwhile;
        endif;
        $wp_query = $tmp_query;
        $result = array(
            'status' => true,
            'data' => $posts
        );

        return ( $result );
    }

    public function clickEventList() {
        $list = array(
            'AppHome' => __('Open the Home page of app', 'onlinerShopApp'),
            'OpenWebsite' => __('Open the site', 'onlinerShopApp'),
            'OpenTelegramChannel' => __('Open telegram channel', 'onlinerShopApp'),
            'CallNumber' => __('Call', 'onlinerShopApp'),
            'OpenInstagram' => __('Open instagram page', 'onlinerShopApp'),
            'DeepLinkArchive' => __('A custom archive', 'onlinerShopApp'),
            'DeepLinkSingle' => __('A custom product', 'onlinerShopApp'),
            'DeepLinkBlogsingle' => __('A custom article (post)', 'onlinerShopApp'),
            'DeepLinkBlogarchive' => __('A custom article (archive)', 'onlinerShopApp'),
            'VendorListBasedCat' => __('Show Vendor List Based on Category ', 'onlinerShopApp')
        );

        return $list;
    }

    public function clickEvent($type, $value) {
        $onClickModel = array();
        switch ($type) {
            case 'OpenWebsite':
                $onClickModel = array(
                    'type' => 'OpenWebsite',
                    'data' => $this->removeUrlProtocol($value),
                );
                break;
            case 'OpenTelegramChannel':
                $onClickModel = array(
                    'type' => 'OpenTelegramChannel',
                    'data' => $value,
                );
                break;
            case 'CallNumber':
                $onClickModel = array(
                    'type' => 'CallNumber',
                    'data' => $value,
                );
                break;
            case 'OpenInstagram':
                $onClickModel = array(
                    'type' => 'OpenInstagram',
                    'data' => $value,
                );
                break;
            case 'DeepLinkArchive':
                $current_term_level = $this->get_tax_level($value, 'product_cat');
                $onClickModel = array(
                    'type' => 'DeepLinkArchive',
                    'level' => $current_term_level,
                    'data' => $value,
                );
                break;
            case 'DeepLinkSingle':
                $onClickModel = array(
                    'type' => 'DeepLinkSingle',
                    'data' => $value,
                );
                break;
            case 'VendorPage':
                $onClickModel = array(
                    'type' => 'VendorPage',
                    'data' => $value,
                );
                break;
            case 'DeepLinkBlogsingle':
                $onClickModel = array(
                    'type' => 'SingleBlog',
                    'data' => $value,
                );
                break;
            case 'DeepLinkBlogarchive':
                $onClickModel = array(
                    'type' => 'SingleArchive',
                    'data' => $value,
                );
                break;

            case 'AppHome':
                $onClickModel = array(
                    'type' => 'AppHome',
                    'data' => '',
                );
                break;
            case 'VendorListBasedCat':
                $onClickModel = array(
                    'type' => 'VendorListBasedCat',
                    'data' => $value,
                );
                break;
        }

        return $onClickModel;
    }

    public function removeUrlProtocol($url) {
        $url = str_replace('http://', '', $url);
        $url = str_replace('http://www.', '', $url);
        $url = str_replace('https://', '', $url);
        $url = str_replace('https://www.', '', $url);
        $url = trim($url);

        return $url;
    }

    private function get_tax_level($id, $tax) {
        $ancestors = get_ancestors($id, $tax);
        $count = count($ancestors) + 1;

        return ( $count == 1 ) ? 2 : $count;
    }

    public function blogSingle() {
        $id = $_POST['id'];
        $post = get_post($id);
        if (has_post_thumbnail($id)) {
            $img_id = get_post_thumbnail_id($id);
            $src = wp_get_attachment_image_src($img_id, 'medium')[0];
        } else {
            $src = OSA_PLUGIN_URL . "/assets/images/notp.png";
        }
        $post_content = $this->extractContent($post->post_content);
        $post = array(
            'id' => $post->ID,
            'title' => html_entity_decode($post->post_title),
            'content' => $post_content,
            'image' => $src,
            'date' => get_the_time('Y/m/d', $id)
        );
        $result = array(
            'status' => true,
            'data' => $post
        );

        return ( $result );
    }

    public function extractContent($content) {

        $fullContent = html_entity_decode($content);
        $fullContent = stripcslashes($fullContent);
        //$fullContent = str_replace(PHP_EOL,'',$fullContent);
        $fullContent = str_replace(array("\r\n"), "\n", $fullContent);
        $fullContent = str_replace(array("\r", "\t", "\v"), '', $fullContent);
        //$fullContent = str_replace('\t','',$fullContent);
        //$fullContent = wp_strip_all_tags($fullContent);
        /* $fullContent = preg_replace("/(<img\\s)[^>]*(src=\\S+)[^>]*(\\/?>)/i", "$2 {?|}", $fullContent); */

        preg_match_all('/<img[^>]+>/i', $fullContent, $images);
        $fullContent = str_replace("[/caption]", '', $fullContent);
        foreach ($images[0] as $image) {
            preg_match('@src="([^"]+)"@', $image, $match);
            $src = array_pop($match);
            $fullContent = str_replace($image, '{?|} src=' . $src . ' {?|}', $fullContent);
        }
        preg_match_all('/(?:<iframe[^>]*)(?:(?:\/>)|(?:>.*?<\/iframe>))/i', $fullContent, $iframes);
        foreach (current($iframes) as $iframe) {
            preg_match('@src="([^"]+)"@', $iframe, $match);
            $src = array_pop($match);
            $fullContent = str_replace($iframe, '{?|} iframe=' . $src . ' {?|}', $fullContent);
        }
        //preg_match_all( '/<a[^>]+>/i', $fullContent, $links );
        //preg_match_all('/<a[^>]+href=([\'"])(.+?)\1[^>]*>/i', $fullContent, $links); // Grab the href
        preg_match_all('#<a\s+href\s*=\s*"([^"]+)"[^>]*>([^<]+)</a>#i', $fullContent, $links, PREG_SET_ORDER);

        //preg_match('~>\K[^<>]*(?=<)~', $fullContent, $anchor);

        foreach ($links as $link) {
            $fullContent = str_replace($link[0], '{?|} href=' . $link[1] . '|' . $link[2] . ' {?|}', $fullContent);
        }


        $fullContent = preg_replace("/(video\\s)[^>]*(mp4=\\S+)/i", "$2 {?|}", $fullContent);
        $fullContent = str_replace("[audio ", '', $fullContent);
        $fullContent = str_replace("[mp4=", '{?|} mp4=', $fullContent);
        $fullContent = str_replace("][/video]", ' {?|}', $fullContent);
        $fullContent = str_replace("][/audio]", ' {?|}', $fullContent);
        //$fullContent = str_replace("][/video]",'',$fullContent);
        $fullContent = str_replace('src=', '{?|} img=', $fullContent);
        $fullContent = str_replace('mp3=', '{?|} mp3=', $fullContent);
        $fullContent = strip_shortcodes($fullContent);
        $fullContent = wp_strip_all_tags($fullContent);
        $fullContent = explode("{?|}", $fullContent);
        foreach ($fullContent as $item) {
            if (strpos($item, ' img=') !== false) {
                $url = str_replace(' img=', '', $item);
                $url = str_replace('\'', '', $url);
                $url = str_replace('"', '', $url);
                $tmp[] = array(
                    'type' => 'img',
                    'value' => trim($url)
                );
            } elseif (strpos($item, ' mp3=') !== false) {
                $url = str_replace(' mp3=', '', $item);
                $url = str_replace('\'', '', $url);
                $url = str_replace('"', '', $url);
                $tmp[] = array(
                    'type' => 'mp3',
                    'value' => trim($url)
                );
            } elseif (strpos($item, ' mp4=') !== false) {
                $url = str_replace(' mp4=', '', $item);
                $url = str_replace('\'', '', $url);
                $url = str_replace('"', '', $url);
                $tmp[] = array(
                    'type' => 'mp4',
                    'value' => trim($url)
                );
            } elseif (strpos($item, ' href=') !== false) {
                $url = str_replace(' href=', '', $item);
                $url = str_replace('\'', '', $url);
                $url = str_replace('"', '', $url);
                $url = explode('|', $url);
                $tmp[] = array(
                    'type' => 'link',
                    'value' => trim($url[0]),
                    'anchor' => trim($url[1])
                );
            } elseif (strpos($item, 'iframe=')) {
                $src = str_replace(' iframe=', '', $item);
                $src = str_replace('\'', '', $src);
                $src = str_replace('"', '', $src);
                $tmp[] = array(
                    'type' => 'iframe',
                    'value' => $src,
                );
            } else {
                if ($item != ' ') {
                    $tmp[] = array(
                        'type' => 'text',
                        'value' => $item
                    );
                }
            }
        }

        return $tmp;
    }

    public function backorderForm() {
        $masterID = stripcslashes($_POST['id']);
        if (get_post_status($masterID)) {
            $name = stripslashes($_POST['name']);
            $phone = stripslashes($_POST['phone']);
            $email = stripslashes($_POST['email']);
            $desc = stripslashes($_POST['desc']);
            $quantity = stripslashes($_POST['quantity']);
            $sale_email = osa_get_option('app_backorder_email');
            $to = ( $sale_email ) ? $sale_email : osa_get_option('admin_email');
            $subject = 'درخواست پیش خرید محصول توسط ' . $name;
            $title = get_the_title($masterID);
            $link = get_permalink($masterID);
            $headers = array('Content-Type: text/html; charset=UTF-8');
            $body = "
		product id = $masterID<br>
		product link = <A href='$link'>$title</A><br>
		quantity = $quantity<br>
		customer phone = $phone<br>
		customer email = $email<br>
		customer description = $desc<br>
		";
            if ($this->send_email_by_wc($to, $headers, $body, $subject)) {
                $result = array(
                    'status' => true,
                    'data' => 'email sent.'
                );
            } else {
                $result = array(
                    'status' => false,
                    'error' => array(
                        'message' => __('email is not sent.', 'onlinerShopApp'),
                        'errorCode' => - 13,
                    )
                );
            }
        } else {
            $result = array(
                'status' => false,
                'error' => array(
                    'message' => __('product id is not exists.', 'onlinerShopApp'),
                    'errorCode' => - 13,
                )
            );
        }

        return ( $result );
    }

    public function send_email_by_wc($to, $head, $body, $subject, $attachments = '') {
        global $woocommerce;
        $mailer = $woocommerce->mailer();
        $message = $mailer->wrap_message(
                // Message head and message body.
                $subject, $body);
        // Cliente email, email subject and message.
        $headers = array(
            sprintf('Reply-To: %s', $to),
            "From: " . strip_tags($to) . "",
            "Content-Type: text/html; charset=UTF-8"
        );
        $res = wp_mail($to, $subject, $message, $headers, $attachments);

        return true;
    }

    public function Advanced_Qty($post_id) {
        $result = array();
        if (class_exists('Woo_Advanced_Qty_Public')) {
            $Woo_Advanced_Qty_Public = new Woo_Advanced_Qty_Public('WooCommerce Advanced Quantity', '2.4.4');
            $fields = array(
                'min',
                'max',
                'step',
                'value',
                'price-suffix',
                'quantity-suffix'
            );
            foreach ($fields as $field) {
                $value = $Woo_Advanced_Qty_Public->get_option($post_id, $field);
                $result['advanced-qty-' . $field] = ( $value ) ? $value : '';
            }
        }

        return $result;
    }

    public function checkAction($action) {
        if ($action == '?getVersion' || $action == 'getVersion') {
            $versionInfo = array(
                "name" => "shoping application",
                "version" => "5.0.0",
                "download_url" => "https://onlinerapp.ir/apk/plugin/onlinerShopApp.zip",
                "sections" => array(
                    "description" => (string) osa_return_html_content(trailingslashit(OSA_STORAGE) . "version-info.php"),
                )
            );
            wp_send_json($versionInfo);
        }
        if (isset($_POST['action'])) {
            return $_POST['action'];
        } else {
            $result = array(
                'status' => false,
                'data' => array(
                    'protocol' => 'https',
                    'message' => 'Action not set',
                )
            );

            wp_send_json($result);
        }
    }

    public function get_address_fields($type) {
        $tmp = array();
        $countries_obj = new WC_Countries;
        $fields = $countries_obj->get_address_fields(null, $type . '_');
        //$fields = WC()->checkout()->checkout_fields[ $type ];
        $all_countries = $countries_obj->__get('countries');
        $base_country = $countries_obj->get_base_country();
        $allowed_countries = $countries_obj->get_allowed_countries();
        foreach ($fields as $key => $field) {
            if ($key == $type . '_lat' OR $key == $type . '_lng') {
                continue;
            }
            if ($key == $type . '_city') {
                $this->get_states_city($fields[$key]);
            }
            if (isset($fields[$key]['enabled']) AND $fields[$key]['enabled'] == false) {
                unset($fields[$key]);
            }
            if ($key == $type . '_state') {
                $fields[$key]['type'] = 'select';
                $fields[$key]['options'] = array();
            }

            switch ($fields[$key]['type']) {
                case 'country':
                    $fields[$key]['type'] = 'select';
                    $countries = array();
                    foreach ($allowed_countries as $country_code => $country_name) {
                        $countries[] = $country_code;
                    }
                    $fields[$key]['options'] = $countries;
                    break;
                case 'select':
                    $tmps = array();
                    foreach ($fields[$key]['options'] as $option) {
                        $tmps[] = $option;
                    }
                    $fields[$key]['options'] = $tmps;
                    break;
                case 'email':
                case 'tel':
                    $fields[$key]['type'] = 'text';
                    break;
                default:
                    $fields[$key]['type'] = 'text';
                    break;
            }

            if (!isset($fields[$key]['label'])) {
                $fields[$key]['label'] = '';
            }
            if (!isset($fields[$key]['required'])) {
                $fields[$key]['required'] = false;
            }
            if (!isset($fields[$key]['default'])) {
                $fields[$key]['default'] = "";
            }

            if (1 == $fields[$key]['required']) {
                $fields[$key]['required'] = true;
            }

            if (0 == $fields[$key]['required']) {
                $fields[$key]['required'] = false;
            }
            $fields[$key]['id'] = $key;

            $fields[$key]['label'] = apply_filters('osa_index_get_address_fields_label', $fields[$key]['label'], $key);

            unset($fields[$key]['autocomplete']);
            unset($fields[$key]['placeholder']);
            unset($fields[$key]['country_field']);
            unset($fields[$key]['validate']);
            unset($fields[$key]['label_class']);
            unset($fields[$key]['clear']);
            unset($fields[$key]['order']);
            //unset( $fields[ $key ]['custom'] );
            unset($fields[$key]['show_in_email']);
            //unset( $fields[ $key ]['show_in_order'] );
            unset($fields[$key]['class']);
            unset($fields[$key]['input_class']);
            //unset( $fields[ $key ]['enabled'] );
            unset($fields[$key]['user_role']);
            unset($fields[$key]['role_options']);
            unset($fields[$key]['role_options2']);
            unset($fields[$key]['wooccm_required']);
            unset($fields[$key]['cow']);
            unset($fields[$key]['color']);
            unset($fields[$key]['colorpickertype']);
            unset($fields[$key]['fancy']);
            /* if($fields[$key]['type'] == ''){}
              if($fields[$key]['type'] == ''){}

              if($fields[$key]['type'] == 'email'){}
              if($fields[$key]['type'] == 'tel'){} */
            $tmp[] = apply_filters('woap_general_' . $type . '_address_field',$fields[$key]);
        }
        $tmp = $this->asort2d($tmp, 'priority');
        return apply_filters("osa_general_get_address_fields", $tmp);
    }

    private function asort2d($records, $field, $reverse = false) {
// Sort an array of arrays with text keys, like a 2d array from a table query:
        $hash = array();
        foreach ($records as $key => $record) {
            $hash[$record[$field] . $key] = $record;
        }
        ( $reverse ) ? krsort($hash) : ksort($hash);
        $records = array();
        foreach ($hash as $record) {
            $records [] = $record;
        }

        return $records;
    }

    public function sendNotif($title, $body, $icon, $extra) {
        if ('person.bilpay.ir' == $_SERVER['HTTP_HOST']) {
            $token = "/topics/woocommerce.onliner.ir";
        } else {
            $domain = $this->validateDomain($_SERVER['HTTP_HOST'], false, '');
            $domain = $this->get_master_domain($domain);
            $english_app_title = osa_get_option("english_app_name");
            $domain = $this->validateDomain($domain, true, 'app.', $english_app_title);
            $token = "/topics/" . $domain;
        }
        $extra['title'] = $title;
        $extra['body'] = $body;
        $extra['icon'] = ( $icon ) ? $icon : 'https://person.bilpay.ir/2/wp-content/uploads/2018/10/bell.png';
        $extra['sound'] = 'default';
        $extra['badge'] = '1';
        $arrayToSend = array('to' => $token, 'priority' => 'high', "data" => $extra);

        $data = json_encode($arrayToSend);
//FCM API end-point
        $url = 'https://fcm.googleapis.com/fcm/send';
//api_key in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
        $server_key = 'AAAAph5EJno:APA91bE9wjWPSoECBBQ0ndUCCaPMgUfXHC1sJOy3n9maXTzntlHOVGt9wwGIOGLy2PWDLl5R2ZUkf6cuuNsqClCXcIzmjfshbu8X35dCVVJOTsX4KX_ak2WFh-2RGUHTEBOMa-gK6EgW';
//header with content_type api key
        $headers = array(
            'Content-Type:application/json',
            'Authorization:key=' . $server_key
        );
//CURL request to route notification to FCM connection server (provided by Google)
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        $ua = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
        if ($result === false) {
            die('Oops! FCM Send Error: ' . curl_error($ch));
        } else {
            return json_decode($result);
        }
        curl_close($ch);

        return false;
    }

// end function asort2d

    function validateDomain($domain, $removeNumber, $prefix, $titleEN = '') {
        $domain = strtolower($domain);
        $domain = $this->removeUrlProtocol($domain);
        $domain = trim($domain, '/');
        if ($removeNumber) {
            $domain = preg_replace('/[0-9]+/', '', $domain);
            $domain = str_replace('-', '', $domain);
            $domain = str_replace('/', '', $domain);
            if (strlen($domain) <= 6) {
                $domain = $titleEN . $domain;
            }
            $domain = $prefix . $domain;
        }

        return $domain;
    }

    public function get_master_domain($domain) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://storina.com/tR92l0x5Pb9s3Qj9nhfY/app_api.php');
        $ua = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
                "action=getMasterDomain&url=$domain");

// In real life you should use something like:
// curl_setopt($ch, CURLOPT_POSTFIELDS,
//          http_build_query(array('postvar1' => 'value1')));
// Receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $server_output = curl_exec($ch);
        $result = json_decode($server_output);
        curl_close($ch);

// Further processing ...
        if ($result->status == true) {
            return $result->url;
        } else {
            return 'ERROR';
        }
    }

    public function get_vendor_towns() {
        $general = $this->service_container->get(General::class);
        $type = osa_get_option('appVendorlist');
        $app_vendor_grouping = osa_get_option('app_vendor_grouping');
        $states = array();
        $vendor_ids = $general->vendor_ids();

        if ($app_vendor_grouping == 'true') {
            if ($type == 'state') {
                if (osa_get_option('app_hidden_empty_state') == 'true') {
                    foreach ($vendor_ids as $vendor_id) {
                        //$vendorOrig                         = get_userdata( $activeVendors[ $i ]->ID );
                        $store_settings = dokan_get_store_info($vendor_id);
                        $state = $this->get_states($store_settings['address']['state']);
                        if (count($state) > 1) {
                            continue;
                        }
                        $key = array_search($store_settings['address']['state'], array_column($states, 'EN'));
                        if ($key === false) {
                            $states[] = array(
                                'fa' => $state,
                                'EN' => $store_settings['address']['state']
                            );
                        }
                    }
                } else {
                    $states = $this->get_states();
                }
                $result = array(
                    'status' => true,
                    'data' => $states
                );
            } else {
                foreach ($vendor_ids as $vendor_id) {
                    //$vendorOrig                         = get_userdata( $activeVendors[ $i ]->ID );
                    $store_settings = dokan_get_store_info($vendor_id);
                    $key = array_search($store_settings['address']['city'], array_column($states, 'EN'));
                    if ($key === false) {
                        $states[] = array(
                            'fa' => $store_settings['address']['city'],
                            'EN' => $store_settings['address']['city']
                        );
                    }
                }
                $result = array(
                    'status' => true,
                    'data' => $states
                );
            }
        } else {
            $result = array(
                'status' => false,
                'data' => array()
            );
        }


        return ( $result );
    }

    public function vendor_ids() {
        if (!function_exists('dokan_is_seller_enabled')) {
            return array();
        }
        $count = - 1;
        $args = array(
            'role__in' => array('seller', 'administrator'),
            'number' => $count,
            'fields' => array('ID'),
        );
        $town = $_POST['vendor_town'];
        $AllVendors = get_users($args);
        $activeVendors = array();
        $type = osa_get_option('appVendorlist');
        $type = ( $type == 'state' ) ? $type : 'city';
        foreach ($AllVendors as $vendor) {
            if (dokan_is_seller_enabled($vendor->ID)) {
                if (strlen($town) == 0 OR ! $town) {
                    $activeVendors[] = $vendor->ID;
                } else {

                    $store_settings = dokan_get_store_info($vendor->ID);
                    if ($store_settings['address'][$type] == $town) {
                        $activeVendors[] = $vendor->ID;
                    }
                }
            }
        }

        return ( empty($activeVendors) ) ? array(- 1) : $activeVendors;
    }

    public function vendor_ids_based_category_id() {
        if (!function_exists('dokan_is_seller_enabled')) {
            return array();
        }
        $category_id = $_POST['cat_id'];
        $category_list = array_map('intval', get_term_children($category_id, "product_cat"));
        $category_list[] = (int) $category_id;
        $args = array(
            'post_type' => array('product'),
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $category_list,
                    'operator' => 'IN',
                ),
            ),
        );
        $vendor_lists = array();
        $products = get_posts($args);
        foreach ($products as $product) {
            $vendor_lists[] = $product->post_author;
        }
        array_map('intval', $vendor_lists);
        return array_values(array_unique($vendor_lists));
    }

    public function get_states($key = null) {
        $find = null;
        $states = array();
        $countries_obj = new WC_Countries();

        //$countries   = $countries_obj->__get('countries');
        $default_country = $countries_obj->get_base_country();
        $default_county_states = $countries_obj->get_states($default_country);
        foreach ($default_county_states as $index => $default_county_state) {
            if ($key == $index) {
                $find = $default_county_state;
            }
            $en = ( is_numeric($index) ) ? strval($index) : $index;
            $states[] = array('fa' => $default_county_state, 'EN' => $en);
        }

        return ( $find ) ? $find : $states;
    }

    public function getStrings() {
        $lng = $_POST['lng'];
        $result = array(
            'status' => true,
            'data' => array(
                1 => 'Hi',
                2 => 'Cart',
                3 => 'Blog',
                4 => 'Vendors',
                5 => 'Exit',
                6 => 'Add to cart',
                7 => 'Checkout',
                8 => 'Shipping',
            )
        );

        return ( $result );
    }

    public function get_states_city(&$city_field) {
        if (!class_exists("PWS_state_city_taxonomy")) {
            return;
        }
        $states = $this->get_states();
        foreach ($states as $state) {
            $parent_term_id = $state['EN'];
            $childrens_args = array("taxonomy" => "state_city", "hide_empty" => false, 'parent' => $parent_term_id);
            $childrens = get_terms($childrens_args);
            foreach ($childrens as $children) {
                $area[] = array(
                    "key" => $children->term_id,
                    "value" => $children->name
                );
            }
        }
        $city_field['type'] = "select";
        $city_field['options'] = $area;
    }

    public function getState() {
        $countries_obj = new WC_Countries;
        $country_code = $_POST['country'];
        $states = array();
        foreach ($countries_obj->get_states($country_code) as $code => $name) {
            $key['fa'] = $name;
            $key['EN'] = $code;
            $states[] = $key;
        }
        return array(
            "status" => true,
            "data" => $states
        );
    }

    public function staticContents(){
        return array(
            osa_get_option('app_privacy_policy'),
            osa_get_option('app_terms_conditions'),
            osa_get_option('app_shopping_guide'),
        );
    }

    public function cities(){
        if(!function_exists('PWS')){
            return [];
        }
        $data =  
            (PWS_Tapin::is_enable())?
            woap_pws_prepare_tapin_cities() : 
            woap_pws_prepare_regular_cities();
        return [
            'status' => true,
            'data' => $data
        ];
    }

}
