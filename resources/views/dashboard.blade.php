@extends('layouts.app')

@section('content')

<div class="pagetitle">

    <h1>SSO Dashboard</h1>

    <nav>

        <ol class="breadcrumb">

            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">
                    Home
                </a>
            </li>

            <li class="breadcrumb-item active">
                Dashboard
            </li>

        </ol>

    </nav>

</div>

<section class="section dashboard">

    {{-- Welcome --}}
    <div class="row">

        <div class="col-12">

            <div class="card">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <h4 class="mt-3 fw-bold">

                                Selamat Datang,
                                {{ auth()->user()->username }}

                            </h4>

                            <p class="text-muted mb-0">

                                UKRI SSO merupakan sistem autentikasi terpusat
                                untuk seluruh aplikasi internal Universitas
                                Kebangsaan Republik Indonesia.

                            </p>

                        </div>

                        <div class="d-none d-md-block">

                            <i class="bi bi-shield-lock"
                               style="font-size:70px;color:#4154f1;"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- Statistics --}}
    <div class="row">

        {{-- OAuth Clients --}}
        <div class="col-xxl-3 col-md-6">

            <div class="card info-card sales-card">

                <div class="card-body">

                    <h5 class="card-title">

                        OAuth Clients

                    </h5>

                    <div class="d-flex align-items-center">

                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">

                            <i class="bi bi-shield-lock"></i>

                        </div>

                        <div class="ps-3">

                            <h6>
                                {{ $oauthClientCount ?? 0 }}
                            </h6>

                            <span class="text-muted small">

                                Registered Applications

                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- Active Tokens --}}
        <div class="col-xxl-3 col-md-6">

            <div class="card info-card revenue-card">

                <div class="card-body">

                    <h5 class="card-title">

                        Active Tokens

                    </h5>

                    <div class="d-flex align-items-center">

                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">

                            <i class="bi bi-key"></i>

                        </div>

                        <div class="ps-3">

                            <h6>
                                {{ $activeTokenCount ?? 0 }}
                            </h6>

                            <span class="text-muted small">

                                OAuth Access Tokens

                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- Applications --}}
        <div class="col-xxl-3 col-md-6">

            <div class="card info-card customers-card">

                <div class="card-body">

                    <h5 class="card-title">

                        Applications

                    </h5>

                    <div class="d-flex align-items-center">

                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">

                            <i class="bi bi-window"></i>

                        </div>

                        <div class="ps-3">

                            <h6>
                                {{ $applicationCount ?? 0 }}
                            </h6>

                            <span class="text-muted small">

                                Connected Applications

                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- Sessions --}}
        <div class="col-xxl-3 col-md-6">

            <div class="card info-card customers-card">

                <div class="card-body">

                    <h5 class="card-title">

                        Active Sessions

                    </h5>

                    <div class="d-flex align-items-center">

                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">

                            <i class="bi bi-person-check"></i>

                        </div>

                        <div class="ps-3">

                            <h6>
                                {{ $activeSessionCount ?? 0 }}
                            </h6>

                            <span class="text-muted small">

                                Logged In Users

                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- Main Content --}}
    <div class="row">

        {{-- Login Activity --}}
        <div class="col-lg-8">

            <div class="card">

                <div class="card-body">

                    <h5 class="card-title">

                        Login Activity

                    </h5>

                    <div id="loginChart"
                         style="min-height: 400px;"
                         class="echart">

                    </div>

                </div>

            </div>

        </div>

        {{-- Quick Access --}}
        <div class="col-lg-4">

            {{-- Quick Menu --}}
            <div class="card">

                <div class="card-body">

                    <h5 class="card-title">

                        Quick Access

                    </h5>

                    <div class="d-grid gap-2">

                        <a href="{{ route('clients.index') }}"
                           class="btn btn-primary">

                            <i class="bi bi-shield-lock"></i>

                            OAuth Clients

                        </a>

                        <a href="#"
                           class="btn btn-success">

                            <i class="bi bi-key"></i>

                            Access Tokens

                        </a>

                        <a href="#"
                           class="btn btn-warning text-white">

                            <i class="bi bi-clock-history"></i>

                            Login Activity

                        </a>

                    </div>

                </div>

            </div>

            {{-- Server Status --}}
            <div class="card">

                <div class="card-body">

                    <h5 class="card-title">

                        System Status

                    </h5>

                    <ul class="list-group">

                        <li class="list-group-item d-flex justify-content-between align-items-center">

                            OAuth Server

                            <span class="badge bg-success">
                                Online
                            </span>

                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">

                            Database

                            <span class="badge bg-success">
                                Connected
                            </span>

                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">

                            SIMANTAP Users

                            <span class="badge bg-success">
                                Synced
                            </span>

                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">

                            Passport OAuth2

                            <span class="badge bg-warning text-dark">
                                Pending
                            </span>

                        </li>

                    </ul>

                </div>

            </div>

        </div>

    </div>

    {{-- Recent OAuth Clients --}}
    <div class="row">

        <div class="col-12">

            <div class="card recent-sales overflow-auto">

                <div class="card-body">

                    <h5 class="card-title">

                        Recent OAuth Clients

                    </h5>

                    <table class="table table-borderless datatable">

                        <thead>

                            <tr>

                                <th>#</th>
                                <th>Application</th>
                                <th>Status</th>
                                <th>Created</th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($recentClients ?? [] as $client)

                                <tr>

                                    <td>
                                        {{ $client->id }}
                                    </td>

                                    <td>

                                        {{ $client->name }}

                                    </td>

                                    <td>

                                        @if($client->revoked)

                                            <span class="badge bg-danger">
                                                Revoked
                                            </span>

                                        @else

                                            <span class="badge bg-success">
                                                Active
                                            </span>

                                        @endif

                                    </td>

                                    <td>

                                        {{ \Carbon\Carbon::parse($client->created_at)->diffForHumans() }}

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="4"
                                        class="text-center text-muted">

                                        Belum ada OAuth Client

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</section>

@endsection

@push('scripts')

<script>

document.addEventListener("DOMContentLoaded", () => {

    echarts.init(document.querySelector("#loginChart")).setOption({

        tooltip: {
            trigger: 'item'
        },

        legend: {
            top: '5%',
            left: 'center'
        },

        series: [{
            name: 'Login Activity',
            type: 'pie',
            radius: ['40%', '70%'],

            data: [

                {
                    value: 120,
                    name: 'Success Login'
                },

                {
                    value: 12,
                    name: 'Failed Login'
                },

            ]
        }]
    });

});

</script>

@endpush