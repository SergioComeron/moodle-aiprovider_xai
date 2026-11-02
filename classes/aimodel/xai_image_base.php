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
 * xAI image generation model interface.
 *
 * @package    aiprovider_xai
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface xai_image_base {
    /**
     * Get response format.
     *
     * xAI hosted URLs are temporary, so image models return b64_json.
     * Return null to omit the parameter.
     *
     * @return string|null response format, or null to omit the parameter from the request.
     */
    public function response_format(): ?string;

    /**
     * Convert the given aspect ratio to an aspect_ratio compatible with the model's API.
     *
     * @param string $ratio The aspect ratio (square, landscape, portrait).
     * @return string The aspect_ratio string for the API request.
     */
    public function calculate_aspect_ratio(string $ratio): string;

    /**
     * Convert the given quality setting to the value expected by the model's API.
     *
     * Return null when the model does not accept quality.
     *
     * @param string $quality The quality setting (standard, hd).
     * @return string|null The quality value for the API request, or null to omit it.
     */
    public function calculate_quality(string $quality): ?string;
}
