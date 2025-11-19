<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Enums\PaymentStatus;
use App\Services\ChapaPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChapaPaymentController extends Controller
{
    // POST /api/v1/payments
    public function initiatePayment(Request $request)
    {
        $user    = Auth::user();
        $amount  = $request->input('amount', 100);
        $currency = $request->input('currency', 'ETB');
        $txRef   = 'chapa_' . uniqid();

        $payment = Payment::create([
            'user_id'  => $user->id,
            'email'    => $user->email,
            'amount'   => $amount,
            'currency' => $currency,
            'tx_ref'   => $txRef,
            'status'   => PaymentStatus::PENDING
        ]);

        $chapa = new ChapaPaymentService();
        try {
            $returnUrl = config('app.url') . '/payments/return/' . $payment->tx_ref; // simple pattern
            $checkoutUrl = $chapa->initializePayment([
                'amount'       => $payment->amount,
                'currency'     => $payment->currency,
                'email'        => $payment->email,
                'first_name'   => $user->name,
                'tx_ref'       => $payment->tx_ref,
                'callback_url' => route('api.chapa.webhook'),
                'return_url'   => $returnUrl,
            ]);
            return response()->json([
                'tx_ref'       => $payment->tx_ref,
                'checkout_url' => $checkoutUrl,
                'status'       => $payment->status->value,
            ], 201);
        } catch (\Exception $e) {
            $payment->update(['status' => PaymentStatus::INIT_FAILED]);
            return response()->json([
                'message' => 'Initialization failed',
                'tx_ref'  => $payment->tx_ref,
                'status'  => PaymentStatus::INIT_FAILED->value
            ], 502);
        }
    }

    // POST /api/v1/payments/webhook
    public function handleCallback(Request $request)
    {
        $txRef = $request->input('trx_ref');
        if (!$txRef) {
            return response()->json(['error' => 'trx_ref missing'], 400);
        }

        $payment = Payment::where('tx_ref', $txRef)->first();
        if (!$payment) {
            return response()->json(['error' => 'unknown tx_ref'], 404);
        }

        $chapa   = new ChapaPaymentService();
        $verify  = $chapa->verifyTransaction($txRef);
        $success = ($verify['status'] ?? null) === 'success' && ($verify['data']['status'] ?? null) === 'success';
    $payment->status = $success ? PaymentStatus::SUCCESS : PaymentStatus::FAILED;
        $payment->save();

    return response()->json(['status' => $payment->status->value]);
    }

    // GET /api/v1/payments/{tx_ref}
    public function show($tx_ref)
    {
        $payment = Payment::where('tx_ref', $tx_ref)->first();
        if (!$payment) {
            return response()->json(['message' => 'Not found'], 404);
        }
        if ($payment->user_id && Auth::id() !== $payment->user_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        return response()->json([
            'tx_ref'   => $payment->tx_ref,
            'amount'   => $payment->amount,
            'currency' => $payment->currency,
            'status'   => $payment->status->value,
        ]);
    }
}
