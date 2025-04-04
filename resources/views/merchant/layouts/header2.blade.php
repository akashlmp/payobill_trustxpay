<div class="sticky">
    <div class="horizontal-main hor-menu clearfix side-header">
        <div class="horizontal-mainwrapper container clearfix">
            <!--Nav-->
            <nav class="horizontalMenu clearfix">
                <ul class="horizontalMenu-list">
                    {{-- <li aria-haspopup="true"><a href="{{url('merchant/dashboard')}}" class=""><i
                                class="fe fe-airplay  menu-icon"></i> Dashboard</a></li> --}}
                    <li aria-haspopup="true"><a href="{{url('merchant/transactions')}}" class=""><i class="fas fa-chart-line"></i> All Transactions</a></li>
                    <li aria-haspopup="true"><a href="{{url('merchant/test-transactions')}}" class=""><i class="fas fa-chart-line"></i> Test Transactions</a></li>
                    <li aria-haspopup="true"><a href="{{url('merchant/payouts')}}" class=""><i class="fas fa-money-check"></i> Payouts</a></li>
                    <li aria-haspopup="true"><a href="{{url('merchant/payment-request')}}" class=""><i class="fas fa-money-check"></i>Payment Request</a></li>
                    <li aria-haspopup="true"><a href="{{url('documentation/payment-api')}}" class=""><i class="fas fa-money-check"></i> APIs Document</a></li>

                    <li aria-haspopup="true" class="btn btn-info" style="float: right;">
                        <a href="#" style="color: white;">
                            Wallet Bal : <span class="normal_balance">{{Auth::guard('merchant')->user()->wallet}}</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <!--Nav-->
        </div>
    </div>
</div>
