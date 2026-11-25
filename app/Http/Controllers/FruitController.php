<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFruitRequest;
use App\Http\Requests\UpdateFruitRequest;
use App\Models\Fruit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FruitController extends Controller
{
    /**
     * Display all fruits.
     */
    public function index()
    {
        $fruits = Fruit::orderBy('name')->get();

        return view('pages.fruits.index', compact('fruits'));
    }

    /**
     * Show the form for creating a fruit.
     */
    public function create()
    {
        return view('pages.fruits.create');
    }

    /**
     * Store a new fruit.
     */
    public function store(StoreFruitRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeImage($request->file('image'));
        }

        Fruit::create($data);

        return redirect()
            ->route('fruits.index')
            ->with('success', 'تم إضافة الصنف بنجاح');
    }

    /**
     * Display the specified fruit.
     */
    public function show(Fruit $fruit)
    {
        return view('pages.fruits.show', compact('fruit'));
    }

    /**
     * Show the form for editing the specified fruit.
     */
    public function edit(Fruit $fruit)
    {
        return view('pages.fruits.edit', compact('fruit'));
    }

    /**
     * Update the specified fruit.
     */
    public function update(UpdateFruitRequest $request, Fruit $fruit)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $this->deleteImage($fruit->image);

            $data['image'] = $this->storeImage($request->file('image'));
        }

        $fruit->update($data);

        return redirect()
            ->route('fruits.index')
            ->with('success', 'تم تحديث الصنف بنجاح');
    }

    /**
     * Delete the specified fruit and its image.
     */
    public function destroy(Fruit $fruit)
    {
        $this->deleteImage($fruit->image);

        $fruit->delete();

        return redirect()
            ->route('fruits.index')
            ->with('success', 'تم حذف الصنف بنجاح.');
    }

    /**
     * Store an uploaded fruit image.
     */
    private function storeImage(UploadedFile $image): string
    {
        $filename = time() . '_' . $image->getClientOriginalName();

        $image->storeAs('fruits', $filename, 'public');

        return $filename;
    }

    /**
     * Delete a fruit image if it exists.
     */
    private function deleteImage($filename): void
    {
        if (
            $filename &&
            Storage::disk('public')->exists('fruits/' . $filename)
        ) {
            Storage::disk('public')->delete('fruits/' . $filename);
        }
    }
}
