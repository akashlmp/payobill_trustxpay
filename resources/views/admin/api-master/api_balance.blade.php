@extends('admin.layout.header')
@section('content')

    <script type="text/javascript">
         $(document).ready(function () {
            mnpBalance();
            justRechargeBalance();
            rechare2Balance();
            giftCardBalance();
            paysprintCreditBalance();
            paysprintDebitBalance();
        });

       


        function mnpBalance()
        {
           
            $.ajax({
            type: "GET",
            url: "{{url('admin/get-mnp-balance')}}",
                success: function (response) {
                    
                    if (response.Status == '0') {
                    
                        $("#mnp_balance").text(response.Balance);
                    
                    }
                    
                },
               
            });
        }
        function justRechargeBalance()
        {

            $.ajax({
            type: "GET",
            url: "{{url('admin/get-just-recharge-balance')}}",
                success: function (response) {
                   if (response.Status == '0') {
                    let balance = response.Balance;
                    
                    $("#recharge_balance").text((parseFloat(balance).toFixed(2)));
                    
                    }
                },
                
            });
        }
        function rechare2Balance()
        {
            $.ajax({
            type: "GET",
            url: "{{url('admin/get-recharge2-balance')}}",
                success: function (response) {
                   
                   if (response.status == 'SUCCESS') {
                        let balance = response.balance;
                        $("#recharge2_balance").text((parseFloat(balance).toFixed(2)));
                    
                    }
                },
               
            });
        }
        function giftCardBalance()
        {
           $.ajax({
            type: "POST",
            data:{ _token: '{{ csrf_token() }}' },
            dataType:'JSON',
            url: "{{url('admin/check-balance')}}",
                success: function (response) {
                   if (response.Status == '1') {
                    
                    $("#giftcard_balance").text(response.Balance);
                    
                    }
                }
            });
        }

        function paysprintCreditBalance()
        {
           $.ajax({
            type: "GET",
           
            url: "{{url('admin/get-paysprint-credit-balance')}}",
                success: function (response) {
                   if (response.response_code == 1 && response.response_code == '1') {
                    console.log("if");
                    $("#paysprint_credit_balance").text(response.wallet);
                    
                    }
                }
            });
        }

        function paysprintDebitBalance()
        {
            $.ajax({
            type: "GET",
           
            url: "{{url('admin/get-paysprint-debit-balance')}}",
                success: function (response) {
                   if (response.response_code == 1 && response.response_code == '1') {
                    
                    $("#paysprint_debit_balance").text(response.cdwallet);
                    
                    }
                }
            });
        }
     

    </script>
 <!-- row -->
 <div class="main-content-body">
    <div class="row row-sm ">
            
            <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="card-order">
                            <h6 class="mb-2">Just Recharge</h6>
                            <h2 class="text-right ">
                                <i class="fas fa-wallet icon-size float-left text-primary text-primary-shadow"></i><span
                                        id="recharge_balance">0</span></h2>
                            <p class="mb-0">Total Balance</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="card-order">
                            <h6 class="mb-2">Recharge2</h6>
                            <h2 class="text-right ">
                                <i class="fas fa-wallet icon-size float-left text-primary text-primary-shadow"></i><span
                                        id="recharge2_balance">0</span></h2>
                            <p class="mb-0">Total Balance</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="card-order">
                            <h6 class="mb-2">MNP</h6>
                            <h2 class="text-right ">
                                <i class="fas fa-wallet icon-size float-left text-primary text-primary-shadow"></i>
                                <span id="mnp_balance">0</span>
                            </h2>
                            <p class="mb-0">Total Balance</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="card-order">
                            <h6 class="mb-2">Gift Card</h6>
                            <h2 class="text-right ">
                                <i class="fas fa-wallet icon-size float-left text-primary text-primary-shadow"></i>
                                <span id="giftcard_balance">0</span>
                            </h2>
                            <p class="mb-0">Total Balance</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="card-order">
                            <h6 class="mb-2">Paysprint Credit Balance</h6>
                            <h2 class="text-right ">
                                <i class="fas fa-wallet icon-size float-left text-primary text-primary-shadow"></i>
                                <span id="paysprint_credit_balance">0</span>
                            </h2>
                            <p class="mb-0">Total Balance</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="card-order">
                            <h6 class="mb-2">Paysprint Debit Balance</h6>
                            <h2 class="text-right ">
                                <i class="fas fa-wallet icon-size float-left text-primary text-primary-shadow"></i>
                                <span id="paysprint_debit_balance">0</span>
                            </h2>
                            <p class="mb-0">Total Balance</p>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</div>
<!-- /row -->
</div>
<!-- /container -->
</div>
<!-- /main-content -->


@endsection