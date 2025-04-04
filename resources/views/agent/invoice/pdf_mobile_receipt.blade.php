<html>
<title>{{ $company_name }}</title>
<head>

</head>

<body>

<div style="border: dotted 2px black;">
    <center>
        <div>
         <img src="{{$cdnLink}}{{$company_logo}}" style="height: 40px;">
            <table style="padding-left:355px;">
                <tr>
                    <td>
                        <small style="font-size: 10px;">Powered by</small>
                    </td>
                    <td>
                        <img src="{{asset('assets/img/trustxpay.png')}}" height="30px">

                    </td>
                </tr>
            </table>

            <h3>Transaction Receipt</h3>
            <span>(Payment through Cash Collection)</span>
        </div>
    </center>

    <table border="1" style="border: 1px solid black; border-collapse: collapse;margin-top:10px;padding-left:300px;">
                <tr>
                    <th width="110px"><b>Reference No</b></th>
                    <td style="padding-left:5px !important;">{{$data["report_id"]}}</td>
                </tr>

                <tr>
                    <th><b>Date & Time</b></th>
                    <td style="padding-left:5px !important;">{{$data["created_at"]}}</td>
                </tr>
                <tr>
                    <th width="110px"><b>Provider Name</b></th>
                    <td style="padding-left:5px !important;">{{$data["provider_name"]}}</td>
                </tr>

                <tr>
                    <th><b>Number</b></th>
                    <td style="padding-left:5px !important;">{{$data["number"]}}</td>
                </tr>

                <tr>
                    <th><b>Txn Id</b></th>
                    <td style="padding-left:5px !important;">{{$data["txnid"]}}</td>
                </tr>

                <tr>
                    <th><b>Amount</b></th>
                    <td style="padding-left:5px !important;"><span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span>{{$data["amount"]}}</td>
                </tr>

                <tr>
                    <th><b>Status</b></th>
                    <td style="padding-left:5px !important;">{{$data["status"]}}</td>
                </tr>

    </table>
    <center>
        <div class="info" style="margin-top:5px">
            <h4 style="font-size: medium;"><b>Shop Name : {{ $data["agent_name"] }}</b></h4>
            <h4 style="font-size: medium;margin-top:20px;margin-bottom:20px"><b>Agent Number : {{ $data["agent_number"] }}</b></h4>
        </div>
    </center>



</div>
</body>
</html>
