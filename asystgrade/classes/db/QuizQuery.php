<?php
namespace local_asystgrade\db;

class QuizQuery implements QuizQueryInterface
{
    private $db;

    public function __construct() {
        global $DB;
        $this->db = $DB;
    }

    /**
     * @param $qid
     * @param $slot
     * @return mixed
     */
    public function get_question_attempts($qid, $slot) {
        return $this->db->get_recordset(
            'question_attempts',
            [
                'questionid' => $qid,
                'slot' => $slot
            ],
            '',
            '*'
        );
    }

    /**
     * @param $qid
     * @return mixed
     */
    public function get_reference_answer($qid) {
        return $this->db->get_record(
            'qtype_essay_options',
            [
                'questionid' => $qid
            ],
            '*',
            MUST_EXIST
        )->graderinfo;
    }

    /**
     * @param $question_attempt_id
     * @return mixed
     */
    public function get_attempt_steps($question_attempt_id) {
        return $this->db->get_recordset(
            'question_attempt_steps',
            [
                'questionattemptid' => $question_attempt_id
            ],
            '',
            '*'
        );
    }

    /**
     * @param $attemptstepid
     * @return mixed
     */
    public function get_student_answer($attemptstepid) {
        return $this->db->get_record(
            'question_attempt_step_data',
            [
                'attemptstepid' => $attemptstepid,
                'name' => 'answer'
            ],
            '*',
            MUST_EXIST
        )->value;
    }
}
