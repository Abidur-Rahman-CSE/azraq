<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MockupRequest;
use App\Models\PersonalizationMockup;
use App\Models\PersonalizationTemplate;
use App\Support\PersonalizationAssetUsage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class MockupController extends Controller
{
    public function index()
    {
        $filters = [
            'q' => request('q'),
            'template_id' => request('template_id'),
            'status' => request('status'),
            'render_mode' => request('render_mode'),
        ];

        $mockups = PersonalizationMockup::query()
            ->with(['template.product', 'map'])
            ->when(filled($filters['q']), function (Builder $query) use ($filters): void {
                $query->where(function (Builder $nested) use ($filters): void {
                    $nested
                        ->where('title', 'like', '%'.$filters['q'].'%')
                        ->orWhere('slug', 'like', '%'.$filters['q'].'%')
                        ->orWhereHas('template', fn (Builder $templateQuery) => $templateQuery->where('name', 'like', '%'.$filters['q'].'%'));
                });
            })
            ->when(filled($filters['template_id']), fn (Builder $query) => $query->where('personalization_template_id', $filters['template_id']))
            ->when(filled($filters['status']), fn (Builder $query) => $query->where('is_active', $filters['status'] === 'active'))
            ->when(filled($filters['render_mode']), fn (Builder $query) => $query->where('render_mode', $filters['render_mode']))
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        return view('admin.mockups.index', [
            'mockups' => $mockups,
            'filters' => $filters,
            'templates' => PersonalizationTemplate::with('product')->orderBy('name')->get(),
            'stats' => [
                'active' => PersonalizationMockup::query()->where('is_active', true)->count(),
                'missing_masks' => PersonalizationMockup::query()->where(function (Builder $query): void {
                    $query->whereNull('mask_image_url')->orWhere('mask_image_url', '');
                })->count(),
                'missing_mappings' => PersonalizationMockup::query()->doesntHave('map')->count(),
                'missing_overlays' => PersonalizationMockup::query()->where(function (Builder $query): void {
                    $query->whereNull('overlay_image_url')->orWhere('overlay_image_url', '');
                })->count(),
            ],
        ]);
    }

    public function create()
    {
        $mockup = new PersonalizationMockup([
            'personalization_template_id' => request('template_id'),
            'render_mode' => 'perspective_quad',
            'sort_order' => (int) PersonalizationMockup::query()->max('sort_order') + 1,
            'is_active' => true,
        ]);

        return view('admin.mockups.create', $this->formData($mockup));
    }

    public function store(MockupRequest $request)
    {
        $mockup = DB::transaction(function () use ($request) {
            $mockup = PersonalizationMockup::create($this->mockupPayload($request));

            $mockup->map()->create($this->mapPayload($request));

            return $mockup;
        });

        return redirect()->route('admin.mockups.edit', $mockup)->with('status', 'Mockup created.');
    }

    public function edit(PersonalizationMockup $mockup)
    {
        $mockup->load(['template.product', 'template.fields', 'template.fonts', 'map']);

        return view('admin.mockups.edit', $this->formData($mockup));
    }

    public function update(MockupRequest $request, PersonalizationMockup $mockup)
    {
        DB::transaction(function () use ($request, $mockup): void {
            $mockup->update($this->mockupPayload($request, $mockup));

            $mockup->map()->updateOrCreate([], $this->mapPayload($request));
        });

        return redirect()->route('admin.mockups.edit', $mockup)->with('status', 'Mockup updated.');
    }

    public function duplicate(PersonalizationMockup $mockup)
    {
        $mockup->load('map');

        $duplicate = DB::transaction(function () use ($mockup) {
            $copy = PersonalizationMockup::create([
                ...collect($mockup->only([
                    'personalization_template_id',
                    'base_image_url',
                    'overlay_image_url',
                    'mask_image_url',
                    'thumb_image_url',
                    'render_mode',
                    'is_active',
                    'notes',
                ]))->all(),
                'title' => $mockup->title.' Copy',
                'slug' => Str::slug($mockup->title.' copy-'.Str::lower(Str::random(5))),
                'sort_order' => ((int) PersonalizationMockup::query()->max('sort_order')) + 1,
            ]);

            if ($mockup->map) {
                $copy->map()->create($mockup->map->only([
                    'map_type',
                    'fit_mode',
                    'top_left_x',
                    'top_left_y',
                    'top_right_x',
                    'top_right_y',
                    'bottom_right_x',
                    'bottom_right_y',
                    'bottom_left_x',
                    'bottom_left_y',
                    'normalized_coordinates',
                    'object_position_x',
                    'object_position_y',
                    'manual_rotation',
                    'shadow_strength',
                    'highlight_strength',
                    'opacity',
                ]));
            }

            return $copy;
        });

        return redirect()->route('admin.mockups.edit', $duplicate)->with('status', 'Mockup duplicated.');
    }

    private function formData(PersonalizationMockup $mockup): array
    {
        $mockup->setRelation('map', $mockup->map ?? $mockup->map()->make([
            'map_type' => 'quad',
            'fit_mode' => 'contain',
            'top_left_x' => 0.20,
            'top_left_y' => 0.18,
            'top_right_x' => 0.80,
            'top_right_y' => 0.18,
            'bottom_right_x' => 0.80,
            'bottom_right_y' => 0.82,
            'bottom_left_x' => 0.20,
            'bottom_left_y' => 0.82,
            'normalized_coordinates' => true,
            'manual_rotation' => 0,
            'shadow_strength' => 0.18,
            'highlight_strength' => 0.12,
            'opacity' => 0.95,
        ]));

        return [
            'mockup' => $mockup,
            'templates' => PersonalizationTemplate::with('product')->orderBy('name')->get(),
        ];
    }

    private function mockupPayload(MockupRequest $request, ?PersonalizationMockup $mockup = null): array
    {
        $title = $request->string('title')->toString();
        $saveMode = $request->input('save_mode', 'published');

        return [
            'personalization_template_id' => $request->filled('personalization_template_id')
                ? (int) $request->input('personalization_template_id')
                : null,
            'title' => $title,
            'slug' => Str::slug($request->input('slug') ?: $title),
            'base_image_url' => $this->resolveUpload($request->file('base_image_upload'), $request->input('base_image_url'), $mockup?->base_image_url, $request->boolean('remove_base_image')),
            'mask_image_url' => $this->resolveUpload($request->file('mask_image_upload'), $request->input('mask_image_url'), $mockup?->mask_image_url, $request->boolean('remove_mask_image')),
            'overlay_image_url' => $this->resolveUpload($request->file('overlay_image_upload'), $request->input('overlay_image_url'), $mockup?->overlay_image_url, $request->boolean('remove_overlay_image')),
            'thumb_image_url' => $this->resolveUpload($request->file('thumb_image_upload'), $request->input('thumb_image_url'), $mockup?->thumb_image_url, $request->boolean('remove_thumb_image')),
            'render_mode' => $request->input('render_mode', 'perspective_quad'),
            'sort_order' => (int) $request->input('sort_order', 0),
            'is_active' => $saveMode === 'draft' ? false : $request->boolean('is_active', true),
            'notes' => $request->input('notes'),
        ];
    }

    private function mapPayload(MockupRequest $request): array
    {
        return [
            'map_type' => $request->input('map.map_type', 'quad'),
            'fit_mode' => $request->input('map.fit_mode', 'contain'),
            'top_left_x' => $request->input('map.top_left_x', 0.20),
            'top_left_y' => $request->input('map.top_left_y', 0.18),
            'top_right_x' => $request->input('map.top_right_x', 0.80),
            'top_right_y' => $request->input('map.top_right_y', 0.18),
            'bottom_right_x' => $request->input('map.bottom_right_x', 0.80),
            'bottom_right_y' => $request->input('map.bottom_right_y', 0.82),
            'bottom_left_x' => $request->input('map.bottom_left_x', 0.20),
            'bottom_left_y' => $request->input('map.bottom_left_y', 0.82),
            'normalized_coordinates' => true,
            'object_position_x' => $request->input('map.object_position_x'),
            'object_position_y' => $request->input('map.object_position_y'),
            'manual_rotation' => $request->input('map.manual_rotation'),
            'shadow_strength' => $request->input('map.shadow_strength'),
            'highlight_strength' => $request->input('map.highlight_strength'),
            'opacity' => $request->input('map.opacity'),
        ];
    }

    private function resolveUpload(?UploadedFile $file, ?string $inputUrl, ?string $currentUrl, bool $remove = false): ?string
    {
        if ($remove) {
            $this->deleteManagedAsset($currentUrl);

            return null;
        }

        if ($file instanceof UploadedFile) {
            $this->deleteManagedAsset($currentUrl);

            return Storage::url($file->store('personalization/mockups', 'public'));
        }

        return $inputUrl ?: $currentUrl;
    }

    private function deleteManagedAsset(?string $url): void
    {
        PersonalizationAssetUsage::deleteManagedAssetIfUnused($url);
    }
}
