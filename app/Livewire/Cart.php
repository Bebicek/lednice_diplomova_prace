<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Debt;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client')]
class Cart extends Component
{
    public array $cart = [];

    public function mount()
    {
        $this->cart = session()->get('cart', []);
    }

    public function increment(int $commodityId)
    {
        if (isset($this->cart[$commodityId])) {

            $currentFridgeQuantity = Stock::where('commodity_id', $commodityId)->where('location', 'fridge')->value('quantity');

            if ($this->cart[$commodityId]['quantity'] + 1 > $currentFridgeQuantity) {
                session()->flash('error', 'Daný produkt už není v lednici dostupný');
                return;
            }
                $this->cart[$commodityId]['quantity']++;
                session()->put('cart', $this->cart);
                $this->dispatch('cart-updated');
        }
    }

    public function decrement(int $commodityId)
    {
        if (isset($this->cart[$commodityId])) {
            if ($this->cart[$commodityId]['quantity'] > 1) {
                $this->cart[$commodityId]['quantity']--;
            } else {
                unset($this->cart[$commodityId]);
            }
            session()->put('cart', $this->cart);
            $this->dispatch('cart-updated');
        }
    }

    public function remove(int $commodityId)
    {
        unset($this->cart[$commodityId]);
        session()->put('cart', $this->cart);
        $this->dispatch('cart-updated');
    }

    public function clearCart()
    {
        $this->cart = [];
        session()->forget('cart');
        $this->dispatch('cart-updated');
    }

    public function getTotal(): int
    {
        return array_reduce($this->cart, function ($carry, $item) {
            return $carry + ($item['price'] * $item['quantity']);
        }, 0);
    }

    public function checkout()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Košík je prázdný.');
            return;
        }

        try {
            DB::transaction(function () {
                $order = Order::create([
                    'user_id' => auth()->id(),
                    'total_amount' => $this->getTotal(),
                ]);

                foreach ($this->cart as $item) {
                    $stock = Stock::where('commodity_id', $item['id'])
                        ->where('location', 'fridge')
                        ->lockForUpdate()
                        ->first();

                    if (!$stock || $stock->quantity < $item['quantity']) {
                        throw new \Exception('Zboží "' . $item['name'] . '" už není v dostatečném množství.');
                    }

                    OrderItem::create([
                        'order_id' => $order->id,
                        'commodity_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);

                    $stock->decrement('quantity', $item['quantity']);

                    StockMovement::create([
                        'commodity_id' => $item['id'],
                        'user_id' => auth()->id(),
                        'type' => 'sale',
                        'quantity' => -$item['quantity'],
                        'note' => "Prodej - objednávka #{$order->id}",
                    ]);
                }

                // Create the debt record
                Debt::create([
                    'user_id' => auth()->id(),
                    'creditor_id' => null, // System debt
                    'order_id' => $order->id,
                    'amount' => $this->getTotal(),
                    'is_paid' => false,
                ]);

                $this->clearCart();
            });
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            return;
        }

        session()->flash('success', 'Objednávka byla vytvořena!');
        return redirect()->route('my-debts');
    }

    public function render()
    {
        return view('livewire.cart', [
            'total' => $this->getTotal(),
        ]);
    }
}
