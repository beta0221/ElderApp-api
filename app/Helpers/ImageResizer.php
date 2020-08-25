<?php
namespace App\Helpers;
use Image;

class ImageResizer{

    public static function aspectFit($file, $requiredSize) {
        $image = Image::make($file);
        $width = $image->width();
        $height = $image->height();
    
        // Check if image resize is required or not
        if ($requiredSize >= $width && $requiredSize >= $height) return $image;
    
        $newWidth = 0;
        $newHeight = 0;
    
        $aspectRatio = $width/$height;
        if ($aspectRatio >= 1.0) {
            $newWidth = $requiredSize;
            $newHeight = $requiredSize / $aspectRatio;
        } else {
            $newWidth = $requiredSize * $aspectRatio;
            $newHeight = $requiredSize;
        }
    
        $image->resize($newWidth, $newHeight);
        return $image;
    }

}