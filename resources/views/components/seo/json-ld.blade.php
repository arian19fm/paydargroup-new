{{-- Safely encoded JSON-LD block. Never build this JSON by hand. --}}
@props(['data'])
<script type="application/ld+json">{!! \App\Support\Seo\JsonLd::encode($data) !!}</script>
