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

namespace aiprovider_xai\aimodel;

use core_ai\aimodel\base;
use MoodleQuickForm;

/**
 * Grok 4.7 text model.
 *
 * Flagship chat model on the xAI API (docs.x.ai, checked 2 November 2026).
 * Selectable, not the default: the same short prompts cost more here than on
 * grok-4.5 because this model spends more reasoning tokens.
 * grok-4.3 is not a preset; enter it in the custom model field.
 * Chat completions accept temperature, top_p and max_completion_tokens.
 * frequency_penalty and presence_penalty are not offered here: the API does not
 * support them on reasoning models, and Grok 4.7 is a reasoning model.
 *
 * @package    aiprovider_xai
 * @copyright  2026 Sergio Comerón <sergiocomeron@icloud.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grok47 extends base implements xai_base {
    #[\Override]
    public function get_model_name(): string {
        return 'grok-4.7';
    }

    #[\Override]
    public function get_model_display_name(): string {
        return 'Grok 4.7';
    }

    #[\Override]
    public function get_model_settings(): array {
        return [
            'temperature' => [
                'elementtype' => 'text',
                'label' => [
                    'identifier' => 'settings_temperature',
                    'component' => 'aiprovider_xai',
                ],
                'type' => PARAM_FLOAT,
                'help' => [
                    'identifier' => 'settings_temperature',
                    'component' => 'aiprovider_xai',
                ],
            ],
            'top_p' => [
                'elementtype' => 'text',
                'label' => [
                    'identifier' => 'settings_top_p',
                    'component' => 'aiprovider_xai',
                ],
                'type' => PARAM_FLOAT,
                'help' => [
                    'identifier' => 'settings_top_p',
                    'component' => 'aiprovider_xai',
                ],
            ],
            'max_completion_tokens' => [
                'elementtype' => 'text',
                'label' => [
                    'identifier' => 'settings_max_completion_tokens',
                    'component' => 'aiprovider_xai',
                ],
                'type' => PARAM_INT,
                'help' => [
                    'identifier' => 'settings_max_completion_tokens',
                    'component' => 'aiprovider_xai',
                ],
            ],
        ];
    }

    #[\Override]
    public function add_model_settings(MoodleQuickForm $mform): void {
        $settings = $this->get_model_settings();
        foreach ($settings as $key => $setting) {
            $mform->addElement(
                $setting['elementtype'],
                $key,
                get_string($setting['label']['identifier'], $setting['label']['component']),
            );
            $mform->setType($key, $setting['type']);
            if (isset($setting['help'])) {
                $mform->addHelpButton($key, $setting['help']['identifier'], $setting['help']['component']);
            }
        }
    }

    #[\Override]
    public function model_type(): array {
        return [self::MODEL_TYPE_TEXT];
    }
}
