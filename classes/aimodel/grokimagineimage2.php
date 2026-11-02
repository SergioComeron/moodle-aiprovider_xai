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

use aiprovider_xai\process_generate_image;
use core_ai\aimodel\base;
use MoodleQuickForm;

/**
 * grok-imagine-image-2.0 image model.
 *
 * Current Imagine image model and the default (docs.x.ai, checked 2 November 2026).
 * Accepts aspect_ratio, resolution (1k or 2k) and quality (low, medium, auto).
 * This class maps Moodle's two quality values onto low and medium.
 * grok-imagine-image-quality was retired on 2 November 2026 and is not offered;
 * requests to that slug are served by this model at quality low.
 * Resolution is a model setting because generate_image has no resolution input;
 * the API default when it is omitted is 1k.
 *
 * @package    aiprovider_xai
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grokimagineimage2 extends base implements xai_base, xai_image_base {
    #[\Override]
    public function get_model_name(): string {
        return 'grok-imagine-image-2.0';
    }

    #[\Override]
    public function get_model_display_name(): string {
        return 'Grok Imagine Image 2.0';
    }

    #[\Override]
    public function model_type(): array {
        return [self::MODEL_TYPE_IMAGE];
    }

    #[\Override]
    public function response_format(): ?string {
        return 'b64_json';
    }

    #[\Override]
    public function calculate_aspect_ratio(string $ratio): string {
        return process_generate_image::map_aspect_ratio($ratio);
    }

    #[\Override]
    public function calculate_quality(string $quality): ?string {
        return process_generate_image::map_quality($quality);
    }

    #[\Override]
    public function get_model_settings(): array {
        return [
            'resolution' => [
                'elementtype' => 'select',
                'options' => [
                    '1k' => get_string('settings_resolution_1k', 'aiprovider_xai'),
                    '2k' => get_string('settings_resolution_2k', 'aiprovider_xai'),
                ],
                'default' => '1k',
                'label' => [
                    'identifier' => 'settings_resolution',
                    'component' => 'aiprovider_xai',
                ],
                'type' => PARAM_TEXT,
                'help' => [
                    'identifier' => 'settings_resolution',
                    'component' => 'aiprovider_xai',
                ],
            ],
        ];
    }

    #[\Override]
    public function add_model_settings(MoodleQuickForm $mform): void {
        $settings = $this->get_model_settings();
        foreach ($settings as $key => $setting) {
            if ($setting['elementtype'] === 'select') {
                $mform->addElement(
                    'select',
                    $key,
                    get_string($setting['label']['identifier'], $setting['label']['component']),
                    $setting['options'],
                );
            } else {
                $mform->addElement(
                    $setting['elementtype'],
                    $key,
                    get_string($setting['label']['identifier'], $setting['label']['component']),
                );
            }
            $mform->setType($key, $setting['type']);
            if (isset($setting['default'])) {
                $mform->setDefault($key, $setting['default']);
            }
            if (isset($setting['help'])) {
                $mform->addHelpButton($key, $setting['help']['identifier'], $setting['help']['component']);
            }
        }
    }
}
