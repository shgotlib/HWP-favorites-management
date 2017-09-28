<?php
/*
 * @author Shlomi Gottlieb
 *
 * Generates shortcodes for frontend
 */


  // Not a WordPress context? Stop.
if(!defined( 'ABSPATH' )) {
    die();
}

class HWP_Favs_Shortcodes {

    protected $option_name;

    public function __construct() {
        $this->option_name = HWP_Favs_Settings::get_option_name();
        add_shortcode( 'favorites', array($this, 'num_of_favs') );
        add_shortcode( 'favorites_list', array($this, 'favorites_list') );
        add_shortcode( 'favs-btn', array($this, 'save_button') );
    }

    /*
     *
     * Icon displays number of favorites user saved
     */
    private function get_icon($icon_name, $default) {
        $settings = get_option( $this->option_name );
        $icon = $settings[$icon_name];
        if(!$icon) {
            $icon = $default;
        }
        return $icon;
    }

    /*
     *
     * Display an icon with a number of saved post
     */
    public function num_of_favs($page_id = null) {
        $icon = $this->get_icon( 'icon_badge', 'dashicons dashicons-sticky' );
        $page_id = $page_id ? intval($page_id['page_id']) : null;
        $user_id = get_current_user_id();
        $num_of_favs = count(explode(",", get_user_meta( $user_id, 'favorites', true ))) - 1;
        $res = '<i class="'.$icon.' color-white favorites-icon"></i>';
        if($page_id) {
            $res .= '<a target="_blank" href="'.get_page_link($page_id).'" class="go-to-favorite" title="'. __('Your favorite list. open in new tab', 'HWP_favs').'">';
        }
        $res .= '<span tabindex="0" class="bookmarked">'.$num_of_favs.'</span>';
        if($page_id) {
            $res .= '</a>';
        }

        return $res;
    }

    
    /*
     *
     * List of favorites the user was saved
     */
    public function favorites_list() {
        ob_start();
        ?>
        <h2 class="fav-main-title"><?php _e('Your favorite list', 'HWP_favs'); ?></h2>
        <?php
        $favPosts = get_user_meta( get_current_user_id(), 'favorites', true );

        if (isset($favPosts) && strlen($favPosts) > 0) {
            ?>
            <div class="empty-list"></div>
            <div class="list-favs">
                <?php
                $posts = get_posts(array('include' => $favPosts));
                foreach ($posts as $post) {
                    $permalink = get_the_permalink( $post );
                    $img_url = get_the_post_thumbnail_url( $post );
                    $id = get_the_ID();
                    $title = get_the_title($post);
                    $excerpt = get_the_excerpt($post);
                    ?>
                    <div class="fav-item">
                        <div class="">
                            <a href="<?php echo $permalink; ?>"><img class="fav-img" src="<?php echo $img_url; ?>" alt="<?php echo $title; ?>"></a>
                        </div>
                        <div class="">
                            <h3 class="fav-title">
                                <a href="<?php echo $permalink; ?>"><?php echo $title; ?></a>
                            </h3>
                        </div>
                        <div class="">
                            <p class="fav-excerpt"><?php echo $excerpt; ?></p>
                        </div>
                        <span class="badge badge-danger badge-pill badge-delete-fav" role="button" tabindex="0">
                            <i 
                                data-post-id="<?php echo $post->ID; ?>" 
                                data-user-id="<?php echo get_current_user_id(); ?>" 
                                class="dashicons dashicons-no delete-fav" 
                                title="<?php _e('Remove this item from your list','HWP_favs'); ?>">
                            </i>
                        </span>
                    </div>
                    
                    <?php
                }
            echo '</div>'; // .list-favs

        } else if (! is_user_logged_in()) {
                echo __('Log in to the site and you could save articles in your private reading list', 'HWP_favs');
        } else {
            echo __('You have not saved any articles yet :(', 'HWP_favs');
        }
        $res = ob_get_clean();
        return $res;                   
    }

    /*
    * Button to saving post to user's list
    */
    public function save_button() {
        $icon = $this->get_icon('icon_save', 'dashicons dashicons-heart');
        $user_id = get_current_user_id();
        $user_favs = explode(",",get_user_meta( $user_id, 'favorites', true ));

        $fav_class = $icon;
        $fav_class .= ( array_search(get_the_ID(), $user_favs) ) !== false ? ' active' : '';
        $favorited = is_user_logged_in() ? ( array_search(get_the_ID(), $user_favs) ) !== false ? __('Remove this item from your list','HWP_favs') : __('Add this item to your list','HWP_favs') : __('Log in to save this article in the reading list', 'HWP_favs');
        $disabled = !is_user_logged_in() ? 'disabled-save-fav' : '';

        $res = '<span class="save-vaf-parent"><i id="save-fav"
                    tabindex="0"
                    role="button"
                    data-user-id="'.$user_id.'" 
                    data-post-id="'.get_the_ID().'" 
                    class="'.$fav_class.' '.$disabled.'"
                    title="'.$favorited.'"
                    >
                </i></span>';
        return $res;
    }
}
