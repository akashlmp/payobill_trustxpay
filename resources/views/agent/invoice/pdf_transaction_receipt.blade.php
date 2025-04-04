<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $company_name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" media="all">

    <style>
        html{
            font-family: unset !important;
        }
    </style>

</head>


<body  style="margin: 0px; padding: 0px; box-sizing: border-box;font-family: unset !important;">



<table style="width:100%">
    <tr>
        <td>

                <img src="{{$cdnLink}}{{$company_logo}}" >

                    <table>
                        <tr>
                            <td>
                                <small style="font-size: 10px;">Powered by</small>
                            </td>
                            <td>
                                <img src="{{asset('assets/img/trustxpay.png')}}" height="30px">

                            </td>
                        </tr>
                    </table>
        </td>
        <td style="align: center;">

                <div style="text-align: center;" >
                    <h1>Invoice</h1>
                    <p>{{$reports->created_at}}</p>
                </div>

        </td>
        <td>

                <div style="text-align: right;">
                    <a href="" style="color:#333; display: block; text-decoration: none;">GSTIN- 09AALCP9498B1ZS</a>
                    <a href=""
                    style="color:#333; display: block; text-decoration: none;">Email- {{env('SUPPORT_EMAIL','info@trustxpay.org')}}</a>

                </div>

        </td>
    </tr>
</table>


<table  style="width:100%;margin-top:10px">
    <tr>

        <td style="width:33.33%">
            <div style="border:1px solid grey;">
                <h3 style="padding: 8px 10px; background-color: #808080; color: #fff; text-transform: capitalize; margin: 0px;">
                    agent details</h3>
                <div style="padding:20px; text-transform: capitalize;" class="spac">
                    <p style="margin: 0px; margin-bottom: 8px; white-space: nowrap;font-size: 12px !important;margin: 0px !important;margin-bottom: 2px !important;" id="ptag"><b>Shop name
                            : </b>{{$data["agent_name"]}}</p>
                    <p style="margin: 0px; margin-bottom: 8px;font-size: 12px !important;margin: 0px !important;margin-bottom: 2px !important;" id="ptag"><b>Mobile No : </b>{{$data["agent_number"]}}</p>
                    <p style="margin: 0px; margin-bottom: 8px;font-size: 12px !important;margin: 0px !important;margin-bottom: 2px !important;" id="ptag"><b>agent address : </b>{{ $data["office_address"] }}</p>

                </div>
            </div>

        </td>

        <td style="width:33.33%">
            @if($data["provider_name"] =='Money Transfer')
            <div style="border:1px solid grey;">
                <h3 style="padding: 8px 10px; background-color: #808080; color: #fff; text-transform: capitalize; margin: 0px;"
                    class="spac">
                    customer details</h3>
                <div style="padding:20px; text-transform: capitalize;" class="spac">
                    <p style="margin: 0px; margin-bottom: 8px;font-size: 12px !important;margin: 0px !important;margin-bottom: 2px !important;" id="ptag"><b>Remitter name : </b>{{$data["remiter_name"]}}</p>
                    <p style="margin: 0px; margin-bottom: 8px;font-size: 12px !important;margin: 0px !important;margin-bottom: 2px !important;" id="ptag"><b>mode : </b>{{$data["channel"]}}</p>
                    <p style="margin: 0px; margin-bottom: 8px;font-size: 12px !important;margin: 0px !important;margin-bottom: 2px !important;" id="ptag"><b>Remitter number : </b>{{$data["remiter_number"]}}
                    </p>

                </div>
            </div>
            @endif
        </td>
        <td style="width:33.33%">
            @if($data["provider_name"] =='Money Transfer')
            <div style="border:1px solid grey;">
                <h3 style="padding: 8px 10px; background-color: #808080; color: #fff; text-transform: capitalize; margin: 0px;"
                    class="spac">
                    beneficiary details</h3>
                <div style="padding:20px; text-transform: capitalize;" class="spac">
                    <p style="margin: 0px; margin-bottom: 8px;font-size: 12px !important;margin: 0px !important;margin-bottom: 2px !important;" id="ptag"><b>bank name : </b>{{ $data["bank_name"] }}</p>
                    <p style="margin: 0px; margin-bottom: 8px;font-size: 12px !important;margin: 0px !important;margin-bottom: 2px !important;" id="ptag"><b>name : </b>{{ $data["beneficiary_name"] }}</p>
                    <p style="margin: 0px; margin-bottom: 8px;font-size: 12px !important;margin: 0px !important;margin-bottom: 2px !important;" id="ptag"><b>account number : </b>{{ $data["account_number"] }}
                    </p>

                </div>
            </div>

            @endif
        </td>

    </tr>
</table>
<table  border="1" style="width:100%;margin-top:10px">
    <tr>
        <th style="background-color: #333;padding: 10px; text-align: center; color: #fff; text-transform: capitalize;width: 20%;" class="spac">Order Id
        </th>
        <th style="background-color: #333;padding: 10px; text-align: center; color: #fff; text-transform: capitalize; width: 20%;"
                    class="spac">Operator Name
        </th>
        <th style="background-color: #333;padding: 10px; text-align: center; color: #fff; text-transform: capitalize; width: 20%;"
                    class="spac">Number
        </th>
        <th style="background-color: #333;padding: 10px; text-align: center; color: #fff; text-transform: capitalize; width: 20%;"
                    class="spac">TXN ID
        </th>
        <th style="background-color: #333;padding: 10px; text-align: center; color: #fff; text-transform: capitalize; width: 20%;"
                    class="spac">Status
        </th>
        <th style="background-color: #333;padding: 10px; text-align: center; color: #fff; text-transform: capitalize; width: 20%;"
                    class="spac">Amount
        </th>
    </tr>
    <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 15px 10px; text-align: center; color: #333; text-transform: capitalize; width: 20%; border-bottom: 1px solid #ddd;"
                    class="spac">{{$data["report_id"]}}</td>
                <td style="padding: 15px 10px; text-align: center; color: #333; text-transform: capitalize; width: 20%; border-bottom: 1px solid #ddd;"
                    class="spac">{{$data["provider_name"]}}</td>
                <td style="padding: 15px 10px; text-align: center; color: #333; text-transform: capitalize; width: 20%; border-bottom: 1px solid #ddd;"
                    class="spac">{{$data["number"]}}</td>
                <td style="padding: 15px 10px; text-align: center; color: #333; text-transform: capitalize; width: 20%; border-bottom: 1px solid #ddd;"
                    class="spac">{{$data["txnid"]}}</td>
                <td style="padding: 15px 10px; text-align: center; color: #333; text-transform: capitalize; width: 20%; border-bottom: 1px solid #ddd;"
                    class="spac">{{$data["status"]}}
                </td>
                <td style="padding: 15px 10px; text-align: center; color: #333; text-transform: capitalize; width: 20%; border-bottom: 1px solid #ddd;"
                    class="spac">
                        <span
                            style="padding:5px 10px; background-color:rgb(122, 122, 122); color:#fff; border-radius:8px;">
                            <span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span>{{ $data["amount"] }}</span>
                </td>

    </tr>
</table>
<table style="width:100%">
    <tr>
        <td  style="width:50%">
            <div style="border: 1px solid #ddd;padding:2px">
                <p><b>Notes: </b>This is a Computer Generated Receipt. Signature Not
                    Required. Amount is inclusive of GST.
                    <br>Website: https://trustxpay.org.
                    {{-- <br>Transaction charges are inclusive of GST--}}
                </p>
            </div>

        </td>
        <td>
            <div style="margin-top:15px;border: 1px solid #ddd">
                <h3 style="padding:5px; background-color: #808080; color: #fff; text-transform: capitalize; margin: 0px; text-align: center;"
                    class="spac">Total Amount</h3>
                <h2 style="text-align: center; padding: 5px; margin: 0px;" class="spac" id="font">
                    Rs. {{ $data["full_amount"] }}/-
                </h2>
                <h2 style="text-align: center; padding: 5px; margin: 0px;" class="spac" id="font">
                    {{Helpers::convertNumber($data["full_amount"]) .' Only'}}
                </h2>
            </div>
        </td>
    </tr>
</table>

</body>

</html>

