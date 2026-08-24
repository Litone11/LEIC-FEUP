<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SearchService;

class SearchController extends Controller
{
    protected SearchService $search;

    public function __construct(SearchService $service)
    {
        $this->search = $service;
    }

    /**
     * ======================================================
     * DASHBOARD GLOBAL SEARCH (INVokable)
     * Route: GET /search
     * ======================================================
     */
    public function __invoke(Request $request)
    {
        $userId = $this->userId();
        $search = trim($request->query('search', ''));
        $type   = $request->query('type', 'all');

        if ($search === '') {
            return $request->ajax()
                ? view('partials.dashboard.search_results', [
                    'projects' => [],
                    'tasks'    => [],
                ])
                : response()->json([
                    'projects' => [],
                    'tasks'    => [],
                ]);
        }

        $results = match ($type) {
            'projects' => [
                'projects' => $this->search->searchProjects($userId, $search),
                'tasks'    => [],
            ],
            'tasks' => [
                'projects' => [],
                'tasks'    => $this->search->searchTasks($userId, $search),
            ],
            default => $this->search->searchAll($userId, $search),
        };

        // AJAX → return blade partial
        if ($request->ajax()) {
            return view('partials.dashboard.search_results', $results);
        }

        // API fallback
        return response()->json($results);
    }

    /**
     * ======================================================
     * PROJECTS PAGE AJAX SEARCH
     * Route: GET /search/projects
     * ======================================================
     */
    public function projects(Request $request)
    {
        $userId = $this->userId();
        $search = trim($request->query('search', ''));

        if ($search === '') {
            return response('');
        }

        $projects = $this->search
            ->searchProjects($userId, $search)
            ->map(fn ($project) => $this->toProjectCard($project));

        return response(
            $projects->map(fn ($project) =>
                view('partials.projects.project_card', ['project' => $project])->render()
            )->implode('')
        );
    }

    /**
     * Normalize project for project_card
     */
    private function toProjectCard($project): array
    {
        return [
            'id'          => $project->project_id,
            'name'        => $project->name,
            'description' => $project->description,
            'progress'    => $project->progress,
            'date'        => $project->formatted_date,
            'members'     => $project->members,
            'is_favorite' => $project->users->first()?->pivot?->is_favorite ?? false,
            'is_coordinator' => $project->isCoordinatorFor($this->userId()),
            'is_archived'    => (bool) $project->is_archived,
        ];
    }
}
