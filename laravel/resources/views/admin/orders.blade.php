@extends('components.layout')

@section('title', 'Admin — Commandes')

@section('content')
<section class="py-8 bg-gray-100 min-h-screen">
    <div class="max-w-7xl mx-auto px-4">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Commandes</h1>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 p-4 rounded-lg mb-6">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left p-3">N°</th>
                        <th class="text-left p-3">Client</th>
                        <th class="text-left p-3">Téléphone</th>
                        <th class="text-left p-3">Commune</th>
                        <th class="text-right p-3">Total</th>
                        <th class="text-left p-3">Statut</th>
                        <th class="text-left p-3">Date</th>
                        <th class="text-left p-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3 font-medium">{{ $order->order_number }}</td>
                            <td class="p-3">{{ $order->customer_name }}</td>
                            <td class="p-3">{{ $order->customer_phone }}</td>
                            <td class="p-3">{{ $order->commune_name }}</td>
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
                            <td class="p-3">
                                <form action="/admin/orders/{{ $order->id }}/status" method="POST" class="flex gap-2">
                                    @csrf @method('PATCH')
                                    <select name="status" class="text-xs border rounded px-2 py-1">
                                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>En attente</option>
                                        <option value="confirmed" {{ $order->status == 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                                        <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Préparation</option>
                                        <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Expédiée</option>
                                        <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Livrée</option>
                                        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Annulée</option>
                                    </select>
                                    <button type="submit" class="text-xs bg-emerald-900 text-white px-2 py-1 rounded">OK</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    </div>
</section>
@endsection
