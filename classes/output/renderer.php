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
 * @copyright  2017 Sergey Shlyanin <sergei.shlyanin@gmail.com>, Aleksandr Raetskiy <ksenon3@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_sibcms\output;

use block_sibcms\sibcms_api;

defined('MOODLE_INTERNAL') || die();

class renderer extends \plugin_renderer_base
{

    /**
     * Render the table with subcategories and course count for selected category
     * @param category_statistic_table $widget
     * @return string
     */
    public function render_category_statistic_table(category_statistic_table $widget)
    {
        global $OUTPUT;
        $result = '';
        if (count($widget->categories) > 0) {
            $table = new \html_table();
            $table->head = array(
                get_string('key2', 'block_sibcms'),
                get_string('key3', 'block_sibcms'),
                get_string('key4', 'block_sibcms')
            );
            foreach ($widget->categories as $category) {
                $category_str = $category->name;
                if ($category->has_subcategories) {
                    $category_url = new \moodle_url('/blocks/sibcms/category.php', array('id' => $category->id));
                    $category_str = \html_writer::tag('a', $category->name, array('href' => $category_url));
                }
                $courses_url = new \moodle_url('/blocks/sibcms/courses.php', array('category' => $category->id));
                $courses_link = \html_writer::tag('a',
                    get_string('key9', 'block_sibcms'),
                    array('href' => $courses_url)
                );
                $courses_str = "{$category->courses_total} ($courses_link)";
                $table->data[] = array(
                    $category_str,
                    $courses_str,
                    $category->courses_require_attention
                );
            }
            $result .= \html_writer::table($table);
        } else {
            $result .= $OUTPUT->heading(get_string('key6', 'block_sibcms'));
        }
        return $result;
    }

    /**
     * Render the table with course feedback data for the selected category
     * @param category_courses_table $widget
     * @return string
     */
    public function render_category_courses_table(category_courses_table $widget)
    {
        global $OUTPUT, $SESSION, $PAGE;
        $result = \html_writer::start_div('', array('id' => 'block_sibcms'));
        if (count($widget->courses) > 0) {
            if (isset($SESSION->block_sibcms_no_next_course) && $SESSION->block_sibcms_no_next_course) {
                $SESSION->block_sibcms_no_next_course = false;
                $result .= $OUTPUT->notification(get_string('key85', 'block_sibcms'), 'success');
            }

            $table = new \html_table();
            $table->attributes = array('class' => 'table');
            $table->head = array(
                '',
                get_string('key10', 'block_sibcms'),
                get_string('key11', 'block_sibcms'),
                get_string('key13', 'block_sibcms')
            );
            $table->align[0] = 'center';
            foreach ($widget->courses as $course) {
                $feedback = \block_sibcms\sibcms_api::get_last_course_feedback($course->id);
                $time_ago = get_string('never');
                if (!empty($feedback)) {
                    $time_ago = format_time(time() - $feedback->timecreated);
                }

                $coursename = $course->fullname;
                $context = \context_course::instance($course->id);
                $toggle = has_capability('block/sibcms:monitoring', $context);
                if ($toggle) {
                    $showhide = sibcms_api::get_course_ignore($course->id) ? 'show' : 'hide';
                    $toggleurl = new \moodle_url('/blocks/sibcms/toggleignore.php',
                        array(
                            $showhide   => $course->id,
                            'sesskey'   => \sesskey(),
                            'returnurl' => $PAGE->url
                        )
                    );
                    $icon = $OUTPUT->pix_icon("t/$showhide", get_string($showhide), '', array('class' => 'iconsmall'));
                    $coursename .= '&nbsp;' . \html_writer::link($toggleurl, $icon);
                }

                $table->data[] = array(
                    \block_sibcms\sibcms_api::require_attention($course) ?
                        \html_writer::span('!', 'bold red text-center') : '',
                    $coursename,
                    $time_ago,
                    \html_writer::tag('a', get_string('key19', 'block_sibcms'),
                        array(
                            'href' => new \moodle_url('/blocks/sibcms/course.php', array(
                                'id'        => $course->id,
                                'category'  => $widget->category_id,
                                'returnurl' => $PAGE->url
                            ))
                        )
                    )
                );
                $row_class = '';
                if ($widget->last_feedback == $course->id) {
                    $row_class = 'block_sibcms_lastfeedback ';
                }
                if (\block_sibcms\sibcms_api::get_course_ignore($course->id) || !$course->visible) {
                    $row_class .= 'dimmed_text';
                }
                $table->rowclasses[] = $row_class;
            }
            $result .= \html_writer::table($table);
            $result .= $OUTPUT->paging_bar($widget->courses_count, $widget->page, 20,
                new \moodle_url('/blocks/sibcms/courses.php', array('category' => $widget->category_id)));
        } else {
            $result .= $OUTPUT->heading(get_string('key6', 'block_sibcms'));
        }
        $result .= \html_writer::end_div();
        return $result;
    }

    public function render_form_assigns_data_table(form_assigns_data_table $widget) {
        $table = new \html_table();
        $table->attributes['class'] = 'generaltable block_sibcms_formtable';
        $table->head = $widget->table_head;
        $table->size = $widget->table_size;
        $table->data = $widget->table_data;
        $table->rowclasses = $widget->table_classes;
        $table_str = \html_writer::table($table);
        return $table_str;
    }

    public function render_form_quiz_data_table(form_quiz_data_table $widget) {
        $table = new \html_table();
        $table->attributes['class'] = 'generaltable block_sibcms_formtable';
        $table->head = $widget->table_head;
        $table->size = $widget->table_size;
        $table->data = $widget->table_data;
        $table->rowclasses = $widget->table_classes;
        $table_str = \html_writer::table($table);
        return $table_str;
    }

    public function render_activity_assigns_data_table(activity_assigns_data_table $widget) {
        $table = new \html_table();
        $table->attributes['class'] = 'generaltable block_sibcms_reporttable';
        $table->head = $widget->table_head;
        $table->size = $widget->table_size;
        $table->data = $widget->table_data;
        $table->rowclasses = $widget->table_classes;
        $table_str = \html_writer::table($table);
        return $table_str;
    }

    public function render_activity_quiz_data_table(activity_quiz_data_table $widget) {
        $table = new \html_table();
        $table->attributes['class'] = 'generaltable block_sibcms_reporttable';
        $table->head = $widget->table_head;
        $table->size = $widget->table_size;
        $table->data = $widget->table_data;
        $table->rowclasses = $widget->table_classes;
        $table_str = \html_writer::table($table);
        return $table_str;
    }

    public function render_properties_table(properties_table $widget) {
        $table = new \html_table();
        $table->attributes['class'] = 'generaltable';
        $table->head = $widget->table_head;
        $table->size = $widget->table_size;
        $table->data = $widget->table_data;
        $table->rowclasses = $widget->table_classes;
        $table_str = \html_writer::table($table);
        return $table_str;
    }

    public function display_activity_report($course_id) {
        global $PAGE, $OUTPUT, $CFG;

        $result = \html_writer::start_div('', array('id' => 'block_sibcms'));

        $course = get_course($course_id);
        $result .= groups_print_course_menu($course, $PAGE->url, true);
        $group = groups_get_course_group($course);
        $course_data = sibcms_api::get_course_data($course, $group, false);

        $graders = $course_data->graders;
        if (count($graders) > 0) {
            $result .= $OUTPUT->heading(get_string('key28', 'block_sibcms') . ':', 3);

            $table = new \html_table();
            $table->attributes['class'] = 'generaltable block_sibcms_gradertable';
            $table->head = array(
                get_string('key66', 'block_sibcms'),
                get_string('key67', 'block_sibcms')
            );

            foreach ($graders as $grader) {
                $cells = array();

                $user_url = "$CFG->wwwroot/user/view.php?id=$grader->id&course=$course_id";
                $content = $OUTPUT->user_picture($grader, array('size' => 15)) . '&nbsp;' .\html_writer::link($user_url, fullname($grader));
                $cells[] = $cell = new \html_table_cell($content);

                $content = $grader->lastcourseaccess ?
                    userdate($grader->lastcourseaccess) . '&nbsp;(' . format_time(time() - $grader->lastcourseaccess) . ')' :
                    get_string('never');
                $cells[] = new \html_table_cell($content);

                $table->data[] = new \html_table_row($cells);
            }

            $result .= \html_writer::table($table);
        }

        $feedback = sibcms_api::get_last_course_feedback($course_data->id);
        if ($feedback && trim($feedback->feedback) != '') {
            $content = get_string('key29', 'block_sibcms') . '&nbsp;(' . userdate($feedback->timecreated, '%d %b %Y, %H:%M') . ')';
            $result .= $OUTPUT->heading($content . ':', 3);
            $result .= \html_writer::div($feedback->feedback);
        }

        if (count($course_data->assigns) > 0) {
            $result .= $OUTPUT->heading(get_string('key37', 'block_sibcms') . ':', 3);
            $assign_table = new activity_assigns_data_table($course_data);
            $result .= $this->render($assign_table);
        }

        if (count($course_data->quiz) > 0) {
            $result .= $OUTPUT->heading(get_string('key36', 'block_sibcms') . ':', 3);
            $quiz_table = new activity_quiz_data_table($course_data);
            $result .= $this->render($quiz_table);
        }

        $value = $course_data->result * 100;
        if ($value < 50) {
            $class = 'block_sibcms_red';
        } else if ($value < 85) {
            $class = 'block_sibcms_yellow';
        } else {
            $class = 'block_sibcms_green';
        }
        $content = \html_writer::start_span($class) . format_float($value, 2, true, true) . '%' . \html_writer::end_span();
        $result .= $OUTPUT->heading(get_string('key62', 'block_sibcms', $content), 3);

        $result .= \html_writer::end_div();

        return $result;
    }

    public function display_teacher_report($teacher_id) {
        global $PAGE, $OUTPUT, $CFG, $DB, $USER;
        $result = \html_writer::start_div('', array('id' => 'block_sibcms'));
        $teacher = $DB->get_record('user', array('id' => $teacher_id));
        $result .= \html_writer::div($teacher->lastname.' '. $teacher->firstname, 'teacher_fullname');

        $courses = enrol_get_users_courses($teacher_id, true, null, null);



        $table = new \html_table();
        $table->attributes['class'] = 'table block_sibcms_monitoringtable';
        $table->head = array(
            get_string('key27', 'block_sibcms'),
            get_string('key28', 'block_sibcms'),
            get_string('key29', 'block_sibcms'),
            ''
        );
        $table->size = array('40%', '30%', '30%', '25px');

        foreach ($courses as $course) {
            if (!$course->visible) continue;

            $ignore = sibcms_api::get_course_ignore($course->id);
            if (!$monitoring && $ignore) continue;

            $context = \context_course::instance($course->id);
            // $role->id = 3 - editingteacher
            if (user_has_role_assignment($teacher_id, 3, $context->id) != true) continue;
            
            $cells = array();
            $course_data = sibcms_api::get_course_data($course);

            $content = $OUTPUT->pix_icon('i/course', null, '', array('class' => 'icon')) . $course_data->fullname;
            if (has_capability('moodle/course:view', $context) || is_enrolled($context)) {
                $courseurl = "$CFG->wwwroot/course/view.php?id=$course_data->id";
                $content = \html_writer::link($courseurl, $content);
            }
            $content = $OUTPUT->heading($content, 4, 'block_sibcms_coursename');
            $cells[] = new \html_table_cell($content);

            $content = '';
            $graders = $course_data->graders;
            if (count($graders) > 0) {
                foreach ($graders as $grader) {
                    $userurl = "$CFG->wwwroot/user/view.php?id=$grader->id&course=$course_data->id";
                    $content .= \html_writer::link($userurl, fullname($grader)) . '&nbsp;';
                    $content .= $grader->lastcourseaccess ? '(' . format_time(time() - $grader->lastcourseaccess) . ')' : '(' . get_string('never') . ')';
                    $content .= '<br />';
                }
            } else $content = get_string('key50', 'block_sibcms');
            $cells[] = new \html_table_cell($content);
            
            $notices = array();
            $class = 'block_sibcms_lightgray';
            $feedback = sibcms_api::get_last_course_feedback($course_data->id);
            if ($feedback) {
                if (trim($feedback->feedback) != '') {
                    $comment = $feedback->feedback . '<br />';
                    $comment .= \html_writer::tag('i', get_string('key77', 'block_sibcms') . ':&nbsp;' . 
                        userdate($feedback->timecreated, '%d %b %Y, %H:%M'));
                    $notices[] = $comment;
                }
                $class = $feedback->result == 0 ? 'block_sibcms_lightgreen' : 'block_sibcms_lightred';
            } else {
                $notices[] = get_string('key76', 'block_sibcms');
            }
            $content = format_float($course_data->result * 100, 2, true, true) . '%';
            $content = get_string('key65', 'block_sibcms', $content);

            $notices[] = \html_writer::tag('b', $content);
            if (is_siteadmin()) {
                $returnurl = '/course/view.php?id='.$course->id.'&category='.\coursecat::get($course->category)->id;
                $content = \html_writer::link('course.php?id='. $course->id. '&category='.
                \coursecat::get($course->category)->id.'&returnurl='.$returnurl, 'Оценить');
                $notices[] = $content;
            } 
            $cells[] = \html_writer::alist($notices);

            $hints = sibcms_api::get_hints($course_data);
            $content = (count($hints) > 0 || (count($course_data->assigns) > 0) || count($course_data->quiz) > 0) ? // true
                \html_writer::div('', 'block_sibcms_showmore') : '';

            $cells[] = new \html_table_cell($content);

            $row = new \html_table_row($cells);
            if ($ignore) {
                $class .= ' dimmed_text';
            }
            $row->attributes['class'] = $class;
            $row->id = 'block_sibcms_' . $course_data->id;
            $table->data[] = $row;
            $cell = new \html_table_cell($content);
            $cell->attributes['class'] = 'block_sibcms_coursestats';
            $cell->colspan = 4;



            $table->data[] = new \html_table_row(array($cell));

        }
        $result .= \html_writer::table($table);

        $result .= \html_writer::end_div();

        return $result;
    }

    public function display_monitoring_report($course_id, $category_id) {
        global $PAGE, $OUTPUT, $CFG;

        $result = \html_writer::start_div('', array('id' => 'block_sibcms'));

        $categories = \coursecat::make_categories_list('block/sibcms:monitoring_report_category');
        if (count($categories) > 0) {
            $result .= \html_writer::start_div('block_sibcms_exportbtn');
            $params = array('id' => $course_id, 'category' => $category_id);
            $export_url = new \moodle_url($CFG->wwwroot . '/blocks/sibcms/export.php', $params);
            $result .= $OUTPUT->single_button($export_url, get_string('key64', 'block_sibcms'), 'get');
            $result .= \html_writer::end_div();

            $params['mode'] = 1;
            $export_url = new \moodle_url($CFG->wwwroot . '/blocks/sibcms/export.php', $params);
            $result .= $OUTPUT->single_button($export_url, get_string('key91', 'block_sibcms'), 'get');

            $label = $OUTPUT->container(get_string('categories') . ':', 'block_sibcms_coursecat_label');
            $select_url = new \moodle_url($CFG->wwwroot . '/blocks/sibcms/report.php', array('id' => $course_id));
            $select = $OUTPUT->single_select($select_url, 'category', $categories, $category_id);
            $result .= $OUTPUT->container($label . $select, 'block_sibcms_coursecat_select');

            $courses = $category_id > 0 ? \coursecat::get($category_id)->get_courses(array('recursive' => true)) : array();
            if (count($courses) > 0) {
                $table = new \html_table();
                $table->attributes['class'] = 'table block_sibcms_monitoringtable';
                $table->head = array(
                    get_string('key27', 'block_sibcms'),
                    get_string('key28', 'block_sibcms'),
                    get_string('key29', 'block_sibcms'),
                    ''
                );
                $table->size = array('40%', '30%', '30%', '25px');

                $monitoring = has_capability('block/sibcms:monitoring', \context_system::instance());
                foreach ($courses as $course) {
                    if (!$course->visible) continue;

                    $ignore = sibcms_api::get_course_ignore($course->id);
                    if (!$monitoring && $ignore) continue;

                    $cells = array();
                    $course_data = sibcms_api::get_course_data($course);

                    $content = $OUTPUT->pix_icon('i/course', null, '', array('class' => 'icon')) . $course_data->fullname;
                    $context = \context_course::instance($course->id);
                    if (has_capability('moodle/course:view', $context) || is_enrolled($context)) {
                        $courseurl = "$CFG->wwwroot/course/view.php?id=$course_data->id";
                        $content = \html_writer::link($courseurl, $content);
                    }
                    if ($monitoring) {
                        $showhide = $ignore ? 'show' : 'hide';
                        $toggleurl = new \moodle_url('/blocks/sibcms/toggleignore.php',
                            array(
                                $showhide   => $course->id,
                                'sesskey'   => \sesskey(),
                                'returnurl' => $PAGE->url . '#block_sibcms_' . $course->id
                            )
                        );
                        $icon = $OUTPUT->pix_icon("t/$showhide", get_string($showhide), '', array('class' => 'iconsmall'));
                        $content .= '&nbsp;' . \html_writer::link($toggleurl, $icon);
                    }
                    $content = $OUTPUT->heading($content, 4, 'block_sibcms_coursename');
                    $cells[] = new \html_table_cell($content);

                    $content = '';
                    $graders = $course_data->graders;
                    if (count($graders) > 0) {
                        foreach ($graders as $grader) {
                            $userurl = "$CFG->wwwroot/user/view.php?id=$grader->id&course=$course_data->id";
                            $content .= \html_writer::link($userurl, fullname($grader)) . '&nbsp;';
                            $content .= $grader->lastcourseaccess ? '(' . format_time(time() - $grader->lastcourseaccess) . ')' : '(' . get_string('never') . ')';
                            $content .= '<br />';
                        }
                    } else $content = get_string('key50', 'block_sibcms');
                    $cells[] = new \html_table_cell($content);
                    
                    $notices = array();
                    $class = 'block_sibcms_lightgray';
                    $feedback = sibcms_api::get_last_course_feedback($course_data->id);
                    if ($feedback) {
                        if (trim($feedback->feedback) != '') {
                            $comment = $feedback->feedback . '<br />';
                            $comment .= \html_writer::tag('i', get_string('key77', 'block_sibcms') . ':&nbsp;' . 
                                userdate($feedback->timecreated, '%d %b %Y, %H:%M'));
                            $notices[] = $comment;
                        }
                        $class = $feedback->result == 0 ? 'block_sibcms_lightgreen' : 'block_sibcms_lightred';
                    } else {
                        $notices[] = get_string('key76', 'block_sibcms');
                    }
                    $content = format_float($course_data->result * 100, 2, true, true) . '%';
                    $content = get_string('key65', 'block_sibcms', $content);
                    $notices[] = \html_writer::tag('b', $content);
                    if ($monitoring) {
                        $params = array('id' => $course->id, 'category' => $course->category, 'returnurl' => $PAGE->url . '#block_sibcms_' . $course->id);
                        $course_url = new \moodle_url("$CFG->wwwroot/blocks/sibcms/course.php", $params);
                        $content = \html_writer::link($course_url, get_string('key19', 'block_sibcms'));
                        $content .= '&nbsp;' . $OUTPUT->pix_icon('monitoring', '', 'block_sibcms', array('class' => 'iconsmall'));
                        $notices[] = $content;
                    }
                    $cells[] = \html_writer::alist($notices);

                    $hints = sibcms_api::get_hints($course_data);
                    $content = (count($hints) > 0 || (count($course_data->assigns) > 0) || count($course_data->quiz) > 0) ? // true
                        \html_writer::div('', 'block_sibcms_showmore') : '';
                    $cells[] = new \html_table_cell($content);

                    $row = new \html_table_row($cells);
                    if ($ignore) {
                        $class .= ' dimmed_text';
                    }
                    $row->attributes['class'] = $class;
                    $row->id = 'block_sibcms_' . $course_data->id;
                    $table->data[] = $row;

                    $content = \html_writer::start_div('block_sibcms_coursestats');
                    if (count($hints) > 0) {
                        $content .= \html_writer::div(get_string('key109', 'block_sibcms') . ':', 'block_sibcms_modheader');
                        $content .= \html_writer::alist($hints, array('class' => 'block_sibcms_hints'));
                    }
                    if (count($course_data->assigns) > 0) {
                        $content .= \html_writer::div(get_string('key37', 'block_sibcms') . ':', 'block_sibcms_modheader');
                        $assign_table = new activity_assigns_data_table($course_data);
                        $content .= $this->render($assign_table);
                    }
                    if (count($course_data->quiz) > 0) {
                        $content .= \html_writer::div(get_string('key36', 'block_sibcms') . ':', 'block_sibcms_modheader');
                        $quiz_table = new activity_quiz_data_table($course_data);
                        $content .= $this->render($quiz_table);
                    }
                    $content .= \html_writer::end_div();
                    $cell = new \html_table_cell($content);
                    $cell->attributes['class'] = 'block_sibcms_coursestats';
                    $cell->colspan = 4;

                    $table->data[] = new \html_table_row(array($cell));

                    $cell = new \html_table_cell('');
                    $cell->attributes['class'] = 'block_sibcms_separator';
                    $cell->colspan = 4;

                    $table->data[] = new \html_table_row(array($cell));
                }

                $result .= \html_writer::table($table);
            }
        }

        $result .= \html_writer::end_div();

        return $result;
    }

}
