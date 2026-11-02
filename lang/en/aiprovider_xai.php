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

/**
 * Strings for component aiprovider_xai, language 'en'.
 *
 * @package    aiprovider_xai
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['action:explain_text:endpoint'] = 'API endpoint';
$string['action:explain_text:model'] = 'AI model';
$string['action:explain_text:model_help'] = 'The model used to explain the provided text.';
$string['action:explain_text:systeminstruction'] = 'System instruction';
$string['action:explain_text:systeminstruction_default'] = 'You will receive a text input from the user. Your task is to explain the provided text. Follow these guidelines:
 1. Elaborate: Expand on key ideas and concepts, ensuring the explanation adds meaningful depth and avoids restating the text verbatim.
 2. Simplify: Break down complex terms or ideas into simpler components, making them easy to understand for a wide audience, including learners.
 3. Provide Context: Explain why something happens, how it works, or what its purpose is. Include relevant examples or analogies to enhance understanding where appropriate.
 4. Organise Logically: Structure your explanation to flow naturally, beginning with fundamental ideas before moving to finer details.

Important Instructions:
 1. Return the explanation in plain text only.
 2. Do not include any markdown formatting, greetings, or platitudes.
 3. Focus on clarity, conciseness, and accessibility.
 4. Reply in the same language as the input text.

Ensure the explanation is easy to read and effectively conveys the main points of the original text.';
$string['action:explain_text:systeminstruction_help'] = 'This instruction is sent to the AI model along with the user\'s prompt. Editing this instruction is not recommended unless absolutely required.';
$string['action:generate_image:endpoint'] = 'API endpoint';
$string['action:generate_image:model'] = 'AI model';
$string['action:generate_image:model_help'] = 'The model used to generate images from user prompts.';
$string['action:generate_text:endpoint'] = 'API endpoint';
$string['action:generate_text:model'] = 'AI model';
$string['action:generate_text:model_help'] = 'The model used to generate the text response.';
$string['action:generate_text:systeminstruction'] = 'System instruction';
$string['action:generate_text:systeminstruction_default'] = 'You will receive a text input from the user. Your task is to generate text based on their request. Follow these important instructions:
 1. Return the generated text in plain text only.
 2. Do not include any markdown formatting, greetings, or platitudes.
 3. Reply in the same language as the user\'s request.';
$string['action:generate_text:systeminstruction_help'] = 'This instruction is sent to the AI model along with the user\'s prompt. Editing this instruction is not recommended unless absolutely required.';
$string['action:summarise_text:endpoint'] = 'API endpoint';
$string['action:summarise_text:model'] = 'AI model';
$string['action:summarise_text:model_help'] = 'The model used to summarise the provided text.';
$string['action:summarise_text:systeminstruction'] = 'System instruction';
$string['action:summarise_text:systeminstruction_default'] = 'You will receive a text input from the user. Your task is to summarize the provided text. Follow these guidelines:
 1. Condense: Shorten long passages into key points.
 2. Simplify: Make complex information easier to understand, especially for learners.

Important Instructions:
 1. Return the summary in plain text only.
 2. Do not include any markdown formatting, greetings, or platitudes.
 3. Focus on clarity, conciseness, and accessibility.
 4. Reply in the same language as the input text.

Ensure the summary is easy to read and effectively conveys the main points of the original text.';
$string['action:summarise_text:systeminstruction_help'] = 'This instruction is sent to the AI model along with the user\'s prompt. Editing this instruction is not recommended unless absolutely required.';
$string['apikey'] = 'xAI API key';
$string['apikey_help'] = 'Get a key from the <a href="https://console.x.ai/" target="_blank">xAI console</a>.';
$string['custom_model_name'] = 'Custom model name';
$string['extraparams'] = 'Extra parameters';
$string['extraparams_help'] = 'Extra parameters can be configured here. We support JSON format. For example:
<pre>
{
    "temperature": 0.5,
    "max_completion_tokens": 100
}
</pre>
Grok reasoning models do not accept frequency_penalty or presence_penalty. max_tokens is deprecated; use max_completion_tokens.';
$string['invalidjson'] = 'Invalid JSON string';
$string['pluginname'] = 'xAI API provider';
$string['privacy:metadata'] = 'The xAI API provider plugin does not store any personal data.';
$string['privacy:metadata:aiprovider_xai:externalpurpose'] = 'This information is sent to the xAI API in order for a response to be generated. Your xAI account settings may change how xAI stores and retains this data. No user data is explicitly sent to xAI or stored in Moodle LMS by this plugin.';
$string['privacy:metadata:aiprovider_xai:model'] = 'The model used to generate the response.';
$string['privacy:metadata:aiprovider_xai:numberimages'] = 'When generating images the number of images used in the response.';
$string['privacy:metadata:aiprovider_xai:prompttext'] = 'The user entered text prompt used to generate the response.';
$string['privacy:metadata:aiprovider_xai:responseformat'] = 'The format of the response when generating images.';
$string['settings'] = 'Settings';
$string['settings_help'] = 'Adjust the settings below to customise how requests are sent to xAI.';
$string['settings_max_completion_tokens'] = 'max_completion_tokens';
$string['settings_max_completion_tokens_help'] = 'The maximum number of tokens used in the generated text. This limit applies to visible output tokens.';
$string['settings_resolution'] = 'resolution';
$string['settings_resolution_1k'] = '1k';
$string['settings_resolution_2k'] = '2k';
$string['settings_resolution_help'] = 'Output resolution for generated images. 1k is the API default. 2k requests a higher-resolution image. 1.5k can be sent as a custom extra parameter.';
$string['settings_temperature'] = 'temperature';
$string['settings_temperature_help'] = 'Sampling temperature between 0 and 2. Higher values make the output more random. Lower values make it more focused.';
$string['settings_top_p'] = 'top_p';
$string['settings_top_p_help'] = 'top_p (nucleus sampling) determines how many possible words to consider. A high value (e.g. 0.9) means the model looks at more words, which makes the generated text more diverse.';
