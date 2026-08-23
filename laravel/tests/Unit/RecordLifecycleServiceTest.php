<?php

namespace Tests\Unit;

use App\Models\Activity;
use App\Models\Record;
use App\Models\Retention;
use App\Models\Sort;
use App\Services\RecordLifecycleService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class RecordLifecycleServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_unclassified_record_is_reported_as_configuration_required(): void
    {
        $record = new Record(['date_exact' => '2020-01-01']);
        $record->setRelation('activity', null);

        $cycle = (new RecordLifecycleService())->analyse($record);

        $this->assertSame('configuration_required', $cycle['phase']);
        $this->assertNotEmpty($cycle['issues']);
    }

    public function test_expired_elimination_rule_produces_elimination_due_phase(): void
    {
        Carbon::setTestNow('2026-08-23');
        $record = $this->recordWithRule('E', 5, '2018-12-31');

        $cycle = (new RecordLifecycleService())->analyse($record);

        $this->assertSame('elimination_due', $cycle['phase']);
        $this->assertSame('2023-12-31', $cycle['deadline']->toDateString());
    }

    public function test_closed_record_with_future_deadline_is_ready_for_transfer(): void
    {
        Carbon::setTestNow('2026-08-23');
        $record = $this->recordWithRule('C', 10, '2025-12-31');

        $cycle = (new RecordLifecycleService())->analyse($record);

        $this->assertSame('transfer_due', $cycle['phase']);
    }

    public function test_effective_deposit_has_priority_over_calculated_deadline(): void
    {
        Carbon::setTestNow('2026-08-23');
        $record = $this->recordWithRule('C', 0, '2020-12-31');
        $record->deposit_effective_date = '2021-01-15';

        $cycle = (new RecordLifecycleService())->analyse($record);

        $this->assertSame('deposited', $cycle['phase']);
    }

    private function recordWithRule(string $sortCode, int $duration, string $closingDate): Record
    {
        $sort = new Sort(['code' => $sortCode, 'name' => $sortCode]);
        $retention = new Retention(['code' => 'RET-DEMO', 'name' => 'Règle démo', 'duration' => $duration]);
        $retention->setRelation('sort', $sort);

        $activity = new Activity(['code' => 'DEMO-001', 'name' => 'Classe démo']);
        $activity->setRelation('retentions', new Collection([$retention]));
        $activity->setRelation('communicability', null);

        $record = new Record(['closing_date' => $closingDate]);
        $record->setRelation('activity', $activity);

        return $record;
    }
}
