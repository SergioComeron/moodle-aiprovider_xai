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

use aiprovider_xai\aimodel\xai_image_base;
use core_ai\ai_image;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class process image generation.
 *
 * Moodle generate_image inputs are mapped onto POST /v1/images/generations:
 * - prompttext -> prompt
 * - numimages -> n, clamped to 1..10 (the xAI limit). The core response object
 *   stores a single draft file, so only data[0] is kept.
 * - aspectratio square|landscape|portrait -> aspect_ratio 1:1|16:9|9:16
 * - quality standard|hd -> quality low|medium on models that accept it.
 *   xAI has no "high". "auto" is not selected here; placements only offer the two Moodle values.
 * - style is not sent. The Imagine generations API has no style parameter.
 * - resolution (1k|2k) is not an action input. It is a per-model setting and defaults to 1k when omitted.
 * - response_format is always b64_json. Returned URLs are temporary.
 *
 * @package    aiprovider_xai
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class process_generate_image extends abstract_processor {
    /** @var int Maximum images accepted by the xAI generations API. */
    private const MAX_IMAGES = 10;

    #[\Override]
    protected function query_ai_api(): array {
        $response = parent::query_ai_api();

        // If the request was successful, save the image to a file.
        if ($response['success']) {
            $fileobj = $this->create_file_from_response(
                $this->action->get_configuration('userid'),
                $response
            );
            // Add the file to the response, so the calling placement can do whatever they want with it.
            $response['draftfile'] = $fileobj;
        }

        return $response;
    }

    /**
     * Number of images to request.
     *
     * Moodle passes numimages. xAI accepts 1 to 10. Values outside that range are clamped.
     * Only the first returned image is stored, because the core response has one draft file.
     *
     * @return int
     */
    private function get_number_of_images(): int {
        $numberimages = (int) $this->action->get_configuration('numimages');
        if ($numberimages < 1) {
            $numberimages = 1;
        }
        if ($numberimages > self::MAX_IMAGES) {
            $numberimages = self::MAX_IMAGES;
        }
        return $numberimages;
    }

    /**
     * Convert the given aspect ratio to an xAI aspect_ratio value.
     *
     * Delegates to the model class if one is found for the configured model,
     * otherwise falls back to square -> 1:1, landscape -> 16:9, portrait -> 9:16.
     *
     * @param string $ratio The aspect ratio of the image ('square', 'landscape', or 'portrait').
     * @return string The aspect_ratio string to send in the API request.
     */
    private function calculate_aspect_ratio(string $ratio): string {
        $modelclass = helper::get_model_class($this->get_model());
        if ($modelclass instanceof xai_image_base) {
            return $modelclass->calculate_aspect_ratio($ratio);
        }
        return self::map_aspect_ratio($ratio);
    }

    /**
     * Convert the given quality setting to an xAI quality value.
     *
     * Delegates to the model class if one is found. A null return means the model does not
     * accept quality, and the parameter is omitted. Unknown custom models use the
     * grok-imagine-image-2.0 mapping: standard -> low, hd -> medium.
     *
     * @param string $quality The quality setting from the action ('standard' or 'hd').
     * @return string|null The quality value to send, or null to omit it.
     */
    private function calculate_quality(string $quality): ?string {
        $modelclass = helper::get_model_class($this->get_model());
        if ($modelclass instanceof xai_image_base) {
            return $modelclass->calculate_quality($quality);
        }
        return self::map_quality($quality);
    }

    /**
     * Map a Moodle aspect ratio to an xAI aspect_ratio.
     *
     * @param string $ratio Moodle aspect ratio.
     * @return string
     */
    public static function map_aspect_ratio(string $ratio): string {
        if ($ratio === 'square') {
            return '1:1';
        } else if ($ratio === 'landscape') {
            return '16:9';
        } else if ($ratio === 'portrait') {
            return '9:16';
        }
        throw new \coding_exception('Invalid aspect ratio: ' . $ratio);
    }

    /**
     * Map a Moodle image quality to an xAI quality.
     *
     * standard maps to low and hd maps to medium. xAI also accepts auto, which is not used
     * because Moodle only offers the two values above. xAI has no high quality.
     *
     * @param string $quality Moodle quality.
     * @return string
     */
    public static function map_quality(string $quality): string {
        if ($quality === 'standard') {
            return 'low';
        } else if ($quality === 'hd') {
            return 'medium';
        }
        throw new \coding_exception('Invalid quality: ' . $quality);
    }

    #[\Override]
    protected function create_request_object(string $userid): RequestInterface {
        $requestobj = new \stdClass();
        $requestobj->model = $this->get_model();
        $requestobj->user = $userid;
        $requestobj->prompt = $this->action->get_configuration('prompttext');
        $requestobj->n = $this->get_number_of_images();
        $requestobj->aspect_ratio = $this->calculate_aspect_ratio($this->action->get_configuration('aspectratio'));

        $quality = $this->calculate_quality($this->action->get_configuration('quality'));
        if ($quality !== null) {
            $requestobj->quality = $quality;
        }

        // Prefer the model class, and always fall back to base64. Hosted URLs expire.
        $modelclass = helper::get_model_class($this->get_model());
        $responseformat = 'b64_json';
        if ($modelclass instanceof xai_image_base && $modelclass->response_format() !== null) {
            $responseformat = $modelclass->response_format();
        }
        $requestobj->response_format = $responseformat;

        // Append the extra model settings (resolution, custom JSON, and so on).
        $modelsettings = $this->get_model_settings();
        foreach ($modelsettings as $setting => $value) {
            $requestobj->$setting = $value;
        }
        return new Request(
            method: 'POST',
            uri: '',
            headers: [
                'Content-Type' => 'application/json',
            ],
            body: json_encode($requestobj, JSON_UNESCAPED_SLASHES),
        );
    }

    #[\Override]
    protected function handle_api_success(ResponseInterface $response): array {
        $responsebody = $response->getBody();
        $bodyobj = json_decode($responsebody->getContents());
        $image = $bodyobj->data[0];

        return [
            'success' => true,
            'b64json' => $image->b64_json,
            'output_format' => $this->file_extension_from_response($image, $bodyobj),
            'revisedprompt' => $image->revised_prompt ?? '',
            'model' => $this->get_model(),
        ];
    }

    /**
     * File extension for the generated image.
     *
     * xAI returns mime_type (image/jpeg unless a quality setting produces PNG) and does not
     * return OpenAI's output_format. output_format is honoured when a response includes it.
     *
     * @param \stdClass $image Image object from data[0].
     * @param \stdClass $bodyobj Full response body.
     * @return string File extension without a dot.
     */
    private function file_extension_from_response(\stdClass $image, \stdClass $bodyobj): string {
        if (!empty($bodyobj->output_format)) {
            return $bodyobj->output_format;
        }
        $mimetype = $image->mime_type ?? 'image/jpeg';
        if ($mimetype === 'image/png') {
            return 'png';
        } else if ($mimetype === 'image/webp') {
            return 'webp';
        }
        return 'jpg';
    }

    /**
     * Decode the base64-encoded image from the API response, add a watermark,
     * and store it as a draft file for the given user.
     *
     * Placements can't interact with the provider AI directly,
     * therefore we need to provide the image file in a format that can
     * be used by placements. So we use the file API.
     *
     * @param int $userid The user id.
     * @param array $response Response from the AI provider, containing 'b64json' and 'output_format'.
     * @return \stored_file The stored draft file.
     */
    private function create_file_from_response(
        int $userid,
        array $response,
    ): \stored_file {
        global $CFG;

        require_once("{$CFG->libdir}/filelib.php");

        // Decode the image and store in temp dir.
        $b64json = $response['b64json'];
        $imagebytes = base64_decode($b64json);
        $filename = substr(hash('sha512', $b64json), 0, 16) . '.' . $response['output_format'];
        $tempdst = make_request_directory() . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($tempdst, $imagebytes);

        // Add the watermark.
        $image = new ai_image($tempdst);
        $image->add_watermark()->save();

        // We put the file in the user draft area initially.
        // Placements (on behalf of the user) can then move it to the correct location.
        $fileinfo = new \stdClass();
        $fileinfo->contextid = \context_user::instance($userid)->id;
        $fileinfo->filearea = 'draft';
        $fileinfo->component = 'user';
        $fileinfo->itemid = file_get_unused_draft_itemid();
        $fileinfo->filepath = '/';
        $fileinfo->filename = $filename;

        $fs = get_file_storage();
        return $fs->create_file_from_string($fileinfo, file_get_contents($tempdst));
    }
}
