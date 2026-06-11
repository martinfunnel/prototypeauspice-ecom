@extends('components.layout')

@section('title', 'Connexion — Auspice Market')

@section('content')
<section class="py-16 bg-gray-50 min-h-[70vh] flex items-center">
    <div class="max-w-md mx-auto px-4 w-full">
        <div class="bg-white p-8 rounded-xl shadow-sm">
            <h1 class="text-2xl font-bold text-gray-900 mb-6 text-center">Connexion admin</h1>

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg mb-6">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="/login" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                    <input type="password" name="password" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <button type="submit" class="w-full bg-emerald-900 text-white py-2 rounded-lg font-semibold hover:bg-emerald-800 transition">
                    Se connecter
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                Pas encore de compte ? <a href="/register" class="text-emerald-700 hover:underline">Créer un compte</a>
            </p>
        </div>
    </div>
</section>
@endsection
