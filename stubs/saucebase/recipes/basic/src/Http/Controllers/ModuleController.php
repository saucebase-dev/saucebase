<?php

namespace ___MODULE_NAMESPACE___\___Module___\Http\Controllers;

use Inertia\Inertia;

class ___Module___Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('{Module}::Index', [
            'message' => 'Welcome to {Module} Module',
            'module' => '{module}',
        ]);
    }
}
