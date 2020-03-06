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

class activity_assigns_data_table implements \renderable
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
        $this->table_head[] = get_string('key38', 'block_sibcms');
        $this->table_head[] = get_string('key39', 'block_sibcms');
        $this->table_head[] = get_string('key40', 'block_sibcms');
        $this->table_head[] = get_string('key41', 'block_sibcms');
        $this->table_head[] = get_string('key42', 'block_sibcms');
        $this->table_head[] = get_string('key43', 'block_sibcms');

        $this->table_size = array('50%', '10%', '10%', '10%', '10%', '10%');

        $this->table_data = array();
        foreach ($course_data->assigns as $id => $assign) {
            $table_row_data = array();

            $content = $OUTPUT->pix_icon('icon', '', 'assign', array('class' => 'icon')) . $assign->name;
            if ($assign->visible) {
                $assign_url = "$CFG->wwwroot/mod/assign/view.php?id=$id";
                $content = \html_writer::link($assign_url, $content);
            }
            $content .= '&nbsp;';
            $context = \context_course::instance($course_data->id);
            $toggle = has_capability('block/sibcms:activity_report', $context) &&
                      has_capability('block/sibcms:activity_report_toggle', $context) &&
                      (has_capability('moodle/course:view', $context) || is_enrolled($context));
            if ($toggle) {
                $showhide = $assign->modvisible ? 'hide' : 'show';
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

            $table_row_data[] = $assign->teamsubmission ? '—' : $assign->participants;
            $table_row_data[] = $assign->teamsubmission ? '—' : $assign->submitted;
            if (!$assign->teamsubmission) {
                $table_row_data[] = $this->percentformat_value($assign->submitted_persent, false);
            } else {
                $table_row_data[] = '—';
            }
            if (!$assign->teamsubmission && !$assign->nograde) {
                $content = $assign->graded;
                if ($assign->need_grading > 0) {
                    $content .= '&nbsp;' . $OUTPUT->pix_icon('alert', get_string('key72', 'block_sibcms'), 'block_sibcms', array('class' => 'icon'));
                }
                $table_row_data[] = $content;
                $table_row_data[] = $this->percentformat_value($assign->graded_persent, false);
            } else {
                $table_row_data[] = '—';
                $table_row_data[] = '—';
            }
            $this->table_data[] = $table_row_data;
            
            $this->table_classes[] = !$assign->modvisible ? 'dimmed_text' : '';
        }

        $result_row = array();
        $result_row[] = get_string('key63', 'block_sibcms');
        $result_row[] = $course_data->assigns_results->participants;
        $result_row[] = $course_data->assigns_results->submitted;
        $result_row[] = $this->percentformat_value($course_data->assigns_results->submitted_persent);
        $result_row[] = $course_data->assigns_results->graded;
        $result_row[] = $this->percentformat_value($course_data->assigns_results->graded_persent);
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
