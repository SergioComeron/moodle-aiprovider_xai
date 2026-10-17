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
 * Grok 4.5 text model.
 *
 * Previous flagship still listed on the xAI pricing page. Same chat-completion
 * settings as Grok 4.7. Kept as a predefined choice so sites can pin it.
 *
 * @package    aiprovider_xai
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grok45 extends grok47 {
    #[\Override]
    public function get_model_name(): string {
        return 'grok-4.5';
    }

    #[\Override]
    public function get_model_display_name(): string {
        return 'Grok 4.5';
    }
}
