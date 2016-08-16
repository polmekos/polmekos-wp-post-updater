<?php
/*
 *
 * Plugin Name: Polmekos wp post updater
 * Plugin URI: http://polmekos.pl
 * Description: This plugin automatically updates posts with post title in another category (on save_post).
 * Version: 1.1
 * Author: Michał "Polmekos" Kłosiński
 * Author URI: http://polmekos.pl
 * License: GPL2
 */

class Polmekos_pau{
    public $post_title;
    public $post_meta;

    public function __construct()
    {
        add_action( 'admin_menu', array($this,'polmekos_pau_add_admin_menu' ));
        add_action( 'admin_init', array($this, 'polmekos_pau_settings_init' ));
        add_action('save_post', array($this, 'polmekos_autoupdate'));
    }

    public function polmekos_pau_add_admin_menu(  ) {

        add_options_page( 'Polmekos wp post updater', 'Polmekos wp post updater', 'manage_options', 'polmekos_pau', array($this, 'polmekos_pau_options_page' ));

    }

    public function polmekos_pau_settings_init(  )
    {

        register_setting('PolmekosPau', 'polmekos_pau_settings');

        add_settings_section(
            'polmekos_pau_PolmekosPau_section',
            __( 'You must select category from list below which it trigger the plugin.', 'wordpress' ),
            array($this, 'polmekos_pau_settings_section_callback'),
            'PolmekosPau'
        );
        add_settings_field(
            'polmekos_pau_text_field_0',
            __( 'Category', 'wordpress' ),
            array($this, 'polmekos_pau_text_field_0_render'),
            'PolmekosPau',
            'polmekos_pau_PolmekosPau_section'
        );
    }


    public function polmekos_pau_text_field_0_render(  ) {

        $this->options = get_option( 'polmekos_pau_settings' );
        global $wpdb;
        $query = "SELECT term_id, name FROM $wpdb->terms";
        $cat_search = $wpdb->get_results($query);
        $name = $this->options['polmekos_pau_text_field_0'];
        ?>
        <select name="polmekos_pau_settings[polmekos_pau_text_field_0]">

            <?php
            foreach ($cat_search as $cat_list ) {
                ?>
                <option value="<?php echo $cat_list->term_id; ?>" <?php selected(esc_attr( $this->options['polmekos_pau_text_field_0']), $cat_list->term_id ); ?>><?php echo $cat_list->name; ?></option>
            <?php } ?>
        </select>

        <?php
    }


    public function polmekos_pau_settings_section_callback(  ) {

        echo __( '' );

    }

    public function polmekos_autoupdate(){
        global $post;
        global $wpdb;
        $this->options = get_option( 'polmekos_pau_settings' );
        $post_title = $_POST['post_title'];
        $post_meta = get_post_meta( $post->ID, 'repertuar', true );
        $post_meta = explode('|', $post_meta);
        $cat = $this->options['polmekos_pau_text_field_0'];
        if (in_category($cat)){
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


    public function polmekos_pau_options_page(  ) {

        ?>
        <div class="wrap">
            <h2> Polmekos wp post updater Settings</h2>
            <form action='options.php' method='post'>
                <?php
                settings_fields( 'PolmekosPau' );
                do_settings_sections( 'PolmekosPau' );
                submit_button();
                ?>

            </form>
        </div>
        <?php

    }
}

$polmekos_pau = new Polmekos_pau();