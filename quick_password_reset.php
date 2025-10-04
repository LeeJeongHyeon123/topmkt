<?php
/**
 * 간단한 비밀번호 재설정
 */

// 새 비밀번호 해시 생성
$newPassword = 'Dnlszkem1!';
$newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

echo "새 비밀번호: $newPassword\n";
echo "새 해시: $newPasswordHash\n\n";
echo "MySQL 명령어:\n";
echo "UPDATE users SET password_hash = '$newPasswordHash' WHERE nickname = '우리집탄이';\n";
?>