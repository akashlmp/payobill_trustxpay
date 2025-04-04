@php
    $currentRouteName = Route::currentRouteName();
@endphp
<div id="docs-sidebar" class="docs-sidebar">
    <nav id="docs-nav" class="docs-nav navbar">
        <ul class="section-items list-unstyled nav flex-column pb-3">
            <li class="nav-item section-title">
                <a class="nav-link  {{ $currentRouteName == 'documentation.dynamicQRPayinApi' ? 'active' : '' }} " href="{{ route('documentation.dynamicQRPayinApi') }}">
                    <span class="theme-icon-holder me-2"><i class="fa fa-arrow-right"></i></span>
                    Payin API
                </a>
            </li>
            <li class="nav-item section-title">
                <a class="nav-link  {{ $currentRouteName == 'documentation.dynamicQRStatusApi' ? 'active' : '' }}" href="{{ route('documentation.dynamicQRStatusApi') }}">
                    <span class="theme-icon-holder me-2"><i class="fa fa-arrow-right"></i></span>
                    Status API
                </a>
            </li>
            <li class="nav-item section-title">
                <a class="nav-link  {{ $currentRouteName == 'documentation.dynamicQRWebhooks' ? 'active' : '' }}" href="{{ route('documentation.dynamicQRWebhooks') }}">
                    <span class="theme-icon-holder me-2"><i class="fa fa-arrow-right"></i></span>
                    Webhooks
                </a>
            </li>
        </ul>
    </nav>
</div>