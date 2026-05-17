@php
    $currentLanguageCode = !empty(request()->lang) ?
        ((!empty($languages) && !empty($languages->firstWhere('code', request()->lang))) ? request()->lang :
        app()->getLocale()) :
        app()->getLocale();

    if (!empty($client) && !empty($client->lang)) {
        $currentLanguageCode = $client->lang;
    }

    /*
        Prompt: php laravel i got $currentLanguageCode, and $languages collections (id, code, name)
        now i want to sort the languages
        which the object that has $currentLanguageCode will be the first object in the collection,
        otherwise if the $currentLanguageCode does not exist in the collection then change nothing
    */

    if ($languages->contains('code', $currentLanguageCode)) {
        $languages = $languages->sortBy(function ($language) use ($currentLanguageCode) {
            return $language->code === $currentLanguageCode ? 0 : 1;
        })->values();
    }

    $defaultLanguage = $languages->first() ?? null;
    $defaultLanguageCode = !empty($languages) ? ($languages->first()->code ?? null) : null;
@endphp

@if (!empty($languages) && !empty($defaultLanguage))
    <div class="dropdown">
        {{-- <a id="lang-choice" class="btn dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="{{ env("APP_STORAGE_URL")."/".$languages[$curLang]['icon'] }}" width="20" />
            {{ $languages[$curLang]['name'] }}
        </a>
        <div class="init-default-language">
            <input type="hidden" id="init-lang" value="{{ $curLang }}"
                data-url="{{ route('change-language', [
                    'locale' => $curLang
                ]) }}"
            >
        </div> --}}

        <a id="lang-choice"
            class="btn dropdown-toggle"
            href="#"
            role="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
        >
            <img src="{{ asset("storage/{$defaultLanguage->icon_path}") }}" width="20" alt=""/>
            {{ $defaultLanguage->name }}
        </a>

        <ul class="dropdown-menu">
            @foreach ($languages as $language)
                @php
                    $redirectTo = "#";

                    if (!empty(request()->lang)) {
                        $params['locale'] = $language->code;
                    }

                    if (isset($edit) && $edit) {
                        $redirectTo = route(Route::currentRouteName(), array_merge(request()->route()->parameters(), [
                            'lang'          => $language->code,
                            'is_success'    => request()->is_success,
                        ]));
                    }
                @endphp

                <li>
                    <a class="dropdown-item change-language"
                        href="{{ $redirectTo }}"
                        data-url="{{ route('change-language', [
                            'locale' => $language->code
                        ]) }}"
                        data-route="{{ route(Route::currentRouteName(), request()->route()->parameters()) }}"
                        data-params="{{ json_encode($params ?? []) }}"
                    >
                        <img src="{{ asset("storage/{$language->icon_path}") }}" width="20" alt=""/>
                        {{ $language->name }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
