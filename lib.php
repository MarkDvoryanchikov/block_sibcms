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
 * @copyright  2017 Sergey Shlyanin <sergei.shlyanin@gmail.com>, Aleksandr Raetskiy <ksenon3@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/coursecatlib.php');

// Link to feedback form page
function block_sibcms_extend_navigation_course(navigation_node $navigation, $course, $context)
{
    global $PAGE;
    global $USER;

    $monitoring        = has_capability('block/sibcms:monitoring', context_system::instance());
    $activity_report   = has_capability('block/sibcms:activity_report', $context);
    $teacher_report    = has_capability('block/sibcms:teacher_report', $context);
    $monitoring_report = has_capability('block/sibcms:monitoring_report', $context) &&
                         count(coursecat::make_categories_list('block/sibcms:monitoring_report_category')) > 0;

    if ($monitoring || $activity_report || $teacher_report || $monitoring_report) {
        $navigation = $navigation->add(get_string('key20', 'block_sibcms'), null, navigation_node::TYPE_CONTAINER, null, 'sibcms_reports', new pix_icon('i/stats', ''));
    }

    if ($activity_report && $course->id != 1) {
        $url = new moodle_url('/blocks/sibcms/report.php', array('id' => $course->id));
        $navigation->add(get_string('key61', 'block_sibcms'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
    if ($teacher_report) {
        $url = new moodle_url('/blocks/sibcms/report.php', array('id' => $course->id, 'teacher' => $USER->id ));
        $navigation->add(get_string('key112', 'block_sibcms'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
    if ($monitoring_report) {
        $category_id = optional_param('category', 0, PARAM_INT);
        $url = new moodle_url('/blocks/sibcms/report.php', array('id' => $course->id, 'category' => $category_id));
        $navigation->add(get_string('key21', 'block_sibcms'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }

    if ($monitoring && $course->id != 1) {
        $url = new moodle_url('/blocks/sibcms/course.php',
            array('id' => $course->id, 'category' => $course->category, 'returnurl' => $PAGE->url));
        $navigation->add(get_string('key19', 'block_sibcms'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('monitoring', '', 'block_sibcms'));
    }
}

function block_sibcms_extend_navigation_frontpage(navigation_node $navigation, $course, $context)
{
    block_sibcms_extend_navigation_course($navigation, $course, $context);
}
