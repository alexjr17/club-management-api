<?php

namespace App\Http\Controllers\ClubDeportivo;

use App\Http\Controllers\Controller;
use App\Models\ClubDeportivo\ParentCache;
use App\Models\UserRole;
use Illuminate\Http\Request;

class ParentCacheController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {}

    public function showByClub(Request $request, int $clubId)
    {
        $limit = $request->input("per_page") ?? 12;
        $sortType = $request->has('ascending') ? ($request->input('ascending') == 1 ? 'asc' : 'desc') : 'asc';
        $search = $request->input("query");
        $is_active = $request->input("is_active");
        $date = $request->input("date");
        $especialidad = $request->input("especialidad");
        $calificacionMinima = $request->input("calificacion_minima");

        $query = UserRole::with(['user', 'parentCache'])
            ->whereHas('role', function ($query) {
                $query->where('nombre', 'padre');
            })
            ->where('club_id', $clubId);

        $parents = $query->paginate($limit);

        return response()->json($parents);
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ParentCache $parentCache)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ParentCache $parentCache)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ParentCache $parentCache)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ParentCache $parentCache)
    {
        //
    }
}
