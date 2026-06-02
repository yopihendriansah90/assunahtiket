<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Checkin;
use Illuminate\Auth\Access\HandlesAuthorization;

class CheckinPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Checkin');
    }

    public function view(AuthUser $authUser, Checkin $checkin): bool
    {
        return $authUser->can('View:Checkin');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Checkin');
    }

    public function update(AuthUser $authUser, Checkin $checkin): bool
    {
        return $authUser->can('Update:Checkin');
    }

    public function delete(AuthUser $authUser, Checkin $checkin): bool
    {
        return $authUser->can('Delete:Checkin');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Checkin');
    }

    public function restore(AuthUser $authUser, Checkin $checkin): bool
    {
        return $authUser->can('Restore:Checkin');
    }

    public function forceDelete(AuthUser $authUser, Checkin $checkin): bool
    {
        return $authUser->can('ForceDelete:Checkin');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Checkin');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Checkin');
    }

    public function replicate(AuthUser $authUser, Checkin $checkin): bool
    {
        return $authUser->can('Replicate:Checkin');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Checkin');
    }

}