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

namespace aiprovider_xai;

/**
 * Long system instructions shipped before they were shortened.
 *
 * Kept so an upgrade can replace an unchanged copy. Edited text does not match.
 *
 * @package    aiprovider_xai
 * @copyright  2026 Sergio Comerón <sergiocomeron@icloud.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class previous_system_instruction {
    /**
     * Previous long defaults, keyed by language then action name.
     *
     * @return array
     */
    public static function all(): array {
        static $all = null;
        if ($all === null) {
            $json = file_get_contents(__DIR__ . '/../db/previous_system_instructions.json');
            $all = json_decode($json, true);
        }
        return $all;
    }
}
