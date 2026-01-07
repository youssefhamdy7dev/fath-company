<?php

namespace App\Http\Controllers;

use App\Models\Fruit;
use App\Http\Requests\StoreFruitRequest;
use App\Http\Requests\UpdateFruitRequest;
use Illuminate\Support\Facades\Storage;

class FruitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fruits = Fruit::orderBy('name')->get();
        return view('pages.fruits.index', compact('fruits'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.fruits.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFruitRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('fruits', $filename, 'public');
            $data['image'] = $filename;
        }

        Fruit::create($data);
        return redirect()->route('fruits.index')->with('success', 'تم إضافة الصنف بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Fruit $fruit)
    {
        return view('pages.fruits.show', compact('fruit'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Fruit $fruit)
    {
        return view('pages.fruits.edit', compact('fruit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFruitRequest $request, Fruit $fruit)
    {
        $data = $request->validated();
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($fruit->image && Storage::disk('public')->exists('fruits/' . $fruit->image)) {
                Storage::disk('public')->delete('fruits/' . $fruit->image);
            }

            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('fruits', $filename, 'public');
            $data['image'] = $filename;
        }

        $fruit->update($data);

        return redirect()->route('fruits.index')->with('success', 'تم تحديث الصنف بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Fruit $fruit)
    {
        // Delete the image file if it exists
        if ($fruit->image && Storage::disk('public')->exists('fruits/' . $fruit->image)) {
            Storage::disk('public')->delete('fruits/' . $fruit->image);
        }

        // Delete the fruit record
        $fruit->delete();

        // Redirect back with success message
        return redirect()->route('fruits.index')->with('success', 'تم حذف الصنف بنجاح.');
    }
}
