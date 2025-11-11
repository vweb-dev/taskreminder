<?php
/**
 * Generate PWA Icons
 * This script creates all required icon sizes for PWA
 * Run: php generate-icons.php
 */

date_default_timezone_set('Asia/Karachi');

// Create icons directory if it doesn't exist
$iconsDir = __DIR__ . '/assets/icons';
if (!is_dir($iconsDir)) {
    mkdir($iconsDir, 0755, true);
}

// Icon sizes required for PWA
$sizes = [72, 96, 128, 144, 152, 192, 384, 512];

// SVG template for icon (not used in current implementation, but kept for reference)
function generateIconSVG($size) {
    $center = $size / 2;
    $iconSize = $size * 0.6;
    $rx = $size * 0.2;
    $strokeWidth = $size * 0.08;
    $circleR = $size * 0.15;
    $quarter = $size * 0.25;
    $threeQuarter = $size * 0.75;
    
    return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $size . ' ' . $size . '" xmlns="http://www.w3.org/2000/svg">' .
           '<defs><linearGradient id="grad' . $size . '" x1="0%" y1="0%" x2="100%" y2="100%">' .
           '<stop offset="0%" style="stop-color:#6366f1;stop-opacity:1" />' .
           '<stop offset="100%" style="stop-color:#8b5cf6;stop-opacity:1" />' .
           '</linearGradient></defs>' .
           '<rect width="' . $size . '" height="' . $size . '" rx="' . $rx . '" fill="url(#grad' . $size . ')"/>' .
           '<path d="M' . $center . ' ' . $quarter . ' L' . $center . ' ' . $threeQuarter . ' M' . $quarter . ' ' . $center . ' L' . $threeQuarter . ' ' . $center . '" ' .
           'stroke="white" stroke-width="' . $strokeWidth . '" stroke-linecap="round"/>' .
           '<circle cx="' . $center . '" cy="' . $center . '" r="' . $circleR . '" fill="white" opacity="0.3"/>' .
           '</svg>';
}

// Check if GD library is available
if (!function_exists('imagecreatetruecolor')) {
    echo "\n❌ GD Library is not enabled in PHP.\n";
    echo "Please enable GD extension in php.ini or use alternative method.\n\n";
    echo "Alternative: Use online icon generator:\n";
    echo "https://realfavicongenerator.net/\n";
    echo "https://www.pwabuilder.com/imageGenerator\n\n";
    exit(1);
}

// Generate PNG from SVG using GD library
function createPNGFromSVG($svg, $size, $outputPath) {
    // Create image
    $image = imagecreatetruecolor($size, $size);
    
    // Enable alpha blending
    imagealphablending($image, false);
    imagesavealpha($image, true);
    
    // Fill with transparent background
    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $transparent);
    
    // For simplicity, we'll create a gradient background
    // Primary color: #6366f1, Secondary: #8b5cf6
    for ($y = 0; $y < $size; $y++) {
        $ratio = $y / $size;
        $r = (int)(99 + (139 - 99) * $ratio);
        $g = (int)(102 + (92 - 102) * $ratio);
        $b = (int)(241 + (246 - 241) * $ratio);
        
        $color = imagecolorallocate($image, $r, $g, $b);
        imageline($image, 0, $y, $size, $y, $color);
    }
    
    // Draw plus icon (white)
    $white = imagecolorallocate($image, 255, 255, 255);
    $lineWidth = max(4, $size / 16);
    $center = $size / 2;
    $iconSize = $size * 0.4;
    
    // Horizontal line
    imagefilledrectangle($image, 
        $center - $iconSize/2, 
        $center - $lineWidth/2, 
        $center + $iconSize/2, 
        $center + $lineWidth/2, 
        $white
    );
    
    // Vertical line
    imagefilledrectangle($image, 
        $center - $lineWidth/2, 
        $center - $iconSize/2, 
        $center + $lineWidth/2, 
        $center + $iconSize/2, 
        $white
    );
    
    // Save as PNG
    imagepng($image, $outputPath);
    imagedestroy($image);
    
    return file_exists($outputPath);
}

echo "🎨 Generating PWA Icons...\n\n";

$successCount = 0;
$failCount = 0;

foreach ($sizes as $size) {
    $filename = "icon-{$size}x{$size}.png";
    $filepath = $iconsDir . '/' . $filename;
    
    echo "Creating {$filename}... ";
    
    if (createPNGFromSVG('', $size, $filepath)) {
        echo "✅\n";
        $successCount++;
    } else {
        echo "❌ Failed\n";
        $failCount++;
    }
}

echo "\n";
echo "✅ Generated: {$successCount} icons\n";
if ($failCount > 0) {
    echo "❌ Failed: {$failCount} icons\n";
}

echo "\n";
echo "📝 Note: If GD library is not available, you can:\n";
echo "   1. Use online icon generators (e.g., https://realfavicongenerator.net/)\n";
echo "   2. Create icons manually in image editor\n";
echo "   3. Use the SVG template and convert to PNG\n";
echo "\n";
echo "Required sizes: 72x72, 96x96, 128x128, 144x144, 152x152, 192x192, 384x384, 512x512\n";

