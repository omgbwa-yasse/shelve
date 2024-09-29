<?php

namespace App\Policies;

use App\Models\User;
namespace App\Http\Controllers;
use App\Models\Record;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\HandlesAuthorization;

class RecordPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Record $record)
    {

        return $user->current_organisation_id === $record->activity->organisation_id
            && $user->hasPermissionTo('view records');
    }



    public function update(User $user, Record $record)
    {

        return $user->current_organisation_id === $record->activity->organisation_id
            && $user->hasPermissionTo('update records');
    }


    public function delete(User $user, Record $record)
    {

        return $user->current_organisation_id === $record->activity->organisation_id
            && $user->hasPermissionTo('delete records');
    }


    public function store(User $user, Request $request)
    {
        foreach($user->currentOrganisation->activities as $activity){
            return $activity->id === $request->activity_id && $user->hasPermissionTo('delete records');;
        }
    }
}


