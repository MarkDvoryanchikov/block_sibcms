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

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $name = get_string('key100', 'block_sibcms');
    $description = get_string('key101', 'block_sibcms');
    $settings->add(new admin_setting_configduration(
        'block_sibcms/feedback_relevance_duration',
        $name,
        $description,
        3 * 24 * 60 * 60 // 3 days
    ));

    $link = '<a href="'.$CFG->wwwroot.'/blocks/sibcms/properties.php">'.get_string('key105', 'block_sibcms').'</a>';
    $settings->add(new admin_setting_heading('block_sibcms_properties', '', $link));

}
