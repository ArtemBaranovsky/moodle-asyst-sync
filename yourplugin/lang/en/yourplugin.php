<?php
/**
 * You may localized strings in your plugin
 *
 * @package    local_yourplugin
 * @copyright 2024 Artem Baranovskyi
 * @license    http://www.gnu.org/copyleft/gpl.html gnu gpl v3 or later
 */

$string['pluginname'] = 'New local plugin';

// Path to the Python 3 executable
$python_executable = '/opt/myenv/bin/python3';

// Path to the moodlemlbackend script to be executed. First we use test API api.py, then run_LR_SBERT.py
$python_script = '/var/www/html/moodle/api.py';
//$python_script = '/var/www/html/moodle/asyst/Source/Skript/german/run_LR_SBERT.py';

// Python command you want to execute
//$python_command = 'print("Hello, world!!!")';
// Execution the command and getting the result
//shell_exec($python_command);

// Formation of a command to execute
//$full_command = $python_executable . ' -c \'' . $python_command . '\'';
//$full_command = $python_executable . ' ' . $python_script;

// Now call the API
$api_url  = 'http://127.0.0.1:5000/api/data';
$response = file_get_contents($api_url);

// Output the result (assuming backend returns JSON)
if ($response !== false) {
    $data = json_decode($response, true);
    if ($data !== null) {
// Data processing
// Example: output results        echo "<pre>";
        print_r($data);
        echo "</pre>";
    } else {
        echo "Error on data processing from moodlemlbackend!";
    }
} else {
    echo "Execution error: result is not a string.";
}
