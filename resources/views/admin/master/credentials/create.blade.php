@extends('admin.layout.header')
@section('content')
    <style>
        .modal-dialog-slideout {min-height: 100%; margin: 0 0 0 auto;background: #fff;}
        .modal.fade .modal-dialog.modal-dialog-slideout {-webkit-transform: translate(100%,0)scale(1);transform: translate(100%,0)scale(1);}
        .modal.fade.show .modal-dialog.modal-dialog-slideout {-webkit-transform: translate(0,0);transform: translate(0,0);display: flex;align-items: stretch;-webkit-box-align: stretch;height: 100%;}
        .modal.fade.show .modal-dialog.modal-dialog-slideout .modal-body{overflow-y: auto;overflow-x: hidden;}
        .modal-dialog-slideout .modal-content{border: 0;}
        .modal-dialog-slideout .modal-header, .modal-dialog-slideout .modal-footer {height: 69px; display: block;}
        .modal-dialog-slideout .modal-header h5 {float:left;}
    </style>
    <div class="main-content-body">
        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">{{ $page_title }}</h4>
                            <button class="btn btn-danger btn-sm">Create Credentials Record</button>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                        </div>
                        <hr>
                    </div>
                    <form action="{{ route('admin.master.credentials.store') }}" method="post">
                    @csrf
                    <div class="card-body">
                        <div class="form-body">
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Name</label>
                                        <input name="name" type="text" id="name" class="form-control" placeholder="First Name">
                                        @if ($errors->has('name'))
                                            {{-- <span class="text-danger">{{ $errors->first('name') }}</span> --}}
                                            <ul class="parsley-errors-list filled">
                                                <li class="parsley-required">{{ $errors->first('name') }}</li>
                                            </ul>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">API Key</label>
                                        <input name="api_key" type="text" id="api_key" class="form-control" placeholder="API Key">
                                        @if ($errors->has('api_key'))
                                            {{-- <span class="text-danger">{{ $errors->first('name') }}</span> --}}
                                            <ul class="parsley-errors-list filled">
                                                <li class="parsley-required">{{ $errors->first('api_key') }}</li>
                                            </ul>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="name">Salt Key</label>
                                        <input name="salt_key" type="text" id="salt_key" class="form-control" placeholder="Salt Key">
                                        @if ($errors->has('salt_key'))
                                            {{-- <span class="text-danger">{{ $errors->first('name') }}</span> --}}
                                            <ul class="parsley-errors-list filled">
                                                <li class="parsley-required">{{ $errors->first('salt_key') }}</li>
                                            </ul>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-danger waves-effect waves-light">
                            Save Details
                        </button>
                    </div>
                    </form>
                </div>
            </div>
            <!--/div-->
        </div>
    </div>
    </div>
    </div>
@endsection