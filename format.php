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
 * Code for exporting questions as Moodle XML.
 *
 * @package    qformat_glossary
 * @copyright  2016 Daniel Thies <dthies@ccal.edu>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/glossary/lib.php');
require_once($CFG->dirroot . '/question/format/xml/format.php');

/**
 * Question Import for Moodle XML glossary format.
 *
 * @copyright  2016 Daniel Thies <dthies@ccal.edu>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qformat_glossary extends qformat_xml {

    // Overwrite export methods.
    public function writequestion($question) {
        global $CFG;
        $expout = '';
        if ($question->qtype == 'shortanswer' || $question->qtype == 'multichoice') {
            $expout .= glossary_start_tag("ENTRY",3,true);
            $answers = $question->options->answers;
            reset($answers);
            while (current($answers)) {
                if (current($answers)->fraction == 1) {
                    $expout .= glossary_full_tag("CONCEPT",4,false, trim(current($answers)->answer));
                    next($answers);
                    break;
                }
                next($answers);
            }

            $expout .= glossary_full_tag("DEFINITION",4,false,$question->questiontext);
            $expout .= glossary_full_tag("FORMAT",4,false, $question->questiontextformat);

            $expout .= glossary_start_tag("ALIASES",4,true);
            while (current($answers)) {
                if (current($answers)->fraction == 1) {
                    $expout .= glossary_start_tag("ALIAS",5,true);
                    $expout .= glossary_full_tag("NAME",6,false,trim(current($answers)->answer));
                    $expout .= glossary_end_tag("ALIAS",5,true);

                }
                next($answers);
            }
            $expout .= glossary_end_tag("ALIASES",4,true);

            $expout .= glossary_full_tag("USEDYNALINK",4,false,get_config('core', 'glossary_linkentries'));
            $expout .= glossary_full_tag("CASESENSITIVE",4,false,get_config('core', 'glossary_casesensitive'));
            $expout .= glossary_full_tag("FULLMATCH",4,false,get_config('core', 'glossary_fullmatch'));


            $expout .= glossary_end_tag("ENTRY",3,true);

        }
        return $expout;
    }

    protected function presave_process($content) {
        // Override to add xml headers and footers and the global glossary settings.
        $co  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

        $co .= glossary_start_tag("GLOSSARY",0,true);
        $co .= glossary_start_tag("INFO",1,true);

        $co .= glossary_start_tag("ENTRIES",2,true);

        $co .= $content;

        $co .= glossary_end_tag("ENTRIES",2,true);

        $co .= glossary_end_tag("INFO",1,true);
        $co .= glossary_end_tag("GLOSSARY",0,true);
        return $co;
    }

    // Overwrite import methods.
    protected function readquestions($lines) {
        $questions = array();

        // We just need it as one big string.
        $lines = implode('', $lines);

        // Large exports are likely to take their time and memory.
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_EXTRA);

        global $CFG;
        require_once $CFG->libdir . "/xmlize.php";

        $xml = xmlize($lines, 0);

        if ($xml) {
            $xmlentries = $xml['GLOSSARY']['#']['INFO'][0]['#']['ENTRIES'][0]['#']['ENTRY'];
            $sizeofxmlentries = sizeof($xmlentries);

            // Iterate through glossary entries.
            for($i = 0; $i < $sizeofxmlentries; $i++) {
                // Extract entry information.
                $xmlentry = $xmlentries[$i];
                $concept = trim($xmlentry['#']['CONCEPT'][0]['#']);
                $definition = trusttext_strip($xmlentry['#']['DEFINITION'][0]['#']);
                $format = trusttext_strip($xmlentry['#']['FORMAT'][0]['#']);

                // Create short answer question object from entry data.
                $qo = $this->defaultquestion();
                $qo->qtype = 'shortanswer';
                $qo->questiontextformat = $format;
                $qo->questiontext = $definition;
                $qo->name = $definition;
                $qo->answer[0] = $concept;
                $qo->fraction[0] = 1;
                $qo->feedback[0] = array();
                $qo->feedback[0]['text'] = '';
                $qo->feedback[0]['format'] = FORMAT_PLAIN;

                $xmlaliases = @$xmlentry['#']['ALIASES'][0]['#']['ALIAS']; // ignore missing ALIASES
                $sizeofxmlaliases = sizeof($xmlaliases);
                for($k = 0; $k < $sizeofxmlaliases; $k++) {
                    $xmlalias = $xmlaliases[$k];
                    $aliasname = $xmlalias['#']['NAME'][0]['#'];
                    $qo->answer[$k +1] = $aliasname;
                    $qo->fraction[$k + 1] = 1;
                    $qo->feedback[$k + 1] = array();
                    $qo->feedback[$k + 1]['text'] = '';
                    $qo->feedback[$k + 1]['format'] = FORMAT_PLAIN;
                }


                $questions[] = $qo;
            }

        }
        return $questions;
    }

}

