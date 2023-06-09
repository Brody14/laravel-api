<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Technology;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {


        //creazione vista cestino
        $trashed = $request->input('trashed');

        if ($trashed) {
            $projects = Project::onlyTrashed()->get();
        } else {
            $projects = Project::all();
        }

        //numero elementi nel cestino
        $in_trash = Project::onlyTrashed()->count();

        return view('projects.index', compact('projects', 'in_trash'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $types = Type::orderBy('name')->get();
        $technologies = Technology::orderBy('name')->get();

        return view('projects.create', compact('types', 'technologies'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreProjectRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProjectRequest $request)
    {
        $validated = $request->validated();

        $validated['slug'] = Str::slug($validated['title']);
        $validated['user_id'] = Auth::id();

        if ($request->hasFile('image')) {
            $image_path = Storage::put('uploads', $validated['image']);
            $validated['cover'] = $image_path;
        }

        $new_project = Project::create($validated);

        if (isset($validated['technologies'])) {
            $new_project->technologies()->attach($validated['technologies']);
        }

        return to_route('projects.show', $new_project)->with('success', 'Project created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project)
    {
        $types = Type::orderBy('name')->get();
        return view('projects.show', compact('project', 'types'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function edit(Project $project)
    {
        $types = Type::orderBy('name')->get();
        $technologies = Technology::orderBy('name')->get();

        return view('projects.edit', compact('project', 'types', 'technologies'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateProjectRequest  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $validated = $request->validated();

        if ($validated['title'] !== $project->title) {
            $validated['slug'] =  Str::slug($validated['title']);
        }

        if ($request->hasFile('image')) {
            $image_path = Storage::put('uploads', $validated['image']);
            $validated['cover'] = $image_path;

            if ($project->cover && Storage::exists('image')) {
                Storage::delete($project->cover);
            }
        }

        $project->update($validated);

        if (isset($validated['technologies'])) {
            $project->technologies()->sync($validated['technologies']);
        } else {
            $project->technologies()->sync([]);
        }

        return to_route('projects.show', $project)->with('update', 'Project updated');
    }

    public function restore(Project $project)
    {
        if ($project->trashed()) {
            $project->restore();
        }

        return back()->with('success', 'Project restored successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {

        if ($project->trashed()) {

            // if ($project->cover && Storage::exists('image')) {
            //     Storage::delete($project->cover);
            // }

            $project->forceDelete();
            return to_route('projects.index')->with('message', 'Project deleted');
        }

        $project->delete();
        return back()->with('moved', 'Project moved to trash');
    }

    public function __construct()
    {
        //gestione autorizzazioni
        $this->authorizeResource(Project::class, 'project');
    }
}
