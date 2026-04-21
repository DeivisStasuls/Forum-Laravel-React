<?php

namespace App\Policies;

use App\Models\PrivateGroup;
use App\Models\User;

class PrivateGroupPolicy
{
    public function view(User $user, PrivateGroup $privateGroup): bool
    {
        return $privateGroup->members()->where('users.id', $user->id)->exists();
    }

    public function viewMessages(User $user, PrivateGroup $privateGroup): bool
    {
        return $this->view($user, $privateGroup);
    }

    public function createMessage(User $user, PrivateGroup $privateGroup): bool
    {
        return $this->view($user, $privateGroup);
    }

    public function leave(User $user, PrivateGroup $privateGroup): bool
    {
        return $this->view($user, $privateGroup);
    }

    public function update(User $user, PrivateGroup $privateGroup): bool
    {
        return $privateGroup->created_by === $user->id;
    }

    public function addMember(User $user, PrivateGroup $privateGroup): bool
    {
        return $this->update($user, $privateGroup);
    }

    public function removeMember(User $user, PrivateGroup $privateGroup): bool
    {
        return $this->update($user, $privateGroup);
    }

    public function delete(User $user, PrivateGroup $privateGroup): bool
    {
        return $this->update($user, $privateGroup);
    }
}
