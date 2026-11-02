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

namespace aiprovider_xai\form;

use aiprovider_xai\aimodel\xai_base;
use aiprovider_xai\system_instruction;

/**
 * Generate text action provider settings form.
 *
 * Also used for summarise_text and explain_text, which share the chat completion call.
 *
 * @package    aiprovider_xai
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class action_generate_text_form extends action_form {
    #[\Override]
    protected function definition(): void {
        parent::definition();
        $mform = $this->_form;

        $this->add_model_fields(xai_base::MODEL_TYPE_TEXT);

        // API endpoint.
        $mform->addElement(
            'text',
            'endpoint',
            get_string("action:{$this->actionname}:endpoint", 'aiprovider_xai'),
            'maxlength="255" size="30"',
        );
        $mform->setType('endpoint', PARAM_URL);
        $mform->addRule('endpoint', null, 'required', null, 'client');
        $mform->setDefault('endpoint', $this->actionconfig['endpoint'] ?? 'https://api.x.ai/v1/chat/completions');

        // System Instructions.
        $mform->addElement(
            'textarea',
            'systeminstruction',
            get_string("action:{$this->actionname}:systeminstruction", 'aiprovider_xai'),
            'wrap="virtual" rows="5" cols="20"',
        );
        $mform->setType('systeminstruction', PARAM_TEXT);
        if (array_key_exists('systeminstruction', $this->actionconfig)) {
            // Swap an unmodified core instruction for the plugin default before the form displays it.
            $this->actionconfig['systeminstruction'] = system_instruction::normalise(
                $this->action,
                (string) $this->actionconfig['systeminstruction'],
            );
        }
        $mform->setDefault(
            'systeminstruction',
            $this->actionconfig['systeminstruction'] ?? system_instruction::for_action($this->action),
        );
        $mform->addHelpButton('systeminstruction', "action:{$this->actionname}:systeminstruction", 'aiprovider_xai');

        if ($this->returnurl) {
            $mform->addElement('hidden', 'returnurl', $this->returnurl);
            $mform->setType('returnurl', PARAM_LOCALURL);
        }

        // Add the action class as a hidden field.
        $mform->addElement('hidden', 'action', $this->action);
        $mform->setType('action', PARAM_TEXT);

        // Add the provider class as a hidden field.
        $mform->addElement('hidden', 'provider', $this->providername);
        $mform->setType('provider', PARAM_TEXT);

        // Add the provider id as a hidden field.
        $mform->addElement('hidden', 'providerid', $this->providerid);
        $mform->setType('providerid', PARAM_INT);

        $this->set_data($this->actionconfig);
    }
}
