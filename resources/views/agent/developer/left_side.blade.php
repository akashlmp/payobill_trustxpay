<!-- Col -->
<div class="col-lg-4 col-xl-3">
    <div class="card mg-b-20 compose-mail">
        <div class="card-header pb-0">
            <div class="d-flex justify-content-between">
                <h4 class="card-title mg-b-2 mt-2">Navigation</h4>
                <i class="mdi mdi-dots-horizontal text-gray"></i>
            </div>
            <hr>
        </div>
        <div class="main-content-left main-content-left-mail card-body">
            <div class="main-mail-menu">
                <nav class="nav main-nav-column mg-b-20">
                    <a class="nav-link {{(Request::is('*settings') ? 'active' : '')}}"
                       href="{{url('agent/developer/settings')}}">Settings</a>

                    <a class="nav-link {{(Request::is('*provider-list') ? 'active' : '')}}"
                       href="{{url('agent/developer/provider-list')}}">Provider List</a>
                    <a class="nav-link {{(Request::is('*prepaid-and-dth') ? 'active' : '')}}"
                       href="{{url('agent/developer/prepaid-and-dth')}}">Prepaid And DTH</a>
                    <a class="nav-link {{(Request::is('*bill-payment') ? 'active' : '')}}"
                       href="{{url('agent/developer/bill-payment')}}">Bill Payment</a>


                    <a class="nav-link {{(Request::is('*money-transfer-docs') ? 'active' : '')}}"
                       href="{{url('agent/developer/money-transfer-docs')}}">Money Transfer</a>

                    <a class="nav-link {{(Request::is('*bank-transfer-docs') ? 'active' : '')}}"
                       href="{{url('agent/developer/bank-transfer-docs')}}">Bank Transfer</a>

                    @if(Auth::User()->company->aeps == 1 && Auth::User()->profile->aeps == 1)
                        <a class="nav-link {{(Request::is('*outlet-list') ? 'active' : '')}}"
                           href="{{url('agent/developer/outlet-list')}}">Outlet List</a>
                    @endif
                    <a class="nav-link {{(Request::is('*call-back-logs') ? 'active' : '')}}"
                       href="{{url('agent/developer/call-back-logs')}}">Call Back Logs</a>
                    <input type="hidden" id="BiometricData">
                </nav>

            </div><!-- main-mail-menu -->
        </div>
    </div>
</div>
<!-- /Col -->


<div class="modal  show" id="token_generate_otp_model" data-toggle="modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">New Token Generate</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="form-body">

                    <div class="row">

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">Token Generate OTP</label>
                                <input type="text" id="token_generate_otp" class="form-control" placeholder="Enter OTP">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="token_generate_otp_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">Your Login Password</label>
                                <input type="password" id="token_generate_password" class="form-control"
                                       placeholder="Login Password">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="token_generate_password_errors"></li>
                                </ul>

                            </div>
                        </div>

                    </div>

                </div>

                <div class="alert alert-outline-success" role="alert">
                    <strong>OTP successfully sent to your register mobile number</strong>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn ripple btn-primary" type="button" id="token_generate_btn"
                        onclick="token_generate_save()">Confirm Now
                </button>
                <button class="btn btn-primary" type="button" id="token_generate_btn_loader" disabled
                        style="display: none;"><span class="spinner-border spinner-border-sm" role="status"
                                                     aria-hidden="true"></span> Loading...
                </button>
                <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>


<div class="modal  show" id="ip_address_otp_model" data-toggle="modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Update Ip Address</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="form-body">

                    <div class="row">

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">OTP</label>
                                <input type="text" id="ip_address_otp" class="form-control" placeholder="Enter OTP">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="ip_address_otp_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">Your Login Password</label>
                                <input type="password" id="ip_address_password" class="form-control"
                                       placeholder="Login Password">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="ip_address_password_errors"></li>
                                </ul>

                            </div>
                        </div>

                    </div>

                </div>

                <div class="alert alert-outline-success" role="alert">
                    <strong>OTP successfully sent to your register mobile number</strong>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn ripple btn-primary" type="button" id="add_ipaddress_btn"
                        onclick="add_ipaddress_save()">Confirm Now
                </button>
                <button class="btn btn-primary" type="button" id="add_ipaddress_btn_loader" disabled
                        style="display: none;"><span class="spinner-border spinner-border-sm" role="status"
                                                     aria-hidden="true"></span> Loading...
                </button>
                <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>


<div class="modal  show" id="remove_ip_address_otp_model" data-toggle="modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Remove IP Address</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="form-body">

                    <div class="row">

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">OTP</label>
                                <input type="text" id="removeIpAddressOTP" class="form-control" placeholder="Enter OTP">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="removeIpAddressOTP_errors"></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="name">Your Login Password</label>
                                <input type="password" id="removeIpAddressPassword" class="form-control"
                                       placeholder="Login Password">
                                <ul class="parsley-errors-list filled">
                                    <li class="parsley-required" id="removeIpAddressPassword_errors"></li>
                                </ul>

                            </div>
                        </div>

                    </div>

                </div>

                <div class="alert alert-outline-success" role="alert">
                    <strong>OTP successfully sent to your register mobile number</strong>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn ripple btn-primary" type="button" id="removeIpAddressBtn"
                        onclick="removeIpAddressSave()">Confirm Now
                </button>
                <button class="btn btn-primary" type="button" id="removeIpAddressBtn_loader" disabled
                        style="display: none;"><span class="spinner-border spinner-border-sm" role="status"
                                                     aria-hidden="true"></span> Loading...
                </button>
                <button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>