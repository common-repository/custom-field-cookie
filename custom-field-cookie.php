<?php
/*
Plugin Name: Custom Field Cookie
Plugin URI: http://peplamb.com/custom-field-cookie/
Description: This plugin checks for all custom keys in a page or a post for custom keys ending with <code>_Custom_Field_Cookie</code> and writes that key as cookie name and its value as cookie value. Please <strong><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=TV873GDVX3MQC&lc=US&item_name=PepLamb&item_number=Custom%20Field%20Cookie&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted" target="_blank">donate</a></strong> to encourage me make more innovative plugins as this, thank you for your support!
Version: 1.1.0
Author: PepLamb
Author URI: http://peplamb.com
*/
/*  Copyright 2009 - 2012 PepLamb (email: peplamb@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
if( !function_exists('Custom_Field_Cookie_get_all_custom_fields') ) {
    function Custom_Field_Cookie_get_all_custom_fields() {
        global $post, $wpdb;
        $custom_fields = $wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE post_id=$post->ID ORDER BY meta_id ASC");
        $custom_field_array = array();
        foreach($custom_fields as $field) {
            $custom_field_array[$field->meta_key] = $field->meta_value;
        }
        return $custom_field_array;
    }
}
function Custom_Field_Cookie_init() { 
    global $post, $wpdb;
    $isCookieSet = false;
    if(is_single() or is_page()) {
        $custom_field_array = Custom_Field_Cookie_get_all_custom_fields();
        foreach($custom_field_array as $key=>$value) {
            if(stristr($key, "_Custom_Field_Cookie")) {
                if(isset($value) and strlen($value) > 0 and $_COOKIE[$key] != $value) {
                    setcookie($key, $value, time()+3600*24*30, "/");
                    $isCookieSet = true;
                }
            }
        }
    }
    if($isCookieSet) {
        header("Location:".get_permalink($post->ID));
        exit;
    }
}
add_action('get_header', 'Custom_Field_Cookie_init');

/**
 * This function gets the present plugin version.
 *
 * @since 1.0.0
 */
function Custom_Field_Cookie_plugin_get_version() {
    if ( ! function_exists( 'get_plugins' ) )
       require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    $plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
    $plugin_file = basename( ( __FILE__ ) );
    return $plugin_folder[$plugin_file]['Version'];
}
/**
 * This function gets the plugin name.
 *
 * @since 1.0.2
 */
function Custom_Field_Cookie_plugin_get_plugin_name() {
    if ( ! function_exists( 'get_plugins' ) )
       require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    $plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
    $plugin_file = basename( ( __FILE__ ) );
    return $plugin_folder[$plugin_file]['Name'];
}
/**
 * This function gets the plugin uri.
 *
 * @since 1.0.2
 */
function Custom_Field_Cookie_plugin_get_plugin_url() {
    if ( ! function_exists( 'get_plugins' ) )
       require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    $plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
    $plugin_file = basename( ( __FILE__ ) );
    return $plugin_folder[$plugin_file]['PluginURI'];
}
/**
 * This function displays the update nag at the top of the
 * dashboard if there is an plugin update available.
 *
 * @since 1.0.0
 */
function Custom_Field_Cookie_update_nag() {
    
    $slug = "custom-field-cookie";
    $file = "$slug/$slug.php";
    
    if(!function_exists('plugins_api'))
        include(ABSPATH . "wp-admin/includes/plugin-install.php");
    $info = plugins_api('plugin_information', array('slug' => $slug ));
    
    if ( !current_user_can('update_plugins') )
        return false;
    if ( stristr(trim($info->version), trim(Custom_Field_Cookie_plugin_get_version())) )
        return false;
    
    $plugin_name = Custom_Field_Cookie_plugin_get_plugin_name();
    $plugin_url = Custom_Field_Cookie_plugin_get_plugin_url();
    if(function_exists('self_admin_url')) {
        $update_url = wp_nonce_url( self_admin_url('update.php?action=upgrade-plugin&plugin=') . $file, 'upgrade-plugin_' . $file);
    }
    else {// to support wp version < 3.1.0
        $update_url = wp_nonce_url( get_bloginfo('wpurl')."/wp-admin/".('update.php?action=upgrade-plugin&plugin=') . $file, 'upgrade-plugin_' . $file);
    }
    $donate_url = "https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=TV873GDVX3MQC&lc=US&item_name=PepLamb&item_number=Custom%20Field%20Cookie&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted";
    
    echo '<div id="update-nag">';
    echo "<strong>$plugin_name</strong> ";
    Custom_Field_Cookie_plugin_print_facebook_like_button();
    echo "<br />";
    printf( __('<a href="%s" target="_blank">%s %s</a> is available! <a href="%s">Please update now</a>. Please consider <a href="%s"><strong>donating</strong></a> to keep me going, Thanks muchly!', $slug), $plugin_url, $plugin_name, $info->version, $update_url, $donate_url );
    echo '</div>';
}
add_action('admin_notices', 'Custom_Field_Cookie_update_nag');

/**
 * Add FAQ and support information.
 *
 * @since 1.0.0
 */
function Custom_Field_Cookie_filter_plugin_links($links, $file) {
    $slug = "custom-field-cookie";
    
    if ( $file == plugin_basename(__FILE__) ) {
        $links[] = '<a href="http://peplamb.com/'.$slug.'/">' . __('FAQ', $slug) . '</a>';
        $links[] = '<a href="http://peplamb.com/'.$slug.'/">' . __('Support', $slug) . '</a>';
        $links[] = '<a href="http://peplamb.com/donate/">' . __('Donate', $slug) . '</a>';
    }
    
    return $links;
}
add_filter('plugin_row_meta', 'Custom_Field_Cookie_filter_plugin_links', 10, 2);

/**
 * Add settings option.
 *
 * @since 1.0.0
 */
function Custom_Field_Cookie_filter_plugin_actions($links) {
    $new_links = array();
    $slug = "custom-field-cookie";
    if(function_exists('self_admin_url')) {
        $new_links[] = '<a href="'.self_admin_url('options-general.php?page='.$slug.'/'.$slug.'.php').'">' . __('Settings', $slug) . '</a>';
    }
    else {// to support wp version < 3.1.0
        $new_links[] = '<a href="'.get_bloginfo('wpurl')."/wp-admin/".('options-general.php?page='.$slug.'/'.$slug.'.php').'">' . __('Settings', $slug) . '</a>';
    }
    
    return array_merge($new_links, $links);
}
add_action('plugin_action_links_' . plugin_basename(__FILE__), 'Custom_Field_Cookie_filter_plugin_actions');

/**
 * This function prints facebook like button.
 *
 * @since 1.0.7
 */
function Custom_Field_Cookie_plugin_print_facebook_like_button() {
    $slug = "custom-field-cookie";
    
    printf( __('<div id="fb-root"></div><script>(function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(d.getElementById(id))return;js=d.createElement(s);js.id=id;js.src="//connect.facebook.net/en_US/all.js#xfbml=1";fjs.parentNode.insertBefore(js,fjs)}(document,\'script\',\'facebook-jssdk\'));</script>', $slug));
    printf( __('<div class="fb-like" data-href="http://peplamb.com/%s/" data-send="true" data-width="450" data-show-faces="true"></div>', $slug), $slug);
}
?>