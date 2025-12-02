@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h4 class="fw-semibold">Data Candidate</h4>
            <p class="text-muted mb-0">Lihat dan unduh data personal seluruh kandidat tanpa duplikasi berdasarkan lowongan.</p>
        </div>
    </div>

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">Filter</div>
        <div class="card-body">
            <form method="GET" action="{{ route('data-candidate.index') }}" class="row g-3 align-items-end">
                <div class="col-md-6 col-lg-4">
                    <label for="keyword" class="form-label">Kata Kunci</label>
                    <input type="text" name="keyword" id="keyword" value="{{ $filters['keyword'] ?? '' }}" class="form-control" placeholder="Nama, NIK, atau Email">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                    <a href="{{ route('data-candidate.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Preview Data Kandidat</span>
            <form method="GET" action="{{ route('data-candidate.download') }}" class="d-flex gap-2">
                @foreach($filters as $key => $value)
                    @if($value !== null && $value !== '')
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                <button type="submit" class="btn btn-success" {{ $results->total() === 0 ? 'disabled' : '' }}>Download CSV</button>
            </form>
        </div>
        <div class="card-body">
            @if($results->count())
                <div class="accordion" id="candidateAccordion">
                    @foreach($results as $personal)
                        @php
                            $headingId = 'candidate-heading-'.$personal->id;
                            $collapseId = 'candidate-collapse-'.$personal->id;
                            $title = $personal->title ? $personal->title.'.' : null;
                            $fullNameParts = array_filter([
                                $title,
                                trim(trim(($personal->first_name ?? '').' '.($personal->last_name ?? ''))),
                            ], function ($value) {
                                return $value !== null && $value !== '';
                            });
                            $fullName = trim(implode(' ', $fullNameParts));
                            if ($fullName === '') {
                                $fullName = 'Tanpa Nama';
                            }
                            $detailUrl = route('data-candidate.detail', $personal->id);
                        @endphp
                        <div class="accordion-item mb-3 candidate-item" data-personal-id="{{ $personal->id }}" data-detail-url="{{ $detailUrl }}" data-detail-loaded="false">
                            <!-- <h2 class="accordion-header" id="{{ $headingId }}"> -->
                                <!-- <button class="accordion-button {{ !$loop->first ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="{{ $collapseId }}">
                                    <div class="d-flex flex-column flex-lg-row w-100 gap-2">
                                        <span class="fw-semibold">{{ $fullName }}</span>
                                        <span class="text-muted">| NIK: {{ $personal->nik ?? '—' }}</span>
                                        <span class="text-muted">| Email: {{ optional($personal->user)->email ?? '—' }}</span>
                                    </div>
                                </button> -->
                                <!-- <button class="accordion-button {{ !$loop->first ? 'collapsed' : '' }} d-flex justify-content-between align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="{{ $collapseId }}">
                                    <div class="d-flex flex-column flex-lg-row gap-2">
                                        <span class="fw-semibold">{{ $fullName }}</span>
                                        <span class="text-muted">| NIK: {{ $personal->nik ?? '—' }}</span>
                                        <span class="text-muted">| Email: {{ optional($personal->user)->email ?? '—' }}</span>
                                    </div>
                                </button> -->
                            <!-- </h2> -->
                             <h2 class="accordion-header d-flex justify-content-between align-items-center" id="{{ $headingId }}">
                                <!-- {{-- Tombol utama untuk toggle accordion --}} -->
                                <button class="accordion-button collapsed" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}"
                                    aria-expanded="false"
                                    aria-controls="{{ $collapseId }}">
                                    <div class="d-flex flex-column flex-lg-row gap-2">
                                        <span class="fw-semibold">{{ $fullName }}</span>
                                        <span class="text-muted">| NIK: {{ $personal->nik ?? '—' }}</span>
                                        <span class="text-muted">| Email: {{ optional($personal->user)->email ?? '—' }}</span>
                                    </div>
                                </button>

                                <!-- {{-- Tombol terpisah di kanan header --}} -->
                                <button type="button" class="btn btn-sm btn-outline-primary me-3 btn-alert"
                                    data-nik="{{ $personal->nik }}">
                                    🔔
                                </button>
                            </h2>
                            <div id="{{ $collapseId }}" class="accordion-collapse collapse" aria-labelledby="{{ $headingId }}" data-bs-parent="#candidateAccordion">
                                <div class="accordion-body">
                                    <div class="candidate-detail-content">
                                        <div class="text-center text-muted py-3">
                                            Detail kandidat akan dimuat saat dibuka.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center text-muted py-4">Tidak ada data kandidat untuk filter ini.</div>
            @endif
        </div>
        @if($results->hasPages())
            <div class="card-footer">
                {{ $results->links() }}
            </div>
        @endif
    </div>
</div>

<div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="photoModalLabel">Profile Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img src="" alt="Profile Photo" id="photoModalImage" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>
@endsection

@section('fungsi')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const photoModal = document.getElementById('photoModal');
            if (photoModal) {
                photoModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const photoUrl = button ? button.getAttribute('data-photo') : null;
                    const modalImage = photoModal.querySelector('#photoModalImage');
                    if (modalImage) {
                        modalImage.src = photoUrl || '';
                    }
                });

                photoModal.addEventListener('hidden.bs.modal', function () {
                    const modalImage = photoModal.querySelector('#photoModalImage');
                    if (modalImage) {
                        modalImage.src = '';
                    }
                });
            }

            const accordion = document.getElementById('candidateAccordion');
            if (!accordion) {
                return;
            }

            const loadDetail = function (item) {
                if (!item || item.getAttribute('data-detail-loaded') === 'true' || item.getAttribute('data-detail-loaded') === 'loading') {
                    return;
                }

                const url = item.getAttribute('data-detail-url');
                const container = item.querySelector('.candidate-detail-content');
                if (!url || !container) {
                    return;
                }

                item.setAttribute('data-detail-loaded', 'loading');
                container.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm me-2" role="status"></div>Memuat detail kandidat...</div>';

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Request failed');
                        }
                        return response.json();
                    })
                    .then(function (data) {
                        if (!data || typeof data.html !== 'string') {
                            throw new Error('Invalid response');
                        }
                        container.innerHTML = data.html;
                        item.setAttribute('data-detail-loaded', 'true');
                    })
                    .catch(function () {
                        container.innerHTML = '<div class="alert alert-danger">Gagal memuat detail kandidat. <button type="button" class="btn btn-link p-0 align-baseline candidate-retry">Coba lagi</button></div>';
                        item.setAttribute('data-detail-loaded', 'error');
                    });
            };

            accordion.addEventListener('show.bs.collapse', function (event) {
                const collapse = event.target.closest('.accordion-collapse');
                if (!collapse) {
                    return;
                }
                const item = collapse.closest('.candidate-item');
                loadDetail(item);
            });

            accordion.addEventListener('click', function (event) {
                const retryButton = event.target.closest('.candidate-retry');
                if (!retryButton) {
                    return;
                }
                event.preventDefault();
                const item = retryButton.closest('.candidate-item');
                if (!item) {
                    return;
                }
                item.setAttribute('data-detail-loaded', 'false');
                loadDetail(item);
            });

            const openCollapses = accordion.querySelectorAll('.accordion-collapse.show');
            openCollapses.forEach(function (collapse) {
                const item = collapse.closest('.candidate-item');
                loadDetail(item);
            });
        });

        $(document).ready(function() {
            $('.btn-alert').on('click', function(e) {
                e.stopPropagation(); // agar tidak ikut toggle accordion
                const nik = $(this).data('nik') || 'NIK tidak tersedia';
                alert('NIK kandidat: ' + nik);
            });
        });
    </script>
@endsection