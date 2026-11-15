<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\InfoPopup;
use Illuminate\Auth\Access\HandlesAuthorization;

class InfoPopupPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:InfoPopup');
    }

    public function view(AuthUser $authUser, InfoPopup $infoPopup): bool
    {
        return $authUser->can('View:InfoPopup');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:InfoPopup');
    }

    public function update(AuthUser $authUser, InfoPopup $infoPopup): bool
    {
        return $authUser->can('Update:InfoPopup');
    }

    public function delete(AuthUser $authUser, InfoPopup $infoPopup): bool
    {
        return $authUser->can('Delete:InfoPopup');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:InfoPopup');
    }

    public function restore(AuthUser $authUser, InfoPopup $infoPopup): bool
    {
        return $authUser->can('Restore:InfoPopup');
    }

    public function forceDelete(AuthUser $authUser, InfoPopup $infoPopup): bool
    {
        return $authUser->can('ForceDelete:InfoPopup');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:InfoPopup');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:InfoPopup');
    }

    public function replicate(AuthUser $authUser, InfoPopup $infoPopup): bool
    {
        return $authUser->can('Replicate:InfoPopup');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:InfoPopup');
    }

}