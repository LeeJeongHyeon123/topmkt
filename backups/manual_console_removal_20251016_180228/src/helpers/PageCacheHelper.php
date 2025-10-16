<?php
/**
 * 페이지 캐싱 헬퍼 클래스
 * 
 * 동적 페이지를 파일로 캐싱하여 성능을 향상시킵니다.
 */
class PageCacheHelper {
    private static $cacheDir = PUBLIC_PATH . '/cache/pages/';
    private static $defaultTTL = 3600; // 1시간
    
    /**
     * 캐시 초기화
     */
    public static function init() {
        if (!file_exists(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
    }
    
    /**
     * 캐시 키 생성
     */
    private static function getCacheKey($url, $params = []) {
        $key = $url;
        if (!empty($params)) {
            ksort($params);
            $key .= '_' . md5(serialize($params));
        }
        return preg_replace('/[^a-zA-Z0-9_-]/', '_', $key);
    }
    
    /**
     * 캐시 파일 경로 생성
     */
    private static function getCachePath($key) {
        return self::$cacheDir . $key . '.cache';
    }
    
    /**
     * 캐시 저장
     */
    public static function set($url, $content, $ttl = null) {
        self::init();
        
        if ($ttl === null) {
            $ttl = self::$defaultTTL;
        }
        
        $key = self::getCacheKey($url, $_GET);
        $path = self::getCachePath($key);
        
        $data = [
            'content' => $content,
            'expires' => time() + $ttl,
            'created' => time(),
            'url' => $url
        ];
        
        return file_put_contents($path, serialize($data)) !== false;
    }
    
    /**
     * 캐시 가져오기
     */
    public static function get($url) {
        $key = self::getCacheKey($url, $_GET);
        $path = self::getCachePath($key);
        
        if (!file_exists($path)) {
            return false;
        }
        
        $data = unserialize(file_get_contents($path));
        
        // 만료 확인
        if ($data['expires'] < time()) {
            unlink($path);
            return false;
        }
        
        return $data['content'];
    }
    
    /**
     * 캐시 삭제
     */
    public static function delete($url) {
        $key = self::getCacheKey($url, $_GET);
        $path = self::getCachePath($key);
        
        if (file_exists($path)) {
            return unlink($path);
        }
        
        return true;
    }
    
    /**
     * 특정 패턴의 캐시 모두 삭제
     */
    public static function deletePattern($pattern) {
        self::init();
        
        $files = glob(self::$cacheDir . $pattern . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }
    
    /**
     * 전체 캐시 삭제
     */
    public static function deleteAll() {
        self::init();
        
        $files = glob(self::$cacheDir . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }
    
    /**
     * 캐시 통계
     */
    public static function getStats() {
        self::init();
        
        $files = glob(self::$cacheDir . '*.cache');
        $totalSize = 0;
        $totalFiles = count($files);
        $expiredFiles = 0;
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
            $data = unserialize(file_get_contents($file));
            if ($data['expires'] < time()) {
                $expiredFiles++;
            }
        }
        
        return [
            'total_files' => $totalFiles,
            'total_size' => $totalSize,
            'expired_files' => $expiredFiles,
            'cache_dir' => self::$cacheDir
        ];
    }
    
    /**
     * 캐시 가능 여부 확인
     */
    public static function isCacheable() {
        // POST 요청은 캐시하지 않음
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return false;
        }
        
        // 로그인한 사용자는 캐시하지 않음
        if (isset($_SESSION['user_id'])) {
            return false;
        }
        
        // 관리자 페이지는 캐시하지 않음
        if (strpos($_SERVER['REQUEST_URI'], '/admin') === 0) {
            return false;
        }
        
        // API 요청은 캐시하지 않음
        if (strpos($_SERVER['REQUEST_URI'], '/api') === 0) {
            return false;
        }
        
        return true;
    }
}
?>