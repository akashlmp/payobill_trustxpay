<header class="header fixed-top">
    <div class="branding docs-branding">
        <div class="container-fluid position-relative py-2">
            <div class="docs-logo-wrapper">
                <button id="docs-sidebar-toggler" class="docs-sidebar-toggler docs-sidebar-visible me-2 d-xl-none" type="button">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <div class="site-logo">
                    <a class="navbar-brand" href="javascript:void(0);">
                        <img src="{{ $cdnLink}}{{ $company_logo }}" class="logo-icon" width="100">
                    </a>
                </div>
            </div>
            <div class="docs-top-utilities d-flex justify-content-end align-items-center">
                @if (!Auth::check())
                    {{-- <a target="_blank" href="{{ url('login') }}" class="btn btn-primary d-none d-lg-flex btn-sm">Sign In</a> --}}
                @endif
                <a href="{{ route('documentation.list') }}" class="btn btn-warning mx-1 d-none d-lg-flex btn-sm">Back</a>
            </div>
        </div>
    </div>
</header>
