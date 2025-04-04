@extends('admin.layout.header')
@section('content')

    <script type="text/javascript"></script>

    <div class="main-content-body">
        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">View Role</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                            <a href="{{ route('admin.roles') }}" class="btn btn-warning btn-sm">
                                <i class="ti ti-plus"></i> Back
                            </a>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        @if (count($role_permissions))
                        <div class="row">
                            @foreach ($role_permissions as $key => $rolePermission)
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0">{{ ucfirst($key) }}</h5>
                                        </div>
                                        <div class="card-body">
                                            @foreach ($rolePermission as $key1 => $item)
                                                <h5 class="card-title mb-0">{{ ucfirst($key1) }}</h5>
                                                @foreach ($item as $permission)
                                                    <span class="btn btn-primary btn-sm">{{ $permission->label ?? '' }}</span>
                                                @endforeach
                                                <br><br>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    </div>
                </div>
            </div>
            <!--/div-->

        </div>

    </div>
    </div>
    </div>


    {{-- update role modal --}}
    <div class="modal fade" id="view_role_model" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-slideout" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Update Roles</h6>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <input type="hidden" id="view_id">

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="name">Role Title</label>
                                    <input type="text" id="view_role_title" class="form-control"
                                        placeholder="Role Title">
                                    <ul class="parsley-errors-list filled">
                                        <li class="parsley-required" id="view_role_title_errors"></li>
                                    </ul>
                                </div>
                            </div>


                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="update_btn" onclick="update_roles()">Save
                        changes</button>
                    <button class="btn btn-primary" type="button" id="update_btn_loader" disabled
                        style="display: none;"><span class="spinner-border spinner-border-sm" role="status"
                            aria-hidden="true"></span> Loading...</button>
                </div>
            </div>
        </div>
    </div>



    <style>
        .modal-dialog-slideout {
            min-height: 100%;
            margin: 0 0 0 auto;
            background: #fff;
        }

        .modal.fade .modal-dialog.modal-dialog-slideout {
            -webkit-transform: translate(100%, 0)scale(1);
            transform: translate(100%, 0)scale(1);
        }

        .modal.fade.show .modal-dialog.modal-dialog-slideout {
            -webkit-transform: translate(0, 0);
            transform: translate(0, 0);
            display: flex;
            align-items: stretch;
            -webkit-box-align: stretch;
            height: 100%;
        }

        .modal.fade.show .modal-dialog.modal-dialog-slideout .modal-body {
            overflow-y: auto;
            overflow-x: hidden;
        }

        .modal-dialog-slideout .modal-content {
            border: 0;
        }

        .modal-dialog-slideout .modal-header,
        .modal-dialog-slideout .modal-footer {
            height: 69px;
            display: block;
        }

        .modal-dialog-slideout .modal-header h5 {
            float: left;
        }
    </style>
@endsection
