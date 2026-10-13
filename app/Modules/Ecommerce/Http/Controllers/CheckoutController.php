<?php

namespace App\Modules\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Ecommerce\Services\Payment\PaymentManager;
use App\Support\Seo\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Ecommerce/Checkout', [
            'gateways' => ['stripe', 'iyzico'],
            'head' => Seo::page(['title' => 'Ödeme', 'noindex' => true]),
        ]);
    }

    public function store(Request $request, PaymentManager $payments): RedirectResponse
    {
        $data = $request->validate([
            'gateway' => ['required', 'in:stripe,iyzico'],
            'amount' => ['required', 'numeric', 'min:1'],
            'currency' => ['required', 'string', 'size:3'],
        ]);

        $result = $payments->driver($data['gateway'])->charge([
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'user' => $request->user(),
        ]);

        return redirect()
            ->route('ecommerce.checkout')
            ->with('success', "Payment initialised ({$result['reference']}).");
    }
}
