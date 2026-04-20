@php($isEdit = $mockup->exists)
@php($map = $mockup->map)
@php($template = $mockup->template)
@php($previewData = old('preview_data', $template?->preview_data_presets ?? [
    'bride_name' => 'Amena',
    'groom_name' => 'Hassan',
    'ceremony_date' => '12 December 2026',
    'venue' => 'Dhaka, Bangladesh',
]))

<form
    method="POST"
    action="{{ $isEdit ? route('admin.mockups.update', $mockup) : route('admin.mockups.store') }}"
    enctype="multipart/form-data"
    class="space-y-6"
    x-data="{
        zoom: 1,
        stageAspectRatio: 4 / 3,
        previewOpacity: {{ (float) old('map.opacity', $map->opacity ?? 0.95) }},
        shadowStrength: {{ (float) old('map.shadow_strength', $map->shadow_strength ?? 0.18) }},
        highlightStrength: {{ (float) old('map.highlight_strength', $map->highlight_strength ?? 0.12) }},
        selectedHandle: 'top_left',
        draggingHandle: null,
        showCompare: true,
        isDirty: false,
        activeTemplateId: '{{ (string) old('personalization_template_id', $mockup->personalization_template_id) }}',
        templateMeta: @js($templates->mapWithKeys(fn ($item) => [
            (string) $item->id => [
                'name' => $item->name,
                'ratio_width' => $item->export_ratio_width ?? 9,
                'ratio_height' => $item->export_ratio_height ?? 13,
            ],
        ])),
        brideName: @js($previewData['bride_name'] ?? 'Amena'),
        groomName: @js($previewData['groom_name'] ?? 'Hassan'),
        ceremonyDate: @js($previewData['ceremony_date'] ?? '12 December 2026'),
        venue: @js($previewData['venue'] ?? 'Dhaka, Bangladesh'),
        baseImageUrl: @js(old('base_image_url', $mockup->base_image_url)),
        maskImageUrl: @js(old('mask_image_url', $mockup->mask_image_url)),
        overlayImageUrl: @js(old('overlay_image_url', $mockup->overlay_image_url)),
        thumbImageUrl: @js(old('thumb_image_url', $mockup->thumb_image_url)),
        removeBaseImage: {{ old('remove_base_image', 0) ? 'true' : 'false' }},
        removeMaskImage: {{ old('remove_mask_image', 0) ? 'true' : 'false' }},
        removeOverlayImage: {{ old('remove_overlay_image', 0) ? 'true' : 'false' }},
        removeThumbImage: {{ old('remove_thumb_image', 0) ? 'true' : 'false' }},
        defaults: {
            top_left: { x: {{ (float) old('map.top_left_x', $map->top_left_x ?? 0.20) }}, y: {{ (float) old('map.top_left_y', $map->top_left_y ?? 0.18) }} },
            top_right: { x: {{ (float) old('map.top_right_x', $map->top_right_x ?? 0.80) }}, y: {{ (float) old('map.top_right_y', $map->top_right_y ?? 0.18) }} },
            bottom_right: { x: {{ (float) old('map.bottom_right_x', $map->bottom_right_x ?? 0.80) }}, y: {{ (float) old('map.bottom_right_y', $map->bottom_right_y ?? 0.82) }} },
            bottom_left: { x: {{ (float) old('map.bottom_left_x', $map->bottom_left_x ?? 0.20) }}, y: {{ (float) old('map.bottom_left_y', $map->bottom_left_y ?? 0.82) }} },
        },
        points: {
            top_left: { x: {{ (float) old('map.top_left_x', $map->top_left_x ?? 0.20) }}, y: {{ (float) old('map.top_left_y', $map->top_left_y ?? 0.18) }} },
            top_right: { x: {{ (float) old('map.top_right_x', $map->top_right_x ?? 0.80) }}, y: {{ (float) old('map.top_right_y', $map->top_right_y ?? 0.18) }} },
            bottom_right: { x: {{ (float) old('map.bottom_right_x', $map->bottom_right_x ?? 0.80) }}, y: {{ (float) old('map.bottom_right_y', $map->bottom_right_y ?? 0.82) }} },
            bottom_left: { x: {{ (float) old('map.bottom_left_x', $map->bottom_left_x ?? 0.20) }}, y: {{ (float) old('map.bottom_left_y', $map->bottom_left_y ?? 0.82) }} },
        },
        sampleDefaults: {
            brideName: @js($previewData['bride_name'] ?? 'Amena'),
            groomName: @js($previewData['groom_name'] ?? 'Hassan'),
            ceremonyDate: @js($previewData['ceremony_date'] ?? '12 December 2026'),
            venue: @js($previewData['venue'] ?? 'Dhaka, Bangladesh'),
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
        pointStyle(key) {
            return `left: calc(${this.points[key].x * 100}% - 10px); top: calc(${this.points[key].y * 100}% - 10px);`;
        },
        polygon() {
            return `${this.points.top_left.x * 100}% ${this.points.top_left.y * 100}%, ${this.points.top_right.x * 100}% ${this.points.top_right.y * 100}%, ${this.points.bottom_right.x * 100}% ${this.points.bottom_right.y * 100}%, ${this.points.bottom_left.x * 100}% ${this.points.bottom_left.y * 100}%`;
        },
        previewStyle() {
            const xValues = [this.points.top_left.x, this.points.top_right.x, this.points.bottom_right.x, this.points.bottom_left.x];
            const yValues = [this.points.top_left.y, this.points.top_right.y, this.points.bottom_right.y, this.points.bottom_left.y];
            const minX = Math.min(...xValues);
            const maxX = Math.max(...xValues);
            const minY = Math.min(...yValues);
            const maxY = Math.max(...yValues);
            const width = Math.max(0.12, maxX - minX);
            const height = Math.max(0.12, maxY - minY);
            return `left:${minX * 100}%; top:${minY * 100}%; width:${width * 100}%; height:${height * 100}%; clip-path: polygon(${this.polygon()}); opacity:${this.previewOpacity}; filter: drop-shadow(0 18px 24px rgba(0,48,73,${this.shadowStrength * 0.35}));`;
        },
        baseTransform() {
            return `transform: scale(${this.zoom}); transform-origin: center center;`;
        },
        beginDrag(key, event) {
            this.draggingHandle = key;
            this.selectedHandle = key;
            this.markDirty();
            this.movePoint(event);
        },
        movePoint(event) {
            if (! this.draggingHandle) return;
            const stage = this.$refs.stage;
            const rect = stage.getBoundingClientRect();
            const x = this.clamp((event.clientX - rect.left) / rect.width);
            const y = this.clamp((event.clientY - rect.top) / rect.height);
            this.points[this.draggingHandle].x = Number(x.toFixed(4));
            this.points[this.draggingHandle].y = Number(y.toFixed(4));
            this.markDirty();
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
        loadSample() {
            this.brideName = this.sampleDefaults.brideName;
            this.groomName = this.sampleDefaults.groomName;
            this.ceremonyDate = this.sampleDefaults.ceremonyDate;
            this.venue = this.sampleDefaults.venue;
            this.markDirty();
        },
        swapAssetPreview(key, event) {
            const file = event.target.files?.[0];

            if (! file) {
                return;
            }

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
            this.markDirty();
        },
    }"
    @mousemove.window="movePoint($event)"
    @mouseup.window="endDrag()"
    @keydown.window="onKeydown($event)"
    @input="markDirty()"
    @change="markDirty()"
>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <section class="grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
        <div class="space-y-6">
            <div class="surface-card p-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Interactive canvas</p>
                        <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Mockup scene with 4-corner placement</h3>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button type="button" class="button-ghost" @click="fitToFrame()">Fit to frame</button>
                        <button type="button" class="button-ghost" @click="zoom = Math.max(0.75, Number((zoom - 0.1).toFixed(2)))">Zoom out</button>
                        <button type="button" class="button-ghost" @click="zoom = Math.min(1.8, Number((zoom + 0.1).toFixed(2)))">Zoom in</button>
                        <button type="button" class="button-ghost" @click="resetMap()">Reset map</button>
                        <button type="button" class="button-ghost" @click="showCompare = !showCompare" x-text="showCompare ? 'Hide compare' : 'Show compare'"></button>
                        <button type="button" class="button-primary" @click="loadSample()">Load test preview</button>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-3 text-sm">
                    <span class="inline-flex items-center rounded-full bg-[rgba(120,0,0,0.08)] px-3 py-1 font-semibold text-[var(--color-primary-900)]">
                        Ratio locked to <span class="ml-1" x-text="ratioLabel()"></span>
                    </span>
                    <span class="inline-flex items-center rounded-full bg-[rgba(102,155,188,0.12)] px-3 py-1 text-[var(--color-secondary-900)]">
                        <span class="font-medium" x-text="ratioMeta().name"></span>
                    </span>
                    <span
                        x-show="isDirty"
                        x-transition.opacity
                        class="inline-flex items-center rounded-full bg-[rgba(163,0,0,0.1)] px-3 py-1 font-medium text-[var(--color-primary-900)]"
                    >
                        Unsaved mapping changes
                    </span>
                </div>

                <div class="mt-6 rounded-[var(--radius-2xl)] border border-[var(--color-border-soft)] bg-[var(--bg-section-soft)] p-5">
                    <div
                        x-ref="stage"
                        class="relative mx-auto aspect-[4/3] w-full max-w-5xl overflow-hidden rounded-[var(--radius-2xl)] border border-[var(--color-border-soft)] bg-white shadow-[var(--shadow-soft)]"
                    >
                        <div class="absolute inset-0" :style="baseTransform()">
                            <template x-if="baseImageUrl">
                                <img :src="baseImageUrl" alt="{{ $mockup->title ?: 'Mockup base' }}" class="h-full w-full object-cover">
                            </template>
                            <template x-if="!baseImageUrl">
                                <div class="flex h-full items-center justify-center bg-[linear-gradient(135deg,rgba(253,240,213,0.72),rgba(255,255,255,0.96))] px-8 text-center text-lg font-medium text-[var(--color-text-soft)]">
                                    Upload a base mockup image to start mapping the Nikah artwork.
                                </div>
                            </template>

                            <div class="absolute inset-0 pointer-events-none" :style="previewStyle()">
                                <div class="h-full w-full rounded-[20px] border border-[rgba(120,0,0,0.16)] bg-white/92 px-[10%] py-[9%] text-center shadow-[0_22px_45px_rgba(0,48,73,0.12)]" :style="`box-shadow: 0 24px 50px rgba(0,48,73,${shadowStrength * 0.45});`">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-[var(--color-primary-900)]">Nikah Nama Preview</p>
                                    <div class="mt-6 space-y-4 text-[var(--color-secondary-900)]">
                                        <p class="text-[11px] uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Bride</p>
                                        <p class="text-[clamp(1rem,2vw,1.7rem)] font-semibold leading-tight" x-text="brideName"></p>
                                        <p class="text-[11px] uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Groom</p>
                                        <p class="text-[clamp(1rem,2vw,1.7rem)] font-semibold leading-tight" x-text="groomName"></p>
                                        <p class="text-[11px] uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Date</p>
                                        <p class="text-[clamp(0.82rem,1.5vw,1.05rem)] font-medium" x-text="ceremonyDate"></p>
                                        <p class="text-[11px] uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Venue</p>
                                        <p class="text-[clamp(0.82rem,1.5vw,1.05rem)] font-medium" x-text="venue"></p>
                                    </div>
                                </div>
                            </div>

                            <template x-if="overlayImageUrl">
                                <img :src="overlayImageUrl" alt="" class="pointer-events-none absolute inset-0 h-full w-full object-cover" :style="`opacity:${Math.max(0.12, highlightStrength)}`">
                            </template>

                            <template x-if="maskImageUrl">
                                <img :src="maskImageUrl" alt="" class="pointer-events-none absolute inset-0 h-full w-full object-cover mix-blend-multiply" :style="`opacity:${Math.max(0.12, highlightStrength * 0.9)}`">
                            </template>

                            <svg class="pointer-events-none absolute inset-0 h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                                <polygon
                                    :points="`${points.top_left.x * 100},${points.top_left.y * 100} ${points.top_right.x * 100},${points.top_right.y * 100} ${points.bottom_right.x * 100},${points.bottom_right.y * 100} ${points.bottom_left.x * 100},${points.bottom_left.y * 100}`"
                                    fill="rgba(102,155,188,0.12)"
                                    stroke="rgba(120,0,0,0.75)"
                                    stroke-width="0.4"
                                    stroke-dasharray="1.2 1.1"
                                />
                            </svg>

                            <template x-for="handle in ['top_left', 'top_right', 'bottom_right', 'bottom_left']" :key="handle">
                                <button
                                    type="button"
                                    class="absolute h-5 w-5 rounded-full border-2 border-white bg-[var(--color-primary-900)] shadow-[0_8px_18px_rgba(0,48,73,0.22)] transition-transform hover:scale-105"
                                    :style="pointStyle(handle)"
                                    @mousedown.prevent="beginDrag(handle, $event)"
                                    @click.prevent="selectedHandle = handle"
                                    :class="{ 'ring-4 ring-[rgba(102,155,188,0.28)]': selectedHandle === handle }"
                                ></button>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="mt-5 grid gap-4 lg:grid-cols-[1.4fr_1fr_1fr_1fr]">
                    <div class="surface-card-soft p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Selected corner</p>
                                <p class="mt-1 text-sm text-[var(--color-text-soft)]" x-text="selectedHandle.replace('_', ' ')"></p>
                            </div>
                            <span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-[var(--color-primary-900)]">Arrow edit</span>
                        </div>
                        <div class="mt-4 grid grid-cols-3 gap-2">
                            <button type="button" class="button-ghost !px-3 !py-2" @click="nudge(selectedHandle, 'y', -0.0025)">Up</button>
                            <button type="button" class="button-ghost !px-3 !py-2" @click="fitToFrame()">Fit</button>
                            <button type="button" class="button-ghost !px-3 !py-2" @click="nudge(selectedHandle, 'y', 0.0025)">Down</button>
                            <button type="button" class="button-ghost !px-3 !py-2" @click="nudge(selectedHandle, 'x', -0.0025)">Left</button>
                            <button type="button" class="button-ghost !px-3 !py-2" @click="resetMap()">Reset</button>
                            <button type="button" class="button-ghost !px-3 !py-2" @click="nudge(selectedHandle, 'x', 0.0025)">Right</button>
                        </div>
                        <p class="mt-3 text-xs leading-6 text-[var(--color-text-soft)]">Arrow keys nudge. Hold <span class="font-semibold text-[var(--color-secondary-900)]">Shift</span> for larger moves, <span class="font-semibold text-[var(--color-secondary-900)]">Alt</span> for fine moves.</p>
                    </div>
                    <div class="surface-card-soft p-5">
                        <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Preview opacity</p>
                        <input type="range" min="0.1" max="1" step="0.01" x-model="previewOpacity" name="map[opacity]" class="mt-4 w-full accent-[var(--color-primary-900)]">
                        <p class="mt-2 text-sm text-[var(--color-text-soft)]"><span x-text="previewOpacity.toFixed(2)"></span> opacity for the sample certificate composite.</p>
                    </div>
                    <div class="surface-card-soft p-5">
                        <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Shadow strength</p>
                        <input type="range" min="0" max="1" step="0.01" x-model="shadowStrength" name="map[shadow_strength]" class="mt-4 w-full accent-[var(--color-primary-900)]">
                        <p class="mt-2 text-sm text-[var(--color-text-soft)]"><span x-text="shadowStrength.toFixed(2)"></span> shadow depth for the test certificate.</p>
                    </div>
                    <div class="surface-card-soft p-5">
                        <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Highlight strength</p>
                        <input type="range" min="0" max="1" step="0.01" x-model="highlightStrength" name="map[highlight_strength]" class="mt-4 w-full accent-[var(--color-primary-900)]">
                        <p class="mt-2 text-sm text-[var(--color-text-soft)]"><span x-text="highlightStrength.toFixed(2)"></span> overlay intensity for highlights and mask blending.</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2" x-show="showCompare" x-transition.opacity>
                <div class="surface-card p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Before / after</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Comparison preview</h3>
                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        <div class="surface-card-soft p-4">
                            <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Flat certificate</p>
                            <div class="mt-4 rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white p-6 text-center">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Original sample</p>
                                <p class="mt-5 text-lg font-semibold text-[var(--color-secondary-900)]" x-text="brideName"></p>
                                <p class="mt-2 text-lg font-semibold text-[var(--color-secondary-900)]" x-text="groomName"></p>
                                <p class="mt-4 text-sm text-[var(--color-text-soft)]" x-text="ceremonyDate"></p>
                                <p class="mt-1 text-sm text-[var(--color-text-soft)]" x-text="venue"></p>
                            </div>
                        </div>
                        <div class="surface-card-soft p-4">
                            <p class="text-sm font-semibold text-[var(--color-secondary-900)]">Mapped composition</p>
                            <div class="mt-4 rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-[var(--bg-section-soft)] p-3">
                                <div class="relative aspect-[4/3] overflow-hidden rounded-[var(--radius-xl)] bg-white">
                                    <template x-if="baseImageUrl">
                                        <img :src="baseImageUrl" alt="" class="h-full w-full object-cover">
                                    </template>
                                    <div class="absolute inset-0 pointer-events-none" :style="previewStyle()">
                                        <div class="h-full w-full rounded-[20px] border border-[rgba(120,0,0,0.14)] bg-white/92 px-[8%] py-[7%] text-center">
                                            <p class="text-[9px] font-semibold uppercase tracking-[0.26em] text-[var(--color-primary-900)]">Preview</p>
                                            <p class="mt-4 text-[clamp(0.9rem,1.1vw,1.1rem)] font-semibold text-[var(--color-secondary-900)]" x-text="brideName"></p>
                                            <p class="mt-1 text-[clamp(0.9rem,1.1vw,1.1rem)] font-semibold text-[var(--color-secondary-900)]" x-text="groomName"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="surface-card p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Preview copy</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Test certificate text</h3>
                    <div class="mt-6 grid gap-4">
                        <label class="field-shell">
                            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Bride name</span>
                            <input type="text" x-model="brideName" class="field-input">
                        </label>
                        <label class="field-shell">
                            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Groom name</span>
                            <input type="text" x-model="groomName" class="field-input">
                        </label>
                        <label class="field-shell">
                            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Ceremony date</span>
                            <input type="text" x-model="ceremonyDate" class="field-input">
                        </label>
                        <label class="field-shell">
                            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Venue</span>
                            <input type="text" x-model="venue" class="field-input">
                        </label>
                        <p class="text-sm leading-7 text-[var(--color-text-soft)]">Use this panel to test different Nikah copy lengths before saving the mapping. Arrow keys nudge the selected corner, and holding shift makes the move larger.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="surface-card p-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Mockup settings</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Identity, media, and assignment</h3>
                </div>

                <div class="mt-6 grid gap-5">
                    <label class="field-shell">
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
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Assigned template</span>
                        <select name="personalization_template_id" class="field-select" x-model="activeTemplateId">
                            <option value="">Select a template</option>
                            @foreach ($templates as $templateOption)
                                <option value="{{ $templateOption->id }}" @selected((string) old('personalization_template_id', $mockup->personalization_template_id) === (string) $templateOption->id)>
                                    {{ $templateOption->name }}{{ $templateOption->product ? ' • '.$templateOption->product->name : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('personalization_template_id') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                    </label>

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="field-shell">
                            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Render mode</span>
                            <select name="render_mode" class="field-select" @change="markDirty()">
                                @foreach (['flat_fit' => 'Flat fit', 'perspective_quad' => 'Perspective quad', 'masked_perspective' => 'Masked perspective'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('render_mode', $mockup->render_mode) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('render_mode') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                        </label>

                        <label class="field-shell">
                            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Sort order</span>
                            <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $mockup->sort_order ?? 0) }}" class="field-input">
                            @error('sort_order') <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                        </label>
                    </div>

                    <label class="inline-flex items-center gap-3 rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 px-4 py-3 text-sm font-medium text-[var(--color-secondary-900)]">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $mockup->is_active)) class="h-4 w-4 rounded border-[var(--color-border-soft)]">
                        Active mockup
                    </label>
                </div>
            </div>

            <div class="surface-card p-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Asset uploads</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Base, mask, overlay, and thumbnail</h3>
                </div>
                <div class="mt-6 space-y-4">
                    @foreach ([
                        ['label' => 'Base image', 'upload' => 'base_image_upload', 'url' => 'base_image_url', 'current' => $mockup->base_image_url, 'state' => 'baseImageUrl', 'remove' => 'remove_base_image', 'remove_state' => 'removeBaseImage'],
                        ['label' => 'Mask image', 'upload' => 'mask_image_upload', 'url' => 'mask_image_url', 'current' => $mockup->mask_image_url, 'state' => 'maskImageUrl', 'remove' => 'remove_mask_image', 'remove_state' => 'removeMaskImage'],
                        ['label' => 'Overlay image', 'upload' => 'overlay_image_upload', 'url' => 'overlay_image_url', 'current' => $mockup->overlay_image_url, 'state' => 'overlayImageUrl', 'remove' => 'remove_overlay_image', 'remove_state' => 'removeOverlayImage'],
                        ['label' => 'Thumbnail image', 'upload' => 'thumb_image_upload', 'url' => 'thumb_image_url', 'current' => $mockup->thumb_image_url, 'state' => 'thumbImageUrl', 'remove' => 'remove_thumb_image', 'remove_state' => 'removeThumbImage'],
                    ] as $asset)
                        <div class="surface-card-soft p-4">
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-sm font-semibold text-[var(--color-secondary-900)]">{{ $asset['label'] }}</p>
                                <button type="button" class="button-ghost !px-3 !py-2" x-show="{{ $asset['state'] }}" @click="clearAsset('{{ $asset['state'] }}')">Remove</button>
                            </div>
                            <input type="hidden" name="{{ $asset['remove'] }}" :value="{{ $asset['remove_state'] }} ? 1 : 0">
                            <template x-if="{{ $asset['state'] }}">
                                <div class="mt-3 overflow-hidden rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white">
                                    <img :src="{{ $asset['state'] }}" alt="{{ $asset['label'] }}" class="aspect-[4/3] w-full object-cover">
                                </div>
                            </template>
                            <label class="field-shell mt-4">
                                <span class="text-sm font-medium text-[var(--color-secondary-900)]">Upload {{ str($asset['label'])->lower() }}</span>
                                <input type="file" name="{{ $asset['upload'] }}" accept="image/*" class="field-input" @change="swapAssetPreview('{{ $asset['state'] }}', $event)">
                                @error($asset['upload']) <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                            </label>
                            <label class="field-shell mt-3">
                                <span class="text-sm font-medium text-[var(--color-secondary-900)]">Or set path / URL</span>
                                <input type="text" name="{{ $asset['url'] }}" value="{{ old($asset['url'], $asset['current']) }}" class="field-input" x-model="{{ $asset['state'] }}" @input="syncAssetUrl('{{ $asset['state'] }}', $event.target.value)">
                                @error($asset['url']) <span class="text-xs text-[var(--color-danger)]">{{ $message }}</span> @enderror
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="surface-card p-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Coordinate editor</p>
                    <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Normalized corner placement</h3>
                </div>
                <div class="mt-6 grid gap-4">
                    <input type="hidden" name="map[map_type]" value="quad">
                    <input type="hidden" name="map[normalized_coordinates]" value="1">

                    <label class="field-shell">
                        <span class="text-sm font-medium text-[var(--color-secondary-900)]">Fit mode</span>
                        <select name="map[fit_mode]" class="field-select">
                            @foreach (['contain' => 'Contain', 'cover' => 'Cover', 'stretch' => 'Stretch'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('map.fit_mode', $map->fit_mode ?? 'contain') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    @foreach ([
                        'top_left' => 'Top left',
                        'top_right' => 'Top right',
                        'bottom_right' => 'Bottom right',
                        'bottom_left' => 'Bottom left',
                    ] as $key => $label)
                        <div class="rounded-[var(--radius-xl)] border border-[var(--color-border-soft)] bg-white/80 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-semibold text-[var(--color-secondary-900)]">{{ $label }}</p>
                                <button type="button" class="button-ghost !px-3 !py-2" @click="selectedHandle = '{{ $key }}'">Select</button>
                            </div>
                            <div class="mt-4 grid gap-3 grid-cols-2">
                                <label class="field-shell">
                                    <span class="text-xs font-medium text-[var(--color-secondary-900)]">X</span>
                                    <input type="number" min="0" max="1" step="0.0001" name="map[{{ $key }}_x]" x-model.number="points.{{ $key }}.x" class="field-input">
                                </label>
                                <label class="field-shell">
                                    <span class="text-xs font-medium text-[var(--color-secondary-900)]">Y</span>
                                    <input type="number" min="0" max="1" step="0.0001" name="map[{{ $key }}_y]" x-model.number="points.{{ $key }}.y" class="field-input">
                                </label>
                            </div>
                        </div>
                    @endforeach

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="field-shell">
                            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Manual rotation</span>
                            <input type="number" step="0.01" min="-180" max="180" name="map[manual_rotation]" value="{{ old('map.manual_rotation', $map->manual_rotation) }}" class="field-input">
                        </label>
                        <label class="field-shell">
                            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Object position X</span>
                            <input type="number" step="0.0001" min="0" max="1" name="map[object_position_x]" value="{{ old('map.object_position_x', $map->object_position_x) }}" class="field-input">
                        </label>
                        <label class="field-shell md:col-span-2">
                            <span class="text-sm font-medium text-[var(--color-secondary-900)]">Object position Y</span>
                            <input type="number" step="0.0001" min="0" max="1" name="map[object_position_y]" value="{{ old('map.object_position_y', $map->object_position_y) }}" class="field-input">
                        </label>
                    </div>
                </div>
            </div>

            <div class="surface-card p-6">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary-900)]">Notes and actions</p>
                <h3 class="mt-2 text-2xl font-semibold text-[var(--color-secondary-900)]">Save or duplicate</h3>
                <label class="field-shell mt-6">
                    <span class="text-sm font-medium text-[var(--color-secondary-900)]">Notes</span>
                    <textarea name="notes" rows="5" class="field-textarea">{{ old('notes', $mockup->notes) }}</textarea>
                </label>

                <div class="mt-6 space-y-3 text-sm leading-7 text-[var(--color-text-soft)]">
                    <p>{{ $mockup->base_image_url ? 'Base scene is ready.' : 'Base scene is still missing.' }}</p>
                    <p>{{ $mockup->mask_image_url ? 'Mask support is attached.' : 'Mask support is optional and currently missing.' }}</p>
                    <p>{{ $mockup->overlay_image_url ? 'Overlay support is attached.' : 'Overlay support is optional and currently missing.' }}</p>
                    <p>{{ $template?->fields?->count() ? 'Template fields are available for testing.' : 'Assigned template fields are still empty.' }}</p>
                </div>
            </div>
        </div>
    </section>

    <div class="flex flex-wrap gap-3">
        <button type="submit" class="button-primary">{{ $isEdit ? 'Save mockup changes' : 'Create mockup' }}</button>
        <a href="{{ route('admin.mockups.index') }}" class="button-ghost">Cancel</a>
    </div>
</form>
