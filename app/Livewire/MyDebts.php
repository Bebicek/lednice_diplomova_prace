<?php

namespace App\Livewire;

use App\Models\Debt;
use App\Models\Setting;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\SvgWriter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client')]
class MyDebts extends Component
{
    public function markAsPaid(int $debtId)
    {
        $debt = Debt::where('id', $debtId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $debt->update([
            'is_paid' => true,
            'paid_at' => now(),
        ]);

        $this->dispatch('debt-updated');
        $this->dispatch('toast-success', message: 'Dluh byl označen jako zaplacený.');
    }

    /**
     * Mark all unpaid accepted debts for a given creditor as paid.
     * $creditorKey = numeric creditor ID, or system (creditor_id = null).
     */
    public function markAllPaidForCreditor(string $creditorKey): void
    {
        $query = Debt::where('user_id', auth()->id())
            ->where('is_paid', false)
            ->where('is_accepted', true);

        if ($creditorKey === 'system') {
            $query->whereNull('creditor_id');
        } else {
            $query->where('creditor_id', (int) $creditorKey);
        }

        $count = $query->count();
        $query->update(['is_paid' => true, 'paid_at' => now()]);

        $this->dispatch('debt-updated');
        $this->dispatch('toast-success', message: "Označeno {$count} " . ($count === 1 ? 'dluh' : ($count <= 4 ? 'dluhy' : 'dluhů')) . ' jako zaplaceno.');
    }

    /**
     * Convert Czech account number + bank code to IBAN (CZ format).
     */
    private function generateCzechIban(string $bankCode, string $accountNumber): ?string
    {
        $bankCode = preg_replace('/\D/', '', $bankCode);
        $accountNumber = preg_replace('/\D/', '', $accountNumber);

        if (strlen($bankCode) !== 4 || $accountNumber === '') {
            return null;
        }

        // CZ BBAN = 4-digit bank code + 16-digit account number (left-padded with zeros)
        $bban = $bankCode . str_pad($accountNumber, 16, '0', STR_PAD_LEFT);

        // Rearrange for check digit: BBAN + CZ (C=12, Z=35) + 00
        $numericIban = $bban . '123500';
        $checkDigit = 98 - (int) bcmod($numericIban, '97');
        $checkStr = str_pad((string) $checkDigit, 2, '0', STR_PAD_LEFT);

        return 'CZ' . $checkStr . $bban;
    }

    /**
     * Build a SPAYD (Short Payment Descriptor) string for QR payments.
     */
    private function generateSpayd(string $iban, int $amountHalire): string
    {
        $amount = number_format($amountHalire / 100, 2, '.', '');
        return "SPD*1.0*ACC:{$iban}*AM:{$amount}*CC:CZK*MSG:Uhrada dluhu";
    }

    /**
     * Render a SPAYD string as an inline SVG QR code (data URI).
     */
    private function generateQrDataUri(string $spayd): string
    {
        return (new Builder(
            writer: new SvgWriter(),
            data: $spayd,
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 250,
            margin: 10,
        ))->build()->getDataUri();
    }

    public function render()
    {
        $userId = auth()->id();

        // Tab 1: Unpaid debts accepted, not yet paid
        $unpaidDebts = Debt::where('user_id', $userId)
            ->where('is_paid', false)
            ->where('is_accepted', true)
            ->with(['order.items.commodity', 'lunch.participants.items', 'lunch.participants.user', 'creditor'])
            ->latest()
            ->get();

        // Tab 2: Outgoing payments current user is the debtor, already paid
        $outgoingPayments = Debt::where('user_id', $userId)
            ->where('is_paid', true)
            ->with(['order.items.commodity', 'lunch.participants.items', 'lunch.participants.user', 'creditor'])
            ->latest('paid_at')
            ->get();

        // Tab 3: Incoming payments current user is the creditor, debtor has paid
        $incomingPayments = Debt::where('creditor_id', $userId)
            ->where('is_paid', true)
            ->with(['order.items.commodity', 'lunch.participants.items', 'lunch.participants.user', 'user'])
            ->latest('paid_at')
            ->get();

        $totalUnpaid = $unpaidDebts->sum('amount');
        $totalIncoming = $incomingPayments->sum('amount');

        // System bank account from settings
        $systemBankNumber = Setting::where('key', 'bank_account_number')->value('value') ?? '';
        $systemBankCode = Setting::where('key', 'bank_code')->value('value') ?? '';

        // Group unpaid debts by creditor for the payment cards
        $creditorGroups = $unpaidDebts
            ->groupBy(fn($d) => $d->creditor_id ?? 'system')
            ->map(function ($debts, $key) use ($systemBankNumber, $systemBankCode) {
                $isSystem = ($key === 'system');
                $creditor = $isSystem ? null : $debts->first()->creditor;
                $total = $debts->sum('amount');
                $iban = null;
                $qrUrl = null;

                if ($isSystem) {
                    if (!empty(trim($systemBankNumber)) && !empty(trim($systemBankCode))) {
                        $iban = $this->generateCzechIban($systemBankCode, $systemBankNumber);
                        if ($iban) {
                            $spayd = $this->generateSpayd($iban, $total);
                            $qrUrl = $this->generateQrDataUri($spayd);
                        }
                    }
                } elseif ($creditor && !empty(trim((string) $creditor->bank_number)) && !empty(trim((string) $creditor->bank_code))) {
                    $iban = $this->generateCzechIban($creditor->bank_code, $creditor->bank_number);
                    if ($iban) {
                        $spayd = $this->generateSpayd($iban, $total);
                        $qrUrl = $this->generateQrDataUri($spayd);
                    }
                }

                return [
                    'creditor_id_key' => (string) $key,
                    'creditor_name' => $creditor?->name ?? 'Lednička',
                    'is_system' => $isSystem,
                    'total' => $total,
                    'count' => $debts->count(),
                    'iban' => $iban,
                    'qr_url' => $qrUrl,
                ];
            })
            ->values();

        return view('livewire.my-debts', [
            'unpaidDebts' => $unpaidDebts,
            'outgoingPayments' => $outgoingPayments,
            'incomingPayments' => $incomingPayments,
            'totalUnpaid' => $totalUnpaid,
            'totalIncoming' => $totalIncoming,
            'creditorGroups' => $creditorGroups,
        ]);
    }
}
