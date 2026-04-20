<?php

namespace Modules\Blog\Policies;

use App\Models\User;
use Modules\Blog\Models\Category;

class CategoryPolicy
{
    public function create(User $user): bool
    {
        return ! is_demo_mode();
    }

    public function update(User $user, Category $category): bool
    {
        return ! is_demo_mode();
    }

    public function delete(User $user, Category $category): bool
    {
        return ! is_demo_mode();
    }

    public function deleteAny(User $user): bool
    {
        return ! is_demo_mode();
    }
}
