<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DocumentationController extends Controller
{
    public function list()
    {
        return view('merchant.documentation.index');
    }

    public function paymentApi()
    {
        return view('merchant.documentation.paymentApi');
    }

    public function statusApi()
    {
        return view('merchant.documentation.statusApi');
    }

    public function webhooks()
    {
        return view('merchant.documentation.webhooks');
    }

    public function dynamicQRPayinApi()
    {
        return view('merchant.documentation.dynamicQR.payinApi');
    }

    public function dynamicQRStatusApi()
    {
        return view('merchant.documentation.dynamicQR.statusApi');
    }

    public function dynamicQRWebhooks()
    {
        return view('merchant.documentation.dynamicQR.webhooks');
    }

    public function staticQRCreateApi()
    {
        return view('merchant.documentation.staticQR.payinQrCreateApi');
    }

    public function staticQRGetApi()
    {
        return view('merchant.documentation.staticQR.payinQrGetApi');
    }

    public function staticQRWebhooks()
    {
        return view('merchant.documentation.staticQR.webhooks');
    }

}
