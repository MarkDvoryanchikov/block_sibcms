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

namespace block_sibcms;

use block_sibcms\output\form_assigns_data_table;
use block_sibcms\output\form_quiz_data_table;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class feedback_form extends \moodleform
{

    public function definition()
    {
        global $PAGE, $OUTPUT;
        $mform = $this->_form;
        $course_data = $this->_customdata['course_data'];
        $last_feedback = $this->_customdata['last_feedback'];
        $renderer = $PAGE->get_renderer('block_sibcms');

        $mform->addElement('header', 'general', get_string('general', 'form'));
        // Course ID
        $mform->addElement('hidden', 'id', null);
        $mform->setType('id', PARAM_INT);

        // Category ID
        $mform->addElement('hidden', 'category', null);
        $mform->setType('category', PARAM_INT);

        // Return url
        $mform->addElement('hidden', 'returnurl', null);
        $mform->setType('returnurl', PARAM_URL);

        // Course link
        $context = \context_course::instance($course_data->id);
        $course_access = (has_capability('moodle/course:view', $context) || is_enrolled($context));
        $course_name = $course_data->fullname;
        if ($course_access) {
            $course_url = new \moodle_url('/course/view.php', array('id' => $course_data->id));
            $course_name = \html_writer::link($course_url, $course_data->fullname, array('target' => '_blank'));
        }
        $mform->addElement('static', 'course_fullname', get_string('key27', 'block_sibcms'), $course_name);

        // Graders
        $graders = $course_data->graders;
        if (count($graders)) {
            $graders_str = \html_writer::start_tag('ul');
            foreach ($graders as $grader) {
                $grader_link = \html_writer::link(
                    new \moodle_url('/user/view.php', array('id' => $grader->id, 'course' => $course_data->id)),
                    fullname($grader));
                $time_ago = get_string('never');
                if ($grader->lastcourseaccess) {
                    $time_ago = format_time(time() - $grader->lastcourseaccess);
                }
                $grader_text = "$grader_link ($time_ago)";
                $graders_str .= \html_writer::tag('li', $grader_text);
            }
            $graders_str .= \html_writer::end_tag('ul');
            $mform->addElement('static', 'graders',
                get_string('key28', 'block_sibcms'),
                $graders_str
            );
        }

        // Hints
        $hints = sibcms_api::get_hints($course_data);
        $hints_str = '';
        if (count($hints)) {
            $hints_str = \html_writer::alist($hints);
        } else {
            $hints_str .= get_string('key58', 'block_sibcms');
        }
        $mform->addElement('static', 'autohints',
            get_string('key109', 'block_sibcms'),
            $hints_str
        );

        // Last feedback time
        $mform->addElement('static', 'graders',
            get_string('key88', 'block_sibcms'),
            ($last_feedback) ?
                userdate($last_feedback->timecreated) . '&nbsp;(' . format_time(time() - $last_feedback->timecreated) . ')' :
                    get_string('never')
        );

        // Course changed
        if ($last_feedback) {
            $course_changes = sibcms_api::course_was_changed($course_data);
            if ($course_changes === null) {
                $course_changed_content = $OUTPUT->pix_icon('unknown', '',
                        'block_sibcms') . '&nbsp;' . get_string('key107', 'block_sibcms');
            } else if (count($course_changes) === 0) {
                $course_changed_content = $OUTPUT->pix_icon('no', '',
                        'block_sibcms') . '&nbsp;' . get_string('no');
            } else {
                $course_changed_content = $OUTPUT->pix_icon('yes', '',
                        'block_sibcms') . '&nbsp;' . get_string('yes');
                $course_changes_str = implode($course_changes, '; ');
                $course_changed_content .= '&nbsp;(' . \core_text::strtolower($course_changes_str) . ')';
            }
            $mform->addElement('static', 'coursechanged',
                get_string('key106', 'block_sibcms'),
                $course_changed_content
            );
        }

        $assigns = $course_data->assigns;
        $quiz = $course_data->quiz;
        if (count($assigns) || count($quiz)) {
            $mform->addElement('header', 'mosuledate',
                get_string('key86', 'block_sibcms'));

            // Assigns information
            if (count($assigns)) {
                $assign_table = new form_assigns_data_table($course_data);
                $assign_table_str = $renderer->render($assign_table);
                $mform->addElement('static', 'assign_table',
                    get_string('key37', 'block_sibcms'),
                    $assign_table_str
                );
            }

            // Quiz information
            if (count($quiz)) {

                $quiz_table = new form_quiz_data_table($course_data);
                $quiz_table_str = $renderer->render($quiz_table);
                $mform->addElement('static', 'quiz_table',
                    get_string('key37', 'block_sibcms'),
                    $quiz_table_str
                );
            }
        }

        // Course view
        if ($course_access) {
            $mform->addElement('header', 'coursedisplayhrd', get_string('key89', 'block_sibcms'));
            $course_iframe = \html_writer::tag('iframe', '',
                array(
                    'src' => new \moodle_url('/course/view.php', array('id' => $course_data->id)),
                    'width' => '100%',
                    'height' => '500px'
                )
            );
            $mform->addElement('static', 'course_display', '', $course_iframe);
        }

        $mform->addElement('header', 'feedbackhdr',
            get_string('key87', 'block_sibcms'));
        $mform->setExpanded('feedbackhdr');

        // Result
        $mform->addElement('advcheckbox', 'result', '', get_string('key23', 'block_sibcms'),
            array('group' => 1, 'class' => 'block_sibcms_props'), array(0, 1));

        // Feedback properties
        $properties = sibcms_api::get_properties();
        foreach ($properties as $property) {
            $mform->addElement('advcheckbox', 'properties[' . $property->id . ']', '', $property->name,
                array('group' => 1, 'class' => 'block_sibcms_props'), array(0, 1));
        }

        // Feedback textarea
        $mform->addElement('textarea', 'feedback', get_string('key34', 'block_sibcms'),
            'wrap="virtual" cols="50" rows="8" class="block_sibcms_feedback"');

        // Comment textarea
        $mform->addElement('textarea', 'comment', get_string('key35', 'block_sibcms'), 'wrap="virtual" cols="50" rows="3"');

        // Buttons
        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('key32', 'block_sibcms'));
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton2', get_string('key84', 'block_sibcms'));
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->setType('buttonar', PARAM_RAW);
        $mform->closeHeaderBefore('buttonar');
    }

    function validation($data, $files)
    {
        $errors = parent::validation($data, $files);
        return $errors;
    }

}
