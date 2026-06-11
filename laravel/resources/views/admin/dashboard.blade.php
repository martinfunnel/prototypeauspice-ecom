@extends('components.layout')

@section('title', 'Admin — Tableau de bord')

@section('content')
<section class="py-8 bg-gray-100 min-h-screen">
    <div class="max-w-7xl mx-auto px-4">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Tableau de bord</h1>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 p-4 rounded-lg mb-6">{{ session('success') }}</div>
        @endif

        {{-- Stats cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-10">
            <div class="bg-white p-6 rounded-xl shadow-sm">
                <p class="text-sm text-gray-500 mb-1">Commandes totales</p>
                <p class="text-3xl font-bold text-gray-900">{{ $stats['total_orders'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm">
                <p class="text-sm text-gray-500 mb-1">En attente</p>
                <p class="text-3xl font-bold text-yellow-600">{{ $stats['pending_orders'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm">
                <p class="text-sm text-gray-500 mb-1">Livrées</p>
                <p class="text-3xl font-bold text-green-600">{{ $stats['delivered_orders'] }}</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm">
                <p class="text-sm text-gray-500 mb-1">Chiffre d'affaires</p>
                <p class="text-3xl font-bold text-emerald-700">{{ number_format($stats['total_revenue'], 0, ',', ' ') }} FCFA</p>
            </div>
        </div>

        {{-- Ventes des 7 derniers jours --}}
        <div class="bg-white p-6 rounded-xl shadow-sm mb-10">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Ventes des 7 derniers jours</h2>
            @if($salesByDay->count())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr><th class="text-left p-3">Date</th><th class="text-right p-3">Commandes</th><th class="text-right p-3">CA</th></tr>
                        </thead>
                        <tbody>
                            @foreach($salesByDay as $day)
                                <tr class="border-b"><td class="p-3">{{ $day->date }}</td><td class="p-3 text-right">{{ $day->count }}</td><td class="p-3 text-right font-semibold">{{ number_format($day->revenue, 0, ',', ' ') }} FCFA</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500">Aucune vente sur les 7 derniers jours.</p>
            @endif
        </div>

        {{-- Commandes récentes --}}
        <div class="bg-white p-6 rounded-xl shadow-sm">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Commandes récentes</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-3">N°</th>
                            <th class="text-left p-3">Client</th>
                            <th class="text-left p-3">Téléphone</th>
                            <th class="text-right p-3">Total</th>
                            <th class="text-left p-3">Statut</th>
                            <th class="text-left p-3">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-3 font-medium">{{ $order->order_number }}</td>
                                <td class="p-3">{{ $order->customer_name }}</td>
                                <td class="p-3">{{ $order->customer_phone }}</td>
                                <td class="p-3 text-right">{{ number_format($order->total, 0, ',', ' ') }} FCFA</td>
                                <td class="p-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium
                                        @if($order->status == 'delivered') bg-green-100 text-green-800
                                        @elseif($order->status == 'cancelled') bg-red-100 text-red-800
                                        @else bg-yellow-100 text-yellow-800
                                        @endif">
                                        {{ $order->statusLabel() }}
                                    </span>
                                </td>
                                <td class="p-3 text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
