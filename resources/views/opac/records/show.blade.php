@extends('opac.layouts.app')

@section('title', $record->title . ' — ' . config('app.name'))

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&display=swap" rel="stylesheet">
<style>
/* ── OPAC Record Show ── */
:root {
    --rs-ink:       #1a1f2e;
    --rs-ink-70:    #4a5068;
    --rs-ink-40:    #8b90a7;
    --rs-ink-10:    #eef0f6;
    --rs-ink-05:    #f6f7fb;
    --rs-gold:      #9a6c2e;
    --rs-gold-lt:   #fdf4e7;
    --rs-gold-mid:  #c99040;
    --rs-blue:      #004a99;
    --rs-blue-lt:   #e8f0fa;
    --rs-green:     #0a6640;
    --rs-green-lt:  #e6f4ee;
    --rs-red:       #8b1a1a;
    --rs-red-lt:    #fdeaea;
    --rs-radius:    10px;
    --rs-radius-lg: 16px;
    --rs-shadow-sm: 0 1px 4px rgba(26,31,46,.06);
    --rs-shadow:    0 4px 16px rgba(26,31,46,.09);
    --rs-shadow-lg: 0 12px 40px rgba(26,31,46,.13);
}

.rs-page {
    font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, sans-serif;
    background: var(--rs-ink-05);
    padding-bottom: 5rem;
}

/* ── Breadcrumb ── */
.rs-breadcrumb-bar {
    background: #fff;
    border-bottom: 1px solid var(--rs-ink-10);
    padding: 0.65rem 0;
}

.rs-breadcrumb {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.35rem;
    list-style: none;
    margin: 0;
    padding: 0;
    font-size: 0.82rem;
    color: var(--rs-ink-40);
}

.rs-breadcrumb li a {
    color: var(--rs-ink-70);
    text-decoration: none;
    transition: color .15s ease;
}

.rs-breadcrumb li a:hover { color: var(--rs-blue); }

.rs-breadcrumb li + li::before {
    content: '/';
    margin-right: 0.35rem;
    color: var(--rs-ink-40);
}

.rs-breadcrumb li:last-child {
    color: var(--rs-ink-40);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 280px;
}

/* ── Hero Banner ── */
.rs-hero {
    background: linear-gradient(135deg, var(--rs-ink) 0%, #253047 60%, #344060 100%);
    padding: 3rem 0 0;
    position: relative;
    overflow: hidden;
}

.rs-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse 50% 100% at 100% 50%, rgba(154,108,46,.15) 0%, transparent 60%),
        url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23fff' fill-opacity='0.02' fill-rule='evenodd'%3E%3Cpath d='M0 0h40v40H0V0zm40 40h40v40H40V40z'/%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.rs-hero-inner {
    position: relative;
    z-index: 1;
}

.rs-hero-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--rs-gold-mid);
    margin-bottom: 1rem;
}

.rs-hero-eyebrow::before {
    content: '';
    display: inline-block;
    width: 24px;
    height: 2px;
    background: var(--rs-gold-mid);
    border-radius: 1px;
}

.rs-hero-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: clamp(1.8rem, 4vw, 2.75rem);
    font-weight: 700;
    color: #fff;
    line-height: 1.18;
    margin: 0 0 0.75rem;
    letter-spacing: -0.01em;
}

.rs-hero-ref {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
    color: rgba(255,255,255,.55);
    margin-bottom: 1rem;
    font-family: 'DM Sans', monospace;
    letter-spacing: 0.04em;
}

.rs-hero-ref-dot {
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background: var(--rs-gold-mid);
}

.rs-hero-authors {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-style: italic;
    font-size: 1.1rem;
    color: rgba(255,255,255,.75);
    margin-bottom: 1.5rem;
}

.rs-hero-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 2rem;
}

.rs-hero-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.03em;
}

.rs-tag-type   { background: rgba(255,255,255,.12); color: rgba(255,255,255,.9); border: 1px solid rgba(255,255,255,.2); }
.rs-tag-level  { background: rgba(154,108,46,.25); color: #f0c97a; border: 1px solid rgba(154,108,46,.3); }
.rs-tag-avail  { background: rgba(10,102,64,.3); color: #6eedb5; border: 1px solid rgba(10,102,64,.4); }
.rs-tag-unavail{ background: rgba(100,100,100,.2); color: rgba(255,255,255,.6); border: 1px solid rgba(255,255,255,.15); }

/* Hero bottom curve */
.rs-hero-curve {
    height: 36px;
    background: var(--rs-ink-05);
    margin-top: -1px;
    clip-path: ellipse(55% 100% at 50% 100%);
}

/* ── Layout ── */
.rs-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 1.5rem;
    margin-top: -1.5rem;
    position: relative;
    z-index: 2;
}

@media (max-width: 900px) {
    .rs-layout { grid-template-columns: 1fr; margin-top: 1rem; }
}

/* ── Sidebar ── */
.rs-sidebar {}

.rs-status-card {
    background: #fff;
    border: 1px solid var(--rs-ink-10);
    border-radius: var(--rs-radius-lg);
    overflow: hidden;
    box-shadow: var(--rs-shadow);
    margin-bottom: 1rem;
}

.rs-status-header {
    padding: 1.5rem 1.5rem 1rem;
    text-align: center;
    border-bottom: 1px solid var(--rs-ink-10);
}

.rs-cover-wrap {
    width: 120px;
    height: 150px;
    margin: 0 auto 1rem;
    border-radius: 8px;
    overflow: hidden;
    background: linear-gradient(135deg, var(--rs-ink-10) 0%, var(--rs-ink-05) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(26,31,46,.12), inset 0 0 0 1px rgba(26,31,46,.06);
}

.rs-cover-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.rs-cover-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    color: var(--rs-ink-40);
}

.rs-cover-placeholder i { font-size: 2.5rem; opacity: .4; }
.rs-cover-placeholder span { font-size: 0.7rem; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; opacity: .5; }

.rs-avail-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.45rem 1.1rem;
    border-radius: 30px;
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.03em;
}

.rs-avail-pill.available   { background: var(--rs-green-lt); color: var(--rs-green); }
.rs-avail-pill.unavailable { background: var(--rs-ink-10); color: var(--rs-ink-40); }

.rs-avail-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    animation: rsPulse 2s ease infinite;
}

.available   .rs-avail-dot { background: var(--rs-green); }
.unavailable .rs-avail-dot { background: var(--rs-ink-40); animation: none; }

@keyframes rsPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: .6; transform: scale(1.3); }
}

.rs-status-body {
    padding: 1rem 1.25rem;
}

.rs-meta-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    padding: 0.55rem 0;
    border-bottom: 1px dashed var(--rs-ink-10);
    font-size: 0.82rem;
    gap: 0.75rem;
}

.rs-meta-row:last-child { border-bottom: none; }

.rs-meta-label {
    color: var(--rs-ink-40);
    font-weight: 500;
    flex-shrink: 0;
}

.rs-meta-value {
    color: var(--rs-ink);
    font-weight: 600;
    text-align: right;
    word-break: break-word;
}

/* Sidebar save card */
.rs-save-card {
    background: #fff;
    border: 1px solid var(--rs-ink-10);
    border-radius: var(--rs-radius-lg);
    padding: 1rem 1.25rem;
    box-shadow: var(--rs-shadow-sm);
}

.rs-save-btn {
    width: 100%;
    background: transparent;
    border: 2px solid var(--rs-blue);
    color: var(--rs-blue);
    border-radius: var(--rs-radius);
    padding: 0.6rem 1rem;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all .2s ease;
}

.rs-save-btn:hover {
    background: var(--rs-blue);
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,74,153,.25);
}

/* ── Main content ── */
.rs-main {}

/* Section card */
.rs-card {
    background: #fff;
    border: 1px solid var(--rs-ink-10);
    border-radius: var(--rs-radius-lg);
    overflow: hidden;
    box-shadow: var(--rs-shadow-sm);
    margin-bottom: 1.25rem;
    transition: box-shadow .2s ease;
}

.rs-card:hover { box-shadow: var(--rs-shadow); }

.rs-card-head {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1.1rem 1.5rem;
    border-bottom: 1px solid var(--rs-ink-10);
}

.rs-card-head-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: var(--rs-blue-lt);
    color: var(--rs-blue);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    flex-shrink: 0;
}

.rs-card-head h2 {
    font-family: 'DM Sans', sans-serif;
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--rs-ink-70);
    margin: 0;
}

.rs-card-body {
    padding: 1.5rem;
}

/* Description */
.rs-description {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.15rem;
    color: var(--rs-ink-70);
    line-height: 1.75;
    margin: 0;
}

/* Biographical history */
.rs-biog {
    font-size: 0.9rem;
    color: var(--rs-ink-70);
    line-height: 1.75;
    margin: 0;
    border-left: 3px solid var(--rs-gold-mid);
    padding-left: 1.1rem;
}

/* Details grid */
.rs-details-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
}

@media (max-width: 640px) {
    .rs-details-grid { grid-template-columns: 1fr; }
}

.rs-detail-item {
    padding: 0.85rem 1rem;
    border-bottom: 1px solid var(--rs-ink-10);
    border-right: 1px solid var(--rs-ink-10);
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.rs-detail-item:nth-child(even) { border-right: none; }
.rs-detail-item:nth-last-child(-n+2) { border-bottom: none; }

.rs-detail-label {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.09em;
    text-transform: uppercase;
    color: var(--rs-ink-40);
}

.rs-detail-value {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--rs-ink);
}

/* Access conditions */
.rs-access-box {
    background: var(--rs-gold-lt);
    border: 1px solid rgba(154,108,46,.2);
    border-left: 3px solid var(--rs-gold);
    border-radius: var(--rs-radius);
    padding: 1rem 1.25rem;
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
    font-size: 0.875rem;
    color: var(--rs-gold);
}

.rs-access-box i { font-size: 1rem; margin-top: 1px; flex-shrink: 0; }
.rs-access-box p { margin: 0; color: #6b4c1a; line-height: 1.6; }

/* Subject tags */
.rs-tags-wrap {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.rs-subject-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.35rem 0.85rem;
    background: var(--rs-ink-05);
    border: 1px solid var(--rs-ink-10);
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--rs-ink-70);
    text-decoration: none;
    transition: all .15s ease;
}

.rs-subject-tag:hover {
    background: var(--rs-blue-lt);
    border-color: rgba(0,74,153,.2);
    color: var(--rs-blue);
    transform: translateY(-1px);
}

/* Attachments */
.rs-attachment-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--rs-ink-10);
    font-size: 0.875rem;
}

.rs-attachment-item:last-child { border-bottom: none; }

.rs-attachment-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: var(--rs-ink-05);
    border: 1px solid var(--rs-ink-10);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--rs-ink-40);
    font-size: 0.9rem;
    flex-shrink: 0;
}

.rs-attachment-name {
    font-weight: 500;
    color: var(--rs-ink);
}

/* Notes */
.rs-notes {
    font-size: 0.875rem;
    color: var(--rs-ink-70);
    line-height: 1.7;
    margin: 0;
}

/* ── Related records ── */
.rs-related-section {
    margin-top: 2rem;
}

.rs-related-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--rs-ink);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.rs-related-title::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--rs-ink-10);
}

.rs-related-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
    gap: 1rem;
}

.rs-related-card {
    background: #fff;
    border: 1px solid var(--rs-ink-10);
    border-radius: var(--rs-radius);
    overflow: hidden;
    box-shadow: var(--rs-shadow-sm);
    transition: all .2s ease;
    text-decoration: none;
    display: flex;
    flex-direction: column;
}

.rs-related-card:hover {
    box-shadow: var(--rs-shadow);
    transform: translateY(-3px);
    border-color: rgba(0,74,153,.15);
}

.rs-related-card-accent {
    height: 4px;
    background: linear-gradient(90deg, var(--rs-blue) 0%, var(--rs-gold-mid) 100%);
}

.rs-related-card-body {
    padding: 1rem;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.rs-related-card-title {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--rs-ink);
    line-height: 1.4;
    transition: color .15s ease;
}

.rs-related-card:hover .rs-related-card-title { color: var(--rs-blue); }

.rs-related-card-authors {
    font-size: 0.78rem;
    color: var(--rs-ink-40);
}

.rs-related-card-cta {
    margin-top: auto;
    padding-top: 0.75rem;
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--rs-blue);
    display: flex;
    align-items: center;
    gap: 0.35rem;
    opacity: 0;
    transform: translateX(-4px);
    transition: all .2s ease;
}

.rs-related-card:hover .rs-related-card-cta {
    opacity: 1;
    transform: translateX(0);
}

/* Appear animations */
@keyframes rsSlideUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}

.rs-card { animation: rsSlideUp .35s ease both; }
.rs-card:nth-child(1) { animation-delay: .05s; }
.rs-card:nth-child(2) { animation-delay: .10s; }
.rs-card:nth-child(3) { animation-delay: .15s; }
.rs-card:nth-child(4) { animation-delay: .20s; }
.rs-card:nth-child(n+5) { animation-delay: .25s; }

.rs-sidebar { animation: rsSlideUp .4s .02s ease both; }
</style>
@endpush

@section('content')
<div class="rs-page">

    {{-- Breadcrumb --}}
    <div class="rs-breadcrumb-bar">
        <div class="container">
            <ol class="rs-breadcrumb">
                <li><a href="{{ route('opac.index') }}"><i class="fas fa-home me-1"></i>{{ __('Home') }}</a></li>
                <li><a href="{{ route('opac.records.index') }}">{{ __('Records') }}</a></li>
                <li>{{ Str::limit($record->title, 60) }}</li>
            </ol>
        </div>
    </div>

    {{-- Hero --}}
    <div class="rs-hero">
        <div class="rs-hero-inner container py-4">
            <div class="rs-hero-eyebrow">
                <i class="fas fa-archive"></i> {{ __('Physical Record') }}
            </div>

            <h1 class="rs-hero-title">{{ $record->title }}</h1>

            @if($record->code)
                <div class="rs-hero-ref">
                    <span class="rs-hero-ref-dot"></span>
                    {{ __('Ref.') }} {{ $record->code }}
                </div>
            @endif

            @if($record->authors)
                <div class="rs-hero-authors">{{ $record->authors }}</div>
            @endif

            <div class="rs-hero-tags">
                <span class="rs-hero-tag rs-tag-type">
                    <i class="fas fa-archive"></i> {{ __('Physical Record') }}
                </span>

                @if($record->record->level ?? null)
                    <span class="rs-hero-tag rs-tag-level">
                        <i class="fas fa-layer-group"></i> {{ $record->record->level->name }}
                    </span>
                @endif

                @if($record->is_available)
                    <span class="rs-hero-tag rs-tag-avail">
                        <i class="fas fa-circle" style="font-size:.55em;"></i> {{ __('Available') }}
                    </span>
                @else
                    <span class="rs-hero-tag rs-tag-unavail">
                        <i class="fas fa-circle" style="font-size:.55em;"></i> {{ __('Unavailable') }}
                    </span>
                @endif
            </div>
        </div>
        <div class="rs-hero-curve"></div>
    </div>

    {{-- Body --}}
    <div class="container mt-0 pt-2">
        <div class="rs-layout">

            {{-- ── Sidebar ── --}}
            <aside class="rs-sidebar">
                {{-- Status card --}}
                <div class="rs-status-card">
                    <div class="rs-status-header">
                        <div class="rs-cover-wrap">
                            @if($record->cover_image)
                                <img src="{{ asset('storage/' . $record->cover_image) }}" alt="{{ $record->title }}">
                            @else
                                <div class="rs-cover-placeholder">
                                    <i class="fas fa-archive"></i>
                                    <span>{{ __('No cover') }}</span>
                                </div>
                            @endif
                        </div>

                        @if($record->is_available)
                            <span class="rs-avail-pill available">
                                <span class="rs-avail-dot"></span>
                                {{ __('Available') }}
                            </span>
                        @else
                            <span class="rs-avail-pill unavailable">
                                <span class="rs-avail-dot"></span>
                                {{ __('Unavailable') }}
                            </span>
                        @endif
                    </div>

                    <div class="rs-status-body">
                        <div class="rs-meta-row">
                            <span class="rs-meta-label">{{ __('Dates') }}</span>
                            <span class="rs-meta-value">{{ $record->formatted_date_range }}</span>
                        </div>

                        @if($record->language_material)
                            <div class="rs-meta-row">
                                <span class="rs-meta-label">{{ __('Language') }}</span>
                                <span class="rs-meta-value">{{ $record->language_material }}</span>
                            </div>
                        @endif

                        @if($record->publisher_name && $record->publisher_name !== 'Inconnu')
                            <div class="rs-meta-row">
                                <span class="rs-meta-label">{{ __('Publisher') }}</span>
                                <span class="rs-meta-value">{{ $record->publisher_name }}</span>
                            </div>
                        @endif

                        <div class="rs-meta-row">
                            <span class="rs-meta-label">{{ __('Published') }}</span>
                            <span class="rs-meta-value">{{ $record->published_at?->format('d/m/Y') }}</span>
                        </div>

                        @if($record->expires_at)
                            <div class="rs-meta-row">
                                <span class="rs-meta-label">{{ __('Expires') }}</span>
                                <span class="rs-meta-value">{{ $record->expires_at->format('d/m/Y') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Save card --}}
                @auth('public')
                    <div class="rs-save-card">
                        <button class="rs-save-btn" type="button">
                            <i class="fas fa-bookmark"></i> {{ __('Save to my list') }}
                        </button>
                    </div>
                @endauth
            </aside>

            {{-- ── Main ── --}}
            <main class="rs-main">

                {{-- Description --}}
                @if($record->content)
                    <div class="rs-card">
                        <div class="rs-card-head">
                            <div class="rs-card-head-icon"><i class="fas fa-align-left"></i></div>
                            <h2>{{ __('Description') }}</h2>
                        </div>
                        <div class="rs-card-body">
                            <p class="rs-description">{{ $record->content }}</p>
                        </div>
                    </div>
                @endif

                {{-- Biographical / Administrative History --}}
                @if($record->biographical_history)
                    <div class="rs-card">
                        <div class="rs-card-head">
                            <div class="rs-card-head-icon"><i class="fas fa-scroll"></i></div>
                            <h2>{{ __('Biographical / Administrative History') }}</h2>
                        </div>
                        <div class="rs-card-body">
                            <p class="rs-biog">{{ $record->biographical_history }}</p>
                        </div>
                    </div>
                @endif

                {{-- Details --}}
                <div class="rs-card">
                    <div class="rs-card-head">
                        <div class="rs-card-head-icon"><i class="fas fa-list-ul"></i></div>
                        <h2>{{ __('Details') }}</h2>
                    </div>
                    <div class="rs-details-grid">
                        <div class="rs-detail-item">
                            <span class="rs-detail-label">{{ __('Dates') }}</span>
                            <span class="rs-detail-value">{{ $record->formatted_date_range }}</span>
                        </div>

                        @if($record->language_material)
                            <div class="rs-detail-item">
                                <span class="rs-detail-label">{{ __('Language') }}</span>
                                <span class="rs-detail-value">{{ $record->language_material }}</span>
                            </div>
                        @endif

                        @if($record->code)
                            <div class="rs-detail-item">
                                <span class="rs-detail-label">{{ __('Reference code') }}</span>
                                <span class="rs-detail-value" style="font-family: monospace; letter-spacing:.04em;">{{ $record->code }}</span>
                            </div>
                        @endif

                        @if($record->publisher_name && $record->publisher_name !== 'Inconnu')
                            <div class="rs-detail-item">
                                <span class="rs-detail-label">{{ __('Published by') }}</span>
                                <span class="rs-detail-value">{{ $record->publisher_name }}</span>
                            </div>
                        @endif

                        <div class="rs-detail-item">
                            <span class="rs-detail-label">{{ __('Published on') }}</span>
                            <span class="rs-detail-value">{{ $record->published_at?->format('d/m/Y') }}</span>
                        </div>

                        @if($record->record->level ?? null)
                            <div class="rs-detail-item">
                                <span class="rs-detail-label">{{ __('Level') }}</span>
                                <span class="rs-detail-value">{{ $record->record->level->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Access Conditions --}}
                @if($record->access_conditions)
                    <div class="rs-card">
                        <div class="rs-card-head">
                            <div class="rs-card-head-icon" style="background:#fff7ed;color:#9a6c2e;"><i class="fas fa-lock"></i></div>
                            <h2>{{ __('Access Conditions') }}</h2>
                        </div>
                        <div class="rs-card-body">
                            <div class="rs-access-box">
                                <i class="fas fa-info-circle"></i>
                                <p>{{ $record->access_conditions }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Subjects --}}
                @if($record->record->thesaurusConcepts->count() > 0)
                    <div class="rs-card">
                        <div class="rs-card-head">
                            <div class="rs-card-head-icon"><i class="fas fa-tags"></i></div>
                            <h2>{{ __('Subjects') }}</h2>
                        </div>
                        <div class="rs-card-body">
                            <div class="rs-tags-wrap">
                                @foreach($record->record->thesaurusConcepts as $concept)
                                    <span class="rs-subject-tag">
                                        <i class="fas fa-tag" style="font-size:.65em;opacity:.6;"></i>
                                        {{ $concept->term }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Attachments --}}
                @if($record->record->attachments->count() > 0)
                    <div class="rs-card">
                        <div class="rs-card-head">
                            <div class="rs-card-head-icon"><i class="fas fa-paperclip"></i></div>
                            <h2>{{ __('Attachments') }}</h2>
                        </div>
                        <div class="rs-card-body" style="padding-top: 0.5rem; padding-bottom: 0.5rem;">
                            @foreach($record->record->attachments as $attachment)
                                <div class="rs-attachment-item">
                                    <div class="rs-attachment-icon">
                                        <i class="fas fa-file"></i>
                                    </div>
                                    <span class="rs-attachment-name">
                                        {{ $attachment->original_name ?? $attachment->name ?? basename($attachment->path) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Notes --}}
                @if($record->publication_notes)
                    <div class="rs-card">
                        <div class="rs-card-head">
                            <div class="rs-card-head-icon"><i class="fas fa-sticky-note"></i></div>
                            <h2>{{ __('Notes') }}</h2>
                        </div>
                        <div class="rs-card-body">
                            <p class="rs-notes">{{ $record->publication_notes }}</p>
                        </div>
                    </div>
                @endif

            </main>
        </div>

        {{-- Related records --}}
        @if($relatedRecords->count() > 0)
            <div class="rs-related-section">
                <h3 class="rs-related-title">{{ __('Related Records') }}</h3>
                <div class="rs-related-grid">
                    @foreach($relatedRecords as $related)
                        <a href="{{ route('opac.records.show', $related->id) }}" class="rs-related-card">
                            <div class="rs-related-card-accent"></div>
                            <div class="rs-related-card-body">
                                <div class="rs-related-card-title">{{ $related->title }}</div>
                                @if($related->authors)
                                    <div class="rs-related-card-authors">{{ $related->authors }}</div>
                                @endif
                                <div class="rs-related-card-cta">
                                    {{ __('View record') }} <i class="fas fa-arrow-right" style="font-size:.7em;"></i>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
