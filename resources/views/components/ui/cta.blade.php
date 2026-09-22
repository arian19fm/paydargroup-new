{{--
    Call to action that only becomes a link when it has a destination. CMS
    pages the home page points at may not be published yet; rather than
    emit a dead href the same visual renders as a <span> until they are.
--}}
@props(['href' => null])
@if ($href)
<a {{ $attributes }} href="{{ $href }}">{{ $slot }}</a>
@else
<span {{ $attributes }}>{{ $slot }}</span>
@endif
