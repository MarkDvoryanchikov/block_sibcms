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

class activity_quiz_data_table implements \renderable
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
        global $OUTPUT, $CFG, $PAGE;

        $this->table_head = array();
        $this->table_head[] = get_string('key46', 'block_sibcms');
        $this->table_head[] = get_string('key47', 'block_sibcms');
        $this->table_head[] = get_string('key48', 'block_sibcms');
        $this->table_head[] = get_string('key49', 'block_sibcms');

        $this->table_size = array('70%', '10%', '10%', '10%');

        $this->table_data = array();
        foreach ($course_data->quiz as $id => $quiz) {
            $table_row_data = array();

            $content = $OUTPUT->pix_icon('icon', '', 'quiz', array('class' => 'icon')) . $quiz->name;
            if ($quiz->visible) {
                $quiz_url = "$CFG->wwwroot/mod/quiz/view.php?id=$id";
                $content = \html_writer::link($quiz_url, $content);
            }
            $content .= '&nbsp;';
            $context = \context_course::instance($course_data->id);
            $toggle = has_capability('block/sibcms:activity_report', $context) &&
                      has_capability('block/sibcms:activity_report_toggle', $context) &&
                      (has_capability('moodle/course:view', $context) || is_enrolled($context));
            if ($toggle) {
                $showhide = $quiz->modvisible ? 'hide' : 'show';
                $toggleurl = new \moodle_url('/blocks/sibcms/toggle.php',
                    array(
                        $showhide   => $id, 
                        'sesskey'   => \sesskey(), 
                        'returnurl' => $PAGE->url . '#block_sibcms_' . $course_data->id
                    )
                );
                $icon = $OUTPUT->pix_icon("t/$showhide", get_string($showhide), '', array('class' => 'iconsmall'));
                $content .= \html_writer::link($toggleurl, $icon);
            }
            if (!$quiz->timelimit) {
                $content .= $OUTPUT->pix_icon('notimelimit', get_string('key96', 'block_sibcms'), 'block_sibcms', array('class' => 'iconsmall'));
            }
            if ($quiz->noquestions) {
                $content .= $OUTPUT->pix_icon('noquestions', get_string('key74', 'block_sibcms'), 'block_sibcms', array('class' => 'iconsmall'));   
            }
            $table_row_data[] = $content;

            $table_row_data[] = $quiz->participants;
            $table_row_data[] = $quiz->submitted;
            $table_row_data[] = $this->percentformat_value($quiz->submitted_persent, false);
            $this->table_data[] = $table_row_data;
            
            $this->table_classes[] = !$quiz->modvisible ? 'dimmed_text' : '';
        }

        $result_row = array();
        $result_row[] = get_string('key63', 'block_sibcms');
        $result_row[] = $course_data->quiz_results->participants;
        $result_row[] = $course_data->quiz_results->submitted;
        $result_row[] = $this->percentformat_value($course_data->quiz_results->submitted_persent);
        $this->table_data[] = $result_row;
    }

    private function percentformat_value($value, $color = true) {
        $class = '';
        $value *= 100;
        if ($color) {
            if ($value < 50) {
                $class = 'block_sibcms_red';
            } else if ($value < 85) {
                $class = 'block_sibcms_yellow';
            } else {
                $class = 'block_sibcms_green';
            }
        }
        return \html_writer::start_span($class) . format_float($value, 2, true, true) . '%' . \html_writer::end_span();
    }
}
