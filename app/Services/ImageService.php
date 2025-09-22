<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ImageService
{
    /**
     * Get the URL for an image stored in public disk
     */
    public static function getImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        // If it's already a full URL, return as is
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // Use asset() for cPanel compatibility - direct path to public/uploads
        return asset('uploads/' . $path);
    }

    /**
     * Get the URL for a profile picture
     */
    public static function getProfilePictureUrl(?string $path): string
    {
        $url = self::getImageUrl($path);
        return $url ?: asset('images/default-profile.png');
    }

    /**
     * Get the URL for a shop image
     */
    public static function getShopImageUrl(?string $path): string
    {
        $url = self::getImageUrl($path);
        return $url ?: asset('images/default-shop.png');
    }

    /**
     * Get the URL for a payment method image
     */
    public static function getPaymentMethodImageUrl(?string $path): string
    {
        $url = self::getImageUrl($path);
        return $url ?: asset('images/cash.png');
    }

    /**
     * Get the URL for a payment proof image
     */
    public static function getPaymentProofImageUrl(?string $path): string
    {
        if (!$path) {
            return asset('images/cash.png');
        }
        
        // If it's already a full URL, return as is
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        
        // Use uploads directory for payment proofs - consistent with other methods
        return asset('uploads/' . $path);
    }

    /**
     * Store an uploaded file in the public disk
     */
    public static function storeUploadedFile($file, string $directory): string
    {
        // Store the file using Laravel's storage
        return $file->store($directory, 'public');
    }

    /**
     * Delete an image file
     */
    public static function deleteImage(?string $path): bool
    {
        if (!$path) {
            return true;
        }

        // Delete from storage
        return Storage::disk('public')->delete($path);
    }
}