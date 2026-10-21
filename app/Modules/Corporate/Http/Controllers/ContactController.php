<?php

namespace App\Modules\Corporate\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Corporate\Models\ContactMessage;
use App\Support\Seo\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/** İletişim sayfası ve formu: mesaj panelde "Gelen Mesajlar"a düşer (bal küpü + hız sınırı rotada). */
class ContactController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Corporate/Contact', [
            'subjects' => ContactMessage::subjects(),
            'head' => Seo::page(['title' => 'İletişim', 'breadcrumbs' => [['İletişim']]]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'subject' => ['nullable', Rule::in(array_keys(ContactMessage::subjects()))],
            'message' => 'required|string|min:10|max:5000',
            'consent' => 'accepted',
        ], [
            'consent.accepted' => 'Devam etmek için aydınlatma metnini onaylayın.',
        ]);

        ContactMessage::create([
            ...collect($validated)->except('consent')->all(),
            'consented_at' => now(),
            'status' => 'new',
        ]);

        return back()->with('success', 'Mesajınız alındı. En kısa sürede dönüş yapacağız.');
    }
}
