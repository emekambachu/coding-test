<?php

namespace App\Services;

use App\Models\Task;

class TaskService
{
    public function task(): Task{
        return new Task();
    }

    public function taskById($id){
       return $this->task()->findOrFail($id);
    }

    public function createTask($request)
    {
        $input = $request->all();
        return $this->task()->create($input);
    }


    public function updateTask($request)
    {
        $input = $request->all();
        $this->taskById($request->id)->update($input);
    }
}
