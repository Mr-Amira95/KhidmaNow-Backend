<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\DebtPaymentResource;
use App\Http\Resources\PayoutResource;
use App\Http\Resources\WalletTransactionResource;
use App\Http\Traits\ApiResponse;
use App\Models\DebtPayment;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\Provider;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FinancialController extends Controller
{
    use ApiResponse;

    /**
     * Company-wide cash-flow overview: income (client payments + provider debt
     * repayments) vs outcome (provider payouts), plus commission actually earned
     * and a monthly trend for the given (or trailing 12-month) range.
     */
    public function overview(Request $request)
    {
        [$from, $to] = $this->resolveRange($request);

        $income = $this->sumPaid(Payment::class, $from, $to) + $this->sumPaid(DebtPayment::class, $from, $to);
        $outcome = $this->sumPaid(Payout::class, $from, $to);

        $commissionQuery = WalletTransaction::where('source_type', 'commission')->where('type', 'debit');
        if ($from) $commissionQuery->where('created_at', '>=', $from);
        if ($to) $commissionQuery->where('created_at', '<=', $to);
        $commissionEarned = (float) $commissionQuery->sum('amount');

        $pendingPayouts = (float) Payout::whereIn('status', ['pending', 'processing'])->sum('amount');
        $outstandingDebt = (float) abs(Wallet::whereHas('user.provider')->where('balance', '<', 0)->sum('balance'));

        return $this->success([
            'summary' => [
                'income'             => (float) $income,
                'outcome'            => (float) $outcome,
                'net'                => (float) ($income - $outcome),
                'commission_earned'  => $commissionEarned,
                'pending_payouts'    => $pendingPayouts,
                'outstanding_debt'   => $outstandingDebt,
                'income_breakdown'  => [
                    'client_payments'        => (float) $this->sumPaid(Payment::class, $from, $to),
                    'provider_debt_payments'  => (float) $this->sumPaid(DebtPayment::class, $from, $to),
                ],
            ],
            'monthly' => $this->monthlySeries($from, $to),
            'range'   => [
                'from' => $from?->toDateString(),
                'to'   => $to?->toDateString(),
            ],
        ]);
    }

    /**
     * Per-provider financial table: lifetime paid payouts/debt, currently pending
     * payouts, and the live wallet balance (positive = owed to the provider,
     * negative = owed by the provider), matching Provider::debtAmount()'s sign.
     */
    public function providers(Request $request)
    {
        $query = Provider::with('user:id,name,email')
            ->withSum(['payouts as paid_payouts_sum' => fn ($q) => $q->where('status', 'paid')], 'amount')
            ->withSum(['payouts as pending_payouts_sum' => fn ($q) => $q->whereIn('status', ['pending', 'processing'])], 'amount')
            ->withSum(['debtPayments as paid_debt_sum' => fn ($q) => $q->where('status', 'paid')], 'amount');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        // Most-indebted providers first (negative wallet balance = owed to the company).
        $query->orderByRaw(
            '(SELECT COALESCE(w.balance, 0) FROM wallets w WHERE w.user_id = providers.user_id) asc'
        );

        $paginator = $query->paginate(15);

        $wallets = Wallet::whereIn('user_id', collect($paginator->items())->pluck('user_id'))
            ->get()
            ->keyBy('user_id');

        $data = collect($paginator->items())->map(fn (Provider $provider) => [
            'id'              => $provider->id,
            'business_name'   => $provider->business_name,
            'user'            => $provider->user ? [
                'id'    => $provider->user->id,
                'name'  => $provider->user->name,
                'email' => $provider->user->email,
            ] : null,
            'paid_payouts'    => (float) ($provider->paid_payouts_sum ?? 0),
            'pending_payouts' => (float) ($provider->pending_payouts_sum ?? 0),
            'paid_debt'       => (float) ($provider->paid_debt_sum ?? 0),
            'wallet_balance'  => (float) ($wallets->get($provider->user_id)?->balance ?? 0),
        ])->values();

        return response()->json([
            'status' => 'success',
            'data'   => $data,
            'meta'   => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    public function providerShow(Provider $provider)
    {
        $provider->load('user:id,name,email');
        $wallet = Wallet::where('user_id', $provider->user_id)->first();

        return $this->success([
            'provider' => [
                'id'            => $provider->id,
                'business_name' => $provider->business_name,
                'user'          => $provider->user ? [
                    'id'    => $provider->user->id,
                    'name'  => $provider->user->name,
                    'email' => $provider->user->email,
                ] : null,
            ],
            'summary' => [
                'paid_payouts'    => (float) Payout::where('provider_id', $provider->id)->where('status', 'paid')->sum('amount'),
                'pending_payouts' => (float) Payout::where('provider_id', $provider->id)->whereIn('status', ['pending', 'processing'])->sum('amount'),
                'paid_debt'       => (float) DebtPayment::where('provider_id', $provider->id)->where('status', 'paid')->sum('amount'),
                'wallet_balance'  => (float) ($wallet->balance ?? 0),
            ],
            'recent_payouts'             => PayoutResource::collection(
                Payout::where('provider_id', $provider->id)->latest()->limit(10)->get()
            ),
            'recent_debt_payments'       => DebtPaymentResource::collection(
                DebtPayment::where('provider_id', $provider->id)->latest()->limit(10)->get()
            ),
            'recent_wallet_transactions' => WalletTransactionResource::collection(
                $wallet ? $wallet->transactions()->latest()->limit(20)->get() : collect()
            ),
        ]);
    }

    private function resolveRange(Request $request): array
    {
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : null;
        $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : null;

        return [$from, $to];
    }

    private function sumPaid(string $modelClass, ?Carbon $from, ?Carbon $to): float
    {
        $query = $modelClass::where('status', 'paid');
        if ($from) $query->where('paid_at', '>=', $from);
        if ($to) $query->where('paid_at', '<=', $to);

        return (float) $query->sum('amount');
    }

    private function monthlySeries(?Carbon $from, ?Carbon $to): array
    {
        $start = $from ? $from->copy()->startOfMonth() : now()->copy()->subMonths(11)->startOfMonth();
        $end = $to ? $to->copy()->startOfMonth() : now()->copy()->startOfMonth();

        $months = [];
        $cursor = $start->copy();

        while ($cursor <= $end) {
            $monthStart = $cursor->copy()->startOfMonth();
            $monthEnd = $cursor->copy()->endOfMonth();

            $monthIncome = $this->sumPaid(Payment::class, $monthStart, $monthEnd)
                + $this->sumPaid(DebtPayment::class, $monthStart, $monthEnd);
            $monthOutcome = $this->sumPaid(Payout::class, $monthStart, $monthEnd);

            $months[] = [
                'month'   => $monthStart->format('Y-m'),
                'income'  => $monthIncome,
                'outcome' => $monthOutcome,
                'net'     => $monthIncome - $monthOutcome,
            ];

            $cursor->addMonth();
        }

        return $months;
    }
}
