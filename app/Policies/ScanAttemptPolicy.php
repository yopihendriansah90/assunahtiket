<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ScanAttempt;
use Illuminate\Auth\Access\HandlesAuthorization;

class ScanAttemptPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ScanAttempt');
    }

    public function view(AuthUser $authUser, ScanAttempt $scanAttempt): bool
    {
        return $authUser->can('View:ScanAttempt');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ScanAttempt');
    }

    public function update(AuthUser $authUser, ScanAttempt $scanAttempt): bool
    {
        return $authUser->can('Update:ScanAttempt');
    }

    public function delete(AuthUser $authUser, ScanAttempt $scanAttempt): bool
    {
        return $authUser->can('Delete:ScanAttempt');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ScanAttempt');
    }

    public function restore(AuthUser $authUser, ScanAttempt $scanAttempt): bool
    {
        return $authUser->can('Restore:ScanAttempt');
    }

    public function forceDelete(AuthUser $authUser, ScanAttempt $scanAttempt): bool
    {
        return $authUser->can('ForceDelete:ScanAttempt');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ScanAttempt');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ScanAttempt');
    }

    public function replicate(AuthUser $authUser, ScanAttempt $scanAttempt): bool
    {
        return $authUser->can('Replicate:ScanAttempt');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ScanAttempt');
    }

}