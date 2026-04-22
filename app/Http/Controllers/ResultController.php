<?php

namespace App\Http\Controllers;

use App\Models\Result;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function show(Request $request, Result $result)
    {
        abort_unless((int) $result->user_id === (int) $request->user()->id, 403);

        $result->load(['exam' => fn($q) => $q->withTrashed()]);

        return view('exam.result', compact('result'));
    }
}

