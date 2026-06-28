@extends('layout.app')

@section('title', 'Subscription - MiniStack Cloud')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-10">
        <a href="{{ route('dashboard') }}" class="text-sm text-blue-600 hover:underline flex items-center gap-1 mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Back to dashboard
        </a>
        <h2 class="text-3xl font-bold">Manage your Subscription</h2>
        <p class="text-slate-600 dark:text-slate-400 mt-2">
            Current plan: <span class="font-semibold text-slate-900 dark:text-white">{{ $currentPackage->name }}</span> ·
            Used storage: <span class="font-semibold text-slate-900 dark:text-white">{{ number_format($usedGB, 4) }} GB</span>
        </p>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-8">
                <div class="flex items-center justify-between gap-4 mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white">Choose a new package</h3>
                    </div>
                </div>

                <form id="subscription-order-form" class="space-y-6">
                    @csrf
                    <input type="hidden" name="package_id" id="package_id" value="">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach ($packages as $package)
                            @php
                                $isCurrent = $package->id === $currentPackage->id;
                                $isUpgrade = $package->price_per_month > $currentPackage->price_per_month;
                                $isSelectable = $isUpgrade;
                            @endphp
                            <button
                                type="button"
                                @if (! $isSelectable) disabled @endif
                                class="package-card focus:outline-none text-left rounded-xl border-2 p-5 transition {{ $isCurrent ? 'is-current border-slate-700 bg-slate-900/40 dark:bg-slate-900/40' : ($isSelectable ? 'is-selectable border-slate-700 hover:border-blue-300 dark:hover:border-blue-500 hover:bg-slate-50 dark:hover:bg-slate-700' : 'border-slate-700/60 bg-slate-900/40 opacity-60 cursor-not-allowed') }} {{ $isSelectable ? 'cursor-pointer' : 'cursor-not-allowed' }}"
                                data-package-id="{{ $package->id }}"
                                data-package-name="{{ $package->name }}"
                                data-package-storage="{{ number_format($package->storage_limit_gb, 0) }} GB"
                                data-package-price="Rp {{ number_format($package->price_per_month, 0, ',', '.') }}"
                                data-package-raw-price="{{ (int) round($package->price_per_month) }}"
                                data-package-upgrade="{{ $isSelectable ? '1' : '0' }}"
                            >
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <div>
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $package->name }}</div>
                                        <div class="text-xs text-slate-500 mt-1">{{ $package->description }}</div>
                                    </div>
                                    @if ($isCurrent)
                                        <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.22em] shadow-sm ring-1 ring-inset" style="background:#22c55e;color:#ffffff;border-color:#86efac;">
                                            <span class="h-1.5 w-1.5 rounded-full" style="background:#ffffff;"></span>
                                            ACTIVE
                                        </span>
                                    @endif
                                </div>
                                <div class="space-y-1 text-sm text-slate-600 dark:text-slate-300">
                                    <div>{{ number_format($package->storage_limit_gb, 0) }} GB storage</div>
                                    <div>{{ $package->max_buckets }} max buckets</div>
                                    <div>{{ $package->vcpu_limit }} vCPU · {{ $package->ram_limit_mb }} MB RAM</div>
                                    <div>{{ number_format($package->bandwidth_limit_gb, 0) }} GB bandwidth</div>
                                    <div class="font-semibold mt-2">Rp {{ number_format($package->price_per_month, 0, ',', '.') }} / month</div>
                                </div>
                                @if (! $isSelectable)
                                    <div class="mt-4 text-xs text-slate-500 dark:text-slate-400">
                                        {{ $isCurrent ? 'This is your active plan.' : 'Select a higher tier to enable checkout.' }}
                                    </div>
                                @endif
                            </button>
                        @endforeach
                    </div>

                </form>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-8">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Recent orders</h3>
                <div class="space-y-3">
                    @forelse ($orders as $order)
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 dark:border-slate-700 px-4 py-3">
                            <div>
                                <div class="font-semibold text-slate-900 dark:text-white">{{ $order->order_id }}</div>
                                <div class="text-sm text-slate-500">{{ $order->package_name ?? '-' }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold text-slate-900 dark:text-white">Rp {{ number_format($order->gross_amount, 0, ',', '.') }}</div>
                                <div class="text-sm text-slate-500">{{ ucfirst($order->transaction_status) }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No orders yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-8 sticky top-24">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Order summary</h3>

                <div class="text-4xl font-bold text-slate-900 dark:text-white mb-8 border-b border-slate-200 dark:border-slate-700 pb-6">
                    <span id="summary-price">Rp 0</span>
                    <span class="text-base font-normal text-slate-500 block mt-1">monthly</span>
                </div>

                <div class="space-y-4 text-sm mb-8">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 dark:text-slate-400">Plan selected</span>
                        <span id="summary-plan" class="font-semibold text-slate-900 dark:text-white">-</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 dark:text-slate-400">Storage capacity</span>
                        <span id="summary-storage" class="font-semibold text-slate-900 dark:text-white">-</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 dark:text-slate-400">Current storage</span>
                        <span class="font-semibold text-slate-900 dark:text-white">{{ number_format($usedGB, 4) }} GB</span>
                    </div>
                </div>

                <button
                    type="submit"
                    form="subscription-order-form"
                    id="checkout-button"
                    class="mt-6 w-full inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-3 font-semibold text-white transition hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled
                >
                    Proceed to Checkout
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if (config('services.midtrans.client_key'))
    @php
        $snapScriptUrl = str_contains(config('services.midtrans.snap_url'), 'sandbox')
            ? 'https://app.sandbox.midtrans.com/snap/snap.js'
            : 'https://app.midtrans.com/snap/snap.js';
    @endphp
    <script src="{{ $snapScriptUrl }}" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
@endif
<script>
    const packageCards = document.querySelectorAll('.package-card');
    const packageIdInput = document.getElementById('package_id');
    const summaryPlan = document.getElementById('summary-plan');
    const summaryStorage = document.getElementById('summary-storage');
    const summaryPrice = document.getElementById('summary-price');
    const checkoutButton = document.getElementById('checkout-button');
    const form = document.getElementById('subscription-order-form');
    const csrfToken = form.querySelector('input[name="_token"]').value;

    const selectedCardStyles = {
        borderColor: '#2563eb',
        backgroundColor: 'rgba(219, 234, 254, 0.1)',
        boxShadow: '0 0 0 1px rgba(37, 99, 235, 0.14)',
    };

    function clearSelectedStyle(card) {
        card.style.borderColor = '';
        card.style.backgroundColor = '';
        card.style.boxShadow = '';
    }

    function setSelectedCard(card) {
        packageCards.forEach(item => {
            if (item.disabled || item.classList.contains('is-current')) {
                return;
            }

            clearSelectedStyle(item);
        });

        if (!card.disabled && !card.classList.contains('is-current')) {
            card.style.borderColor = selectedCardStyles.borderColor;
            card.style.backgroundColor = selectedCardStyles.backgroundColor;
            card.style.boxShadow = selectedCardStyles.boxShadow;
        }
    }

    function setSummaryFromCard(card) {
        packageIdInput.value = card.dataset.packageId;
        summaryPlan.textContent = card.dataset.packageName;
        summaryStorage.textContent = card.dataset.packageStorage;
        summaryPrice.textContent = card.dataset.packagePrice;

        const isUpgrade = card.dataset.packageUpgrade === '1';
        checkoutButton.disabled = !isUpgrade;
        setSelectedCard(card);
    }

    packageCards.forEach(card => {
        if (card.disabled) {
            return;
        }

        card.addEventListener('click', () => {
            setSummaryFromCard(card);
        });
    });

    const currentCard = Array.from(packageCards).find(card => card.classList.contains('is-current'));
    if (currentCard) {
        setSummaryFromCard(currentCard);
    } else {
        const firstUpgradeCard = Array.from(packageCards).find(card => !card.disabled && card.dataset.packageUpgrade === '1');
        if (firstUpgradeCard) {
            firstUpgradeCard.click();
        } else if (packageCards.length > 0) {
            const firstSelectableCard = Array.from(packageCards).find(card => !card.disabled);
            if (firstSelectableCard) {
                firstSelectableCard.click();
            } else {
                setSummaryFromCard(packageCards[0]);
            }
        }
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!packageIdInput.value) {
            return;
        }

        checkoutButton.disabled = true;

        try {
            const response = await fetch('{{ route('dashboard.subscription.orders.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    package_id: packageIdInput.value,
                }),
            });

            const payload = await response.json();

            if (!response.ok) {
                alert("Checkout Gagal: " + (payload.message || "Terjadi kesalahan sistem."));
                console.error("Detail Eror:", payload);
                
                checkoutButton.disabled = false;
                return;
            }

            if (window.snap && payload.snap_token) {
                window.snap.pay(payload.snap_token, {
                    onSuccess: async function () {
                        try {
                            const syncResponse = await fetch('{{ route('dashboard.subscription.orders.sync') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                },
                                body: JSON.stringify({
                                    order_id: payload.order_id,
                                }),
                            });

                            await syncResponse.json();

                            window.location.reload();
                        } catch (syncError) {
                            window.location.reload();
                        }
                    },
                    onPending: function () {
                        checkoutButton.disabled = false;
                    },
                    onError: function () {
                        checkoutButton.disabled = false;
                    },
                    onClose: function () {
                        checkoutButton.disabled = false;
                    },
                });
            } else if (payload.redirect_url) {
                window.location.href = payload.redirect_url;
            } else {
                checkoutButton.disabled = false;
            }
        } catch (error) {
            console.error(error);
            checkoutButton.disabled = false;
        }
    });
</script>
@endpush
