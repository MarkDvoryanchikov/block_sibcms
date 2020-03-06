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

require_once('../../config.php');
require_once('../../lib/coursecatlib.php');
require_once("$CFG->libdir/formslib.php");

$course_id   = required_param('id', PARAM_INT);
$category_id = required_param('category', PARAM_INT);
$return_url  = optional_param('returnurl',
    new moodle_url('/blocks/sibcms/courses.php', array('category' => $category_id)), PARAM_URL);

$PAGE->set_url(new moodle_url('/blocks/sibcms/course.php', array('id' => $course_id, 'category' => $category_id)));

require_login(1);

require_capability('block/sibcms:monitoring', context_system::instance());

$course = get_course($course_id);
$category = coursecat::get($category_id);

//Check if the category contains the course
$courses = $category->get_courses(array('recursive' => true));
$course_founded = false;
foreach ($courses as $course) {
    if ($course->id == $course_id) {
        $course_founded = true;
        break;
    }
}
if (!$course_founded) {
    print_error('key83', 'block_sibcms', '',
        array('category' => $category_id, 'course' => $course_id));
}

$PAGE->set_heading($course->fullname);
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('key21', 'block_sibcms'), new moodle_url('/blocks/sibcms/category.php'));
$path = $category->get_parents();
foreach ($path as $parent) {
    $parent_category = coursecat::get($parent);
    $PAGE->navbar->add($parent_category->name, new moodle_url('/blocks/sibcms/category.php', array('id' => $parent_category->id)));
}
$PAGE->navbar->add($category->name,
    $category->has_children() ?
        new moodle_url('/blocks/sibcms/category.php', array('id' => $category->id)) :
        null);
$PAGE->navbar->add(get_string('key8', 'block_sibcms'),
    new moodle_url('/blocks/sibcms/courses.php', array('category' => $category->id)));
$PAGE->navbar->add($course->fullname, $PAGE->url);
$PAGE->set_title(get_string('key22', 'block_sibcms', array('name' => $course->fullname)));

$output = $PAGE->get_renderer('block_sibcms');

$last_feedback = block_sibcms\sibcms_api::get_last_course_feedback($course_id);

$custom_data = array(
    'course_data'   => block_sibcms\sibcms_api::get_course_data($course, 0, false),
    'last_feedback' => $last_feedback
);
$mform = new block_sibcms\feedback_form(null, $custom_data);

if ($mform->is_cancelled()) {
    redirect($return_url);
} else if ($data = $mform->get_data()) {
    \block_sibcms\sibcms_api::save_feedback(
        $data->id,
        $data->feedback,
        $data->comment,
        $data->result ? 0 : 1,
        $data->properties
    );
    $event = \block_sibcms\event\comment_created::create(array('objectid' => $course_id, 'other' => array('category' => $category_id)));
    $event->trigger();
    $SESSION->block_sibcms_lastfeedback = $data->id;
    if (!empty($data->submitbutton)) {
        redirect($return_url);
    }
    if (!empty($data->submitbutton2)) {
        $next_course = \block_sibcms\sibcms_api::get_require_attention_course($category->id);
        if (!empty($next_course)) {
            redirect(new moodle_url('/blocks/sibcms/course.php', array('id' => $next_course, 'category' => $category->id)));
        } else {
            $SESSION->block_sibcms_no_next_course = true;
            redirect($return_url);
        }
    }
} else {
    echo $output->header();

    $params = array(
        'id'        => $course_id,
        'category'  => $category_id,
        'returnurl' => $return_url,
        'feedback'  => '',
        'comment'   => ''
    );

    if ($last_feedback) {
        $params['feedback'] = $last_feedback->feedback;
        $params['comment']  = $last_feedback->comment;
        $properties = array();
        foreach ($last_feedback->properties as $property) {
            $properties[$property->id] = 1;
        }
        $params['properties'] = $properties;
        $params['result'] = $last_feedback->result ? 0 : 1;

    }

    $mform->set_data($params);

    echo \html_writer::start_div('', array('id' => 'block_sibcms'));
    $mform->display();
    echo \html_writer::end_div();


    echo $output->footer();
}
