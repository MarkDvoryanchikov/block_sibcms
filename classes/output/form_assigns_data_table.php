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

namespace block_sibcms\output;

defined('MOODLE_INTERNAL') || die();

class form_assigns_data_table implements \renderable
{
    public $table_head;
    public $table_size;
    public $table_data;
    public $table_classes;

    /**
     * Assigns_data_table constructor.
     * @param $course_data
     */
    public function __construct($course_data)
    {
        global $OUTPUT, $CFG;

        if (count($course_data->assigns) == 0) {
            return '';
        }

        $this->table_head = array();
        // Assign name
        $this->table_head[] = get_string('key38', 'block_sibcms');
        // Participant count
        $this->table_head[] = get_string('key39', 'block_sibcms');
        // Submited files
        $this->table_head[] = get_string('key40', 'block_sibcms');
        // Graded files
        $this->table_head[] = get_string('key42', 'block_sibcms');
        // Grade type
        $this->table_head[] = get_string('key44', 'block_sibcms');
        // Feedback types
        $this->table_head[] = get_string('key45', 'block_sibcms');

        $this->table_size = array('30%', '10%', '10%', '10%', '15%', '35%');

        $this->table_data = array();
        foreach ($course_data->assigns as $id => $assign) {
            $table_row_data = array();
            // Assign name
            $content = $OUTPUT->pix_icon('icon', '', 'assign', array('class' => 'icon')) . $assign->name;
            if ($assign->visible) {
                $assign_url = new \moodle_url('/mod/assign/view.php', array('id' => $id));
                $content = \html_writer::link($assign_url, $content) . '&nbsp;';

                $settings_url = new \moodle_url('/course/modedit.php', array('update' => $id));
                $icon = $OUTPUT->pix_icon('i/settings', get_string('settings'), '', array('class' => 'iconsmall'));
                $content .= \html_writer::link($settings_url, $icon);
            } else {
                $content .= '&nbsp;';
            }

            if ($assign->nograde) {
                $content .= $OUTPUT->pix_icon('nograde', get_string('key56', 'block_sibcms'), 'block_sibcms', array('class' => 'iconsmall'));
            }
            if ($assign->teamsubmission) {
                $content .= $OUTPUT->pix_icon('i/users', get_string('key73', 'block_sibcms'), '', array('class' => 'iconsmall'));
            }
            if ($assign->plagiarism) {
                $content .= $OUTPUT->pix_icon('plagiarism', get_string('key108', 'block_sibcms'), 'block_sibcms', array('class' => 'iconsmall'));
            }
            $table_row_data[] = $content;
            // Participant count
            $table_row_data[] = $assign->teamsubmission ? '—' : $assign->participants;
            // Submited files
            $table_row_data[] = $assign->teamsubmission ? '—' : $assign->submitted;
            // Graded files
            if (!$assign->teamsubmission && !$assign->nograde) {
                $table_row_data[] = $assign->graded;
            } else {
                $table_row_data[] = '—';
            }
            // Grade type
            $table_row_data[] = $assign->grade;
            // Feedback types
            $feedbacks = $assign->feedbacks;
            $feedbacks_names = array();
            foreach ($feedbacks as $feedback) {
                $feedbacks_names[] = $feedback->get_name();
            }
            if (count($feedbacks_names) == 0) {
                $feedbacks_names[] = get_string('key75', 'block_sibcms');
            }
            $table_row_data[] = \html_writer::alist($feedbacks_names);

            $this->table_classes[] = !$assign->modvisible ? 'dimmed_text' : '';
            // Write data
            $this->table_data[] = $table_row_data;
        }

    }

}
