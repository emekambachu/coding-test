<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    protected TaskService $task;
    public function __construct(TaskService $task){
        $this->task = $task;
    }

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
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        try {
            $data = $this->task->createTask($request);
            return response()->json([
                'success' => true,
                'task' => $data,
            ]);

        }catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);

        }

        // Create a new task from the $request
//        $task = Task::create($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $this->task->task()->with('user')->findOrFail($request->id);
            return response()->json([
                'success' => true,
                'task' => new TaskResource($data),
            ]);

        }catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);

        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->task->updateTask($request);
            return response()->json([
                'success' => true,
                'message' => 'Updated',
            ]);

        }catch (\Throwable $th) {
            return response()->json([
              'success' => false,
              'message' => $th->getMessage()
            ]);

        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        Task::destroy($task->id);
    }
}
