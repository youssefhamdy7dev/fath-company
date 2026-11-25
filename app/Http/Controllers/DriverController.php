<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDriverRequest;
use App\Http\Requests\UpdateDriverRequest;
use App\Models\Driver;

class DriverController extends Controller
{
    /**
     * Display all drivers.
     */
    public function index()
    {
        $drivers = Driver::orderBy('name')->get();

        return view('pages.drivers.index', compact('drivers'));
    }

    /**
     * Show the form for creating a driver.
     */
    public function create()
    {
        return view('pages.drivers.create');
    }

    /**
     * Store a new driver.
     */
    public function store(StoreDriverRequest $request)
    {
        Driver::create($request->validated());

        return redirect()
            ->route('drivers.index')
            ->with('status', 'تم إضافة السائق بنجاح.');
    }

    /**
     * Display the specified driver.
     */
    public function show(Driver $driver)
    {
        return view('pages.drivers.show', compact('driver'));
    }

    /**
     * Show the form for editing the specified driver.
     */
    public function edit(Driver $driver)
    {
        return view('pages.drivers.edit', compact('driver'));
    }

    /**
     * Update the specified driver.
     */
    public function update(UpdateDriverRequest $request, Driver $driver)
    {
        $driver->update($request->validated());

        return redirect()
            ->route('drivers.index')
            ->with('success', 'تم تحديث بيانات السائق بنجاح.');
    }

    /**
     * Delete the specified driver.
     */
    public function destroy(Driver $driver)
    {
        $driver->delete();

        return redirect()
            ->route('drivers.index')
            ->with('success', 'تم حذف السائق بنجاح.');
    }
}
