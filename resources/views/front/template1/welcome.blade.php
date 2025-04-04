@extends('front.template1.header')
@section('content')



    <section class="form-12" id="home">
        <div class="">
            <div class="">
                <div class="grid">
                    <div class="column2">
                    </div>

                    <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
                        <div class="carousel-inner">
                            @foreach($frontbanner as $key => $banner)
                                <div class="carousel-item {{ $key == 0 ? ' active' : '' }}">
                                    <img class="d-block w-100" src="{{$cdnLink}}{{$banner->banners}}" alt="Slider Banner">
                                </div>
                            @endforeach
                        </div>
                        <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Previous</span>
                        </a>
                        <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Next</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="w3l-features-1">
        <!-- /features -->
        <div class="features py-4">
            <div class="container">

                <div class="fea-gd-vv row ">
                    <div class="float-lt feature-gd col-lg-3 col-sm-6">
                        <div class="icon"> <span class="fa fa-plus-circle" aria-hidden="true"></span></div>
                        <div class="icon-info">
                            <h5>Create your account</h5>
                        </div>

                    </div>
                    <div class="float-mid feature-gd col-lg-3 col-sm-6 mt-sm-0 mt-4">
                        <div class="icon"> <span class="fa fa-check-square" aria-hidden="true"></span></div>
                        <div class="icon-info">
                            <h5>Chose your plan</h5>
                        </div>
                    </div>
                    <div class="float-rt feature-gd col-lg-3 col-sm-6 mt-lg-0 mt-4">
                        <div class="icon"> <span class="fa fa-cc-paypal" aria-hidden="true"></span></div>
                        <div class="icon-info">
                            <h5>Pay plan amount</h5>
                        </div>
                    </div>
                    <div class="float-lt feature-gd col-lg-3 col-sm-6 mt-lg-0 mt-4">
                        <div class="icon"> <span class="fa fa-thumbs-up" aria-hidden="true"></span></div>
                        <div class="icon-info">
                            <h5>Enjoy Services</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- //features -->
    </section>
    <!---728x90--->

    <!-- content-with-photo4 block -->
    <section class="w3l-content-with-photo-4">
        <div id="content-with-photo4-block" class="pt-5">
            <div class="container py-md-5">
                <div class="cwp4-two row">

                    <div class="cwp4-text col-lg-6">
                        <h3>Welcome to {{ $company_name }}</h3>
                        <p>
                            {{ $company_name }} is on-line Portal developed to create a B2B Business System to enhance the chances of earning money for retailers. Following the competition we understand the business requirement of retailers and provides best rates and commission for our services.
                        </p>
                        <p>
                            We provides service like PANCARD, AEPS (Aadhar Enabled Payment System), DMT (Domestic Money Transfer), Bill Payments (Electricity,PostPaid,Telephone), Mobile & DTH Recharges.
                        </p>
                        <p>
                            On call solutions and Prompt Response is our key to service and our customer base . We are Best Money transfer, Mobile recharge , Electricity bill payment, AEPS, Pancard Service and Portal provider in PAN India.
                        </p>
                    </div>
                    <div class="cwp4-image col-lg-6 pl-lg-5 mt-lg-0 mt-5">
                        <img src="{{url('front/images/about-img.png')}}" class="img-fluid" alt="" />
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- content-with-photo4 block -->
    <!---728x90--->

    <!-- specifications -->
    <section class="w3l-specifications-9">
        <div class="main-w3 pb-5" id="stats">
            <div class="container py-md-5 mt-4">
                <div class="main-cont-wthree-fea row">
                    <div class="grids-speci1 col-lg-3 col-6">
                        <div class="spec-2">
                            <span class="fa fa-heart"></span>
                            <h3 class="title-spe">40450</h3>
                            <p>Our Clients</p>
                        </div>
                    </div>
                    <div class="grids-speci1 midd-eff-spe col-lg-3 col-6">
                        <div class="spec-2">
                            <span class="fa fa-thumbs-up"></span>
                            <h3 class="title-spe">13500</h3>
                            <p>Packages Delivered</p>
                        </div>
                    </div>
                    <div class="grids-speci1 las-but col-lg-3 col-6  mt-lg-0 mt-4">
                        <div class="spec-2">
                            <span class="fa fa-address-card-o"></span>
                            <h3 class="title-spe">1500</h3>
                            <p>Repeat Customers</p>
                        </div>
                    </div>
                    <div class="grids-speci1 las-t col-lg-3 col-6  mt-lg-0 mt-4">
                        <div class="spec-2">
                            <span class="fa fa-cog"></span>
                            <h3 class="title-spe">2000 </h3>
                            <p>Commercial Goods</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- //specifications -->
    </section>

    <!-- skills-1 block -->
    <section class="w3l-skills-1" id="features" style="display:none;">
        <div id="skills-1-block" class="py-5 bg bg2" data-selector=".bg.bg2">
            <div class="container py-md-5">
                <div class="team-1 row">
                    <div class="right-single-team col-lg-6">
                        <h6>Here are a few places to explore shipping.</h6>
                        <h3 class="mb-4">Are You a Sender?</h3>
                        <li><span class="fa fa-check"></span> Core freight</li>
                        <li><span class="fa fa-check"></span> Integrated logistics – LLP</li>
                        <li><span class="fa fa-check"></span> Strategic-Xpert</li>
                        <li><span class="fa fa-check"></span> One time solutions</li>
                        <li><span class="fa fa-check"></span> Geo-Gateways</li>
                    </div>
                    <div class="left-single-team  col-lg-6">
                        <h6>Things need to know about shipping.</h6>
                        <h3 class="mb-4">Are You a Shipper?</h3>
                        <li><span class="fa fa-check"></span> Customs & Tax Representation</li>
                        <li><span class="fa fa-check"></span> Reusable Packaging</li>
                        <li><span class="fa fa-check"></span> Warehousing</li>
                        <li><span class="fa fa-check"></span> Finished Vehicle Logistics</li>
                        <li><span class="fa fa-check"></span> Control Tower</li>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Teams14 block -->
    <!---728x90--->

    <section class="w3l-feature-2">
        <div class="grid top-bottom py-5">
            <div class="container py-md-5">
                <div class="heading text-center mx-auto">
                    <h3 class="head">Our Services</h3>
                    <p class="my-3 head">
                        We provide all online services like Mobile, DTH and Data Card Recharges, Postpaid, Electricty & Landline Bill Payment, AEPS, Money Transfers, PAN Card, Recharge and Bill Payment API.
                    </p>
                </div>
                <div class="middle-section row mt-5 pt-3">
                    <div class="three-grids-columns col-lg-4 col-sm-6 ">
                        <div class="icon"> <span class="fa fa-mobile" aria-hidden="true"></span></div>
                        <h4>Recharges</h4>
                        <p>Fast and secure way to recharge any mobile, any operator instantly through website, mobile app Quick Recharge all mobile operator.</p>
                        <a href="#" class="red mt-3">Read More <span class="fa fa-angle-right pl-1"></span></a>
                    </div>
                    <div class="three-grids-columns col-lg-4 col-sm-6 mt-sm-0 mt-5">
                        <div class="icon"> <span class="fa fa-lightbulb-o" aria-hidden="true"></span></div>
                        <h4>Bill Payment</h4>
                        <p>Pay Gas Bill, Postpaid Bill, Water Bill and Electricity bills in a seconds using our plartform and avoid late payment charges.</p>
                        <a href="#" class="red mt-3">Read More <span class="fa fa-angle-right pl-1"></span></a>
                    </div>
                    <div class="three-grids-columns col-lg-4 col-sm-6 mt-lg-0 mt-5">
                        <div class="icon"> <span class="fa fa-id-card" aria-hidden="true"></span></div>
                        <h4>Pan Card</h4>
                        <p>Our UTI Pan Service direct from UTIITSL & also we provide NSDL Pan Service through NSDL software. Pan allote within 3-5 days.</p>
                        <a href="#" class="red mt-3">Read More <span class="fa fa-angle-right pl-1"></span></a>
                    </div>
                </div>
                <div class="middle-section row mt-5 pt-3">
                    <div class="three-grids-columns col-lg-4 col-sm-6 ">
                        <div class="icon"> <span class="fa fa-money" aria-hidden="true"></span></div>
                        <h4>Money Transfer</h4>
                        <p>Transfer money to more than 200 banks in India. Instant and easy DMR service allows you to transfer money to any bank account in India.</p>
                        <a href="#" class="red mt-3">Read More <span class="fa fa-angle-right pl-1"></span></a>
                    </div>
                    <div class="three-grids-columns col-lg-4 col-sm-6 mt-sm-0 mt-5">
                        <div class="icon"> <span class="fa fa-university" aria-hidden="true"></span></div>
                        <h4>AEPS</h4>
                        <p>Aadhar Enabled Payment System is safe and secure banking system. Balace Inquiry, Cash Withdrawal, Mini Statement Available in AEPS.</p>
                        <a href="#" class="red mt-3">Read More <span class="fa fa-angle-right pl-1"></span></a>
                    </div>
                    <div class="three-grids-columns col-lg-4 col-sm-6 mt-lg-0 mt-5">
                        <div class="icon"> <span class="fa fa-calculator" aria-hidden="true"></span></div>
                        <h4>Micro ATM</h4>
                        <p>Accept Payments or Withdraw on Your Smartphone/Tablet through our mATM Solutions. Credit/Debit Card Accepted, Real-Time Settlement.</p>
                        <a href="#" class="red mt-3">Read More <span class="fa fa-angle-right pl-1"></span></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!--customers-7-->
    <section class="w3l-customers-8" id="testimonials">
        <div class="customers_sur py-5">
            <div class="container py-md-5">
                <div class="heading text-center mx-auto">
                    <h3 class="head text-white">Words From Our Clients</h3>
                    <p class="my-3 head text-white">
                        <!--                    Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae;-->
                        <!--                    Nulla mollis dapibus nunc, ut rhoncus-->
                        <!--                    turpis sodales quis. Integer sit amet mattis quam.-->
                    </p>
                </div>
                <div class="customers-top_sur row mt-5 pt-3">
                    <div class="customers-left_sur col-md-6">
                        <div class="customers_grid">

                            <p class="sub-test"><span class="fa fa-quote-left"></span>
                                I have never seen such a great quality. Good luck and keep it up! Thanks for their support which helps us to grow our business.
                            </p>

                        </div>
                        <div class="customers-bottom_sur row">
                            <div class="custo-img-res col-2">
                                <!-- <img src="images/te2.jpg" alt=" " class="img-responsive"> -->
                            </div>
                            {{--<div class="custo_grid col-10">
                                <h5 class="text-white">Drx Laxman Singh Sonigara</h5>
                                <span>Client</span>
                            </div>--}}

                        </div>
                    </div>
                    <div class="customers-middle_sur col-md-6 mt-md-0 mt-4">
                        <div class="customers_grid">

                            <p class="sub-test"><span class="fa fa-quote-left"></span>
                                Their quality services lead us to achieve new grwoth in market.Thanks to webtech solution.net for their outstanding support and services.
                            </p>

                        </div>
                        <div class="customers-bottom_sur row">
                            <div class="custo-img-res col-2">
                                <!-- <img src="images/te1.jpg" alt=" " class="img-responsive"> -->
                            </div>
                            {{--<div class="custo_grid col-10">
                                <h5 class="text-white">Lokesh Agarwal</h5>
                                <span>Client</span>
                            </div>--}}

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--//customers-7-->

    <section class="grids-4" id="news">
        <div id="grids4-block" class="py-5">
            <div class="container py-md-5">
                <div class="heading text-center mx-auto">
                    <h3 class="head">Available Platform</h3>
                    <p class="my-3 head"> </p>
                </div>
                <div class="row mt-5 pt-3">
                    <div class="grids4-info  col-lg-4 col-md-6">

                        <div class="info-bg editContent">
                            <div class="icon"> <span class="fa fa-desktop" aria-hidden="true" style="font-size: 50px; color: red;"></span></div>
                            <h5 class="mt-4 mb-3 editContent"><a class="editContent">Web Application</a></h5>
                            <p>
                                You can recharge using Web Application. Recharge easily in few clicks. Choose from secure wallet options provided by us.
                            </p>
                        </div>
                    </div>
                    <div class="grids4-info col-lg-4 col-md-6 mt-md-0 mt-4">

                        <div class="info-bg editContent">
                            <div class="icon"> <span class="fa fa-android" aria-hidden="true" style="font-size: 50px; color: red;"></span></div>
                            <h5 class="mt-4 mb-3 editContent"><a class="editContent">Android</a></h5>
                            <p>
                                You can recharge using Android Application. Recharge easily in few clicks. Choose from secure wallet options provided by us.
                            </p>
                        </div>
                    </div>
                    <div class="grids4-info col-lg-4 col-md-6 offset-lg-0 offset-md-3 mt-lg-0 mt-4">

                        <div class="info-bg editContent">
                            <div class="icon"> <span class="fa fa-mobile" aria-hidden="true" style="font-size: 50px; color: red;"></span></div>
                            <h5 class="mt-4 mb-3 editContent"><a class="editContent">SMS</a></h5>
                            <p>
                                You can recharge using SMS Offline Service. Recharge easily in few clicks. Choose from secure wallet options provided by us.
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>



@endsection