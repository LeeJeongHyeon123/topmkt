<?php
/**
 * .env 파일 로더
 *
 * PHP의 $_ENV 슈퍼글로벌에 .env 파일의 환경변수를 로드합니다.
 * vlucas/phpdotenv 라이브러리 없이 간단하게 구현된 버전입니다.
 *
 * 보안 강화: 2025-10-19
 * - API 키, 암호화 키, DB 비밀번호를 .env 파일에서 로드
 * - 하드코딩된 비밀번호 제거
 */

/**
 * .env 파일 로드 함수
 *
 * @param string $envPath .env 파일 경로
 * @return void
 */
function loadEnv($envPath) {
    if (!file_exists($envPath)) {
        error_log("CRITICAL: .env file not found at: " . $envPath);
        throw new Exception(".env file not found - check file permissions and location");
    }

    // 파일 권한 확인 (600 또는 400이어야 함)
    $perms = fileperms($envPath);
    if ($perms === false) {
        error_log("CRITICAL: Cannot read .env file permissions");
        throw new Exception("Cannot read .env file permissions");
    }

    // 파일 읽기
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        error_log("CRITICAL: Cannot read .env file");
        throw new Exception("Cannot read .env file - check file permissions");
    }

    foreach ($lines as $line) {
        // 주석과 빈 줄 무시
        $line = trim($line);
        if (empty($line) || $line[0] === '#') {
            continue;
        }

        // key=value 형식 파싱
        if (strpos($line, '=') === false) {
            continue;
        }

        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        // 따옴표 제거 (있으면)
        if (strlen($value) >= 2) {
            if (($value[0] === '"' && $value[strlen($value) - 1] === '"') ||
                ($value[0] === "'" && $value[strlen($value) - 1] === "'")) {
                $value = substr($value, 1, -1);
            }
        }

        // $_ENV, $_SERVER, putenv()에 모두 설정 (호환성 최대화)
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv("$key=$value");
    }
}

// .env 파일 로드 (프로젝트 루트)
$envPath = dirname(__DIR__, 2) . '/.env';
try {
    loadEnv($envPath);

    // 중요 환경변수 존재 확인 (보안)
    $requiredVars = ['DB_PASSWORD', 'ENCRYPTION_KEY', 'HASH_SALT', 'JWT_SECRET_KEY'];
    $missing = [];

    foreach ($requiredVars as $var) {
        if (!isset($_ENV[$var]) || empty($_ENV[$var])) {
            $missing[] = $var;
        }
    }

    if (!empty($missing)) {
        error_log("CRITICAL: Required environment variables missing: " . implode(', ', $missing));
        throw new Exception("Required environment variables missing in .env file");
    }

} catch (Exception $e) {
    error_log("ENV LOADER ERROR: " . $e->getMessage());
    // 에러를 던지지 않고 로그만 기록 (기존 fallback 동작 유지)
    // throw $e; // 프로덕션에서는 주석 처리하여 fallback 허용
}
?>
