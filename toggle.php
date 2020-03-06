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
$return_url  = optional_param('returnurl', new moodle_url('/blocks/sibcms/report.php', array('id' => $id)), PARAM_URL);

list($course, $module) = get_course_and_cm_from_cmid($id);
if ($module->modname != 'assign' && $module->modname != 'quiz') {
    print_error('error');
}
require_login($course);

$context = context_course::instance($course->id);
require_capability('block/sibcms:activity_report', $context);
require_capability('block/sibcms:activity_report_toggle', $context);

if ($show && confirm_sesskey()) {
    if (!\block_sibcms\sibcms_api::get_modvisible($module)) {
        \block_sibcms\sibcms_api::set_modvisible($module, 1);
    }
} else if ($hide && confirm_sesskey()) {
    if (\block_sibcms\sibcms_api::get_modvisible($module)) {
        \block_sibcms\sibcms_api::set_modvisible($module, 0);
    }
}

redirect($return_url);
