<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class AdminWebDiscountController extends Controller
{
    /**
     * Show all products with their discount info.
     */
    public function index(Request $request)
    {
        $query = Product::with(['images', 'variants.discounts', 'allDiscounts'])->where('status', '!=', 'inactive');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.discounts', compact('products'));
    }

    /**
     * Save discounts for a product AND its variants (all-in-one).
     * Receives: { product_discount: %, starts_at, ends_at, is_active, variants: [{id, pct},...] }
     */
    public function update(Request $request, $productId)
    {
        $validated = $request->validate([
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'starts_at'           => 'nullable|date',
            'ends_at'             => 'nullable|date|after_or_equal:starts_at',
            'is_active'           => 'nullable|boolean',
            'variant_discounts'   => 'nullable|array',
            'variant_discounts.*.variant_id'           => 'required|integer|exists:product_variants,id',
            'variant_discounts.*.discount_percentage'  => 'required|numeric|min:0|max:100',
        ]);

        $product = Product::findOrFail($productId);

        // ── Product-level discount (product_variant_id = null) ──
        $productPct = (float)($validated['discount_percentage'] ?? 0);

        // Remove old product-level discount
        $product->allDiscounts()->whereNull('product_variant_id')->delete();

        $startsAt = $validated['starts_at'] ? \Carbon\Carbon::parse($validated['starts_at'])->startOfDay() : null;
        $endsAt   = $validated['ends_at'] ? \Carbon\Carbon::parse($validated['ends_at'])->endOfDay() : null;

        if ($productPct > 0) {
            $product->allDiscounts()->create([
                'product_variant_id'  => null,
                'discount_percentage' => $productPct,
                'starts_at'           => $startsAt,
                'ends_at'             => $endsAt,
                'is_active'           => $validated['is_active'] ?? true,
            ]);
        }

        // ── Variant-level discounts ──
        if (!empty($validated['variant_discounts'])) {
            foreach ($validated['variant_discounts'] as $vd) {
                $variantId  = (int) $vd['variant_id'];
                $variantPct = (float) $vd['discount_percentage'];

                // Remove old discount for this variant
                Discount::where('product_id', $product->id)
                    ->where('product_variant_id', $variantId)
                    ->delete();

                if ($variantPct > 0) {
                    Discount::create([
                        'product_id'          => $product->id,
                        'product_variant_id'  => $variantId,
                        'discount_percentage' => $variantPct,
                        'starts_at'           => $startsAt,
                        'ends_at'             => $endsAt,
                        'is_active'           => $validated['is_active'] ?? true,
                    ]);
                }
            }
        }

        // Keep product original_price in sync
        if ($productPct > 0) {
            $product->update(['original_price' => $product->price]);
        } else {
            $product->update(['original_price' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Diskon berhasil disimpan.',
        ]);
    }

    /**
     * Toggle is_active for all discounts of a product.
     */
    public function toggle(Request $request, $productId)
    {
        $product   = Product::findOrFail($productId);
        $discounts = $product->allDiscounts();

        if ($discounts->count() === 0) {
            return response()->json(['success' => false, 'message' => 'Belum ada diskon.'], 404);
        }

        $first     = $discounts->first();
        $newStatus = !$first->is_active;
        $discounts->update(['is_active' => $newStatus]);

        return response()->json(['success' => true, 'is_active' => $newStatus]);
    }

    /**
     * Delete ALL discounts for a product (product + variant level).
     */
    public function destroy($productId)
    {
        $product = Product::findOrFail($productId);
        $product->allDiscounts()->delete();
        $product->update(['original_price' => null]);

        return response()->json(['success' => true, 'message' => 'Semua diskon dihapus.']);
    }

    /**
     * Bulk update product-level discounts for multiple products.
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'product_ids'         => 'required|array',
            'product_ids.*'       => 'integer|exists:products,id',
            'discount_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $products = Product::whereIn('id', $validated['product_ids'])->get();

        foreach ($products as $product) {
            $product->allDiscounts()->whereNull('product_variant_id')->delete();

            if ($validated['discount_percentage'] > 0) {
                $product->allDiscounts()->create([
                    'product_variant_id'  => null,
                    'discount_percentage' => $validated['discount_percentage'],
                    'is_active'           => true,
                ]);
                $product->update(['original_price' => $product->price]);
            } else {
                $product->update(['original_price' => null]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Diskon massal berhasil diterapkan.']);
    }
}
