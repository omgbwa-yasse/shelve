<?php

namespace App\Helpers;

class FileHelper
{
    /**
     * Format file size in bytes to human readable format
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    public static function formatBytes($bytes, $precision = 2)
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Get file extension from filename
     *
     * @param string $filename
     * @return string
     */
    public static function getExtension($filename)
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Check if file format is supported for content extraction
     *
     * @param string $filename
     * @return bool
     */
    public static function isSupportedFormat($filename)
    {
        $supportedFormats = config('mcp.supported_formats', ['pdf', 'txt', 'docx', 'doc', 'rtf', 'odt']);
        return in_array(self::getExtension($filename), $supportedFormats);
    }

    /**
     * Get appropriate icon class for file type
     *
     * @param string $filename
     * @return string
     */
    public static function getIconClass($filename)
    {
        $extension = self::getExtension($filename);

        return match($extension) {
            'pdf' => 'fas fa-file-pdf text-danger',
            'txt' => 'fas fa-file-alt text-secondary',
            'docx', 'doc' => 'fas fa-file-word text-primary',
            'rtf' => 'fas fa-file-alt text-info',
            'odt' => 'fas fa-file-alt text-success',
            'xls', 'xlsx' => 'fas fa-file-excel text-success',
            'ppt', 'pptx' => 'fas fa-file-powerpoint text-warning',
            'zip', 'rar', '7z' => 'fas fa-file-archive text-dark',
            'jpg', 'jpeg', 'png', 'gif', 'bmp' => 'fas fa-file-image text-info',
            'mp3', 'wav', 'flac' => 'fas fa-file-audio text-warning',
            'mp4', 'avi', 'mkv', 'mov' => 'fas fa-file-video text-danger',
            default => 'fas fa-file text-muted'
        };
    }
}
