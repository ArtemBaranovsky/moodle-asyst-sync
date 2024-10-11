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

use local_asystgrade\db\quizquery;

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
        $quizQuery = new quizquery();

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

        $js_data = [
                'apiendpoint' => 'http://flask:5000/api/autograde',
                'formNames'   => $inputNames,
                'maxmark'     => $maxmark,
                'request'     => [
                        'referenceAnswer' => $data['referenceAnswer'],
                        'studentAnswers'  => array_column($studentData, 'studentAnswer')
                ]
        ];

        $PAGE->requires->js(new moodle_url('/local/asystgrade/js/grade.js', ['v' => time()]));
        $PAGE->requires->js_init_call('M.local_asystgrade.init', [$js_data]);
    }
}

/**
 * Processes question attempts and answers to prepare for API a data to estimate answers
 *
 * @param quizquery $database
 * @param $question_attempts
 * @param $referenceAnswer
 * @return array
 */
function prepare_api_data(quizquery $database, $question_attempts, $referenceAnswer): array
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