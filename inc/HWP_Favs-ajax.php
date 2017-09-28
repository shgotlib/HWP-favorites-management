<?php 
/*
 * @author Shlomi Gottlieb
 *
 * Handlers the AJAX calls from the front end 
 */


 // Not a WordPress context? Stop.
if(!defined( 'ABSPATH' )) {
    die();
}

class HWP_Favs_AJAX {
    /*
     * 
     * Hooks AJAX actions to admin-ajax
     */
    public function __construct() {
        add_action('wp_ajax_HWP_save_fav', array($this, 'HWP_save_fav'));
        add_action('wp_ajax_nopriv_HWP_save_fav', array($this, 'HWP_save_fav'));

        add_action('wp_ajax_HWP_delete_fav', array($this, 'HWP_delete_fav'));
        add_action('wp_ajax_nopriv_HWP_delete_fav', array($this, 'HWP_delete_fav'));
    }

    /*
     * Triggers when user click on save button.
     * @return void
     * before dies, echo the recent number of items user saved 
     *
     */
    public function HWP_save_fav() {
        if (isset($_POST['pId']) && $_POST['pId'] != ''
            && isset($_POST['uId']) && $_POST['uId'] != '') {

            $data = $this->HWP_sanitize_button_data($_POST);
 
            $user_id = $data['user_id'];
            $post_id = $data['post_id'];

            $old_favs = explode(',', get_user_meta( $user_id, 'favorites', true ));

            array_push($old_favs, $post_id);
            $old_favs = array_unique($old_favs);
            $new_favs = implode(",", $old_favs);

            update_user_meta( $user_id, 'favorites', $new_favs );
            echo count(explode(",", get_user_meta( $user_id, 'favorites', true ))) - 1;
            die();
        } 
    }

    /*
     * Triggers when user click on save button.
     * @return void
     * before dies, echo the recent number of items user saved 
     *
     */
    public function HWP_delete_fav() {
        if (isset($_POST['pId']) && $_POST['pId'] != ''
            && isset($_POST['uId']) && $_POST['uId'] != '') {

            $data = $this->HWP_sanitize_button_data($_POST);
 
            $user_id = $data['user_id'];
            $post_id = $data['post_id'];

            $old_favs = explode(',', get_user_meta( $user_id, 'favorites', true ));
            
            if (array_search($post_id, $old_favs) !== false) {
                $old_favs = array_diff($old_favs, [$post_id]);
            }
            $new_favs = implode(",", $old_favs);

            update_user_meta( $user_id, 'favorites', $new_favs );
            echo count(explode(",", get_user_meta( $user_id, 'favorites', true ))) - 1;
            die();
        } 
    }

    private function HWP_sanitize_button_data($arr) {
        $data = array();
        if (!is_numeric($arr['uId']) || !is_numeric($arr['pId'])) {
            return $data;
        }
        $data['user_id'] = intval($arr['uId']);
        $data['post_id'] = intval($arr['pId']);

        return $data;
    }
}


