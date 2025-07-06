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

namespace qformat_glossary;

defined('MOODLE_INTERNAL') || die();

use core_question\local\bank\question_edit_contexts;
use context_course;

global $CFG;
require_once($CFG->dirroot . '/mod/glossary/tests/generator/lib.php');
require_once($CFG->dirroot . '/question/format/glossary/format.php');
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/format.php');
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
require_once($CFG->dirroot . '/question/editlib.php');


/**
 * Provides the unit tests for glossary search.
 *
 * @package     qformat_glossary
 * @category    test
 * @copyright   2025 Daniel Thies <dethies@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \qformat_glossary
 * @group       qformat_glossary
 */
final class glossaryxmlformat_test extends \advanced_testcase {
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
    }

    /**
     * Create object qformat_glossary for test.
     * @param string $filename with name for testing file.
     * @param stdClass $course
     * @return qformat_glossary Moodle Glossary XML question format object.
     */
    public function create_qformat($filename, $course) {
        $qformat = new \qformat_glossary();
        $qformat->setContexts((new question_edit_contexts(context_course::instance($course->id)))->all());
        $qformat->setCourse($course);
        $qformat->setFilename(__DIR__ . '/fixtures/' . $filename);
        $qformat->setRealfilename($filename);
        $qformat->setMatchgrades('error');
        $qformat->setCatfromfile(1);
        $qformat->setContextfromfile(1);
        $qformat->setStoponerror(1);
        $qformat->setCattofile(1);
        $qformat->setContexttofile(1);
        $qformat->set_display_progress(false);

        return $qformat;
    }

    /**
     * Export glossary into question.
     *
     * @return void
     */
    public function test_glossary_import_glossary_export(): void {

        global $DB;

        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();

        $course1 = self::getDataGenerator()->create_course();
        $course2 = self::getDataGenerator()->create_course();

        $this->getDataGenerator()->enrol_user($user1->id, $course1->id, 'student');
        $this->getDataGenerator()->enrol_user($user2->id, $course1->id, 'student');

        $record = new \stdClass();
        $record->course = $course1->id;

        $this->setUser($user1);

        // Approved entries by default glossary.
        $glossary1 = self::getDataGenerator()->create_module('glossary', $record);
        $entry1 = self::getDataGenerator()->get_plugin_generator('mod_glossary')->create_content($glossary1);
        $entry2 = self::getDataGenerator()->get_plugin_generator('mod_glossary')->create_content($glossary1);

        $content = glossary_generate_export_file($glossary1);
        $qformat = $this->create_qformat('filename', $course1);
        $questions = $qformat->readquestions([$content]);
        $this->assertCount(2, $questions);
    }

    /**
     * Impopt glossary file into question bank.
     *
     * @return void
     */
    public function test_glossary_import_glossary_file(): void {

        global $DB;

        $user1 = self::getDataGenerator()->create_user();
        $user2 = self::getDataGenerator()->create_user();

        $course1 = self::getDataGenerator()->create_course();
        $course2 = self::getDataGenerator()->create_course();
        $this->setAdminUser();

        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $category = $generator->create_question_category([
                'name' => 'Alpha',
                'contextid' => context_course::instance($course1->id)->id,
                'info' => 'This is Alpha category for test',
                'infoformat' => '0',
                'idnumber' => 'The inequalities < & >',
                'stamp' => make_unique_id_code(),
                'parent' => '0',
                'sortorder' => '999']);

        $qformat = $this->create_qformat('Glossary.xml', $course1);
        $qformat->setCategory($category);
        $questions = $qformat->importprocess();
        $this->assertCount(1, $DB->get_records('question'));
    }
}
