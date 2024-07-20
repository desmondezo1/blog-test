<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $user2): bool
    {
        // Allow admins to delete any user
        if ($user->isAdministrator()) {
            return true;
        }
    
        // Allow users to delete their own account
        return $user->id === $user2->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $user2): bool
    {
        // Allow admins to delete any user
        if ($user->isAdministrator()) {
            return true;
        }
 
        // Allow users to delete their own account
        return $user->id === $user2->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        //
    }
}