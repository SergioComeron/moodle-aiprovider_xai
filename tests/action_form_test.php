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

use aiprovider_xai\aimodel\xai_base;

/**
 * Tests for action setting forms.
 *
 * @package    aiprovider_xai
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\aiprovider_xai\form\action_form::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\aiprovider_xai\form\action_generate_text_form::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\aiprovider_xai\form\action_generate_image_form::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\aiprovider_xai\provider::class)]
final class action_form_test extends \advanced_testcase {
    /**
     * Set up the test.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * PARAM_RAW and other string types stay strings. Only int and float are cast.
     */
    public function test_cast_value_to_type_keeps_strings(): void {
        $form = new form\action_generate_image_form(customdata: [
            'actionname' => 'generate_image',
            'action' => \core_ai\aiactions\generate_image::class,
            'providername' => 'aiprovider_xai',
        ]);
        $method = new \ReflectionMethod($form, 'cast_value_to_type');

        $this->assertSame('1k', $method->invoke($form, '1k', PARAM_RAW));
        $this->assertSame('2k', $method->invoke($form, '2k', PARAM_TEXT));
        $this->assertSame('1k', $method->invoke($form, '1k', PARAM_RAW_TRIMMED));
        $this->assertSame('', $method->invoke($form, '', PARAM_RAW));

        $this->assertSame(100, $method->invoke($form, '100', PARAM_INT));
        $this->assertNull($method->invoke($form, '', PARAM_INT));

        $floatvalue = $method->invoke($form, '0.5', PARAM_FLOAT);
        $this->assertIsFloat($floatvalue);
        $this->assertEquals(0.5, $floatvalue);
        $this->assertNull($method->invoke($form, '', PARAM_FLOAT));
    }

    /**
     * Text defaults ask for generated text, and summarise/explain ask for the input language.
     */
    public function test_system_instruction_defaults(): void {
        $generate = provider::get_action_setting_defaults(\core_ai\aiactions\generate_text::class);
        $summarise = provider::get_action_setting_defaults(\core_ai\aiactions\summarise_text::class);
        $explain = provider::get_action_setting_defaults(\core_ai\aiactions\explain_text::class);

        $this->assertSame(
            system_instruction::for_action(\core_ai\aiactions\generate_text::class),
            $generate['systeminstruction'],
        );
        $this->assertStringContainsString('Return the generated text in plain text only', $generate['systeminstruction']);
        $this->assertStringNotContainsString('Return the summary', $generate['systeminstruction']);
        $this->assertStringContainsString('same language as the user\'s request', $generate['systeminstruction']);

        $this->assertSame(
            system_instruction::for_action(\core_ai\aiactions\summarise_text::class),
            $summarise['systeminstruction'],
        );
        $this->assertStringContainsString('Return the summary in plain text only', $summarise['systeminstruction']);
        $this->assertStringContainsString('same language as the input text', $summarise['systeminstruction']);

        $this->assertSame(
            system_instruction::for_action(\core_ai\aiactions\explain_text::class),
            $explain['systeminstruction'],
        );
        $this->assertStringContainsString('Return the explanation in plain text only', $explain['systeminstruction']);
        $this->assertStringNotContainsString('Return the summary', $explain['systeminstruction']);
        $this->assertStringContainsString('same language as the input text', $explain['systeminstruction']);
    }

    /**
     * A stored core instruction is shown as the plugin default. Custom text is kept.
     */
    public function test_form_replaces_core_system_instruction(): void {
        $action = \core_ai\aiactions\generate_text::class;
        $form = new form\action_generate_text_form(customdata: [
            'actionname' => 'generate_text',
            'action' => $action,
            'providername' => 'aiprovider_xai',
            'actionconfig' => [
                'settings' => [
                    'systeminstruction' => $action::get_system_instruction(),
                ],
            ],
        ]);
        $defaults = $form->get_defaults();
        $this->assertSame(system_instruction::for_action($action), $defaults['systeminstruction']);
        $this->assertStringNotContainsString('Return the summary', $defaults['systeminstruction']);
    }

    /**
     * A saved custom instruction is kept.
     */
    public function test_saved_system_instruction_is_kept(): void {
        $form = new form\action_generate_text_form(customdata: [
            'actionname' => 'generate_text',
            'action' => \core_ai\aiactions\generate_text::class,
            'providername' => 'aiprovider_xai',
            'actionconfig' => [
                'settings' => [
                    'systeminstruction' => 'Write a haiku.',
                ],
            ],
        ]);
        $defaults = $form->get_defaults();
        $this->assertSame('Write a haiku.', $defaults['systeminstruction']);
    }

    /**
     * Predefined text models are grok-4.7, grok-4.6 and grok-4.5, plus a custom id.
     */
    public function test_text_model_list_and_default(): void {
        $form = new form\action_generate_text_form(customdata: [
            'actionname' => 'generate_text',
            'action' => \core_ai\aiactions\generate_text::class,
            'providername' => 'aiprovider_xai',
        ]);
        $method = new \ReflectionMethod($form, 'get_model_list');
        $models = $method->invoke($form, xai_base::MODEL_TYPE_TEXT);

        $this->assertArrayHasKey('custom', $models);
        $this->assertArrayHasKey('grok-4.7', $models);
        $this->assertArrayHasKey('grok-4.6', $models);
        $this->assertArrayHasKey('grok-4.5', $models);
        $this->assertArrayNotHasKey('grok-4.3', $models);

        $defaults = provider::get_action_setting_defaults(\core_ai\aiactions\generate_text::class);
        $this->assertSame('grok-4.7', $defaults['model']);
    }

    /**
     * Image models are the current Imagine family. The retired quality slug is not offered.
     */
    public function test_image_model_list_and_default(): void {
        $form = new form\action_generate_image_form(customdata: [
            'actionname' => 'generate_image',
            'action' => \core_ai\aiactions\generate_image::class,
            'providername' => 'aiprovider_xai',
        ]);
        $method = new \ReflectionMethod($form, 'get_model_list');
        $models = $method->invoke($form, xai_base::MODEL_TYPE_IMAGE);

        $this->assertArrayHasKey('custom', $models);
        $this->assertArrayHasKey('grok-imagine-image-2.0', $models);
        $this->assertArrayHasKey('grok-imagine-image', $models);
        $this->assertArrayNotHasKey('grok-imagine-image-quality', $models);
        $this->assertArrayNotHasKey('grok-imagine-image-pro', $models);

        $defaults = provider::get_action_setting_defaults(\core_ai\aiactions\generate_image::class);
        $this->assertSame('grok-imagine-image-2.0', $defaults['model']);
    }

    /**
     * A removed preset is shown as the new default, not as Custom.
     */
    public function test_retired_preset_is_not_shown_as_custom(): void {
        $form = new form\action_generate_text_form(customdata: [
            'actionname' => 'generate_text',
            'action' => \core_ai\aiactions\generate_text::class,
            'providername' => 'aiprovider_xai',
            'actionconfig' => [
                'settings' => [
                    'model' => 'grok-4.3',
                ],
                'modelsettings' => [
                    'grok-4.3' => ['temperature' => '0.4'],
                ],
            ],
        ]);
        $defaults = $form->get_defaults();
        $this->assertSame('grok-4.7', $defaults['model']);

        $mform = new \ReflectionProperty($form, '_form');
        $template = $mform->getValue($form)->getElementValue('modeltemplate');
        $template = is_array($template) ? $template[0] : $template;
        $this->assertSame('grok-4.7', $template);
    }

    /**
     * A custom model id stays on the custom field.
     */
    public function test_custom_model_id_is_kept(): void {
        $form = new form\action_generate_text_form(customdata: [
            'actionname' => 'generate_text',
            'action' => \core_ai\aiactions\generate_text::class,
            'providername' => 'aiprovider_xai',
            'actionconfig' => [
                'settings' => [
                    'model' => 'grok-build-0.1',
                ],
            ],
        ]);
        $mform = new \ReflectionProperty($form, '_form');
        $template = $mform->getValue($form)->getElementValue('modeltemplate');
        $template = is_array($template) ? $template[0] : $template;
        $custom = $mform->getValue($form)->getElementValue('custommodel');
        $custom = is_array($custom) ? $custom[0] : $custom;
        $this->assertSame('custom', $template);
        $this->assertSame('grok-build-0.1', $custom);
    }
}
