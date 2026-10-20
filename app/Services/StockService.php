<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use RuntimeException;

/**
 * Every stock change goes through here so it is locked, validated and logged.
 * Call inside a DB::transaction().
 */
class StockService
{
    public function adjust(int $productId, int $change, string $reason, ?string $reference = null, ?string $note = null): Product
    {
        $product = Product::whereKey($productId)->lockForUpdate()->firstOrFail();
        $balance = $product->stock + $change;

        if ($balance < 0) {
            throw new RuntimeException("Not enough stock for {$product->name}. Only {$product->stock} left.");
        }

        $product->update(['stock' => $balance]);

        StockMovement::create([
            'product_id' => $product->id,
            'user_id'    => auth()->id(),
            'change'     => $change,
            'balance'    => $balance,
            'reason'     => $reason,
            'reference'  => $reference,
            'note'       => $note,
        ]);

        return $product;
    }

    /** Set an absolute stock figure (used by the admin inventory screen). */
    public function setLevel(int $productId, int $newStock, ?string $note = null): Product
    {
        $current = Product::whereKey($productId)->lockForUpdate()->value('stock');
        return $this->adjust($productId, $newStock - (int) $current, 'adjustment', null, $note);
    }
}
