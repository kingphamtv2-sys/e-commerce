<?php

namespace App\Services;

use App\Models\InventoryStock;
use App\Models\Order;
use App\Models\User;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AdminOrderService
{
    private const TRANSITIONS = [
        'pending' => ['confirmed'],
        'confirmed' => ['processing'],
        'processing' => ['shipped', 'completed'],
        'shipped' => ['completed'],
        'completed' => [],
        'cancelled' => [],
        'refunded' => [],
    ];

    private const FULFILLMENT_TRANSITIONS = [
        'unfulfilled' => ['processing', 'shipped', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped' => ['delivered'],
        'delivered' => [],
        'cancelled' => [],
    ];

    private const PAYMENT_TRANSITIONS = [
        'unpaid' => ['pending', 'paid', 'failed', 'cancelled'],
        'pending' => ['unpaid', 'paid', 'failed', 'cancelled'],
        'failed' => ['unpaid', 'pending', 'paid', 'cancelled'],
        'paid' => [],
        'cancelled' => [],
        'refunded' => [],
    ];

    public function __construct(private readonly EmailNotificationService $emails) {}

    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return Order::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('order_code', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_email', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%");
                });
            })
            ->when($filters['order_status'] ?? null, fn ($query, $status) => $query->where('order_status', $status))
            ->when($filters['payment_status'] ?? null, fn ($query, $status) => $query->where('payment_status', $status))
            ->when($filters['fulfillment_status'] ?? null, fn ($query, $status) => $query->where('fulfillment_status', $status))
            ->when($filters['payment_method'] ?? null, fn ($query, $method) => $query->where('payment_method', $method))
            ->when(($filters['customer_type'] ?? null) === 'guest', fn ($query) => $query->whereNull('user_id'))
            ->when(($filters['customer_type'] ?? null) === 'customer', fn ($query) => $query->whereNotNull('user_id'))
            ->when(($filters['coupon_used'] ?? null) === 'yes', fn ($query) => $query->whereNotNull('coupon_snapshot'))
            ->when(($filters['coupon_used'] ?? null) === 'no', fn ($query) => $query->whereNull('coupon_snapshot'))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('placed_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('placed_at', '<=', $date))
            ->when(
                $filters['sort'] ?? 'newest',
                fn ($query, $sort) => match ($sort) {
                    'oldest' => $query->oldest('placed_at')->oldest('id'),
                    'total_high' => $query->orderByDesc('total_amount')->latest('id'),
                    'total_low' => $query->orderBy('total_amount')->latest('id'),
                    default => $query->latest('placed_at')->latest('id'),
                },
            )
            ->paginate($perPage)
            ->withQueryString();
    }

    public function allowedTransitions(Order $order): array
    {
        return self::TRANSITIONS[$order->order_status] ?? [];
    }

    public function canCancel(Order $order): bool
    {
        return in_array($order->order_status, ['pending', 'confirmed', 'processing'], true);
    }

    public function allowedFulfillmentTransitions(Order $order): array
    {
        if (in_array($order->order_status, ['cancelled', 'completed'], true)) {
            return [];
        }

        return self::FULFILLMENT_TRANSITIONS[$order->fulfillment_status] ?? [];
    }

    public function allowedPaymentTransitions(Order $order): array
    {
        if ($order->payment_method !== 'cod' || $order->order_status === 'cancelled') {
            return [];
        }

        return self::PAYMENT_TRANSITIONS[$order->payment_status] ?? [];
    }

    public function updateStatus(Order $order, string $status, ?string $note, User $actor): Order
    {
        [$updated, $from, $historyId] = DB::transaction(function () use ($order, $status, $note, $actor): array {
            $locked = Order::query()->lockForUpdate()->findOrFail($order->id);
            if (! in_array($status, $this->allowedTransitions($locked), true)) {
                throw new DomainException(__('admin.orders.invalid_transition'));
            }

            $from = $locked->order_status;
            $timestamps = match ($status) {
                'confirmed' => ['confirmed_at' => now()],
                'completed' => ['completed_at' => now()],
                default => [],
            };
            $locked->forceFill(['order_status' => $status, ...$timestamps])->save();
            $history = $locked->statusHistories()->create([
                'from_status' => $from,
                'to_status' => $status,
                'note' => $note,
                'changed_by' => $actor->id,
                'changed_by_type' => 'admin',
            ]);

            return [$locked->refresh(), $from, $history->id];
        });

        $this->emails->orderStatusUpdated($updated, $from, $status, $historyId);

        return $updated;
    }

    public function updateCodPayment(Order $order, string $status, ?string $note, User $actor): Order
    {
        $updated = DB::transaction(function () use ($order, $status, $note, $actor): Order {
            $locked = Order::query()->lockForUpdate()->findOrFail($order->id);
            if ($locked->payment_method !== 'cod') {
                throw new DomainException(__('admin.orders.cod_only'));
            }
            if ($locked->order_status === 'cancelled') {
                throw new DomainException(__('admin.orders.cancelled_payment_locked'));
            }
            if (! in_array($status, $this->allowedPaymentTransitions($locked), true)) {
                throw new DomainException(__('admin.orders.invalid_payment_transition'));
            }

            $from = $locked->payment_status;
            $paidAt = $status === 'paid' ? now() : null;
            $locked->forceFill([
                'payment_status' => $status,
                'paid_at' => $paidAt,
            ])->save();
            $locked->payment()->update(['status' => $status, 'paid_at' => $paidAt]);
            $locked->orderPayments()->update(['payment_status' => $status, 'paid_at' => $paidAt]);
            $locked->paymentHistories()->create([
                'from_status' => $from,
                'to_status' => $status,
                'note' => $note,
                'changed_by' => $actor->id,
            ]);

            return $locked->refresh();
        });

        $this->emails->paymentChanged($updated, null, $status);

        return $updated;
    }

    public function updateFulfillment(Order $order, string $status, ?string $note, User $actor): Order
    {
        return DB::transaction(function () use ($order, $status, $note, $actor): Order {
            $locked = Order::query()->lockForUpdate()->findOrFail($order->id);
            if (! in_array($status, $this->allowedFulfillmentTransitions($locked), true)) {
                throw new DomainException(__('admin.orders.invalid_fulfillment_transition'));
            }

            $from = $locked->fulfillment_status;
            $locked->forceFill(['fulfillment_status' => $status])->save();
            $locked->internalNotes()->create([
                'type' => 'system',
                'note' => __('admin.orders.fulfillment_changed', [
                    'from' => __('admin.orders.fulfillment_'.$from),
                    'to' => __('admin.orders.fulfillment_'.$status),
                ]).($note ? "\n".$note : ''),
                'created_by' => $actor->id,
            ]);

            return $locked->refresh();
        });
    }

    public function cancel(Order $order, string $reason, bool $restock, User $actor): Order
    {
        $cancelled = DB::transaction(function () use ($order, $reason, $restock, $actor): Order {
            $locked = Order::query()->with('orderItems')->lockForUpdate()->findOrFail($order->id);
            if (! $this->canCancel($locked)) {
                throw new DomainException(__('admin.orders.cannot_cancel'));
            }

            if ($restock) {
                $this->restock($locked, $actor);
            }

            $from = $locked->order_status;
            $paymentFrom = $locked->payment_status;
            $locked->forceFill([
                'order_status' => 'cancelled',
                'fulfillment_status' => 'cancelled',
                'cancelled_at' => now(),
                'payment_status' => $locked->payment_status === 'paid' ? 'paid' : 'cancelled',
            ])->save();
            if ($locked->payment_status !== 'paid') {
                $locked->payment()->update(['status' => 'cancelled']);
                $locked->orderPayments()->update(['payment_status' => 'cancelled']);
                $locked->paymentHistories()->create([
                    'from_status' => $paymentFrom,
                    'to_status' => 'cancelled',
                    'note' => $reason,
                    'changed_by' => $actor->id,
                ]);
            }
            $locked->statusHistories()->create([
                'from_status' => $from,
                'to_status' => 'cancelled',
                'note' => $reason,
                'changed_by' => $actor->id,
                'changed_by_type' => 'admin',
            ]);
            $locked->internalNotes()->create([
                'type' => 'system',
                'note' => __('admin.orders.cancel_note', ['reason' => $reason]),
                'created_by' => $actor->id,
            ]);

            return $locked->refresh();
        });

        $this->emails->orderCancelled($cancelled);

        return $cancelled;
    }

    public function addNote(Order $order, string $note, User $actor): Order
    {
        $order->internalNotes()->create(['type' => 'internal', 'note' => $note, 'created_by' => $actor->id]);

        return $order->refresh();
    }

    private function restock(Order $order, User $actor): void
    {
        if ($order->inventory_restocked_at !== null) {
            throw new DomainException(__('admin.orders.already_restocked'));
        }

        foreach ($order->orderItems as $item) {
            if (! $item->product_id) {
                throw new DomainException(__('admin.orders.stock_missing'));
            }

            $stock = InventoryStock::query()
                ->where('product_id', $item->product_id)
                ->when(
                    $item->product_variant_id,
                    fn ($query) => $query->where('product_variant_id', $item->product_variant_id),
                    fn ($query) => $query->whereNull('product_variant_id'),
                )
                ->lockForUpdate()
                ->first();

            if (! $stock) {
                throw new DomainException(__('admin.orders.stock_missing'));
            }

            $before = $stock->quantity;
            $after = $before + $item->quantity;
            $stock->forceFill(['quantity' => $after])->save();
            $stock->inventoryLogs()->create([
                'product_id' => $stock->product_id,
                'product_variant_id' => $stock->product_variant_id,
                'type' => 'cancel_order',
                'quantity_before' => $before,
                'quantity_change' => $item->quantity,
                'quantity_after' => $after,
                'reason' => __('admin.orders.restock_reason', ['order' => $order->order_code]),
                'note' => 'Order cancellation',
                'created_by' => $actor->id,
            ]);
        }

        $order->forceFill(['inventory_restocked_at' => now()])->save();
        $order->internalNotes()->create([
            'type' => 'system',
            'note' => __('admin.orders.restocked_note'),
            'created_by' => $actor->id,
        ]);
    }
}
