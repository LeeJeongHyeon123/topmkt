<?php
/**
 * Admin Corporate List - SearchFilter 컴포넌트 QA 테스트
 * v3.37.0
 */

class AdminCorporateSearchFilterQA
{
    private $passed = 0;
    private $failed = 0;

    public function run()
    {
        echo "🔍 Admin Corporate List - SearchFilter QA 테스트\n";
        echo str_repeat("=", 60) . "\n\n";

        $this->test("1. PHP 구문 오류 없음", function() {
            exec('php -l /var/www/html/topmkt/src/views/admin/corporate/list.php 2>&1', $output, $return);
            return $return === 0;
        });

        $this->test("2. SearchFilter 컴포넌트 로드 확인", function() {
            $content = file_get_contents('/var/www/html/topmkt/src/views/admin/corporate/list.php');
            return strpos($content, "require_once SRC_PATH . '/components/ui/SearchFilter.php';") !== false;
        });

        $this->test("3. SearchFilter::create() 호출 존재", function() {
            $content = file_get_contents('/var/www/html/topmkt/src/views/admin/corporate/list.php');
            return strpos($content, 'SearchFilter::create([') !== false;
        });

        $this->test("4. 3개 필터 설정 확인 (상태, 가입일, 기업유형)", function() {
            $content = file_get_contents('/var/www/html/topmkt/src/views/admin/corporate/list.php');
            return strpos($content, "'id' => 'statusFilter'") !== false &&
                   strpos($content, "'id' => 'joinDateFilter'") !== false &&
                   strpos($content, "'id' => 'companyTypeFilter'") !== false;
        });

        $this->test("5. grid-3 레이아웃 설정 확인", function() {
            $content = file_get_contents('/var/www/html/topmkt/src/views/admin/corporate/list.php');
            return strpos($content, "'layout' => 'grid-3'") !== false;
        });

        $this->test("6. JavaScript 모드 확인", function() {
            $content = file_get_contents('/var/www/html/topmkt/src/views/admin/corporate/list.php');
            return strpos($content, "'method' => 'JS'") !== false;
        });

        $this->test("7. searchInput ID 설정 확인", function() {
            $content = file_get_contents('/var/www/html/topmkt/src/views/admin/corporate/list.php');
            return strpos($content, "'searchInputId' => 'searchInput'") !== false;
        });

        $this->test("8. filterMembers() 콜백 설정 확인", function() {
            $content = file_get_contents('/var/www/html/topmkt/src/views/admin/corporate/list.php');
            return strpos($content, "'onSubmit' => 'filterMembers()'") !== false;
        });

        $this->test("9. 중복 CSS 제거 확인", function() {
            $content = file_get_contents('/var/www/html/topmkt/src/views/admin/corporate/list.php');
            return strpos($content, '/* .filter-section, .filter-row 등은 SearchFilter 컴포넌트에서 자동 제공 */') !== false;
        });

        $this->test("10. heredoc 문법 사용 확인", function() {
            $content = file_get_contents('/var/www/html/topmkt/src/views/admin/corporate/list.php');
            return strpos($content, "<<<'SCRIPTS'") !== false;
        });

        // 결과 출력
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "총 테스트: " . ($this->passed + $this->failed) . "\n";
        echo "통과: " . $this->passed . " ✅\n";
        echo "실패: " . $this->failed . " ❌\n";
        echo "성공률: " . round(($this->passed / ($this->passed + $this->failed)) * 100, 1) . "%\n";

        if ($this->failed === 0) {
            echo "\n🎉 완벽합니다! admin/corporate/list.php SearchFilter 적용 완료!\n";
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
$qa = new AdminCorporateSearchFilterQA();
$qa->run();
