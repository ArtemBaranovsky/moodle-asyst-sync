<?php
/**
 * Version details.
 *
 * @package   local_asystgrade
 * @copyright 2024 Artem Baranovskyi
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_asystgrade'; // Full name of the plugin.
$plugin->version   = 2024032201;         // The current module version (Date: YYYYMMDDXX).
$plugin->requires  = 2022041900;         // Requires Moodle version 3.11
//$plugin->cron      = 0;
$plugin->maturity  = MATURITY_STABLE;
$plugin->release   = '1.0';

$plugin->dependencies = [
    'mod_quiz' => ANY_VERSION, // This plugin depends on the quiz module
];