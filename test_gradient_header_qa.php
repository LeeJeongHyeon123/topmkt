<?php
/**
 * Gradient Header Component QA Test
 *
 * 그라디언트 헤더 컴포넌트 통합 QA 테스트
 */

define('SRC_PATH', __DIR__ . '/src');
require_once SRC_PATH . '/components/ui/GradientHeader.php';

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gradient Header Component QA Test</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            padding: 40px;
            background: #f7fafc;
            margin: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            text-align: center;
            color: #1a202c;
            margin-bottom: 10px;
        }
        .intro {
            text-align: center;
            color: #718096;
            margin-bottom: 40px;
        }
        .test-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .test-section h2 {
            margin-top: 0;
            color: #2d3748;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .test-info {
            background: #edf2f7;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #4a5568;
        }
        .test-info strong {
            color: #2d3748;
        }
        .code-block {
            background: #2d3748;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            overflow-x: auto;
            margin: 15px 0;
        }
        .summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
        }
        .summary h2 {
            margin-top: 0;
            color: white;
            border: none;
        }
        .checklist {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .checklist-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 8px;
        }
        .checklist-item h4 {
            margin-top: 0;
            color: white;
        }
        .checklist-item ul {
            margin: 10px 0 0 0;
            padding-left: 20px;
            list-style: none;
        }
        .checklist-item li:before {
            content: "✅ ";
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎨 Gradient Header Component QA Test</h1>
        <p class="intro">그라디언트 헤더 컴포넌트의 다양한 옵션과 스타일을 테스트합니다</p>

        <!-- Test 1: 기본 헤더 (제목만) -->
        <div class="test-section">
            <h2>Test 1: 기본 헤더 (제목만)</h2>
            <div class="test-info">
                <strong>테스트 내용:</strong> 제목만 있는 가장 기본적인 그라디언트 헤더
            </div>
            <div class="code-block">
&lt;?= renderGradientHeader(['title' => '기본 그라디언트 헤더']) ?&gt;
            </div>
            <?= renderGradientHeader(['title' => '기본 그라디언트 헤더']) ?>
        </div>

        <!-- Test 2: 제목 + 부제목 -->
        <div class="test-section">
            <h2>Test 2: 제목 + 부제목</h2>
            <div class="test-info">
                <strong>테스트 내용:</strong> 제목과 부제목이 있는 헤더
            </div>
            <div class="code-block">
&lt;?= renderGradientHeader([
    'title' => '2025년 최신 마케팅 전략',
    'subtitle' => '글로벌 마케팅 전문가가 알려주는 실전 전략'
]) ?&gt;
            </div>
            <?= renderGradientHeader([
                'title' => '2025년 최신 마케팅 전략',
                'subtitle' => '글로벌 마케팅 전문가가 알려주는 실전 전략'
            ]) ?>
        </div>

        <!-- Test 3: 배지 포함 -->
        <div class="test-section">
            <h2>Test 3: 배지 포함</h2>
            <div class="test-info">
                <strong>테스트 내용:</strong> 배지, 제목, 부제목이 모두 있는 완전한 헤더
            </div>
            <div class="code-block">
&lt;?= renderGradientHeader([
    'title' => '디지털 마케팅 마스터 클래스',
    'subtitle' => '4주 만에 완성하는 실전 마케팅',
    'badge' => '온라인',
    'badgeIcon' => '📚'
]) ?&gt;
            </div>
            <?= renderGradientHeader([
                'title' => '디지털 마케팅 마스터 클래스',
                'subtitle' => '4주 만에 완성하는 실전 마케팅',
                'badge' => '온라인',
                'badgeIcon' => '📚'
            ]) ?>
        </div>

        <!-- Test 4: 크기 테스트 (sm, md, lg, xl) -->
        <div class="test-section">
            <h2>Test 4: 크기 테스트</h2>
            <div class="test-info">
                <strong>테스트 내용:</strong> 4가지 크기 옵션 (sm, md, lg, xl)
            </div>

            <h3 style="margin-top: 20px;">Small (sm)</h3>
            <?= renderGradientHeader([
                'title' => '작은 헤더',
                'subtitle' => '섹션 헤더에 적합',
                'size' => 'sm'
            ]) ?>

            <h3 style="margin-top: 20px;">Medium (md) - 기본값</h3>
            <?= renderGradientHeader([
                'title' => '중간 헤더',
                'subtitle' => '대부분의 페이지에 적합',
                'size' => 'md'
            ]) ?>

            <h3 style="margin-top: 20px;">Large (lg)</h3>
            <?= renderGradientHeader([
                'title' => '큰 헤더',
                'subtitle' => '중요한 페이지에 적합',
                'size' => 'lg'
            ]) ?>

            <h3 style="margin-top: 20px;">Extra Large (xl)</h3>
            <?= renderGradientHeader([
                'title' => '아주 큰 헤더',
                'subtitle' => '랜딩 페이지에 적합',
                'size' => 'xl'
            ]) ?>
        </div>

        <!-- Test 5: 색상 테마 -->
        <div class="test-section">
            <h2>Test 5: 색상 테마</h2>
            <div class="test-info">
                <strong>테스트 내용:</strong> 6가지 색상 테마 (purple, blue, green, orange, red, pink)
            </div>

            <h3 style="margin-top: 20px;">Purple (기본값)</h3>
            <?= renderGradientHeader([
                'title' => 'Purple Theme',
                'theme' => 'purple',
                'size' => 'sm'
            ]) ?>

            <h3 style="margin-top: 20px;">Blue</h3>
            <?= renderGradientHeader([
                'title' => 'Blue Theme',
                'theme' => 'blue',
                'size' => 'sm'
            ]) ?>

            <h3 style="margin-top: 20px;">Green</h3>
            <?= renderGradientHeader([
                'title' => 'Green Theme',
                'theme' => 'green',
                'size' => 'sm'
            ]) ?>

            <h3 style="margin-top: 20px;">Orange</h3>
            <?= renderGradientHeader([
                'title' => 'Orange Theme',
                'theme' => 'orange',
                'size' => 'sm'
            ]) ?>

            <h3 style="margin-top: 20px;">Red</h3>
            <?= renderGradientHeader([
                'title' => 'Red Theme',
                'theme' => 'red',
                'size' => 'sm'
            ]) ?>

            <h3 style="margin-top: 20px;">Pink</h3>
            <?= renderGradientHeader([
                'title' => 'Pink Theme',
                'theme' => 'pink',
                'size' => 'sm'
            ]) ?>
        </div>

        <!-- Test 6: 정렬 옵션 -->
        <div class="test-section">
            <h2>Test 6: 정렬 옵션</h2>
            <div class="test-info">
                <strong>테스트 내용:</strong> 3가지 정렬 옵션 (left, center, right)
            </div>

            <h3 style="margin-top: 20px;">Left (기본값)</h3>
            <?= renderGradientHeader([
                'title' => '왼쪽 정렬',
                'subtitle' => '기본 정렬 방식',
                'align' => 'left',
                'size' => 'sm'
            ]) ?>

            <h3 style="margin-top: 20px;">Center</h3>
            <?= renderGradientHeader([
                'title' => '중앙 정렬',
                'subtitle' => '랜딩 페이지에 적합',
                'align' => 'center',
                'size' => 'sm'
            ]) ?>

            <h3 style="margin-top: 20px;">Right</h3>
            <?= renderGradientHeader([
                'title' => '오른쪽 정렬',
                'subtitle' => '특별한 디자인에 적합',
                'align' => 'right',
                'size' => 'sm'
            ]) ?>
        </div>

        <!-- Test 7: 헬퍼 함수 -->
        <div class="test-section">
            <h2>Test 7: 헬퍼 함수</h2>
            <div class="test-info">
                <strong>테스트 내용:</strong> 간편한 헬퍼 함수 테스트
            </div>

            <h3 style="margin-top: 20px;">renderSimpleGradientHeader()</h3>
            <div class="code-block">
&lt;?= renderSimpleGradientHeader('간단한 헤더') ?&gt;
            </div>
            <?= renderSimpleGradientHeader('간단한 헤더') ?>

            <h3 style="margin-top: 20px;">renderGradientSectionHeader()</h3>
            <div class="code-block">
&lt;?= renderGradientSectionHeader('섹션 헤더', '작고 중앙정렬된 헤더') ?&gt;
            </div>
            <?= renderGradientSectionHeader('섹션 헤더', '작고 중앙정렬된 헤더') ?>
        </div>

        <!-- QA 체크리스트 -->
        <div class="summary">
            <h2>📋 QA 체크리스트</h2>
            <div class="checklist">
                <div class="checklist-item">
                    <h4>✅ 기본 기능</h4>
                    <ul>
                        <li>제목 렌더링</li>
                        <li>부제목 렌더링</li>
                        <li>배지 렌더링</li>
                        <li>HTML 이스케이프</li>
                    </ul>
                </div>
                <div class="checklist-item">
                    <h4>✅ 크기 옵션</h4>
                    <ul>
                        <li>Small (sm)</li>
                        <li>Medium (md)</li>
                        <li>Large (lg)</li>
                        <li>Extra Large (xl)</li>
                    </ul>
                </div>
                <div class="checklist-item">
                    <h4>✅ 색상 테마</h4>
                    <ul>
                        <li>Purple (기본)</li>
                        <li>Blue</li>
                        <li>Green</li>
                        <li>Orange</li>
                        <li>Red</li>
                        <li>Pink</li>
                    </ul>
                </div>
                <div class="checklist-item">
                    <h4>✅ 정렬 옵션</h4>
                    <ul>
                        <li>Left (기본)</li>
                        <li>Center</li>
                        <li>Right</li>
                    </ul>
                </div>
                <div class="checklist-item">
                    <h4>✅ 헬퍼 함수</h4>
                    <ul>
                        <li>renderSimpleGradientHeader()</li>
                        <li>renderGradientSectionHeader()</li>
                    </ul>
                </div>
                <div class="checklist-item">
                    <h4>✅ 반응형</h4>
                    <ul>
                        <li>모바일 (320px~)</li>
                        <li>태블릿 (768px~)</li>
                        <li>데스크톱 (1024px~)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
