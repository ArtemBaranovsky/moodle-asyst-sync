<?php
/**
 * You may have settings in your plugin
 *
 * @package    local_asystgrade
 * @copyright 2024 Artem Baranovskyi
 * @license    http://www.gnu.org/copyleft/gpl.html gnu gpl v3 or later
 */

defined('MOODLE_INTERNAL') || die(); // security check, only internall call through Moodle allowed.

if ($hassiteconfig) {
    global $ADMIN;
    // Ensure the settings page is created under the correct location in the site admin menu.
    $ADMIN->fulltree = true;

    // Create a new settings page for your plugin.
    $settings = new admin_settingpage('local_asystgrade', get_string('pluginname', 'local_asystgrade'));

    // Add the settings page to the admin tree.
    $ADMIN->add('localplugins', $settings);

    // Add your settings here.
    $settings->add(new admin_setting_configtext(
        'local_asystgrade/apiendpoint',
        get_string('apiendpoint', 'local_asystgrade'),
        get_string('apiendpoint_desc', 'local_asystgrade'),
        '',
        PARAM_URL
    ));
}