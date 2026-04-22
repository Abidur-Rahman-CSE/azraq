@php($isEdit = $mockup->exists)
@php($map = $mockup->map)
@php($editorMap = $editorMap ?? ($map?->toArray() ?? []))
@php($template = $mockup->template)
<form
    method="POST"
    action="{{ $isEdit ? route('admin.mockups.update', $mockup) : route('admin.mockups.store') }}"
    enctype="multipart/form-data"
    class="space-y-8"
    x-data="{
        zoom: 1,
        stageAspectRatio: 4 / 3,
        previewOpacity: {{ (float) old('map.opacity', $map->opacity ?? 0.95) }},
        shadowStrength: {{ (float) old('map.shadow_strength', $map->shadow_strength ?? 0.18) }},
        highlightStrength: {{ (float) old('map.highlight_strength', $map->highlight_strength ?? 0.12) }},
        zoneColor: '#dc2626',
        selectedHandle: 'top_left',
        draggingHandle: null,
        isPanning: false,
        panX: 0,
        panY: 0,
        lastPanPoint: null,
        isDirty: false,
        activeTemplateId: '{{ (string) old('personalization_template_id', $mockup->personalization_template_id) }}',
        fitMode: @js(old('map.fit_mode', $map->fit_mode ?? 'stretch')),
        objectPositionX: {{ (float) old('map.object_position_x', $map->object_position_x ?? 0.5) }},
        objectPositionY: {{ (float) old('map.object_position_y', $map->object_position_y ?? 0.5) }},
        manualRotation: {{ (float) old('map.manual_rotation', $map->manual_rotation ?? 0) }},
        templateMeta: @js($templates->mapWithKeys(fn ($item) => [
            (string) $item->id => [
                'name' => $item->name,
                'ratio_width' => $item->export_ratio_width ?? 9,
                'ratio_height' => $item->export_ratio_height ?? 13,
                'preview_url' => $item->preview_image_url ?: $item->base_template_url,
                'fields' => $item->fields->map(fn ($field) => [
                    'key' => $field->field_key,
                    'label' => $field->label,
                    'placeholder' => $field->placeholder,
                    'x' => (float) $field->position_x,
                    'y' => (float) $field->position_y,
                    'width' => (float) $field->width,
                    'height' => (float) $field->height,
                    'rotation' => (float) $field->rotation,
                    'align' => $field->text_align,
                    'color' => $field->text_color,
                    'line_height' => (float) $field->line_height,
                    'letter_spacing' => (float) $field->letter_spacing,
                    'font_size_min' => (int) $field->font_size_min,
                    'font_size_max' => (int) $field->font_size_max,
                    'z_index' => (int) ($field->z_index ?? 1),
                ])->values()->all(),
            ],
        ])),
        baseImageUrl: @js(old('base_image_url', $mockup->base_image_url)),
        naturalBaseWidth: {{ (int) old('image_width', $mockup->image_width ?? 0) }},
        naturalBaseHeight: {{ (int) old('image_height', $mockup->image_height ?? 0) }},
        maskImageUrl: @js(old('mask_image_url', $mockup->mask_image_url)),
        overlayImageUrl: @js(old('overlay_image_url', $mockup->overlay_image_url)),
        thumbImageUrl: @js(old('thumb_image_url', $mockup->thumb_image_url)),
        removeBaseImage: {{ old('remove_base_image', 0) ? 'true' : 'false' }},
        removeMaskImage: {{ old('remove_mask_image', 0) ? 'true' : 'false' }},
        removeOverlayImage: {{ old('remove_overlay_image', 0) ? 'true' : 'false' }},
        removeThumbImage: {{ old('remove_thumb_image', 0) ? 'true' : 'false' }},
        defaults: {
            top_left: { x: {{ (float) old('map.top_left_x', data_get($editorMap, 'top_left_x', 0.20)) }}, y: {{ (float) old('map.top_left_y', data_get($editorMap, 'top_left_y', 0.18)) }} },
            top_right: { x: {{ (float) old('map.top_right_x', data_get($editorMap, 'top_right_x', 0.80)) }}, y: {{ (float) old('map.top_right_y', data_get($editorMap, 'top_right_y', 0.18)) }} },
            bottom_right: { x: {{ (float) old('map.bottom_right_x', data_get($editorMap, 'bottom_right_x', 0.80)) }}, y: {{ (float) old('map.bottom_right_y', data_get($editorMap, 'bottom_right_y', 0.82)) }} },
            bottom_left: { x: {{ (float) old('map.bottom_left_x', data_get($editorMap, 'bottom_left_x', 0.20)) }}, y: {{ (float) old('map.bottom_left_y', data_get($editorMap, 'bottom_left_y', 0.82)) }} },
        },
        points: {
            top_left: { x: {{ (float) old('map.top_left_x', data_get($editorMap, 'top_left_x', 0.20)) }}, y: {{ (float) old('map.top_left_y', data_get($editorMap, 'top_left_y', 0.18)) }} },
            top_right: { x: {{ (float) old('map.top_right_x', data_get($editorMap, 'top_right_x', 0.80)) }}, y: {{ (float) old('map.top_right_y', data_get($editorMap, 'top_right_y', 0.18)) }} },
            bottom_right: { x: {{ (float) old('map.bottom_right_x', data_get($editorMap, 'bottom_right_x', 0.80)) }}, y: {{ (float) old('map.bottom_right_y', data_get($editorMap, 'bottom_right_y', 0.82)) }} },
            bottom_left: { x: {{ (float) old('map.bottom_left_x', data_get($editorMap, 'bottom_left_x', 0.20)) }}, y: {{ (float) old('map.bottom_left_y', data_get($editorMap, 'bottom_left_y', 0.82)) }} },
        },
        cornerMeta: {
            top_left: {
                label: 'Top left',
                fill: '#780000',
                soft: 'rgba(120,0,0,0.10)',
                border: 'rgba(120,0,0,0.24)',
                ring: 'rgba(120,0,0,0.22)',
            },
            top_right: {
                label: 'Top right',
                fill: '#003049',
                soft: 'rgba(0,48,73,0.10)',
                border: 'rgba(0,48,73,0.22)',
                ring: 'rgba(0,48,73,0.18)',
            },
            bottom_right: {
                label: 'Bottom right',
                fill: '#C1121F',
                soft: 'rgba(193,18,31,0.10)',
                border: 'rgba(193,18,31,0.22)',
                ring: 'rgba(193,18,31,0.20)',
            },
            bottom_left: {
                label: 'Bottom left',
                fill: '#669BBC',
                soft: 'rgba(102,155,188,0.18)',
                border: 'rgba(102,155,188,0.28)',
                ring: 'rgba(102,155,188,0.22)',
            },
        },
        clamp(value) {
            return Math.min(1, Math.max(0, value));
        },
        markDirty() {
            this.isDirty = true;
        },
        ratioMeta() {
            return this.templateMeta[this.activeTemplateId] ?? { ratio_width: 9, ratio_height: 13, name: 'Assigned template' };
        },
        ratioLabel() {
            const meta = this.ratioMeta();
            return `${meta.ratio_width}:${meta.ratio_height}`;
        },
        hexToRgb(hex) {
            const normalized = `${hex || ''}`.replace('#', '').trim();

            if (normalized.length !== 6) {
                return { r: 220, g: 38, b: 38 };
            }

            return {
                r: Number.parseInt(normalized.slice(0, 2), 16),
                g: Number.parseInt(normalized.slice(2, 4), 16),
                b: Number.parseInt(normalized.slice(4, 6), 16),
            };
        },
        bounds() {
            const xValues = [this.points.top_left.x, this.points.top_right.x, this.points.bottom_right.x, this.points.bottom_left.x];
            const yValues = [this.points.top_left.y, this.points.top_right.y, this.points.bottom_right.y, this.points.bottom_left.y];
            const minX = Math.min(...xValues);
            const maxX = Math.max(...xValues);
            const minY = Math.min(...yValues);
            const maxY = Math.max(...yValues);

            return {
                left: minX,
                top: minY,
                width: Math.max(0.12, maxX - minX),
                height: Math.max(0.12, maxY - minY),
            };
        },
        imageBounds() {
            const stage = this.$refs.stage;

            if (! stage) {
                return { left: 0, top: 0, width: 1, height: 1 };
            }

            const stageWidth = stage.clientWidth || 1;
            const stageHeight = stage.clientHeight || 1;
            const naturalWidth = this.naturalBaseWidth || stageWidth;
            const naturalHeight = this.naturalBaseHeight || stageHeight;
            const imageRatio = naturalWidth / Math.max(1, naturalHeight);
            const stageRatio = stageWidth / Math.max(1, stageHeight);

            let width = stageWidth;
            let height = stageHeight;
            let left = 0;
            let top = 0;

            if (imageRatio > stageRatio) {
                width = stageWidth;
                height = width / imageRatio;
                top = (stageHeight - height) / 2;
            } else {
                height = stageHeight;
                width = height * imageRatio;
                left = (stageWidth - width) / 2;
            }

            return { left, top, width, height };
        },
        pointStyle(key) {
            const bounds = this.imageBounds();
            const x = bounds.left + (this.points[key].x * bounds.width);
            const y = bounds.top + (this.points[key].y * bounds.height);

            return `left:${x - 15}px; top:${y - 15}px;`;
        },
        pointToneStyle(key) {
            const meta = this.cornerMeta[key];
            return `background:rgba(255,255,255,0.92); border:3px solid ${meta.fill}; box-shadow: 0 12px 26px ${meta.ring};`;
        },
        pointCenterStyle(key) {
            const meta = this.cornerMeta[key];
            return `background:${meta.fill}; box-shadow: 0 0 0 2px rgba(255,255,255,0.98);`;
        },
        cornerBadgeStyle(key) {
            const meta = this.cornerMeta[key];
            return `background:${meta.fill}; color:#ffffff; border:1px solid ${meta.fill};`;
        },
        cornerCardStyle(key) {
            const meta = this.cornerMeta[key];
            return this.selectedHandle === key
                ? `background: linear-gradient(180deg, ${meta.soft}, rgba(255,255,255,0.98)); border: 2px solid ${meta.border}; box-shadow: 0 18px 35px ${meta.ring};`
                : `background: rgba(255,255,255,0.92); border: 2px solid ${meta.border};`;
        },
        polygon() {
            return `${this.points.top_left.x * 100}% ${this.points.top_left.y * 100}%, ${this.points.top_right.x * 100}% ${this.points.top_right.y * 100}%, ${this.points.bottom_right.x * 100}% ${this.points.bottom_right.y * 100}%, ${this.points.bottom_left.x * 100}% ${this.points.bottom_left.y * 100}%`;
        },
        localPolygon() {
            const bounds = this.bounds();
            const normalizeX = (value) => ((value - bounds.left) / bounds.width) * 100;
            const normalizeY = (value) => ((value - bounds.top) / bounds.height) * 100;

            return [
                `${normalizeX(this.points.top_left.x)}% ${normalizeY(this.points.top_left.y)}%`,
                `${normalizeX(this.points.top_right.x)}% ${normalizeY(this.points.top_right.y)}%`,
                `${normalizeX(this.points.bottom_right.x)}% ${normalizeY(this.points.bottom_right.y)}%`,
                `${normalizeX(this.points.bottom_left.x)}% ${normalizeY(this.points.bottom_left.y)}%`,
            ].join(', ');
        },
        previewStyle() {
            const bounds = this.bounds();
            const imageBounds = this.imageBounds();
            const { r, g, b } = this.hexToRgb(this.zoneColor);

            return `left:${imageBounds.left + (bounds.left * imageBounds.width)}px; top:${imageBounds.top + (bounds.top * imageBounds.height)}px; width:${bounds.width * imageBounds.width}px; height:${bounds.height * imageBounds.height}px; clip-path: polygon(${this.localPolygon()}); background: rgba(${r}, ${g}, ${b}, ${this.previewOpacity}); border: 1.5px dashed rgba(${r}, ${g}, ${b}, 0.95); transform: rotate(${this.manualRotation}deg); transform-origin: center center; filter: drop-shadow(0 18px 24px rgba(0,48,73,${this.shadowStrength * 0.35}));`;
        },
        baseTransform() {
            return `transform: translate(${this.panX}px, ${this.panY}px) scale(${this.zoom}); transform-origin: center center;`;
        },
        beginDrag(key, event) {
            this.draggingHandle = key;
            this.selectedHandle = key;
            this.markDirty();
            this.movePoint(event);
        },
        beginPan(event) {
            if (this.draggingHandle || this.zoom <= 1 || event.target.closest('button')) return;
            this.isPanning = true;
            this.lastPanPoint = { x: event.clientX, y: event.clientY };
        },
        movePoint(event) {
            if (this.draggingHandle) {
                const stage = this.$refs.stage;
                const rect = stage.getBoundingClientRect();
                const imageBounds = this.imageBounds();
                const x = this.clamp((event.clientX - rect.left - imageBounds.left) / imageBounds.width);
                const y = this.clamp((event.clientY - rect.top - imageBounds.top) / imageBounds.height);
                this.points[this.draggingHandle].x = Number(x.toFixed(4));
                this.points[this.draggingHandle].y = Number(y.toFixed(4));
                this.markDirty();
                return;
            }

            if (! this.isPanning || ! this.lastPanPoint) return;
            this.panX = Number((this.panX + (event.clientX - this.lastPanPoint.x)).toFixed(2));
            this.panY = Number((this.panY + (event.clientY - this.lastPanPoint.y)).toFixed(2));
            this.lastPanPoint = { x: event.clientX, y: event.clientY };
        },
        endPan() {
            this.isPanning = false;
            this.lastPanPoint = null;
        },
        nudgePan(axis, amount) {
            if (axis === 'x') this.panX = Number((this.panX + amount).toFixed(2));
            if (axis === 'y') this.panY = Number((this.panY + amount).toFixed(2));
        },
        resetView() {
            this.zoom = 1;
            this.panX = 0;
            this.panY = 0;
        },
        endDrag() {
            this.draggingHandle = null;
        },
        nudge(key, axis, delta) {
            this.points[key][axis] = Number(this.clamp(this.points[key][axis] + delta).toFixed(4));
            this.markDirty();
        },
        onKeydown(event) {
            if (! this.selectedHandle) return;
            const step = event.shiftKey ? 0.01 : (event.altKey ? 0.001 : 0.0025);
            if (event.key === 'ArrowLeft') { event.preventDefault(); this.nudge(this.selectedHandle, 'x', -step); }
            if (event.key === 'ArrowRight') { event.preventDefault(); this.nudge(this.selectedHandle, 'x', step); }
            if (event.key === 'ArrowUp') { event.preventDefault(); this.nudge(this.selectedHandle, 'y', -step); }
            if (event.key === 'ArrowDown') { event.preventDefault(); this.nudge(this.selectedHandle, 'y', step); }
        },
        resetMap() {
            this.points = JSON.parse(JSON.stringify(this.defaults));
            this.markDirty();
        },
        fitToFrame() {
            const meta = this.ratioMeta();
            const targetRatio = Math.max(0.2, Number(meta.ratio_width) / Math.max(1, Number(meta.ratio_height)));
            const maxWidth = 0.74;
            const maxHeight = 0.78;

            let width = maxWidth;
            let height = width / (targetRatio * this.stageAspectRatio);

            if (height > maxHeight) {
                height = maxHeight;
                width = height * targetRatio * this.stageAspectRatio;
            }

            const left = (1 - width) / 2;
            const top = (1 - height) / 2;

            this.points.top_left = { x: Number(left.toFixed(4)), y: Number(top.toFixed(4)) };
            this.points.top_right = { x: Number((left + width).toFixed(4)), y: Number(top.toFixed(4)) };
            this.points.bottom_right = { x: Number((left + width).toFixed(4)), y: Number((top + height).toFixed(4)) };
            this.points.bottom_left = { x: Number(left.toFixed(4)), y: Number((top + height).toFixed(4)) };
            this.markDirty();
        },
        syncBaseImageMetrics(event) {
            const image = event?.target;

            if (! image) return;

            this.naturalBaseWidth = image.naturalWidth || this.naturalBaseWidth || 0;
            this.naturalBaseHeight = image.naturalHeight || this.naturalBaseHeight || 0;
        },
        swapAssetPreview(key, event) {
            const file = event.target.files?.[0];
            if (! file) return;

            if (typeof this[key] === 'string' && this[key].startsWith('blob:')) {
                URL.revokeObjectURL(this[key]);
            }

            this[key] = URL.createObjectURL(file);
            this[{
                baseImageUrl: 'removeBaseImage',
                maskImageUrl: 'removeMaskImage',
                overlayImageUrl: 'removeOverlayImage',
                thumbImageUrl: 'removeThumbImage',
            }[key]] = false;
            this.markDirty();
        },
        syncAssetUrl(key, value) {
            if (typeof this[key] === 'string' && this[key].startsWith('blob:')) {
                URL.revokeObjectURL(this[key]);
            }

            this[key] = value;
            this[{
                baseImageUrl: 'removeBaseImage',
                maskImageUrl: 'removeMaskImage',
                overlayImageUrl: 'removeOverlayImage',
                thumbImageUrl: 'removeThumbImage',
            }[key]] = false;
            this.markDirty();
        },
        clearAsset(key) {
            if (typeof this[key] === 'string' && this[key].startsWith('blob:')) {
                URL.revokeObjectURL(this[key]);
            }

            this[key] = '';
            this[{
                baseImageUrl: 'removeBaseImage',
                maskImageUrl: 'removeMaskImage',
                overlayImageUrl: 'removeOverlayImage',
                thumbImageUrl: 'removeThumbImage',
            }[key]] = true;

            if (key === 'baseImageUrl') {
                this.naturalBaseWidth = 0;
                this.naturalBaseHeight = 0;
            }
            this.markDirty();
        },
    }"
    @mousemove.window="movePoint($event)"
    @mouseup.window="endDrag(); endPan()"
    @keydown.window="onKeydown($event)"
    @input="markDirty()"
    @change="markDirty()"
>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif
    <input type="hidden" name="image_width" :value="naturalBaseWidth || ''">
    <input type="hidden" name="image_height" :value="naturalBaseHeight || ''">

    <section class="surface-card px-6 py-6 md:px-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div class="max-w-3xl space-y-3">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Mockup Editor</p>
                <div class="space-y-2">
                    <h1 class="text-3xl font-semibold tracking-tight text-[var(--color-secondary-900)]">Edit mockup</h1>
                    <p class="max-w-2xl text-sm leading-7 text-[var(--color-text-soft)]">
                        Keep the scene settings compact up top, then adjust the four-corner placement beside the live preview for fast Nikah Nama mockup mapping.
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.mockups.index') }}" class="button-ghost">Back to mockups</a>
                <button type="submit" name="save_mode" value="draft" class="button-ghost" title="Save as draft">Save draft</button>
                <button type="submit" name="save_mode" value="published" class="button-primary" title="Save mockup">Save mockup</button>
            </div>
        </div>
    </section>

    @if (($map->coordinate_space ?? 'stage') !== 'image')
        <section class="surface-card-soft px-5 py-4">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Legacy map detected</p>
                    <p class="mt-2 max-w-3xl text-sm leading-7 text-[var(--color-text-soft)]">
                        This mockup was mapped before image-space coordinates were introduced. The editor is already showing the corrected image-space points.
                        Save once to persist the upgraded coordinates so storefront preview and proof export stay aligned.
                    </p>
                </div>
                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">
                    Current image: {{ $mockup->image_width ?: 'Unknown' }} × {{ $mockup->image_height ?: 'Unknown' }}
                </span>
            </div>
        </section>
    @endif

    <section class="grid gap-5 xl:grid-cols-2 2xl:grid-cols-[1.05fr_1.1fr_0.9fr]">
        <div class="surface-card-soft px-5 py-5">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Identity</p>
                    <h2 class="mt-1 text-lg font-semibold text-[var(--color-secondary-900)]">Title, template, and visibility</h2>
                </div>
                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]" x-text="ratioLabel()"></span>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <label class="field-shell md:col-span-2">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Title</span>
                    <input type="text" name="title" value="{{ old('title', $mockup->title) }}" class="field-input">
                    @error('title') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                </label>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Slug</span>
                    <input type="text" name="slug" value="{{ old('slug', $mockup->slug) }}" class="field-input" placeholder="Auto-generated if left blank">
                    @error('slug') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                </label>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Sort order</span>
                    <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $mockup->sort_order ?? 0) }}" class="field-input">
                    @error('sort_order') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                </label>

                <label class="field-shell md:col-span-2">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Assigned template <span class="text-[var(--color-text-soft)]">(optional)</span></span>
                    <select name="personalization_template_id" class="field-select" x-model="activeTemplateId">
                        <option value="">Reusable across products</option>
                        @foreach ($templates as $templateOption)
                            <option value="{{ $templateOption->id }}" @selected((string) old('personalization_template_id', $mockup->personalization_template_id) === (string) $templateOption->id)>
                                {{ $templateOption->name }}{{ $templateOption->product ? ' • '.$templateOption->product->name : '' }}
                            </option>
                        @endforeach
                    </select>
                    <span class="text-xs text-[var(--color-text-soft)]">Leave this blank when the same mockup scene should be reusable across multiple Nikahnama products.</span>
                    @error('personalization_template_id') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                </label>

                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Render mode</span>
                    <select name="render_mode" class="field-select">
                        @foreach (['flat_fit' => 'Flat fit', 'perspective_quad' => 'Perspective quad', 'masked_perspective' => 'Masked perspective'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('render_mode', $mockup->render_mode) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('render_mode') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                </label>

                <label class="inline-flex items-center justify-between gap-3 rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/90 px-4 py-3 text-sm font-medium text-[var(--color-secondary-900)]">
                    <span>Active mockup</span>
                    <span class="inline-flex items-center gap-3">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $mockup->is_active)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                    </span>
                </label>
            </div>
        </div>

        <div class="surface-card-soft px-5 py-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Asset uploads</p>
                <h2 class="mt-1 text-lg font-semibold text-[var(--color-secondary-900)]">Scene, overlay, and mask files</h2>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-2">
                @foreach ([
                    ['label' => 'Base image', 'upload' => 'base_image_upload', 'url' => 'base_image_url', 'state' => 'baseImageUrl', 'remove' => 'remove_base_image', 'remove_state' => 'removeBaseImage'],
                    ['label' => 'Mask image', 'upload' => 'mask_image_upload', 'url' => 'mask_image_url', 'state' => 'maskImageUrl', 'remove' => 'remove_mask_image', 'remove_state' => 'removeMaskImage'],
                    ['label' => 'Overlay image', 'upload' => 'overlay_image_upload', 'url' => 'overlay_image_url', 'state' => 'overlayImageUrl', 'remove' => 'remove_overlay_image', 'remove_state' => 'removeOverlayImage'],
                    ['label' => 'Thumbnail image', 'upload' => 'thumb_image_upload', 'url' => 'thumb_image_url', 'state' => 'thumbImageUrl', 'remove' => 'remove_thumb_image', 'remove_state' => 'removeThumbImage'],
                ] as $asset)
                    <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/90 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-[var(--color-secondary-900)]">{{ $asset['label'] }}</p>
                                <p class="mt-1 text-xs leading-5 text-[var(--color-text-soft)]">
                                    {{ $asset['label'] === 'Base image' ? 'Main uploaded scene for the mapped certificate.' : ($asset['label'] === 'Mask image' ? 'Optional clipping helper for more realistic depth.' : ($asset['label'] === 'Overlay image' ? 'Optional surface highlight or glass effect.' : 'Used in admin cards and selector views.')) }}
                                </p>
                            </div>
                            <button type="button" class="button-ghost !px-3 !py-2" x-show="{{ $asset['state'] }}" @click="clearAsset('{{ $asset['state'] }}')" title="Remove image">Remove</button>
                        </div>
                        <input type="hidden" name="{{ $asset['remove'] }}" :value="{{ $asset['remove_state'] }} ? 1 : 0">
                        <template x-if="{{ $asset['state'] }}">
                            <div class="mt-3 overflow-hidden rounded-[18px] border border-[var(--color-border-soft)] bg-[var(--bg-section-soft)]">
                                <img
                                    :src="{{ $asset['state'] }}"
                                    src="{{ old($asset['url'], $mockup->{str_replace('_upload', '_url', $asset['upload'])} ?? '') }}"
                                    alt="{{ $asset['label'] }}"
                                    class="aspect-[4/3] w-full object-cover"
                                >
                            </div>
                        </template>
                        <label class="field-shell mt-3">
                            <span class="text-xs font-medium text-[var(--color-secondary-900)]">Upload</span>
                            <input type="file" name="{{ $asset['upload'] }}" accept="image/*" class="field-input" @change="swapAssetPreview('{{ $asset['state'] }}', $event)">
                            @error($asset['upload']) <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                        </label>
                        <label class="field-shell mt-2">
                            <span class="text-xs font-medium text-[var(--color-secondary-900)]">Path / URL</span>
                            <input type="text" name="{{ $asset['url'] }}" value="{{ old($asset['url'], $mockup->{str_replace('_upload', '_url', $asset['upload'])} ?? null) }}" class="field-input" x-model="{{ $asset['state'] }}" @input="syncAssetUrl('{{ $asset['state'] }}', $event.target.value)">
                            @error($asset['url']) <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="surface-card-soft px-5 py-5 2xl:col-span-1 xl:col-span-2">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Notes and actions</p>
                    <h2 class="mt-1 text-lg font-semibold text-[var(--color-secondary-900)]">Production notes and duplicate tools</h2>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if ($isEdit)
                        <button type="submit" form="duplicate-mockup-form" class="button-ghost" title="Duplicate mockup">Duplicate mockup</button>
                    @endif
                    <button type="submit" name="save_mode" value="published" class="button-primary" title="Save mockup">Save mockup</button>
                </div>
            </div>

            <div class="mt-5 grid gap-4 xl:grid-cols-[1.2fr_0.8fr]">
                <label class="field-shell">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Notes</span>
                    <textarea name="notes" rows="5" class="field-textarea">{{ old('notes', $mockup->notes) }}</textarea>
                </label>

                <div class="grid gap-3">
                    <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/90 px-4 py-3 text-sm text-[var(--color-text-soft)]">
                        <p class="font-semibold text-[var(--color-secondary-900)]">{{ $mockup->base_image_url ? 'Base scene is ready.' : 'Base scene is still missing.' }}</p>
                        <p class="mt-1">Upload the hero scene first, then adjust the four corners until the certificate sits naturally.</p>
                    </div>
                    <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/90 px-4 py-3 text-sm text-[var(--color-text-soft)]">
                        <p class="font-semibold text-[var(--color-secondary-900)]">{{ $template?->fields?->count() ? 'Zone overlay is ready for mapping.' : 'Assigned template fields are still empty.' }}</p>
                        <p class="mt-1">Use the transparent zone color below the preview to quickly judge whether the mapped area matches the visible frame opening.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.45fr_0.85fr]">
        <div class="surface-card px-6 py-6 md:px-7">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-2xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Live mockup preview</p>
                    <h2 class="mt-1 text-2xl font-semibold text-[var(--color-secondary-900)]">Scene mapping canvas</h2>
                    <p class="mt-2 text-sm leading-7 text-[var(--color-text-soft)]">
                        Drag the four corner handles directly on the scene. This preview now shows only a transparent active zone overlay so the frame opening stays easy to inspect.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <span class="inline-flex items-center rounded-full bg-[rgba(120,0,0,0.08)] px-3 py-1 font-semibold text-[var(--color-primary-900)]">
                        Ratio <span class="ml-1" x-text="ratioLabel()"></span>
                    </span>
                    <span class="inline-flex items-center rounded-full bg-[rgba(102,155,188,0.14)] px-3 py-1 text-[var(--color-secondary-900)]">
                        <svg class="mr-1.5 h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M3 4.75A1.75 1.75 0 0 1 4.75 3h10.5A1.75 1.75 0 0 1 17 4.75v7.5A1.75 1.75 0 0 1 15.25 14H10.5a.75.75 0 0 0-.53.22l-1.75 1.75A.75.75 0 0 1 6.94 15.44V14H4.75A1.75 1.75 0 0 1 3 12.25v-7.5Z" /></svg>
                        <span class="font-medium truncate max-w-[14rem]" x-text="ratioMeta().name"></span>
                    </span>
                    <span
                        x-show="isDirty"
                        x-transition.opacity
                        class="inline-flex items-center rounded-full bg-[rgba(193,18,31,0.12)] px-3 py-1 font-medium text-[var(--color-primary-900)]"
                    >
                        <svg class="mr-1.5 h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0ZM9 6a1 1 0 1 1 2 0v4a1 1 0 1 1-2 0V6Zm1 9a1.25 1.25 0 1 0 0-2.5A1.25 1.25 0 0 0 10 15Z" clip-rule="evenodd" /></svg>
                        Unsaved
                    </span>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-2 text-sm">
                <div class="group relative">
                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[var(--color-border-soft)] bg-white text-[var(--color-secondary-900)] transition hover:border-[var(--color-primary-900)] hover:text-[var(--color-primary-900)]" @click="zoom = Math.max(0.75, Number((zoom - 0.1).toFixed(2)))" aria-label="Zoom out">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M9 3a6 6 0 1 0 3.874 10.582l3.272 3.272a1 1 0 0 0 1.414-1.414l-3.272-3.272A6 6 0 0 0 9 3Zm-3 6a1 1 0 0 1 1-1h4a1 1 0 1 1 0 2H7a1 1 0 0 1-1-1Z" clip-rule="evenodd" /></svg>
                    </button>
                    <span class="pointer-events-none absolute -top-10 left-1/2 hidden -translate-x-1/2 rounded-full bg-[var(--color-secondary-900)] px-3 py-1 text-xs font-medium text-white shadow-lg group-hover:block">Zoom out</span>
                </div>
                <div class="group relative">
                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[var(--color-border-soft)] bg-white text-[var(--color-secondary-900)] transition hover:border-[var(--color-primary-900)] hover:text-[var(--color-primary-900)]" @click="zoom = Math.min(1.8, Number((zoom + 0.1).toFixed(2)))" aria-label="Zoom in">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M9 3a6 6 0 1 0 3.874 10.582l3.272 3.272a1 1 0 0 0 1.414-1.414l-3.272-3.272A6 6 0 0 0 9 3ZM7 9a1 1 0 0 1 1-1H9V7a1 1 0 1 1 2 0v1h1a1 1 0 1 1 0 2h-1v1a1 1 0 1 1-2 0v-1H8a1 1 0 0 1-1-1Z" clip-rule="evenodd" /></svg>
                    </button>
                    <span class="pointer-events-none absolute -top-10 left-1/2 hidden -translate-x-1/2 rounded-full bg-[var(--color-secondary-900)] px-3 py-1 text-xs font-medium text-white shadow-lg group-hover:block">Zoom in</span>
                </div>
                <div class="group relative">
                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[var(--color-border-soft)] bg-white text-[var(--color-secondary-900)] transition hover:border-[var(--color-primary-900)] hover:text-[var(--color-primary-900)]" @click="nudgePan('x', -24)" aria-label="Pan left">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M11.79 4.21a1 1 0 0 1 0 1.42L8.41 9H16a1 1 0 1 1 0 2H8.41l3.38 3.38a1 1 0 1 1-1.42 1.42l-5.09-5.09a1 1 0 0 1 0-1.42l5.09-5.08a1 1 0 0 1 1.42 0Z" clip-rule="evenodd" /></svg>
                    </button>
                    <span class="pointer-events-none absolute -top-10 left-1/2 hidden -translate-x-1/2 rounded-full bg-[var(--color-secondary-900)] px-3 py-1 text-xs font-medium text-white shadow-lg group-hover:block">Pan left</span>
                </div>
                <div class="group relative">
                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[var(--color-border-soft)] bg-white text-[var(--color-secondary-900)] transition hover:border-[var(--color-primary-900)] hover:text-[var(--color-primary-900)]" @click="nudgePan('x', 24)" aria-label="Pan right">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.21 4.21a1 1 0 0 0 0 1.42L11.59 9H4a1 1 0 1 0 0 2h7.59l-3.38 3.38a1 1 0 1 0 1.42 1.42l5.09-5.09a1 1 0 0 0 0-1.42L9.63 4.2a1 1 0 0 0-1.42 0Z" clip-rule="evenodd" /></svg>
                    </button>
                    <span class="pointer-events-none absolute -top-10 left-1/2 hidden -translate-x-1/2 rounded-full bg-[var(--color-secondary-900)] px-3 py-1 text-xs font-medium text-white shadow-lg group-hover:block">Pan right</span>
                </div>
                <div class="group relative">
                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[var(--color-border-soft)] bg-white text-[var(--color-secondary-900)] transition hover:border-[var(--color-primary-900)] hover:text-[var(--color-primary-900)]" @click="nudgePan('y', -24)" aria-label="Pan up">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M15.79 11.79a1 1 0 0 1-1.42 0L11 8.41V16a1 1 0 1 1-2 0V8.41L5.62 11.8a1 1 0 1 1-1.42-1.42l5.09-5.1a1 1 0 0 1 1.42 0l5.08 5.1a1 1 0 0 1 0 1.41Z" clip-rule="evenodd" /></svg>
                    </button>
                    <span class="pointer-events-none absolute -top-10 left-1/2 hidden -translate-x-1/2 rounded-full bg-[var(--color-secondary-900)] px-3 py-1 text-xs font-medium text-white shadow-lg group-hover:block">Pan up</span>
                </div>
                <div class="group relative">
                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[var(--color-border-soft)] bg-white text-[var(--color-secondary-900)] transition hover:border-[var(--color-primary-900)] hover:text-[var(--color-primary-900)]" @click="nudgePan('y', 24)" aria-label="Pan down">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M15.79 8.21a1 1 0 0 0-1.42 0L11 11.59V4a1 1 0 1 0-2 0v7.59L5.62 8.2A1 1 0 1 0 4.2 9.62l5.09 5.1a1 1 0 0 0 1.42 0l5.08-5.1a1 1 0 0 0 0-1.41Z" clip-rule="evenodd" /></svg>
                    </button>
                    <span class="pointer-events-none absolute -top-10 left-1/2 hidden -translate-x-1/2 rounded-full bg-[var(--color-secondary-900)] px-3 py-1 text-xs font-medium text-white shadow-lg group-hover:block">Pan down</span>
                </div>
                <div class="group relative">
                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[var(--color-border-soft)] bg-white text-[var(--color-secondary-900)] transition hover:border-[var(--color-primary-900)] hover:text-[var(--color-primary-900)]" @click="resetView()" aria-label="Reset view">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M15.312 4.17a8 8 0 1 0 2.41 5.742 1 1 0 1 1-2 0 6 6 0 1 1-1.807-4.307H12a1 1 0 1 1 0-2h4.5a1 1 0 0 1 1 1V9a1 1 0 1 1-2 0V6.245a8.036 8.036 0 0 0-.188-.075Z" clip-rule="evenodd" /></svg>
                    </button>
                    <span class="pointer-events-none absolute -top-10 left-1/2 hidden -translate-x-1/2 rounded-full bg-[var(--color-secondary-900)] px-3 py-1 text-xs font-medium text-white shadow-lg group-hover:block">Reset view</span>
                </div>
                <div class="group relative">
                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[var(--color-border-soft)] bg-white text-[var(--color-secondary-900)] transition hover:border-[var(--color-primary-900)] hover:text-[var(--color-primary-900)]" @click="resetMap()" aria-label="Reset map">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 2a1 1 0 0 1 .894.553l2.5 5A1 1 0 0 1 12.5 9h-5a1 1 0 0 1-.894-1.447l2.5-5A1 1 0 0 1 10 2ZM4 11a1 1 0 0 1 1-1h4a1 1 0 0 1 0 2H5a1 1 0 0 1-1-1Zm7 0a1 1 0 0 1 1-1h3a1 1 0 1 1 0 2h-3a1 1 0 0 1-1-1Zm-7 4a1 1 0 0 1 1-1h10a1 1 0 1 1 0 2H5a1 1 0 0 1-1-1Z" /></svg>
                    </button>
                    <span class="pointer-events-none absolute -top-10 left-1/2 hidden -translate-x-1/2 rounded-full bg-[var(--color-secondary-900)] px-3 py-1 text-xs font-medium text-white shadow-lg group-hover:block">Reset map</span>
                </div>
            </div>

            <div class="mt-6 rounded-[32px] border border-[var(--color-border-soft)] bg-[linear-gradient(180deg,rgba(253,240,213,0.65),rgba(255,255,255,0.96))] p-5 md:p-6">
                <div
                    x-ref="stage"
                    class="relative mx-auto aspect-[4/3] w-full max-w-5xl overflow-hidden rounded-[28px] border border-[var(--color-border-soft)] bg-white shadow-[var(--shadow-soft)]"
                    :class="zoom > 1 && !draggingHandle ? (isPanning ? 'cursor-grabbing' : 'cursor-grab') : 'cursor-default'"
                    @mousedown="beginPan($event)"
                >
                    <div class="absolute inset-0" :style="baseTransform()">
                        <template x-if="baseImageUrl">
                            <img
                                x-ref="stageImage"
                                :src="baseImageUrl"
                                src="{{ old('base_image_url', $mockup->base_image_url) }}"
                                alt="{{ $mockup->title ?: 'Mockup base' }}"
                                class="h-full w-full bg-[var(--bg-section-soft)] object-contain"
                                @load="syncBaseImageMetrics($event)"
                            >
                        </template>
                        <template x-if="!baseImageUrl">
                            <div class="flex h-full items-center justify-center bg-[linear-gradient(135deg,rgba(253,240,213,0.76),rgba(255,255,255,0.96))] px-8 text-center text-lg font-medium text-[var(--color-text-soft)]">
                                Upload a base mockup image to start placing the Nikah Nama artwork.
                            </div>
                        </template>

                        <div class="absolute inset-0 pointer-events-none" :style="previewStyle()"></div>

                        <template x-if="overlayImageUrl">
                            <img
                                :src="overlayImageUrl"
                                src="{{ old('overlay_image_url', $mockup->overlay_image_url) }}"
                                alt=""
                                class="pointer-events-none absolute inset-0 h-full w-full object-contain"
                                :style="`opacity:${Math.max(0.12, highlightStrength)}`"
                            >
                        </template>

                        <template x-if="maskImageUrl">
                            <img
                                :src="maskImageUrl"
                                src="{{ old('mask_image_url', $mockup->mask_image_url) }}"
                                alt=""
                                class="pointer-events-none absolute inset-0 h-full w-full object-contain mix-blend-multiply"
                                :style="`opacity:${Math.max(0.12, highlightStrength * 0.9)}`"
                            >
                        </template>

                        <svg class="pointer-events-none absolute inset-0 h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                            <polygon
                                :points="(() => { const bounds = imageBounds(); return `${((bounds.left + (points.top_left.x * bounds.width)) / ($refs.stage?.clientWidth || 1)) * 100},${((bounds.top + (points.top_left.y * bounds.height)) / ($refs.stage?.clientHeight || 1)) * 100} ${((bounds.left + (points.top_right.x * bounds.width)) / ($refs.stage?.clientWidth || 1)) * 100},${((bounds.top + (points.top_right.y * bounds.height)) / ($refs.stage?.clientHeight || 1)) * 100} ${((bounds.left + (points.bottom_right.x * bounds.width)) / ($refs.stage?.clientWidth || 1)) * 100},${((bounds.top + (points.bottom_right.y * bounds.height)) / ($refs.stage?.clientHeight || 1)) * 100} ${((bounds.left + (points.bottom_left.x * bounds.width)) / ($refs.stage?.clientWidth || 1)) * 100},${((bounds.top + (points.bottom_left.y * bounds.height)) / ($refs.stage?.clientHeight || 1)) * 100}`; })()"
                                fill="rgba(102,155,188,0.12)"
                                stroke="rgba(120,0,0,0.78)"
                                stroke-width="0.45"
                                stroke-dasharray="1.4 1.2"
                            />
                        </svg>

                        <template x-for="handle in ['top_left', 'top_right', 'bottom_right', 'bottom_left']" :key="handle">
                            <button
                                type="button"
                                class="absolute flex h-[30px] w-[30px] items-center justify-center rounded-full transition-transform hover:scale-105"
                                :style="`${pointStyle(handle)} ${pointToneStyle(handle)} ${selectedHandle === handle ? `--tw-ring-color:${cornerMeta[handle].ring};` : ''}`"
                                @mousedown.prevent="beginDrag(handle, $event)"
                                @click.prevent="selectedHandle = handle"
                                :class="{ 'ring-4': selectedHandle === handle }"
                                :title="`Select ${cornerMeta[handle].label} corner`"
                            >
                                <span class="h-[8px] w-[8px] rounded-full" :style="pointCenterStyle(handle)"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-3 text-sm">
                <span
                    class="rounded-full px-3 py-1 font-medium"
                    :style="cornerBadgeStyle(selectedHandle)"
                >
                    Selected corner: <span x-text="cornerMeta[selectedHandle].label"></span>
                </span>
                <span class="rounded-full bg-[rgba(255,255,255,0.8)] px-3 py-1 text-[var(--color-text-soft)]">Drag to move</span>
                <span class="rounded-full bg-[rgba(255,255,255,0.8)] px-3 py-1 text-[var(--color-text-soft)]">Zoomed scene can be dragged</span>
                <span class="rounded-full bg-[rgba(255,255,255,0.8)] px-3 py-1 text-[var(--color-text-soft)]">Arrow keys nudge</span>
                <span class="rounded-full bg-[rgba(255,255,255,0.8)] px-3 py-1 text-[var(--color-text-soft)]">Shift = larger move</span>
                <span class="rounded-full bg-[rgba(255,255,255,0.8)] px-3 py-1 text-[var(--color-text-soft)]">Alt = micro move</span>
            </div>

            <div class="mt-6 surface-card-soft px-4 py-4">
                <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Overlay controls</p>
                <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <label class="field-shell">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Preview opacity</span>
                        <input type="range" min="0.1" max="1" step="0.01" x-model="previewOpacity" name="map[opacity]" class="mt-2 w-full accent-[var(--color-primary-900)]">
                        <span class="text-xs text-[var(--color-text-soft)]"><span x-text="previewOpacity.toFixed(2)"></span> opacity</span>
                    </label>
                    <label class="field-shell">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Shadow strength</span>
                        <input type="range" min="0" max="1" step="0.01" x-model="shadowStrength" name="map[shadow_strength]" class="mt-2 w-full accent-[var(--color-primary-900)]">
                        <span class="text-xs text-[var(--color-text-soft)]"><span x-text="shadowStrength.toFixed(2)"></span> strength</span>
                    </label>
                    <label class="field-shell">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Highlight strength</span>
                        <input type="range" min="0" max="1" step="0.01" x-model="highlightStrength" name="map[highlight_strength]" class="mt-2 w-full accent-[var(--color-primary-900)]">
                        <span class="text-xs text-[var(--color-text-soft)]"><span x-text="highlightStrength.toFixed(2)"></span> strength</span>
                    </label>
                    <label class="field-shell">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Zone color</span>
                        <input type="color" x-model="zoneColor" class="mt-2 h-11 w-full rounded-[var(--radius-lg)] border border-[var(--color-border-soft)] bg-white p-1">
                        <span class="text-xs text-[var(--color-text-soft)]" x-text="zoneColor.toUpperCase()"></span>
                    </label>
                </div>
            </div>
        </div>

        <div class="space-y-6 xl:sticky xl:top-24 xl:self-start">
            <div class="surface-card px-6 py-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Placement adjustment</p>
                    <h2 class="mt-1 text-2xl font-semibold text-[var(--color-secondary-900)]">Corner mapping and render controls</h2>
                </div>

                <div class="mt-6 grid gap-4">
                    <input type="hidden" name="map[map_type]" value="quad">
                    <input type="hidden" name="map[normalized_coordinates]" value="1">
                    <input type="hidden" name="map[coordinate_space]" value="image">

                    <label class="field-shell">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Fit mode</span>
                        <select name="map[fit_mode]" class="field-select" x-model="fitMode">
                            @foreach (['contain' => 'Contain', 'cover' => 'Cover', 'stretch' => 'Stretch'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('map.fit_mode', $map->fit_mode ?? 'stretch') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    @foreach ([
                        'top_left' => 'Top left',
                        'top_right' => 'Top right',
                        'bottom_right' => 'Bottom right',
                        'bottom_left' => 'Bottom left',
                    ] as $key => $label)
                        <div
                            class="rounded-[var(--radius-xl)] border p-4 transition-all"
                            :style="cornerCardStyle('{{ $key }}')"
                            @click="selectedHandle = '{{ $key }}'"
                        >
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-sm font-semibold text-[var(--color-secondary-900)]">{{ $label }}</p>
                                        <span
                                            class="rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.14em]"
                                            :style="cornerBadgeStyle('{{ $key }}')"
                                        >
                                            Corner
                                        </span>
                                    </div>
                                    <p class="mt-1 text-xs leading-5 text-[var(--color-text-soft)]">Click the corner or input to focus this handle.</p>
                                </div>
                                <button type="button" class="button-ghost !px-3 !py-2" @click.stop="selectedHandle = '{{ $key }}'" title="Select this corner">Select</button>
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-3">
                                <label class="field-shell">
                                    <span class="text-xs font-medium text-[var(--color-secondary-900)]">X</span>
                                    <input
                                        type="number"
                                        min="0"
                                        max="1"
                                        step="0.0001"
                                        name="map[{{ $key }}_x]"
                                        value="{{ old('map.'.$key.'_x', data_get($editorMap, $key.'_x')) }}"
                                        x-model.number="points.{{ $key }}.x"
                                        class="field-input"
                                        data-corner-input="{{ $key }}"
                                        @click.stop
                                        @focus="selectedHandle = '{{ $key }}'"
                                    >
                                </label>
                                <label class="field-shell">
                                    <span class="text-xs font-medium text-[var(--color-secondary-900)]">Y</span>
                                    <input
                                        type="number"
                                        min="0"
                                        max="1"
                                        step="0.0001"
                                        name="map[{{ $key }}_y]"
                                        value="{{ old('map.'.$key.'_y', data_get($editorMap, $key.'_y')) }}"
                                        x-model.number="points.{{ $key }}.y"
                                        class="field-input"
                                        @click.stop
                                        @focus="selectedHandle = '{{ $key }}'"
                                    >
                                </label>
                            </div>
                        </div>
                    @endforeach

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="field-shell">
                            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Manual rotation</span>
                            <input type="number" step="0.01" min="-180" max="180" name="map[manual_rotation]" value="{{ old('map.manual_rotation', $map->manual_rotation) }}" x-model.number="manualRotation" class="field-input">
                        </label>
                        <label class="field-shell">
                            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Object position X</span>
                            <input type="number" step="0.0001" min="0" max="1" name="map[object_position_x]" value="{{ old('map.object_position_x', $map->object_position_x) }}" x-model.number="objectPositionX" class="field-input">
                        </label>
                        <label class="field-shell md:col-span-2">
                            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Object position Y</span>
                            <input type="number" step="0.0001" min="0" max="1" name="map[object_position_y]" value="{{ old('map.object_position_y', $map->object_position_y) }}" x-model.number="objectPositionY" class="field-input">
                        </label>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <div class="flex flex-wrap gap-3">
        <button type="submit" name="save_mode" value="draft" class="button-ghost" title="Save as draft">Save draft</button>
        <button type="submit" name="save_mode" value="published" class="button-primary" title="{{ $isEdit ? 'Save mockup' : 'Create mockup' }}">{{ $isEdit ? 'Save mockup' : 'Create mockup' }}</button>
        <a href="{{ route('admin.mockups.index') }}" class="button-ghost" title="Cancel and go back">Cancel</a>
    </div>
</form>

@if ($isEdit)
    <form id="duplicate-mockup-form" method="POST" action="{{ route('admin.mockups.duplicate', $mockup) }}" class="hidden">
        @csrf
    </form>
@endif
