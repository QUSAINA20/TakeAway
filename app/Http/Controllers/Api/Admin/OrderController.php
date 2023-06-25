<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        $orders = Order::orderBy('created_at', 'desc')->get();

        if ($orders->isEmpty()) {
            return response()->json(['orders' => []]);
        }

        return response()->json(['orders' => $orders]);
    }



    public function getOrdersForCurrentDay()
    {
        $startOfDay = Carbon::today()->startOfDay();
        $now = Carbon::now();

        $orders = Order::whereBetween('date', [$startOfDay, $now])->get();
        $count = $orders->count();

        if ($count === 0) {
            return response()->json(['message' => 'No orders found for the current day']);
        }

        return response()->json(['today_orders_count' => $count]);
    }




    public function getOrdersForCurrentDayWithCash()
    {
        $startOfDay = Carbon::today()->startOfDay();
        $now = Carbon::now();
        $orders = Order::whereBetween('date', [$startOfDay, $now])->whereIn('pay_method', ['cash'])->get();
        $count = $orders->count();

        return response()->json(['cash_orders_count' => $count]);
    }

    public function getOrdersForCurrentDayWithPoints()
    {
        $startOfDay = Carbon::today()->startOfDay();
        $now = Carbon::now();
        $orders = Order::whereBetween('date', [$startOfDay, $now])->whereIn('pay_method', ['points'])->get();
        $count = $orders->count();

        return response()->json(['points_orders_count' => $count]);
    }

    public function show($id)
    {
        $order = Order::with('products')->findOrFail($id);

        return response()->json(['order' => $order]);
    }


    public function create(Request $request)
    {
        $request->validate([
            'custom_id' => 'required|exists:users,custom_id',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'pay_method' => 'required|in:cash,points',
        ]);

        $user = User::where('custom_id', $request->input('custom_id'))->firstOrFail();
        $userId = $user->id;
        $products = $request->input('products');

        $totalPrice = 0;
        $totalPoints = 0;
        $totalProductsNumber = 0;

        foreach ($products as $productData) {
            $product = Product::findOrFail($productData['product_id']);
            $totalPrice += $product->price * $productData['quantity'];
            $totalPoints += $product->points_price * $productData['quantity'];
            $totalProductsNumber += $productData['quantity'];
        }

        if ($user->points < $totalPoints && $request->input('pay_method') === 'points') {
            return response()->json(['error' => 'Insufficient points'], 400);
        }

        $pointsGift = $request->input('pay_method') === 'cash' ? $this->calculatePointsGift($products) : 0;

        $order = new Order();
        $order->date = now()->format('Y/m/d h:i:s');
        $order->price = $totalPrice;
        $order->pay_method = $request->input('pay_method', 'cash');
        $order->products_number = $totalProductsNumber;
        $order->user_id = $userId;
        $order->save();
        $order->products()->attach($this->extractProductIdsWithQuantities($products));

        if ($request->input('pay_method') === 'cash') {
            $user->points += $pointsGift;
        } else {
            $user->points -= $totalPoints;
        }

        $user->save();

        return response()->json(['message' => 'Order created successfully']);
    }

    private function calculatePointsGift($products)
    {
        $pointsGift = 0;

        foreach ($products as $productData) {
            $product = Product::findOrFail($productData['product_id']);
            $pointsGift += $product->points_gift * $productData['quantity'];
        }

        return $pointsGift;
    }

    private function extractProductIdsWithQuantities($products)
    {
        $productIdsWithQuantities = [];

        foreach ($products as $productData) {
            $productIdsWithQuantities[$productData['product_id']] = ['quantity' => $productData['quantity']];
        }

        return $productIdsWithQuantities;
    }
}
