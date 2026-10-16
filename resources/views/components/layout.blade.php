@props([
    'title' => 'La Petdiatría — Sistema Veterinario',
])

@include('layouts.app', ['title' => $title, 'slot' => $slot])
