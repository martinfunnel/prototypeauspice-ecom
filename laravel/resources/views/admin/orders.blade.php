@extends('components.admin-shell')

@section('title', 'Commandes')

@section('content')


        @if(session('success'))
            <div class="bg-success/10 border border-success/20 text-success p-4 rounded-lg mb-6">{{ session('success') }}</div>
        @endif

        <div class="bg-card rounded-xl shadow-card overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-muted">
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
                        <tr class="border-b hover:bg-muted">
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
                            <td class="p-3 text-muted-foreground">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="p-3">
                                @canDo('update_orders')
                                <form action="/admin/orders/{{ $order->id }}/status" method="POST" class="flex gap-2">
                                    @csrf @method('PATCH')
                                    <select name="status" class="text-xs border border-border rounded px-2 py-1 bg-background">
                                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>En attente</option>
                                        <option value="confirmed" {{ $order->status == 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                                        <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Préparation</option>
                                        <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Expédiée</option>
                                        <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Livrée</option>
                                        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Annulée</option>
                                    </select>
                                    <button type="submit" class="text-xs bg-accent text-accent-foreground px-2 py-1 rounded">OK</button>
                                </form>
                                @endcanDo
                                @canDo('delete_orders')
                                <form action="/admin/orders/{{ $order->id }}" method="POST" class="inline mt-1" onsubmit="return confirm('Supprimer cette commande ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-destructive hover:opacity-70 text-xs">Supprimer</button>
                                </form>
                                @endcanDo
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $orders->links() }}
        </div>
@endsection
