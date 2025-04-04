@extends('agent.layout.header')
@section('content')
    <script type='text/javascript' src='{{ asset('assets/iserveu/isuSDK.min.js') }}?v=2'></script>
    <script type="text/javascript">
        watchLocation();

        function refresh() {
            window.location.reload(true);
        }

        function watchLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        this.positionNew = position.coords;
                    },
                    error => {
                        if (error.code === 1) {
                            this.locationAlerts(false, error.message);
                        } else if (error.code === 2) {}
                    }
                );
            } else {
                this.locationAlerts(true, '');
            }
        }

        function locationAlerts(type, msg) {
            let alertHtml = type;
            if (alertHtml) {} else {
                alert(msg);
            }
        }

        function submitForm(e) {
            e.preventDefault()
            var apiusername = document.getElementById("apiusername").value;
            var username = document.getElementById("username").value;
            // var clientrefid = document.getElementById("clientrefid").value;
            // Get the current day of the month
            var day = new Date().getDate();
            var timestamp = Math.floor(Date.now() / 1000);
            var clientrefid = "R" + day + timestamp + getRandomString(5);
            document.getElementById("clientrefid").value = clientrefid;
            console.log(clientrefid)
            var pagename = document.getElementById("pagename").value;
            var isreceipt = document.getElementById("isreceipt").value ? document.getElementById("isreceipt").value :
                "true";
            var callbackurl = document.getElementById("callbackurl").value ? document.getElementById("callbackurl").value :
                "";
            var token = document.getElementById("token").value;
            var pass_key = document.getElementById("pass_key").value;
            var cd_amount = document.getElementById("cd_amount").value ? document.getElementById("cd_amount").value : "";
            let check = isreceipt === "false" && callbackurl !== "";
            if (token === "" || pagename === "" || pass_key === "") {
                alert("please enter all the field!");
            } else if (isreceipt === "false" && callbackurl === "") {
                alert("please enter all the field!");
            } else if (pagename === "CASH_DEPOSIT" && cd_amount === "") {
                alert("please enter amount for cash deposit!!")
            } else if (cd_amount % 100 != 0) {
                alert("please enter amount for cash deposit in multiples of 100 !!")
            } else if (cd_amount > 10000) {
                alert("Maximum amount allowed is ₹ 10000 !!")
            } else {
                isuSDK("#uaeps").open({
                    closeButton: true,
                    title: "<img src='/logo.png' width='' height='' alt='UAEPS & Adhaarpay'>",
                    className: "zoom-and-spin",
                    clientRefId: clientrefid,
                    inputParam: apiusername,
                    pagename: pagename,
                    token: token,
                    pass_key: pass_key,
                    cd_amount: cd_amount,
                    username: username,
                    isreceipt: isreceipt,
                    callbackurl: callbackurl,
                    paramA: "WEB",
                    paramB: "WEB",
                    paramC: ""
                });
            }

        }

        window.addEventListener("message", function(event) {
            console.log(event.data)
            if (event.data === "transactionCpmpleted") {
                const ele = document.getElementsByClassName('isuSDK-overlay');
                const ele2 = document.getElementsByClassName('zoom-and-spin');
                // const ele3 = document.getElementsByClassName('isuSDK-open');
                const ele4 = document.getElementsByClassName('isuSDK-modal');
                console.log("Transaction Done")
                ele[0].remove();
                ele2[0].remove();
                // ele3[0].remove();
                ele4[0].remove();
                window.location.reload(true);
            }
        });

        function showAmountField() {
            var dropdown = document.getElementById("pagename");
            var cd_amount = document.getElementById("cd_amountFields");

            if (dropdown.value == "CASH_DEPOSIT" || dropdown.value == "CASH_WITHDRAWAL" || dropdown.value == "ADHAAR_PAY") {
                cd_amount.style.display = "block";
            } else {
                cd_amount.style.display = "none"
            }

        }

        function getRandomString(length) {
            let characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let result = '';
            for (let i = 0; i < length; i++) {
                result += characters.charAt(Math.floor(Math.random() * characters.length));
            }
            return result.toUpperCase(); // Convert to uppercase
        }
        $(document).on("click",".isuSDK-close",function(){
            location.reload();
        });
        $(document).ready(function() {


            // Log the result
            console.log(finalString);
        });

        // $(document).ready(function(){
        //     $('#pagename').change(function(){
        //         if($(this).val()=="CASH_WITHDRAWAL"){
        //             $('#idShowAmount').show();
        //         }else{
        //             $('#idShowAmount').hide();
        //         }
        //     });
        // });
    </script>

    <link rel="stylesheet" href='{{ asset('assets/iserveu/isuSDK.min.css') }}'>
    <style>
        .instantpayAeps .nav-item {
            border: 1px solid #aba9c7;
            font-size: 16px;
            padding: 5px;
        }

        .instantpayAeps a {
            color: #555268;
        }

        .instantpayAeps .active {
            color: #3857f6;
        }

        .instantpayAeps .nav-active {
            border-color: #3857f6;
        }

        .input_container {
            width: 400px;
            margin-top: 10px;
        }

        .show {
            overflow-y: revert !important;
        }
    </style>


    <div id="uaeps"></div>
    <div class="main-content-body">
        <div class="row">
            <div class="col-lg-8 col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <form class="form-horizontal" onsubmit="return submitForm(event)">
                            <div class="mb-4 main-content-label">{{ $page_title }}</div>
                            <hr>
                            <input type="hidden" id="apiusername" name="apiusername" value="{{ $api_username }}">
                            <input type="hidden" id="username" name="username" value="{{ $username }}">
                            <input type="hidden" id="clientrefid" name="clientrefid" value="{{ $ref_id }}">
                            <input type="hidden" id="token" name="token" value="{{ $token }}">
                            <input type="hidden" id="pass_key" name="pass_key" value="{{ $pass_key }}">
                            <input type="hidden" id="isreceipt" name="isreceipt" value="{{ $is_receipt }}">
                            <input type="hidden" id="callbackurl" name="callbackurl" value="{{ $callback_url }}">

                            <div class="row">
                                <div class="col-md-6 offset-3">
                                    <div class="form-group">
                                        <label>Transaction Type</label>
                                        <select class="form-control select2" id="pagename" name="pagename"
                                            onchange="showAmountField()">
                                            <option value="CASH_WITHDRAWAL">CASH WITHDRAWAL</option>
                                            <option value="MINI_STATEMENT">MINI STATEMENT</option>
                                            <option value="BALANCE_ENQUIRY">BALANCE ENQUIRY</option>
                                            <option value="ADHAAR_PAY">ADHAAR PAY</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 offset-3" id="cd_amountFields">
                                    <div class="form-group">
                                        <label>Amount</label>
                                        <input type="text" placeholder="Enter amount" name="cd_amount" id="cd_amount"
                                            class="form-control" />
                                    </div>
                                </div>

                                <div class="col-md-6 offset-3 text-center">
                                    <button type="submit" class="btn btn-success">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
@endsection
