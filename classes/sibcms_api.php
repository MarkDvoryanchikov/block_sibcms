<?php
// This file is part of Moodle - http://moodle.org/
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

/**
 * block_sibcms
 *
 * @package    block_sibcms
 * @copyright  2020 Mark Dvoryanchikov <kicksaflips@gmail.com>, Sergey Shlyanin <sergei.shlyanin@gmail.com>, Aleksandr Raetskiy <ksenon3@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_sibcms;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/assign/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

class sibcms_api
{

    /**
     * Get all course feedbacks
     * @param $course_id
     * @return array
     */
    public static function get_course_feedbacks($course_id)
    {
        global $DB;
        return $DB->get_records('block_sibcms_feedbacks', array('courseid' => $course_id));
    }

    /**
     * Get last course feedback
     * @param $course_id
     * @return mixed
     */
    public static function get_last_course_feedback($course_id, $only_active_properties = true)
    {
        global $DB;
        $last_feedback = $DB->get_records('block_sibcms_feedbacks', array('courseid' => $course_id), 'timecreated DESC', '*', 0, 1);
        if (count($last_feedback) > 0) {
            $last_feedback = reset($last_feedback);
            $last_feedback->properties = sibcms_api::get_feedback_properties($last_feedback->id, $only_active_properties);
            return $last_feedback;
        } else {
            return false;
        }
    }

    public static function get_feedback_properties($feedback_id, $only_active = true)
    {
        global $DB;
        $sql = 'SELECT p.id, p.name
                  FROM {block_sibcms_feedback_props} fp
                  JOIN {block_sibcms_properties} p
                    ON fp.propertyid = p.id
                 WHERE fp.feedbackid = ?';
        if ($only_active) {
            $sql .= ' AND p.hidden = 0';
        }
        return $DB->get_records_sql($sql, array($feedback_id));
    }

    /**
     * Get all properties
     * @param bool $only_active
     * @return array
     */
    public static function get_properties($only_active = true)
    {
        global $DB;
        return $DB->get_records('block_sibcms_properties', $only_active ? array('hidden' => 0) : array());
    }


    /**
     * Save feedback for the course
     * @param $course_id
     * @param $feedback
     * @param $comment
     * @param $result
     * @return bool|int
     */
    public static function save_feedback($course_id, $feedback, $comment, $result, $properties)
    {
        global $DB, $USER;
        $record = new \stdClass();
        $record->userid = $USER->id;
        $record->courseid = $course_id;
        $record->timecreated = time();
        $record->feedback = $feedback;
        $record->comment = $comment;
        $record->result = $result;
        $last_id = $DB->insert_record('block_sibcms_feedbacks', $record);

        $all_properties = \block_sibcms\sibcms_api::get_properties(false);
        foreach ($properties as $property_id => $value) {
            if ($value) {
                if (!array_key_exists($property_id, $all_properties)) {
                    print_error('error');
                }
                $record = new \stdClass();
                $record->feedbackid = $last_id;
                $record->propertyid = $property_id;
                $DB->insert_record('block_sibcms_feedback_props', $record);
            }
        }

        return $last_id;
    }

    /**
     * Delete all feedbacks for the course
     * @param $course_id
     */
    public static function delete_feedbacks($course_id)
    {
        global $DB;
        $feedbacks = $DB->get_records('block_sibcms_feedbacks', array('courseid' => $course_id));
        foreach ($feedbacks as $feedback) {
            $DB->delete_records('block_sibcms_feedback_props', array('feedbackid' => $feedback->id));
        }
        $DB->delete_records('block_sibcms_feedbacks', array('courseid' => $course_id));
    }

    /**
     * Add new property
     * @param $name
     */
    public static function add_property($name)
    {
        global $DB;
        $record = new \stdClass();
        $record->name = $name;
        $record->hidden = 0;
        return $DB->insert_record('block_sibcms_properties', $record);
    }

    /**
     * Delte property
     * @param $property_id
     */
    public static function delete_property($property_id)
    {
        global $DB;
        $DB->delete_records('block_sibcms_feedback_props', array('propertyid' => $property_id));
        $DB->delete_records('block_sibcms_properties', array('id' => $property_id));
    }

    public static function set_propvisible($property_id, $hidden)
    {
        global $DB;
        $hidden = $hidden ? 1 : 0;
        $exists = $DB->record_exists('block_sibcms_properties', array('id' => $property_id));
        if ($exists) {
            $DB->set_field('block_sibcms_properties', 'hidden', $hidden, array('id' => $property_id));
        }
    }

    /**
     * Check if course require attention
     * @param $course_id
     * @return bool
     */
    public static function require_attention($course)
    {
        // Course is not visible
        if (!$course->visible) {
            return false;
        }

        // Course is ignored
        $ignore = sibcms_api::get_course_ignore($course->id);
        if ($ignore) {
            return false;
        }

        $last_feedback = sibcms_api::get_last_course_feedback($course->id);
        // Course has no feedback
        if (!$last_feedback) {
            return true;
        }
        
        $time_ago = time() - $last_feedback->timecreated;
        if ($time_ago > get_config('block_sibcms', 'feedback_relevance_duration')) {
            return true;
        }

        return false;
    }

    /**
     * Get ID of the course that require attention in the certain category
     * @param $category_id
     * @return int|null
     */
    public static function get_require_attention_course($category_id)
    {
        $category = \coursecat::get($category_id);
        if (!$category) {
            return null;
        }
        $courses = $category->get_courses(array('recursive' => true));
        foreach ($courses as $course) {
            if (sibcms_api::require_attention($course)) {
                return $course->id;
            }
        }
        return null;
    }

    /**
     * Get all automatic hints for course data
     * @param $course_data
     * @return array
     */
    public static function get_hints($course_data)
    {
        $hints = array();
        if (count($course_data->graders) == 0) {
            $hints[] = get_string('key50', 'block_sibcms');
        }

        foreach ($course_data->graders as $grader) {
            if (!$grader->lastcourseaccess) {
                $hints[] = get_string('key90', 'block_sibcms');
                break;
            };
        }

        if (count($course_data->graders) == $course_data->participants) {
            $hints[] = get_string('key51', 'block_sibcms');
        }
        if ($course_data->filescount == 0) {
            $hints[] = get_string('key52', 'block_sibcms');
        }

        $need_grading = 0;
        $assigns_and_quizes = 0;
        $all_assings_are_grading = true;
        $all_assings_have_feedbacks = true;
        foreach ($course_data->assigns as $assign) {
            if ($assign->modvisible) {
                if (!$assign->teamsubmission && !$assign->nograde) {
                    $need_grading += $assign->need_grading;
                }
                $assigns_and_quizes++;
                $all_assings_are_grading &= !$assign->nograde;
                $all_assings_have_feedbacks &= (count($assign->feedbacks) > 0);
            }
        }
        $all_quiz_have_questions = true;
        $all_quiz_have_time_limit = true;
        foreach ($course_data->quiz as $quiz) {
            if ($quiz->modvisible) {
                $assigns_and_quizes++;
                $all_quiz_have_questions &= !$quiz->noquestions;
                $all_quiz_have_time_limit &= $quiz->timelimit > 0;
            }
        }

        if ($assigns_and_quizes == 0) {
            $hints[] = get_string('key53', 'block_sibcms');
        }
        if (!$all_assings_are_grading) {
            $hints[] = get_string('key54', 'block_sibcms');
        }
        if (!$all_assings_have_feedbacks) {
            $hints[] = get_string('key82', 'block_sibcms');
        }
        if (!$all_quiz_have_questions) {
            $hints[] = get_string('key55', 'block_sibcms');
        }
        if (!$all_quiz_have_time_limit) {
            $hints[] = get_string('key95', 'block_sibcms');
        }
        if ($need_grading > 0) {
            $hints[] = get_string('key60', 'block_sibcms', array('count' => $need_grading));
        }

        return $hints;
    }

    // TODO: Union all graders and mark their capabilities
    public static function get_course_graders($courseid, $fields = 'u.*')
    {
        $context = \context_course::instance((int)$courseid);
        $graders = get_enrolled_users($context, 'mod/assign:grade', null, $fields, null, null, null, true);
        foreach ($graders as $grader) {
            if (!is_enrolled($context, $grader, 'mod/quiz:grade', true)) {
                unset($graders[$grader->id]);
            } else {
                $graders[$grader->id]->lastcourseaccess = sibcms_api::get_last_access_to_course($courseid, $grader->id);
            }

        }
        return $graders;
    }

    /**
     * Get united course data
     * @param $course
     * @param $group
     * @return \stdClass
     */
    public static function get_course_data($course, $group = 0, $onlyvisible = true)
    {
        $modinfo = get_fast_modinfo($course->id);
        $result = new \stdClass();
        $result->id = $course->id;
        $result->fullname = $course->fullname;
        $result->shortname = $course->shortname;
        $result->visible = $course->visible;
        $result->ignore = sibcms_api::get_course_ignore($course->id);
        $result->graders = sibcms_api::get_course_graders($course->id);
        $result->participants = sibcms_api::get_count_course_participants($course->id);
        $result->filescount = sibcms_api::get_count_course_files($course->id);
        $assign_data = sibcms_api::get_assign_grades_data($modinfo, $group, $onlyvisible);
        $result->assigns = $assign_data['data'];
        $result->assigns_results = $assign_data['results'];
        $quiz_data = sibcms_api::get_quiz_grades_data($modinfo, $group, $onlyvisible);
        $result->quiz = $quiz_data['data'];
        $result->quiz_results = $quiz_data['results'];
        $all_tasks = $result->assigns_results->participants + $result->quiz_results->participants;
        $all_grades = $result->assigns_results->finished + $result->quiz_results->submitted;
        $result->result = $all_tasks > 0 ? $all_grades / $all_tasks : 0;
        return $result;
    }

    /**
     * Get user's last access time to the course
     * @param $course_id
     * @param $user_id
     * @return mixed
     */
    public static function get_last_access_to_course($course_id, $user_id)
    {
        global $DB;
        return $DB->get_field('user_lastaccess', 'timeaccess',
            array(
                'courseid' => $course_id,
                'userid' => $user_id
            )
        );
    }

    /**
     * Check if course has changed since last feedback
     */
    public static function course_was_changed($course_data)
    {
        $last_feedback = sibcms_api::get_last_course_feedback($course_data->id);
        if (!$last_feedback) {
            return null;
        }

        $loglifetime = get_config('logstore_standard', 'loglifetime') * 24 * 60 * 60;
        $feedback_time = time() - $last_feedback->timecreated;
        if ($loglifetime > 0 && $feedback_time > $loglifetime) {
            return null;
        }

        $global_event_names = array(
            '\\\\core\\\\event\\\\course_module_created',
            '\\\\core\\\\event\\\\course_module_updated',
            '\\\\core\\\\event\\\\course_module_deleted',
            '\\\\core\\\\event\\\\course_reset_started',
            '\\\\core\\\\event\\\\course_reset_ended',
            '\\\\core\\\\event\\\\course_restored',
            '\\\\core\\\\event\\\\course_section_updated',
            '\\\\core\\\\event\\\\course_section_deleted',
            '\\\\core\\\\event\\\\course_updated',
            '\\\\core\\\\event\\\\enrol_instance_created',
            '\\\\core\\\\event\\\\enrol_instance_updated',
            '\\\\core\\\\event\\\\enrol_instance_deleted',
            '\\\\core\\\\event\\\\group_created',
            '\\\\core\\\\event\\\\group_updated',
            '\\\\core\\\\event\\\\group_deleted',
            '\\\\core\\\\event\\\\grouping_created',
            '\\\\core\\\\event\\\\grouping_deleted',
            '\\\\core\\\\event\\\\grouping_group_assigned',
            '\\\\core\\\\event\\\\grouping_group_unassigned',
            '\\\\core\\\\event\\\\grouping_updated',
        );
        $graders_event_names = array(
            '\\\\core\\\\event\\\\group_member_added',
            '\\\\core\\\\event\\\\group_member_removed',
            '\\\\core\\\\event\\\\role_assigned',
            '\\\\core\\\\event\\\\role_unassigned',
            '\\\\core\\\\event\\\\user_enrolment_created',
            '\\\\core\\\\event\\\\user_enrolment_updated',
            '\\\\core\\\\event\\\\user_enrolment_deleted',
        );
        $graders_event_names_grades = array(
            '\\\\core\\\\event\\\\user_graded',
            '\\\\core\\\\event\\\\grade_deleted',
        );

        $where = "courseid = ? AND timecreated > ?";
        $global_where = '(';
        for ($i = 0; $i < count($global_event_names); $i++) {
            $eventname = $global_event_names[$i];
            $global_where .= $i == 0 ? "eventname = '$eventname'" : " OR eventname = '$eventname'";
        }
        $global_where .= ')';

        $grades_where = '';
        $graders_where = '';
        if (count($course_data->graders) > 0) {
            $grades_where = $graders_where = '((';
            for ($i = 0; $i < count($graders_event_names_grades); $i++) {
                $eventname = $graders_event_names_grades[$i];
                $grades_where .= $i == 0 ? "eventname = '$eventname'" : " OR eventname = '$eventname'";
            }
            for ($i = 0; $i < count($graders_event_names); $i++) {
                $eventname = $graders_event_names[$i];
                $graders_where .= $i == 0 ? "eventname = '$eventname'" : " OR eventname = '$eventname'";
            }
            $grades_where .= ') AND (';
            $graders_where .= ') AND (';
            $graders_users_where = '';
            for ($i = 0; $i < count($course_data->graders); $i++) {
                $graders_users_where .= $i == 0 ? "userid = ?" : " OR userid = ?";
            }
            $grades_where .= ($graders_users_where . '))');
            $graders_where .= ($graders_users_where . '))');
        }

        $changes = array();
        $params = array($course_data->id, $last_feedback->timecreated);
        $graders_ids = array_map(function($grader) { return $grader->id; }, $course_data->graders);
        $params = array_merge($params, $graders_ids);
        $store = new \logstore_standard\log\store(new \tool_log\log\manager());

        $result_where = $where;
        $result_where .= count($course_data->graders) > 0 ?
            " AND ($global_where OR $graders_where)" : " AND $global_where";
        $count = $store->get_events_select_count($result_where, $params);
        if ($count > 0) {
            $changes[] = get_string('key110', 'block_sibcms');
        }

        if (count($course_data->graders) > 0) {
            $count = $store->get_events_select_count($where . " AND $grades_where", $params);
            if ($count > 0) {
                $changes[] = get_string('key111', 'block_sibcms');
            }
        }

        return $changes;
    }


    /**
     * Get assings data
     * @param $modinfo
     * @param $activitygroup
     * @param bool $onlyvisible
     * @return array
     */
    public static function get_assign_grades_data($modinfo, $activitygroup = 0, $onlyvisible = false)
    {
        global $DB;

        $modules = $modinfo->get_instances_of('assign');
        $course = $modinfo->get_course();

        $data = array();
        $results = new \stdClass();
        $results->participants = 0;
        $results->submitted = 0;
        $results->graded = 0;
        $results->need_grading = 0;
        $results->finished = 0;

        foreach ($modules as $module) {

            $visible = sibcms_api::get_modvisible($module);
            if ($onlyvisible && !$visible) continue;
            $cm = \context_module::instance($module->id);
            $assign = new \assign($cm, $module, $course);
            $instance = $assign->get_instance();
            $moddata = new \stdClass();

            $moddata->name = $module->name;
            $moddata->teamsubmission = $instance->teamsubmission;
            $moddata->nograde = $instance->grade == 0;
            $moddata->modvisible = $visible;
            $moddata->visible = has_capability('mod/assign:view', $cm);
            $moddata->gradeitem = $assign->get_grade_item();
            if ($moddata->nograde) {
                $moddata->grade = get_string('key56', 'block_sibcms');
            } else {
                if ($moddata->gradeitem->gradetype == 2) {
                    $moddata->grade = $DB->get_record('scale', array('id' => $moddata->gradeitem->scaleid))->name;
                } else {
                    $points = array('points' => format_float($moddata->gradeitem->grademax, 0));
                    $moddata->grade = get_string('key57', 'block_sibcms', $points);
                }
            }
            $moddata->feedbacks = array();
            $feedbacks = $assign->get_feedback_plugins();
            foreach ($feedbacks as $feedback) {
                $type = "assignfeedback_{$feedback->get_type()}";
                if (!get_config($type, 'disabled') && $feedback->is_enabled()) {
                    $moddata->feedbacks[] = $feedback;
                }
            }

            if ($instance->teamsubmission) { // Moodle calculation
                $moddata->participants = $assign->count_teams($activitygroup);
                $moddata->submitted = $assign->count_submissions_with_status(ASSIGN_SUBMISSION_STATUS_DRAFT) +
                    $assign->count_submissions_with_status(ASSIGN_SUBMISSION_STATUS_SUBMITTED);
                $moddata->need_grading = $assign->count_submissions_need_grading();
                $moddata->graded = null;
                $moddata->submitted_persent = null;
                $moddata->graded_persent = null;
                // TODO: Decide whether to add participants to results
            } else { // Own calculation algorithm
                list($esql, $uparams) = get_enrolled_sql($cm, 'mod/assign:submit', $activitygroup, true);
                $info = new \core_availability\info_module($module);
                list($fsql, $fparams) = $info->get_user_list_sql(true);
                if ($fsql) $uparams = array_merge($uparams, $fparams);
                $psql = "SELECT COUNT(*) FROM {user} u JOIN ($esql) e ON u.id = e.id " . ($fsql ? "JOIN ($fsql) f ON u.id = f.id" : "");
                $moddata->participants = $DB->count_records_sql($psql, $uparams);
                if ($moddata->modvisible) {
                    $results->participants += $moddata->participants;
                    if (!$moddata->nograde) {
                        $results->need_grading += $moddata->participants;
                    }
                }

                $select = "SELECT COUNT(DISTINCT(s.userid)) ";
                $table = "FROM {assign_submission} s ";
                $ujoin = "JOIN ($esql) e ON s.userid = e.id " . ($fsql ? "JOIN ($fsql) f ON s.userid = f.id " : "");
                $where = "WHERE s.assignment = :assign AND s.timemodified IS NOT NULL AND (s.status = :stat1 OR s.status = :stat2) ";
                $sparams = array(
                    'assign' => $module->instance,
                    'stat1' => ASSIGN_SUBMISSION_STATUS_SUBMITTED,
                    'stat2' => ASSIGN_SUBMISSION_STATUS_DRAFT
                );
                $sparams = array_merge($sparams, $uparams);
                $moddata->submitted = $DB->count_records_sql($select . $table . $ujoin . $where, $sparams);
                if ($moddata->modvisible) {
                    $results->submitted += $moddata->submitted;
                }

                $moddata->submitted_persent = $moddata->participants > 0 ?
                    $moddata->submitted / $moddata->participants : 0;

                $select = "SELECT COUNT(s.userid) ";
                $gjoin = "LEFT JOIN {assign_grades} g ON s.assignment = g.assignment AND s.userid = g.userid AND g.attemptnumber = s.attemptnumber ";
                $where .= "AND s.latest = 1 AND (s.timemodified >= g.timemodified OR g.timemodified IS NULL OR g.grade IS NULL)";
                $moddata->need_grading = $DB->count_records_sql($select . $table . $ujoin . $gjoin . $where, $sparams);
                if ($moddata->nograde) {
                    $moddata->graded = null;
                    $moddata->graded_persent = null;
                } else {
                    $moddata->graded = $moddata->submitted - $moddata->need_grading;
                    if ($moddata->modvisible) {
                        $results->graded += $moddata->graded;
                    }
                    $moddata->graded_persent = $moddata->participants > 0 ?
                        $moddata->graded / $moddata->participants : 0;
                }

                if ($moddata->modvisible) {
                    $subval = !$moddata->teamsubmission ? $moddata->submitted : 0;
                    $gradeval = !$moddata->nograde ? $moddata->graded : $subval;
                    $results->finished += ($subval + $gradeval) / 2;
                }

            }

            // Plagiarism check
            $moddata->plagiarism = false;
            $plagiarism_plugins = \core_component::get_plugin_list('plagiarism');
            if (isset($plagiarism_plugins['rucontext'])) {
                $rucontext_record = $DB->get_record('plagiarism_rucontext_mod_en', array('moduleid' => $module->id));
                if ($rucontext_record && $rucontext_record->enable == 1) {
                    $moddata->plagiarism = true;
                }
            }

            $data[$module->id] = $moddata;
        }

        $results->submitted_persent = $results->participants > 0 ? $results->submitted / $results->participants : 0;
        $results->graded_persent = $results->need_grading > 0 ? $results->graded / $results->need_grading : 0;

        return array('data' => $data, 'results' => $results);
    }

    /**
     * Get quiz data
     * @param $modinfo
     * @param $activitygroup
     * @param bool $onlyvisible
     * @return array
     */
    public static function get_quiz_grades_data($modinfo, $activitygroup = 0, $onlyvisible = false)
    {
        global $DB;

        $modules = $modinfo->get_instances_of('quiz');

        $data = array();
        $results = new \stdClass();
        $results->participants = 0;
        $results->submitted = 0;

        foreach ($modules as $module) {

            $visible = sibcms_api::get_modvisible($module);
            if ($onlyvisible && !$visible) continue;
            $cm = \context_module::instance($module->id);
            $quiz = \quiz::create($module->instance);
            $moddata = new \stdClass();

            $moddata->name = $module->name;
            $moddata->id = $module->id;
            $moddata->noquestions = !$quiz->has_questions();
            $moddata->modvisible = $visible;
            $moddata->visible = has_capability('mod/quiz:view', $cm);
            $moddata->timelimit = $quiz->get_quiz()->timelimit;

            list($esql, $uparams) = get_enrolled_sql($cm, 'mod/quiz:attempt', $activitygroup, true);
            $info = new \core_availability\info_module($module);
            list($fsql, $fparams) = $info->get_user_list_sql(true);
            if ($fsql) $uparams = array_merge($uparams, $fparams);
            $psql = "SELECT COUNT(*) FROM {user} u JOIN ($esql) e ON u.id = e.id " . ($fsql ? "JOIN ($fsql) f ON u.id = f.id" : "");
            $moddata->participants = $DB->count_records_sql($psql, $uparams);
            if ($moddata->modvisible) {
                $results->participants += $moddata->participants;
            }

            $select = "SELECT COUNT(qg.id) ";
            $table = "FROM {quiz_grades} qg ";
            $ujoin = "JOIN ($esql) e ON qg.userid = e.id " . ($fsql ? "JOIN ($fsql) f ON qg.userid = f.id " : "");
            $where = "WHERE qg.quiz = :quiz";
            $qparams = array_merge(array('quiz' => $module->instance), $uparams);
            $moddata->submitted = $DB->count_records_sql($select . $table . $ujoin . $where, $qparams);
            if ($moddata->modvisible) {
                $results->submitted += $moddata->submitted;
            }
            $moddata->submitted_persent = $moddata->participants > 0 ?
                $moddata->submitted / $moddata->participants : 0;

            $data[$module->id] = $moddata;

        }

        $results->submitted_persent = $results->participants > 0 ? $results->submitted / $results->participants : 0;

        return array('data' => $data, 'results' => $results);
    }

    /**
     * Get course participants count
     * @param $courseid
     * @return array
     */
    public static function get_count_course_participants($course_id)
    {
        $context = \context_course::instance($course_id);
        return count_enrolled_users($context);
    }

    /**
     * Get course files count
     * @param $courseid
     * @param bool $onlyvisible
     * @return int
     */
    public static function get_count_course_files($course_id, $onlyvisible = false)
    {
        $result = 0;
        $modinfo = get_fast_modinfo($course_id);

        $modules = $modinfo->get_instances_of('resource');
        foreach ($modules as $module) {
            if ($onlyvisible && !$module->visible) continue;
            $result++;
        }
        $fs = get_file_storage();
        $modules = $modinfo->get_instances_of('folder');
        foreach ($modules as $module) {
            if ($onlyvisible && !$module->visible) continue;
            $cm = \context_module::instance($module->id);
            $files = $fs->get_area_files($cm->id, 'mod_folder', 'content', 0, null, false);
            $result += count($files);
        }

        return $result;
    }

    public static function set_modvisible($module, $visible)
    {
        global $DB;
        $visible = $visible ? 1 : 0;
        $count = $DB->count_records('block_sibcms_visibility', array('moduleid' => $module->id));
        if ($count > 0) {
            $DB->set_field('block_sibcms_visibility', 'visible', $visible, array('moduleid' => $module->id));
        } else {
            $DB->execute('INSERT INTO {block_sibcms_visibility} (courseid, moduleid, visible) VALUES (?, ?, ?)', 
                array($module->course, $module->id, $visible));
        }
    }

    public static function get_modvisible($module)
    {
        global $DB;
        $record = $DB->get_record('block_sibcms_visibility', array('moduleid' => $module->id));
        return !$record ? $module->visible : $record->visible;
    }
    
    public static function delete_modvisible($module_id) {
        global $DB;
        $DB->delete_records('block_sibcms_visibility', array('moduleid' => $module_id));
    }
    
    public static function delete_modvisible_by_course_id($course_id) {
        global $DB;
        $DB->delete_records('block_sibcms_visibility', array('courseid' => $course_id));
    }

    public static function set_course_ignore($course_id, $ignore)
    {
        global $DB;
        $ignore = $ignore ? 1 : 0;
        $count = $DB->count_records('block_sibcms_ignore_courses', array('courseid' => $course_id));
        if ($count > 0) {
            $DB->set_field('block_sibcms_ignore_courses', 'ignoring', $ignore, array('courseid' => $course_id));
        } else {
            $DB->execute('INSERT INTO {block_sibcms_ignore_courses} (courseid, ignoring) VALUES (?, ?)',
                array($course_id, $ignore));
        }
    }

    public static function get_course_ignore($course_id)
    {
        global $DB;
        $record = $DB->get_record('block_sibcms_ignore_courses', array('courseid' => $course_id));
        return !$record ? false : $record->ignoring;
    }

    public static function delete_course_ignore($course_id) {
        global $DB;
        $DB->delete_records('block_sibcms_ignore_courses', array('courseid' => $course_id));
    }
    
    // get all teachers (group with name 445)
    function get_all_teachers() {
        global $DB;
        
        $query = 'SELECT u.id AS id, u.username AS login, u.firstname AS firstname, u.lastname AS lastname, u.suspended
        FROM {user} u
        INNER JOIN {cohort_members} cm ON cm.userid = u.id
        INNER JOIN {cohort} c ON cm.cohortid = c.id
            WHERE u.deleted = 0 AND c.id = 445';
        $query .= ' ORDER BY u.lastname';
        $users = $DB->get_records_sql($query, array($teacher));
        return $users;
    }
}
