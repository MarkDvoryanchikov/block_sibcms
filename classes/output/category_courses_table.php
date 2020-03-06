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

class category_courses_table implements \renderable
{
    public $courses;
    public $page;
    public $courses_count;
    public $category_id;
    public $last_feedback;

    public function __construct($category_id, $page, $last_feedback)
    {
        $category = \coursecat::get($category_id);
        $this->category_id = $category_id;
        $this->page = $page;
        $courses_count = $category->get_courses_count(array('recursive' => true));
        $this->courses_count = $courses_count;
        $this->courses = $category->get_courses(array('recursive' => true, 'offset' => $page * 20, 'limit' => 20));
        $this->last_feedback = $last_feedback;
    }

}
