@extends('layouts.admin')

@section('title', 'Laporan Penjualan')

@section('content')
<div class="container py-4">
    <h1 class="h3 mb-4 fw-bold">Laporan Penjualan</h1>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.reports.sales') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    
                    {{-- Tombol Export --}}
                    <a href="{{ route('admin.reports.export-sales', request()->all()) }}" class="btn btn-success">
                        <i class="bi bi-file-earmark-excel"></i> Export Excel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection