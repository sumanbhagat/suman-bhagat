<?php
/**
 * File Upload Handler
 * Securely handles file uploads with validation
 */
require_once __DIR__ . '/security.php';

class FileUploader {
    private $upload_dir;
    private $allowed_types;
    private $max_size;
    private $create_thumbnails;
    private $thumbnail_width;
    
    public function __construct($upload_dir = '../uploads/', $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], $max_size = 5242880) {
        $this->upload_dir = rtrim($upload_dir, '/') . '/';
        $this->allowed_types = $allowed_types;
        $this->max_size = $max_size;
        $this->create_thumbnails = true;
        $this->thumbnail_width = 300;
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->upload_dir)) {
            mkdir($this->upload_dir, 0755, true);
        }
        
        // Create subdirectories
        foreach (['images', 'thumbnails', 'files'] as $subdir) {
            if (!is_dir($this->upload_dir . $subdir)) {
                mkdir($this->upload_dir . $subdir, 0755, true);
            }
        }
    }
    
    /**
     * Upload a file
     */
    public function upload($file, $type = 'images') {
        // Validate file
        $errors = validateFileUpload($file, $this->allowed_types, $this->max_size);
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Generate secure filename
        $filename = generateSecureFileName($file['name']);
        $filepath = $this->upload_dir . $type . '/' . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => false, 'errors' => ['Failed to move uploaded file']];
        }
        
        // Set proper permissions
        chmod($filepath, 0644);
        
        // Create thumbnail for images
        $thumbnail_path = null;
        if ($type === 'images' && $this->create_thumbnails) {
            $thumbnail_filename = 'thumb_' . $filename;
            $thumbnail_path = $this->upload_dir . 'thumbnails/' . $thumbnail_filename;
            $this->createThumbnail($filepath, $thumbnail_path, $this->thumbnail_width);
        }
        
        $relative_path = 'uploads/' . $type . '/' . $filename;
        $thumbnail_relative = $thumbnail_path ? 'uploads/thumbnails/' . $thumbnail_filename : null;
        
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $filepath,
            'relative_path' => $relative_path,
            'thumbnail_path' => $thumbnail_relative,
            'url' => $relative_path,
            'thumbnail_url' => $thumbnail_relative
        ];
    }
    
    /**
     * Upload multiple files
     */
    public function uploadMultiple($files, $type = 'images') {
        $results = [];
        
        // Handle array of files
        $file_count = count($files['name']);
        
        for ($i = 0; $i < $file_count; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];
                
                $results[] = $this->upload($file, $type);
            }
        }
        
        return $results;
    }
    
    /**
     * Delete a file
     */
    public function delete($filepath) {
        $full_path = $this->upload_dir . str_replace('uploads/', '', $filepath);
        
        if (file_exists($full_path)) {
            unlink($full_path);
            
            // Also delete thumbnail if exists
            $thumbnail_path = str_replace('images/', 'thumbnails/thumb_', $full_path);
            if (file_exists($thumbnail_path)) {
                unlink($thumbnail_path);
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Create thumbnail
     */
    private function createThumbnail($source, $destination, $width) {
        // Check if GD extension is available
        if (!function_exists('imagecreatefromjpeg')) {
            return false; // GD not available, skip thumbnail
        }
        
        list($orig_width, $orig_height, $type) = getimagesize($source);
        
        // Calculate new height
        $height = ($orig_height / $orig_width) * $width;
        
        // Create image from source
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source_img = imagecreatefromjpeg($source);
                break;
            case IMAGETYPE_PNG:
                $source_img = imagecreatefrompng($source);
                break;
            case IMAGETYPE_GIF:
                $source_img = imagecreatefromgif($source);
                break;
            case IMAGETYPE_WEBP:
                $source_img = imagecreatefromwebp($source);
                break;
            default:
                return false;
        }
        
        // Create thumbnail
        $thumb = imagecreatetruecolor($width, $height);
        
        // Preserve transparency for PNG and GIF
        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
            imagecolortransparent($thumb, imagecolorallocatealpha($thumb, 0, 0, 0, 127));
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
        }
        
        // Resize
        imagecopyresampled($thumb, $source_img, 0, 0, 0, 0, $width, $height, $orig_width, $orig_height);
        
        // Save thumbnail
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($thumb, $destination, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumb, $destination, 6);
                break;
            case IMAGETYPE_GIF:
                imagegif($thumb, $destination);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($thumb, $destination, 85);
                break;
        }
        
        // Clean up
        imagedestroy($source_img);
        imagedestroy($thumb);
        
        chmod($destination, 0644);
        
        return true;
    }
    
    /**
     * Get file info
     */
    public function getFileInfo($filepath) {
        $full_path = $this->upload_dir . str_replace('uploads/', '', $filepath);
        
        if (!file_exists($full_path)) {
            return null;
        }
        
        return [
            'name' => basename($full_path),
            'size' => filesize($full_path),
            'modified' => filemtime($full_path),
            'type' => mime_content_type($full_path),
            'url' => $filepath
        ];
    }
    
    /**
     * List uploaded files
     */
    public function listFiles($type = 'images', $page = 1, $per_page = 20) {
        $dir = $this->upload_dir . $type;
        $files = [];
        
        if (is_dir($dir)) {
            $iterator = new DirectoryIterator($dir);
            
            foreach ($iterator as $fileinfo) {
                if ($fileinfo->isFile()) {
                    $files[] = [
                        'name' => $fileinfo->getFilename(),
                        'size' => $fileinfo->getSize(),
                        'modified' => $fileinfo->getMTime(),
                        'url' => 'uploads/' . $type . '/' . $fileinfo->getFilename()
                    ];
                }
            }
        }
        
        // Sort by modified date (newest first)
        usort($files, function($a, $b) {
            return $b['modified'] - $a['modified'];
        });
        
        // Pagination
        $total = count($files);
        $offset = ($page - 1) * $per_page;
        $files = array_slice($files, $offset, $per_page);
        
        return [
            'files' => $files,
            'total' => $total,
            'pages' => ceil($total / $per_page),
            'current_page' => $page
        ];
    }
}
?>
