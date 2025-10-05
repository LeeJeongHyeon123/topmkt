<?php
/**
 * Admin Corporate Pending - SearchFilter 컴포넌트 QA 테스트
 * v3.37.0
 */

class AdminPendingSearchFilterQA
{
    private $passed = 0;
    private $failed = 0;

    public function run()
    {
        echo "🔍 Admin Corporate Pending - SearchFilter QA 테스트\n";
        echo str_repeat("=", 60) . "\n\n";

        $this->test("1. PHP 구문 오류 없음", function() {
            exec('php -l /var/www/html/topmkt/src/views/admin/corporate/pending.php 2>&1', $output, $return);
            return $return === 0;
        });

        $this->test("2. SearchFilter 컴포넌트 로드 확인", function() {
            $content = file_get_contents('/var/www/html/topmkt/src/views/admin/corporate/pending.php');
            return strpos($content, "require_once SRC_PATH . '/components/ui/SearchFilter.php';") !== false;
        });

        $this->test("3. inline 레이아웃 설정 확인", function() {
            $content = file_get_contents('/var/www/html/topmkt/src/views/admin/corporate/pending.php');
            return strpos($content, "'layout' => 'inline'") !== false;
        });

        $this->test("4. 2개 필터 설정 확인 (대기기간, 기업유형)", function() {
            $content = file_get_contents('/var/www/html/topmkt/src/views/admin/corporate/pending.php');
            return strpos($content, "'id' => 'waitTimeFilter'") !== false &&
                   strpos($content, "'id' => 'companyTypeFilter'") !== false;
        });

        $this->test("5. 필터 레이블 설정 확인", function() {
            $content = file_get_contents('/var/www/html/topmkt/src/views/admin/corporate/pending.php');
            return strpos($content, "'label' => '대기기간'") !== false &&
                   strpos($content, "'label' => '기업 유형'") !== false;
        });

        $this->test("6. JavaScript 모드 확인", function() {
            $content = file_get_contents('/var/www/html/topmkt/src/views/admin/corporate/pending.php');
            return strpos($content, "'method' => 'JS'") !== false;
        });

        $this->test("7. searchInput ID 설정 확인", function() {
            $content = file_get_contents('/var/www/html/topmkt/src/views/admin/corporate/pending.php');
            return strpos($content, "'searchInputId' => 'searchInput'") !== false;
        });

        $this->test("8. filterApplications() 콜백 설정 확인", function() {
            $content = file_get_contents('/var/www/html/topmkt/src/views/admin/corporate/pending.php');
            return strpos($content, "'onSubmit' => 'filterApplications()'") !== false;
        });

        $this->test("9. heredoc 문법 사용 확인", function() {
            $content = file_get_contents('/var/www/html/topmkt/src/views/admin/corporate/pending.php');
            return strpos($content, "<<<'SCRIPTS'") !== false;
        });

        // 결과 출력
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "총 테스트: " . ($this->passed + $this->failed) . "\n";
        echo "통과: " . $this->passed . " ✅\n";
        echo "실패: " . $this->failed . " ❌\n";
        echo "성공률: " . round(($this->passed / ($this->passed + $this->failed)) * 100, 1) . "%\n";

        if ($this->failed === 0) {
            echo "\n🎉 완벽합니다! admin/corporate/pending.php SearchFilter 적용 완료!\n";
        } else {
            echo "\n⚠️  일부 테스트 실패. 수정이 필요합니다.\n";
        }
    }

    private function test($name, $callback)
    {
        $result = $callback();
        if ($result) {
            echo "✅ " . $name . "\n";
            $this->passed++;
        } else {
            echo "❌ " . $name . "\n";
            $this->failed++;
        }
    }
}

// 실행
$qa = new AdminPendingSearchFilterQA();
$qa->run();
