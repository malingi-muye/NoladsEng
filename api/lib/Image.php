<?php
class Image {
    private $config;
    
    public function __construct($config) {
        $this->config = $config;
    }
    
    public function process($file) {
        $this->validate($file);
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '-' . bin2hex(random_bytes(8)) . '.' . $extension;
        
        // Ensure upload directory exists
        if (!is_dir($this->config['dir'])) {
            mkdir($this->config['dir'], 0777, true);
        }
        
        $path = $this->config['dir'] . '/' . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $path)) {
            throw new Exception('Failed to move uploaded file');
        }
        
        // Try to convert to WebP if it's an image
        if ($this->isImage($file['type'])) {
            try {
                $webpPath = $this->convertToWebP($path);
                if ($webpPath) {
                    // If conversion successful, delete original and use WebP
                    unlink($path);
                    $path = $webpPath;
                    $filename = basename($webpPath);
                }
            } catch (Exception $e) {
                // If conversion fails, keep original
                error_log('WebP conversion failed: ' . $e->getMessage());
            }
        }
        
        // Generate public URL
        $url = '/uploads/' . $filename;
        
        return [
            'filename' => $filename,
            'path' => $path,
            'url' => $url
        ];
    }
    
    private function validate($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Upload failed with error code ' . $file['error']);
        }
        
        if ($file['size'] > $this->config['maxSize']) {
            throw new Exception('File size exceeds limit');
        }
        
        if (!in_array($file['type'], $this->config['allowedTypes'])) {
            throw new Exception('File type not allowed');
        }
    }
    
    private function isImage($mimeType) {
        return strpos($mimeType, 'image/') === 0;
    }
    
    private function convertToWebP($source) {
        // Check if we have Imagick
        if (extension_loaded('imagick')) {
            return $this->convertWithImagick($source);
        }
        
        // Fallback to GD
        if (extension_loaded('gd')) {
            return $this->convertWithGD($source);
        }
        
        return null;
    }
    
    private function convertWithImagick($source) {
        $imagick = new Imagick($source);
        $imagick->setImageFormat('webp');
        $imagick->setImageCompressionQuality(80);
        
        $webpPath = preg_replace('/\.[^.]+$/', '.webp', $source);
        $imagick->writeImage($webpPath);
        
        return $webpPath;
    }
    
    private function convertWithGD($source) {
        $mimeType = mime_content_type($source);
        $image = null;
        
        switch ($mimeType) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($source);
                break;
            case 'image/png':
                $image = imagecreatefrompng($source);
                // Handle transparency
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            default:
                return null;
        }
        
        if (!$image) {
            return null;
        }
        
        $webpPath = preg_replace('/\.[^.]+$/', '.webp', $source);
        imagewebp($image, $webpPath, 80);
        imagedestroy($image);
        
        return $webpPath;
    }
}
