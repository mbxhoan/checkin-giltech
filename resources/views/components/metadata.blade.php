<meta http-equiv=”content-language” content="{{ !empty($language) ? $language : 'vi' }}" />

<title>{{ !empty($title) ? $title : '' }} | {{ config('app.name', 'Check-In App') }}</title>
<meta name="description" content="{{ !empty($description) ? $description : '' }}">
<meta name="robots" content="{{ !empty($robots) ? $robots : '' }}"/> {{-- noodp,index,follow --}}
{{-- <meta name=”google” content=”nositelinkssearchbox” /> --}}
{{-- Google / Search Engine Tags --}}
<meta itemprop="name" content="{{ !empty($title) ? $title : '' }}">
<meta itemprop="description" content="{{ !empty($description) ? $description : '' }}">
<meta itemprop="image" content="{{ !empty($image) ? $image : '' }}">
{{-- Facebook Meta Tags --}}
<meta property="og:url" content="{{ !empty($url) ? $url : '' }}">
<meta property="og:type" content="website">
<meta property="og:title" content="{{ !empty($title) ? $title : '' }}">
<meta property="og:description" content="{{ !empty($description) ? $description : '' }}">
<meta property="og:image" content="{{ !empty($image) ? $image : '' }}">
<meta property="og:image:alt" content="{{ !empty($title) ? $title : '' }}">
{{-- Twitter Meta Tags --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ !empty($title) ? $title : '' }}">
<meta name="twitter:description" content="{{ !empty($description) ? $description : '' }}">
<meta name="twitter:image" content="{{ !empty($image) ? $image : '' }}">
