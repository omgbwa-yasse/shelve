<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Models\RecordPhysical;
use App\Models\RecordSupport;
use App\Models\RecordStatus;
use App\Models\Container;
use App\Models\Activity;
use App\Models\SlipStatus;
use App\Models\Accession;
use App\Models\Author;
use App\Models\RecordLevel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LifeCycleController extends Controller
{
    // Constantes pour éviter la duplication avec logique date_end/date_exact
    const RECORDS_SELECT = 'records.*';

    /**
     * Convertit une date selon son format en date MySQL
     * @param string $dateField Le champ de date (date_start, date_end, etc.)
     * @param string $formatField Le champ de format de date
     * @return string Expression SQL pour la conversion
     */
    private function convertDateToMysqlDate($dateField, $formatField = 'records.date_format')
    {
        return "CASE
            WHEN {$formatField} = 'Y' AND {$dateField} REGEXP '^[0-9]{4}$' THEN
                MAKEDATE({$dateField}, 365)
            WHEN {$formatField} = 'M' AND {$dateField} REGEXP '^[0-9]{4}/[0-9]{1,2}$' THEN
                STR_TO_DATE(CONCAT(REPLACE({$dateField}, '/', '-'), '-01'), '%Y-%m-%d')
            WHEN {$formatField} = 'D' AND {$dateField} REGEXP '^[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}$' THEN
                STR_TO_DATE(REPLACE({$dateField}, '/', '-'), '%Y-%m-%d')
            WHEN {$dateField} IS NOT NULL AND {$dateField} != '' THEN
                CASE
                    WHEN {$dateField} REGEXP '^[0-9]{4}$' THEN MAKEDATE({$dateField}, 365)
                    WHEN {$dateField} REGEXP '^[0-9]{4}/[0-9]{1,2}$' THEN
                        STR_TO_DATE(CONCAT(REPLACE({$dateField}, '/', '-'), '-01'), '%Y-%m-%d')
                    WHEN {$dateField} REGEXP '^[0-9]{4}/[0-9]{1,2}/[0-9]{1,2}$' THEN
                        STR_TO_DATE(REPLACE({$dateField}, '/', '-'), '%Y-%m-%d')
                    WHEN {$dateField} REGEXP '^[0-9]{4}-[0-9]{1,2}$' THEN
                        STR_TO_DATE(CONCAT({$dateField}, '-01'), '%Y-%m-%d')
                    WHEN {$dateField} REGEXP '^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$' THEN
                        STR_TO_DATE({$dateField}, '%Y-%m-%d')
                    ELSE MAKEDATE({$dateField}, 365)
                END
            ELSE NULL
        END";
    }

    /**
     * Retourne l'expression SQL pour la date de référence (date_end convertie en priorité, sinon date_exact)
     */
    private function getReferenceDateExpression()
    {
        $convertedDateEnd = $this->convertDateToMysqlDate('records.date_end');
        return "COALESCE({$convertedDateEnd}, records.date_exact)";
    }

    /**
     * Génère la condition SQL pour vérifier si la durée de rétention est expirée
     */
    private function getRetentionExpiredCondition()
    {
        $referenceDate = $this->getReferenceDateExpression();
        return "DATEDIFF(NOW(), {$referenceDate}) > retentions.duration * 365";
    }

    /**
     * Génère la condition SQL pour vérifier si la durée de rétention est encore active
     */
    private function getRetentionActiveCondition()
    {
        $referenceDate = $this->getReferenceDateExpression();
        return "DATEDIFF(NOW(), {$referenceDate}) <= retentions.duration * 365";
    }

    /**
     * Génère la condition SQL pour vérifier si la communicabilité est expirée
     */
    private function getCommunicabilityExpiredCondition()
    {
        $referenceDate = $this->getReferenceDateExpression();
        return "DATEDIFF(NOW(), {$referenceDate}) > communicabilities.duration * 365";
    }

    /**
     * Retourne la date de référence pour le tri (date_end convertie en priorité, sinon date_exact)
     */
    private function addDateOrderBy($query)
    {
        $referenceDate = $this->getReferenceDateExpression();
        return $query->orderByRaw("{$referenceDate} DESC");
    }

    /**
     * Base query pour les relations avec rétention
     */
    private function getRetentionBaseQuery()
    {
        return RecordPhysical::join('activities', 'records.activity_id', '=', 'activities.id')
            ->join('retention_activity', 'activities.id', '=', 'retention_activity.activity_id')
            ->join('retentions', 'retention_activity.retention_id', '=', 'retentions.id')
            ->join('sorts', 'retentions.sort_id', '=', 'sorts.id');
    }

    /**
     * Données communes pour les vues
     */
    private function getCommonViewData()
    {
        return [
            'slipStatuses' => SlipStatus::all(),
            'statuses' => RecordStatus::all(),
            'terms' => [],
            'users' => User::select('id', 'name')->get(),
            'organisations' => Organisation::select('id', 'name')->get(),
        ];
    }

    /**
     * Documents à conserver définitivement - période de rétention non écoulée
     * Sort = C et (date_end + durée rétention) > aujourd'hui
     */
    public function recordToRetain()
    {
        $title = "à conserver - période de rétention active";

        $records = $this->addDateOrderBy(
            $this->getRetentionBaseQuery()
                ->where('sorts.code', 'C')
                ->whereRaw($this->getRetentionActiveCondition())
                ->select(self::RECORDS_SELECT)
                ->with(['activity', 'status', 'level', 'user'])
        )->paginate(15);

        return view('records.index', array_merge(
            compact('records', 'title'),
            $this->getCommonViewData()
        ));
    }

    /**
     * Documents à transférer aux archives historiques - période de communicabilité écoulée
     * (date_end + durée communicabilité) < aujourd'hui
     */
    public function recordToTransfer()
    {
        $title = "à transférer aux archives historiques - communicabilité écoulée";

        $records = $this->addDateOrderBy(
            RecordPhysical::join('activities', 'records.activity_id', '=', 'activities.id')
                ->join('communicabilities', 'activities.communicability_id', '=', 'communicabilities.id')
                ->whereRaw($this->getCommunicabilityExpiredCondition())
                ->select(self::RECORDS_SELECT)
                ->with(['activity', 'status', 'level', 'user'])
        )->paginate(15);

        return view('records.index', array_merge(
            compact('records', 'title'),
            $this->getCommonViewData()
        ));
    }

    /**
     * Documents à trier - période de rétention écoulée avec sort = T
     * Sort = T et (date_end + durée rétention) < aujourd'hui
     */
    public function recordToSort()
    {
        $title = "à trier - durée de rétention écoulée";

        $records = $this->addDateOrderBy(
            $this->getRetentionBaseQuery()
                ->where('sorts.code', 'T')
                ->whereRaw($this->getRetentionExpiredCondition())
                ->select(self::RECORDS_SELECT)
                ->with(['activity', 'status', 'level', 'user'])
        )->paginate(15);

        return view('records.index', array_merge(
            compact('records', 'title'),
            $this->getCommonViewData()
        ));
    }

    /**
     * Documents à archiver définitivement - période de rétention écoulée avec sort = C
     * Sort = C et (date_end + durée rétention) < aujourd'hui
     */
    public function recordToStore()
    {
        $title = "à archiver définitivement - durée de rétention écoulée";

        $records = $this->addDateOrderBy(
            $this->getRetentionBaseQuery()
                ->where('sorts.code', 'C')
                ->whereRaw($this->getRetentionExpiredCondition())
                ->select(self::RECORDS_SELECT)
                ->with(['activity', 'status', 'level', 'user'])
        )->paginate(15);

        return view('records.index', array_merge(
            compact('records', 'title'),
            $this->getCommonViewData()
        ));
    }

    /**
     * Documents en attente de conservation - période de rétention non écoulée avec sort = C
     * Sort = C et (date_end + durée rétention) > aujourd'hui
     * NOTE: Fonction identique à recordToRetain mais avec un titre différent pour un contexte différent
     */
    public function recordToKeep()
    {
        $title = "en attente de conservation - période de rétention active";

        $records = $this->addDateOrderBy(
            $this->getRetentionBaseQuery()
                ->where('sorts.code', 'C')
                ->whereRaw($this->getRetentionActiveCondition())
                ->select(self::RECORDS_SELECT)
                ->with(['activity', 'status', 'level', 'user'])
        )->paginate(15);

        return view('records.index', array_merge(
            compact('records', 'title'),
            $this->getCommonViewData()
        ));
    }

    /**
     * Documents à éliminer - période de rétention écoulée avec sort = E
     * Sort = E et (date_end + durée rétention) < aujourd'hui
     */
    public function recordToEliminate()
    {
        $title = "à éliminer - durée de rétention écoulée";

        $records = $this->addDateOrderBy(
            $this->getRetentionBaseQuery()
                ->where('sorts.code', 'E')
                ->whereRaw($this->getRetentionExpiredCondition())
                ->select(self::RECORDS_SELECT)
                ->with(['activity', 'status', 'level', 'user'])
        )->paginate(15);

        return view('records.index', array_merge(
            compact('records', 'title'),
            $this->getCommonViewData()
        ));
    }

    /**
     * Calcule les données du cycle de vie pour un enregistrement donné
     * @param RecordPhysical $record
     * @return array
     */
    public function getLifecycleData($record)
    {
        // Calcul de la date de référence (date_exact en priorité, sinon date_end)
        $referenceDate = $record->date_exact ?? $record->date_end;

        // Conversion de la date selon le format si nécessaire
        if ($referenceDate && !$record->date_exact) {
            try {
                switch ($record->date_format) {
                    case 'Y':
                        $referenceDate = $referenceDate . '-12-31';
                        break;
                    case 'M':
                        $referenceDate = str_replace('/', '-', $referenceDate) . '-01';
                        break;
                    case 'D':
                        $referenceDate = str_replace('/', '-', $referenceDate);
                        break;
                }
                $referenceDateObj = new \DateTime($referenceDate);
            } catch (\Exception $e) {
                $referenceDateObj = null;
            }
        } elseif ($referenceDate) {
            try {
                $referenceDateObj = new \DateTime($referenceDate);
            } catch (\Exception $e) {
                $referenceDateObj = null;
            }
        } else {
            $referenceDateObj = null;
        }

        // Calcul des délais pour le bureau (communicabilité)
        $communicabilityData = null;
        $bureauExpired = false;
        if ($record->activity && $record->activity->communicability && $referenceDateObj) {
            $communicability = $record->activity->communicability;
            $bureauEndDate = clone $referenceDateObj;
            $bureauEndDate->add(new \DateInterval('P' . $communicability->duration . 'Y'));
            $bureauExpired = new \DateTime() > $bureauEndDate;
            $communicabilityData = [
                'duration' => $communicability->duration,
                'end_date' => $bureauEndDate,
                'expired' => $bureauExpired,
                'name' => $communicability->name ?? 'Communicabilité'
            ];
        }

        // Calcul des délais pour la salle d'archives (rétention la plus longue)
        $retentionData = null;
        $archiveExpired = false;
        if ($record->activity && $record->activity->retentions->isNotEmpty() && $referenceDateObj) {
            $longestRetention = $record->activity->retentions->sortByDesc('duration')->first();
            $archiveEndDate = clone $referenceDateObj;
            $archiveEndDate->add(new \DateInterval('P' . $longestRetention->duration . 'Y'));
            $archiveExpired = new \DateTime() > $archiveEndDate;
            $retentionData = [
                'duration' => $longestRetention->duration,
                'end_date' => $archiveEndDate,
                'expired' => $archiveExpired,
                'sort' => $longestRetention->sort,
                'name' => $longestRetention->name ?? 'Rétention'
            ];
        }

        return [
            'reference_date' => $referenceDateObj,
            'bureau' => $communicabilityData,
            'archive' => $retentionData,
            'summary' => [
                'bureau_expired' => $bureauExpired,
                'archive_expired' => $archiveExpired,
                'current_phase' => $this->getCurrentLifecyclePhase($bureauExpired, $archiveExpired, $retentionData)
            ]
        ];
    }

    /**
     * Détermine la phase actuelle du cycle de vie
     * @param bool $bureauExpired
     * @param bool $archiveExpired
     * @param array|null $retentionData
     * @return string
     */
    private function getCurrentLifecyclePhase($bureauExpired, $archiveExpired, $retentionData)
    {
        if (!$bureauExpired) {
            return 'bureau';
        } elseif ($bureauExpired && !$archiveExpired) {
            return 'archive';
        } elseif ($archiveExpired && $retentionData) {
            switch ($retentionData['sort']->code) {
                case 'C':
                    return 'conservation_definitive';
                case 'T':
                    return 'tri_requis';
                case 'E':
                    return 'elimination_requise';
                default:
                    return 'action_requise';
            }
        }
        return 'indetermine';
    }
}
