<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */
declare(strict_types=1);

namespace Zero\StagingAiAssistant\Test\Unit\Model\Tool;

use PHPUnit\Framework\TestCase;
use Zero\AiAssistant\Model\Conversations;
use Zero\AiAssistant\Model\Tool\Context;
use Zero\Staging\Model\Campaigns;
use Zero\Staging\Model\Clock;
use Zero\StagingAiAssistant\Model\Tool\ListCampaigns;

/**
 * list_campaigns reads through Zero\Staging\Model\Campaigns and Clock (constructor dependencies now): upcoming means
 * scheduled and live, times are shown in store time.
 */
class ListCampaignsTest extends TestCase
{
    public function testUpcomingIsScheduledAndActiveWithTheirChangesInStoreTime(): void
    {
        $campaigns = $this->createMock(Campaigns::class);
        $campaigns->expects($this->once())->method('all')->with([Campaigns::SCHEDULED, Campaigns::ACTIVE])->willReturn([[
            'campaign_id' => '3', 'name' => 'Sale', 'status' => 'scheduled', 'start_at' => 'S', 'end_at' => 'E',
        ]]);
        $campaigns->method('changes')->with(3)->willReturn([[
            'change_id' => '5', 'target' => 'product', 'entity_id' => '9', 'label' => 'Shirt', 'data' => '{"price":"5"}', 'status' => 'pending',
        ]]);
        $clock = $this->createStub(Clock::class);
        $clock->method('toInput')->willReturnCallback(static fn ($utc) => 'local ' . $utc);
        $clock->method('now')->willReturn('N');

        $result = (new ListCampaigns($campaigns, $clock))->run([], $this->context());

        $this->assertSame('local S', $result['campaigns'][0]['start']);
        $this->assertSame('local N', $result['now']);
        $this->assertSame(['price' => '5'], $result['campaigns'][0]['changes'][0]['data']);
        $this->assertSame(9, $result['campaigns'][0]['changes'][0]['entity_id']);
    }

    public function testCompletedAsksOnlyForCompletedCampaigns(): void
    {
        $campaigns = $this->createMock(Campaigns::class);
        $campaigns->expects($this->once())->method('all')->with([Campaigns::COMPLETED])->willReturn([]);

        (new ListCampaigns($campaigns, $this->createStub(Clock::class)))->run(['status' => 'completed'], $this->context());
    }

    private function context(): Context
    {
        return new Context(1, 1, $this->createStub(Conversations::class));
    }
}
