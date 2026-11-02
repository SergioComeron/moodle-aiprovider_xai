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
 * Still served as of 2 November 2026 (docs.x.ai). Same short-context token
 * price as Grok 4.7, with a cheaper cached-input rate. Predefined so a site
 * can pin it. Same accepted sampling settings as Grok 4.7.
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
