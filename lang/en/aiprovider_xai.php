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
 * @copyright  2026 Sergio Comerón <sergiocomeron@icloud.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['action:explain_text:endpoint'] = 'API endpoint';
$string['action:explain_text:model'] = 'AI model';
$string['action:explain_text:model_help'] = 'The model used to explain the provided text.';
$string['action:explain_text:systeminstruction'] = 'System instruction';
$string['action:explain_text:systeminstruction_default'] = 'Explain the user\'s text. Return the explanation in plain text only, in the same language as the input text. No markdown, greetings, or platitudes.';
$string['action:explain_text:systeminstruction_help'] = 'This instruction is sent to the AI model along with the user\'s prompt. Editing this instruction is not recommended unless absolutely required.';
$string['action:generate_image:endpoint'] = 'API endpoint';
$string['action:generate_image:model'] = 'AI model';
$string['action:generate_image:model_help'] = 'The model used to generate images from user prompts.';
$string['action:generate_text:endpoint'] = 'API endpoint';
$string['action:generate_text:model'] = 'AI model';
$string['action:generate_text:model_help'] = 'The model used to generate the text response.';
$string['action:generate_text:systeminstruction'] = 'System instruction';
$string['action:generate_text:systeminstruction_default'] = 'Generate text from the user\'s request. Return the generated text in plain text only, in the same language as the user\'s request. No markdown, greetings, or platitudes.';
$string['action:generate_text:systeminstruction_help'] = 'This instruction is sent to the AI model along with the user\'s prompt. Editing this instruction is not recommended unless absolutely required.';
$string['action:summarise_text:endpoint'] = 'API endpoint';
$string['action:summarise_text:model'] = 'AI model';
$string['action:summarise_text:model_help'] = 'The model used to summarise the provided text.';
$string['action:summarise_text:systeminstruction'] = 'System instruction';
$string['action:summarise_text:systeminstruction_default'] = 'Summarise the user\'s text. Return the summary in plain text only, in the same language as the input text. No markdown, greetings, or platitudes.';
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
$string['privacy:metadata:aiprovider_xai:externalpurpose'] = 'This information is sent to the xAI API in order for a response to be generated. Your xAI account settings may change how xAI stores and retains this data. No user data is explicitly sent to xAI or stored in Moodle LMS by this plugin. xAI privacy policy: https://x.ai/legal/privacy-policy';
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
