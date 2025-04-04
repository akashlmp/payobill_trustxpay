<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return redirect()->route('merchant.transaction');
        $params['page_title'] = 'Dashboard';
        return view('merchant.dashboard.index',$params);
    }
}
