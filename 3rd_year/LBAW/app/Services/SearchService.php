<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Collection;

class SearchService
{
    protected function makeTsQuery(string $search): string
    {
        return implode(' & ', array_map(
            fn ($w) => "$w:*",
            explode(' ', trim($search))
        ));
    }

    // ---------------- PROJECT SEARCH ----------------
    public function searchProjects(int $userId, string $search): Collection
    {
        if ($search === '') return collect();

        $tsQuery = $this->makeTsQuery($search);

        return Project::query()
            ->forUser($userId)
            ->withTaskStats()         
            ->withMemberCount()
            ->withUserPivotFor($userId)
            ->where(function ($q) use ($tsQuery, $search) {
                $q->whereRaw("project.tsvectors @@ to_tsquery('english', ?)", [$tsQuery])
                ->orWhere('name', 'ILIKE', "%{$search}%")
                ->orWhere('description', 'ILIKE', "%{$search}%");
            })
            ->get();
    }


    // ---------------- TASK SEARCH ----------------
    public function searchTasks(int $userId, string $search): Collection
    {
        if ($search === '') return collect();

        $tsQuery = $this->makeTsQuery($search);

        return Task::query()
            ->with('project:project_id,name')
            ->whereHas('project.users', fn ($q) =>
                $q->where('users.user_id', $userId)
            )
            ->where(function ($q) use ($tsQuery, $search) {
                $q->whereRaw("task.tsvectors @@ to_tsquery('english', ?)", [$tsQuery])
                  ->orWhere('task.name', 'ILIKE', "%{$search}%")
                  ->orWhere('task.description', 'ILIKE', "%{$search}%");
            })
            ->get();
    }

    // ---------------- BOTH ----------------
    public function searchAll(int $userId, string $search): array
    {
        return [
            'projects' => $this->searchProjects($userId, $search),
            'tasks'    => $this->searchTasks($userId, $search),
        ];
    }
}
