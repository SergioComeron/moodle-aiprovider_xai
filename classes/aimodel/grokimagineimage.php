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

/**
 * grok-imagine-image model.
 *
 * Imagine 1.0. Still served and lower priced (docs.x.ai, checked 2 November 2026).
 * The 2 November 2026 retirement of grok-imagine-image-quality does not apply here.
 * The quality parameter is documented only for grok-imagine-image-2.0, so this
 * model omits it. Aspect ratio, resolution and base64 output are unchanged.
 *
 * @package    aiprovider_xai
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grokimagineimage extends grokimagineimage2 {
    #[\Override]
    public function get_model_name(): string {
        return 'grok-imagine-image';
    }

    #[\Override]
    public function get_model_display_name(): string {
        return 'Grok Imagine Image';
    }

    #[\Override]
    public function calculate_quality(string $quality): ?string {
        return null;
    }
}
