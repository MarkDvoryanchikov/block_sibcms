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
 * @copyright  2020 Mark Dvoryanchikov <kicksflips@gmail.com>, Sergey Shlyanin <sergei.shlyanin@gmail.com>, Aleksandr Raetskiy <ksenon3@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir.'/coursecatlib.php');
require_once($CFG->libdir.'/excellib.class.php');

$course_id   = required_param('id', PARAM_INT);
$category_id = optional_param('category', 0, PARAM_INT);
$mode        = optional_param('mode', 0, PARAM_INT);

$course = $DB->get_record('course', array('id' => $course_id), '*', MUST_EXIST);
$contextcoursecat = $category_id ? context_coursecat::instance($category_id) : null;

$base_url = new moodle_url('/blocks/sibcms/export.php', array('id' => $course_id));
$PAGE->set_url($base_url);

require_login($course);
$contextcourse = context_course::instance($course_id);
require_capability('block/sibcms:monitoring_report', $contextcourse);

$monitoring = has_capability('block/sibcms:monitoring', context_system::instance());

if ($contextcoursecat) {

    require_capability('block/sibcms:monitoring_report_category', $contextcoursecat);

    $str_monitoring = get_string('key21', 'block_sibcms');
    $download_filename = clean_filename("$str_monitoring.xls");
    $workbook = new MoodleExcelWorkbook("-");
    $workbook->send($download_filename);
    $myxls = $workbook->add_worksheet($str_monitoring);
    $courses = coursecat::get($category_id)->get_courses(array('recursive' => true));

    if ($mode == 0) {

        $myxls->write_string(0, 0, get_string('key27', 'block_sibcms'));
        $myxls->write_string(0, 1, get_string('key28', 'block_sibcms'));
        $myxls->write_string(0, 2, get_string('key39', 'block_sibcms'));
        $myxls->write_string(0, 3, get_string('key40', 'block_sibcms'));
        $myxls->write_string(0, 4, get_string('key41', 'block_sibcms'));
        $myxls->write_string(0, 5, get_string('key42', 'block_sibcms'));
        $myxls->write_string(0, 6, get_string('key43', 'block_sibcms'));
        $myxls->write_string(0, 7, get_string('key47', 'block_sibcms'));
        $myxls->write_string(0, 8, get_string('key48', 'block_sibcms'));
        $myxls->write_string(0, 9, get_string('key49', 'block_sibcms'));
        $myxls->write_string(0, 10, get_string('key68', 'block_sibcms'));
        $myxls->write_string(0, 11, get_string('key29', 'block_sibcms'));

        $temp = 12;
        if ($monitoring) {
            $myxls->write_string(0, $temp++, get_string('key35', 'block_sibcms'));
        }
        $myxls->write_string(0, $temp++, get_string('key77', 'block_sibcms'));

        $myxls->write_string(0, $temp++, get_string('key23', 'block_sibcms'));
        $properties = \block_sibcms\sibcms_api::get_properties();
        foreach ($properties as $property) {
            $property->total = 0;
            $myxls->write_string(0, $temp++, $property->name);
        }

        $index = 1;
        $course_ready = $course_count = 0;
        $students_active = $graders_active = 0;
        foreach ($courses as $course) {
            if (!$course->visible) continue;

            $ignore = \block_sibcms\sibcms_api::get_course_ignore($course->id);
            if ($ignore) continue;

            $course_count++;
            $course_data = \block_sibcms\sibcms_api::get_course_data($course);

            $myxls->write_string($index, 0, $course_data->fullname);

            $graders = array();
            if (count($course_data->graders) > 0) {
                foreach ($course_data->graders as $grader) {
                    $content = fullname($grader);
                    $content .= $grader->lastcourseaccess ? ' (' . format_time(time() - $grader->lastcourseaccess) . ')' : ' (' . get_string('never') . ')';
                    $graders[] = $content;
                }
            } else $graders[] = get_string('key50', 'block_sibcms');
            $myxls->write_string($index, 1, implode('; ', $graders));

            $myxls->write_number($index, 2, $course_data->assigns_results->participants);
            $myxls->write_number($index, 3, $course_data->assigns_results->submitted);
            $myxls->write_number($index, 4, $course_data->assigns_results->submitted_persent);
            $myxls->write_number($index, 5, $course_data->assigns_results->graded);
            $myxls->write_number($index, 6, $course_data->assigns_results->graded_persent);

            $myxls->write_number($index, 7, $course_data->quiz_results->participants);
            $myxls->write_number($index, 8, $course_data->quiz_results->submitted);
            $myxls->write_number($index, 9, $course_data->quiz_results->submitted_persent);

            $myxls->write_number($index, 10, $course_data->result);

            if ($course_data->assigns_results->submitted > 0 || $course_data->quiz_results->submitted > 0) {
                $students_active++;
            }

            if ($course_data->assigns_results->graded > 0 ||
               ($course_data->assigns_results->participants == 0 && $course_data->quiz_results->submitted > 0)) {
                $graders_active++;
            }

            $comment = '';
            $feedback = '';
            $datetime = get_string('never');
            $feedback_data = \block_sibcms\sibcms_api::get_last_course_feedback($course_data->id);
            if ($feedback_data) {
                $comment = $feedback_data->comment;
                $feedback = $feedback_data->feedback;
                $datetime = userdate($feedback_data->timecreated, '%d %b %Y, %H:%M');
            }
            $myxls->write_string($index, 11, $feedback);

            $temp = 12;
            if ($monitoring) {
                $myxls->write_string($index, $temp++, $comment);
            }

            $myxls->write_string($index, $temp++, $datetime);

            if ($feedback_data) {
                if ($feedback_data->result == 0) {
                    $myxls->write_string($index, $temp, '+');
                    $course_ready++;
                }
                $temp++;
                foreach ($properties as $property) {
                    if (array_key_exists($property->id, $feedback_data->properties)) {
                        $properties[$property->id]->total++;
                        $myxls->write_string($index, $temp, '+');
                    }
                    $temp++;
                }
            } else {
                $temp += (count($properties) + 1);
            }

            $myxls->write_string($index, $temp, "$CFG->wwwroot/course/view.php?id=$course->id");

            $index++;
        }

        $myxls->write_string(++$index, 0, get_string('key3', 'block_sibcms'));
        $myxls->write_number($index++, 1, $course_count);

        $myxls->write_string($index, 0, get_string('key97', 'block_sibcms'));
        $myxls->write_number($index++, 1, $course_ready);

        $myxls->write_string($index, 0, get_string('key98', 'block_sibcms'));
        $myxls->write_number($index++, 1, $students_active);

        $myxls->write_string($index, 0, get_string('key99', 'block_sibcms'));
        $myxls->write_number($index++, 1, $graders_active);

        foreach ($properties as $property) {
            $myxls->write_string($index, 0, get_string('key63', 'block_sibcms') . ' ' . $property->name);
            $myxls->write_number($index++, 1, $property->total);
        }

    } else {

        $courses_tree = array();
        foreach ($courses as $course) {
            if (!$course->visible) continue;

            $ignore = \block_sibcms\sibcms_api::get_course_ignore($course->id);
            if ($ignore) continue;

            $course_data = \block_sibcms\sibcms_api::get_course_data($course);
            $course_data->feedback = \block_sibcms\sibcms_api::get_last_course_feedback($course_data->id);

            foreach ($course_data->graders as $id => $grader) {
                if (!isset($courses_tree[$id])) {
                    $courses_tree[$id] = array(
                        'user'    => $grader,
                        'courses' => array(
                            $course_data->id => $course_data
                        )
                    );
                } else {
                    $courses_tree[$id]['courses'][$course_data->id] = $course_data;
                }
            }
        }

        $index = 0;
        foreach ($courses_tree as $data) {
            $myxls->write_string($index++, 0, fullname($data['user']));

            $myxls->write_string($index, 0, get_string('key27', 'block_sibcms'));
            $myxls->write_string($index, 1, get_string('key28', 'block_sibcms'));
            $myxls->write_string($index, 2, get_string('key39', 'block_sibcms'));
            $myxls->write_string($index, 3, get_string('key40', 'block_sibcms'));
            $myxls->write_string($index, 4, get_string('key41', 'block_sibcms'));
            $myxls->write_string($index, 5, get_string('key42', 'block_sibcms'));
            $myxls->write_string($index, 6, get_string('key43', 'block_sibcms'));
            $myxls->write_string($index, 7, get_string('key47', 'block_sibcms'));
            $myxls->write_string($index, 8, get_string('key48', 'block_sibcms'));
            $myxls->write_string($index, 9, get_string('key49', 'block_sibcms'));
            $myxls->write_string($index, 10, get_string('key68', 'block_sibcms'));
            $myxls->write_string($index, 11, get_string('key29', 'block_sibcms'));

            $temp = 12;
            if ($monitoring) {
                $myxls->write_string($index, $temp++, get_string('key35', 'block_sibcms'));
            }
            $myxls->write_string($index++, $temp, get_string('key77', 'block_sibcms'));

            $result = 0;
            foreach ($data['courses'] as $course) {
                $myxls->write_string($index, 0, $course->fullname);

                $graders = array();
                foreach ($course->graders as $grader) {
                    $content = fullname($grader);
                    $content .= $grader->lastcourseaccess ? ' (' . format_time(time() - $grader->lastcourseaccess) . ')' : ' (' . get_string('never') . ')';
                    $graders[] = $content;
                }
                $myxls->write_string($index, 1, implode('; ', $graders));

                $myxls->write_number($index, 2, $course->assigns_results->participants);
                $myxls->write_number($index, 3, $course->assigns_results->submitted);
                $myxls->write_number($index, 4, $course->assigns_results->submitted_persent);
                $myxls->write_number($index, 5, $course->assigns_results->graded);
                $myxls->write_number($index, 6, $course->assigns_results->graded_persent);

                $myxls->write_number($index, 7, $course->quiz_results->participants);
                $myxls->write_number($index, 8, $course->quiz_results->submitted);
                $myxls->write_number($index, 9, $course->quiz_results->submitted_persent);

                $myxls->write_number($index, 10, $course->result);
                $result += $course->result;

                $comment = '';
                $feedback = '';
                $datetime = get_string('never');
                $feedback_data = $course->feedback;
                if ($feedback_data) {
                    $comment = $feedback_data->comment;
                    $feedback = $feedback_data->feedback;
                    $datetime = userdate($feedback_data->timecreated, '%d %b %Y, %H:%M');
                }
                $myxls->write_string($index, 11, $feedback);

                $temp = 12;
                if ($monitoring) {
                    $myxls->write_string($index, $temp++, $comment);
                }

                $myxls->write_string($index, $temp++, $datetime);

                $myxls->write_string($index++, $temp, "$CFG->wwwroot/course/view.php?id=$course->id");
            }

            $count = count($data['courses']); // > 0
            $myxls->write_string($index, 9, get_string('key63', 'block_sibcms'));
            $myxls->write_number($index, 10, $result / $count);

            $index += 2;
        }

    }

    $workbook->close();

    exit;
}

$returnurl = new moodle_url('/blocks/sibcms/report.php');
$returnurl->param('id', $course_id);
$returnurl->param('category', $category_id);
redirect($returnurl);
