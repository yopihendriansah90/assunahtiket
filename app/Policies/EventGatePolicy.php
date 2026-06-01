<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\EventGate;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventGatePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EventGate');
    }

    public function view(AuthUser $authUser, EventGate $eventGate): bool
    {
        return $authUser->can('View:EventGate');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EventGate');
    }

    public function update(AuthUser $authUser, EventGate $eventGate): bool
    {
        return $authUser->can('Update:EventGate');
    }

    public function delete(AuthUser $authUser, EventGate $eventGate): bool
    {
        return $authUser->can('Delete:EventGate');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:EventGate');
    }

    public function restore(AuthUser $authUser, EventGate $eventGate): bool
    {
        return $authUser->can('Restore:EventGate');
    }

    public function forceDelete(AuthUser $authUser, EventGate $eventGate): bool
    {
        return $authUser->can('ForceDelete:EventGate');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EventGate');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EventGate');
    }

    public function replicate(AuthUser $authUser, EventGate $eventGate): bool
    {
        return $authUser->can('Replicate:EventGate');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EventGate');
    }

}