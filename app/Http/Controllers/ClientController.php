<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;

class ClientController extends Controller
{
    /**
     * Display all clients.
     */
    public function index()
    {
        $clients = Client::orderBy('name')->get();

        return view('pages.clients.index', compact('clients'));
    }

    /**
     * Show the form for creating a client.
     */
    public function create()
    {
        return view('pages.clients.create');
    }

    /**
     * Store a new client.
     */
    public function store(StoreClientRequest $request)
    {
        Client::create($request->validated());

        return redirect()
            ->route('clients.index')
            ->with('success', 'تمت إضافة العميل بنجاح.');
    }

    /**
     * Display the specified client.
     */
    public function show(Client $client)
    {
        return view('pages.clients.show', compact('client'));
    }

    /**
     * Show the form for editing the specified client.
     */
    public function edit(Client $client)
    {
        return view('pages.clients.edit', compact('client'));
    }

    /**
     * Update the specified client.
     */
    public function update(UpdateClientRequest $request, Client $client)
    {
        $client->update($request->validated());

        return redirect()
            ->route('clients.index')
            ->with('success', 'تم تعديل بيانات العميل بنجاح.');
    }

    /**
     * Delete the specified client.
     */
    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()
            ->route('clients.index')
            ->with('success', 'تم حذف العميل بنجاح.');
    }
}
