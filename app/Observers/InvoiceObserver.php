<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\InvoiceDetail;
use Illuminate\Support\Facades\Auth;

class InvoiceObserver
{
    public function created(Invoice $invoice): void
    {
        $platforms = ['uber', 'bolt', 'bliq', 'freenow'];

        foreach ($platforms as $platform) {
            InvoiceDetail::create([
                'invoice_id' => $invoice->id,
                'platform' => $platform,
            ]);
        }
    }
}
