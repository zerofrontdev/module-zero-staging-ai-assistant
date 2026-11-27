<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */
declare(strict_types=1);

namespace Zero\StagingAiAssistant\Test\Unit\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Zero\AiAssistant\Model\Tool\ToolInterface;
use Zero\StagingAiAssistant\Model\Tool\ListCampaigns;
use Zero\StagingAiAssistant\Model\Tool\ScheduleChange;

/**
 * What the Staging tools tell the assistant (name, ACL resource, area, whether they change data) and how etc/di.xml
 * registers them (item name = tool name, sortOrder, `requires`): none of it may change, the e2e test, the role's
 * ACL and Claude depend on it.
 */
class ToolsTest extends TestCase
{
    private const DI = __DIR__ . '/../../../etc/di.xml';

    /**
     * @return array<string, array{0: string, 1: string, 2: string, 3: string, 4: bool, 5: int}>
     */
    public static function tools(): array
    {
        return [
            'list_campaigns' => [ListCampaigns::class, 'list_campaigns', 'Zero_Staging::staging', 'staging', false, 1410],
            'schedule_change' => [ScheduleChange::class, 'schedule_change', 'Zero_Staging::staging', 'staging', true, 1420],
        ];
    }

    #[DataProvider('tools')]
    public function testTheToolKeepsItsContract(string $class, string $name, string $resource, string $area, bool $changes, int $sortOrder): void
    {
        $tool = $this->tool($class);

        $this->assertSame($name, $tool->name());
        $this->assertSame($resource, $tool->resource());
        $this->assertSame($area, $tool->area());
        $this->assertSame($changes, $tool->changes());
        $this->assertNotSame('', $tool->description());
        $this->assertSame('object', $tool->schema()['type']);

        $items = $this->diItems('tools');
        $this->assertSame($class . '\\Proxy', $items[$name]['class'] ?? null, 'registered as a Proxy item under the tool name');
        $this->assertSame($sortOrder, $items[$name]['sortOrder']);
        $this->assertSame('Zero_Staging', $this->diItems('requires')[$name]['class'] ?? null);
    }

    public function testDiRegistersNoOtherToolThanTheOnesHere(): void
    {
        $this->assertEqualsCanonicalizing(array_column(self::tools(), 1), array_keys($this->diItems('tools')));
        $this->assertEqualsCanonicalizing(array_column(self::tools(), 1), array_keys($this->diItems('requires')));
    }

    private function tool(string $class): ToolInterface
    {
        $arguments = [];
        foreach ((new \ReflectionClass($class))->getConstructor()->getParameters() as $parameter) {
            $arguments[] = $this->createStub((string) $parameter->getType());
        }

        return new $class(...$arguments);
    }

    /**
     * @return array<string, array{class: string, sortOrder: int}> the items of one argument of Zero\AiAssistant\Model\Tools
     */
    private function diItems(string $argument): array
    {
        $items = [];
        $xml = simplexml_load_file(self::DI);
        foreach ($xml->type as $type) {
            if ((string) $type['name'] !== 'Zero\\AiAssistant\\Model\\Tools') {
                continue;
            }
            foreach ($type->arguments->argument as $arg) {
                if ((string) $arg['name'] === $argument) {
                    foreach ($arg->item as $item) {
                        $items[(string) $item['name']] = ['class' => trim((string) $item), 'sortOrder' => (int) $item['sortOrder']];
                    }
                }
            }
        }

        return $items;
    }
}
