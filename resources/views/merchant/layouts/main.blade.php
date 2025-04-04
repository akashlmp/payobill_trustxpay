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
    <script src="{{url('assets/plugins/jquery/jquery.min.js')}}"></script>
    @yield('customCss')

    @include('merchant.layouts.style')
</head>

<body class="main-body app">


<div class="loader" style="display: none;"></div>

{{--Header top--}}
@include('merchant.layouts.header1')

{{--Header top second--}}
@include('merchant.layouts.header2')


<!-- main-content opened -->
<div class="main-content horizontal-content">
    <!-- container opened -->
    <div class="container">
        <!-- breadcrumb -->
        <div class="breadcrumb-header justify-content-between">
            <div>
                <h4 class="content-title mb-2">
                    Hi, {{ Auth::guard('merchant')->user()->first_name }} {{ Auth::guard('merchant')->user()->last_name }}
                    welcome back!</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{url('admin/dashboard')}}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $page_title  }}</li>
                    </ol>
                </nav>
            </div>
        </div>

        @yield('content')

    </div>
    <!-- /container -->
</div>
<!-- /main-content -->


<!-- Footer opened -->
@include('merchant.layouts.footer')
<!-- Footer closed -->

<input type="hidden"  name="latitude" id="inputLatitude" value="0.00">
<input type="hidden"  name="longitude" id="inputLongitude" value="0.00">
<!--- Back-to-top --->
<a href="#top" id="back-to-top"><i class="las la-angle-double-up"></i></a>




<script src="{{url('assets/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<script src="{{url('assets/plugins/ionicons/ionicons.js')}}"></script>
<script src="{{url('assets/plugins/select2/js/select2.min.js')}}"></script>>
<script src="{{url('assets/plugins/horizontal-menu/horizontal-menu.js')}}"></script>
<script src="{{url('assets/js/eva-icons.min.js')}}"></script>
<!--- Datepicker js --->
<script src="{{url('assets/plugins/jquery-ui/ui/widgets/datepicker.js')}}"></script>
<script src="{{url('assets/plugins/moment/moment.js')}}"></script>
<!--- Validation js --->
<script src="{{ url('assets/js/jquery.validate.min.js') }}"></script>
<script src="{{ url('assets/js/additional-methods.min.js') }}"></script>
<!--- Datatable js --->
<script src="{{url('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
<script src="{{url('assets/plugins/datatable/js/dataTables.dataTables.min.js')}}"></script>
<script src="{{url('assets/plugins/datatable/js/responsive.dataTables.min.js')}}"></script>
<script src="{{url('assets/plugins/datatable/js/jquery.dataTables.js')}}"></script>
<script src="{{url('assets/plugins/datatable/js/dataTables.bootstrap4.js')}}"></script>
<script src="{{url('assets/plugins/datatable/js/dataTables.buttons.min.js')}}"></script>
<script src="{{url('assets/plugins/datatable/js/dataTables.responsive.min.js')}}"></script>
<script src="{{url('assets/plugins/datatable/js/responsive.bootstrap4.min.js')}}"></script>
<script src="{{url('assets/js/table-data.js')}}"></script>

<!--- Custom js --->
<script src="{{url('assets/js/custom.js')}}"></script>
<script src="{{url('assets/plugins/sweet-alert/sweetalert.min.js')}}"></script>
<script src="{{url('assets/plugins/sweet-alert/jquery.sweet-alert.js')}}"></script>
<script src="{{url('assets/js/common.js')}}"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
    $(document).ready(function () {
        getLocation();
    });

    function buttonDisabled(target) {
        $(target).attr('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
    }

    function buttonEnabled(target, html) {
        $(target).attr('disabled', false).html(html);
    }

    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition);
        } else {
            x.innerHTML = "Geolocation is not supported by this browser.";
        }
    }

    function showPosition(position) {
        $("#inputLatitude").val(position.coords.latitude);
        $("#inputLongitude").val(position.coords.longitude);
    }
</script>
@yield('customScript')
@csrf
</body>
</html>
