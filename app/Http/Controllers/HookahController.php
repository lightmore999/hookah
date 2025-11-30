<?php

namespace App\Http\Controllers;

use App\Models\Hookah;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HookahController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): View
    {
        $hookahs = Hookah::latest()->get();
        $hookahsData = $hookahs->map(function (Hookah $hookah) {
            return [
                'id' => $hookah->id,
                'name' => $hookah->name,
                'price' => $hookah->price,
                'cost' => $hookah->cost,
                'hookah_maker_rate' => $hookah->hookah_maker_rate,
                'administrator_rate' => $hookah->administrator_rate,
            ];
        })->values();

        return view('hookahs.index', compact('hookahs', 'hookahsData'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): View
    {
        return view('hookahs.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'hookah_maker_rate' => 'required|numeric|min:0',
            'administrator_rate' => 'required|numeric|min:0',
        ]);

        Hookah::create($validated);

        return redirect()->route('hookahs.index')
            ->with('success', 'Кальян успешно добавлен.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Hookah  $hookah
     * @return \Illuminate\Http\Response
     */
    public function edit(Hookah $hookah): View
    {
        return view('hookahs.edit', compact('hookah'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Hookah  $hookah
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Hookah $hookah): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'hookah_maker_rate' => 'required|numeric|min:0',
            'administrator_rate' => 'required|numeric|min:0',
        ]);

        $hookah->update($validated);

        return redirect()->route('hookahs.index')
            ->with('success', 'Кальян успешно обновлен.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Hookah  $hookah
     * @return \Illuminate\Http\Response
     */
    public function destroy(Hookah $hookah): RedirectResponse
    {
        $hookah->delete();

        return redirect()->route('hookahs.index')
            ->with('success', 'Кальян успешно удален.');
    }
}
