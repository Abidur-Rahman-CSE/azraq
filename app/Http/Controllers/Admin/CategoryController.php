<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {
        $filters = [
            'q' => request('q'),
            'status' => request('status'),
            'featured' => request()->boolean('featured'),
            'homepage' => request()->boolean('homepage'),
        ];

        $categories = Category::query()
            ->with('parent')
            ->withCount('products')
            ->when(filled($filters['q']), function (Builder $query) use ($filters): void {
                $query->where(function (Builder $nested) use ($filters): void {
                    $nested
                        ->where('name', 'like', '%'.$filters['q'].'%')
                        ->orWhere('slug', 'like', '%'.$filters['q'].'%');
                });
            })
            ->when(filled($filters['status']), function (Builder $query) use ($filters): void {
                $query->where('is_active', $filters['status'] === 'active');
            })
            ->when($filters['featured'], fn (Builder $query) => $query->where('is_featured', true))
            ->when($filters['homepage'], fn (Builder $query) => $query->where('show_on_homepage', true))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.catalog.categories.index', [
            'categories' => $categories,
            'filters' => $filters,
            'stats' => [
                'missing_images' => Category::query()->where(function (Builder $query): void {
                    $query->whereNull('image_url')->orWhere('image_url', '');
                })->count(),
                'missing_banners' => Category::query()->where(function (Builder $query): void {
                    $query->whereNull('banner_image_url')->orWhere('banner_image_url', '');
                })->count(),
                'empty_categories' => Category::query()->doesntHave('products')->count(),
            ],
        ]);
    }

    public function create()
    {
        $category = new Category(['is_active' => true]);

        return view('admin.catalog.categories.create', $this->formData($category));
    }

    public function store(CategoryRequest $request)
    {
        $category = Category::create($this->categoryPayload($request));
        $this->syncCategoryRelations($category, $request);

        return redirect()->route('admin.catalog.categories.index')->with('status', 'Category created.');
    }

    public function edit(Category $category)
    {
        $category->load('relatedCategories');

        return view('admin.catalog.categories.edit', $this->formData($category));
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $category->update($this->categoryPayload($request, $category));
        $this->syncCategoryRelations($category, $request);

        return redirect()->route('admin.catalog.categories.index')->with('status', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.catalog.categories.index')->with('status', 'Category deleted.');
    }

    private function formData(Category $category): array
    {
        return [
            'category' => $category,
            'parents' => Category::query()
                ->when($category->exists, fn (Builder $query) => $query->whereKeyNot($category->id))
                ->orderBy('name')
                ->get(),
            'relatedCategories' => Category::query()
                ->when($category->exists, fn (Builder $query) => $query->whereKeyNot($category->id))
                ->orderBy('name')
                ->get(),
        ];
    }

    private function categoryPayload(CategoryRequest $request, ?Category $category = null): array
    {
        return [
            ...$request->safe()->except([
                'image_upload',
                'banner_upload',
                'mobile_banner_upload',
                'icon_upload',
                'seo_image_upload',
                'related_category_ids',
            ]),
            'sort_order' => $request->integer('sort_order'),
            'is_active' => $request->boolean('is_active'),
            'is_featured' => $request->boolean('is_featured'),
            'show_on_homepage' => $request->boolean('show_on_homepage'),
            'image_url' => $this->resolveUpload($request->file('image_upload'), $category?->image_url),
            'banner_image_url' => $this->resolveUpload($request->file('banner_upload'), $category?->banner_image_url),
            'mobile_banner_image_url' => $this->resolveUpload($request->file('mobile_banner_upload'), $category?->mobile_banner_image_url),
            'icon_image_url' => $this->resolveUpload($request->file('icon_upload'), $category?->icon_image_url),
            'seo_image_url' => $this->resolveUpload($request->file('seo_image_upload'), $category?->seo_image_url),
        ];
    }

    private function syncCategoryRelations(Category $category, CategoryRequest $request): void
    {
        $category->relatedCategories()->sync($request->input('related_category_ids', []));
    }

    private function resolveUpload(?UploadedFile $file, ?string $current): ?string
    {
        if (! $file instanceof UploadedFile) {
            return $current;
        }

        $path = $file->store('categories', 'public');

        return Storage::url($path);
    }
}
