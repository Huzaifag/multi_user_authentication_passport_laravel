<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function adminDashboard()
{
    return response()->json(['message' => 'Welcome to the Admin Dashboard']);
}

public function managerDashboard()
{
    return response()->json(['message' => 'Welcome to the Manager Dashboard']);
}

public function employeeDashboard()
{
    return response()->json(['message' => 'Welcome to the Employee Dashboard']);
}
}
