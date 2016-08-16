<?php
/*
 *
 * Plugin Name: Polmekos wp post updater
 * Plugin URI: http://polmekos.pl
 * Description: This plugin automatically updates posts with post title in another category (on save_post).
 * Version: 1.0
 * Author: Michał "Polmekos" Kłosiński
 * Author URI: http://polmekos.pl
 * License: GPL2
 */

class Polmekos_pau{
    public $post_title;
    public $post_meta;

    public function __construct()
    {
        add_action('save_post', array($this, 'polmekos_autoupdate'));
    }

    public function polmekos_autoupdate(){
        global $post;
        global $wpdb;
        $post_title = $_POST['post_title'];
        $post_meta = get_post_meta( $post->ID, 'repertuar', true );
        $post_meta = explode('|', $post_meta);

        if (in_category('concertos')){
            foreach ($post_meta as $rep){
                $query = "SELECT * FROM $wpdb->posts WHERE $wpdb->posts.post_title LIKE '$rep'";
                $results = $wpdb->get_results( $query );
                foreach ($results as $result){
                    $id = $result->ID;
                    $post_content = $result->post_content;
                    $pos = strpos($post_content, $post_title);
                    $oldul = "<ul>";
                    $newul = '<ul><li>' .$post_title. '</li>';
                    $newul2 = '<ul><li>' .$post_title. '</li></ul>';
                    $ulsearch = strpos($post_content, $oldul);

                    if ($ulsearch === false) {
                        $post_content = $post_content . $newul2;
                        $my_post = array(
                            'ID' => $id,
                            'post_content' => $post_content,
                        );

                        remove_action('save_post', array($this, 'polmekos_autoupdate'));
                        wp_update_post($my_post);
                        add_action('save_post', array($this, 'polmekos_autoupdate'));
                    }
                        else if ($pos === false){
//
                            $new_content = str_replace($oldul, $newul, $post_content);
                            $post_content = $new_content;

                            $my_post = array(
                                'ID'           => $id,
                                'post_content' => $post_content,
                            );

                            remove_action('save_post', array($this, 'polmekos_autoupdate'));
                            wp_update_post( $my_post );
                            add_action('save_post', array($this, 'polmekos_autoupdate'));
                        }
                    }
                }

            }
        }



}

$polmekos_pau = new Polmekos_pau();