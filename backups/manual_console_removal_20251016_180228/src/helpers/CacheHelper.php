<?php
/**
 * 캐시 헬퍼 클래스
 * 대용량 데이터 성능 최적화를 위한 간단한 파일 기반 캐시
 */

require_once __DIR__ . '/WebLogger.php';

class CacheHelper {
    private static $cacheDir = '/tmp/topmkt_cache';
    private static $defaultTtl = 300; // 5분
    
    /**
     * 캐시 디렉토리 초기화
     */
    private static function initCacheDir() {
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
    }
    
    /**
     * 캐시 키 생성
     */
    private static function getCacheKey($key) {
        return self::$cacheDir . '/' . md5($key) . '.cache';
    }
    
    /**
     * 캐시 저장
     */
    public static function set($key, $data, $ttl = null) {
        self::initCacheDir();
        
        $ttl = $ttl ?? self::$defaultTtl;
        $cacheFile = self::getCacheKey($key);
        
        $cacheData = [
            'data' => $data,
            'expires' => time() + $ttl,
            'created' => time()
        ];
        
        return file_put_contents($cacheFile, serialize($cacheData)) !== false;
    }
    
    /**
     * 캐시 조회
     */
    public static function get($key) {
        $cacheStartTime = microtime(true);
        WebLogger::info("💾 [CACHE] 캐시 조회 시작: " . substr($key, 0, 50) . "...");
        
        $cacheFile = self::getCacheKey($key);
        
        if (!file_exists($cacheFile)) {
            $cacheTime = (microtime(true) - $cacheStartTime) * 1000;
            WebLogger::info("💾 [CACHE] 캐시 미스: " . round($cacheTime, 2) . "ms");
            return null;
        }
        
        $cacheData = unserialize(file_get_contents($cacheFile));
        
        if (!$cacheData || $cacheData['expires'] < time()) {
            self::delete($key);
            $cacheTime = (microtime(true) - $cacheStartTime) * 1000;
            WebLogger::info("💾 [CACHE] 캐시 만료: " . round($cacheTime, 2) . "ms");
            return null;
        }
        
        $cacheTime = (microtime(true) - $cacheStartTime) * 1000;
        WebLogger::info("💾 [CACHE] 캐시 히트: " . round($cacheTime, 2) . "ms");
        return $cacheData['data'];
    }
    
    /**
     * 캐시 존재 확인
     */
    public static function has($key) {
        return self::get($key) !== null;
    }
    
    /**
     * 캐시 삭제
     */
    public static function delete($key) {
        $cacheFile = self::getCacheKey($key);
        
        if (file_exists($cacheFile)) {
            return unlink($cacheFile);
        }
        
        return true;
    }
    
    /**
     * 모든 캐시 삭제
     */
    public static function clear() {
        if (!is_dir(self::$cacheDir)) {
            return true;
        }
        
        $files = glob(self::$cacheDir . '/*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
        
        return true;
    }
    
    /**
     * 캐시 또는 콜백 실행
     */
    public static function remember($key, $callback, $ttl = null) {
        $rememberStartTime = microtime(true);
        WebLogger::info("🔄 [CACHE] Remember 시작: " . substr($key, 0, 50) . "...");
        
        $cached = self::get($key);
        
        if ($cached !== null) {
            $rememberTime = (microtime(true) - $rememberStartTime) * 1000;
            WebLogger::info("🔄 [CACHE] Remember 완료 (캐시 사용): " . round($rememberTime, 2) . "ms");
            return $cached;
        }
        
        WebLogger::info("🔄 [CACHE] 콜백 함수 실행 시작");
        $callbackStartTime = microtime(true);
        $data = $callback();
        $callbackTime = (microtime(true) - $callbackStartTime) * 1000;
        WebLogger::info("🔄 [CACHE] 콜백 함수 실행 완료: " . round($callbackTime, 2) . "ms");
        
        self::set($key, $data, $ttl);
        
        $rememberTime = (microtime(true) - $rememberStartTime) * 1000;
        WebLogger::info("🔄 [CACHE] Remember 완료 (새 데이터): " . round($rememberTime, 2) . "ms");
        
        return $data;
    }
    
    /**
     * 게시글 목록 캐시 키 생성
     */
    public static function getPostListCacheKey($page, $pageSize, $search = null) {
        return "posts_list_" . md5("page:{$page}_size:{$pageSize}_search:" . ($search ?? ''));
    }
    
    /**
     * 게시글 총 개수 캐시 키 생성
     */
    public static function getPostCountCacheKey($search = null) {
        return "posts_count_" . md5("search:" . ($search ?? ''));
    }
}
?>