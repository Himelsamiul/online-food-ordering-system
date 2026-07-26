<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Food;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Price after discount — used for both filtering and sorting so the
     * customer filters on the price they actually pay.
     */
    private const EFFECTIVE_PRICE = 'price - (price * COALESCE(discount, 0) / 100)';

    private const PER_PAGE = 12;

    /**
     * The single menu page: every dish in one grid, narrowed down with
     * category / subcategory / search / price / sort.
     *
     * The same action answers the AJAX calls made when a filter changes,
     * returning just the rendered cards instead of the whole page.
     */
    public function index(Request $request)
    {
        $foods = $this->filteredFoods($request);

        if ($request->ajax()) {
            return response()->json([
                'html'     => view('frontend.partials.food-grid', compact('foods'))->render(),
                'total'    => $foods->total(),
                'has_more' => $foods->hasMorePages(),
            ]);
        }

        $categories = Category::where('status', 1)
            ->with(['subcategories' => fn ($q) => $q->where('status', 1)->orderBy('name')])
            ->withCount(['foods' => fn ($q) => $q->where('foods.status', 1)
                                                 ->where('foods.quantity', '>', 0)])
            ->orderBy('name')
            ->get();

        [$minPrice, $maxPrice] = $this->priceBounds();

        return view('frontend.pages.menu', compact(
            'categories', 'foods', 'minPrice', 'maxPrice'
        ));
    }

    /**
     * Build the filtered, sorted, paginated food query.
     */
    private function filteredFoods(Request $request)
    {
        $query = Food::query()
            ->with('subcategory.category')
            ->where('status', 1)
            ->where('quantity', '>', 0);   // only in-stock items are orderable

        // --- search ---
        if ($request->filled('q')) {
            $term = trim($request->input('q'));

            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%");
            });
        }

        // --- subcategory wins over category, since it is the narrower filter ---
        if ($request->filled('subcategory')) {
            $query->where('subcategory_id', (int) $request->input('subcategory'));
        } elseif ($request->filled('category')) {
            $categoryId = (int) $request->input('category');

            $query->whereHas('subcategory', fn ($q) => $q->where('category_id', $categoryId));
        }

        // --- price range ---
        if ($request->filled('min_price')) {
            $query->whereRaw(self::EFFECTIVE_PRICE . ' >= ?', [(float) $request->input('min_price')]);
        }

        if ($request->filled('max_price')) {
            $query->whereRaw(self::EFFECTIVE_PRICE . ' <= ?', [(float) $request->input('max_price')]);
        }

        // --- sorting ---
        match ($request->input('sort')) {
            'price_low'  => $query->orderByRaw(self::EFFECTIVE_PRICE . ' asc'),
            'price_high' => $query->orderByRaw(self::EFFECTIVE_PRICE . ' desc'),
            'discount'   => $query->orderByDesc('discount'),
            'name'       => $query->orderBy('name'),
            default      => $query->orderByDesc('id'),
        };

        return $query->paginate(self::PER_PAGE)->withQueryString();
    }

    /**
     * Cheapest / dearest dish on the menu — the endpoints of the price slider.
     */
    private function priceBounds(): array
    {
        $bounds = Food::where('status', 1)
            ->where('quantity', '>', 0)
            ->selectRaw('MIN(' . self::EFFECTIVE_PRICE . ') as low, MAX(' . self::EFFECTIVE_PRICE . ') as high')
            ->first();

        $low  = (int) floor($bounds->low ?? 0);
        $high = (int) ceil($bounds->high ?? 0);

        // Keep the slider usable even when the menu is empty or every dish
        // happens to cost the same.
        if ($high <= $low) {
            $high = $low + 100;
        }

        return [$low, $high];
    }

    public function foodDetails(Food $food)
    {
        // optional: ensure only active food
        if ($food->status != 1) {
            abort(404);
        }

        return view('frontend.pages.food-details', compact('food'));
    }

    /**
     * Legacy drill-down URLs. They now fold into the single menu page with
     * the matching filter pre-applied, so old links/bookmarks still work.
     */
    public function show($id)
    {
        return redirect()->route('menu.index', ['category' => $id]);
    }

    public function foods(Subcategory $subcategory)
    {
        return redirect()->route('menu.index', [
            'category'    => $subcategory->category_id,
            'subcategory' => $subcategory->id,
        ]);
    }
}
