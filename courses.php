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
require_once ('../../lib/coursecatlib.php');

$category_id = required_param('category', PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);

$PAGE->set_url(new moodle_url('/blocks/sibcms/courses.php', array('category' => $category_id, 'page' => $page)));

require_login(1);

require_capability('block/sibcms:monitoring', context_system::instance());

// System root link
$PAGE->navbar->add(get_string('key21', 'block_sibcms'), new moodle_url('/blocks/sibcms/category.php'));
//$PAGE->navbar->add(get_course(1)->shortname, new moodle_url('/blocks/sibcms/category.php'));

$category = coursecat::get($category_id);
$PAGE->set_heading($category->name);
$path = $category->get_parents();
foreach ($path as $parent) {
    $parent_category = coursecat::get($parent);
    $PAGE->navbar->add($parent_category->name,
        new moodle_url('/blocks/sibcms/category.php', array('id' => $parent_category->id)));
}
$PAGE->navbar->add($category->name,
    $category->has_children() ?
        new moodle_url('/blocks/sibcms/category.php', array('id' => $category->id)) :
        null);
$PAGE->navbar->add(get_string('key8', 'block_sibcms'), $PAGE->url);
$PAGE->set_title(get_string('key22', 'block_sibcms', array('name' => $category->name)));

$output = $PAGE->get_renderer('block_sibcms');

$last_feedback = null;
if (isset($SESSION->block_sibcms_lastfeedback)) {
    $last_feedback = $SESSION->block_sibcms_lastfeedback;
}

$category_table = new block_sibcms\output\category_courses_table($category_id, $page, $last_feedback);

echo $output->header();

echo $output->render($category_table);

echo $output->footer();
