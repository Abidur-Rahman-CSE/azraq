<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaLibraryController extends Controller
{
    public function index(Request $request)
    {
        $source = (string) $request->query('source', 'all');
        $folder = trim((string) $request->query('folder', ''));
        $search = trim((string) $request->query('search', ''));
        $perPage = 48;
        $page = max(1, (int) $request->query('page', 1));

        $items = collect();

        if (in_array($source, ['all', 'uploads'], true)) {
            $items = $items->merge($this->uploadMediaItems());
        }

        if (in_array($source, ['all', 'public'], true)) {
            $items = $items->merge($this->publicMediaItems());
        }

        $folderOptions = $items
            ->countBy('folder')
            ->sortKeys()
            ->map(fn ($count, $key) => ['folder' => $key, 'count' => $count])
            ->values();

        $filtered = $items
            ->when($folder !== '', fn (Collection $collection) => $collection->filter(fn (array $item) => $item['folder'] === $folder))
            ->when($search !== '', function (Collection $collection) use ($search) {
                $needle = Str::lower($search);

                return $collection->filter(function (array $item) use ($needle) {
                    return Str::contains(Str::lower($item['path']), $needle)
                        || Str::contains(Str::lower($item['name']), $needle)
                        || Str::contains(Str::lower($item['folder']), $needle);
                });
            })
            ->sortByDesc('modified_ts')
            ->values();

        $paginator = new LengthAwarePaginator(
            $filtered->forPage($page, $perPage)->values(),
            $filtered->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('admin.content.media.index', [
            'items' => $paginator,
            'source' => $source,
            'folder' => $folder,
            'search' => $search,
            'folderOptions' => $folderOptions,
            'stats' => [
                'total' => $items->count(),
                'filtered' => $filtered->count(),
                'uploads' => $items->where('source', 'uploads')->count(),
                'public' => $items->where('source', 'public')->count(),
            ],
        ]);
    }

    private function uploadMediaItems(): Collection
    {
        return collect(Storage::disk('public')->allFiles())
            ->filter(fn (string $path) => $this->isImagePath($path))
            ->map(function (string $path): array {
                $normalized = ltrim($path, '/');
                $fullPath = Storage::disk('public')->path($normalized);

                return [
                    'id' => sha1('uploads:'.$normalized),
                    'source' => 'uploads',
                    'name' => basename($normalized),
                    'path' => '/storage/'.$normalized,
                    'relative_path' => '/storage/'.$normalized,
                    'folder' => dirname($normalized) === '.' ? 'root' : dirname($normalized),
                    'url' => asset('storage/'.$normalized),
                    'preview_url' => asset('storage/'.$normalized),
                    'modified_ts' => @filemtime($fullPath) ?: 0,
                    'modified_label' => @filemtime($fullPath) ? date('d M Y, h:i A', filemtime($fullPath)) : 'Unknown',
                    'size_label' => $this->formatBytes(@filesize($fullPath) ?: 0),
                ];
            })
            ->values();
    }

    private function publicMediaItems(): Collection
    {
        $base = public_path('images');

        if (! File::isDirectory($base)) {
            return collect();
        }

        return collect(File::allFiles($base))
            ->map(fn (\SplFileInfo $file) => $file->getPathname())
            ->filter(fn (string $path) => $this->isImagePath($path))
            ->map(function (string $path) use ($base): array {
                $relative = Str::of($path)->after($base.DIRECTORY_SEPARATOR)->replace(DIRECTORY_SEPARATOR, '/')->toString();

                return [
                    'id' => sha1('public:'.$relative),
                    'source' => 'public',
                    'name' => basename($relative),
                    'path' => '/images/'.$relative,
                    'relative_path' => '/images/'.$relative,
                    'folder' => 'images/'.(dirname($relative) === '.' ? 'root' : dirname($relative)),
                    'url' => asset('images/'.$relative),
                    'preview_url' => asset('images/'.$relative),
                    'modified_ts' => @filemtime($path) ?: 0,
                    'modified_label' => @filemtime($path) ? date('d M Y, h:i A', filemtime($path)) : 'Unknown',
                    'size_label' => $this->formatBytes(@filesize($path) ?: 0),
                ];
            })
            ->values();
    }

    private function isImagePath(string $path): bool
    {
        return (bool) preg_match('/\.(avif|gif|jpe?g|png|svg|webp)$/i', $path);
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '—';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);
        $value = $bytes / (1024 ** $power);

        return number_format($value, $power === 0 ? 0 : 1).' '.$units[$power];
    }
}
