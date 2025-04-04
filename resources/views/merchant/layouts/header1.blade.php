<!-- main-header opened -->
<div class="main-header nav nav-item hor-header">
    <div class="container">
        <div class="main-header-left " style="width: calc(100% - 165px);">
            <a class="animated-arrow hor-toggle horizontal-navtoggle"><span></span></a><!-- sidebar-toggle-->
            <a class="header-brand" href="{{url('agent/dashboard')}}">
                <img src="{{$cdnLink}}{{$company_logo}}" class="logo-white ">
                <img src="{{$cdnLink}}{{$company_logo}}" class="logo-default">
                <img src="{{$cdnLink}}{{$company_logo}}" class="icon-white">
                <img src="{{$cdnLink}}{{$company_logo}}" class="icon-default">
            </a>
        </div><!-- search -->

        <div class="main-header-right">


            <div class="dropdown main-profile-menu nav nav-item nav-link">
                <a class="profile-user d-flex" href="">
                    <img src="{{url('assets/img/profile-pic.jpg')}}" alt="user-img"
                         class="rounded-circle mCS_img_loaded">
                </a>
                <div class="dropdown-menu">
                    <div class="main-header-profile header-img">
                        <div class="main-img-user"><img alt="" src="{{url('assets/img/profile-pic.jpg')}}"></div>
                        <h6>{{ Auth::guard('merchant')->user()->first_name }} {{ Auth::guard('merchant')->user()->last_name }}</h6>
                    </div>
                    <a class="dropdown-item" href="{{url('merchant/my-profile')}}"><i class="far fa-user"></i> My Profile</a>
                    <a class="dropdown-item" href="{{url('merchant/my-settings')}}"><i class="fas fa-user-cog"></i> Settings</a>
                    <a class="dropdown-item" href="{{ route('merchant.logout') }}"><i class="fas fa-sign-out-alt"></i>Logout</a>
                </div>
            </div>
        </div>
    </div>
</div>
