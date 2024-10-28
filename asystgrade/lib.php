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
        $js_data = [
                'apiendpoint' => 'http://flask:5000/api/autograde',
                'qid' => $qid,
                'slot' => $slot
        ];

        $PAGE->requires->js(new moodle_url('/local/asystgrade/js/grade.js', ['v' => time()]));
        $PAGE->requires->js_init_call('M.local_asystgrade.init', [$js_data]);
    }
}