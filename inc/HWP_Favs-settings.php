<?php

/*
 * @author Shlomi Gottlieb
 *
 * Registing some settings and manage the data from the admin panel
 */

 // Not a WordPress context? Stop.
if(!defined( 'ABSPATH' )) {
    die();
}

class HWP_Favs_Settings {

    /**
	 * Editable fields.
	 *
	 * @type array
	 */
	protected $fields = array();

    /**
	 * Internal prefix
	 *
	 * @type string
	 */
	protected $prefix = 'HWP';

    /**
	 * Option name
	 *
	 * @type string
	 */
	protected static $option_name = 'HWP_settings';

    public function __construct() {
        add_action( 'show_user_profile', array($this, 'extra_favs_column') );
        add_filter( 'manage_users_columns', array($this, 'new_modify_user_table') );
        add_filter( 'manage_users_custom_column', array($this, 'new_modify_user_table_row'), 10, 3 );
        add_action( 'admin_menu', array($this, 'ff_favorites_setting_page') );
        add_action( 'admin_init', array($this, 'plugin_settings') );
    }

    /**
     * Return the option base name
     *
     * @return string
     */
    public static function get_option_name() {
        return self::$option_name;
    }

    /**
     * Adds column to users admin table
     *
     */
    public function extra_favs_column() {
    ?>
        <table id="facebook_user_field_table" class="form-table">
            <tr id="facebook_user_field_row">
                <th>
                    <label for="facebook_field"><?php _e('Number of favorites', 'HWP_favs'); ?></label>
                </th>
                <td>
                    <strong class=""><a href="<?php menu_page_url( 'ff-favorites-settings' ); ?>"><?php echo count(explode(",", get_user_meta( get_current_user_id(), 'favorites', true ))) - 1; ?></a></strong>
                </td>
            </tr>
        </table>
        <?php
    }

    public function new_modify_user_table( $column ) {
        $column['favorites'] = 'Num of Favorites';
        return $column;
    }

    public function new_modify_user_table_row( $val, $column_name, $user_id ) {
        switch ($column_name) {
            case 'favorites' :
                return count(explode(",", get_user_meta( $user_id, 'favorites', true ))) - 1;
                break;
            default:
        }
        return $val;
    }

    public function plugin_settings() {
        $this->fields = array(
			'icon_save' => __( 'Icon save', 'HWP_favs' ),
			'icon_badge' => __( 'Icon badge', 'HWP_favs' )
		);
		// You may extend or restrict the fields.
		$hook_name = $this->prefix . '_fields';
		$this->fields = apply_filters( $hook_name, $this->fields );

        register_setting( self::$option_name, self::$option_name, array($this, 'HWP_settings_validate') );
        add_settings_section('plugin_style', __('Style Settings', 'HWP_favs'), array($this, 'HWP_favs_style_inputs'), 'ff-favorites-settings');
        foreach ( $this->fields as $type => $desc )
		{
			$handle   = self::$option_name . "_$type";
			$args     = array(
				'label_for' => $handle,
				'type'      => $type
			);
			$callback = array( $this, 'print_input_field' );
			add_settings_field(
				$handle,
				$desc,
				$callback,
				'ff-favorites-settings',
				'plugin_style',
				$args
			);
		}
    }

    /**
	 * Input fields in 'wp-admin/options-general.php'
	 *
	 * @param  array $args Arguments
	 * @return void
	 */
    public function print_input_field( $args ){
		$type   = $args['type'];
		$id     = $args['label_for'];
		$data   = get_option( self::$option_name, array() );
		$value  = isset ( $data[ $type ] ) ? $data[ $type ] : '';

		$value  = esc_attr( $value );
		$name   = self::$option_name . '[' . $type . ']';
		$desc   = $this->get_input_help( $type );
		echo "<input type='text' value='$value' name='$name' id='$id'
			class='regular-text' /> <span class='description'>$desc</span>";
	}

    /**
	 * Usage hint for input fields.
	 *
	 * @param  string $type
	 * @return string
	 */
	protected function get_input_help( $type ) {
		$desc = __(
			'You can for example put in here %s.',
			'HWP_favs'
		);
		return sprintf( $desc, "<code>dashicons dashicons-heart</code>" );
	}

    public function HWP_settings_validate($options = array()) {
        $default = get_option( self::$option_name );
        $options = array_map('trim', $options);
        $options['icon_save'] = sanitize_text_field( $options['icon_save'] );
        $options['icon_badge'] = sanitize_text_field( $options['icon_badge'] );
        return $options;
    }

    public function HWP_favs_style_inputs($arg) {
        printf('<p>%s</p>', __('Choose icons for your site.', 'HWP_favs'));
    }

    // Register setting page
    public function ff_favorites_setting_page() {
        add_submenu_page( 'users.php', __('Favorites', 'HWP_favs'), __('Favorites', 'HWP_favs'), 'edit_users', 'ff-favorites-settings', array($this, 'ff_favorites_settings'), 'dashicons-star-filled', 80 );
    }

    public function ff_favorites_settings() {
        ?>
        <h1 class="wp-heading-inline"><?php _e('Manage your Favorites', 'HWP_favs'); ?></h1>
        <h2 class="nav-tab-wrapper">
            <a class="nav-tab <?php echo !isset($_GET['tab']) ? 'nav-tab-active' : ''; ?>" href="<?php menu_page_url( 'ff-favorites-settings' ); ?>" id="tab-general">General</a>
            <a class="nav-tab <?php echo isset($_GET['tab']) && $_GET['tab'] === 'style' ? 'nav-tab-active' : ''; ?>" href="<?php menu_page_url( 'ff-favorites-settings' ); ?>&tab=style" id="tab-style">Style</a>
            <a class="nav-tab <?php echo isset($_GET['tab']) && $_GET['tab'] === 'support' ? 'nav-tab-active' : ''; ?>" href="<?php menu_page_url( 'ff-favorites-settings' ); ?>&tab=support" id="tab-support">Support</a>
        </h2>
        <?php
        if(isset($_GET['tab']) && $_GET['tab'] === 'support') {
            ?>
            <div class="metabox-holder">
                <table style="width:100%;">
                    <tr valign="top">
                        <td>
                            <div class="stuffbox" style="padding:20px;">
                                <h3><?php _e('Show to user how much itemes he saved.', 'HWP_favs'); ?></h3>
                                <p><?php _e('just paste this shortcode:', 'HWP_favs'); ?> <br> <b>[favorites]</b> <br> <?php _e('or in your php file:', 'HWP_favs'); ?> <br> <b>echo do_shortcode("[favorites]");</b></p>
                                <hr>
                                <h3><?php _e('Show to user his favorites list.', 'HWP_favs'); ?></h3>
                                <p><?php _e('just paste this shortcode:', 'HWP_favs'); ?> <br> <b>[favorites_list]</b> <br> <?php _e('or in your php file:', 'HWP_favs'); ?> <br> <b>echo do_shortcode("[favorites_list]");</b></p>
                                <hr>
                                <h3><?php _e('Place a button on single post or single post type for saving the item to user\'s list.', 'HWP_favs'); ?></h3>
                                <p><?php _e('just paste this shortcode:', 'HWP_favs'); ?> <br> <b>[favs-btn]</b> <br> <?php _e('or in your php file:', 'HWP_favs'); ?> <br> <b>echo do_shortcode("[favs-btn]");</b></p>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <?php
            return;

        } else if(isset($_GET['tab']) && $_GET['tab'] === 'style') {
            ?>
                <form method="post" action="options.php">
                    <?php
                    settings_fields( self::$option_name );
                    do_settings_sections( 'ff-favorites-settings' );
                    submit_button(); 
                    ?>
                </form>
                <script>
                    $(document).ready(function(){
                        var iconSave = $("#HWP_favs_icon_save").val();
                        var iconBadge = $("#HWP_favs_icon_badge").val();
                        if(iconSave.length) {
                            $(".show-icon-save i").addClass(iconSave);
                        }
                        if(iconBadge.length) {
                            $(".show-icon-save i").addClass(iconBadge);
                        }
                    });
                </script>
            <?php
            return;
        } else {
            $user_id =  get_current_user_id();
            if(isset($_POST['HWP_favs_delete_all'])) {
                unset($_POST['HWP_favs_delete_all']);
                if (isset( $_POST['delete_all_items'] ) && wp_verify_nonce( $_POST['delete_all_items'], 'HWP_favs_delete_all_nonce' ) ) {
                    delete_user_meta($user_id, 'favorites' );
                }
            }
            if(isset($_GET['delete_one_post']) && isset($_GET['post_id'])) {
                if (isset($_GET['HWP_nonce']) && wp_verify_nonce($_GET['HWP_nonce'], 'delete_one_post')) {
                    $post_id = intval($_GET['post_id']);
                    $favorites = explode(',', get_user_meta($user_id ,'favorites', true));
                    if (array_search($post_id, $favorites) !== false) {
                        $favorites = array_diff($favorites, [$post_id]);
                    }
                    $favorites = implode(",", $favorites);
                    update_user_meta( $user_id, 'favorites', $favorites );       
                }
            }
        }
        
        ?>
        
        <table class="form-table">
            <tbody>
                <tr>
                    <th><?php _e('Number of stored favorites', 'HWP_favs'); ?></th>
                    <td><?php echo count(explode(',', get_user_meta( $user_id, 'favorites', true ))) - 1; ?></td>
                </tr>
                <tr>
                    <th><?php _e('Delete all favorites (cannot be restored!)', 'HWP_favs'); ?></th>
                    <td>
                        <form method="post">
                            <?php wp_nonce_field( 'HWP_favs_delete_all_nonce', 'delete_all_items' ); ?>
                            <button name="HWP_favs_delete_all" type="submit" class="button-link-delete"><?php _e('Delete', 'HWP_favs'); ?></button>
                        </form>
                    </td>
                </tr>
                <tr>
                    <td><h3><?php _e('Favorites list', 'HWP_favs'); ?></h3></td>
                </tr>
                <tr>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Post title', 'HWP_favs'); ?></th>
                                <th class="num"><?php _e('Delete post from the list', 'HWP_favs'); ?></th>
                            </tr>
                        </thead>
                        <tbody class="the-list">
                            <?php $favorites = explode(',' ,get_user_meta( $user_id, 'favorites', true ));
                                foreach ($favorites as $fav) :
                                    if ($fav) : ?>
                                    <tr>
                                        <td><a href="<?php echo get_post_permalink($fav); ?>"><?php echo get_the_title($fav); ?></a></td>
                                        <?php $url = menu_page_url( 'ff-favorites-settings', false )."&delete_one_post&post_id=".$fav;?>
                                        <td class="num"><a href="<?php echo wp_nonce_url($url, 'delete_one_post', 'HWP_nonce'); ?>"><span class="dashicons dashicons-no"></span></a></td>
                                    </tr>
                                <?php
                                    endif;
                                endforeach; 
                            ?>
                        </tbody>
                        
                    </table>
                </tr>
            </tbody>
        </table>

    <?php
    }
}
