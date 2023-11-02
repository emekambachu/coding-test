<?php

namespace App\Services;

use App\Models\Phase;
use Illuminate\Support\Facades\DB;

class PhaseService
{
    public function phase(){
        return new Phase();
    }

    public function phaseById($id){
        return $this->phase()->findOrFail($id);
    }

    public function updatePhase($request){
        $this->phaseById($request->id)->update($request->all());
        return $this->phaseById($request->id);
    }

    public function deletePhase($request): void
    {
        $phase = $this->phase()->with('tasks')->findOrFail($request->id);

        DB::transaction(static function() use($phase)
        {
            $phase->tasks()->each(function($task) {
                $task->delete();
            });
        });

        $phase->delete();
    }
}
