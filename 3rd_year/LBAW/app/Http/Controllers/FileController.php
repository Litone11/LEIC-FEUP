<?php

namespace App\Http\Controllers;

use App\Models\RegularUser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class FileController extends Controller
{
    protected static string $default = 'default-profile.svg';
    protected static string $diskName = 'Tutorial02';

    protected static array $systemTypes = [
        'profile' => ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'],
    ];

    protected static array $directories = [
        'profile' => 'profiles',
    ];

    private static function baseDir(string $type): string
    {
        return self::$directories[$type] ?? $type;
    }

    private static function isValidType(string $type): bool
    {
        return array_key_exists($type, self::$systemTypes);
    }

    private static function isValidExtension(string $type, string $extension): bool
    {
        return self::isValidType($type)
            && in_array(strtolower($extension), self::$systemTypes[$type], true);
    }

    private static function defaultAsset(string $type): string
    {
        $path = self::baseDir($type) . '/' . self::$default;

        if (Storage::disk(self::$diskName)->exists($path)) {
            return asset($path);
        }

        return asset('images/' . self::$default);
    }

    private static function normalizePath(string $type, ?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $path = ltrim($path, '/');

        if ($path === self::$default) {
            return null;
        }

        if (str_contains($path, '/')) {
            return $path;
        }

        return self::baseDir($type) . '/' . $path;
    }

    private static function getFileName(string $type, int $id): ?string
    {
        return match ($type) {
            'profile' => RegularUser::find($id)?->profile_pic,
            default => null,
        };
    }

    public static function get(string $type, int $userId): string
    {
        if (!self::isValidType($type)) {
            return self::defaultAsset('profile');
        }

        $relativePath = self::normalizePath($type, self::getFileName($type, $userId));

        if ($relativePath && Storage::disk(self::$diskName)->exists($relativePath)) {
            return asset($relativePath);
        }

        if ($relativePath && Storage::disk('public')->exists($relativePath)) {
            return Storage::disk('public')->url($relativePath);
        }

        return self::defaultAsset($type);
    }

    public static function storeProfile(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();

        if (!self::isValidExtension('profile', $extension)) {
            throw ValidationException::withMessages([
                'profile_pic' => 'Formato de imagem não suportado.',
            ]);
        }

        $fileName = $file->hashName();
        $file->storeAs(self::baseDir('profile'), $fileName, self::$diskName);

        return $fileName;
    }

    public static function deleteProfile(?string $path): void
    {
        $relativePath = self::normalizePath('profile', $path);

        if (!$relativePath) {
            return;
        }

        if (Storage::disk(self::$diskName)->exists($relativePath)) {
            Storage::disk(self::$diskName)->delete($relativePath);
            return;
        }

        if (Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }
    }

    public static function hasCustomProfile(?string $path): bool
    {
        $relativePath = self::normalizePath('profile', $path);

        if (!$relativePath) {
            return false;
        }

        return basename($relativePath) !== self::$default;
    }

    public static function defaultProfile(): string
    {
        return self::defaultAsset('profile');
    }
}
