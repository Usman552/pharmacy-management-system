<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($s = $request->search) {
            $query->where(fn ($q) => $q->where('name', 'like', "%$s%")->orWhere('sku', 'like', "%$s%")->orWhere('brand', 'like', "%$s%"));
        }
        if ($c = $request->category) $query->where('category_id', $c);
        match ($request->level) {
            'out' => $query->where('stock', 0),
            'low' => $query->where('stock', '>', 0)->whereColumn('stock', '<', 'min_stock_alert'),
            'ok'  => $query->whereColumn('stock', '>=', 'min_stock_alert'),
            default => null,
        };

        $products   = $query->orderBy('stock')->orderBy('name')->paginate(20)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $summary = [
            'out' => Product::where('stock', 0)->count(),
            'low' => Product::where('stock', '>', 0)->whereColumn('stock', '<', 'min_stock_alert')->count(),
            'units' => (int) Product::sum('stock'),
        ];
        $recent = StockMovement::with(['product', 'user'])->latest('id')->take(8)->get();

        return view('admin.inventory.index', compact('products', 'categories', 'summary', 'recent'));
    }

    public function adjust(Request $request, Product $product, StockService $stock)
    {
        $data = $request->validate([
            'mode'  => 'required|in:add,remove,set',
            'qty'   => 'required|integer|min:0|max:1000000',
            'note'  => 'nullable|string|max:200',
        ]);

        try {
            DB::transaction(function () use ($data, $product, $stock) {
                match ($data['mode']) {
                    'add'    => $stock->adjust($product->id, $data['qty'], 'restock', null, $data['note'] ?? null),
                    'remove' => $stock->adjust($product->id, -$data['qty'], 'adjustment', null, $data['note'] ?? null),
                    'set'    => $stock->setLevel($product->id, $data['qty'], $data['note'] ?? null),
                };
            });
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Stock updated for ' . $product->name . '.');
    }

    public function movements(Product $product)
    {
        $movements = $product->hasMany(StockMovement::class)->with('user')->latest('id')->paginate(25);
        return view('admin.inventory.movements', compact('product', 'movements'));
    }
}
