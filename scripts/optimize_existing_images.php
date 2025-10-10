<?php
/**
 * 🚀 v3.64.0: 기존 강의 이미지 일괄 최적화
 * 사용법: php scripts/optimize_existing_images.php
 */

define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

$uploadDir = ROOT_PATH . '/public/assets/uploads/lectures';
$images = glob($uploadDir . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);

echo "=== 🔥 기존 강의 이미지 일괄 최적화 ===\n";
echo "발견된 이미지: " . count($images) . "개\n\n";

$totalOriginal = 0;
$totalOptimized = 0;
$processedCount = 0;
$errorCount = 0;

foreach ($images as $filePath) {
    try {
        $filename = basename($filePath);
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $originalSize = filesize($filePath);
        $totalOriginal += $originalSize;

        echo "[{$processedCount}] {$filename} (" . number_format($originalSize) . " bytes)\n";

        // 이미지 로드
        $image = null;
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($filePath);
                break;
            case 'png':
                $image = imagecreatefrompng($filePath);
                break;
            case 'gif':
                $image = imagecreatefromgif($filePath);
                break;
        }

        if ($image === false) {
            echo "  ❌ 이미지 로드 실패\n";
            $errorCount++;
            continue;
        }

        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);

        // 리사이징 (최대 2000px)
        $maxDimension = 2000;
        $needsResize = ($originalWidth > $maxDimension || $originalHeight > $maxDimension);

        if ($needsResize) {
            $ratio = min($maxDimension / $originalWidth, $maxDimension / $originalHeight);
            $newWidth = (int)($originalWidth * $ratio);
            $newHeight = (int)($originalHeight * $ratio);

            $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

            if ($extension === 'png') {
                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);
                $transparent = imagecolorallocatealpha($resizedImage, 0, 0, 0, 127);
                imagefill($resizedImage, 0, 0, $transparent);
            }

            imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
            imagedestroy($image);
            $image = $resizedImage;

            echo "  📐 리사이징: {$originalWidth}x{$originalHeight} → {$newWidth}x{$newHeight}\n";
        }

        // WebP 변환
        $webpPath = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $filePath);

        if ($extension === 'png') {
            imagealphablending($image, false);
            imagesavealpha($image, true);
            imagewebp($image, $webpPath, 85);
        } else {
            imagewebp($image, $webpPath, 85);
        }

        $webpSize = filesize($webpPath);
        $reduction = (1 - ($webpSize / $originalSize)) * 100;

        echo "  🎯 WebP 변환: " . number_format($originalSize) . " → " . number_format($webpSize) . " bytes\n";
        echo "  📊 감소율: " . number_format($reduction, 1) . "%\n";

        // WebP가 더 작으면 교체
        if ($webpSize < $originalSize && file_exists($webpPath)) {
            unlink($filePath);
            rename($webpPath, preg_replace('/\.[^.]+$/', '.webp', $filePath));
            echo "  ✅ 원본 삭제, WebP로 교체\n";
            $totalOptimized += $webpSize;
        } else {
            if (file_exists($webpPath)) {
                unlink($webpPath);
            }
            // 원본 재압축
            if ($extension === 'jpg' || $extension === 'jpeg') {
                imagejpeg($image, $filePath, 85);
            } elseif ($extension === 'png') {
                imagealphablending($image, false);
                imagesavealpha($image, true);
                imagepng($image, $filePath, 6);
            }
            $finalSize = filesize($filePath);
            echo "  ⚠️ WebP가 더 큼, 원본 재압축\n";
            $totalOptimized += $finalSize;
        }

        imagedestroy($image);
        $processedCount++;
        echo "\n";

    } catch (Exception $e) {
        echo "  ❌ 오류: " . $e->getMessage() . "\n\n";
        $errorCount++;
    }
}

echo "=== 📊 최적화 완료 ===\n";
echo "처리된 이미지: {$processedCount}개\n";
echo "오류: {$errorCount}개\n";
echo "원본 총 크기: " . number_format($totalOriginal / 1024 / 1024, 2) . " MB\n";
echo "최적화 후: " . number_format($totalOptimized / 1024 / 1024, 2) . " MB\n";
echo "절약: " . number_format(($totalOriginal - $totalOptimized) / 1024 / 1024, 2) . " MB (" . number_format((1 - $totalOptimized / $totalOriginal) * 100, 1) . "%)\n";
