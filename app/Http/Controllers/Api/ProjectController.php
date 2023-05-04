<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $results = Project::with('type', 'technologies')->paginate(9);

        return response()->json([
            'success' => true,
            'results' => $results
        ]);
    }

    public function show($slug)
    {

        $project = Project::with([
            'type.projects' => function (Builder $query) {
                $query->orderBy('created_at', 'desc')->limit(3);
            }

        ])->where('slug', $slug)->first();

        return response()->json([
            'success' => true,
            'project' => $project
        ]);
    }
}
