<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePhaseRequest;
use App\Http\Requests\UpdatePhaseRequest;
use App\Models\Phase;
use App\Services\PhaseService;
use Illuminate\Http\Request;

class PhaseController extends Controller
{
    protected PhaseService $phase;
    public function __construct(PhaseService $phase){
        $this->phase = $phase;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(StorePhaseRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Phase $phase)
    {
        return $phase->load('tasks.user')->toJson();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Phase $phase)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePhaseRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $this->phase->updatePhase($request);
            return response()->json([
                'success' => true,
                'phase' => $data,
                'message' => "Updated"
            ]);

        }catch (\Throwable $th) {
            return response()->json([
              'success' => false,
              'message' => $th->getMessage()
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->phase->deletePhase($request);
            return response()->json([
                'success' => true,
                'message' => "Deleted"
            ]);

        }catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 400);
        }
    }
}
