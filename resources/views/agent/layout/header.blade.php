<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="Description" content="">
    <meta name="Author" content="">
    <meta name="Keywords" content=""/>

    <!-- Title -->
    <title> {{ $company_name }} </title>

    <!--- Favicon --->

<!--  <link rel="icon" href="{{asset('assets/img/trustxpay-favicon.png')}}" type="image/x-icon"/> -->
    <link rel="icon" href="{{asset('assets/img/trustxpay-favicon.png')}}" type="image/x-icon"/>


    <!--- Icons css --->
    <link href="{{url('assets/css/icons.css')}}" rel="stylesheet">
    <link href="{{url('assets/plugins/select2/css/select2.min.css')}}" rel="stylesheet">
    <link href="{{url('assets/plugins/sweet-alert/sweetalert.css')}}" rel="stylesheet">


    <link href="{{url('assets/plugins/datatable/css/dataTables.bootstrap4.min.css')}}" rel="stylesheet">
    <link href="{{url('assets/plugins/datatable/css/buttons.bootstrap4.min.css')}}" rel="stylesheet">
    <link href="{{url('assets/plugins/datatable/css/responsive.bootstrap4.min.css')}}" rel="stylesheet">
    <link href="{{url('assets/plugins/datatable/css/jquery.dataTables.min.css')}}" rel="stylesheet">
    <link href="{{url('assets/plugins/datatable/css/responsive.dataTables.min.css')}}" rel="stylesheet">


    <!-- Owl-carousel css-->
    <link href="{{url('assets/plugins/owl-carousel/owl.carousel.css')}}" rel="stylesheet"/>

    <!--- Right-sidemenu css --->
    <link href="{{url('assets/plugins/sidebar/sidebar.css')}}" rel="stylesheet">

    <!--- Style css --->

    <link href="{{url('assets/css/style.css')}}" rel="stylesheet">
    <link href="{{url('assets/css/skin-modes.css')}}" rel="stylesheet">

    <!--- Animations css --->
    <link href="{{url('assets/css/animate.css')}}" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>


    <script type="text/javascript">
        var session_id = "{!! (Session::getId())?Session::getId():'' !!}";
        var user_id = "{!! (Auth::user())?Auth::user()->id:'' !!}";

        // Your web app's Firebase configuration
        var firebaseConfig = {
            apiKey: "FIREBASE_API_KEY",
            authDomain: "FIREBASE_AUTH_DOMAIN",
            databaseURL: "FIREBASE_DATABASE_URL",
            storageBucket: "FIREBASE_STORAGE_BUCKET",
        };
        // Initialize Firebase
        firebase.initializeApp(firebaseConfig);

        var database = firebase.database();


        firebase.database().ref('/users/' + user_id).on('value', function (snapshot2) {
            var v = snapshot2.val();

            if (v.session_id !== session_id) {

                console.log("Your account login from another device!!");

                setTimeout(function () {
                    window.location = '/login';
                }, 4000);
            }
        });
    </script>
    {!! $chat_script !!}
    @include('agent.layout.style')
</head>

<body class="main-body  app">


<script type="text/javascript">
    $(document).ready(function () {
        $.ajax({
            url: "{{url('admin/dashboard-data-api')}}",
            success: function (msg) {
                if (msg.status == 'success') {
                    $("#dashboard_api_balance").text(msg.balance.api_balance);
                    $("#dashboard_today_sale").text(msg.sales.today_sale);
                    $("#dashboard_today_profit").text(msg.sales.today_profit);

                }

            }
        });
        getWalletBal();
    });

    function getWalletBal() {
        var token = $("input[name=_token]").val();
        var dataString = '_token=' + token;
        $.ajax({
            type: "POST",
            url: "{{url('agent/get-wallet-balance')}}",
            data: dataString,
            success: function (msg) {
                if (msg.status == 'success') {
                    $(".normal_balance").text(msg.data.normal_balance);
                    $(".aeps_balance").text(msg.data.aeps_balance);
                }
            }
        });
    }

    function buttonDisabled(target) {
        $(target).attr('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
    }

    function buttonEnabled(target, html) {
        $(target).attr('disabled', false).html(html);
    }
</script>


<div class="loader" style="display: none;"></div>

{{--Header top--}}
@include('agent.layout.header_top')
</div>
</div>
<!-- main-header closed -->

<!--Horizontal-main -->
<div class="sticky">
    <div class="horizontal-main hor-menu clearfix side-header">
        <div class="horizontal-mainwrapper container clearfix">
            <!--Nav-->
            <nav class="horizontalMenu clearfix">
                <ul class="horizontalMenu-list">
                    <li aria-haspopup="true"><a href="{{url('agent/dashboard')}}" class=""><i
                                class="fe fe-airplay  menu-icon"></i> Dashboard</a></li>


                    @php
                        $library = new \App\Library\BasicLibrary;
                        $companyActiveService = $library->getCompanyActiveService(Auth::id());
                        $userActiveService = $library->getUserActiveService(Auth::id());
                    @endphp
                    <li aria-haspopup="true"><a href="#" class="sub-icon"><i class="fas fa-table"></i> Report <i
                                class="fe fe-chevron-down horizontal-icon"></i></a>
                        <ul class="sub-menu">
                            <li aria-haspopup="true"><a href="{{url('agent/report/v1/all-transaction-report')}}"
                                                        class="slide-item"> All Transaction Report</a></li>
                            @if(Auth::id() != 116)
                                @foreach(App\Models\Servicegroup::where('status_id', 1)->select('id', 'group_name')->get() as $value)
                                    <li aria-haspopup="true" class="sub-menu-sub"><a
                                            href="#">{{ $value->group_name }}</a>
                                        <ul class="sub-menu">
                                            @foreach(App\Models\Service::where('servicegroup_id', $value->id)->whereIn('id', $companyActiveService)->whereIn('id', $userActiveService)->where('status_id', 1)->select('id', 'service_name', 'report_slug')->get() as $serv)
                                                <li aria-haspopup="true"><a
                                                        href="{{url('agent/report/v1/welcome')}}/{{ $serv->report_slug }}"
                                                        class="slide-item">{{ $serv->service_name }} History</a></li>
                                            @endforeach
                                            @if($value->id=='6')
                                                <li aria-haspopup="true">
                                                    <a href="{{url('agent/report/v1/move-to-bank-history')}}"
                                                       class="slide-item"> Move To Bank History </a>
                                                </li>
                                            @endif
                                        </ul>
                                    </li>
                                @endforeach

                                <li aria-haspopup="true"><a href="{{url('agent/report/v1/ledger-report')}}"
                                                            class="slide-item"> Ledger Report</a></li>

                                <li aria-haspopup="true" class="sub-menu-sub"><a href="#">Sales & Income</a>
                                    <ul class="sub-menu">
                                        <li aria-haspopup="true"><a href="{{url('agent/operator-report')}}"
                                                                    class="slide-item">Operator Wise Sales</a></li>
                                        <li aria-haspopup="true"><a href="{{url('agent/income-report')}}"
                                                                    class="slide-item">Income</a></li>
                                    </ul>
                                </li>
                            @endif
                        </ul>
                    </li>
                    @if(Auth::id() != 116)
                        <li aria-haspopup="true"><a href="#" class="sub-icon"><i class="fas fa-rupee-sign"></i> Payment
                                <i
                                    class="fe fe-chevron-down horizontal-icon"></i></a>
                            <ul class="sub-menu">
                                <li aria-haspopup="true"><a href="{{url('agent/payment-request')}}" class="">Payment
                                        Request</a></li>
                                <li aria-haspopup="true"><a href="{{url('agent/payout-request')}}" class="">Payout
                                        Request</a></li>
                                {{--<li aria-haspopup="true"><a href="{{url('agent/balance-return-request')}}" class="slide-item"> Balance Return Request</a></li>--}}
                                @if(Auth::User()->company->cashfree == 1 && Auth::User()->profile->cashfree == 1)
                                    <li aria-haspopup="true"><a href="{{url('agent/add-money/v1/welcome')}}"
                                                                class="slide-item"> Add Money</a></li>
                                @endif

                            </ul>
                        </li>
                    @endif
                    @if(Auth::user()->role_id==8)
                    <li aria-haspopup="true" class=""><a
                        href="{{url('agent/virtual-account-static-qr')}}"
                        >Virtual Account (Static QR)</a></li>
                    @endif
                    @if(Auth::User()->company->ecommerce == 1 && Auth::User()->profile->ecommerce == 1)
                        <li aria-haspopup="true"><a href="{{url('agent/ecommerce/welcome')}}" class=""><i
                                    class="fas fa-shopping-cart"></i> Shop </a></li>
                    @endif

                    <li aria-haspopup="true"><a href="{{route('documentation.list')}}" class=""><i class="fas fa-money-check"></i> APIs Document</a></li>

                    @if(Auth::id() != 116)
                        <li aria-haspopup="true" class="btn btn-danger"><a
                                href="{{url('agent/referral/refer-and-earn')}}"
                                style="color: white;">Refer & Earn</a></li>
                    @endif



                    <li aria-haspopup="true" class="btn btn-info" style="float: right;">
                        <a href="#" style="color: white;">
                            Normal Bal : <span class="normal_balance"></span>
                            | Aeps Bal : <span class="aeps_balance"></span>
                        </a>
                    </li>
                </ul>
            </nav>
            <!--Nav-->
        </div>
    </div>
</div>
<!--Horizontal-main -->

<!-- main-content opened -->
<div class="main-content horizontal-content">

    <!-- container opened -->
    <div class="container">

        <!-- breadcrumb -->
        <div class="breadcrumb-header justify-content-between">
            <div>
                <h4 class="content-title mb-2">Hi, {{ Auth::User()->name }} welcome back!</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{url('admin/dashboard')}}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $page_title  }}</li>
                    </ol>
                </nav>

            </div>


            <div class="d-flex my-auto">
                <div class=" d-flex right-page">
                    <div class="d-flex justify-content-center mr-5">
                        <div class="">
									<span class="d-block">
										<span class="label">Today Sale</span>
									</span>
                            <span class="value" id="dashboard_today_sale"></span>
                        </div>
                        <div class="ml-3 mt-2">
                            <span class="sparkline_bar"></span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center">
                        <div class="">
									<span class="d-block">
										<span class="label">Today Profit</span>
									</span>
                            <span class="value" id="dashboard_today_profit"></span>
                        </div>
                        <div class="ml-3 mt-2">
                            <span class="sparkline_bar31"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /breadcrumb -->

        @if(Auth::User()->active == 0)
            <div class="alert alert-danger" role="alert">
                <strong>Alert </strong> {{Auth::User()->reason}}
            </div>
        @endif

        @if(Auth::User()->mobile_verified == 1 && Auth::User()->active != 0)
            @yield('content')
        @else
            @include('agent.layout.profile_verify')
        @endif



    <!--Sidebar-right-->
        <div class="sidebar sidebar-right sidebar-animate">
            <div class="panel panel-primary card mb-0">
                <div class="panel-body tabs-menu-body p-0 border-0">
                    <ul class="Date-time">
                        <li class="time">
                            <h1 class="animated ">21:00</h1>
                            <p class="animated ">Sat,October 1st 2029</p>
                        </li>
                    </ul>
                    <div class="card-body latest-tasks">

                        <div class="task-stat pb-0">
                            <div class="d-flex tasks">
                                <div class="mb-0">
                                    <div class="h6 fs-15 mb-0">Normal Balance</div>
                                </div>
                                <span
                                    class="float-right ml-auto">{{number_format(Auth::user()->balance->user_balance,2)}}</span>
                            </div>

                            <div class="d-flex tasks">
                                <div class="mb-0">
                                    <div class="h6 fs-15 mb-0">Lien Balance</div>
                                </div>
                                <span
                                    class="float-right ml-auto">{{number_format(Auth::user()->balance->lien_amount,2)}}</span>
                            </div>

                            <div class="d-flex tasks">
                                <div class="mb-0">
                                    <div class="h6 fs-15 mb-0">Sms Balance</div>
                                </div>
                                <span
                                    class="float-right ml-auto">{{number_format(Auth::user()->balance->sms_balance,2)}}</span>
                            </div>
                            <div class="d-flex tasks">
                                <div class="mb-0">
                                    <div class="h6 fs-15 mb-0">Aeps Balance</div>
                                </div>
                                <span
                                    class="float-right ml-auto">{{number_format(Auth::user()->balance->aeps_balance,2)}}</span>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
        <!--/Sidebar-right-->


        <!-- Footer opened -->
        <div class="main-footer ht-40">
            <div class="container-fluid pd-t-0-f ht-100p">
                {{-- <span>Copyright © 2024 Trustxpay is a product which is operating under legally registered entity the auspices of Trustxpay | All rights reserved.</span> --}}
            </div>
        </div>
        <!-- Footer closed -->


        <!--- Back-to-top --->
        <a href="#top" id="back-to-top"><i class="las la-angle-double-up"></i></a>

        <!--- JQuery min js --->
        <script src="{{url('assets/plugins/jquery/jquery.min.js')}}"></script>

        <!--- Datepicker js --->
        <script src="{{url('assets/plugins/jquery-ui/ui/widgets/datepicker.js')}}"></script>

        <!--- Bootstrap Bundle js --->
        <script src="{{url('assets/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>

        <!--- Ionicons js --->
        <script src="{{url('assets/plugins/ionicons/ionicons.js')}}"></script>

        <script src="{{url('assets/plugins/select2/js/select2.min.js')}}"></script>


        <!--- Chart bundle min js --->
        <script src="{{url('assets/plugins/chart.js/Chart.bundle.min.js')}}"></script>
        <script src="{{url('assets/plugins/chart.js/excanvas.js')}}"></script>
        <script src="{{url('assets/plugins/chart.js/utils.js')}}"></script>


        <!--- Index js --->
        <script src="{{url('assets/js/index.js')}}"></script>

        <!--- JQuery sparkline js --->
        <script src="{{url('assets/plugins/jquery-sparkline/jquery.sparkline.min.js')}}"></script>

        <!--- Internal Sampledata js --->
        <script src="{{url('assets/js/chart.flot.sampledata.js')}}"></script>

        <!--- Rating js --->
        <script src="{{url('assets/plugins/rating/jquery.rating-stars.js')}}"></script>
        <script src="{{url('assets/plugins/rating/jquery.barrating.js')}}"></script>

        <!--- Horizontalmenu js --->
        <script src="{{url('assets/plugins/horizontal-menu/horizontal-menu.js')}}"></script>

        <!--- Eva-icons js --->
        <script src="{{url('assets/js/eva-icons.min.js')}}"></script>

        <!--- Moment js --->
        <script src="{{url('assets/plugins/moment/moment.js')}}"></script>

        <script src="{{ url('assets/js/jquery.validate.min.js') }}"></script>
        <script src="{{ url('assets/js/additional-methods.min.js') }}"></script>
        <script src="{{url('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
        <script src="{{url('assets/plugins/datatable/js/dataTables.dataTables.min.js')}}"></script>
        <script src="{{url('assets/plugins/datatable/js/dataTables.responsive.min.js')}}"></script>
        <script src="{{url('assets/plugins/datatable/js/responsive.dataTables.min.js')}}"></script>
        <script src="{{url('assets/plugins/datatable/js/jquery.dataTables.js')}}"></script>
        <script src="{{url('assets/plugins/datatable/js/dataTables.bootstrap4.js')}}"></script>
        <script src="{{url('assets/plugins/datatable/js/dataTables.buttons.min.js')}}"></script>

        {{--  <script src="{{url('assets/plugins/datatable/js/jszip.min.js')}}"></script>
          <script src="{{url('assets/plugins/datatable/js/pdfmake.min.js')}}"></script>
          <script src="{{url('assets/plugins/datatable/js/vfs_fonts.js')}}"></script>
          <script src="{{url('assets/plugins/datatable/js/buttons.html5.min.js')}}"></script>
          <script src="{{url('assets/plugins/datatable/js/buttons.print.min.js')}}"></script>--}}


        <script src="{{url('assets/plugins/datatable/js/dataTables.responsive.min.js')}}"></script>
        <script src="{{url('assets/plugins/datatable/js/responsive.bootstrap4.min.js')}}"></script>
        <script src="{{url('assets/js/table-data.js')}}"></script>


        <!--- Perfect-scrollbar js --->
        <script src="{{url('assets/plugins/perfect-scrollbar/perfect-scrollbar.min.js')}}"></script>
        <script src="{{url('assets/plugins/perfect-scrollbar/p-scroll.js')}}"></script>

        <!--- Sticky js --->
        <script src="{{url('assets/js/sticky.js')}}"></script>

        <!--- Right-sidebar js --->
        <script src="{{url('assets/plugins/sidebar/sidebar.js')}}"></script>
        <script src="{{url('assets/plugins/sidebar/sidebar-custom.js')}}"></script>

        <!--- Scripts js --->
        <script src="{{url('assets/js/script.js')}}"></script>

        <!--- Custom js --->
        <script src="{{url('assets/js/custom.js')}}"></script>
        <script src="{{url('assets/plugins/sweet-alert/sweetalert.min.js')}}"></script>
        <script src="{{url('assets/plugins/sweet-alert/jquery.sweet-alert.js')}}"></script>
        <script src="{{url('assets/js/common.js')}}"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

        <script>
            $(document).ready(function () {
                $("#search_text").autocomplete({
                    source: function (request, response) {
                        $.ajax({
                            type: "GET",
                            url: "{{url('agent/ecommerce/searchProductAjax')}}",
                            data: {
                                term: request.term
                            },
                            dataType: 'json',
                            success: function (data) {
                                response(data);
                            }
                        });
                    },
                    minLength: 1,
                });

                $(document).on('click', '.ui-menu-item', function () {
                    $('#search-form').submit();
                });
            });

        </script>
@yield('customScript')

@csrf

</body>
</html>
