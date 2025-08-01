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
 * Version information for the calculated question type.
 *
 * @package    qformat_glossary
 * @copyright  2016 Daniel Thies <dethies@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'qformat_glossary';
$plugin->version   = 2016072808;
$plugin->release   = '1.0.3';

$plugin->requires  = 2018051400;

$plugin->maturity  = MATURITY_STABLE;

$plugin->dependencies = [
    'mod_glossary' => 2013110500,
    'qformat_xml' => 2013110500,
];
