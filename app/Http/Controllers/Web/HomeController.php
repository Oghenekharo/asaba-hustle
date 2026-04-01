<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            /** @var \App\Models\User|null $user 08000000010 */
            $user = Auth::user();
            return redirect()->route(
                $user->hasRole('admin') ? 'admin.dashboard' : 'web.app'
            );
        }

        $skills = Schema::hasTable('skills')
            ? Skill::query()->orderBy('name')->limit(8)->get()
            : collect();

        // return view('web.home', compact('skills'));
        return view('welcome');
    }

    public function subscribe(Request $request)
    {
        $payload = $request->validate([
            'endpoint' => ['required', 'string'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
        ]);

        /** @var \App\Models\User|null $user 08000000010 */
        $user = $request->user();

        $user->updatePushSubscription(
            $payload['endpoint'],
            $payload['keys']['p256dh'],
            $payload['keys']['auth']
        );

        return response()->json(['success' => true]);
    }
}
