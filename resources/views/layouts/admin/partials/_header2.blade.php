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
