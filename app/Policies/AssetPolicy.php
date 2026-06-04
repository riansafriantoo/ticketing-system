<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;

class AssetPolicy
{
    public function viewAny(User $user): bool  { 
        return $user->isAgent(); 
    }

    public function view(User $user, Asset $asset): bool { 
        return $user->isAgent(); 
    }

    public function create(User $user): bool   { 
        return $user->isAgent(); 
    }

    public function update(User $user, Asset $asset): bool  {
         return $user->isAgent(); 
    }

    public function delete(User $user, Asset $asset): bool  {
         return $user->isAdmin(); 
    }

    public function assign(User $user, Asset $asset): bool  {
         return $user->isAgent(); 
    }

    public function retire(User $user, Asset $asset): bool  {
         return $user->isAdmin(); 
    }
    
}