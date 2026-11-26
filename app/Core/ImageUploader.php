<?php

declare(strict_types=1);

namespace App\Core;

/** Stores user images. The type is detected from file content, never from the client's name. */
final class ImageUploader
{
    private const TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    private string $directory;
    private int $maxBytes;

    public function __construct(array $config)
    {
        $this->directory = $config['dir'];
        $this->maxBytes = $config['max_bytes'];
    }

    /** Validates the upload and returns the extension to use. */
    public function validate(array $file): string
    {
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE || ($file['size'] ?? 0) > $this->maxBytes) {
            throw new ValidationError(['image' => 'Image must be 2 MB or smaller.']);
        }
        if ($error !== UPLOAD_ERR_OK || !is_file($file['tmp_name'] ?? '')) {
            throw new ValidationError(['image' => 'The image could not be uploaded.']);
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset(self::TYPES[$mime]) || @getimagesize($file['tmp_name']) === false) {
            throw new ValidationError(['image' => 'Only JPG, PNG and WebP images are allowed.']);
        }

        return self::TYPES[$mime];
    }

    /** @return string path relative to the public folder */
    public function store(array $file, string $folder): string
    {
        $extension = $this->validate($file);
        $target = $this->directory . '/' . $folder;
        if (!is_dir($target) && !mkdir($target, 0775, true) && !is_dir($target)) {
            throw new DomainError('The upload folder is not writable.');
        }
        $name = bin2hex(random_bytes(16)) . '.' . $extension;
        if (!move_uploaded_file($file['tmp_name'], "$target/$name")) {
            throw new DomainError('The image could not be saved.');
        }

        return "uploads/$folder/$name";
    }

    public function delete(?string $relativePath): void
    {
        if ($relativePath === null || !preg_match('#^uploads/(food|restaurants)/[a-f0-9]{32}\.(jpg|png|webp)$#', $relativePath)) {
            return;
        }
        $path = dirname($this->directory) . '/' . $relativePath;
        if (is_file($path)) {
            unlink($path);
        }
    }
}
