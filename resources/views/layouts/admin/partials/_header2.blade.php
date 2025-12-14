<header class="header fixed-top navbar-fixed shadow-sm bg-white">
    <div class="d-flex align-items-center justify-content-between gap-3">

        {{-- Sidebar Toggle --}}
        <div>
            <button type="button" class="d-none d-lg-block btn-icon border-0">
                <i class="fi fi-rr-menu-burger"></i>
            </button>

            <button type="button" class="d-lg-none p-0 bg-transparent border-0"
                data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasAside">
                <i class="fi fi-rr-menu-burger"></i>
            </button>
        </div>

        {{-- Right Content --}}
        <div class="navbar-nav-wrap-content-right">
            <ul class="navbar-nav align-items-center flex-row gap-3">

                {{-- Website Link --}}
                <li class="nav-item">
                    <a class="btn-icon" href="{{ route('home') }}" target="_blank"
                        data-bs-toggle="tooltip"
                        data-bs-title="{{ translate('Website') }}">
                        <i class="fi fi-rr-globe fs-18"></i>
                    </a>
                </li>

                {{-- Language --}}
                <li class="nav-item">
                    @php($local = session('local', 'en'))
                    @php($lang = \App\Models\BusinessSetting::where('type', 'language')->first())

                    <div class="dropdown">
                        <a class="btn-icon" href="javascript:" data-bs-toggle="dropdown">
                            @foreach (json_decode($lang->value, true) as $data)
                                @if ($data['code'] == $local)
                                    <img width="20"
                                        src="{{ dynamicAsset(path: 'public/assets/front-end/img/flags/' . $data['code'] . '.png') }}"
                                        alt="{{ $data['name'] }}">
                                @endif
                            @endforeach
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end">
                            @foreach (json_decode($lang->value, true) as $data)
                                @if ($data['status'] == 1)
                                    <li class="change-language"
                                        data-action="{{ route('change-language') }}"
                                        data-language-code="{{ $data['code'] }}">
                                        <a class="dropdown-item d-flex align-items-center gap-2 {{ $data['code'] == $local ? 'active' : '' }}"
                                           href="javascript:">
                                            <img width="20"
                                                src="{{ dynamicAsset(path: 'public/assets/front-end/img/flags/' . $data['code'] . '.png') }}">
                                            <span>{{ $data['name'] }}</span>
                                            {!! $data['code'] == $local ? '<i class="fi fi-rr-check ms-auto"></i>' : '' !!}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </li>

                {{-- Profile --}}
                <li class="nav-item">
                    <div class="dropdown">
                        <a class="d-flex" href="javascript:" data-bs-toggle="dropdown">
                            <img class="rounded-circle border border-2 min-w-36 aspect-1"
                                width="36"
                                src="{{ getStorageImages(path: auth()->user()->image_full_url, type: 'backend-profile') }}"
                                alt="profile">
                        </a>

                        <div class="dropdown-menu dropdown-menu-end">
                            <div class="dropdown-item">
                                <h6 class="fw-bold mb-0 text-truncate" style="font-size: 22px">
                                    {{ auth()->user()->name }}
                                </h6>
                                <small class="text-muted" style="font-size:14px">
                                    {{ auth()->user()->role }}
                                </small>
                            </div>

                            <div class="dropdown-divider"></div>


                            <a class="dropdown-item d-flex align-items-center gap-2"
                                href="javascript:"
                                data-bs-toggle="modal"
                                data-bs-target="#sign-out-modal">
                                <i class="fi fi-sr-sign-out-alt text-danger"></i>
                                {{ translate('logout') }}
                            </a>
                        </div>
                    </div>
                </li>

            </ul>
        </div>

    </div>
</header>


@push('script')
    <script>
        let currentIndex = -1;

        function getItems() {
            return $('.search-list .search-item-wrapper');
        }

        function updateHighlight(index) {
            const items = getItems();
            items.removeClass('active-item');

            if (index >= 0 && index < items.length) {
                const currentItem = $(items[index]);
                currentItem.addClass('active-item');

                const link = currentItem.find('.search-list-item')[0];

                if (link) {
                    const container = document.querySelector('#searchResults');
                    const itemRect = link.getBoundingClientRect();
                    const containerRect = container.getBoundingClientRect();

                    if (itemRect.top < containerRect.top || itemRect.bottom > containerRect.bottom) {
                        link.scrollIntoView({
                            behavior: 'auto',
                            block: 'nearest'
                        });
                    }
                }
            }
        }

        $(document).ready(function() {

            $('#search-btn').on('click', function() {
                $('#advanceSearchModal').modal('show');
                $('#advanceSearchModal').on('shown.bs.modal', function() {
                    $('#advance-search-input-global').focus();
                });
            });

            const platform = navigator.platform;
            let shortcutText = '';
            let isMac = false;

            if (platform.toLowerCase().includes('mac')) {
                shortcutText = 'Cmd+K';
                isMac = true;
            } else if (platform.toLowerCase().includes('linux') || platform.toLowerCase().includes('win')) {
                shortcutText = 'Ctrl+K';
                isMac = false;
            } else {
                shortcutText = 'Ctrl+K';
                isMac = false;
            }

            const currentPlaceholder = "{{ translate('Search_or') }}";
            $('#search-input').html(
                `${currentPlaceholder} <span class="search-shortcut-key-wrapper">${shortcutText}</span>`);

            $(document).keydown(function(event) {
                if ((event.ctrlKey && !isMac) || (event.metaKey && isMac)) {
                    if (event.key === 'k' || event.key === 'K') {
                        event.preventDefault();

                        $('#advanceSearchModal').modal('show');
                        $('#advanceSearchModal').on('shown.bs.modal', function() {
                            $('#advance-search-input-global').focus();
                        });
                    }
                }
                if (event.key === 'Escape') {
                    if ($('#advanceSearchModal').hasClass('show')) {
                        $('#advanceSearchModal').modal('hide');
                    }
                }
            });

            $('#advanceSearchModal').on('hidden.bs.modal', function() {
                $('#advanceSearchModal').off('shown.bs.modal');
            });


            let currentRequest = null;
            let debounceTimer = null;

            $('#advance-search-input-global').on('input', function() {
                const searchKeyword = $(this).val().trim();

                clearTimeout(debounceTimer);


                if (searchKeyword === '') {
                    if (currentRequest && currentRequest.readyState !== 4) {
                        currentRequest.abort();
                    }
                    toggleSearchLoader('show');
                    $.ajax({
                        type: 'GET',
                        url: '{{ route('admin.advanced-search') }}',
                        success: function(response) {
                            $('#searchResults').empty().html(response.htmlView);
                            toggleSearchLoader('hide');
                        },
                        error: function(xhr, status, error) {
                            console.error(xhr.responseText);
                        }
                    });

                    return;
                }

                debounceTimer = setTimeout(function() {
                    if (currentRequest && currentRequest.readyState !== 4) {
                        currentRequest.abort();
                    }

                    toggleSearchLoader('show');

                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    currentRequest = $.ajax({
                        type: 'GET',
                        url: '{{ route('admin.advanced-search') }}',
                        data: {
                            keyword: searchKeyword,

                        },
                        success: function(response) {
                            $('#searchResults').empty().html(response.htmlView);

                            if (currentIndex === -1) {
                                currentIndex = 0;
                            }
                            updateHighlight(currentIndex);
                            toggleSearchLoader('hide');
                        },
                        error: function(xhr, status, error) {
                            if (status !== 'abort') {
                                console.error('Search error:', error);
                            }
                            toggleSearchLoader('hide');
                        }
                    });

                }, 300);
            });
            $('#advance-search-input-global').on('focus', function() {
                const searchKeyword = $(this).val().trim();

                if (searchKeyword.length > 0) {
                    if (currentRequest && currentRequest.readyState !== 4) {
                        currentRequest.abort();
                    }
                    toggleSearchLoader('show');
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    currentRequest = $.ajax({
                        type: 'GET',
                        url: '{{ route('admin.advanced-search') }}',
                        data: {
                            keyword: searchKeyword
                        },
                        success: function(response) {
                            $('#searchResults').empty().html(response.htmlView);

                            if (currentIndex === -1) {
                                currentIndex = 0;
                            }
                            toggleSearchLoader('hide');
                            updateHighlight(currentIndex);
                        },
                        error: function(xhr, status, error) {
                            if (status !== 'abort') {
                                console.error('Search error:', error);
                            }
                        }
                    });
                } else {
                    $.ajax({
                        type: 'GET',
                        url: '{{ route('admin.advanced-search') }}',
                        success: function(response) {
                            $('#searchResults').empty().html(response.htmlView);
                        },
                        error: function(xhr, status, error) {
                            console.error(xhr.responseText);
                             toggleSearchLoader('hide');
                        }
                    });
                }
            });

        });

        function toggleSearchLoader(type) {
            const loader = $('#searchLoaderOverlay');
            if (type === 'show') {
                loader.removeClass('d-none');
            } else if (type === 'hide') {
                loader.addClass('d-none');
            }
        }


        $(document).on('keydown', function(e) {
            if (!$('#advanceSearchModal').hasClass('show')) return;

            const items = getItems();

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (currentIndex < items.length - 1) {
                    currentIndex++;
                    updateHighlight(currentIndex);
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (currentIndex > 0) {
                    currentIndex--;
                    updateHighlight(currentIndex);
                }
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (currentIndex >= 0 && currentIndex < items.length) {
                    const target = $(items[currentIndex]).find('.search-list-item')[0];
                    if (target) target.click();
                }
            }
        });

        $('#advanceSearchModal').on('shown.bs.modal', function() {
            currentIndex = -1;
            const input = $('#advance-search-input-global');
            const keyword = input.val().trim();
            input.focus();
            if (keyword.length > 0) {
                input.trigger('focus');
            }
        });

        $('#advanceSearchModal').on('hidden.bs.modal', function() {
            currentIndex = -1;
        });

        $('#advance-search-input-global').on('input focus', function() {
            currentIndex = -1;
        });
    </script>
@endpush
