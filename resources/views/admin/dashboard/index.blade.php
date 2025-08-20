@extends('layouts.admin')

@section('title', 'Tableau de bord')

@section('page-header')
    <h1 class="page-title">
        <i class="fas fa-tachometer-alt me-3"></i>
        Tableau de bord
    </h1>
    <p class="text-muted mb-0">Vue d'ensemble de votre administration</p>
@endsection

@section('content')
<div class="row">
    <!-- Statistiques principales -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Utilisateurs
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $stats['users']['total'] }}
                        </div>
                        <div class="text-muted small mt-1">
                            {{ $stats['users']['active'] }} actifs
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Produits
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $stats['products']['total'] }}
                        </div>
                        <div class="text-muted small mt-1">
                            {{ $stats['products']['active'] }} actifs
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-box fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Catégories
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $stats['categories']['total'] }}
                        </div>
                        <div class="text-muted small mt-1">
                            {{ $stats['categories']['active'] }} actives
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-tags fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning h-100">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Stock faible
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $stats['products']['out_of_stock'] }}
                        </div>
                        <div class="text-muted small mt-1">
                            Produits en rupture
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Utilisateurs récents -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-users me-2"></i>
                    Utilisateurs récents
                </h6>
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-primary">
                    Voir tout
                </a>
            </div>
            <div class="card-body">
                @if($recentUsers->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Statut</th>
                                    <th>Créé le</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentUsers as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="badge {{ $user->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ ucfirst($user->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">Aucun utilisateur récent.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Produits récents -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-success">
                    <i class="fas fa-box me-2"></i>
                    Produits récents
                </h6>
                <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-success">
                    Voir tout
                </a>
            </div>
            <div class="card-body">
                @if($recentProducts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Catégorie</th>
                                    <th>Prix</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentProducts as $product)
                                <tr>
                                    <td>{{ Str::limit($product->name, 30) }}</td>
                                    <td>{{ $product->category->name }}</td>
                                    <td>{{ number_format($product->price, 2) }} €</td>
                                    <td>
                                        <span class="badge {{ $product->stock_quantity > 10 ? 'bg-success' : ($product->stock_quantity > 0 ? 'bg-warning' : 'bg-danger') }}">
                                            {{ $product->stock_quantity }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">Aucun produit récent.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Actions rapides -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-bolt me-2"></i>
                    Actions rapides
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-user-plus mb-2 d-block"></i>
                            Nouvel utilisateur
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.categories.create') }}" class="btn btn-info btn-lg w-100">
                            <i class="fas fa-tag mb-2 d-block"></i>
                            Nouvelle catégorie
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.products.create') }}" class="btn btn-success btn-lg w-100">
                            <i class="fas fa-plus-square mb-2 d-block"></i>
                            Nouveau produit
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('profile.edit') }}" class="btn btn-secondary btn-lg w-100">
                            <i class="fas fa-cog mb-2 d-block"></i>
                            Paramètres
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .border-left-primary {
        border-left: 0.25rem solid var(--primary-color) !important;
    }
    
    .border-left-success {
        border-left: 0.25rem solid var(--success-color) !important;
    }
    
    .border-left-info {
        border-left: 0.25rem solid var(--secondary-color) !important;
    }
    
    .border-left-warning {
        border-left: 0.25rem solid var(--warning-color) !important;
    }
    
    .text-xs {
        font-size: 0.7rem;
    }
</style>
@endpush