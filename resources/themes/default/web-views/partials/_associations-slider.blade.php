@php(
    $associations = \App\Models\Association::query()
        ->where('is_active', true)
        ->orderBy('priority')
        ->orderBy('id')
        ->take(50)
        ->get()
        ->unique('id')
        ->values()
)
@if($associations->count())
    <section class="container py-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h3 class="mb-0 assoc-title">{{ translate('Associations') }}</h3>
        </div>

        <div class="assoc-marquee">
            <div class="assoc-track">
                @foreach($associations as $assoc)
                    <div class="assoc-item text-center">
                        <a href="{{ $assoc->url ?? '#' }}" class="d-block" target="_blank">
                            <img class="img-fluid assoc-img" alt="{{ $assoc->name }}"
                                 src="{{ $assoc->image ? getValidImage($assoc->image, 'banner') : theme_asset(path: 'public/assets/front-end/img/placeholder/placeholder-2-1.png') }}">
                            <span class="assoc-name d-block text-truncate">{{ $assoc->name }}</span>
                        </a>
                    </div>
                @endforeach
                @foreach($associations as $assoc)
                    <div class="assoc-item text-center">
                        <a href="{{ $assoc->url ?? '#' }}" class="d-block" target="_blank">
                            <img class="img-fluid assoc-img" alt="{{ $assoc->name }}"
                                 src="{{ $assoc->image ? getValidImage($assoc->image, 'banner') : theme_asset(path: 'public/assets/front-end/img/placeholder/placeholder-2-1.png') }}">
                            <span class="assoc-name d-block text-truncate">{{ $assoc->name }}</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @push('css_or_js')
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700&family=Tajawal:wght@500;700&display=swap');
            .assoc-title{font-family:'Tajawal','Inter',system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,'Helvetica Neue',Arial,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';font-weight:700;letter-spacing:.2px;color:#0f172a}
            .assoc-marquee{overflow:hidden}
            .assoc-track{display:flex;gap:24px;align-items:center;animation:assoc-scroll 30s linear infinite}
            .assoc-item{flex:0 0 auto;width:170px}
            .assoc-img{height:110px;width:auto;object-fit:contain;display:block;margin:0 auto;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,.08);transition:transform .2s ease, box-shadow .2s ease;will-change:transform}
            @media (min-width:576px){.assoc-item{width:190px}.assoc-img{height:120px}}
            @media (min-width:768px){.assoc-item{width:210px}.assoc-img{height:132px}}
            @media (min-width:1200px){.assoc-item{width:230px}.assoc-img{height:144px}}
            .assoc-name{font-family:'Inter',system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,'Helvetica Neue',Arial,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';font-size:1.05rem;font-weight:500;color:#1f2937;margin-top:.5rem}
            .assoc-item a:hover .assoc-img{transform:scale(1.06);box-shadow:0 8px 24px rgba(0,0,0,.12)}
            @keyframes assoc-scroll{from{transform:translateX(0)}to{transform:translateX(-50%)}}
        </style>
    @endpush
@endif


