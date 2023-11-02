<?php

namespace App\Services;

use App\Models\Phase;

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
        $this->phaseById($request->id)->delete();
    }
}
