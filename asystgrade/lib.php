<?php

/**
 * @package     local_asystgrade
 * @author      Artem Baranovskyi
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

//use Exception;
//use local_asystgrade\api\client;
//require_once $dirroot . 'local_asystgrade\api\client.php';

defined('MOODLE_INTERNAL') || die();

/**
 * A hook function that will process the data and insert the rating value.
 * The function must be called on the desired page like https://www.moodle.loc/mod/quiz/report.php?id=2&mode=grading&slot=1&qid=1&grade=needsgrading&includeauto=1
 *
 * @return void
 */

function local_asystgrade_before_footer()
{
    global $PAGE, $DB;

    // Получение параметров из URL
    $qid  = optional_param('qid', null, PARAM_INT);
    $slot = optional_param('slot', false, PARAM_INT);

    if ($PAGE->url->compare(new moodle_url('/mod/quiz/report.php'), URL_MATCH_BASE) && $slot) {
        $question_attempts = $DB->get_recordset(
            'question_attempts',
            [
                'questionid' => $qid,
                'slot'       => $slot
            ],
            '',
            '*'
        );

        // Obtaining exemplary answer
        $referenceAnswer = $DB->get_record(
            'qtype_essay_options',
            [
                'questionid' => $qid
            ],
            '*',
            MUST_EXIST
        )->graderinfo;

        $studentAnswers = [];
        foreach ($question_attempts as $question_attempt) {

            // Получение всех шагов для данного questionusageid
            $quizattempt_steps = $DB->get_recordset(
                'question_attempt_steps',
                [
                    'questionattemptid' => $question_attempt->id
                ],
                '',
                '*'
            );

            // Processing every quiz attempt step
            foreach ($quizattempt_steps as $quizattempt_step) {
                if ($quizattempt_step->state === 'complete') {
                    $userid        = $quizattempt_step->userid;
                    $attemptstepid = $quizattempt_step->id;

                    // Obtaining student's answer
                    $studentAnswer = $DB->get_record(
                        'question_attempt_step_data',
                        [
                            'attemptstepid' => $attemptstepid,
                            'name'          => 'answer'
                        ],
                        '*',
                        MUST_EXIST
                    )->value;

                    // Forming student's answers array
                    $studentAnswers[] = $studentAnswer;

                    error_log("User ID: $userid, Student Answer: $studentAnswer, Reference Answer: $referenceAnswer");
                }
            }

            // Closing of record's sets
            $quizattempt_steps->close();
        }

        // Closing of record's sets
        $question_attempts->close();

        // API request preparation
        $data = [
            'referenceAnswer' => $referenceAnswer,
            'studentAnswers'  => $studentAnswers
        ];

        error_log('Data prepared: ' . print_r($data, true));

        // Obtaining API settings
        $apiendpoint = get_config('local_asystgrade', 'apiendpoint');
        if (!$apiendpoint) {
            $apiendpoint = 'http://127.0.0.1:5000/api/autograde'; // Default setting
        }

        error_log('APIendpoint: ' . $apiendpoint);

        // Initializing API client
        try {
            $apiClient = new \local_asystgrade\api\client($apiendpoint);
            error_log('ApiClient initiated.');

            // Sending data on API and obtaining auto grades
            error_log('Sending data to API and getting grade');
            $response = $apiClient->send_data($data);
            $grades   = json_decode($response, true);

            error_log('Grade obtained: ' . print_r($grades, true));
        } catch (Exception $e) {
            error_log('Error sending data to API: ' . $e->getMessage());
            return;
        }

        error_log('After API call');

        // Check grades existence and pasting them at grade input fields through JavaScript DOM manipulations
        $script = "
            <script type='text/javascript'>
                document.addEventListener('DOMContentLoaded', function() {";
                    foreach ($grades as $index => $grade) {
                        if (isset($grade['predicted_grade'])) {
                            $predicted_grade = $grade['predicted_grade'] == 'correct' ? 1 : 0;
                            // How forms param name="q2:1_-mark" see at https://github.com/moodle/moodle/blob/main/question/behaviour/rendererbase.php#L132
                            // and https://github.com/moodle/moodle/blob/main/question/engine/questionattempt.php#L381 , L407
                            // TODO: fix question attempt -> ID and question attempt -> step
                            $input_name      = "q" . ($index + 2) . ":1_-mark"; // Q is an question attempt -> ID of mdl_quiz_attempts, :1_ is question attempt -> step
                            $script          .= "
                                console.log('Trying to update input: {$input_name} with grade: {$predicted_grade}');
                                var gradeInput = document.querySelector('input[name=\"{$input_name}\"]');
                                if (gradeInput) {
                                    console.log('Found input: {$input_name}');
                                    gradeInput.value = '{$predicted_grade}';
                                } else {
                                    console.log('Input not found: {$input_name}');
                                }";
                        }
                    }
        $script .= "
            });
        </script>";

        echo $script;
        error_log('URL matches /mod/quiz/report.php in page_init');
    }
}

spl_autoload_register(function ($classname) {
    // Check if the class name starts with our plugin's namespace
    if (strpos($classname, 'local_asystgrade\\') === 0) {
        // Преобразуем пространство имен в путь
        $classname = str_replace('local_asystgrade\\', '', $classname);
        $classname = str_replace('\\', DIRECTORY_SEPARATOR, $classname);
        $filepath  = __DIR__ . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $classname . '.php';

        if (file_exists($filepath)) {
            require_once($filepath);
        }
    }
});
