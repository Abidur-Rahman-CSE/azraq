<x-layouts.admin
    title="Media Library | Azraq Bridal"
    page-title="Media Library"
    page-subtitle="Content workspace"
    :breadcrumbs="[
        ['label' => 'Admin', 'href' => route('admin.dashboard')],
        ['label' => 'Content'],
        ['label' => 'Media Library'],
    ]"
>
    <div
        class="space-y-6"
        x-data="{
            copied: '',
            fallbackCopy(value) {
                const input = document.createElement('textarea');
                input.value = value;
                input.setAttribute('readonly', '');
                input.style.position = 'fixed';
                input.style.top = '0';
                input.style.left = '-9999px';
                document.body.appendChild(input);
                input.focus();
                input.select();
                input.setSelectionRange(0, input.value.length);

                let copied = false;

                try {
                    copied = document.execCommand('copy');
                } catch (error) {
                    copied = false;
                }

                document.body.removeChild(input);

                if (!copied) {
                    window.prompt('Copy this value:', value);
                }
            },
            copyText(value, label) {
                const done = () => {
                    this.copied = label;
                    setTimeout(() => this.copied = '', 1600);
                };

                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(value)
                        .then(done)
                        .catch(() => {
                            this.fallbackCopy(value);
                            done();
                        });
                    return;
                }

                this.fallbackCopy(value);
                done();
            }
        }"
    >
        <div class="surface-card p-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-[0.24em] text-[var(--color-primary-900)]">Content</p>
                    <h2 class="mt-2 text-3xl font-semibold text-[var(--color-secondary-900)]">Media Library</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-7 text-[var(--color-text-soft)]">
                        Existing uploaded and public image assets are listed here so you can preview them, open them in a new tab, or copy the exact path/URL into admin forms.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-2xl bg-[var(--color-surface-cream)] px-4 py-3">
                        <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">All media</p>
                        <p class="mt-1 text-2xl font-semibold text-[var(--color-secondary-900)]">{{ number_format($stats['total']) }}</p>
                    </div>
                    <div class="rounded-2xl bg-[var(--color-surface-cream)] px-4 py-3">
                        <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Filtered</p>
                        <p class="mt-1 text-2xl font-semibold text-[var(--color-secondary-900)]">{{ number_format($stats['filtered']) }}</p>
                    </div>
                    <div class="rounded-2xl bg-[var(--color-surface-cream)] px-4 py-3">
                        <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Uploads</p>
                        <p class="mt-1 text-2xl font-semibold text-[var(--color-secondary-900)]">{{ number_format($stats['uploads']) }}</p>
                    </div>
                    <div class="rounded-2xl bg-[var(--color-surface-cream)] px-4 py-3">
                        <p class="text-xs uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Public images</p>
                        <p class="mt-1 text-2xl font-semibold text-[var(--color-secondary-900)]">{{ number_format($stats['public']) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.content.media.index') }}" class="surface-card grid gap-4 p-6 lg:grid-cols-[minmax(0,1.1fr)_220px_260px_auto] lg:items-end">
            <label class="space-y-2">
                <span class="text-sm font-medium text-[var(--color-secondary-900)]">Search file name or folder</span>
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3"
                    placeholder="Search e.g. mockups, homepage, template"
                >
            </label>

            <label class="space-y-2">
                <span class="text-sm font-medium text-[var(--color-secondary-900)]">Source</span>
                <select name="source" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                    <option value="all" @selected($source === 'all')>All</option>
                    <option value="uploads" @selected($source === 'uploads')>Uploads</option>
                    <option value="public" @selected($source === 'public')>Public images</option>
                </select>
            </label>

            <label class="space-y-2">
                <span class="text-sm font-medium text-[var(--color-secondary-900)]">Folder</span>
                <select name="folder" class="w-full rounded-2xl border border-[var(--color-border-soft)] px-4 py-3">
                    <option value="">All folders</option>
                    @foreach ($folderOptions as $option)
                        <option value="{{ $option['folder'] }}" @selected($folder === $option['folder'])>
                            {{ $option['folder'] }} ({{ $option['count'] }})
                        </option>
                    @endforeach
                </select>
            </label>

            <div class="flex gap-3">
                <button type="submit" class="button-primary">Filter</button>
                <a href="{{ route('admin.content.media.index') }}" class="button-ghost">Reset</a>
            </div>
        </form>

        <div class="surface-card p-4 sm:p-6">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-[var(--color-text-soft)]">
                    Showing <span class="font-semibold text-[var(--color-secondary-900)]">{{ number_format($items->count()) }}</span>
                    items on this page out of <span class="font-semibold text-[var(--color-secondary-900)]">{{ number_format($items->total()) }}</span>.
                </p>

                <div
                    class="rounded-full bg-[var(--color-surface-cream)] px-4 py-2 text-xs font-medium text-[var(--color-secondary-900)]"
                    x-show="copied"
                    x-transition.opacity.duration.200ms
                    x-text="copied"
                ></div>
            </div>

            @if ($items->isEmpty())
                <div class="rounded-3xl border border-dashed border-[var(--color-border-soft)] px-6 py-12 text-center">
                    <p class="text-lg font-semibold text-[var(--color-secondary-900)]">No media found</p>
                    <p class="mt-2 text-sm text-[var(--color-text-soft)]">Try broadening the search or switching to another folder/source.</p>
                </div>
            @else
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                    @foreach ($items as $item)
                        <article class="surface-card-soft overflow-hidden">
                            <div class="aspect-[4/3] overflow-hidden bg-[var(--color-surface-cream)]">
                                <img src="{{ $item['preview_url'] }}" alt="{{ $item['name'] }}" class="h-full w-full object-cover">
                            </div>

                            <div class="space-y-3 p-4">
                                <div>
                                    <p class="truncate text-sm font-semibold text-[var(--color-secondary-900)]">{{ $item['name'] }}</p>
                                    <p class="mt-1 text-xs text-[var(--color-text-soft)]">{{ $item['folder'] }}</p>
                                </div>

                                <div class="flex flex-wrap gap-2 text-xs">
                                    <span class="rounded-full bg-white px-3 py-1 text-[var(--color-secondary-900)]">{{ ucfirst($item['source']) }}</span>
                                    <span class="rounded-full bg-white px-3 py-1 text-[var(--color-secondary-900)]">{{ $item['size_label'] }}</span>
                                    <span class="rounded-full bg-white px-3 py-1 text-[var(--color-secondary-900)]">{{ $item['modified_label'] }}</span>
                                </div>

                                <div class="space-y-2">
                                    <div class="rounded-2xl border border-[var(--color-border-soft)] bg-white px-3 py-2">
                                        <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Relative path</p>
                                        <p class="mt-1 break-all text-xs text-[var(--color-secondary-900)]">{{ $item['relative_path'] }}</p>
                                    </div>

                                    <div class="rounded-2xl border border-[var(--color-border-soft)] bg-white px-3 py-2">
                                        <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--color-text-soft)]">Full URL</p>
                                        <p class="mt-1 break-all text-xs text-[var(--color-secondary-900)]">{{ $item['url'] }}</p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <button
                                        type="button"
                                        class="button-ghost"
                                        @click="copyText(@js($item['relative_path']), 'Path copied')"
                                    >
                                        Copy path
                                    </button>
                                    <button
                                        type="button"
                                        class="button-ghost"
                                        @click="copyText(@js($item['url']), 'URL copied')"
                                    >
                                        Copy URL
                                    </button>
                                    <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" class="button-ghost">Open</a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
