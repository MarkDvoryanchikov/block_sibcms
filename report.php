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

$id = required_param('id', PARAM_INT);
$category_id = optional_param('category', -1, PARAM_INT);
$teacher_id = optional_param('teacher', -1, PARAM_INT);

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

$str_report = '';
$params = array('id' => $id);
if ($teacher_id > 0) {
    $str_report = get_string('key112', 'block_sibcms');
} elseif ($category_id < 0) {
    $str_report = get_string('key61', 'block_sibcms');
} else {
    $str_report = get_string('key21', 'block_sibcms');
    $params['category'] = $category_id;
}

$PAGE->set_url('/blocks/sibcms/report.php', $params);

require_login($course);
$context = context_course::instance($course->id);

if ($teacher_id > 0) {
    require_capability('block/sibcms:teacher_report', $context); 
} elseif ($category_id < 0) {
    require_capability('block/sibcms:activity_report', $context);
} else {
    require_capability('block/sibcms:monitoring_report', $context);
    if ($category_id > 0) {
        $contextcoursecat = context_coursecat::instance($category_id);
        require_capability('block/sibcms:monitoring_report_category', $contextcoursecat);
    }
}

$PAGE->set_title("$course->shortname: $str_report");
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('incourse');

if ($category_id >= 0) {
    $PAGE->requires->css('/blocks/sibcms/css/styles-plugin.css');
    $PAGE->requires->js_call_amd('block_sibcms/monitoringtable', 'init');
}

echo $OUTPUT->header();
echo $OUTPUT->heading($str_report);

$renderer = $PAGE->get_renderer('block_sibcms');
if ($teacher_id > 0) {
    echo $renderer->display_teacher_report($teacher_id);
    $event = \block_sibcms\event\activity_viewed::create(array('context' => $context));
    $event->trigger();
} elseif ($category_id < 0) {
    echo $renderer->display_activity_report($id);
    $event = \block_sibcms\event\activity_viewed::create(array('context' => $context));
    $event->trigger();
} else {
    echo $renderer->display_monitoring_report($id, $category_id);
    $event = \block_sibcms\event\monitoring_viewed::create(array('context' => $context, 'objectid' => $category_id));
    $event->trigger();
}

echo $OUTPUT->footer();
