<?php
/**
 * RedisCacheHelper 클래스
 * Redis를 활용한 고성능 캐싱 기능을 제공합니다.
 */

class RedisCacheHelper {

    private static $redis = null;
    private static $defaultTtl = 3600; // 1시간

    /**
     * Redis 연결 초기화
     */
    private static function initRedis() {
        if (self::$redis === null) {
            try {
                self::$redis = new Redis();

                // 로컬 Redis 서버 연결 시도
                if (!self::$redis->connect('127.0.0.1', 6379, 3)) {
                    // Redis 연결 실패 시 파일 캐시로 폴백
                    self::$redis = null;
                    return false;
                }

                // Redis 연결 성공
                if (!self::$redis->ping()) {
                    self::$redis = null;
                    return false;
                }

                return true;
            } catch (Exception $e) {
                error_log("Redis 연결 실패: " . $e->getMessage());
                self::$redis = null;
                return false;
            }
        }

        return true;
    }

    /**
     * 데이터 캐싱 (Remember 패턴)
     */
    public static function remember($key, $ttl, $callback) {
        if (self::initRedis() && self::$redis) {
            // Redis 사용
            $cachedData = self::$redis->get($key);

            if ($cachedData !== false) {
                $data = json_decode($cachedData, true);
                if ($data && isset($data['expires']) && $data['expires'] > time()) {
                    return $data['value'];
                }
            }

            $data = $callback();
            self::put($key, $data, $ttl);
            return $data;
        } else {
            // Redis 연결 실패 시 파일 캐시로 폴백
            return CacheHelper::remember($key, $ttl, $callback);
        }
    }

    /**
     * 데이터 저장
     */
    public static function put($key, $data, $ttl = null) {
        $ttl = $ttl ?: self::$defaultTtl;

        if (self::initRedis() && self::$redis) {
            // Redis 사용
            $cacheData = [
                'value' => $data,
                'expires' => time() + $ttl,
                'created' => time()
            ];

            return self::$redis->setex($key, $ttl, json_encode($cacheData));
        } else {
            // Redis 연결 실패 시 파일 캐시로 폴백
            return CacheHelper::put($key, $data, $ttl);
        }
    }

    /**
     * 데이터 조회
     */
    public static function get($key, $default = null) {
        if (self::initRedis() && self::$redis) {
            // Redis 사용
            $cachedData = self::$redis->get($key);

            if ($cachedData !== false) {
                $data = json_decode($cachedData, true);
                if ($data && isset($data['expires']) && $data['expires'] > time()) {
                    return $data['value'];
                }
            }

            return $default;
        } else {
            // Redis 연결 실패 시 파일 캐시로 폴백
            return CacheHelper::get($key, $default);
        }
    }

    /**
     * 캐시 삭제
     */
    public static function forget($key) {
        if (self::initRedis() && self::$redis) {
            return self::$redis->del($key) > 0;
        } else {
            return CacheHelper::forget($key);
        }
    }

    /**
     * 모든 캐시 삭제
     */
    public static function flush() {
        if (self::initRedis() && self::$redis) {
            return self::$redis->flushDB();
        } else {
            return CacheHelper::flush();
        }
    }

    /**
     * 캐시 존재 확인
     */
    public static function has($key) {
        return self::get($key) !== null;
    }

    /**
     * 캐시 만료 시간 설정
     */
    public static function setDefaultTtl($seconds) {
        self::$defaultTtl = $seconds;
    }

    /**
     * 캐시 통계 반환
     */
    public static function getStats() {
        if (self::initRedis() && self::$redis) {
            $info = self::$redis->info();
            return [
                'redis_connected' => true,
                'redis_version' => $info['redis_version'] ?? 'unknown',
                'used_memory' => $info['used_memory_human'] ?? '0B',
                'connected_clients' => $info['connected_clients'] ?? 0,
                'keyspace_hits' => $info['keyspace_hits'] ?? 0,
                'keyspace_misses' => $info['keyspace_misses'] ?? 0
            ];
        } else {
            return array_merge(['redis_connected' => false], CacheHelper::getStats());
        }
    }

    /**
     * 사용자별 캐시 태그 관리
     */
    public static function tag($tags, $callback) {
        if (self::initRedis() && self::$redis) {
            $tagKey = 'cache:tags:' . implode(':', $tags);
            $cacheKey = 'cache:' . uniqid();

            // 태그에 캐시 키 추가
            self::$redis->sAdd($tagKey, $cacheKey);

            try {
                $result = $callback($cacheKey);

                // 캐시 키에 데이터 저장
                self::put($cacheKey, $result);

                return $result;
            } catch (Exception $e) {
                // 태그에서 캐시 키 제거
                self::$redis->sRem($tagKey, $cacheKey);
                throw $e;
            }
        } else {
            // Redis 연결 실패 시 일반 캐시 사용
            return $callback('fallback_' . uniqid());
        }
    }

    /**
     * 태그로 캐시 플러시
     */
    public static function flushTags($tags) {
        if (self::initRedis() && self::$redis) {
            $tagKey = 'cache:tags:' . implode(':', $tags);
            $cacheKeys = self::$redis->sMembers($tagKey);

            if (!empty($cacheKeys)) {
                self::$redis->del($cacheKeys);
                self::$redis->del($tagKey);
            }

            return true;
        } else {
            return CacheHelper::flush();
        }
    }
}
?>
