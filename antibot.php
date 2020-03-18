<?php /*
Plugin Name: Antibots
Plugin URI: http://antibotsplugin.com
Description: Anti Bots, SPAM bots and spiders. No DNS or Cloud Traffic Redirection. No Slow Down Your Site!
 * @ Modified time: 2020-03-17 14:48:46
Text Domain: antibots
Domain Path: /language
Author: Bill Minozzi
Author URI: http://antibotsplugin.com
License:     GPL2
Copyright (c) 2016 Bill Minozzi
AntiBots is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
AntiBots_optin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with AntiBots_optin. If not, see {License URI}.
Permission is hereby granted, free of charge subject to the following conditions:
The above copyright notice and this FULL permission notice shall be included in
all copies or substantial portions of the Software.
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
DEALINGS IN THE SOFTWARE.
 */
if (!defined('ABSPATH')) {
    exit;
}
$antibots_maxMemory = @ini_get('memory_limit');
$antibots_last = strtolower(substr($antibots_maxMemory, -1));
$antibots_maxMemory = (int) $antibots_maxMemory;
if ($antibots_last == 'g') {
    $antibots_maxMemory = $antibots_maxMemory * 1024 * 1024 * 1024;
} else if ($antibots_last == 'm') {
    $antibots_maxMemory = $antibots_maxMemory * 1024 * 1024;
} else if ($antibots_last == 'k') {
    $antibots_maxMemory = $antibots_maxMemory * 1024;
}
if ($antibots_maxMemory < 134217728 /* 128 MB */ && $antibots_maxMemory > 0) {
    if (strpos(ini_get('disable_functions'), 'ini_set') === false) {
        @ini_set('memory_limit', '128M');
    }
}
global $wpdb;
define('ANTIBOTSVERSION', '1.0');
define('ANTIBOTSPATH', plugin_dir_path(__file__));
define('ANTIBOTSURL', plugin_dir_url(__file__));
define('ANTIBOTSDOMAIN', get_site_url());
define('ANTIBOTSIMAGES', plugin_dir_url(__file__) . 'assets/images');
define('ANTIBOTsPAGE', trim(sanitize_text_field($GLOBALS['pagenow'])));
$antibots_method = sanitize_text_field($_SERVER["REQUEST_METHOD"]);
$antibotsserver = sanitize_text_field($_SERVER['SERVER_NAME']);
$antibots_request_url = esc_url($_SERVER['REQUEST_URI']);
if (isset($_SERVER['HTTP_REFERER']))
    $antibots_referer = sanitize_text_field($_SERVER['HTTP_REFERER']);
else
    $antibots_referer = '';
$antibots_version = trim(sanitize_text_field(get_site_option('antibots_version', '')));
if (!function_exists('wp_get_current_user')) {
    require_once(ABSPATH . "wp-includes/pluggable.php");
}
$antibots_enable_whitelist = sanitize_text_field(get_option('antibots_enable_whitelist', 'yes'));
$antibots_my_radio_report_all_visits = sanitize_text_field(get_option('antibots_my_radio_report_all_visits', 'no'));
$antibots_my_radio_report_all_visits = strtolower($antibots_my_radio_report_all_visits);
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'antibots_add_action_links');
function antibots_add_action_links($links)
{
    $mylinks = array(
        '<a href="' . admin_url('admin.php?page=settings-anti-bots') . '">Settings</a>',
    );
    return array_merge($links, $mylinks);
}
/* Begin Language */
if (is_admin() or is_super_admin()) {
    if (isset($_GET['page'])) {
        $page = sanitize_text_field($_GET['page']);
        if ($page == 'anti_bots_plugin' or $page == 'antibots_my-custom-submenu-page') {
            $path = dirname(plugin_basename(__FILE__)) . '/language/';
            $loaded = load_plugin_textdomain('antibots', false, $path);
        }
    }
} else {
    add_action('plugins_loaded', 'antibots_localization_init');
}
function antibots_localization_init()
{
    $path = dirname(plugin_basename(__FILE__)) . '/language/';
    $loaded = load_plugin_textdomain('antibots', false, $path);
}
/* End language */
$antibots_active = sanitize_text_field(get_option('antibots_is_active', ''));
$antibots_active = strtolower($antibots_active);
$antibots_keep_data = sanitize_text_field(get_option('antibots_keep_data', '4'));
$antibots_keep_data = strtolower($antibots_keep_data);
$antibots_admin_email = trim(get_option('antibots_my_email_to', ''));
if (!empty($antibots_admin_email)) {
    if (!is_email($antibots_admin_email)) {
        $antibots_admin_email = '';
        update_option('antibots_my_email_to', '');
    }
}
require_once ANTIBOTSPATH . "functions/functions.php";
require_once ABSPATH . 'wp-includes/pluggable.php';
require_once ANTIBOTSPATH . 'dashboard/main.php';
require_once ANTIBOTSPATH . "settings/load-plugin.php";
require_once ANTIBOTSPATH . "settings/options/plugin_options_tabbed.php";
if (is_admin() or is_super_admin()) {
    function antibots_add_admscripts()
    {
        wp_enqueue_style('bill-datatables', ANTIBOTSURL . 'assets/css/dataTables.bootstrap4.min.css');
        wp_enqueue_style('bill-datatables-jquery', ANTIBOTSURL . 'assets/css/jquery.dataTables.min.css');
        wp_enqueue_script('flot', ANTIBOTSURL .
            'assets/js/jquery.flot.min.js', array('jquery'));
        wp_enqueue_script('flotpie', ANTIBOTSURL .
            'assets/js/jquery.flot.pie.js', array('jquery'));
        wp_enqueue_script('botstrap', ANTIBOTSURL .
            'assets/js/bootstrap.bundle.min.js', array('jquery'));
        wp_enqueue_script('easing', ANTIBOTSURL .
            'assets/js/jquery.easing.min.js', array('jquery'));
        wp_enqueue_script('datatables1', ANTIBOTSURL .
            'assets/js/jquery.dataTables.min.js', array('jquery'));
        wp_localize_script('datatables1', 'datatablesajax', array('url' => admin_url('admin-ajax.php')));
        wp_enqueue_script('botstrap4', ANTIBOTSURL .
            'assets/js/dataTables.bootstrap4.min.js', array('jquery'));
        wp_enqueue_script('datatables2', ANTIBOTSURL .
            'assets/js/dataTables.buttons.min.js', array('jquery'));
        wp_register_script('datatables_visitors', ANTIBOTSURL .
            'assets/js/antibots_table.js', array(), '1.0', true);
        wp_enqueue_script('datatables_visitors');
    }
    add_action('admin_enqueue_scripts', 'antibots_add_admscripts', 1000);
}
require_once ANTIBOTSPATH . 'table/visitors.php';
register_activation_hook(__FILE__, 'antibots_plugin_was_activated');