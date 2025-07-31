<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Models\Record;
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
    const RETENTION_DURATION_EXPIRED = 'DATEDIFF(NOW(), COALESCE(records.date_end, records.date_exact)) > retentions.duration * 365';
    const RETENTION_DURATION_ACTIVE = 'DATEDIFF(NOW(), COALESCE(records.date_end, records.date_exact)) <= retentions.duration * 365';
    const COMMUNICABILITY_EXPIRED = 'DATEDIFF(NOW(), COALESCE(records.date_end, records.date_exact)) > communicabilities.duration * 365';

    /**
     * Retourne la date de référence pour le tri (date_end en priorité, sinon date_exact)
     */
    private function addDateOrderBy($query)
    {
        return $query->orderByRaw('COALESCE(records.date_end, records.date_exact) DESC');
    }

    /**
     * Base query pour les relations avec rétention
     */
    private function getRetentionBaseQuery()
    {
        return Record::join('activities', 'records.activity_id', '=', 'activities.id')
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
    /**
     * Documents à conserver définitivement - période de rétention non écoulée
     * Sort = C et (date_end + durée rétention) > aujourd'hui
     */
    public function recordToRetain()
    {
        $title = "à conserver - période de rétention active";

        // Debug étape par étape
        // Étape 1: Tester avec tous les records
//        $records = Record::with(['activity', 'status', 'level', 'user'])
//            ->paginate(15);

        // Étape 2: Si ça marche, décommenter progressivement les lignes ci-dessous

//        $records = Record::join('activities', 'records.activity_id', '=', 'activities.id')
//            ->select('records.*')
//            ->with(['activity', 'status', 'level', 'user'])
//            ->paginate(15);
//

        // Étape 3: Ajouter les autres joins
        $records = Record::join('activities', 'records.activity_id', '=', 'activities.id')
            ->join('retention_activity', 'activities.id', '=', 'retention_activity.activity_id')
            ->join('retentions', 'retention_activity.retention_id', '=', 'retentions.id')
            ->join('sorts', 'retentions.sort_id', '=', 'sorts.id')
            ->select('records.*')
            ->with(['activity', 'status', 'level', 'user'])
            ->paginate(15);


        // Étape 4: Ajouter les conditions
        /*
        $records = Record::join('activities', 'records.activity_id', '=', 'activities.id')
            ->join('retention_activity', 'activities.id', '=', 'retention_activity.activity_id')
            ->join('retentions', 'retention_activity.retention_id', '=', 'retentions.id')
            ->join('sorts', 'retentions.sort_id', '=', 'sorts.id')
            ->where('sorts.code', 'C')
            ->whereRaw('DATEDIFF(NOW(), COALESCE(records.date_end, records.date_exact)) <= retentions.duration * 365')
            ->select('records.*')
            ->with(['activity', 'status', 'level', 'user'])
            ->orderByRaw('COALESCE(records.date_end, records.date_exact) DESC')
            ->paginate(15);
        */

        // Debug: afficher le nombre de résultats
         dd($records->total()); // Décommenter pour voir le nombre de résultats

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
            Record::join('activities', 'records.activity_id', '=', 'activities.id')
                ->join('communicabilities', 'activities.communicability_id', '=', 'communicabilities.id')
                ->whereRaw(self::COMMUNICABILITY_EXPIRED)
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
                ->whereRaw(self::RETENTION_DURATION_EXPIRED)
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
                ->whereRaw(self::RETENTION_DURATION_EXPIRED)
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
                ->whereRaw(self::RETENTION_DURATION_ACTIVE)
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
                ->whereRaw(self::RETENTION_DURATION_EXPIRED)
                ->select(self::RECORDS_SELECT)
                ->with(['activity', 'status', 'level', 'user'])
        )->paginate(15);

        return view('records.index', array_merge(
            compact('records', 'title'),
            $this->getCommonViewData()
        ));
    }
}
