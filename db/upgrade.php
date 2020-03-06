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

defined('MOODLE_INTERNAL') || die();

function xmldb_block_sibcms_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2017111219) {
        $table = new xmldb_table('block_sibcms_visibility');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL);
        $table->add_field('moduleid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL);
        $table->add_field('visible', XMLDB_TYPE_INTEGER, '1');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('foreign1', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));
        $table->add_key('foreign2', XMLDB_KEY_FOREIGN_UNIQUE, array('moduleid'), 'course_modules', array('id'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }

    if ($oldversion < 2017111915) {
        $table = new xmldb_table('block_sibcms_ignore_courses');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL);
        $table->add_field('ignoring', XMLDB_TYPE_INTEGER, '1');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('courseid', XMLDB_KEY_FOREIGN, array('courseid'), 'course', array('id'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }

    if ($oldversion < 2017120100) {
        $sql = 'UPDATE {block_sibcms_feedbacks}
                   SET result = 1
                 WHERE result <> 0';
        $DB->execute($sql);

        $table1 = new xmldb_table('block_sibcms_properties');
        $table1->add_field('id', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table1->add_field('name', XMLDB_TYPE_CHAR, '1000', null, XMLDB_NOTNULL);
        $table1->add_field('hidden', XMLDB_TYPE_INTEGER, '1');
        $table1->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        if (!$dbman->table_exists($table1)) {
            $dbman->create_table($table1);
        }

        $table2 = new xmldb_table('block_sibcms_feedback_props');
        $table2->add_field('id', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table2->add_field('feedbackid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL);
        $table2->add_field('propertyid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL);
        $table2->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table2->add_key('foreign1', XMLDB_KEY_FOREIGN, array('feedbackid'), 'block_sibcms_feedbacks', array('id'));
        $table2->add_key('foreign2', XMLDB_KEY_FOREIGN, array('propertyid'), 'block_sibcms_properties', array('id'));
        $table2->add_key('unique1', XMLDB_KEY_UNIQUE, array('feedbackid', 'propertyid'));
        if (!$dbman->table_exists($table2)) {
            $dbman->create_table($table2);
        }

    }

    return true;
}
