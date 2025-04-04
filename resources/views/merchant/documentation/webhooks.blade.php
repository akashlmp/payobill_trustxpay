<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ config('app.name') }} | Merchant | Payment Document</title>

    <!-- Favicon -->
    <link rel="icon" href="{{asset('assets/img/trustxpay-favicon.png')}}" type="image/x-icon"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css"
        integrity="sha512-5A8nwdMOWrSz20fDsjczgUidUBR8liPYU+WymTZP1lmY9G6Oc7HlZv156XqnsgNUzTyMefFTcsFH/tnJE/+xBg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">

    <!-- Theme CSS -->
    <link id="theme-style" rel="stylesheet" href="{{ asset('assets/documentation/css/theme.css') }}">


    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/atom-one-dark.min.css">

    <style>
        body {
            overflow: hidden;
            height: 100vh;
        }

        .bg-custom{
            background-color: #F7F7F7;
        }

        .left__section {
            overflow-y: scroll;
            height: 100vh;
            padding: 30px 30px 70px 30px;
        }

        .nav-pills .customNavItem {
            position: relative;
            width: 50%;
            /* Set each tab to take 50% width */
            text-align: center;
            padding: 0.5rem;
        }

        .nav-pills .customNavLink.active::after {
            content: '';
            display: block;
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: #0D7CFF;

        }

        .nav-pills .customNavLink.active {
            background: transparent;
            color: #0D7CFF !important;
            font-weight: bold;
        }

        .nav-pills .nav-link {
            color: #FFFFFF;
        }

        .headerFieldsBody,
        .statusFieldsBody tr td:first-child {
            width: 150px !important;
        }

        .statusFieldsBody tr td:nth-child(2) {
            width: 100px !important;
        }

        .statusFieldsBody tr td:nth-child(3) {
            min-width: 300px !important;
        }

        pre {
            border-radius: 20px;
        }

        .custom-div{
            background-color: #FFFFFF;
            border-radius: 10px;
            padding: 30px;
            word-break: break-all;
        }
    </style>
</head>
<body class="docs-page">
    <!-- Header --->
    @include('merchant.documentation.layouts.header')
    <!--- End Header ---->
    <div class="docs-wrapper">
        @include ('merchant.documentation.layouts.sidebar')
        <!--- Main Section ---->
        <div class="docs-content">
            <div class="container-fluid doc__div">
                <div class="row bg-custom">
                    <div class="col-lg-12 left__section">
                        <div class="d-flex  justify-content-start align-items-center">
                            <span class="fw-bold text-dark ms-2 " style="font-size: 40px;">Webhook</span>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row m-0">
                                    <div class="col-md-12 custom-div">
                                        <!-- Introduction --->
                                        <section>
                                            <pre>
                                                <code class="language-json hljs">
{
    "success": true,
    "status": "FAILED",
    "message": "Payout request failed.",
    "transaction_id": "R071730981987Q5LQK",
    "merchant_reference_id": "MRT1730981987",
    "ben_name": "Beneficiary Full Name",
    "ben_account_number": "10002000300040",
    "ben_ifsc": "BARB0KOLKIX",
    "ben_phone_number": 9662229878,
    "ben_bank_name": "Bank of Baroda",
    "transfer_type": "IMPS",
    "amount": 100,
    "signature": "a578341399c4cd8cfb2ad75d6e6caf29b8de992d4f3998b3d965e213216f69db",
    "timestamp": "2024-11-07 12:19:47"
}
                                                </code>
                                            </pre>
                                        </section>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row m-0">
                                    <div class="col-md-12 custom-div">
                                        <section class="mt-2">
                                            <p>You can set webhook url to Settings menu. We will send webhooks to that url whenever any pending transaction updates.</p>
                                            <p>You need to verify signature to make sure webhook is from Trustxpay. For that, create string according to below method and compare with the signature that you received in webhook.</p>

                                            <p><b>Step:1</b></p>
                                            <p>To generate the signature, you need to create string with below parameters in same sequence.</p>

                                            <p>ben_account_number + amount + status + ben_ifsc + merchant_reference_id + secret_key</p>

                                            <p>For example, for below data:</p>

                                            <p>12345678901234 + FAILED  + 100 + HDFC0009444 + ALNR00001 + FaMExY6DyzfWIugQ</p>

                                            <p>The string will be:</p>

                                            <p><b>12345678901234FAILED100HDFC0009444ALNR00001FaMExY6DyzfWIugQ</b></p>

                                            <p><b>Step:2</b></p>
                                            <p>Now, encrypt this string using SHA256 method, this will create encrypted string as below.</p>

                                            <p><b>a578341399c4cd8cfb2ad75d6e6caf29b8de992d4f3998b3d965e213216f69db</b></p>

                                            <p>Compare this string with webhook <b>“signature”</b> parameter.</p>
                                        </section>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/documentation/js/gumshoe.polyfills.min.js') }}"></script>
    <script src="{{ asset('assets/documentation/js/docs.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
    <script>
        hljs.highlightAll();
    </script>
</body>
</html>
