<?php
namespace local_asystgrade\privacy;

/**
 * https://moodledev.io/docs/4.5/apis/subsystems/privacy#implementation-requirements
 * GDPR Implementation requirements
 * In order to let Moodle know that you have audited your plugin, and that you do not store
 * any personal user data, you must implement the \core_privacy\local\metadata\null_provider
 * interface in your plugin's provider.
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem implementation for local_asystgrade.
 *
 * @package    local_asystgrade
 */
class provider implements \core_privacy\local\metadata\null_provider {

    /**
     * Get the reason why this plugin stores no data.
     *
     * @return  string
     */
    public static function get_reason() : string {
        return get_string('privacy:metadata', 'local_asystgrade');
    }
}
