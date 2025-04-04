<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Requests\PasswordExpiredRequest;
use Illuminate\Support\Facades\Hash;


class ExpiredPasswordController extends Controller
{
    public function expired()
    {
        return view('auth.passwords.expired');
    }

    public function postExpired(PasswordExpiredRequest $request)
    {
        // Checking current password
        if (!Hash::check($request->current_password, $request->user()->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Current password is not correct']);
        }
        // if (!Hash::check($request->transaction_current_password, $request->user()->transaction_password)) {
        //     return redirect()->back()->withErrors(['transaction_current_password' => 'Current transaction password is not correct']);
        // }
        // if ($request->password==$request->transaction_password) {
        //     return redirect()->back()->withErrors(['transaction_password' => 'Login password and transaction password can not same']);
        // }
        $request->user()->update([
            'password' => bcrypt($request->password),
            'password_changed_at' => Carbon::now()->toDateTimeString()
            // 'transaction_password' => bcrypt($request->transaction_password),
        ]);
        return redirect()->back()->with(['status' => 'Password changed successfully']);
    }
}
