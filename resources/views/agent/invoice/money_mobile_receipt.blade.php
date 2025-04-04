<html>
<title>{{ $company_name }}</title>
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

</head>
<body>

<div id="invoice-POS">

    <center id="top">
        <div class="info">
            <img src="{{$cdnLink}}{{$company_logo}}" style="height: 40px;">
            <div style="display: flex">
                <small style="font-size: 10px;margin: 9px 0 0 87px;">Powered by</small>
                <img src="{{asset('assets/img/trustxpay.png')}}" alt="" style="width: 35%;" id="logo">
            </div>
            <h3>Transaction Receipt</h3>
            <span>(Payment Through Cash Collection)</span>
        </div><!--End Info-->
    </center><!--End InvoiceTop-->
    <hr>


    <div id="bot">

        <div id="table">
            <table border="1" style="width:100%;  border: 1px solid black; border-collapse: collapse;">
                <tr>
                    <th>Date</th>
                    <td>{{$created_at}}</td>
                </tr>

                <tr>
                    <th>Bank Name</th>
                    <td>{{$bank_name}}</td>
                </tr>

                <tr>
                    <th>Bene Name</th>
                    <td>{{$beneficiary_name}}</td>
                </tr>

                <tr>
                    <th>Account No</th>
                    <td>{{$account_number}}</td>
                </tr>

                <tr>
                    <th>Remitter No</th>
                    <td>{{$remiter_number}}</td>
                </tr>

                <tr>
                    <th>Total Amount</th>
                    <td>₹{{$full_amount}}</td>
                </tr>
            </table>

        </div><!--End Table-->
        <table border="1" style="width:100%;  border: 1px solid black; border-collapse: collapse; margin-top: 2%;">
            <tr>
                <th>Id</th>
                <th>Amount</th>
                <th>UTR</th>
                <th>Status</th>
            </tr>
            @foreach($reports as $value)
                <tr>
                    <td>{{ $value->id }}</td>
                    <td>₹{{ number_format($value->amount, 2) }}</td>
                    <td>{{ $value->txnid }}</td>
                    <td>{{ $value->status->status }}</td>
                </tr>
            @endforeach

        </table>
        <center id="top">
            <div class="info">
                <h4>Shop Name : {{ $agent_name }}</h4>
                <h4>Agent Number : {{ $agent_number }}</h4>
            </div><!--End Info-->
        </center><!--End InvoiceTop-->

    </div><!--End InvoiceBot-->
</div><!--End Invoice-->

<div style="text-align: center; margin-top: 2%;">
    <button id="printPageButton" onClick="window.print();">Print</button>
    <a href="https://wa.me/6281228430523?text=Hi%20Qiscus" class="floating" target="_blank">
        <i class="fa fa-whatsapp fab-icon"></i>
    </a>
</div>

<style>

    @media print {
        #printPageButton {
            display: none;
        }
    }

    #invoice-POS{
        box-shadow: 0 0 1in -0.25in rgba(0, 0, 0, 0.5);
        padding:2mm;
        margin: 0 auto;
        width: 77mm;
        border: dotted;
        background: #FFF;




    ::selection {background: #f31544; color: #FFF;}
    ::moz-selection {background: #f31544; color: #FFF;}
    h1{
        font-size: 1.5em;
        color: #222;
    }
    h2{font-size: .9em;}
    h3{
        font-size: 1.2em;
        font-weight: 300;
        line-height: 2em;
    }
    p{
        font-size: .7em;
        color: #666;
        line-height: 1.2em;
    }

    #top, #mid,#bot{ /* Targets all id with 'col-' */
        border-bottom: 1px solid #EEE;
    }

    #top{min-height: 100px;}
    #mid{min-height: 80px;}
    #bot{ min-height: 50px;}

    #top .logo{
    //float: left;
        height: 60px;
        width: 60px;
        background: url(http://michaeltruong.ca/images/logo1.png) no-repeat;
        background-size: 60px 60px;
    }
    .clientlogo{
        float: left;
        height: 60px;
        width: 60px;
        background: url(http://michaeltruong.ca/images/client.jpg) no-repeat;
        background-size: 60px 60px;
        border-radius: 50px;
    }
    .info{
        display: block;
    //float:left;
        margin-left: 0;
    }
    .title{
        float: right;
    }
    .title p{text-align: right;}

    table, th, td {
        border: 1px solid black;
        border-collapse: collapse;
    }

    .tabletitle{
    //padding: 5px;
        font-size: .5em;
        background: #EEE;
    }
    .service{border-bottom: 1px solid #EEE;}
    .item{width: 24mm;}
    .itemtext{font-size: .5em;}

    #legalcopy{
        margin-top: 5mm;
    }



    }
    /* Add WA floating button CSS */
    .floating {
        position: fixed;
        width: 40px;
        height: 40px;
        margin: -8px 0 8px 8px;
        background-color: #25d366;
        color: #fff;
        border-radius: 50px;
        text-align: center;
        font-size: 30px;
        z-index: 100;
    }

    .fab-icon {
        padding: 5px;
    }
</style>
</body>
</html>
