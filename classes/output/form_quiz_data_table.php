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

defined('MOODLE_INTERNAL') || die();

class form_quiz_data_table implements \renderable
{
    public $table_head;
    public $table_size;
    public $table_data;
    public $table_classes;

    /**
     * Quiz_data_table constructor.
     * @param $course_data
     */
    public function __construct($course_data)
    {
        global $OUTPUT;

        if (count($course_data->quiz) == 0) {
            return '';
        }

        $this->table_head = array();
        // Test name
        $this->table_head[] = get_string('key46', 'block_sibcms');
        // Time limit
        $this->table_head[] = get_string('key94', 'block_sibcms');
        // Participant count
        $this->table_head[] = get_string('key47', 'block_sibcms');
        // Submitted attempts
        $this->table_head[] = get_string('key48', 'block_sibcms');

        $this->table_size = array('40%', '20%', '20%', '20%');

        $this->table_data = array();
        foreach ($course_data->quiz as $id => $quiz) {
            $table_row_data = array();
            // Quiz name;
            $content = $OUTPUT->pix_icon('icon', '', 'quiz', array('class' => 'icon')) . $quiz->name;
            if ($quiz->visible) {
                $quiz_url = new \moodle_url('/mod/quiz/view.php', array('id' => $id));
                $content = \html_writer::link($quiz_url, $content) . '&nbsp;';

                $settings_url = new \moodle_url('/course/modedit.php', array('update' => $id));
                $icon = $OUTPUT->pix_icon('i/settings', get_string('settings'), '', array('class' => 'iconsmall'));
                $content .= \html_writer::link($settings_url, $icon);
            } else {
                $content .= '&nbsp;';
            }

            if (!$quiz->timelimit) {
                $content .= $OUTPUT->pix_icon('notimelimit', get_string('key96', 'block_sibcms'), 'block_sibcms', array('class' => 'iconsmall'));
            }
            if ($quiz->noquestions) {
                $content .= $OUTPUT->pix_icon('noquestions', get_string('key74', 'block_sibcms'), 'block_sibcms', array('class' => 'iconsmall'));
            }
            $table_row_data[] = $content;

            // Time limit
            $table_row_data[] = $quiz->timelimit ? format_time($quiz->timelimit) : '—';
            // Participant count
            $table_row_data[] = $quiz->participants;
            // Submitted attempts
            $table_row_data[] = $quiz->submitted;

            $this->table_classes[] = !$quiz->modvisible ? 'dimmed_text' : '';
            // Write data
            $this->table_data[] = $table_row_data;
        }

    }

}
