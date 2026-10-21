<?php

namespace App\Modules\Announcements\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Announcements\Models\Announcement;
use App\Support\Seo\Seo;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller
{
    public function index(): Response
    {
        $announcements = Announcement::query()
            ->published()
            ->latest('published_at')
            ->paginate(12)
            ->through(fn (Announcement $announcement) => $announcement->toCard());

        return Inertia::render('Announcements/Index', [
            'announcements' => $announcements,
            // Boş liste ince içerik: dizine girmesin (sitemap'te de yok).
            'head' => Seo::page(['title' => 'Duyurular', 'noindex' => $announcements->isEmpty(), 'breadcrumbs' => [['Duyurular']]]),
        ]);
    }

    public function show(string $slug): Response
    {
        $announcement = Announcement::query()->published()->where('slug', $slug)->firstOrFail();

        return Inertia::render('Announcements/Show', [
            'announcement' => [...$announcement->toCard(), 'body' => $announcement->body],
            'head' => Seo::page([
                'title' => $announcement->meta_title ?: $announcement->title,
                'description' => $announcement->meta_description ?: ($announcement->summary ?: $announcement->body),
                'image' => $announcement->getFirstMediaUrl('cover') ?: null,
                'type' => 'article',
                'published_time' => $announcement->published_at,
                'modified_time' => $announcement->updated_at,
                'breadcrumbs' => [['Duyurular', route('announcements.index', absolute: false)], [$announcement->title]],
            ]),
        ]);
    }
}
