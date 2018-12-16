<?php
/**
 * Plugin name: Hide menu
 * Description: This plugin allows you to show-hide admin menu items
 * Version: 1.0.0
 * Author: Glum
 * Text Domain: hide-menu-localization
 * Domain Path: /language
 */

function active_plugin_hide_menu(){
    global $menu;
    $all_menu = array();
    foreach($menu as $item){
        if ( empty($item[0]) || $item[2] == 'hide-menu') continue;
        $pattern = '/(.*?) <(.*?)><(.*?)>(.*?)<\/(.*?)><\/(.*?)>/';
        $replace = '$1';
        $name = preg_replace($pattern, $replace, $item[0] );
        $all_menu[] = array('name' => $name, 'slug'  => $item[2], 'hide' => 0);
    }
    if ( get_option('hide_menu_all_item') ) {
        delete_option('hide_menu_all_item');
    }
    add_option('hide_menu_all_item', serialize($all_menu),'','no');
}
register_activation_hook(__FILE__, 'active_plugin_hide_menu');

add_action( 'plugins_loaded', function(){
    load_plugin_textdomain( 'hide-menu-localization', false, dirname( plugin_basename(__FILE__) ) . '/language' );
} );

function add_admin_menu_page(){
    add_menu_page( __('Hide menu item(s) in the administrative panel of the site','hide-menu-localization'),'HideMenu', 'activate_plugins', 'hide-menu','hide_menu','dashicons-editor-justify', 81);
}
add_action('admin_menu', 'add_admin_menu_page');

if($_SERVER['REQUEST_METHOD'] == "POST"){
    $val = $_POST['hide_menu'];
    $current_val = unserialize(get_option('hide_menu_all_item'));
    $new_val = array();
    foreach ($current_val as $key => $item) {
        if(isset($val[$key])){
            $new_val[] = array('name' => $item['name'], 'slug' => $item['slug'], 'hide' => 1);
        } else {
            $new_val[] = array('name' => $item['name'], 'slug' => $item['slug'], 'hide' => 0);
        }
    }
    update_option('hide_menu_all_item', serialize($new_val), 'no');
}

function hide_menu(){
    ?>
    <div class="wrap">
        <h2><?php echo get_admin_page_title(); ?></h2>
        <form method="POST">
            <?php
            settings_fields( 'hide_menu_group' );
            do_settings_sections('hide-menu');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function hidemenu_setting(){
    register_setting('hide_menu_group', 'hide_menu', array(
        'type'              => 'string',
        'group'             => 'hide_menu_group',
        'description'       => '',
        'sanitize_callback' => null,
        'show_in_rest'      => false,
    ));
    add_settings_section('hide_menu_section', __('Menu items','hide-menu-localization'), '','hide-menu');
    $all_menu = unserialize(get_option('hide_menu_all_item'));
    $i = 0;
    foreach ( $all_menu as $item ) {
        if ( $item['slug'] == 'hide-menu') continue;
        add_settings_field( "item_{$i}", $item['name'],'field_view','hide-menu','hide_menu_section');
        $i++;
    }
}
add_action('admin_init', 'hidemenu_setting');

function field_view(){
    static $num_checkbox = 0;
    $val = unserialize(get_option('hide_menu_all_item'));
    $val = $val ? $val[$num_checkbox]['hide'] : null;
    ?>
    <label><input type="checkbox" name="hide_menu[<?php echo $num_checkbox; ?>]" value="1" <?php checked( 1, $val ) ?> /> <?php _e('Hide','hide-menu-localization');?></label>
    <?php
    $num_checkbox++;
}

function remove_menus(){
    $val = unserialize(get_option('hide_menu_all_item'));
    foreach ($val as $item) {
        if ($item['hide']) {
            remove_menu_page( $item['slug'] );
        }
    }
}
add_action( 'admin_menu', 'remove_menus', 9999 );