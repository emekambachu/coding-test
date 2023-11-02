<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
<<<<<<< HEAD
=======
use App\Services\TaskService;
use Illuminate\Http\Request;
>>>>>>> dev

class TaskController extends Controller
{

    public function kanban()
    {
        return view('tasks.index');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return \App\Models\Phase::with('tasks.user')->get();
    }

    /**
     * Display a listing of the Users resource.
     */
    public function users()
    {
        return \App\Models\User::all();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        // Create a new task from the $request
        $task = Task::create($request->validated());
    }

    /**
     * Display the specified resource.
     */
<<<<<<< HEAD
    public function show(Task $task)
    {
        //
    }
=======
    public function show(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $this->task->task()->with('user')->findOrFail($request->id);
            return response()->json([
                'success' => true,
                'task' => new TaskResource($data),
            ]);
>>>>>>> dev

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        Task::destroy($task->id);
    }
}
