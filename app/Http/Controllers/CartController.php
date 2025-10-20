<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Core\Services\CartService;
use App\Core\Entities\Cart;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CartController extends Controller
{
    protected CartService $service;
    use AuthorizesRequests; 
    public function __construct(CartService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        // Kullanıcının sepetini bul ve yetki kontrolü yap
        $cart = Cart::firstOrCreate(['user_id' => auth()->id()]);
        $this->authorize('view', $cart);

        return response()->json($this->service->getCart());
    }

    public function store(Request $request)
    {
        $cart = Cart::firstOrCreate(['user_id' => auth()->id()]);
        $this->authorize('update', $cart);

        // validation yok: ham input
        $data = [
            'product_id' => (int) $request->input('product_id'),
            'quantity'   => (int) $request->input('quantity'),
        ];

        return response()->json($this->service->addItem($data), 201);
    }

    public function update(Request $request, $id)
    {
        $cart = Cart::firstOrCreate(['user_id' => auth()->id()]);
        $this->authorize('update', $cart);

        $qty = (int) $request->input('quantity');
        return response()->json($this->service->updateItem((int) $id, $qty));
    }

    public function destroy($id)
    {
        $cart = Cart::firstOrCreate(['user_id' => auth()->id()]);
        $this->authorize('update', $cart);

        return response()->json($this->service->removeItem((int) $id));
    }

    public function clear()
    {
        $cart = Cart::firstOrCreate(['user_id' => auth()->id()]);
        $this->authorize('delete', $cart);

        return response()->json($this->service->clear());
    }
}
