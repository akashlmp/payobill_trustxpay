@extends('admin.layout.header')
@section('content')

<script>
    $(document).on('click', '.deleteRow', function () {
        let id = $(this).data('id');
        var token = $("input[name=_token]").val();
        swal({
            title: "<small style='font-size: 20px;'>Are you sure want to delete this role?</small>",
            text: '',
            html: true,
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-primary btn-sm mt-3",
            cancelButtonClass: "btn-secondary btn-sm mt-3",
            confirmButtonText: "Yes",
            closeOnConfirm: false,
            showLoaderOnConfirm: true
        }, function (result) {
            if (result) {
                $.ajax({
                    method: 'POST',
                    url: "{{url('/')}}" + '/admin/role/destroy',
                    data: "id=" + id + "&_token="+token,
                    success: function (result) {
                        if (result.status == 1) {
                            swalSuccessReload(result.message);
                        } else {
                            swalError(result.message);
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status === 401) {
                            swalError('You are not authorized to access this resource.');
                        }
                    }
                });
            }
        });

    });
</script>

    <div class="main-content-body">
        <div class="row row-sm">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h4 class="card-title mg-b-2 mt-2">Roles</h4>
                            <i class="mdi mdi-dots-horizontal text-gray"></i>
                            <a href="{{ route('admin.role.create') }}" class="btn btn-success btn-sm">
                                <i class="ti ti-plus"></i> Create Role
                            </a>
                        </div>
                        <hr>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table
                                class="@if (Auth::User()->company->table_format == 1) table text-md-nowrap @else display responsive nowrap @endif"
                                id="example1">
                                <thead>
                                    <tr>
                                        <th class="wd-25p border-bottom-0">Role Name</th>
                                        <th class="wd-25p border-bottom-0">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($roles))
                                        @foreach ($roles as $role)
                                            <tr>
                                                <th scope="row">{{ $role->id }}</th>
                                                <td>{{ $role->name }}</td>
                                                <td>
                                                    <div class="text-center">

                                                            <a href="{{ route('admin.role.show', base64Encode($role->id)) }}"
                                                                class="btn btn-sm btn-warning"><i class="ti ti-eye"></i></a>

                                                            <a href="{{ route('admin.role.edit', base64Encode($role->id)) }}"
                                                                class="btn btn-sm btn-primary"><i
                                                                    class="ti ti-pencil"></i></a>

                                                            <a href="javascript:;" data-id="{{base64Encode($role->id)}}"
                                                                data-url="{{ route('admin.role.destroy', base64Encode($role->id)) }}"
                                                                class="btn btn-sm btn-danger deleteRow"><i
                                                                    class="ti ti-trash"></i></a>

                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr class="text-center">
                                            <td colspan="99">No record available!</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!--/div-->

        </div>

    </div>
    </div>
    </div>


@endsection
