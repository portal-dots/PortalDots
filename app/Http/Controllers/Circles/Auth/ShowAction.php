<?php

namespace App\Http\Controllers\Circles\Auth;

use App\Eloquents\Circle;
use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class ShowAction extends Controller
{
    public function __invoke(Circle $circle)
    {
        $this->authorize('circle.belongsTo', $circle);

        $reauthorized_at = new CarbonImmutable(session()->get('user_reauthorized_at'));

        if (session()->has('user_reauthorized_at') && $reauthorized_at->addHours(2)->gte(now())) {
            return redirect()
                ->route('circles.show', ['circle' => $circle]);
        }
        return view('circles.auth')
            ->with('circle', $circle)
            ->with('booth', $circle->booth);
    }
}
