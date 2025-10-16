#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
OG 이미지 생성 스크립트
SVG를 PNG로 변환하여 소셜 미디어 호환성 향상
"""

from PIL import Image, ImageDraw, ImageFont
import os

def create_og_image():
    # 이미지 크기 (Facebook/Twitter OG 권장 크기)
    width, height = 1200, 630
    
    # 히어로 섹션과 동일한 그라디언트 배경 생성
    img = Image.new('RGB', (width, height), color='#667eea')
    draw = ImageDraw.Draw(img)
    
    # 그라디언트 효과 (간단한 선형 그라디언트)
    for y in range(height):
        # 색상 보간 (667eea -> 764ba2)
        ratio = y / height
        r = int(0x66 + (0x76 - 0x66) * ratio)
        g = int(0x7e + (0x4b - 0x7e) * ratio)
        b = int(0xea + (0xa2 - 0xea) * ratio)
        color = (r, g, b)
        draw.line([(0, y), (width, y)], fill=color)
    
    # 배경 장식 원들 (반투명)
    overlay = Image.new('RGBA', (width, height), (255, 255, 255, 0))
    overlay_draw = ImageDraw.Draw(overlay)
    
    # 배경 패턴 원들
    circles = [
        (200, 150, 80),
        (1000, 100, 60),
        (1100, 400, 100),
        (100, 500, 70)
    ]
    
    for x, y, r in circles:
        overlay_draw.ellipse([x-r, y-r, x+r, y+r], fill=(255, 255, 255, 25))
    
    img = Image.alpha_composite(img.convert('RGBA'), overlay).convert('RGB')
    draw = ImageDraw.Draw(img)
    
    # 텍스트 추가를 위한 폰트 로드 시도 (정확한 경로로)
    try:
        # 설치된 나눔고딕 폰트 경로
        title_font = ImageFont.truetype('/usr/share/fonts/korean/TrueType/NanumGothic-Regular.ttf', 96)
        subtitle_font = ImageFont.truetype('/usr/share/fonts/korean/TrueType/NanumGothic-Regular.ttf', 36)
        desc_font = ImageFont.truetype('/usr/share/fonts/korean/TrueType/NanumGothic-Regular.ttf', 32)
        print("나눔고딕 폰트 로드 성공!")
    except Exception as e:
        print("나눔고딕 폰트 로드 실패: " + str(e))
        try:
            # DejaVu 폰트 대체
            title_font = ImageFont.truetype('/usr/share/fonts/dejavu/DejaVuSans.ttf', 96)
            subtitle_font = ImageFont.truetype('/usr/share/fonts/dejavu/DejaVuSans.ttf', 36)
            desc_font = ImageFont.truetype('/usr/share/fonts/dejavu/DejaVuSans.ttf', 32)
            print("DejaVu 폰트 로드 성공!")
        except Exception as e2:
            print("DejaVu 폰트 로드도 실패: " + str(e2))
            # 기본 폰트
            title_font = ImageFont.load_default()
            subtitle_font = ImageFont.load_default()
            desc_font = ImageFont.load_default()
            print("기본 폰트 사용")
    
    # 중앙 기준점
    center_x, center_y = width // 2, height // 2
    
    # 텍스트 추가 (영문 버전으로 변경)
    # 메인 타이틀
    title_text = "TOPMKT"
    title_width = draw.textsize(title_text, font=title_font)[0]
    draw.text((center_x - title_width//2, center_y - 60), title_text, 
              fill='white', font=title_font)
    
    # 영문 서브타이틀
    subtitle_text = "TOP MARKETING"
    subtitle_width = draw.textsize(subtitle_text, font=subtitle_font)[0]
    draw.text((center_x - subtitle_width//2, center_y + 50), subtitle_text, 
              fill=(255, 255, 255), font=subtitle_font)
    
    # 설명 텍스트
    desc_text = "Marketing Community Platform"
    desc_width = draw.textsize(desc_text, font=desc_font)[0]
    draw.text((center_x - desc_width//2, center_y + 110), desc_text, 
              fill=(255, 255, 255), font=desc_font)
    
    # 하단 장식선
    line_y = height - 50
    draw.rectangle([width//2 - 300, line_y, width//2 + 300, line_y + 2], fill=(255, 255, 255))
    
    # 장식 점들
    for i, x_offset in enumerate([-20, 0, 20]):
        draw.ellipse([center_x + x_offset - 3, line_y - 2, center_x + x_offset + 3, line_y + 4], 
                    fill='white')
    
    return img

def main():
    """메인 실행 함수"""
    print("OG 이미지 생성 중...")
    print("디버깅: Python 버전 및 PIL 정보 확인")
    
    # 시스템 정보 출력
    import sys
    print("Python 버전: " + str(sys.version))
    
    # PIL 버전 확인
    try:
        from PIL import __version__
        print("PIL 버전: " + str(__version__))
    except:
        print("PIL 버전 확인 불가")
    
    # 폰트 파일 존재 확인
    font_path = '/usr/share/fonts/korean/TrueType/NanumGothic-Regular.ttf'
    if os.path.exists(font_path):
        print("폰트 파일 존재함: " + font_path)
    else:
        print("폰트 파일 없음: " + font_path)
    
    # 이미지 생성
    og_image = create_og_image()
    
    # 현재 스크립트 위치 기준으로 저장
    script_dir = os.path.dirname(os.path.abspath(__file__))
    output_path = os.path.join(script_dir, 'topmkt-og-image.png')
    
    # PNG로 저장
    og_image.save(output_path, 'PNG', quality=95, optimize=True)
    
    print("✅ OG 이미지가 생성되었습니다: " + output_path)
    print("📐 크기: 1200x630px (Facebook/Twitter OG 권장 크기)")
    print("🎨 히어로 섹션과 동일한 그라디언트 배경 적용")

if __name__ == "__main__":
    main()