<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\AuctionLot;   
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Tampilkan daftar produk.
     */
    public function index(Request $request)
    {
        $query = Product::query()
            ->withCount(['auctionLots'])
            ->when($request->search, function ($q, $search) {
                $q->where(function ($x) use ($search) {
                    $x->where('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->when($request->brand, fn($q, $b) => $q->where('brand', $b))
            ->when($request->condition, fn($q, $c) => $q->where('condition', $c))
            ->when($request->year_min, fn($q, $y) => $q->where('year', '>=', $y))
            ->when($request->year_max, fn($q, $y) => $q->where('year', '<=', $y))
            ->when($request->category, function ($q, $cat) {
                $q->whereRaw('FIND_IN_SET(?, category)', [$cat]);
            });

        // sorting
        $sort = $request->get('sort', 'newest');
        $query = match ($sort) {
            'brand_asc'  => $query->orderBy('brand')->orderBy('model'),
            'brand_desc' => $query->orderByDesc('brand')->orderByDesc('model'),
            'lots_desc'  => $query->orderByDesc('auction_lots_count'),
            default      => $query->latest(),
        };

        //pagination
        $allowed = [10,25,50];
        $perPage = (int)($request->get('per', 10));
        if (!in_array($perPage, $allowed, true)) { $perPage = 10; }
        $products = $query->paginate($perPage)->withQueryString();

        $brands = Product::select('brand')->distinct()->pluck('brand')->sort()->values();
        $totalProducts = Product::count();
        $categories = [
            'Dress Watch',
            'Diver Watch',
            'Chronograph Watch',
            'Pilot Watch',
            'Field Watch',
        ];

        return view('admin.products.index', compact(
            'products', 'brands', 'sort', 'perPage', 'categories', 'totalProducts'
        ));
    }

    /**
     * Simpan produk baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand'        => 'required|string|max:255',
            'model'        => 'required|string|max:255',
            'category'     => 'nullable|array',
            'category.*'   => 'string|max:255',
            'year'         => 'nullable|integer|min:1900|max:' . now()->year,
            'condition'    => 'required|in:NEW,USED',
            'description'  => 'nullable|string',
            'weight_grams' => 'required|integer|min:0',
            'images'       => 'nullable|array|max:10',
            'images.*'     => 'image|mimes:jpg,jpeg,png,webp|max:1024',
        ]);

        if (isset($validated['category']) && is_array($validated['category'])) {
            $validated['category'] = implode(',', $validated['category']); // misal disimpan "Diver,Chronograph"
        }

        DB::transaction(function () use ($request, $validated, &$product) {
            $product = Product::create($validated);

            // simpan file gambar jika ada
            $files = $request->file('images', []);
            $files = array_values($files); // reindex 0..n-1

            foreach ($files as $index => $file) {
                $orig = $file->getClientOriginalName();
                $base = pathinfo($orig, PATHINFO_FILENAME);
                $ext  = $file->getClientOriginalExtension();

                $ts       = now()->format('YmdHis');
                $safeBase = \Illuminate\Support\Str::slug($base, '-');
                $filename = "{$ts}_{$product->id}_{$safeBase}.{$ext}";

                $file->storeAs('products', $filename, 'public');

                $product->images()->create([
                    'filename'   => $filename,
                    'is_primary' => $index === 0,   // ← pasti 1 untuk gambar pertama
                    'sort_order' => $index + 1,
                ]);
            }
        });

        return redirect()
            ->route('products.index')
            ->with('success', "Produk '{$product->brand} {$product->model}' berhasil ditambahkan.");
    }

    /**
     * Update produk yang sudah ada.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'brand'        => 'required|string|max:255',
            'model'        => 'required|string|max:255',
            'category'     => 'nullable|array',
            'category.*'   => 'string|max:255',
            'year'         => 'nullable|integer|min:1900|max:' . now()->year,
            'condition'    => 'required|in:NEW,USED',
            'description'  => 'nullable|string',
            'weight_grams' => 'required|integer|min:0',
        ]);

        if (isset($validated['category']) && is_array($validated['category'])) {
            $validated['category'] = implode(',', $validated['category']);
        }

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', "Produk '{$product->brand} {$product->model}' berhasil diperbarui.");
    }

    /**
     * Hapus produk.
     */
    public function destroy(Product $product)
    {
        $now = now();

        $locked = $product->auctionLots()
            ->whereNull('cancelled_at')
            ->where(function ($q) use ($now) {
                $q->where('start_at', '>', $now)          // SCHEDULED
                ->orWhere(function ($q2) use ($now) {   // ACTIVE
                    $q2->where('start_at', '<=', $now)
                        ->where('end_at', '>=', $now);
                });
            })
            ->exists();

        if ($locked) {
            return back()->with('error', 'Produk ini dipakai lot yang masih aktif/terjadwal.');
        }

        foreach ($product->images as $img) {
            Storage::disk('public')->delete('products/'.$img->filename);
            $img->delete();
        }

        $product->delete();

        return redirect()->route('products.index')->with('success','Produk beserta gambar berhasil dihapus.');
    }

    /**
     * Sinkronisasi urutan dan status gambar.
     */
    public function addImage(Product $product, Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:1024',
        ]);

        $file = $request->file('image');

        $orig = $file->getClientOriginalName();
        $base = pathinfo($orig, PATHINFO_FILENAME);
        $ext  = $file->getClientOriginalExtension();

        $ts       = now()->format('YmdHis');
        $safeBase = \Illuminate\Support\Str::slug($base, '-');
        $filename = "{$ts}_{$product->id}_{$safeBase}.{$ext}";

        $file->storeAs('products', $filename, 'public');

        $nextOrder = ($product->images()->max('sort_order') ?? 0) + 1;

        $product->images()->create([
            'filename'   => $filename,   // ← filename saja
            'sort_order' => $nextOrder,
            'is_primary' => !$product->images()->exists(),
        ]);

        return back()->with('success', 'Gambar ditambahkan.');
    }

    public function syncImages(Product $product, Request $req)
    {
        $order   = collect(json_decode($req->order,   true));
        $removed = collect(json_decode($req->removed, true));

        DB::transaction(function () use ($product, $order, $removed, $req) {

            // 1) hapus yang ditandai
            if ($removed->isNotEmpty()) {
                // hapus file fisik + row
                $toDel = $product->images()->whereIn('id', $removed)->get();
                foreach ($toDel as $img) {
                    Storage::disk('public')->delete('products/'.$img->filename);
                }
                $product->images()->whereIn('id', $removed)->delete();
            }

            // 2) simpan upload baru → buat filename custom → insert → catat peta 'new-*' → id baru
            $mapNew = []; // ['new-xxx' => 123]
            if ($req->hasFile('uploads')) {
                foreach ($req->file('uploads') as $file) {
                    $orig = $file->getClientOriginalName();
                    $base = pathinfo($orig, PATHINFO_FILENAME);
                    $ext  = $file->getClientOriginalExtension();

                    $ts       = now()->format('YmdHis');
                    $safeBase = \Illuminate\Support\Str::slug($base, '-');
                    $filename = "{$ts}_{$product->id}_{$safeBase}.{$ext}";

                    $file->storeAs('products', $filename, 'public');

                    $new = $product->images()->create(['filename' => $filename]);

                    // urutan 'new-*' mengikuti posisi pertama ia muncul di $order
                    $nextPseudo = $order->first(fn($v) => str_starts_with($v,'new-') && !isset($mapNew[$v]));
                    $mapNew[$nextPseudo] = $new->id;
                }
            }

            // 3) ganti pseudo di $order (new-*) → id asli
            $order = $order->map(fn($v) => $mapNew[$v] ?? $v)->values();

            // 4) tulis ulang urutan + primary
            foreach ($order as $i => $id) {
                $product->images()->where('id', $id)->update([
                    'sort_order' => $i + 1,
                    'is_primary' => $i === 0,
                ]);
            }
        });

        return back()->with('success', 'Gambar disimpan.');
    }

    public function show(Product $product)
    {
        $product->load(['images' => function ($q) {
            $q->orderBy('sort_order');
        }, 'auctionLots']);

        $categories = $product->category
            ? array_map('trim', explode(',', $product->category))
            : [];

        return view('admin.products.show', compact('product', 'categories'));
    }

}
