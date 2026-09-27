@extends('layouts.site')

{{--
    Team page (Figma 229:12 desktop / 246:675 mobile). Groups from the CMS,
    each a heading at the start and a row of member cards at the end;
    group backgrounds alternate between the grey and warm tones exactly as
    in the frames. A member without a photo keeps the toned box.
--}}

@section('content')
    <div class="pg-team">
        <div class="pg-container">
            <header class="pg-team__intro" data-motion="reveal">
                <p class="pg-team__eyebrow">{{ __('team.eyebrow') }}</p>
                <h1 class="pg-team__title" data-motion="reveal-heading">{{ __('team.heading') }}</h1>
            </header>

            @if ($groups->isEmpty())
                <p class="pg-team__empty">{{ __('team.empty') }}</p>
            @else
                @foreach ($groups as $group)
                    <section class="pg-team__group pg-team__group--{{ $loop->odd ? 'grey' : 'warm' }}" aria-labelledby="team-group-{{ $group->id }}">
                        <h2 id="team-group-{{ $group->id }}" class="pg-team__group-title">{{ $group->name }}</h2>
                        <ul class="pg-team__members" data-motion="reveal-group">
                            @foreach ($group->members as $member)
                                <li class="pg-member">
                                    <div class="pg-member__photo">
                                        @if ($member->photo)
                                            <img src="{{ $member->photo->url() }}" width="{{ $member->photo->width ?: 600 }}" height="{{ $member->photo->height ?: 640 }}" alt="{{ $member->photo->alt_text ?: $member->name }}" loading="lazy" decoding="async">
                                        @endif
                                    </div>
                                    <div class="pg-member__meta">
                                        <div class="pg-member__text">
                                            <h3 class="pg-member__name">{{ $member->name }}</h3>
                                            @if ($member->role)
                                                <p class="pg-member__role">{{ $member->role }}</p>
                                            @endif
                                        </div>
                                        @if ($member->linkedin_url)
                                            <a class="pg-member__linkedin" href="{{ $member->linkedin_url }}" target="_blank" rel="noopener" aria-label="{{ __('team.linkedin', ['name' => $member->name]) }}">
                                                <img src="{{ asset('images/icons/linkedin-card.svg') }}" width="20" height="20" alt="" aria-hidden="true">
                                            </a>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endforeach
            @endif
        </div>
    </div>
@endsection
