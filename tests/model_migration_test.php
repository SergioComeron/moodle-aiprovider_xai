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
 * Tests for retired preset model migration.
 *
 * @package    aiprovider_xai
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\aiprovider_xai\model_migration::class)]
final class model_migration_test extends \advanced_testcase {
    /**
     * Retired presets move to the current default. A custom id is kept.
     */
    public function test_rewrite_actionconfig_migrates_retired_presets_only(): void {
        $generate = \core_ai\aiactions\generate_text::class;
        $image = \core_ai\aiactions\generate_image::class;
        $actionconfig = [
            $generate => [
                'settings' => [
                    'model' => 'grok-4.3',
                    'modelextraparams' => '{"temperature": 0.2}',
                ],
                'modelsettings' => [
                    'grok-4.3' => ['temperature' => 0.2],
                ],
            ],
            $image => [
                'settings' => [
                    'model' => 'grok-imagine-image-quality',
                ],
                'modelsettings' => [
                    'grok-imagine-image-quality' => ['resolution' => '2k'],
                ],
            ],
            \core_ai\aiactions\summarise_text::class => [
                'settings' => [
                    'model' => 'grok-build-0.1',
                    'modelextraparams' => '{"temperature": 0.1}',
                ],
            ],
            \core_ai\aiactions\explain_text::class => [
                'settings' => [
                    'model' => 'grok-4.6',
                ],
            ],
        ];

        $rewritten = model_migration::rewrite_actionconfig($actionconfig);

        $this->assertSame('grok-4.7', $rewritten[$generate]['settings']['model']);
        $this->assertArrayNotHasKey('modelextraparams', $rewritten[$generate]['settings']);
        $this->assertSame(['temperature' => 0.2], $rewritten[$generate]['modelsettings']['grok-4.7']);
        $this->assertArrayNotHasKey('grok-4.3', $rewritten[$generate]['modelsettings']);

        $this->assertSame('grok-imagine-image-2.0', $rewritten[$image]['settings']['model']);
        $this->assertSame(['resolution' => '2k'], $rewritten[$image]['modelsettings']['grok-imagine-image-2.0']);
        $this->assertArrayNotHasKey('grok-imagine-image-quality', $rewritten[$image]['modelsettings']);

        $this->assertSame('grok-build-0.1', $rewritten[\core_ai\aiactions\summarise_text::class]['settings']['model']);
        $this->assertSame(
            '{"temperature": 0.1}',
            $rewritten[\core_ai\aiactions\summarise_text::class]['settings']['modelextraparams'],
        );
        $this->assertSame('grok-4.6', $rewritten[\core_ai\aiactions\explain_text::class]['settings']['model']);
    }
}
