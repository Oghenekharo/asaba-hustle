<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminRatingIndexRequest;
use App\Models\Rating;

class AdminRatingController extends Controller
{
    public function index(AdminRatingIndexRequest $request)
    {
        $ratings = Rating::with(['worker', 'client', 'job'])
            ->adminFilter($request->filters())
            ->latest()
            ->paginate(20);

        return view('admin.ratings.index', compact('ratings'));
    }

    public function destroy(Rating $rating)
    {
        $rating->delete();

        return back();
    }
}
