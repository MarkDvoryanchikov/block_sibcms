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

$show = optional_param('show', 0, PARAM_INT);
$hide = optional_param('hide', 0, PARAM_INT);
$id = $show ? $show : $hide;

$course = get_course($id);

$return_url  = optional_param('returnurl',
    new moodle_url('/blocks/sibcms/courses.php', array('category' => $course->category)), PARAM_URL);

require_login(1);

$context = context_system::instance();
require_capability('block/sibcms:monitoring', $context);

if ($show && confirm_sesskey()) {
    if (\block_sibcms\sibcms_api::get_course_ignore($id)) {
        \block_sibcms\sibcms_api::set_course_ignore($id, 0);
    }
} else if ($hide && confirm_sesskey()) {
    if (!\block_sibcms\sibcms_api::get_course_ignore($id)) {
        \block_sibcms\sibcms_api::set_course_ignore($id, 1);
    }
}

redirect($return_url);
