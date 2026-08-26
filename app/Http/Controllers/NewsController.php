<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index()
    {
        if (session('user_logged_in') !== true) {
            return redirect()->route('login');
        }

        $news = News::orderBy('is_pinned', 'desc')->latest()->get();
        return view('news.index', compact('news'));
    }
}
