<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }
    public function verifyPassword(Request $request)
    {
        //return Response()->json(['status' => 'success', 'message' => 'Password match']);
        $password = $request->password;
        $user_id = Auth::id();
        $userdetail = User::find($user_id);
        $current_password = $userdetail->transaction_password;
        if (Hash::check($password, $current_password)) {
            return Response()->json(['status' => 'success', 'message' => 'Password match']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Password does not match']);
        }
    }
}
