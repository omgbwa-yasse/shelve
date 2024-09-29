<?php

namespace App\Policies;

use App\Models\Record;
use App\Models\User;
use App\Models\UserOrganisationRole;
use Illuminate\Auth\Access\HandlesAuthorization;

class RecordPolicy
{
    use HandlesAuthorization;



    public function show(User $user, Record $record)
    {

        return true;
    }




    public function create(User $user)
    {
       /*  $userOrganisationRoles = UserOrganisationRole::where('user_id', $user->id)->get();

        foreach($userOrganisationRoles as $userOrganisationRole){
            if($user->current_organisation_id === $userOrganisationRole->organisation_id && $user->hasPermissionTo('record_create')){
                return true;
            }
        }
        */
        return true;
    }




    public function update(User $user, Record $record)
    {
        return $user->current_organisation_id === $record->activity->organisation_id
            && $user->hasPermissionTo('record_update');
    }




    public function delete(User $user, Record $record)
    {
        return $user->current_organisation_id === $record->activity->organisation_id
            && $user->hasPermissionTo('record_delete');
    }


}
