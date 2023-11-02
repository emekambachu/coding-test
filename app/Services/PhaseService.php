<?php

namespace App\Services;

use App\Models\Phase;

class PhaseService
{
    public function phase(){
        return new Phase();
    }
}
