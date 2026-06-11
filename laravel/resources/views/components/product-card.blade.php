<a href="/produit/{{ $product->slug }}" class="block bg-white rounded-xl shadow-sm hover:shadow-lg transition overflow-hidden group">
    <div class="aspect-square bg-gray-100 relative overflow-hidden">
        @if(!empty($product->images[0]))
            <img src="{{ $product->images[0] }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
        @else
            <div class="w-full h-full flex items-center justify-center text-gray-400">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        @endif
        @if($product->hasPromo())
            <span class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full font-bold">PROMO</span>
        @endif
    </div>
    <div class="p-4">
        <h3 class="font-semibold text-gray-900 mb-1 truncate">{{ $product->name }}</h3>
        <p class="text-sm text-gray-500 mb-2 line-clamp-2">{{ $product->short_description }}</p>
        <div class="flex items-center justify-between">
            <div>
                @if($product->hasPromo())
                    <span class="text-lg font-bold text-emerald-700">{{ number_format($product->displayPrice(), 0, ',', ' ') }} FCFA</span>
                    <span class="text-sm text-gray-400 line-through ml-2">{{ number_format($product->price, 0, ',', ' ') }} FCFA</span>
                @else
                    <span class="text-lg font-bold text-gray-900">{{ number_format($product->price, 0, ',', ' ') }} FCFA</span>
                @endif
            </div>
        </div>
    </div>
</a>
