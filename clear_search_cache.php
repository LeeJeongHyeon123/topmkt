<?php
/**
 * 검색 캐시 초기화 스크립트
 */

// 캐시 디렉토리 정리
$cacheDir = '/tmp/topmkt_cache';

if (is_dir($cacheDir)) {
    $files = glob($cacheDir . '/*.cache');
    $deleted = 0;
    
    foreach ($files as $file) {
        if (unlink($file)) {
            $deleted++;
        }
    }
    
    echo "✅ 캐시 파일 {$deleted}개 삭제 완료\n";
} else {
    echo "ℹ️ 캐시 디렉토리가 존재하지 않습니다\n";
}

echo "🚀 검색 캐시 초기화 완료! 이제 새로운 검색을 테스트하세요.\n";
?>