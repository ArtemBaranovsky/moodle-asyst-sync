<?php

use local_asystgrade\api\client;
use local_asystgrade\api\http_client;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/phpunit/classes/advanced_testcase.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/tests/generator/lib.php');

class quiz_api_test extends advanced_testcase
{

    protected function setUp(): void
    {
        global $DB;

        $this->resetAfterTest();
        parent::setUp();

        $DB->execute('TRUNCATE TABLE {quiz_attempts}');
        $DB->execute('TRUNCATE TABLE {quiz_slots}');
    }

    /**
     * @throws Exception
     */
    public function test_quiz_api()
    {
        global $DB;

        $generator   = $this->getDataGenerator();
        $quizgen     = $generator->get_plugin_generator('mod_quiz');
        $questiongen = $generator->get_plugin_generator('core_question');

        // Create a course
        $coursegen = $generator->create_course([
            'fullname'  => 'Test Course',
            'shortname' => 'testcourse',
            'category'  => 1,
        ]);

        $teacher       = $this->getDataGenerator()->create_user();
        $teacherRoleId = $DB->get_record('role', ['shortname' => 'teacher'])->id;
        $this->getDataGenerator()->enrol_user($teacher->id, $coursegen->id, $teacherRoleId);
        $this->setUser($teacher);

        // Create a quiz in the course
        $quiz = $quizgen->create_instance([
            'course'    => $coursegen->id,
            'name'      => 'Test Quiz',
            'intro'     => 'This is a test quiz.',
            'attempts'  => 1,
            'timeopen'  => time(),
            'timeclose' => time() + 3600,
        ]);

        // Create questions and answers
        $questions = include_once 'fakedata/questions.php';

        // Create a question category
        $context  = context_course::instance($coursegen->id);
        $category = $this->create_question_category($context->id);

        foreach ($questions as $questiondata) {
            // Create a question
            $question = $questiongen->create_question($questiondata['qtype'], null, [
                'category'     => $category->id, // question category
                'questiontext' => [
                    'text'   => $questiondata['questiontext'],
                    'format' => FORMAT_HTML,
                ],
                'name'         => 'Test Question',
                'contextid'    => $context->id, // https://www.examulator.com/er/output/tables/question_usages.html - a contextid of question usage
                'modifiedby'   => $teacher->id,
            ]);

            // Check the question ID
            if (!$question) {
                throw new Exception("Failed to create question.");
            }

            // Add answers to the question
            foreach ($questiondata['answers'] as $answertext => $fraction) {
                // Check the validity of answer data
                if (!is_string($answertext) || !is_numeric($fraction)) {
                    throw new InvalidArgumentException("Invalid answer format.");
                }
                error_log("Created answer: Answer Text: $answertext, Fraction: $fraction");

                $answer    = [
                    'question'       => $question->id,
                    'answer'         => $answertext,
                    'fraction'       => $fraction,
                    'feedback'       => '',  // можно добавить текст обратной связи
                    'feedbackformat' => FORMAT_MOODLE,
                ];
                $answer_id = $DB->insert_record('question_answers', $answer);

                // Check the answer ID
                if (!$answer_id) {
                    throw new Exception("Failed to create answer.");
                }
            }

            error_log("Created question: " . print_r($questiondata, true));

            // Get the current maximum slot for the quiz
            $maxslot = $DB->get_field_sql("
                    SELECT MAX(slot) 
                    FROM {quiz_slots} 
                    WHERE quizid = :quizid", ['quizid' => $quiz->id]);

            // Set the new slot
            $newslot = $maxslot ? $maxslot + 1 : 1;

            // Define the number of questions per page
            $questions_per_page = 1;
            $current_page       = floor(($newslot - 1) / $questions_per_page) + 1;

            // Check if a record already exists
            $existing_slot = $DB->get_record('quiz_slots', [
                'quizid' => $quiz->id,
                'slot'   => $newslot,
                'page'   => $current_page
            ]);

            if ($existing_slot) {
                echo "Slot and page combination already exists: Quiz ID: {$quiz->id}, Slot: {$newslot}, Page: {$current_page}\n";
                continue; // Skip adding this question
            }

            // Manually add the question to the quiz via the quiz_slots table
            $slotdata = [
                'quizid'          => $quiz->id,
                'questionid'      => $question->id,
                'slot'            => $newslot, // Set a new slot
                'page'            => $current_page, // Set the current page
                'requireprevious' => 0,
                'maxmark'         => 1.0, // Maximum mark for the question
            ];

            echo "Inserting into quiz_slots with Quiz ID: {$quiz->id}, Slot: {$newslot}, Page: {$current_page}\n";
            // Insert the data into the table
            try {
                $transaction = $DB->start_delegated_transaction();
                $DB->insert_record('quiz_slots', (object)$slotdata);
                $transaction->allow_commit();
            } catch (dml_exception $e) {
                $transaction->rollback($e);
                throw new Exception("Failed to add question to quiz: " . $e->getMessage());
            }
        }

        // Creating students
        $students = [];
        for ($i = 0; $i < 7; $i++) {
            $students[] = $this->getDataGenerator()->create_user();
        }

        // Students course enrollment
        foreach ($students as $student) {
            $this->getDataGenerator()->enrol_user($student->id, $coursegen->id, 'student');
        }

        // Create attempts for students manually
        $all_answers  = array_map(function ($question) {
            return array_keys($question['answers']);
        }, $questions);
        $flat_answers = array_merge(...$all_answers);
        $questions_db = $DB->get_records('question', []);
        var_dump($questions_db);
        $question_keys = array_merge(
            ...array_map(fn($question) => [
                $question->id => $question->questiontext
            ], $questions_db)
        );

        $random_key = array_rand($question_keys);
        var_dump($random_key, array_column($questions_db, 'id')[$random_key]);
        $random_question_id = array_column($questions_db, 'id')[$random_key];

        for ($i = 0; $i < count($students); $i++) {
            $this->create_quiz_attempt($quiz->id, $students[$i]->id, $random_question_id, $flat_answers[$random_key], $i);
        }

        global $DB;
        $studentAnswers  = [];

        // Logging the question ID
        error_log("Question ID: $question->id");

        // Logging the question text
        error_log("Question Text: $question_keys[$random_key]");

        $referenceAnswer = $flat_answers[$random_key];

        $answers = $DB->get_records('question_attempt_step_data', ['name' => 'answer']);
        // Logging the number of responses
        error_log("Number of Answers: " . count($answers));

        foreach ($answers as $answer) {
            // Log each response
            error_log("Answer: " . $answer->value);

            $studentAnswers[] = $answer->value;
            var_dump($answer->value);
            error_log("Answer: " . $answer->value);
        }

        $apiendpoint = get_config('local_asystgrade', 'apiendpoint');
        if (!$apiendpoint) {
            $apiendpoint = 'http://flask:5000/api/autograde'; // Default setting
        }

        error_log('APIendpoint: ' . $apiendpoint);

        try {
            $httpClient = new http_client();
            $apiClient  = client::getInstance($apiendpoint, $httpClient);
            error_log('ApiClient initiated.');

            error_log('Sending data to API and getting grade');
            $data = [
                'referenceAnswer' => $referenceAnswer,
                'studentAnswers'  => $studentAnswers
            ];
//            var_dump($data);

            error_log("Data to send to API: " . print_r($data, true));
            $response = $apiClient->send_data($data);
            $grades   = json_decode($response, true);

            error_log('Grade obtained: ' . print_r($grades, true));
        } catch (Exception $e) {
            error_log('Error sending data to API: ' . $e->getMessage());
            return;
        }

        var_dump($grades);

        // Check the result
        $this->assertNotEmpty($grades);
        $this->assertEquals($grades[0]['predicted_grade'], 'correct');
        $this->assertEquals($grades[5]['predicted_grade'], 'incorrect');
        $this->assertEquals($grades[6]['predicted_grade'], 'incorrect');
    }

    private function create_question_category($contextid)
    {
        global $DB;

        $category = [
            'name'       => 'Test Category',
            'contextid'  => $contextid,
            'parent'     => 0,
            'info'       => '',
            'infoformat' => FORMAT_MOODLE,
        ];
        // Use Moodle API to create a category
        $categoryid = $DB->insert_record('question_categories', (object)$category);

        if (!$categoryid) {
            throw new coding_exception("Failed to create category.");
        }

        return $DB->get_record('question_categories', array('id' => $categoryid));
    }
    private function create_quiz_attempt($quizid, $userid, $questionid, $exapmle_answers, $student_id)
    {
        global $DB;

        $sql    = "SELECT MAX(attempt) as max_attempt FROM {quiz_attempts} WHERE quiz = :quizid AND userid = :userid";
        $params = array('quizid' => $quizid, 'userid' => $userid);
        $record = $DB->get_record_sql($sql, $params);

        // Set unique attempt number
        $attempt_number = ($record && $record->max_attempt) ? $record->max_attempt + 1 : 1;
        // Generate unique id for uniqueid
        // This query takes the maximum uniqueid value from the table and adds 1
        $uniqueid = $DB->get_field_sql('SELECT COALESCE(MAX(uniqueid), 0) + 1 FROM {quiz_attempts}');

        $attempt = [
            'quiz'       => $quizid,
            'userid'     => $userid,
            'attempt'    => $attempt_number,
            'timestart'  => time() - 3600,
            'timefinish' => time(),
            'sumgrades'  => 10,
            'layout'     => '1,0,2,0,3,0,4,0,5,0,6,0', // array of slot numbers on the page in order
            'uniqueid'   => $uniqueid,
        ];

        $question_attempt = [
            'questionusageid' => $uniqueid, // Linked to the test attempt
            'slot'            => 1, // Question slot number in the test
            'behaviour'       => 'manualgraded', // Essay requires manual grading
            'questionid'      => $questionid, // Question ID
            'variant'         => 1, // Question variant
            'maxmark'         => 10.0, // Maximum score for the question
            'minfraction'     => 0.0, // Minimum fraction of the score
            'maxfraction'     => 1.0, // Maximum fraction of the score
            'flagged'         => 0, // Flag indicating whether the question was flagged
            'questionsummary' => 'Essay question', // Brief description of the question
            'rightanswer'     => '', // For essays, there may be no correct answer
            'responsesummary' => '', // Summary response (if any)
            'timemodified'    => time(), // Time of modification
        ];

        // Insert a record into the quiz_attempt* tables
        try {
            $DB->insert_record('quiz_attempts', $attempt);
            $question_attempt_id = $DB->insert_record('question_attempts', $question_attempt);

            $question_attempt_step = [
                'questionattemptid' => $question_attempt_id, // ID of the question from the `mdl_question_attempts` table
                'sequencenumber'    => 1, // Step sequence number
                'state'             => 'complete', // Step state
                'fraction'          => 0.0, // For essays, this may be 0 until graded
                'timecreated'       => time(), // Time of step creation
                'userid'            => $userid, // ID of the user who created the step
            ];

            $question_attempt_step_id = $DB->insert_record('question_attempt_steps', $question_attempt_step);

            // Example of how to shorten the answer length
            $answer_length  = strlen($exapmle_answers) - (int) ($student_id * strlen($exapmle_answers) / 6); // Reduce the answer by 10 characters for each subsequent student
            $student_answer = substr($exapmle_answers, 0, $answer_length);

            // Saving the answer text
            $question_attempt_step_data_answer = [
                'attemptstepid' => $question_attempt_step_id, // ID of the step from the `mdl_question_attempt_steps` table
                'name'          => 'answer', // Data name (e.g., 'answer')
                'value'         => $student_answer, // User's answer
            ];

            // Saving the answer format
            $question_attempt_step_data_format = [
                'attemptstepid' => $question_attempt_step_id, // ID of the step from the `mdl_question_attempt_steps` table
                'name'          => 'answerformat', // Data name (e.g., 'answerformat')
                'value'         => '1', // Answer format (1 = HTML, 0 = plain text, etc.)
            ];

            // Inserting data into the question_attempt_step_data table
            $DB->insert_record('question_attempt_step_data', $question_attempt_step_data_answer);
            $DB->insert_record('question_attempt_step_data', $question_attempt_step_data_format);

            echo `Quiz attempt inserted successfully. $quizid\n`;
        } catch (dml_write_exception $e) {
            // Handling database write errors
            echo "Error inserting quiz attempt: " . $e->getMessage() . "\n";
        }
    }
}
