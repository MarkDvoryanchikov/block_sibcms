<?php

require_once('../../config.php');
require_once($CFG->libdir.'/coursecatlib.php');
require_once($CFG->libdir.'/excellib.class.php');

$course_id   = required_param('id', PARAM_INT);
$category_id = optional_param('category', 0, PARAM_INT);

$course = $DB->get_record('course', array('id' => $course_id), '*', MUST_EXIST);
$contextcoursecat = $category_id ? context_coursecat::instance($category_id) : null;

$base_url = new moodle_url('/blocks/sibcms/export.php', array('id' => $course_id));
$PAGE->set_url($base_url);

require_login($course);
$contextcourse = context_course::instance($course_id);
require_capability('block/sibcms:monitoring_report', $contextcourse);

if ($contextcoursecat) {
    require_capability('block/sibcms:monitoring_report_category', $contextcoursecat);

    $str_monitoring = get_string('key21', 'block_sibcms');
    $download_filename = clean_filename("$str_monitoring.xls");
    $workbook = new MoodleExcelWorkbook("-");
    $workbook->send($download_filename);
    $myxls = $workbook->add_worksheet($str_monitoring);

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

    $index = 1;
    $courses = coursecat::get($category_id)->get_courses(array('recursive' => true));
    foreach ($courses as $course) {
        if (!$course->visible) continue;
        $course_data = \block_sibcms\sibcms_api::get_course_data($course);

        $myxls->write_string($index, 0, $course_data->fullname);

        $graders = array();
        if (count($course_data->graders) > 0) {
            foreach ($course_data->graders as $grader) {
                $content = fullname($grader);
                $content .= $grader->lastcourseaccess ? '(' . format_time(time() - $grader->lastcourseaccess) . ')' : '(' . get_string('never') . ')';
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

        $comment = get_string('key76', 'block_sibcms');
        $feedback = \block_sibcms\sibcms_api::get_last_course_feedback($course_data->id);
        if ($feedback) {
            $comment = $feedback->feedback;
        }
        $myxls->write_string($index, 11, $comment);

        $myxls->write_string($index, 12, "$CFG->wwwroot/course/view.php?id=$course->id");

        $index++;
    }

    $workbook->close();
    
    exit;
}

$returnurl = new moodle_url('/blocks/sibcms/report.php');
$returnurl->param('id', $course_id);
$returnurl->param('category', $category_id);
redirect($returnurl);