<?php
/**
 * VerificationCode 모델 클래스
 * 인증 코드 안전 저장 및 검증 기능
 */

// SRC_PATH 상수 정의 확인
if (!defined('SRC_PATH')) {
    define('SRC_PATH', dirname(__DIR__));
}

require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/helpers/SecurityHelper.php';

class VerificationCode {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * 인증 코드 생성 및 저장
     */
    public function createVerificationCode($phone, $purpose = 'signup', $expiryMinutes = 3) {
        try {
            // 기존 미인증 코드 정리
            $this->cleanupExpiredCodes();
            $this->deleteExistingCodes($phone, $purpose);

            // 6자리 랜덤 인증 코드 생성
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // 인증 코드 해시화
            $codeHash = SecurityHelper::encryptSearchable($code);

            // 암호화된 코드 저장 (보안을 위해)
            $encryptedCode = SecurityHelper::encrypt($code);

            // 만료 시간 계산
            $expiresAt = date('Y-m-d H:i:s', time() + ($expiryMinutes * 60));

            $sql = "INSERT INTO verification_codes (
                phone, code, code_hash, purpose, attempts, verified, expires_at
            ) VALUES (?, ?, ?, ?, 0, 0, ?)";

            $result = $this->db->execute($sql, [
                $phone,
                $encryptedCode,
                $codeHash,
                $purpose,
                $expiresAt
            ]);

            if ($result) {
                return [
                    'success' => true,
                    'code' => $code, // 실제 코드는 SMS 발송용으로만 반환
                    'expires_at' => $expiresAt
                ];
            }

            throw new Exception('인증 코드 저장에 실패했습니다.');

        } catch (Exception $e) {
            error_log('VerificationCode::createVerificationCode 오류: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => '인증 코드 생성에 실패했습니다.'
            ];
        }
    }

    /**
     * 인증 코드 검증
     */
    public function verifyCode($phone, $code, $purpose = 'signup') {
        try {
            // 만료된 코드 정리
            $this->cleanupExpiredCodes();

            // 코드 해시 생성
            $codeHash = SecurityHelper::encryptSearchable($code);

            // 인증 코드 조회
            $sql = "SELECT id, code, attempts, verified, expires_at
                    FROM verification_codes
                    WHERE phone = ? AND code_hash = ? AND purpose = ?
                    AND verified = 0 AND expires_at > NOW()
                    ORDER BY created_at DESC LIMIT 1";

            $verification = $this->db->fetch($sql, [$phone, $codeHash, $purpose]);

            if (!$verification) {
                return [
                    'success' => false,
                    'message' => '유효하지 않거나 만료된 인증 코드입니다.'
                ];
            }

            // 시도 횟수 확인 (최대 5회)
            if ($verification['attempts'] >= 5) {
                return [
                    'success' => false,
                    'message' => '인증 시도 횟수를 초과했습니다. 새로운 인증 코드를 요청해주세요.'
                ];
            }

            // 암호화된 코드 복호화 및 비교
            $decryptedCode = SecurityHelper::decrypt($verification['code']);

            if ($decryptedCode !== $code) {
                // 실패 시도 횟수 증가
                $this->incrementAttempts($verification['id']);

                return [
                    'success' => false,
                    'message' => '인증 코드가 일치하지 않습니다.'
                ];
            }

            // 인증 성공 처리
            $this->markAsVerified($verification['id']);

            return [
                'success' => true,
                'message' => '인증이 완료되었습니다.'
            ];

        } catch (Exception $e) {
            error_log('VerificationCode::verifyCode 오류: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => '인증 처리 중 오류가 발생했습니다.'
            ];
        }
    }

    /**
     * 인증 완료 상태로 변경
     */
    private function markAsVerified($id) {
        $sql = "UPDATE verification_codes SET verified = 1 WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }

    /**
     * 실패 시도 횟수 증가
     */
    private function incrementAttempts($id) {
        $sql = "UPDATE verification_codes SET attempts = attempts + 1 WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }

    /**
     * 기존 미인증 코드 삭제
     */
    private function deleteExistingCodes($phone, $purpose) {
        $sql = "DELETE FROM verification_codes
                WHERE phone = ? AND purpose = ? AND verified = 0";
        return $this->db->execute($sql, [$phone, $purpose]);
    }

    /**
     * 만료된 인증 코드 정리
     */
    public function cleanupExpiredCodes() {
        $sql = "DELETE FROM verification_codes WHERE expires_at < NOW()";
        return $this->db->execute($sql);
    }

    /**
     * 인증 완료 여부 확인
     */
    public function isPhoneVerified($phone, $purpose = 'signup') {
        $sql = "SELECT id FROM verification_codes
                WHERE phone = ? AND purpose = ? AND verified = 1
                AND expires_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                ORDER BY created_at DESC LIMIT 1";

        $result = $this->db->fetch($sql, [$phone, $purpose]);
        return !empty($result);
    }

    /**
     * 인증 코드 통계 조회 (관리자용)
     */
    public function getVerificationStats($days = 7) {
        $sql = "SELECT
                    purpose,
                    COUNT(*) as total_codes,
                    SUM(CASE WHEN verified = 1 THEN 1 ELSE 0 END) as verified_codes,
                    AVG(attempts) as avg_attempts
                FROM verification_codes
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY purpose";

        return $this->db->fetchAll($sql, [$days]);
    }
}