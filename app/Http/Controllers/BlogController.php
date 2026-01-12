<?php

namespace App\Http\Controllers;

use App\Models\Blog;

class BlogController extends Controller
{
    public function index()
    {
        return view('blog.index');
    }

    public function show(string $slug, int $id)
    {
        $locale = app()->getLocale();

        $blog = Blog::query()
            ->where('id', $id)
            ->where('status', true)
            ->where("slug->{$locale}", $slug)
            ->with([
                'contents' => function ($q) {
                    $q->where('status', true)
                        ->orderBy('sort_order', 'asc');
                }
            ])
            ->firstOrFail();

        // okunma sayısı
        $blog->increment('click_count');

        /* ---------------- HERO TITLE ---------------- */

        $rawTitle = trim($blog->getTranslation('title', $locale) ?? '');
        $words = preg_split('/\s+/', $rawTitle, -1, PREG_SPLIT_NO_EMPTY);
        $count = count($words);

        $first = '';
        $second = '';
        $break = false;

        if ($count === 1) {
            $first = $words[0];
        } elseif ($count === 2) {
            $first = $words[0];
            $second = $words[1];
        } elseif ($count === 3) {
            $first = $words[0] . ' ' . $words[1];
            $second = $words[2];
            $break = true;
        } else {
            $first = implode(' ', array_slice($words, 0, 3));
            $second = implode(' ', array_slice($words, 3));
            $break = true;
        }

        /* ---------------- HERO DATA ---------------- */

        $hero = [
            'title' => [
                'first'  => $first,
                'second' => $second,
                'break'  => $break,
            ],
            'themes'  => $blog->getTranslation('themes', $locale) ?? [],
            'excerpt' => $blog->getTranslation('excerpt', $locale) ?? '',
        ];
        
        return view('blog.show', compact('blog', 'locale', 'hero'));
    }
}
