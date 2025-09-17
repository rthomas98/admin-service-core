<?php

namespace App\Services;

use App\Models\ServiceRequest;
use App\Models\ServiceRequestActivity;
use App\Models\ServiceRequestAttachment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServiceRequestFileUploadService
{
    public function __construct(
        private readonly string $disk = 'public',
        private readonly string $directory = 'service-requests'
    ) {}

    /**
     * Upload a file for a service request
     */
    public function uploadFile(
        ServiceRequest $serviceRequest,
        UploadedFile $file,
        User $uploadedBy,
        ?string $description = null
    ): ServiceRequestAttachment {
        // Validate file
        $this->validateFile($file);

        // Generate unique filename
        $filename = $this->generateUniqueFilename($file);

        // Create directory path
        $directory = $this->getUploadDirectory($serviceRequest);

        // Store file
        $filePath = $file->storeAs($directory, $filename, $this->disk);

        // Create attachment record
        $attachment = ServiceRequestAttachment::create([
            'service_request_id' => $serviceRequest->id,
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_by' => $uploadedBy->id,
            'description' => $description,
        ]);

        // Log the activity
        ServiceRequestActivity::logAttachmentAdded(
            $serviceRequest,
            $uploadedBy,
            $file->getClientOriginalName()
        );

        return $attachment;
    }

    /**
     * Upload multiple files for a service request
     */
    public function uploadMultipleFiles(
        ServiceRequest $serviceRequest,
        array $files,
        User $uploadedBy,
        array $descriptions = []
    ): array {
        $attachments = [];

        foreach ($files as $index => $file) {
            if ($file instanceof UploadedFile) {
                $description = $descriptions[$index] ?? null;
                $attachments[] = $this->uploadFile(
                    $serviceRequest,
                    $file,
                    $uploadedBy,
                    $description
                );
            }
        }

        return $attachments;
    }

    /**
     * Delete an attachment
     */
    public function deleteAttachment(ServiceRequestAttachment $attachment): bool
    {
        // Delete file from storage
        if (Storage::disk($this->disk)->exists($attachment->file_path)) {
            Storage::disk($this->disk)->delete($attachment->file_path);
        }

        // Delete attachment record
        return $attachment->delete();
    }

    /**
     * Get file URL for download/preview
     */
    public function getFileUrl(ServiceRequestAttachment $attachment): string
    {
        return Storage::disk($this->disk)->url($attachment->file_path);
    }

    /**
     * Get file contents for download
     */
    public function downloadFile(ServiceRequestAttachment $attachment): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return Storage::disk($this->disk)->download(
            $attachment->file_path,
            $attachment->original_filename
        );
    }

    /**
     * Check if file exists
     */
    public function fileExists(ServiceRequestAttachment $attachment): bool
    {
        return Storage::disk($this->disk)->exists($attachment->file_path);
    }

    /**
     * Get file size in human readable format
     */
    public function getFileSizeFormatted(ServiceRequestAttachment $attachment): string
    {
        return $attachment->getFileSizeFormatted();
    }

    /**
     * Generate file preview URL for images
     */
    public function getPreviewUrl(ServiceRequestAttachment $attachment): ?string
    {
        if (! $attachment->isImage()) {
            return null;
        }

        return $this->getFileUrl($attachment);
    }

    /**
     * Get all allowed file extensions
     */
    public static function getAllowedExtensions(): array
    {
        return ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'];
    }

    /**
     * Get all allowed MIME types
     */
    public static function getAllowedMimeTypes(): array
    {
        return [
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
        ];
    }

    /**
     * Get maximum file size in MB
     */
    public static function getMaxFileSize(): int
    {
        return 10; // 10MB
    }

    /**
     * Get maximum file size in bytes
     */
    public static function getMaxFileSizeBytes(): int
    {
        return self::getMaxFileSize() * 1024 * 1024;
    }

    /**
     * Validate uploaded file
     */
    private function validateFile(UploadedFile $file): void
    {
        // Check file size
        if ($file->getSize() > self::getMaxFileSizeBytes()) {
            throw new \InvalidArgumentException(
                'File size exceeds maximum allowed size of '.self::getMaxFileSize().'MB'
            );
        }

        // Check MIME type
        if (! in_array($file->getMimeType(), self::getAllowedMimeTypes())) {
            throw new \InvalidArgumentException(
                'File type not allowed. Allowed types: '.implode(', ', self::getAllowedExtensions())
            );
        }

        // Check extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, self::getAllowedExtensions())) {
            throw new \InvalidArgumentException(
                'File extension not allowed. Allowed extensions: '.implode(', ', self::getAllowedExtensions())
            );
        }
    }

    /**
     * Generate unique filename
     */
    private function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $cleanName = Str::slug($name);

        return $cleanName.'_'.time().'_'.Str::random(8).'.'.$extension;
    }

    /**
     * Get upload directory for service request
     */
    private function getUploadDirectory(ServiceRequest $serviceRequest): string
    {
        return $this->directory.'/'.$serviceRequest->id;
    }

    /**
     * Get total file size for service request
     */
    public function getTotalFileSize(ServiceRequest $serviceRequest): int
    {
        return $serviceRequest->attachments()->sum('file_size');
    }

    /**
     * Get total file size formatted for service request
     */
    public function getTotalFileSizeFormatted(ServiceRequest $serviceRequest): string
    {
        $totalBytes = $this->getTotalFileSize($serviceRequest);

        if ($totalBytes >= 1073741824) {
            return number_format($totalBytes / 1073741824, 2).' GB';
        } elseif ($totalBytes >= 1048576) {
            return number_format($totalBytes / 1048576, 2).' MB';
        } elseif ($totalBytes >= 1024) {
            return number_format($totalBytes / 1024, 2).' KB';
        } else {
            return $totalBytes.' bytes';
        }
    }

    /**
     * Get file statistics for service request
     */
    public function getFileStatistics(ServiceRequest $serviceRequest): array
    {
        $attachments = $serviceRequest->attachments;

        $stats = [
            'total_files' => $attachments->count(),
            'total_size' => $this->getTotalFileSize($serviceRequest),
            'total_size_formatted' => $this->getTotalFileSizeFormatted($serviceRequest),
            'file_types' => [],
            'images' => 0,
            'documents' => 0,
            'other' => 0,
        ];

        foreach ($attachments as $attachment) {
            // Count by type
            if ($attachment->isImage()) {
                $stats['images']++;
            } elseif ($attachment->isDocument()) {
                $stats['documents']++;
            } else {
                $stats['other']++;
            }

            // Count by extension
            $extension = pathinfo($attachment->original_filename, PATHINFO_EXTENSION);
            $stats['file_types'][$extension] = ($stats['file_types'][$extension] ?? 0) + 1;
        }

        return $stats;
    }
}
