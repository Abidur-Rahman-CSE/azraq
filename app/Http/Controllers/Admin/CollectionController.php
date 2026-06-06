<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CollectionRequest;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CollectionController extends Controller
{
    public function index()
    {
        $filters = [
            'q' => request('q'),
            'mode' => request('mode'),
            'status' => request('status'),
            'featured' => request()->boolean('featured'),
        ];

        $collections = Collection::query()
            ->withCount('products')
            ->when(filled($filters['q']), function (Builder $query) use ($filters): void {
                $query->where(function (Builder $nested) use ($filters): void {
                    $nested
                        ->where('name', 'like', '%'.$filters['q'].'%')
                        ->orWhere('slug', 'like', '%'.$filters['q'].'%');
                });
            })
            ->when(filled($filters['mode']), fn (Builder $query) => $query->where('collection_mode', $filters['mode']))
            ->when(filled($filters['status']), fn (Builder $query) => $query->where('is_active', $filters['status'] === 'active'))
            ->when($filters['featured'], fn (Builder $query) => $query->where('is_featured', true))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.catalog.collections.index', [
            'collections' => $collections,
            'filters' => $filters,
            'stats' => [
                'missing_covers' => Collection::query()->where(function (Builder $query): void {
                    $query->whereNull('cover_image_url')->orWhere('cover_image_url', '');
                })->count(),
                'manual_collections' => Collection::query()->where('collection_mode', 'manual')->count(),
                'featured_collections' => Collection::query()->where('is_featured', true)->count(),
            ],
        ]);
    }

    public function create()
    {
        $collection = new Collection(['is_active' => true, 'collection_mode' => 'manual']);

        return view('admin.catalog.collections.create', $this->formData($collection));
    }

    public function store(CollectionRequest $request)
    {
        $collection = Collection::create($this->collectionPayload($request));
        $collection->products()->sync($request->input('product_ids', []));

        return redirect()->route('admin.catalog.collections.index')->with('status', 'Collection created.');
    }

    public function edit(Collection $collection)
    {
        $collection->load('products');

        return view('admin.catalog.collections.edit', $this->formData($collection));
    }

    public function update(CollectionRequest $request, Collection $collection)
    {
        $collection->update($this->collectionPayload($request, $collection));
        $collection->products()->sync($request->input('product_ids', []));

        return redirect()->route('admin.catalog.collections.index')->with('status', 'Collection updated.');
    }

    public function destroy(Collection $collection)
    {
        $collection->delete();

        return redirect()->route('admin.catalog.collections.index')->with('status', 'Collection deleted.');
    }

    private function formData(Collection $collection): array
    {
        return [
            'collection' => $collection,
            'products' => Product::query()
                ->with(['category', 'images', 'personalizationTemplate', 'personalizationMockups'])
                ->orderBy('name')
                ->get(),
        ];
    }

    private function collectionPayload(CollectionRequest $request, ?Collection $collection = null): array
    {
        return [
            ...$request->safe()->except(['cover_image_upload', 'product_ids']),
            'sort_order' => $request->integer('sort_order'),
            'is_active' => $request->boolean('is_active'),
            'is_featured' => $request->boolean('is_featured'),
            'cover_image_url' => $this->resolveUpload($request->file('cover_image_upload'), $collection?->cover_image_url),
        ];
    }

    private function resolveUpload(?UploadedFile $file, ?string $current): ?string
    {
        if (! $file instanceof UploadedFile) {
            return $current;
        }

        $path = $file->store('collections', 'public');

        return Storage::url($path);
    }
}
