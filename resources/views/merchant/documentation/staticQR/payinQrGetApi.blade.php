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
        @include ('merchant.documentation.staticQR.sidebar')
        <!--- Main Section ---->
        <div class="docs-content">
            <div class="container-fluid doc__div">
                <div class="row bg-custom">
                    <div class="col-lg-12 left__section">
                        <div class="d-flex  justify-content-start align-items-center">
                            <span class="fw-bold text-dark ms-2 " style="font-size: 40px;"> Get QR Code API</span>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row m-0">
                                    <div class="col-md-12 custom-div">
                                        <!-- Introduction --->
                                        <section class="mt-2">
                                            <h4>Introduction</h4>
                                            <p>This document provides how to integrate trustxpay get qr code API.</p>
                                        </section>

                                        <section class="mt-4">
                                            <h4>Keys provided</h4>
                                            <p><b>API Key:</b> Use it to authenticate as Bearer token</p>
                                            <p><b>Secret Key:</b> Use it to generate signature</p>
                                            <p><b>Request Endpoint:</b> <code>{{env('APP_URL')}}/api/prod-mod/payin-static-qr/get</code></p>
                                        </section>

                                        <section class="mt-4">
                                            <h4>Headers</h4>
                                            <p><b>Content-Type:</b> application/json</p>
                                            <p><b>Authorization:</b> Bearer Your-API-KEY-Here</p>
                                        </section>

                                        <section class="mt-4">
                                            <h4>Method</h4>
                                            <span class="badge bg-success">POST</span>
                                        </section>

                                        <section class="mt-4">
                                            <h4>Parameters</h4>
                                            <h6>No param for this api</h6>
                                        </section>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row m-0">
                                    <div class="col-md-12 custom-div">
                                        <section class="mt-2">
                                            <h4>Response</h4>
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Parameter</th>
                                                        <th>Description</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="headerFieldsBody">
                                                    <tr>
                                                        <td>success</td>
                                                        <td>true/false in boolean field</td>
                                                    </tr>
                                                    <tr>
                                                        <td>status</td>
                                                        <td>This field displays the actual status of a transaction.</td>
                                                    </tr>
                                                    <tr>
                                                        <td>message</td>
                                                        <td>Description of the status of the transaction</td>
                                                    </tr>
                                                    <tr>
                                                        <td>reference_id</td>
                                                        <td>Trustxpay system generated unique reference id</td>
                                                    </tr>
                                                    <tr>
                                                        <td>upi_qrcode_image</td>
                                                        <td>QR code image generated for transactions</td>
                                                    </tr>
                                                    <tr>
                                                        <td>upi_qrcode_pdf</td>
                                                        <td>QR code pdf generated for transactions</td>
                                                    </tr>
                                                    <tr>
                                                        <td>upi_intent</td>
                                                        <td>UPI Intent for payment</td>
                                                    </tr>
                                                    <tr>
                                                        <td>merchant_reference_id</td>
                                                        <td>Merchant system unique transaction id</td>
                                                    </tr>
                                                    <tr>
                                                        <td>timestamp</td>
                                                        <td>Date time of the transaction in yyyy-mm-dd hh:mm:ss format</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </section>

                                        <section class="mt-4">
                                            <p>Possible <b>“status”</b> parameter values:</p>
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Value</th>
                                                        <th>Description</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="headerFieldsBody">
                                                    <tr>
                                                        <td>SUCCESS</td>
                                                        <td>Transaction is Successful</td>
                                                    </tr>
                                                    <tr>
                                                        <td>FAILED</td>
                                                        <td>Transaction failed. Check “message” parameter for more details</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </section>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="row m-0">
                                    <div class="col-md-12 custom-div">
                                        <section class="mt-2">
                                            <h4>Example response</h4>

                                            <p>When <b>“success”</b> parameter value is <b>false,</b> it means that the transaction was failed at Trustxpay side due to authentication or invalid data provided in the API. More information was provided into <b>“message”</b> parameter.</p>
                                            <p>If the request was failed due to specific parameter data, <b>“errors”</b> parameter will supply with array of parameters and message.</p>

                                            <b>Invalid api_key used for authentication</b>
                                            <pre>
                                                <code class="language-json hljs">
{
    "success": false,
    "message": "Invalid API key provided."
}
                                                </code>
                                            </pre>

                                            <b>Request validation failed due to invalid data or no mandatory parameter provided</b>
                                            <pre>
                                                <code class="language-json hljs">
{
    "success": false,
    "message": "Validation error, please check errors parameters.",
    "errors": {
        "merchant_reference_id": [
            "The merchant reference id field is required."
        ]
    }
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
                                        <b>Transaction Success</b>
                                        <pre>
                                            <code class="language-json hljs">
{
    "success": true,
    "status": "SUCCESS",
    "message": "",
    "merchant_reference_id": "REF000001",
    "reference_id": "R061736143273AWSIT",
    "upi_qrcode_image": "https://example.com/qrcodes/230913107321@yesbank.png",
    "upi_qrcode_pdf": "https://example.com/qrcodes/23090913107321@yesbank.pdf",
    "upi_intent": "upi://pay?tr=R061736143273AWSIT&tid=&pa=23090913107321@yesbank&mc=&pn=User&am=&cu=INR&tn=Pay+for+User",
    "timestamp": "2025-01-06T06:01:13.000000Z"
}
                                            </code>
                                        </pre>
                                        <b>Transaction failed</b>
                                        <pre>
                                            <code class="language-json hljs">
{
    "success": true,
    "status": "FAILED",
    "message": "Transaction request failed."
}
                                            </code>
                                        </pre>
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
