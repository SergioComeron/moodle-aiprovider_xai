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
 * Map retired preset model ids onto the current defaults.
 *
 * Ids that were never a preset are left unchanged.
 *
 * @package    aiprovider_xai
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class model_migration {
    /** @var string Current default for text actions. */
    public const TEXT_DEFAULT = 'grok-4.7';

    /** @var string Current default for image actions. */
    public const IMAGE_DEFAULT = 'grok-imagine-image-2.0';

    /**
     * Preset ids this plugin no longer offers.
     *
     * @var string[]
     */
    public const RETIRED = [
        'grok-4.3',
        'grok-imagine-image-quality',
    ];

    /**
     * Whether this id was a preset and has been removed.
     *
     * @param string $model Model id.
     * @return bool
     */
    public static function is_retired(string $model): bool {
        return in_array($model, self::RETIRED, true);
    }

    /**
     * Default model id for an action name such as generate_text.
     *
     * @param string $actionname Action name.
     * @return string
     */
    public static function replacement(string $actionname): string {
        if ($actionname === 'generate_image') {
            return self::IMAGE_DEFAULT;
        }
        return self::TEXT_DEFAULT;
    }

    /**
     * Replace a retired preset id. Any other id is returned unchanged.
     *
     * @param string $actionname Action name.
     * @param string $model Model id.
     * @return string
     */
    public static function migrate(string $actionname, string $model): string {
        if (!self::is_retired($model)) {
            return $model;
        }
        return self::replacement($actionname);
    }

    /**
     * Rewrite stored provider action config.
     *
     * Retired preset ids move to the current default for that action.
     * Per-model settings follow the id. A custom id is not changed.
     *
     * @param array $actionconfig Decoded provider action config.
     * @return array
     */
    public static function rewrite_actionconfig(array $actionconfig): array {
        foreach ($actionconfig as $actionclass => $action) {
            if (!is_string($actionclass) || !is_array($action)) {
                continue;
            }
            $actionname = substr($actionclass, (strrpos($actionclass, '\\') + 1));
            $model = $action['settings']['model'] ?? null;
            if (!is_string($model) || !self::is_retired($model)) {
                continue;
            }
            $replacement = self::replacement($actionname);
            $actionconfig[$actionclass]['settings']['model'] = $replacement;
            unset($actionconfig[$actionclass]['settings']['modelextraparams']);

            if (!isset($action['modelsettings']) || !is_array($action['modelsettings'])) {
                continue;
            }
            if (isset($action['modelsettings'][$model]) && !isset($action['modelsettings'][$replacement])) {
                $actionconfig[$actionclass]['modelsettings'][$replacement] = $action['modelsettings'][$model];
            }
            unset($actionconfig[$actionclass]['modelsettings'][$model]);
        }
        return $actionconfig;
    }
}
