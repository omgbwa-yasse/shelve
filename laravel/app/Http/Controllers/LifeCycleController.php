<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Models\Record;
use App\Models\RecordStatus;
use App\Models\SlipStatus;
use App\Models\User;
use App\Services\RecordLifecycleService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LifeCycleController extends Controller
{
    private const RECORDS_SELECT = 'records.*';

    public function __construct(private readonly RecordLifecycleService $lifecycle)
    {
    }

    private function referenceDateExpression(): string
    {
        return 'COALESCE(records.closing_date, records.end_date, records.date_exact, records.opening_date, records.start_date)';
    }

    private function retentionExpiredCondition(): string
    {
        return "DATE_ADD({$this->referenceDateExpression()}, INTERVAL retentions.duration YEAR) < CURRENT_DATE";
    }

    private function retentionActiveCondition(): string
    {
        return "DATE_ADD({$this->referenceDateExpression()}, INTERVAL retentions.duration YEAR) >= CURRENT_DATE";
    }

    private function lifecycleBaseQuery(): Builder
    {
        $query = Record::query()
            ->currentVersion()
            ->whereNull('records.destruction_effective_date')
            ->with([
                'type', 'activity.retentions.sort', 'activity.communicability',
                'status', 'level', 'creator', 'organisation',
            ]);

        if (! Auth::user()->isSuperAdmin()) {
            $query->where('records.organisation_id', Auth::user()->current_organisation_id);
        }

        return $query;
    }

    /**
     * Une classe utilisée dans le cycle de vie doit porter exactement une règle.
     * Cette contrainte évite qu'un même dossier apparaisse simultanément dans des
     * listes de sorts finaux contradictoires.
     */
    private function retentionBaseQuery(): Builder
    {
        return $this->lifecycleBaseQuery()
            ->has('activity.retentions', '=', 1)
            ->join('activities', 'records.activity_id', '=', 'activities.id')
            ->join('retention_activity', 'activities.id', '=', 'retention_activity.activity_id')
            ->join('retentions', 'retention_activity.retention_id', '=', 'retentions.id')
            ->join('sorts', 'retentions.sort_id', '=', 'sorts.id')
            ->whereNotNull(DB::raw($this->referenceDateExpression()))
            ->select(self::RECORDS_SELECT);
    }

    private function commonViewData(): array
    {
        return [
            'types' => \App\Models\RecordType::active()->ordered()->get(),
            'slipStatuses' => SlipStatus::all(),
            'statuses' => RecordStatus::all(),
            'terms' => [],
            'users' => User::select('id', 'name')->get(),
            'organisations' => Organisation::select('id', 'name')->get(),
            'showLifecycleColumns' => true,
        ];
    }

    private function render(Builder $query, string $title)
    {
        $records = $query
            ->orderByRaw($this->referenceDateExpression().' DESC')
            ->paginate(15);

        $lifecycleData = $records->getCollection()
            ->mapWithKeys(fn (Record $record) => [$record->id => $this->lifecycle->analyse($record)]);

        return view('records.index', array_merge(
            compact('records', 'title', 'lifecycleData'),
            $this->commonViewData()
        ));
    }

    public function recordToConfigure()
    {
        $query = $this->lifecycleBaseQuery()
            ->where(function (Builder $q) {
                $q->whereNull('records.activity_id')
                    ->orWhereDoesntHave('activity.retentions')
                    ->orWhereHas('activity', fn (Builder $activity) => $activity->has('retentions', '>', 1))
                    ->orWhere(function (Builder $dates) {
                        $dates->whereNull('records.closing_date')
                            ->whereNull('records.end_date')
                            ->whereNull('records.date_exact')
                            ->whereNull('records.opening_date')
                            ->whereNull('records.start_date');
                    });
            });

        return $this->render($query, 'À configurer — classe, règle ou date manquante');
    }

    public function recordToRetain()
    {
        return $this->render(
            $this->retentionBaseQuery()->whereRaw($this->retentionActiveCondition()),
            'Conservation en cours — échéance non atteinte'
        );
    }

    public function recordToKeep()
    {
        return $this->recordToRetain();
    }

    /**
     * Le transfert est déclenché par la fermeture du dossier. La communicabilité
     * reste une règle d'accès et n'est plus utilisée comme date de mouvement.
     */
    public function recordToTransfer()
    {
        return $this->render(
            $this->retentionBaseQuery()
                ->whereNotNull('records.closing_date')
                ->whereNull('records.transfer_effective_date'),
            'À transférer — dossiers fermés non encore reçus par la destination'
        );
    }

    public function recordToSort()
    {
        return $this->render(
            $this->retentionBaseQuery()
                ->where('sorts.code', 'T')
                ->whereRaw($this->retentionExpiredCondition()),
            'À trier — durée de conservation écoulée'
        );
    }

    public function recordToStore()
    {
        return $this->render(
            $this->retentionBaseQuery()
                ->where('sorts.code', 'C')
                ->whereRaw($this->retentionExpiredCondition())
                ->whereNull('records.deposit_effective_date'),
            'À verser en conservation définitive'
        );
    }

    public function recordToEliminate()
    {
        return $this->render(
            $this->retentionBaseQuery()
                ->where('sorts.code', 'E')
                ->whereRaw($this->retentionExpiredCondition()),
            'À éliminer — après liste, approbation et validation'
        );
    }

    public function getLifecycleData(Record $record): array
    {
        return $this->lifecycle->analyse($record);
    }
}
