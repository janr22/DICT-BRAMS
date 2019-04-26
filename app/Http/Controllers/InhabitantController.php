<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Inhabitant;

class InhabitantController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $inhabitants = Inhabitant::paginate();
        return $inhabitants;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return Inhabitant::create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Inhabitant  $inhabitant
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $household = Household::select('name')->get();
        return $household;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Inhabitant  $inhabitant
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $inhabitants = Inhabitant::findOrFail($id);
        $inhabitants->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Inhabitant  $inhabitant
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $inhabitants = Inhabitant::findOrFail($id);
        $inhabitants->delete();
    }
}
