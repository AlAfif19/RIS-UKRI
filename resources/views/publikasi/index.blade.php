@extends('layouts.app')

@section('content')

<div class="pagetitle">
    <h1>Publikasi Karya</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Publikasi Karya</li>
        </ol>
    </nav>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-12">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mt-3 mb-4">
                        <h5 class="card-title p-0 m-0">Daftar Publikasi Karya</h5>
                        <a href="{{ route('publikasi.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i> Tambah Publikasi Karya
                        </a>
                    </div>

                    <form method="GET" action="{{ route('publikasi.index') }}" class="row g-2 mb-3">
                        <div class="col-md-4">
                            <input type="text" name="search" id="search-input" class="form-control" placeholder="Cari Judul / Jurnal / Kategori..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-search"></i> Cari
                            </button>
                        </div>
                    </form>

                    <div id="table-publikasi-container">
                        @include('publikasi.partials.table')
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search-input');
        const container = document.getElementById('table-publikasi-container');
        let timeout = null;

        // Perform AJAX search
        function doSearch(page = 1) {
            const query = searchInput.value;
            const url = `{{ route('publikasi.index') }}?search=${encodeURIComponent(query)}&page=${page}`;
            
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.text())
            .then(html => {
                container.innerHTML = html;
                bindPagination();
            })
            .catch(err => console.error('Search error:', err));
        }

        // Debounce input to avoid excessive requests
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                doSearch(1);
            }, 300);
        });

        // Prevent form submit on Enter key
        searchInput.closest('form').addEventListener('submit', function(e) {
            e.preventDefault();
            doSearch(1);
        });

        // Bind pagination link click events dynamically
        function bindPagination() {
            const links = container.querySelectorAll('.pagination a');
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const urlObj = new URL(this.href);
                    const page = urlObj.searchParams.get('page') || 1;
                    doSearch(page);
                });
            });
        }

        bindPagination();
    });
</script>
@endpush
