<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $items = $this->getCartItems();

        if ($items === []) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        return view('storefront.checkout', compact('items'));
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $cart = session()->get('cart', []);

        if ($cart === []) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        $items = [];
        $subtotal = 0;

        foreach ($cart as $productId => $cartItem) {
            $product = Product::lockForUpdate()->find($productId);
            $quantity = (int) ($cartItem['quantity'] ?? 0);

            if (! $product || ! $product->is_active) {
                throw ValidationException::withMessages([
                    'checkout' => ['One or more products are no longer available.'],
                ]);
            }

            if ($quantity <= 0 || $quantity > $product->stock_quantity) {
                throw ValidationException::withMessages([
                    'checkout' => ['Insufficient stock for ' . $product->name . '.'],
                ]);
            }

            $unitPrice = $product->current_price;
            $subtotal += $unitPrice * $quantity;
            $items[] = [
                'product' => $product,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
            ];
        }

        $validated = $request->validated();
        $total = $subtotal;

        $order = DB::transaction(function () use ($validated, $items, $total, $subtotal) {
            $order = Order::create([
                'user_id' => auth()->id(),
                'customer_name' => $validated['customer_name'],
                'mobile_number' => $validated['mobile_number'],
                'email' => $validated['email'] ?? null,
                'delivery_address' => $validated['delivery_address'],
                'district_or_city' => $validated['district_or_city'],
                'order_notes' => $validated['order_notes'] ?? null,
                'subtotal' => $subtotal,
                'total' => $total,
                'status' => 'pending',
            ]);

            foreach ($items as $item) {
                $product = $item['product'];
                $product->decrement('stock_quantity', $item['quantity']);

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['unit_price'] * $item['quantity'],
                ]);
            }

            session()->forget('cart');

            return $order;
        });

        return redirect()->route('checkout.success', ['orderNumber' => $order->order_number])->with('success', 'Order placed successfully.');
    }

    public function success(string $orderNumber): View
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        return view('storefront.checkout-success', compact('order'));
    }

    protected function getCartItems(): array
    {
        $cart = session()->get('cart', []);
        $items = [];

        foreach ($cart as $productId => $item) {
            $product = Product::find($productId);

            if (! $product || ! $product->is_active) {
                continue;
            }

            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            $items[] = [
                'product' => $product,
                'quantity' => $quantity,
                'line_total' => $quantity * $product->current_price,
            ];
        }

        return $items;
    }
}
