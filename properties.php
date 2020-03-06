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

$show       = optional_param('show', 0, PARAM_INT);
$hide       = optional_param('hide', 0, PARAM_INT);
$delete     = optional_param('delete', 0, PARAM_INT);
$return_url = optional_param('returnurl', new moodle_url('/blocks/sibcms/properties.php'), PARAM_URL);

$id = $show ? $show : ($hide ? $hide : $delete);

$PAGE->set_url(new moodle_url('/blocks/sibcms/properties.php'));

require_login(1);

if (!is_siteadmin()) {
    print_error('error');
}

$PAGE->set_heading(get_string('key105', 'block_sibcms'));
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('key21', 'block_sibcms'), new moodle_url('/blocks/sibcms/category.php'));
$PAGE->navbar->add(get_string('key105', 'block_sibcms'), new moodle_url('/blocks/sibcms/properties.php'));
$PAGE->set_title(get_string('key105', 'block_sibcms'));

$mform = new \block_sibcms\property_form();
$properties = \block_sibcms\sibcms_api::get_properties(false);

if ($id) {
    if (!array_key_exists($id, $properties)) {
        print_error('error');
    }
}

if ($show && confirm_sesskey()) {
    if ($properties[$id]->hidden) {
        \block_sibcms\sibcms_api::set_propvisible($id, 0);
    }
    redirect($return_url);
} else if ($hide && confirm_sesskey()) {
    if (!$properties[$id]->hidden) {
        \block_sibcms\sibcms_api::set_propvisible($id, 1);
    }
    redirect($return_url);
} else if ($delete && confirm_sesskey()) {
    \block_sibcms\sibcms_api::delete_property($id);
    redirect($return_url);
} else if ($data = $mform->get_data()) {
    block_sibcms\sibcms_api::add_property($data->name);
    redirect($return_url);
}

$output = $PAGE->get_renderer('block_sibcms');

echo $output->header();

if (count($properties) > 0) {
    $properties_table = new \block_sibcms\output\properties_table($properties);
    echo $output->render($properties_table);
}

$mform->display();

echo $output->footer();
