@extends('agent.layout.header')
@section('content')

    <script type="text/javascript">
        $(document).ready(function () {
            showGraph();
            dashboard_details();
        });

        function showGraph() {
            var id = 1;
            var dataString = 'id=' + id;
            $.ajax({
                type: "GET",
                url: "{{url('agent/dashboard-chart-api')}}",
                data: dataString,
                success: function (msg) {
                    var provider_name = [];
                    var amount = [];
                    for (var i in msg.provider) {
                        provider_name.push(msg.provider[i].provider_name);
                        amount.push(msg.provider[i].amount);
                    }
                    var chartdata = {
                        labels: provider_name,
                        datasets: [
                            {
                                label: 'Provider Wise Chart',
                                backgroundColor: '#49e2ff',
                                borderColor: '#46d5f1',
                                hoverBackgroundColor: '#CCCCCC',
                                hoverBorderColor: '#666666',
                                data: amount
                            }
                        ]
                    };
                    var graphTarget = $("#graphCanvas");
                    var barGraph = new Chart(graphTarget, {
                        type: 'bar',
                        data: chartdata
                    });
                }
            });
        }

        function dashboard_details() {
            var id = 1;
            var dataString = 'id=' + id;
            $.ajax({
                type: "GET",
                url: "{{url('agent/dashboard-details-api')}}",
                data: dataString,
                success: function (msg) {
                    if (msg.status == 'success') {
                        $("#dashboard_today_success").text(msg.sales_overview.today_success);
                        $("#dashboard_today_failure").text(msg.sales_overview.today_failure);
                        $("#dashboard_today_pending").text(msg.sales_overview.today_pending);
                        $("#dashboard_today_refunded").text(msg.sales_overview.today_refunded);
                        $("#dashboard_today_credit").text(msg.sales_overview.today_credit);
                        $("#dashboard_today_debit").text(msg.sales_overview.today_debit);
                        $("#normal_distributed_balance").text(msg.balances.normal_distributed_balance);
                        $("#aeps_distributed_balance").text(msg.balances.aeps_distributed_balance);
                        $("#my_balances").text(msg.balances.my_balances);
                        $("#dashboard_total_members").text(msg.balances.dashboard_total_members);
                        $("#dashboard_total_active_users").text(msg.balances.dashboard_total_active_users);
                    }

                }
            });
        }
    </script>


    <!-- main-content-body -->
    <div class="main-content-body">

    {{--Dashboard popup start--}}
    @include('common.dashboard_popup')
    {{--Dashboard popup End--}}
    @if(Session::has('error') && Session::get('error')!='')
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error:</strong> {{Session::get('error')}}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      @php
          Session::put('error','')
      @endphp
    @endif
    <!-- row -->
        <div class="row row-sm ">

            {{-- graph view
            <div class="col-xl-9 col-lg-12 col-md-12 col-sm-12">
                <div class="card overflow-hidden">
                    <div class="card-header bg-transparent pd-b-0 pd-t-20 bd-b-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-10">Today Sales</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body pd-y-7">
                        <canvas id="graphCanvas"></canvas>
                    </div>
                </div>
            </div>
            graph view End--}}

            <div class="col-xl-9 col-lg-12 col-md-12 col-sm-12">
                <div class="card overflow-hidden">
                    <div class="card-header bg-transparent pd-b-0 pd-t-20 bd-b-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-10">Services</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body pd-y-7">

                        @if(Auth::User()->role_id == 10)
                            <canvas id="graphCanvas"></canvas>
                        @else
                            <div class="row no-gutters">
                                @php
                                    $library = new \App\Library\BasicLibrary;
                                    $companyActiveService = $library->getCompanyActiveService(Auth::id());
                                    $userActiveService = $library->getUserActiveService(Auth::id());
                                @endphp
                                @foreach(App\Models\Service::where('status_id', 1)
                                        ->whereIn('id', $companyActiveService)
                                        ->whereIn('id', $userActiveService)
                                        ->whereNotIn('id', [16,23, 29])
                                        ->get() as $value)
                                    @if(strtolower($value->service_name) == 'bbps')
                                        <div class="col-lg-3 col-md-3 col-sm-4 col-6 animated flipInX">
                                            <a href='{{url('agent')}}/{{ $value->slug }}' target="_blank" class="tray2 waves-effect">
                                                <img src="{{asset('assets/img/BBPS.png')}}" style="height: 40px;">
                                                <span>BBPS</span>
                                            </a>
                                        </div>
                                    @else
                                        <?php
                                        $target = "";
                                        if($value->id==25 && $cms_provider==2){
                                            $target = "target=_blank";
                                        }
                                        ?>
                                        <div class="col-lg-3 col-md-3 col-sm-4 col-6 animated flipInX">
                                            <a href="{{url('agent')}}/{{ $value->slug }}" {{$target}} class="tray2 waves-effect">
                                                <img src="{{ $cdnLink }}{{ $value->service_image }}"
                                                     style="height: 40px;">
                                                <span>{{ $value->service_name }}</span>
                                            </a>
                                        </div>
                                    @endif
                                @endforeach
                                <div class="col-lg-3 col-md-3 col-sm-4 col-6 animated flipInX">
                                    <a href="#" onclick="swal('Coming Soon!');" class="tray2 waves-effect">
                                        <img src="{{asset('assets/img/IRCTC.png')}}" style="height: 40px;">
                                        <span>IRCTC</span>
                                    </a>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-4 col-6 animated flipInX">
                                    <a href="#" onclick="swal('Coming Soon!');" class="tray2 waves-effect">
                                        <img src="{{asset('assets/img/pen-card-service.png')}}" style="height: 40px;">
                                        <span>PAN card services</span>
                                    </a>
                                </div>
                                @if(Auth::id() != 116)
{{--                                <div class="col-lg-3 col-md-3 col-sm-4 col-6 animated flipInX">--}}
{{--                                    <a href='#' target="_blank" class="tray2 waves-effect">--}}
{{--                                        <img src="{{asset('assets/img/BBPS.png')}}" style="height: 40px;">--}}
{{--                                        <span>BBPS</span>--}}
{{--                                    </a>--}}
{{--                                </div>--}}
                                @endif
                            </div>
                        @endif
                        @if(Auth::User()->member->kyc_status != 1)
                            <div class="row no-gutters">
                                <div class="col-12 animated flipInX">
                                    <div class="alert alert-danger mg-b-0" role="alert">
                                        <strong>Note! </strong> Your KYC is currently pending. You will be able to
                                        access the
                                        service once your KYC is approved. Kindly submit the necessary documents for
                                        approval.
                                        <a href="{{ url('agent/my-profile') }}">click here</a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-3">
                <div class="card">
                    <div class="card-header pb-0 pt-4">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-10">Last 5 login records</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                    </div>
                    <div class="card-body p-0 m-scroll mh-350 mt-2">
                        <div class="list-group projects-list">

                            @foreach(App\Models\Loginlog::where('user_id', Auth::id())->orderBy('id', 'DESC')->paginate(5) as $value)
                                <a href="{{ url('agent/activity-logs') }}"
                                   class="list-group-item list-group-item-action flex-column align-items-start border-top-0">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1 font-weight-semibold ">{{ $value->get_device }}
                                            - {{ $value->get_browsers }} - {{ $value->get_os }}</h6>
                                        <small
                                            class="text-danger">{{ \Carbon\Carbon::parse($value->created_at)->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-0 text-muted mb-0 tx-12">IP Address: {{ $value->ip_address }}</p>
                                    <small class="text-muted">Latitude: {{ $value->latitude }},
                                        Longitude: {{ $value->longitude }}</small>
                                </a>
                            @endforeach

                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header pb-0 pt-4">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-10">Today Overview</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                    </div>
                    <div class="card-body p-0 m-scroll mh-350 mt-2">
                        <div class="list-group projects-list">

                            <a href="#"
                               class="list-group-item list-group-item-action flex-column align-items-start border-top-0">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1 font-weight-semibold ">Success</h6>
                                    <small class="text-success" id="dashboard_today_success"></small>
                                </div>
                            </a>

                            <a href="#"
                               class="list-group-item list-group-item-action flex-column align-items-start border-top-0">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1 font-weight-semibold ">Failure</h6>
                                    <small class="text-danger" id="dashboard_today_failure"></small>
                                </div>
                            </a>

                            <a href="#"
                               class="list-group-item list-group-item-action flex-column align-items-start border-top-0">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1 font-weight-semibold ">Pending</h6>
                                    <small class="text-warning" id="dashboard_today_pending"></small>
                                </div>
                            </a>

                            <a href="#"
                               class="list-group-item list-group-item-action flex-column align-items-start border-top-0">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1 font-weight-semibold ">Refunded</h6>
                                    <small class="text-danger" id="dashboard_today_refunded"></small>
                                </div>
                            </a>

                            <a href="#"
                               class="list-group-item list-group-item-action flex-column align-items-start border-top-0">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1 font-weight-semibold ">Debit</h6>
                                    <small class="text-warning" id="dashboard_today_debit"></small>
                                </div>
                            </a>

                            <a href="#"
                               class="list-group-item list-group-item-action flex-column align-items-start border-top-0">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1 font-weight-semibold ">Credit</h6>
                                    <small class="text-warning" id="dashboard_today_credit"></small>
                                </div>
                            </a>

                        </div>
                    </div>
                </div>


            </div>
        </div>
        <!-- /row -->


        <div class="row row-sm ">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                <div class="row row-sm ">
                    <div class="col-md-12 col-xl-12">
                        <div class="card overflow-hidden review-project">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <h4 class="card-title mg-b-10">Mini Statement </h4>
                                    <i class="mdi mdi-dots-horizontal text-gray"></i>
                                </div>

                                <div class="table-responsive mb-0">
                                    <table
                                        class="table table-hover table-bordered mb-0 text-md-nowrap text-lg-nowrap text-xl-nowrap table-striped ">
                                        <thead>
                                        <tr>
                                            <th>Id</th>
                                            <th>Date</th>
                                            <th>Provider</th>
                                            <th>Amount</th>
                                            <th>Profit</th>
                                            <th>Balance</th>
                                            <th>Status</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach(App\Models\Report::where('user_id', Auth::id())->orderBy('id', 'DESC')->paginate(10) as $value)
                                            <tr>
                                                <td>{{ $value->id }}</td>
                                                <td>{{ $value->created_at }}</td>
                                                <td>{{ isset($value->provider) ? $value->provider->provider_name : "NA" }}</td>
                                                <td>{{ number_format($value->amount, 2) }}</td>
                                                <td>{{ number_format($value->profit, 2) }}</td>
                                                <td>{{ number_format($value->total_balance, 2) }}</td>
                                                <td><span
                                                        class="{{ $value->status->class }}">{{ $value->status->status }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /row -->
            </div>
        </div>

        <!-- row -->

        <!-- row -->
    </div>
    <!-- /row -->
    </div>
    <!-- /container -->
    </div>
    <!-- /main-content -->


    <style>
        .no-gutters {
            margin-right: 0;
            margin-left: 0;
        }

        .tray2 {
            text-align: center;
            padding: 12px 0;
            border: 1px solid #e5e5e5;
            background: snow;
            border-radius: 6px;
            margin: 15px;
            transition: .4s ease-out;
            display: block !important;
            color: inherit;
            min-height: 117px !important;
        }

        .tray2:hover {
            -webkit-box-shadow: 0 0 36px 0 rgba(0, 0, 0, .32);
            -moz-box-shadow: 0 0 36px 0 rgba(0, 0, 0, .32);
            box-shadow: 0 0 36px 0 rgba(0, 0, 0, .32);
            background: #fff;
            border: 1px solid var(--default-bg);
            color: var(--default-bg)
        }

        .tray2 i {
            font-size: 60px
        }

        .tray2 span {
            display: block;
            margin: 10px auto 0;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 1px;
            font-family: Poppins, sans-serif
        }
    </style>
@endsection
