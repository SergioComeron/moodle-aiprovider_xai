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

/**
 * Tests for system instruction defaults and replacement of unmodified core text.
 *
 * @package    aiprovider_xai
 * @copyright  2026 Sergio Comerón <sergiocomeron@icloud.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\aiprovider_xai\system_instruction::class)]
final class system_instruction_test extends \advanced_testcase {
    /**
     * Set up the test.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Unmodified core instructions are replaced. Custom and empty values are kept.
     */
    public function test_normalise_replaces_only_core_defaults(): void {
        $generate = \core_ai\aiactions\generate_text::class;
        $core = $generate::get_system_instruction();
        $plugin = system_instruction::for_action($generate);

        $this->assertNotSame($core, $plugin);
        $this->assertStringContainsString('Return the summary in plain text only.', $core);
        $this->assertStringNotContainsString('Return the summary', $plugin);
        $this->assertSame($plugin, system_instruction::normalise($generate, $core));
        $this->assertSame('Write a haiku.', system_instruction::normalise($generate, 'Write a haiku.'));
        $this->assertSame('', system_instruction::normalise($generate, ''));
        $this->assertSame($plugin, system_instruction::normalise($generate, $plugin));
    }

    /**
     * Summarise and explain defaults ask for the language of the input text.
     */
    public function test_summarise_and_explain_defaults_match_input_language(): void {
        $summarise = system_instruction::for_action(\core_ai\aiactions\summarise_text::class);
        $explain = system_instruction::for_action(\core_ai\aiactions\explain_text::class);

        $this->assertStringContainsString('same language as the input text', $summarise);
        $this->assertStringContainsString('same language as the input text', $explain);
        $this->assertStringContainsString('Return the explanation in plain text only', $explain);
        $this->assertStringNotContainsString('Return the summary', $explain);

        $coreexplain = \core_ai\aiactions\explain_text::get_system_instruction();
        $this->assertSame($explain, system_instruction::normalise(\core_ai\aiactions\explain_text::class, $coreexplain));
    }

    /**
     * Defaults stay about as short as the core generate-text instruction.
     */
    public function test_defaults_stay_short(): void {
        $actions = [
            \core_ai\aiactions\generate_text::class,
            \core_ai\aiactions\summarise_text::class,
            \core_ai\aiactions\explain_text::class,
        ];
        foreach ($actions as $action) {
            $instruction = system_instruction::for_action($action);
            $this->assertLessThan(220, strlen($instruction), $action);
            $this->assertStringNotContainsString('Elaborate', $instruction);
            $this->assertStringNotContainsString('Condense', $instruction);
        }
    }

    /**
     * An unchanged long default from the previous release is replaced.
     */
    public function test_previous_long_default_is_replaced(): void {
        $explain = \core_ai\aiactions\explain_text::class;
        $long = previous_system_instruction::all()['en']['explain_text'];
        $this->assertGreaterThan(800, strlen($long));
        $this->assertSame(
            system_instruction::for_action($explain),
            system_instruction::normalise($explain, $long),
        );
        $this->assertSame(
            'Keep this edited explanation instruction.',
            system_instruction::normalise($explain, 'Keep this edited explanation instruction.'),
        );
    }

    /**
     * Stored action config keeps a custom instruction and replaces a core one.
     */
    public function test_rewrite_actionconfig(): void {
        $generate = \core_ai\aiactions\generate_text::class;
        $summarise = \core_ai\aiactions\summarise_text::class;
        $actionconfig = [
            $generate => [
                'settings' => [
                    'systeminstruction' => $generate::get_system_instruction(),
                    'model' => 'grok-4.7',
                ],
            ],
            $summarise => [
                'settings' => [
                    'systeminstruction' => 'Resume en espanol.',
                ],
            ],
        ];

        $rewritten = system_instruction::rewrite_actionconfig($actionconfig);

        $this->assertSame(
            system_instruction::for_action($generate),
            $rewritten[$generate]['settings']['systeminstruction'],
        );
        $this->assertSame('grok-4.7', $rewritten[$generate]['settings']['model']);
        $this->assertSame('Resume en espanol.', $rewritten[$summarise]['settings']['systeminstruction']);
    }
}
