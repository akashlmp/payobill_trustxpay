@extends('merchant.layouts.main')
@section('title','Merchant Dashboard')
@section('content')
    <div class="main-content-body">
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
        <div class="row row-sm ">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                <div class="card overflow-hidden">
                    <div class="card-header bg-transparent pd-b-0 pd-t-20 bd-b-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-10">Services</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body pd-y-7">
                        <div class="row no-gutters">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('customCss')
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
@section('customScript')
@endsection
