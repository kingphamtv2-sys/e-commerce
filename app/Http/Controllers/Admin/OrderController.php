<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CancelOrderRequest;
use App\Http\Requests\Admin\StoreOrderNoteRequest;
use App\Http\Requests\Admin\UpdateOrderFulfillmentRequest;
use App\Http\Requests\Admin\UpdateOrderPaymentRequest;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\AdminOrderService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request, AdminOrderService $service): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'order_status' => ['nullable', 'in:pending,confirmed,processing,shipped,completed,cancelled,refunded'],
            'payment_status' => ['nullable', 'in:unpaid,pending,paid,failed,refunded,cancelled'],
            'fulfillment_status' => ['nullable', 'in:unfulfilled,processing,shipped,delivered,cancelled'],
            'payment_method' => ['nullable', 'in:cod,online'],
            'customer_type' => ['nullable', 'in:guest,customer'],
            'coupon_used' => ['nullable', 'in:yes,no'],
            'sort' => ['nullable', 'in:newest,oldest,total_high,total_low'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        return view('admin.orders.index', [
            'orders' => $service->paginate($filters),
            'filters' => $filters,
        ]);
    }

    public function show(Order $order, AdminOrderService $service): View
    {
        $this->loadOrder($order);

        return view('admin.orders.show', compact('order', 'service'));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order, AdminOrderService $service): JsonResponse
    {
        return $this->mutate($order, fn () => $service->updateStatus(
            $order,
            $request->validated('status'),
            $request->validated('note'),
            $request->user(),
        ), $service, __('admin.orders.status_updated'));
    }

    public function updatePayment(UpdateOrderPaymentRequest $request, Order $order, AdminOrderService $service): JsonResponse
    {
        return $this->mutate($order, fn () => $service->updateCodPayment(
            $order,
            $request->validated('payment_status'),
            $request->validated('note'),
            $request->user(),
        ), $service, __('admin.orders.payment_updated'));
    }

    public function updateFulfillment(UpdateOrderFulfillmentRequest $request, Order $order, AdminOrderService $service): JsonResponse
    {
        return $this->mutate($order, fn () => $service->updateFulfillment(
            $order,
            $request->validated('fulfillment_status'),
            $request->validated('note'),
            $request->user(),
        ), $service, __('admin.orders.fulfillment_updated'));
    }

    public function markPaid(Request $request, Order $order, AdminOrderService $service): JsonResponse
    {
        return $this->mutate(
            $order,
            fn () => $service->updateCodPayment($order, 'paid', __('admin.orders.marked_paid'), $request->user()),
            $service,
            __('admin.orders.marked_paid'),
        );
    }

    public function cancel(CancelOrderRequest $request, Order $order, AdminOrderService $service): JsonResponse
    {
        return $this->mutate($order, fn () => $service->cancel(
            $order,
            $request->validated('reason'),
            $request->boolean('restock'),
            $request->user(),
        ), $service, __('admin.orders.cancelled'));
    }

    public function storeNote(StoreOrderNoteRequest $request, Order $order, AdminOrderService $service): JsonResponse
    {
        return $this->mutate(
            $order,
            fn () => $service->addNote($order, $request->validated('note'), $request->user()),
            $service,
            __('admin.orders.note_added'),
        );
    }

    private function mutate(Order $order, callable $callback, AdminOrderService $service, string $message): JsonResponse
    {
        try {
            $callback();
        } catch (DomainException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
                'errors' => ['order' => [$exception->getMessage()]],
            ], 422);
        }

        $this->loadOrder($order);

        return response()->json([
            'success' => true,
            'message' => $message,
            'order_status' => $order->order_status,
            'payment_status' => $order->payment_status,
            'fulfillment_status' => $order->fulfillment_status,
            'status_html' => view('admin.orders._status-summary', compact('order'))->render(),
            'management_html' => view('admin.orders._management', compact('order', 'service'))->render(),
            'timeline_html' => view('admin.orders._timeline', compact('order'))->render(),
        ]);
    }

    private function loadOrder(Order $order): void
    {
        $order->refresh()->load([
            'orderItems',
            'orderAddresses',
            'orderPayments',
            'payment',
            'statusHistories.changedBy',
            'paymentHistories.changedBy',
            'paymentTransactions',
            'internalNotes.createdBy',
        ]);
    }
}
