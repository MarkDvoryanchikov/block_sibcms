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

defined('MOODLE_INTERNAL') || die();

class block_sibcms extends block_list
{

    function init()
    {
        $this->title = get_string('pluginname', 'block_sibcms');
    }

    function instance_allow_multiple()
    {
        return false;
    }

    function instance_allow_config()
    {
        return false;
    }

    function has_config()
    {
        return true;
    }

    function get_content()
    {
        global $CFG, $OUTPUT;

        $monitoring = has_capability('block/sibcms:monitoring', context_system::instance());
        if (!isloggedin() || !$monitoring) {
            $this->content = '';
            return $this->content;
        }

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        $this->content->items[] = html_writer::tag('a', get_string('key1', 'block_sibcms'), array('href' => $CFG->wwwroot . '/blocks/sibcms/category.php'));
        $this->content->icons[] = $OUTPUT->pix_icon('monitoring', '', 'block_sibcms', array('class' => 'iconsmall'));

        return $this->content;
    }

}
