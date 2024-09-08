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

use local_asystgrade\api\client;
use local_asystgrade\api\http_client;
use local_asystgrade\db\QuizQuery;

defined('MOODLE_INTERNAL') || die();

/**
 * A hook function that will process the data and insert the rating value.
 * The function must be called on the desired page like https://www.moodle.loc/mod/quiz/report.php?id=2&mode=grading&slot=1&qid=1&grade=needsgrading&includeauto=1
 *
 * @return void
 */

function local_asystgrade_before_footer()
{
    global $PAGE;
    // Obtaining parameters from URL
    $qid = optional_param('qid', null, PARAM_INT);
    $slot = optional_param('slot', false, PARAM_INT);

    if ($PAGE->url->compare(new moodle_url('/mod/quiz/report.php'), URL_MATCH_BASE) && $slot) {
        $quizQuery = new QuizQuery();

        if ($quizQuery->gradesExist($qid, $slot)) {
            error_log('Grades already exist in the database.');
            return;
        }

        $question_attempts = $quizQuery->get_question_attempts($qid, $slot);
        $referenceAnswer = $quizQuery->get_reference_answer($qid);
        $maxmark = (float)$question_attempts->current()->maxmark;
        $data = prepare_api_data($quizQuery, $question_attempts, $referenceAnswer);

        foreach (array_keys($data['studentData']) as $studentId) {
            if ($quizQuery->gradesExist($qid, $studentId)) {
                return;
            }
        }

        $studentData = $data['studentData'];
        $inputNames = array_column($studentData, 'inputName');

        error_log('Data prepared: ' . print_r($data, true));

        $apiendpoint = get_config('local_asystgrade', 'apiendpoint');
        if (!$apiendpoint) {
            $apiendpoint = 'http://flask:5000/api/autograde'; // Default setting, flask is the name of flask container
        }

        error_log('APIendpoint: ' . $apiendpoint);

        try {
            $httpClient = new http_client();
            $apiClient = client::getInstance($apiendpoint, $httpClient);
            error_log('ApiClient initiated.');

            error_log('Sending data to API and getting grade');
            $response = $apiClient->send_data([
                'referenceAnswer' => $data['referenceAnswer'],
                'studentAnswers' => array_column($studentData, 'studentAnswer')
            ]);
            $grades = json_decode($response, true);

            error_log('Grade obtained: ' . print_r($grades, true));
        } catch (Exception $e) {
            error_log('Error sending data to API: ' . $e->getMessage());
            return;
        }

        error_log('After API call');

        pasteGradedMarks($grades, $inputNames, $maxmark);

        error_log('URL matches /mod/quiz/report.php in page_init');
    }
}

/**
 * Adds JavasScript scrypt to update marks
 * @param  array $grades
 * @param  array $inputNames
 * @param  float $maxmark
 * @return void
 */

function pasteGradedMarks(array $grades, array $inputNames, float $maxmark): void
{
    echo generate_script($grades, $inputNames, $maxmark);
}

/**
 * Processes question attempts and answers to prepare for API a data to estimate answers
 *
 * @param QuizQuery $database
 * @param $question_attempts
 * @param $referenceAnswer
 * @return array
 */
function prepare_api_data(QuizQuery $database, $question_attempts, $referenceAnswer): array
{
    $studentData = [];

    foreach ($question_attempts as $question_attempt) {
        $quizattempt_steps = $database->get_attempt_steps($question_attempt->id);

        foreach ($quizattempt_steps as $quizattempt_step) {
            if ($quizattempt_step->state === 'complete') {
                $studentAnswer = $database->get_student_answer($quizattempt_step->id);
                $studentId = $quizattempt_step->userid;
                $inputName = "q" . $question_attempt->questionusageid . ":" . $question_attempt->slot . "_-mark";

                // Adding data to an associative array
                $studentData[$studentId] = [
                    'studentAnswer' => $studentAnswer,
                    'inputName' => $inputName // identifying name for mark input field updating
                ];

                error_log("Student ID: $studentId, Student Answer: $studentAnswer, Input Name: $inputName");
            }
        }

        $quizattempt_steps->close();
    }

    $question_attempts->close();

    return [
        'referenceAnswer' => $referenceAnswer,
        'studentData' => $studentData
    ];
}

/**
 * Builds JavasScript scrypt to update marks using DOM manipulations
 *
 * @param  array $grades
 * @param  array $inputNames
 * @param  float $maxmark
 * @return string
 */
function generate_script(array $grades, array $inputNames, float $maxmark) {
    $script = "<script type='text/javascript'>
        document.addEventListener('DOMContentLoaded', function() {";

    foreach ($grades as $index => $grade) {
        if (isset($grade['predicted_grade'])) {
            $predicted_grade = $grade['predicted_grade'] == 'correct' ? $maxmark : 0;
            $input_name = $inputNames[$index];
            $script .= "
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

    $script .= "});
    </script>";

    return $script;
}

/**
 * Autoloader registration
 */
spl_autoload_register(function ($classname) {
    // Check if the class name starts with our plugin's namespace
    if (strpos($classname, 'local_asystgrade\\') === 0) {
        // Transforming the Namespace into the Path
        $classname = str_replace('local_asystgrade\\', '', $classname);
        $classname = str_replace('\\', DIRECTORY_SEPARATOR, $classname);
        $filepath  = __DIR__ . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $classname . '.php';

        if (file_exists($filepath)) {
            require_once($filepath);
        }
    }
});
