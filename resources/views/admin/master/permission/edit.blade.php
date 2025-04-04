@extends('admin.layout.header')
@section('content')
    <script type="text/javascript"></script>

    <div class="main-content-body">
        {{-- perssinal details --}}
        <div class="row row-sm">
            <div class="col-xl-12">

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="row" action="{{ route('admin.role.update', base64Encode($role->id)) }}"
                                    method="post">
                                    @csrf
                                    <div class="col-12 form-group">
                                        <label for="inputNanme4" class="form-label">Name</label>
                                        <input type="text" name="name" class="form-control" id="inputNanme4"
                                            value="{{ $role->name ?? '' }}">
                                        @if ($errors->has('name'))
                                            <span class="text-danger">{{ $errors->first('name') }}</span>
                                        @endif
                                    </div>
                                    <div class="col-12">
                                        <ul class="nav nav-pills mb-4" id="myTab" role="tablist">
                                            @foreach ($modules as $module)
                                                <li class="nav-item" role="presentation">
                                                    <a class="nav-link @if ($loop->first) active @endif"
                                                        id="{{ $loop->iteration }}-tab" data-toggle="tab"
                                                        href="#tab-{{ $loop->iteration }}" role="tab"
                                                        aria-controls="{{ $loop->iteration }}" aria-selected="true">
                                                        {{ ucwords($module) }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                        <div class="tab-content" id="myTabContent">
                                            @foreach ($modules as $module)
                                                <div class="tab-pane fade @if ($loop->first) active show @endif"
                                                    id="tab-{{ $loop->iteration }}" role="tabpanel"
                                                    aria-labelledby="{{ $loop->iteration }}-tab">
                                                    <div class="row">
                                                        @foreach ($permission[$module] as $key => $subModule)
                                                            <div class="col-md-12">
                                                                <h5 class="card-title mb-1">{{ ucfirst($key) }}</h5>
                                                                <div class="row">
                                                                    @foreach ($subModule as $value)
                                                                        <div class="col-3 mb-2">
                                                                            <div class="form-check form-check-inline">
                                                                                <input class="form-check-input"
                                                                                    name="permission[]" type="checkbox"
                                                                                    id="inlineCheckbox1{{ $value->id }}"
                                                                                    value="{{ $value->id }}"
                                                                                    @if (in_array($value->id, $role_permissions)) checked @endif>
                                                                                <label class="form-check-label"
                                                                                    for="inlineCheckbox1{{ $value->id }}">{{ $value->label }}</label>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                        <a href="{{ route('admin.roles') }}" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </form><!-- Vertical Form -->

                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <!--/div-->
        </div>
        {{-- perssinal details clase --}}




    </div>
    </div>
    </div>
@endsection
