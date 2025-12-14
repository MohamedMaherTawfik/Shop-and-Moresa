@php
    use App\Utils\Helpers;
    use App\Enums\EmailTemplateKey;
    $eCommerceLogo = getWebConfig(name: 'company_web_logo');
@endphp

<aside class="js-aside aside d-none d-lg-block">
    <div class="aside-header d-flex align-items-center gap-2 justify-content-between">
        <a class="navbar-logo" href="{{ route('admin.dashboard.index') }}">
            <img height="24" src="{{ getStorageImages(path: $eCommerceLogo, type: 'backend-logo') }}"
                 alt="{{ translate('logo') }}">
        </a>
        <button type="button" class="js-aside-toggle navbar-aside-toggle btn-icon border-0">
            <i class="fi fi-rr-menu-burger"></i>
        </button>
    </div>
    <div class="aside-body search-aside-attribute-container py-4 pt-0">

        <ul class="aside-nav navbar-nav gap-2">
            <li>
                <a class="nav-link {{ Request::is('admin/dashboard') ? 'active' : '' }}"
                   title="{{ translate('dashboard') }}" href="{{ route('admin.dashboard.index') }}">
                    <i class="fi fi-sr-home"></i>
                    <span class="aside-mini-hidden-element text-truncate">
                        {{ translate('dashboard') }}
                    </span>
                </a>
            </li>
            @if(Helpers::module_permission_check('support_section'))
                <li>
                    <a class="nav-link {{ Request::is('admin/support-ticket*') ? 'active' : '' }}"
                       href="{{ route('admin.support-ticket.view') }}" title="{{ translate('support_Ticket') }}">
                        <i class="fi fi-sr-headphones"></i>
                        <span class="aside-mini-hidden-element text-truncate">
                            <span class="position-relative">
                                {{ translate('support_Ticket') }}
                                @if(\App\Models\SupportTicket::where('status','open')->count()>0)
                                    <span
                                        class="btn-status btn-xs-status btn-status-danger position-absolute top-0 menu-status"></span>
                                @endif
                            </span>
                        </span>
                    </a>
                </li>
            @endif



            <?php $checkSetupGuideRequirements = checkSetupGuideRequirements(panel: 'admin'); ?>

            <li class="nav-item {{ $checkSetupGuideRequirements['completePercent'] < 100 ? 'pt-5 mt-5 d-none d-lg-block' : '' }}">
                <div class="pt-4"></div>
            </li>
        </ul>
    </div>
</aside>

@include("layouts.admin.partials._setup-guide")

<div class="offcanvas offcanvas-start bg-panel d-lg-none w-280" tabindex="-1" id="offcanvasAside"
     aria-labelledby="offcanvasAsideLabel">
    <div class="offcanvas-header d-flex align-items-center gap-2 justify-content-between">
        <a class="navbar-logo" href="{{ route('admin.dashboard.index') }}">
            <img height="24" src="{{ getStorageImages(path: $eCommerceLogo, type: 'backend-logo') }}"
                 alt="{{ translate('logo') }}">
        </a>

        <button type="button" class="bg-transparent p-0 text-white border-0" data-bs-dismiss="offcanvas"
                aria-label="Close">
            <i class="fi fi-rr-cross"></i>
        </button>
    </div>

    <div class="offcanvas-body js-offcanvas-body pt-0">

    </div>
</div>
