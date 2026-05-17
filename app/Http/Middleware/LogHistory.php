<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\History;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class LogHistory
{
    public function handle($request, Closure $next)
    {
        // Thu thập param an toàn – KHÔNG dính file
        $parameters = collect($request->except([
            '_token', 'token', '_method', 'method'
        ]))
        ->reject(fn ($v) => $v instanceof \Illuminate\Http\UploadedFile)
        ->toArray();

        $fileNames = [];
        if ($request->hasFile('image')) {
            foreach ((array) $request->file('image') as $file) {
                if ($file->isValid()) {
                    $fileNames[] = $file->getClientOriginalName();
                }
            }
        }
        if ($fileNames) {
            $parameters['image'] = $fileNames;
        }

        $response = $next($request);

        // Xử lý object và user
        $object = $this->convertToDbObject($this->getObjectTypeFromRoute($request));
        $objects = History::getAllObjects();
        $error = null;

        if (Auth::check()) {
                $user = auth()->user();
            } else {
                if ($object == "login") {
                    $email = $request->input('email');
                    $error = "Login failed. Email address: {$email}";
                }
            }

            /* CHECK IF params has "page" */
            if ($request->method() == "GET") {
                if (empty($request->page)) {
            return $response;
                }
        }

        if (in_array($object, $objects['tables'])) {
                /* CHECK IF json-api-auth THEN SHOW ONLY EMAIL & NAME */
            if (in_array($object, $objects['auth'])) {
                $parameters = [
                        'email' => !empty($request->input('email')) ? $request->input('email') : null,
                        'name'  => !empty($request->input('name')) ? $request->input('name') : null,
                ];
            }

            $history = [
                'user_id'       => !empty($user) ? $user->id : null,
                'action'        => $this->getActionFromRoute($request),
                'object'        => $object,
                'function'      => $this->getFunctionFromRoute($request),
                'method'        => $request->method(),
                'parameters'    => $parameters,
                'error'         => $error,
            ];

            History::create($history);
        }

        return $response;
    }

    protected function getActionFromRoute($request)
    {
        return $request->route()->getName() ?? $request->route()->uri();
    }

    protected function getObjectTypeFromRoute($request)
    {
        $routeName = $request->route()->getName();
        $parts = explode('.', $routeName);
        return $parts[1] ?? null;

        $uri = $request->route()->uri();
        $object = Str::afterLast($uri, '/');
        return $object;
    }

    protected function getFunctionFromRoute($request)
    {
        $uri = $request->route()->getName();
        $action = Str::afterLast($uri, '.');
        $action = Str::before($action, '.');
        return $action;
    }

    protected function convertToDbObject($objectWithUnderscores) {
        $objectWithUnderscores = $this->removeBraces($objectWithUnderscores);
        $stringWithHyphens = str_replace('-', '_', $objectWithUnderscores);
        return $stringWithHyphens;
    }

    /* Check if the string starts with '{' and ends with '}' */

    protected function removeBraces($object) {
        if (preg_match('/^\{.*\}$/', $object)) {
            $object = trim($object, '{}');
        }

        return $object;
    }
}
