<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>[불매 알림] 고고다이브 — 안전과 존중이 확인될 때까지 저는 이용을 중단합니다</title>
    <style>
        /* Reset & Base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            font-size: 16px;
            scroll-behavior: smooth;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Noto Sans KR", sans-serif;
            line-height: 1.7;
            color: #2d3748;
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            min-height: 100vh;
        }

        /* Container */
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
            background: #ffffff;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            border-radius: 20px;
            margin-top: 40px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #e53e3e 0%, #dd6b20 50%, #d69e2e 100%);
            border-radius: 20px 20px 0 0;
        }

        /* Typography */
        h1 {
            font-size: 2.25rem;
            font-weight: 800;
            color: #1a202c;
            margin-bottom: 32px;
            line-height: 1.2;
            text-align: center;
            padding: 20px 0;
            border-bottom: 3px solid #e2e8f0;
            position: relative;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, #e53e3e, #dd6b20);
            border-radius: 2px;
        }

        h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2d3748;
            margin: 40px 0 20px 0;
            padding-left: 16px;
            border-left: 4px solid #4299e1;
            background: linear-gradient(90deg, #ebf8ff, transparent);
            padding: 12px 16px;
            border-radius: 0 8px 8px 0;
        }

        p {
            margin-bottom: 20px;
            font-size: 1.05rem;
            text-align: justify;
            word-break: keep-all;
        }

        /* Strong Text */
        strong {
            color: #e53e3e;
            font-weight: 700;
            padding: 2px 4px;
            background: rgba(229, 62, 62, 0.1);
            border-radius: 4px;
        }

        /* Lists */
        ul {
            margin: 20px 0;
            padding-left: 0;
            list-style: none;
        }

        li {
            margin-bottom: 16px;
            padding: 16px 20px;
            background: #f8f9fa;
            border-left: 4px solid #68d391;
            border-radius: 0 8px 8px 0;
            position: relative;
            font-size: 1.05rem;
            line-height: 1.6;
        }

        li::before {
            content: '•';
            color: #68d391;
            font-weight: bold;
            font-size: 1.5rem;
            position: absolute;
            left: 8px;
            top: 12px;
        }

        /* Fact Section */
        .fact-section {
            background: linear-gradient(135deg, #ebf8ff 0%, #bee3f8 100%);
            border: 1px solid #90cdf4;
            border-radius: 12px;
            padding: 24px;
            margin: 24px 0;
            position: relative;
        }

        .fact-section::before {
            content: '📋';
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 1.5rem;
        }

        .fact-section h2 {
            background: transparent;
            border: none;
            padding-left: 40px;
            margin-top: 0;
            color: #2b6cb0;
        }

        /* Opinion Section */
        .opinion-section {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border: 1px solid #cbd5e0;
            border-radius: 12px;
            padding: 24px;
            margin: 24px 0;
            position: relative;
        }

        .opinion-section::before {
            content: '💭';
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 1.5rem;
        }

        .opinion-section h2 {
            background: transparent;
            border: none;
            padding-left: 40px;
            margin-top: 0;
            color: #4a5568;
        }

        .opinion-section ol {
            padding-left: 20px;
        }

        .opinion-section ol li {
            list-style: decimal;
            background: #ffffff;
            border-left: 4px solid #a0aec0;
        }

        /* Decision Section */
        .decision-section {
            background: linear-gradient(135deg, #fed7d7 0%, #feb2b2 100%);
            border: 1px solid #fc8181;
            border-radius: 12px;
            padding: 24px;
            margin: 24px 0;
            position: relative;
        }

        .decision-section::before {
            content: '⚠️';
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 1.5rem;
        }

        .decision-section h2 {
            background: transparent;
            border: none;
            padding-left: 40px;
            margin-top: 0;
            color: #c53030;
        }

        /* Final Section */
        .final-section {
            background: linear-gradient(135deg, #f0fff4 0%, #c6f6d5 100%);
            border: 1px solid #9ae6b4;
            border-radius: 12px;
            padding: 24px;
            margin: 24px 0;
            position: relative;
        }

        .final-section::before {
            content: '🤝';
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 1.5rem;
        }

        .final-section h2 {
            background: transparent;
            border: none;
            padding-left: 40px;
            margin-top: 0;
            color: #22543d;
        }

        /* Disclaimer */
        .disclaimer {
            background: linear-gradient(135deg, #fffaf0 0%, #feebc8 100%);
            border: 2px solid #ed8936;
            border-radius: 12px;
            padding: 20px;
            margin: 32px 0;
            text-align: center;
            font-weight: 700;
            color: #c05621;
            font-size: 1.1rem;
            position: relative;
        }

        .disclaimer::before {
            content: '⚖️';
            display: block;
            font-size: 2rem;
            margin-bottom: 10px;
        }

        /* Date & Location */
        .highlight-info {
            background: linear-gradient(90deg, #fed7e2, #fbb6ce);
            color: #97266d;
            padding: 3px 8px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.95rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                margin: 20px auto;
                padding: 24px 16px;
                border-radius: 12px;
            }

            h1 {
                font-size: 1.75rem;
                margin-bottom: 24px;
                padding: 16px 0;
            }

            h2 {
                font-size: 1.25rem;
                margin: 32px 0 16px 0;
                padding: 10px 12px;
            }

            p {
                font-size: 1rem;
                margin-bottom: 16px;
            }

            li {
                padding: 12px 16px;
                font-size: 1rem;
            }

            .fact-section,
            .opinion-section,
            .decision-section,
            .final-section {
                padding: 20px 16px;
                margin: 20px 0;
            }

            .fact-section h2,
            .opinion-section h2,
            .decision-section h2,
            .final-section h2 {
                padding-left: 32px;
                font-size: 1.1rem;
            }

            .disclaimer {
                padding: 16px;
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            html {
                font-size: 14px;
            }

            .container {
                margin: 10px;
                padding: 20px 12px;
            }

            h1 {
                font-size: 1.5rem;
                line-height: 1.3;
            }

            h2 {
                font-size: 1.1rem;
            }

            li {
                padding: 10px 12px;
            }

            .fact-section::before,
            .opinion-section::before,
            .decision-section::before,
            .final-section::before {
                font-size: 1.25rem;
            }
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
            }
            
            .container {
                box-shadow: none;
                margin: 0;
                padding: 20px;
            }
            
            .container::before {
                display: none;
            }
        }

        /* Accessibility */
        @media (prefers-reduced-motion: reduce) {
            html {
                scroll-behavior: auto;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            body {
                background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
                color: #e2e8f0;
            }
            
            .container {
                background: #2d3748;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            }
            
            h1, h2 {
                color: #f7fafc;
            }
            
            li {
                background: #4a5568;
                color: #e2e8f0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>[불매 알림] 고고다이브 — 안전과 존중이 확인될 때까지 저는 이용을 중단합니다</h1>

        <div class="intro-section">
            <h2>왜 이 글을 쓰는가</h2>
            <p>수강생의 안전과 교육 현장의 존중 문화는 양보할 수 없는 기준입니다. 아래 내용은 제가 직접 겪은 사실과 그에 대한 <strong>제 의견을 분리</strong>하여 기록한 것입니다. 목적은 특정 개인 비난이 아니라 수강생 안전과 교육 품질 향상을 위한 공익적 문제 제기입니다.</p>
        </div>

        <div class="fact-section">
            <h2>사실 요약(팩트)</h2>
            <ul>
                <li><strong>일시/장소:</strong> <span class="highlight-info">2025년 8월 28일(목) 17:00~20:00, 시흥 파라다이브</span></li>
                <li><strong>수업 구조:</strong> 5명의 수강생이 순번대로 하강, 매 하강마다 '버디'가 상승을 보조</li>
                <li><strong>혼선 발생:</strong> 저는 2번 순번으로 1번의 버디를 수행했으나, 사후에야 제가 4번의 버디로 지정되어 있었다는 사실을 알게 됐습니다. 해당 지정은 수업 중 명확히 통지·확인되지 않아 <strong>4번 상승 시 버디 공백이 발생했다고 판단합니다.</strong></li>
                <li><strong>공개 발언:</strong> 그 직후, 여러 수강생이 있는 자리에서 저를 향해 "정신 차리세요"라는 발언이 있었고, <strong>머리를 두드리는 제스처를 보았습니다.</strong></li>
                <li><strong>공식 입장(기관 발표 요지):</strong> 고고다이브는 버디 전달 미흡과 위 표현·제스처로 불쾌감이 있을 수 있었음을 인정·사과하고, 사전 브리핑 보완·커뮤니케이션 가이드라인 강화·교육 품질 프로그램 조기 도입을 약속했습니다(시말서는 내부 규정상 대상 아님이라고 회신).</li>
            </ul>
        </div>

        <div class="opinion-section">
            <h2>쟁점에 대한 제 판단(의견)</h2>
            <ol>
                <li><strong>버디 지정·확인 절차는 안전의 핵심</strong>입니다. 지정·확인이 모호하면 상승 구간에서 공백이 생기고, 이는 즉시 위험 요인이 됩니다.</li>
                <li><strong>공개적 질책과 제스처는 수강생의 심리적 안전을 해칩니다.</strong> 심리적 안전이 무너지면 보고·교정·협력이 위축되어 안전수칙 이행도 떨어질 수 있습니다. 지적이 필요하다면 공개 망신이 아니라 <strong>명확한 설명과 차분한 재브리핑</strong>이 우선되어야 합니다.</li>
                <li>기관의 사과와 개선 약속은 확인했습니다. 다만 약속은 <strong>실행 일정과 결과</strong>로 확인되어야 신뢰를 회복할 수 있습니다.</li>
            </ol>
        </div>

        <div class="decision-section">
            <h2>저의 결정(불매 선언)</h2>
            <ul>
                <li>저는 오늘부터 <strong>고고다이브 이용을 중단</strong>합니다. 안전과 존중의 기준이 <strong>구체적 조치와 이행 결과</strong>로 확인될 때까지 불매를 이어갑니다.</li>
                <li>동의하시는 분께서는 <strong>각자의 판단에 따라</strong> 이용 중단을 검토해 주시기 바랍니다. 이 글의 목적은 안전과 품질 개선이며, 비방·욕설·항의전화 유도 등 <strong>과격한 행동은 지양</strong>합니다.</li>
            </ul>
        </div>

        <div class="final-section">
            <h2>마지막으로</h2>
            <p>본 글은 제 경험에 근거한 <strong>사실과 의견을 분리</strong>해 작성했습니다. <strong>사실과 다른 부분이 확인되면 즉시 정정</strong>하겠습니다. 판단은 각자의 몫입니다. 고고다이브의 성실한 이행과 책임 있는 후속 조치를 기대합니다.</p>
        </div>

        <div class="disclaimer">
            본 글은 제가 직접 겪은 사실에 기초하여 작성되었으며, 사실과 다르면 즉시 정정하겠습니다.
        </div>
    </div>
</body>
</html>