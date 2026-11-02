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

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class process text generation.
 *
 * @package    aiprovider_xai
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class process_generate_text extends abstract_processor {
    #[\Override]
    protected function get_system_instruction(): string {
        $stored = $this->provider->actionconfig[$this->action::class]['settings']['systeminstruction'] ?? '';
        return system_instruction::normalise($this->action::class, $stored);
    }

    #[\Override]
    protected function create_request_object(string $userid): RequestInterface {
        // Create the user object.
        $userobj = new \stdClass();
        $userobj->role = 'user';
        $userobj->content = $this->action->get_configuration('prompttext');

        // Create the request object.
        $requestobj = new \stdClass();
        $requestobj->model = $this->get_model();
        $requestobj->user = $userid;

        // If there is a system string available, use it.
        $systeminstruction = $this->get_system_instruction();
        if (!empty($systeminstruction)) {
            $systemobj = new \stdClass();
            $systemobj->role = 'system';
            $systemobj->content = $systeminstruction;
            $requestobj->messages = [$systemobj, $userobj];
        } else {
            $requestobj->messages = [$userobj];
        }

        // Append the extra model settings.
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

    /**
     * Handle a successful response from the external AI api.
     *
     * @param ResponseInterface $response The response object.
     * @return array The response.
     */
    protected function handle_api_success(ResponseInterface $response): array {
        $responsebody = $response->getBody();
        $bodyobj = json_decode($responsebody->getContents());

        return [
            'success' => true,
            'id' => $bodyobj->id,
            'fingerprint' => $bodyobj->system_fingerprint,
            'generatedcontent' => $bodyobj->choices[0]->message->content,
            'finishreason' => $bodyobj->choices[0]->finish_reason,
            // Kept on the response for both supported lines. Moodle 5.1 and 5.2
            // store these on the action table. Moodle 5.3 (MDL-89123) reads the
            // same keys from the response and writes them on ai_action_register.
            'prompttokens' => $bodyobj->usage->prompt_tokens,
            // Reasoning tokens are billed as output but are omitted from completion_tokens.
            'completiontokens' => $this->billed_completion_tokens($bodyobj->usage),
            'model' => $bodyobj->model ?? $this->get_model(),
        ];
    }

    /**
     * Output tokens that xAI bills for this response.
     *
     * completion_tokens is the visible reply. Reasoning tokens are reported
     * separately under completion_tokens_details.reasoning_tokens and are not
     * included in completion_tokens. OpenAI's completion_tokens already includes
     * reasoning, so the value stored here is the sum.
     *
     * @param \stdClass $usage Usage object from the chat completion.
     * @return int
     */
    private function billed_completion_tokens(\stdClass $usage): int {
        $details = $usage->completion_tokens_details ?? null;
        $reasoningtokens = (int) ($details?->reasoning_tokens ?? 0);
        return (int) $usage->completion_tokens + $reasoningtokens;
    }
}
