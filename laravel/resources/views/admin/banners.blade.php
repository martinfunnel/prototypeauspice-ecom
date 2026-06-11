@extends('components.layout')

@section('title', 'Admin — Bannières')

@section('content')
<section class="py-8 bg-gray-100 min-h-screen">
    <div class="max-w-4xl mx-auto px-4">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Bannières promotionnelles</h1>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 p-4 rounded-lg mb-6">{{ session('success') }}</div>
        @endif

        <div class="space-y-4">
            @foreach($banners as $banner)
                <div class="bg-white p-6 rounded-xl shadow-sm">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm text-gray-500">Clé : {{ $banner->key }}</p>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $banner->title ?? 'Sans titre' }}</h3>
                            <p class="text-gray-600">{{ $banner->subtitle ?? '—' }}</p>
                            <p class="text-sm text-gray-500 mt-2">CTA : {{ $banner->cta_label }} → {{ $banner->cta_url }}</p>
                        </div>
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $banner->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                            {{ $banner->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
