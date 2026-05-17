<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class Localize
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
        
        $session = $request->session();
        $sessionId = $session->getId();

        $locale = !empty($request->query()['locale']) ?
            $request->query()['locale'] :
                ($session->get("{$sessionId}.language") ?
                $session->get("{$sessionId}.language"):
                config('app.locale'));

        $languages = Language::where('status', Language::STATUS_ACTIVE)->get()->pluck('id', 'code')->toArray();

        if (in_array($locale, array_keys($languages))) {
            App::setLocale($locale);
            return $next($request);
        } else {
            abort(404);
        }
    }
}
