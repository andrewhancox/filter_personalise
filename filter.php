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
 * @package    filter_personalise
 * @copyright  2015 onwards Andrew Hancox (andrewdchancox@googlemail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Activity name filtering
 */
class filter_personalise extends moodle_text_filter {
    /**
     * @param $text
     * @param $element
     * @return mixed
     */
    private function dosubstitutions($text, $element) {
        $matches = array();
        $prefix = "personalise_{$element}_";
        preg_match_all("/{$prefix}[a-z]+/", $text, $matches);

        foreach ($matches as $matchlist) {
            foreach ($matchlist as $match) {
                if (empty($match)) {
                    continue;
                }

                $fieldname = substr($match, strlen($prefix));

                $methodname = "resolve{$element}field";
                $resolved = $this->$methodname($fieldname);

                $text = str_replace($match, $resolved, $text);
            }
        }
        return $text;
    }

    private function resolveuserfield($fieldname) {
        global $USER;

        static $alloweduserfields = array(
            'username',
            'idnumber',
            'firstname',
            'lastname',
            'email',
            'icq',
            'skype',
            'yahoo',
            'aim',
            'msn',
            'phone1',
            'phone2',
            'institution',
            'department',
            'address',
            'city',
            'country',
            'lastnamephonetic',
            'firstnamephonetic',
            'middlename',
            'alternatename',
            'fullname');

        if ($this->useguestrole()) {
            return $this->getguestrolename();
        }

        if ($fieldname == 'fullname') {
            return fullname($USER);
        }

        if (in_array($fieldname, $alloweduserfields)) {
            return $USER->$fieldname;
        }
    }

    private function useguestrole() {
        static $useguestrole = null;

        if ($useguestrole === null) {
            $useguestrole = isguestuser() || !isloggedin();
        }

        return $useguestrole;
    }

    private function getguestrolename() {
        static $guestrolename = null;

        if ($guestrolename === null) {
            $guestrole = get_guest_role();
            $guestrolename = empty($guestrole->name) ? $guestrole->shortname : $guestrole->name;
        }

        return $guestrolename;
    }

    private function resolvecoursefield($fieldname) {
        global $COURSE;

        static $allowedcoursefields = array(
            'fullname',
            'shortname'
        );

        if (in_array($fieldname, $allowedcoursefields)) {
            return $COURSE->$fieldname;
        }
    }

    public function filter($text, array $options = array()) {
        $text = $this->dosubstitutions($text, 'user');
        $text = $this->dosubstitutions($text, 'course');

        return $text;
    }
}