<?php

namespace local_asystgrade\db;

interface quizquery_interface
{
    public function get_question_attempts($qid, $slot);

    public function get_reference_answer($qid);

    public function get_attempt_steps($question_attempt_id);

    public function get_student_answer($attemptstepid);
}