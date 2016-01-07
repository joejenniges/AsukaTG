<?php

namespace App\Http\Middleware;

use Closure;

class BotMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $botKey = null;
        if (isset($request->route()[2]['botKey'])) {
            $botKey = $request->route()[2]['botKey'];
        }

        if (!$botKey) {
            return redirect();
        }

        $telegram = app('telegram');
        if ($botKey != $telegram->getBotConfig(config('telegram.default'))['token']) {
            return redirect();
        }
        return $next($request);
    }
}
