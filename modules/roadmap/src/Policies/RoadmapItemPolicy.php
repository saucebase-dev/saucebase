<?php

namespace Modules\Roadmap\Policies;

use App\Models\User;
use Modules\Roadmap\Models\RoadmapItem;

class RoadmapItemPolicy
{
    public function create(User $user): bool
    {
        return ! is_demo_mode();
    }

    public function update(User $user, RoadmapItem $roadmapItem): bool
    {
        return ! is_demo_mode();
    }

    public function delete(User $user, RoadmapItem $roadmapItem): bool
    {
        return ! is_demo_mode();
    }

    public function deleteAny(User $user): bool
    {
        return ! is_demo_mode();
    }
}
