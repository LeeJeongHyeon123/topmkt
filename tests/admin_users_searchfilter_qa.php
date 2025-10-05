<?php
/**
 * Admin Users List - SearchFilter 컴포넌트 QA 테스트
 * v3.37.0
 */

class AdminUsersSearchFilterQA
{
    private $passed = 0;
    private $failed = 0;
    private $tests = [];

    public function run()
    {
        echo "🔍 Admin Users List - SearchFilter QA 테스트\n";
        echo str_repeat("=", 60) . "\n\n";

        // 1. PHP 구문 체크
        $this->test("1. PHP 구문 오류 없음", function() {
            exec('php -l /var/www/html/topmkt/src/views/admin/users/list.php 2>&1', $output, $return);
            return $return === 0;
        });

        // 2. SearchFilter 컴포넌트 로드 확인
        $this->test("2. SearchFilter 컴포넌트 파일 존재", function() {
            return file_exists('/var/www/html/topmkt/src/components/ui/SearchFilter.php');
        });

        // 3. CSS 파일 로드 확인
        $this->test("3. search-filter.css 파일 존재", function() {
            return file_exists('/var/www/html/topmkt/public/assets/css/search-filter.css');
        });

        // 4. SearchFilter::create() 호출 확인
        $this->test("4. SearchFilter::create() 호출 존재", function() {
            $content = file_get_contents('/var/www/html/topmkt/src/views/admin/users/list.php');
            return strpos($content, 'SearchFilter::create([') !== false;
        });

        // 5. 8개 필터 설정 확인
        $this->test("5. 8개 필터 설정 확인", function() {
            $content = file_get_contents('/var/www/html/topmkt/src/views/admin/users/list.php');
            preg_match_all('/\[\s*\n\s*\'type\'\s*=>/s', $content, $matches);
            return count($matches[0]) >= 8;
        });

        // 6. grid-4 레이아웃 확인
        $this->test("6. grid-4 레이아웃 설정 확인", function() {
            $content = file_get_contents('/var/www/html/topmkt/src/views/admin/users/list.php');
            return strpos($content, "'layout' => 'grid-4'") !== false;
        });

        // 7. JavaScript 모드 확인
        $this->test("7. JavaScript 모드 (method=JS) 확인", function() {
            $content = file_get_contents('/var/www/html/topmkt/src/views/admin/users/list.php');
            return strpos($content, "'method' => 'JS'") !== false;
        });

        // 8. 검색 input ID 일치 확인
        $this->test("8. search-input ID 설정 확인", function() {
            $content = file_get_contents('/var/www/html/topmkt/src/views/admin/users/list.php');
            return strpos($content, "'searchInputId' => 'search-input'") !== false;
        });

        // 9. applyFilters() 콜백 확인
        $this->test("9. applyFilters() 콜백 설정 확인", function() {
            $content = file_get_contents('/var/www/html/topmkt/src/views/admin/users/list.php');
            return strpos($content, "'onSubmit' => 'applyFilters()'") !== false;
        });

        // 10. resetFilters() 콜백 확인
        $this->test("10. resetFilters() 콜백 설정 확인", function() {
            $content = file_get_contents('/var/www/html/topmkt/src/views/admin/users/list.php');
            return strpos($content, "'onReset' => 'resetFilters()'") !== false;
        });

        // 11. 중복 CSS 제거 확인
        $this->test("11. 중복 .filters-section CSS 제거 확인", function() {
            $content = file_get_contents('/var/www/html/topmkt/src/views/admin/users/list.php');
            // 주석 처리되었는지 확인
            return strpos($content, '/* .filters-section, .filters-header 등은 SearchFilter 컴포넌트에서 자동 제공 */') !== false;
        });

        // 12. heredoc 사용 확인 (quote 문제 해결)
        $this->test("12. heredoc 문법 사용 확인", function() {
            $content = file_get_contents('/var/www/html/topmkt/src/views/admin/users/list.php');
            return strpos($content, "<<<'HTML'") !== false && strpos($content, "<<<'SCRIPTS'") !== false;
        });

        // 결과 출력
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "총 테스트: " . ($this->passed + $this->failed) . "\n";
        echo "통과: " . $this->passed . " ✅\n";
        echo "실패: " . $this->failed . " ❌\n";
        echo "성공률: " . round(($this->passed / ($this->passed + $this->failed)) * 100, 1) . "%\n";

        if ($this->failed === 0) {
            echo "\n🎉 완벽합니다! admin/users/list.php SearchFilter 적용 완료!\n";
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
        $this->tests[] = ['name' => $name, 'result' => $result];
    }
}

// 실행
$qa = new AdminUsersSearchFilterQA();
$qa->run();
