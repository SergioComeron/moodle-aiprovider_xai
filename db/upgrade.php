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
 * Upgrade steps for aiprovider_xai.
 *
 * @package    aiprovider_xai
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade the plugin.
 *
 * @param int $oldversion The old version of the plugin.
 * @return bool
 */
function xmldb_aiprovider_xai_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2026110200) {
        $records = $DB->get_records('ai_providers', ['provider' => 'aiprovider_xai\provider']);
        foreach ($records as $record) {
            if ($record->actionconfig === null || $record->actionconfig === '') {
                continue;
            }
            $actionconfig = json_decode($record->actionconfig, true);
            if (!is_array($actionconfig)) {
                continue;
            }
            $updatedconfig = \aiprovider_xai\system_instruction::rewrite_actionconfig($actionconfig);
            if ($updatedconfig === $actionconfig) {
                continue;
            }
            $updated = new \stdClass();
            $updated->id = $record->id;
            $updated->actionconfig = json_encode($updatedconfig);
            $DB->update_record('ai_providers', $updated);
        }

        upgrade_plugin_savepoint(true, 2026110200, 'aiprovider', 'xai');
    }

    if ($oldversion < 2026110201) {
        $records = $DB->get_records('ai_providers', ['provider' => 'aiprovider_xai\provider']);
        foreach ($records as $record) {
            if ($record->actionconfig === null || $record->actionconfig === '') {
                continue;
            }
            $actionconfig = json_decode($record->actionconfig, true);
            if (!is_array($actionconfig)) {
                continue;
            }
            $updatedconfig = \aiprovider_xai\system_instruction::rewrite_actionconfig($actionconfig);
            $updatedconfig = \aiprovider_xai\model_migration::rewrite_actionconfig($updatedconfig);
            if ($updatedconfig === $actionconfig) {
                continue;
            }
            $updated = new \stdClass();
            $updated->id = $record->id;
            $updated->actionconfig = json_encode($updatedconfig);
            $DB->update_record('ai_providers', $updated);
        }

        upgrade_plugin_savepoint(true, 2026110201, 'aiprovider', 'xai');
    }

    if ($oldversion < 2026110202) {
        // Retired presets now land on grok-4.5. grok-4.7 and grok-4.6 stay if already chosen.
        $records = $DB->get_records('ai_providers', ['provider' => 'aiprovider_xai\provider']);
        foreach ($records as $record) {
            if ($record->actionconfig === null || $record->actionconfig === '') {
                continue;
            }
            $actionconfig = json_decode($record->actionconfig, true);
            if (!is_array($actionconfig)) {
                continue;
            }
            $updatedconfig = \aiprovider_xai\model_migration::rewrite_actionconfig($actionconfig);
            if ($updatedconfig === $actionconfig) {
                continue;
            }
            $updated = new \stdClass();
            $updated->id = $record->id;
            $updated->actionconfig = json_encode($updatedconfig);
            $DB->update_record('ai_providers', $updated);
        }

        upgrade_plugin_savepoint(true, 2026110202, 'aiprovider', 'xai');
    }

    if ($oldversion < 2026110203) {
        // Completion tokens now include reasoning tokens. No stored data to rewrite.
        upgrade_plugin_savepoint(true, 2026110203, 'aiprovider', 'xai');
    }

    return true;
}
