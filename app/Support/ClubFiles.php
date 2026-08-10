<?php

namespace App\Support;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ClubFiles
{
    public static function diskName(): string
    {
        return (string) config('ccm.files_disk', 'public');
    }

    public static function disk(): FilesystemAdapter
    {
        return Storage::disk(static::diskName());
    }

    public static function legacyDiskName(): string
    {
        return 'public';
    }

    public static function exists(?string $path): bool
    {
        return static::readDisk($path) !== null;
    }

    public static function put(string $path, mixed $contents): void
    {
        static::disk()->put($path, $contents);
    }

    public static function writeStream(string $path, mixed $stream): bool
    {
        return static::disk()->writeStream($path, $stream);
    }

    public static function delete(?string $path): bool
    {
        if (! $path) {
            return false;
        }

        $deleted = false;

        if (static::disk()->exists($path)) {
            $deleted = static::disk()->delete($path) || $deleted;
        }

        if (static::diskName() !== static::legacyDiskName()) {
            $legacyDisk = Storage::disk(static::legacyDiskName());
            if ($legacyDisk->exists($path)) {
                $deleted = $legacyDisk->delete($path) || $deleted;
            }
        }

        return $deleted;
    }

    public static function storeUploadedFile(string $directory, UploadedFile $file): string
    {
        return static::disk()->putFile($directory, $file);
    }

    public static function download(string $path, ?string $name = null, array $headers = [])
    {
        return static::readDiskOrFail($path)->download($path, $name, $headers);
    }

    public static function response(string $path, ?string $name = null, array $headers = [], ?string $disposition = 'inline')
    {
        return static::readDiskOrFail($path)->response($path, $name, $headers, $disposition);
    }

    public static function readDisk(?string $path): ?FilesystemAdapter
    {
        if (! $path) {
            return null;
        }

        $disk = static::disk();
        if ($disk->exists($path)) {
            return $disk;
        }

        if (static::diskName() !== static::legacyDiskName()) {
            $legacyDisk = Storage::disk(static::legacyDiskName());
            if ($legacyDisk->exists($path)) {
                return $legacyDisk;
            }
        }

        return null;
    }

    public static function readDiskOrFail(string $path): FilesystemAdapter
    {
        $disk = static::readDisk($path);

        if ($disk) {
            return $disk;
        }

        abort(404, 'Requested file not found.');
    }
}
