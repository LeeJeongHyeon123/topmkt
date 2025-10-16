#!/usr/bin/env python3
"""
매우 신중한 Console 제거 스크립트
- 독립적인 console.XXX() 문만 제거
- HTML 속성 내부는 신중하게 처리
- 문법 오류가 발생하지 않도록 안전하게 제거
"""

import re
import os
from pathlib import Path

PROJECT_ROOT = "/var/www/html/topmkt"

def is_commented_line(line):
    """주석 라인인지 확인"""
    stripped = line.strip()
    return stripped.startswith('//') or stripped.startswith('/*') or stripped.startswith('*')

def remove_console_from_line(line):
    """
    한 줄에서 console을 안전하게 제거

    케이스 1: console.log(...);  -> 완전 제거
    케이스 2: onerror="console.error(...); other();"  -> console 부분만 제거
    케이스 3: 객체 속성 { console: value } -> 그대로 유지
    """
    original_line = line

    # 이미 주석이면 그대로 반환
    if is_commented_line(line):
        return line, False

    # 패턴 1: 독립적인 console 문 (줄 전체가 console로만 구성)
    # 예: "        console.log('test');"
    pattern1 = r'^\s*console\.(log|error|warn|info|debug|group|groupEnd|groupCollapsed|table|time|timeEnd|trace|dir|dirxml|count|countReset|assert|clear)\s*\([^)]*\)\s*;?\s*$'
    if re.match(pattern1, line):
        # 빈 줄로 교체 (줄 번호 유지)
        return '', True

    # 패턴 2: HTML 속성 내부의 console (onerror="console.error(...); ...")
    # console.XXX(...); 부분만 제거하고 나머지는 유지
    pattern2 = r'console\.(log|error|warn|info|debug|group|groupEnd|groupCollapsed|table|time|timeEnd|trace|dir|dirxml|count|countReset|assert|clear)\s*\([^)]*\)\s*;?\s*'

    # HTML 속성 내부인지 확인 (onerror=", onclick=" 등)
    if 'onerror=' in line or 'onclick=' in line or 'onload=' in line:
        # console.XXX(...); 만 제거
        new_line = re.sub(pattern2, '', line)
        if new_line != line:
            return new_line, True

    # 패턴 3: 여러 statement가 있는 줄에서 console만 제거
    # 예: "const x = 1; console.log(x); return x;"
    if 'console.' in line and (';' in line or line.strip().endswith(')')):
        # console statement를 찾아서 제거
        parts = []
        modified = False

        # 세미콜론으로 분리
        statements = line.split(';')
        for stmt in statements:
            if 'console.' in stmt and not '{' in stmt:
                # console statement는 제거
                modified = True
            else:
                parts.append(stmt)

        if modified:
            new_line = ';'.join(parts)
            # 빈 세미콜론 정리
            new_line = re.sub(r';\s*;', ';', new_line)
            return new_line, True

    return line, False

def process_file(filepath):
    """파일 처리"""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            lines = f.readlines()

        new_lines = []
        removed_count = 0

        for line in lines:
            new_line, removed = remove_console_from_line(line)
            new_lines.append(new_line)
            if removed:
                removed_count += 1

        if removed_count > 0:
            # 파일 저장
            with open(filepath, 'w', encoding='utf-8') as f:
                f.writelines(new_lines)
            return True, removed_count

        return False, 0

    except Exception as e:
        print(f"⚠️  오류 ({filepath}): {e}")
        return False, 0

def main():
    print("=" * 70)
    print("🔧 매우 신중한 Console 제거 시작")
    print("=" * 70)
    print()

    # console 포함 파일 찾기
    import subprocess
    result = subprocess.run(
        ['grep', '-rl', 'console.', f'{PROJECT_ROOT}/src', f'{PROJECT_ROOT}/public',
         '--include=*.php', '--include=*.js'],
        capture_output=True, text=True
    )

    files = [f for f in result.stdout.strip().split('\n') if f]
    print(f"📁 {len(files)}개 파일 발견")
    print()

    # 각 파일 처리
    processed = 0
    total_removed = 0

    for filepath in files:
        rel_path = filepath.replace(PROJECT_ROOT + '/', '')
        modified, removed = process_file(filepath)

        if modified:
            processed += 1
            total_removed += removed
            print(f"  ✅ {rel_path}: {removed}개 제거")

    print()
    print("=" * 70)
    print("📊 제거 완료 통계")
    print("=" * 70)
    print(f"처리된 파일: {processed}개")
    print(f"제거된 라인: {total_removed}개")
    print()

    # 남은 console 확인
    result = subprocess.run(
        ['grep', '-rn', 'console.', f'{PROJECT_ROOT}/src', f'{PROJECT_ROOT}/public',
         '--include=*.php', '--include=*.js'],
        capture_output=True, text=True
    )

    remaining = len(result.stdout.strip().split('\n')) if result.stdout.strip() else 0
    print(f"남은 console: {remaining}개")

    if remaining > 0:
        print()
        print("💡 남은 console은 주석이거나 객체 속성일 수 있습니다.")
        print()
        # 샘플 출력
        lines = result.stdout.strip().split('\n')[:10]
        for line in lines:
            print(f"  {line}")

    print()
    print("=" * 70)
    print("✅ Console 제거 완료!")
    print("=" * 70)

if __name__ == '__main__':
    main()
