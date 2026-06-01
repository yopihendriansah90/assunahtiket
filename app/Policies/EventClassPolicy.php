<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\EventClass;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventClassPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EventClass');
    }

    public function view(AuthUser $authUser, EventClass $eventClass): bool
    {
        return $authUser->can('View:EventClass');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EventClass');
    }

    public function update(AuthUser $authUser, EventClass $eventClass): bool
    {
        return $authUser->can('Update:EventClass');
    }

    public function delete(AuthUser $authUser, EventClass $eventClass): bool
    {
        return $authUser->can('Delete:EventClass');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:EventClass');
    }

    public function restore(AuthUser $authUser, EventClass $eventClass): bool
    {
        return $authUser->can('Restore:EventClass');
    }

    public function forceDelete(AuthUser $authUser, EventClass $eventClass): bool
    {
        return $authUser->can('ForceDelete:EventClass');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EventClass');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EventClass');
    }

    public function replicate(AuthUser $authUser, EventClass $eventClass): bool
    {
        return $authUser->can('Replicate:EventClass');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EventClass');
    }

}