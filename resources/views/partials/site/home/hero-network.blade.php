{{--
    Hero network — the "fintech ecosystem" visual: a line-art globe at the
    centre, the published businesses as chips on a ring around it and a
    curved, glowing link from the globe's rim to each chip with a light
    travelling along it. Desktop shows it beside the copy (in place of the
    hero photo), mobile/tablet above the headline.

    One inline SVG on a 330×290 grid holds the globe (a nested <svg>), the
    ring and the links; the chips are real links in an HTML list laid over
    the same grid in percentages (.pg-hero__nodes), so they stay crisp,
    focusable and readable by assistive tech while the drawing is
    aria-hidden. The box keeps the grid's aspect ratio, so SVG and chips
    always agree at any width.

    Radial geometry: centre (165,145), sphere radius 55.9, chip ring
    radius 84 (28-unit spokes). A slot is the point where the chip meets the ring — its
    inner edge (left/right chips grow outwards, top/bottom chips grow away
    from the globe) — so a chip's width never moves the join and no chip
    can reach the sphere. Six slots at 45° above/below the horizontal plus
    top and bottom (45° leaves the side chips the most room on a phone); the sets below pick a balanced subset for 1–6 chips
    (the list is capped at six). Each link runs from the rim to the slot
    and bends 5 units off its chord; sparks ride the same paths.
--}}
@php
    $slots = [
        'top' => ['side' => 'top', 'cx' => 50.00, 'cy' => 21.03, 'port' => [165.0, 89.1], 'path' => 'M165.0 89.1 Q170.0 75.0 165.0 61.0'],
        'ul' => ['side' => 'left', 'cx' => 32.00, 'cy' => 29.52, 'port' => [125.5, 105.5], 'path' => 'M125.5 105.5 Q112.0 99.1 105.6 85.6'],
        'ur' => ['side' => 'right', 'cx' => 68.00, 'cy' => 29.52, 'port' => [204.5, 105.5], 'path' => 'M204.5 105.5 Q218.0 99.1 224.4 85.6'],
        'll' => ['side' => 'left', 'cx' => 32.00, 'cy' => 70.48, 'port' => [125.5, 184.5], 'path' => 'M125.5 184.5 Q119.1 198.0 105.6 204.4'],
        'lr' => ['side' => 'right', 'cx' => 68.00, 'cy' => 70.48, 'port' => [204.5, 184.5], 'path' => 'M204.5 184.5 Q210.9 198.0 224.4 204.4'],
        'bottom' => ['side' => 'bottom', 'cx' => 50.00, 'cy' => 78.97, 'port' => [165.0, 200.9], 'path' => 'M165.0 200.9 Q170.0 214.9 165.0 229.0'],
    ];
    $sets = [
        1 => ['top'],
        2 => ['ul', 'ur'],
        3 => ['top', 'll', 'lr'],
        4 => ['ul', 'ur', 'll', 'lr'],
        5 => ['top', 'ul', 'ur', 'll', 'lr'],
        6 => ['top', 'ul', 'ur', 'll', 'lr', 'bottom'],
    ];
    $chips = array_values(array_slice($products ?? [], 0, 6));
    $keys = $sets[count($chips)] ?? [];
@endphp
<div class="pg-network">
<svg class="pg-network__canvas" viewBox="0 0 330 290" width="330" height="290" aria-hidden="true" focusable="false">
    {{-- The ring the chips sit on (tablet/desktop): the ecosystem's orbit. --}}
    <circle class="pg-network__ring" cx="165" cy="145" r="84" fill="none" stroke="var(--pg-globe-line)" stroke-width=".75" stroke-opacity=".28" stroke-dasharray="1.5 6" stroke-linecap="round"/>

    {{-- Links: a faint dashed track, a glowing draw-in stroke, a port on the rim and a light riding the path (SMIL; hidden under reduced motion). --}}
    <g class="pg-network__links" fill="none" stroke-linecap="round">
        @foreach ($keys as $i => $key)
            <path id="pg-link-{{ $i }}" class="pg-network__track" d="{{ $slots[$key]['path'] }}" stroke="var(--pg-globe-line)" stroke-width="1" stroke-opacity=".35" stroke-dasharray="2 5"/>
            <path class="pg-network__link" d="{{ $slots[$key]['path'] }}" stroke="var(--pg-globe-glow)" stroke-width="1.1" stroke-opacity=".75" style="--i: {{ $i }}"/>
        @endforeach
    </g>
    @foreach ($keys as $i => $key)
        <circle class="pg-network__port" cx="{{ $slots[$key]['port'][0] }}" cy="{{ $slots[$key]['port'][1] }}" r="1.8" fill="var(--pg-globe-glow)"/>
        <circle class="pg-network__spark" r="2.2" fill="#fff" style="--i: {{ $i }}">
            <animateMotion dur="3.6s" begin="{{ $i * -0.7 }}s" repeatCount="indefinite" calcMode="spline" keyTimes="0;1" keySplines=".4 0 .2 1"><mpath href="#pg-link-{{ $i }}"/></animateMotion>
        </circle>
    @endforeach

    {{-- The globe, nested at 172×172 around (165,145). --}}
    <svg class="pg-globe" x="79" y="59" width="172" height="172" viewBox="0 0 320 320" overflow="visible">
        <defs>
            <radialGradient id="pg-globe-backdrop" cx="50%" cy="50%" r="50%">
                <stop offset="0" stop-color="var(--pg-globe-backdrop)" stop-opacity=".85"/>
                <stop offset=".6" stop-color="var(--pg-globe-backdrop)" stop-opacity=".45"/>
                <stop offset="1" stop-color="var(--pg-globe-backdrop)" stop-opacity="0"/>
            </radialGradient>
            <radialGradient id="pg-globe-glow" cx="50%" cy="50%" r="50%">
                <stop offset=".55" stop-color="var(--pg-globe-glow)" stop-opacity="0"/>
                <stop offset=".78" stop-color="var(--pg-globe-glow)" stop-opacity=".16"/>
                <stop offset="1" stop-color="var(--pg-globe-glow)" stop-opacity="0"/>
            </radialGradient>
            <radialGradient id="pg-globe-sheen" cx="35%" cy="30%" r="70%">
                <stop offset="0" stop-color="var(--pg-globe-glow)" stop-opacity=".14"/>
                <stop offset="1" stop-color="var(--pg-globe-glow)" stop-opacity="0"/>
            </radialGradient>
            <linearGradient id="pg-globe-rim" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="var(--pg-globe-glow)" stop-opacity=".9"/>
                <stop offset=".5" stop-color="var(--pg-globe-line)" stop-opacity=".35"/>
                <stop offset="1" stop-color="var(--pg-globe-glow)" stop-opacity=".7"/>
            </linearGradient>
            <linearGradient id="pg-globe-market" x1="0" y1="0" x2="1" y2="0">
                <stop offset="0" stop-color="var(--pg-globe-glow)" stop-opacity=".2"/>
                <stop offset=".6" stop-color="var(--pg-globe-glow)"/>
                <stop offset="1" stop-color="#fff"/>
            </linearGradient>
            <linearGradient id="pg-globe-area" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" stop-color="var(--pg-globe-glow)" stop-opacity=".22"/>
                <stop offset="1" stop-color="var(--pg-globe-glow)" stop-opacity="0"/>
            </linearGradient>
            <clipPath id="pg-globe-clip"><circle cx="160" cy="160" r="104"/></clipPath>
        </defs>

        {{-- Soft dark halo so the line art reads over anything, then the glow. --}}
        <circle cx="160" cy="160" r="160" fill="url(#pg-globe-backdrop)"/>
        <circle cx="160" cy="160" r="150" fill="url(#pg-globe-glow)"/>

        {{-- Sphere: tilted 16° like a desk globe. Parallels are static; the meridians spin. The detail group is desktop-only density. --}}
        <g class="pg-globe__sphere" transform="rotate(-16 160 160)">
            <circle cx="160" cy="160" r="104" fill="url(#pg-globe-sheen)"/>
            <g class="pg-globe__grid" fill="none" stroke="var(--pg-globe-line)" stroke-width="1" clip-path="url(#pg-globe-clip)">
                <ellipse cx="160" cy="160" rx="104" ry="30" stroke-opacity=".9"/>
                <ellipse cx="160" cy="108" rx="90" ry="26" stroke-opacity=".55"/>
                <ellipse cx="160" cy="212" rx="90" ry="26" stroke-opacity=".55"/>
                <ellipse cx="160" cy="70" rx="52" ry="15" stroke-opacity=".4"/>
                <ellipse cx="160" cy="250" rx="52" ry="15" stroke-opacity=".4"/>
                <g class="pg-globe__detail" stroke-opacity=".3">
                    <ellipse cx="160" cy="133" rx="100" ry="29"/>
                    <ellipse cx="160" cy="187" rx="100" ry="29"/>
                    <ellipse cx="160" cy="86" rx="73" ry="21"/>
                    <ellipse cx="160" cy="234" rx="73" ry="21"/>
                </g>
                <g class="pg-globe__meridians" stroke-opacity=".7">
                    <circle class="pg-globe__meridian" cx="160" cy="160" r="104" style="--i: 0"/>
                    <circle class="pg-globe__meridian" cx="160" cy="160" r="104" style="--i: 1"/>
                    <circle class="pg-globe__meridian" cx="160" cy="160" r="104" style="--i: 2"/>
                    <circle class="pg-globe__meridian" cx="160" cy="160" r="104" style="--i: 3"/>
                    <circle class="pg-globe__meridian" cx="160" cy="160" r="104" style="--i: 4"/>
                    <circle class="pg-globe__meridian" cx="160" cy="160" r="104" style="--i: 5"/>
                    <g class="pg-globe__detail" stroke-opacity=".5">
                        <circle class="pg-globe__meridian" cx="160" cy="160" r="104" style="--i: .5"/>
                        <circle class="pg-globe__meridian" cx="160" cy="160" r="104" style="--i: 1.5"/>
                        <circle class="pg-globe__meridian" cx="160" cy="160" r="104" style="--i: 2.5"/>
                        <circle class="pg-globe__meridian" cx="160" cy="160" r="104" style="--i: 3.5"/>
                        <circle class="pg-globe__meridian" cx="160" cy="160" r="104" style="--i: 4.5"/>
                        <circle class="pg-globe__meridian" cx="160" cy="160" r="104" style="--i: 5.5"/>
                    </g>
                </g>
            </g>
            <circle cx="160" cy="160" r="104" fill="none" stroke="url(#pg-globe-rim)" stroke-width="1.25"/>
        </g>

        {{-- Orbit rings: dashed paths whose dashes travel — data/capital in motion. --}}
        <g fill="none" stroke="var(--pg-globe-line)" stroke-linecap="round">
            <ellipse class="pg-globe__orbit" cx="160" cy="160" rx="150" ry="46" transform="rotate(-24 160 160)" stroke-width="1" stroke-opacity=".55" stroke-dasharray="3 9"/>
            <ellipse class="pg-globe__orbit pg-globe__orbit--reverse" cx="160" cy="160" rx="136" ry="38" transform="rotate(30 160 160)" stroke-width=".9" stroke-opacity=".4" stroke-dasharray="1.5 7"/>
        </g>

        {{-- Market line: an uptrend crossing the globe, with its area and five candles. --}}
        <g class="pg-globe__market">
            <path fill="url(#pg-globe-area)" d="M44 236 L80 214 L104 224 L130 190 L156 198 L184 160 L208 170 L236 128 L262 136 L290 96 L290 260 L44 260 Z"/>
            <path class="pg-globe__trend" fill="none" stroke="url(#pg-globe-market)" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" d="M44 236 L80 214 L104 224 L130 190 L156 198 L184 160 L208 170 L236 128 L262 136 L290 96"/>
            <g class="pg-globe__candles" stroke="var(--pg-globe-glow)" stroke-width="1" fill="var(--pg-globe-glow)">
                <line x1="92" y1="208" x2="92" y2="232"/><rect x="89.5" y="214" width="5" height="10" rx=".8"/>
                <line x1="143" y1="180" x2="143" y2="206"/><rect x="140.5" y="186" width="5" height="12" rx=".8" fill-opacity=".35"/>
                <line x1="196" y1="150" x2="196" y2="178"/><rect x="193.5" y="156" width="5" height="12" rx=".8"/>
                <line x1="249" y1="118" x2="249" y2="146"/><rect x="246.5" y="126" width="5" height="10" rx=".8" fill-opacity=".35"/>
                <line x1="276" y1="100" x2="276" y2="126"/><rect x="273.5" y="104" width="5" height="12" rx=".8"/>
            </g>
        </g>

        {{-- Network nodes: connection points on the sphere, two of them pulsing; more on desktop. --}}
        <g class="pg-globe__nodes" fill="var(--pg-globe-glow)">
            <circle cx="184" cy="160" r="2.4"/>
            <circle class="pg-globe__pulse" cx="184" cy="160" r="2.4" fill="none" stroke="var(--pg-globe-glow)" stroke-width="1"/>
            <circle cx="236" cy="128" r="2"/>
            <circle cx="118" cy="132" r="1.8" fill-opacity=".8"/>
            <circle class="pg-globe__pulse pg-globe__pulse--late" cx="118" cy="132" r="1.8" fill="none" stroke="var(--pg-globe-glow)" stroke-width="1"/>
            <circle cx="130" cy="190" r="1.6" fill-opacity=".7"/>
            <circle cx="290" cy="96" r="3"/>
            <circle cx="290" cy="96" r="6" fill="var(--pg-globe-glow)" fill-opacity=".25"/>
            <circle cx="212" cy="214" r="1.5" fill-opacity=".6"/>
            <circle cx="78" cy="118" r="1.5" fill-opacity=".6"/>
            <g class="pg-globe__detail">
                <circle cx="96" cy="176" r="1.4" fill-opacity=".6"/>
                <circle cx="226" cy="92" r="1.4" fill-opacity=".6"/>
                <circle cx="160" cy="246" r="1.4" fill-opacity=".5"/>
                <circle cx="248" cy="196" r="1.6" fill-opacity=".7"/>
                <circle class="pg-globe__pulse pg-globe__pulse--later" cx="248" cy="196" r="1.6" fill="none" stroke="var(--pg-globe-glow)" stroke-width="1"/>
                <path fill="none" stroke="var(--pg-globe-line)" stroke-width=".6" stroke-opacity=".35" d="M118 132 L184 160 L236 128 M184 160 L248 196 M96 176 L130 190"/>
            </g>
        </g>
    </svg>
</svg>

@if (count($chips))
    <ul class="pg-hero__nodes" aria-label="{{ __('home.hero.network_label') }}">
        @foreach ($keys as $i => $key)
            <li class="pg-hero__node-slot pg-hero__node-slot--{{ $slots[$key]['side'] }}" style="--cx: {{ $slots[$key]['cx'] }}; --cy: {{ $slots[$key]['cy'] }}; --i: {{ $i }}">
                <a class="pg-node pg-node--{{ $chips[$i]['key'] }}" href="{{ $chips[$i]['url'] }}">
                    <span class="pg-node__dot" aria-hidden="true"></span>
                    <span class="pg-node__label">{{ $chips[$i]['plain_name'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>
@endif
</div>
