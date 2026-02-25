<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NewFeatureService;

class NewFeatureController extends Controller
{
    protected $service;

    public function __construct(NewFeatureService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return response()->json(['message' => 'New Feature Placeholder']);
    }
}
