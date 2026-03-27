<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User|null $user 08000000010 */
        if (Auth::check()) {
            return redirect()->route(
                Auth::user()->hasRole('admin') ? 'admin.dashboard' : 'web.app'
            );
        }

        $skills = Schema::hasTable('skills')
            ? Skill::query()->orderBy('name')->limit(8)->get()
            : collect();

        // return view('web.home', compact('skills'));
        return view('welcome');
    }
}
