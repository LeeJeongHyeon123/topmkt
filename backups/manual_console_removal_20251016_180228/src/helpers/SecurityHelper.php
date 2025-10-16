<?php
/**
 * 보안 암호화/해시화 헬퍼 클래스
 *
 * 기능:
 * - AES-256-GCM 암호화/복호화
 * - 검색 가능한 암호화 (이메일용)
 * - 안전한 해시화 (인증코드, remember token, IP 주소)
 * - 데이터 마스킹
 */

class SecurityHelper {

    // 암호화 키 (환경변수에서 가져오거나 설정 파일에서 관리)
    private static $encryptionKey = null;
    private static $hashSalt = null;

    /**
     * 초기화 - 암호화 키 설정
     */
    private static function init() {
        if (self::$encryptionKey === null) {
            // 실제 운영에서는 환경변수에서 가져와야 함
            self::$encryptionKey = hash('sha256', 'TOPMKT_ENCRYPTION_KEY_2024_SECURE_DATA_PROTECTION', true);
            self::$hashSalt = 'TOPMKT_HASH_SALT_2024_SECURE_HASHING';
        }
    }

    /**
     * AES-256-GCM 암호화
     *
     * @param string $data 암호화할 데이터
     * @return string|false 암호화된 데이터 (base64) 또는 실패 시 false
     */
    public static function encrypt($data) {
        if (empty($data)) {
            return $data;
        }

        self::init();

        try {
            $iv = random_bytes(12); // GCM은 12바이트 IV 권장
            $tag = '';

            $encrypted = openssl_encrypt(
                $data,
                'aes-256-gcm',
                self::$encryptionKey,
                OPENSSL_RAW_DATA,
                $iv,
                $tag
            );

            if ($encrypted === false) {
                error_log('SecurityHelper: 암호화 실패');
                return false;
            }

            // IV + Tag + 암호화된 데이터를 결합하여 base64 인코딩
            return base64_encode($iv . $tag . $encrypted);

        } catch (Exception $e) {
            error_log('SecurityHelper: 암호화 오류 - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * AES-256-GCM 복호화
     *
     * @param string $encryptedData 암호화된 데이터 (base64)
     * @return string|false 복호화된 데이터 또는 실패 시 false
     */
    public static function decrypt($encryptedData) {
        if (empty($encryptedData)) {
            return $encryptedData;
        }

        self::init();

        try {
            $data = base64_decode($encryptedData);
            if ($data === false) {
                return false;
            }

            $iv = substr($data, 0, 12);
            $tag = substr($data, 12, 16);
            $encrypted = substr($data, 28);

            $decrypted = openssl_decrypt(
                $encrypted,
                'aes-256-gcm',
                self::$encryptionKey,
                OPENSSL_RAW_DATA,
                $iv,
                $tag
            );

            if ($decrypted === false) {
                error_log('SecurityHelper: 복호화 실패');
                return false;
            }

            return $decrypted;

        } catch (Exception $e) {
            error_log('SecurityHelper: 복호화 오류 - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 검색 가능한 암호화 (이메일용)
     * HMAC을 이용한 결정적 암호화
     *
     * @param string $data 암호화할 데이터
     * @return string 암호화된 해시값
     */
    public static function encryptSearchable($data) {
        if (empty($data)) {
            return $data;
        }

        self::init();

        // 소문자로 정규화하여 대소문자 구분 없이 검색 가능
        $normalizedData = strtolower(trim($data));

        return hash_hmac('sha256', $normalizedData, self::$hashSalt);
    }

    /**
     * 안전한 해시화 (단방향)
     * 인증코드, remember token, IP 주소용
     *
     * @param string $data 해시화할 데이터
     * @return string 해시값
     */
    public static function secureHash($data) {
        if (empty($data)) {
            return $data;
        }

        self::init();

        return hash_hmac('sha256', $data, self::$hashSalt);
    }

    /**
     * IP 주소 암호화 (복호화 가능한 양방향)
     *
     * @param string $ip IP 주소
     * @return string 암호화된 IP 주소
     */
    public static function encryptIpAddress($ip) {
        if (empty($ip)) {
            return $ip;
        }

        return self::encrypt($ip);
    }

    /**
     * IP 주소 복호화
     *
     * @param string $encryptedIp 암호화된 IP 주소
     * @return string 복호화된 IP 주소
     */
    public static function decryptIpAddress($encryptedIp) {
        if (empty($encryptedIp)) {
            return $encryptedIp;
        }

        return self::decrypt($encryptedIp);
    }

    /**
     * IP 주소 지역 정보 조회 (관리자용)
     *
     * @param string $ip IP 주소
     * @return array IP 지역 정보
     */
    public static function getIpLocationInfo($ip) {
        if (empty($ip)) {
            return ['country' => 'Unknown', 'region' => 'Unknown', 'city' => 'Unknown'];
        }

        // 내부 IP 확인
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return ['country' => 'Internal', 'region' => 'Private Network', 'city' => 'Local'];
        }

        // 실제 운영에서는 MaxMind GeoIP 등의 서비스 사용 권장
        // 여기서는 간단한 한국 IP 대역 체크만 구현
        $parts = explode('.', $ip);
        if (count($parts) === 4) {
            $firstOctet = (int)$parts[0];
            $secondOctet = (int)$parts[1];

            // 대략적인 한국 IP 대역 (실제로는 더 정확한 데이터 필요)
            if (($firstOctet >= 1 && $firstOctet <= 126) ||
                ($firstOctet >= 210 && $firstOctet <= 223)) {
                return ['country' => 'Korea', 'region' => 'Unknown', 'city' => 'Unknown'];
            }
        }

        return ['country' => 'Foreign', 'region' => 'Unknown', 'city' => 'Unknown'];
    }

    /**
     * IP 주소 보안 등급 평가
     *
     * @param string $ip IP 주소
     * @return array 보안 평가 결과
     */
    public static function evaluateIpSecurity($ip) {
        if (empty($ip)) {
            return ['level' => 'unknown', 'risk' => 'unknown', 'notes' => 'No IP provided'];
        }

        $result = [
            'level' => 'normal',
            'risk' => 'low',
            'notes' => []
        ];

        // 내부 IP 확인
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            $result['level'] = 'internal';
            $result['risk'] = 'very_low';
            $result['notes'][] = 'Private/Reserved IP range';
            return $result;
        }

        // 여기에 실제 보안 로직 추가 가능:
        // - 알려진 악성 IP 데이터베이스 체크
        // - VPN/프록시 IP 탐지
        // - 지역별 위험도 평가
        // - 이전 공격 이력 확인

        $locationInfo = self::getIpLocationInfo($ip);
        if ($locationInfo['country'] === 'Foreign') {
            $result['risk'] = 'medium';
            $result['notes'][] = 'Foreign IP address';
        }

        return $result;
    }

    /**
     * 휴대폰 번호 부분 마스킹
     *
     * @param string $phone 휴대폰 번호
     * @return string 마스킹된 휴대폰 번호 (예: 010-****-1234)
     */
    public static function maskPhone($phone) {
        if (empty($phone)) {
            return $phone;
        }

        // 숫자만 추출
        $numbers = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($numbers) === 11) {
            return substr($numbers, 0, 3) . '-****-' . substr($numbers, -4);
        }

        return '***-****-' . substr($numbers, -4);
    }

    /**
     * 사업자번호 마스킹
     *
     * @param string $businessNumber 사업자번호
     * @return string 마스킹된 사업자번호 (예: 123-**-***45)
     */
    public static function maskBusinessNumber($businessNumber) {
        if (empty($businessNumber)) {
            return $businessNumber;
        }

        $numbers = preg_replace('/[^0-9]/', '', $businessNumber);

        if (strlen($numbers) === 10) {
            return substr($numbers, 0, 3) . '-**-***' . substr($numbers, -2);
        }

        return '***-**-***' . substr($numbers, -2);
    }

    /**
     * 생년월일을 연령대로 변환
     *
     * @param string $birthDate 생년월일 (YYYY-MM-DD)
     * @return string 연령대 (예: "30대")
     */
    public static function convertToAgeGroup($birthDate) {
        if (empty($birthDate)) {
            return null;
        }

        try {
            $birth = new DateTime($birthDate);
            $today = new DateTime();
            $age = $today->diff($birth)->y;

            $ageGroup = floor($age / 10) * 10;

            if ($ageGroup < 20) {
                return '10대';
            } elseif ($ageGroup >= 60) {
                return '60대 이상';
            } else {
                return $ageGroup . '대';
            }

        } catch (Exception $e) {
            return '알 수 없음';
        }
    }

    /**
     * 데이터 검증 - 암호화된 데이터인지 확인
     *
     * @param string $data 검증할 데이터
     * @return bool 암호화된 데이터인지 여부
     */
    public static function isEncrypted($data) {
        if (empty($data)) {
            return false;
        }

        // base64로 인코딩된 데이터인지 확인
        $decoded = base64_decode($data, true);
        if ($decoded === false) {
            return false;
        }

        // 최소 길이 확인 (IV 12 + Tag 16 + 최소 데이터 1)
        return strlen($decoded) >= 29;
    }

    /**
     * 안전한 비교 (타이밍 공격 방지)
     *
     * @param string $hash1 첫 번째 해시
     * @param string $hash2 두 번째 해시
     * @return bool 일치 여부
     */
    public static function secureCompare($hash1, $hash2) {
        return hash_equals($hash1, $hash2);
    }

    /**
     * 랜덤 토큰 생성
     *
     * @param int $length 토큰 길이 (기본 32바이트)
     * @return string 안전한 랜덤 토큰
     */
    public static function generateSecureToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
}
?>