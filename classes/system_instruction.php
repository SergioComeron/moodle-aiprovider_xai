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

use core_ai\aiactions\base;

/**
 * Plugin system instructions for text actions.
 *
 * Core generate_text and explain_text tell the model to return "the summary".
 * This plugin stores its own defaults. An instruction that still matches the
 * unmodified core string is replaced; anything an admin has edited is kept.
 *
 * @package    aiprovider_xai
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class system_instruction {
    /**
     * Plugin default system instruction for an action.
     *
     * @param string $actionclass Action class name.
     * @return string
     */
    public static function for_action(string $actionclass): string {
        $actionname = substr($actionclass, (strrpos($actionclass, '\\') + 1));
        $identifier = "action:{$actionname}:systeminstruction_default";
        if (get_string_manager()->string_exists($identifier, 'aiprovider_xai')) {
            return get_string($identifier, 'aiprovider_xai');
        }
        if (is_a($actionclass, base::class, true)) {
            return $actionclass::get_system_instruction();
        }
        return '';
    }

    /**
     * Replace an unmodified core instruction with the plugin default.
     *
     * An empty value is left empty so an admin can clear the instruction.
     * Any other text that is not the core default is returned unchanged.
     *
     * @param string $actionclass Action class name.
     * @param string $stored Instruction saved on the provider.
     * @return string
     */
    public static function normalise(string $actionclass, string $stored): string {
        if ($stored === '') {
            return '';
        }
        if (in_array($stored, self::core_defaults($actionclass), true)) {
            return self::for_action($actionclass);
        }
        return $stored;
    }

    /**
     * Rewrite stored action settings that still use a core instruction.
     *
     * @param array $actionconfig Decoded provider action config.
     * @return array
     */
    public static function rewrite_actionconfig(array $actionconfig): array {
        $actions = [
            \core_ai\aiactions\generate_text::class,
            \core_ai\aiactions\summarise_text::class,
            \core_ai\aiactions\explain_text::class,
        ];
        foreach ($actions as $actionclass) {
            if (!isset($actionconfig[$actionclass]['settings']['systeminstruction'])) {
                continue;
            }
            $current = $actionconfig[$actionclass]['settings']['systeminstruction'];
            if (!is_string($current)) {
                continue;
            }
            $actionconfig[$actionclass]['settings']['systeminstruction'] = self::normalise($actionclass, $current);
        }
        return $actionconfig;
    }

    /**
     * Unmodified core instructions in the current language and in English.
     *
     * @param string $actionclass Action class name.
     * @return array
     */
    public static function core_defaults(string $actionclass): array {
        if (!is_a($actionclass, base::class, true)) {
            return [];
        }
        $defaults = [$actionclass::get_system_instruction()];
        $currentlang = current_language();
        if ($currentlang !== 'en') {
            force_current_language('en');
            try {
                $defaults[] = $actionclass::get_system_instruction();
            } finally {
                force_current_language($currentlang);
            }
        }
        return array_values(array_unique($defaults));
    }
}
