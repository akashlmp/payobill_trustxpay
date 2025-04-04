@php
    $currentRouteName = Route::currentRouteName();
@endphp
<div id="docs-sidebar" class="docs-sidebar">
    <nav id="docs-nav" class="docs-nav navbar">
        <ul class="section-items list-unstyled nav flex-column pb-3">
            <li class="nav-item section-title">
                <a class="nav-link  {{ $currentRouteName == 'documentation.staticQRApi' ? 'active' : '' }} " href="{{ route('documentation.staticQRApi') }}">
                    <span class="theme-icon-holder me-2"><i class="fa fa-arrow-right"></i></span>
                    Get Static QR
                </a>
            </li>
            <li class="nav-item section-title">
                <a class="nav-link  {{ $currentRouteName == 'documentation.staticQRCreateApi' ? 'active' : '' }}" href="{{ route('documentation.staticQRCreateApi') }}">
                    <span class="theme-icon-holder me-2"><i class="fa fa-arrow-right"></i></span>
                    Create Static QR
                </a>
            </li>
            <li class="nav-item section-title">
                <a class="nav-link  {{ $currentRouteName == 'documentation.staticQRWebhooks' ? 'active' : '' }}" href="{{ route('documentation.staticQRWebhooks') }}">
                    <span class="theme-icon-holder me-2"><i class="fa fa-arrow-right"></i></span>
                    Webhooks
                </a>
            </li>
        </ul>
    </nav>
</div>
