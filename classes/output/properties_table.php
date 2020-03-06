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

namespace block_sibcms\output;

use block_sibcms\sibcms_api;

defined('MOODLE_INTERNAL') || die();

class properties_table implements \renderable
{
    public $table_head;
    public $table_size;
    public $table_data;
    public $table_classes;

    /**
     * Quiz_data_table constructor.
     * @param $course_data
     */
    public function __construct($properties)
    {
        global $OUTPUT, $PAGE;

        $this->table_head = array();
        // Property name
        $this->table_head[] = get_string('key102', 'block_sibcms');
        // Settings
        $this->table_head[] = get_string('key103', 'block_sibcms');

        $this->table_size = array('80%', '20%');

        $this->table_data = array();
        foreach ($properties as $property) {
            $table_row_data = array();
            // Property name
            $table_row_data[] = $property->name;
            // Settings
            $settings = '';

            $showhide = $property->hidden ? 'show' : 'hide';
            $toggleurl = new \moodle_url('/blocks/sibcms/properties.php',
                array(
                    $showhide   => $property->id,
                    'sesskey'   => \sesskey(),
                    'returnurl' => $PAGE->url
                )
            );
            $icon = $OUTPUT->pix_icon("t/$showhide", get_string($showhide), '', array('class' => 'iconsmall'));
            $settings .= \html_writer::link($toggleurl, $icon);

            $deleteurl = new \moodle_url('/blocks/sibcms/properties.php',
                array(
                    'delete'   => $property->id,
                    'sesskey'   => \sesskey(),
                    'returnurl' => $PAGE->url
                )
            );
            $icon = $OUTPUT->pix_icon("t/delete", get_string('delete'), '', array('class' => 'iconsmall'));
            $settings .= \html_writer::link($deleteurl, $icon);

            $table_row_data[] = $settings;

            $this->table_classes[] = $property->hidden ? 'dimmed_text' : '';
            // Write data
            $this->table_data[] = $table_row_data;
        }
    }

}
