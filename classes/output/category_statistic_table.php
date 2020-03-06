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

class category_statistic_table implements \renderable
{
    public $category_id;
    public $categories;

    public function __construct($category_id)
    {
        $this->category_id = $category_id;
        $parent_category = \coursecat::get($category_id);
        $categories = $parent_category->get_children();
        $this->categories = array();
        foreach ($categories as $category) {
            $cat = new \stdClass();
            $cat->id = $category->id;
            $cat->name = $category->name;
            $courses = $category->get_courses(array('recursive' => true));
            $cat->courses_total = count($courses);
            $cat->courses_require_attention = 0;
            foreach ($courses as $course) {
                if (\block_sibcms\sibcms_api::require_attention($course)) {
                    $cat->courses_require_attention++;
                }
            }
            $cat->has_subcategories = $category->has_children();
            $this->categories[] = $cat;
        }
    }

}
