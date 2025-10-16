#!/usr/bin/env python3
"""
파비콘 이미지 생성 스크립트
SVG에서 PNG 파비콘들을 생성합니다.
"""

from PIL import Image, ImageDraw
import os

def create_rocket_icon(size, bg_gradient=True):
    """로켓 아이콘 생성"""
    img = Image.new('RGBA', (size, size), (0, 0, 0, 0))
    draw = ImageDraw.Draw(img)
    
    # 배경 그라데이션 (근사치)
    if bg_gradient:
        for y in range(size):
            # 그라데이션 색상 계산
            ratio = y / size
            r = int(102 + (118 - 102) * ratio)  # 667eea -> 764ba2
            g = int(126 + (75 - 126) * ratio)
            b = int(234 + (162 - 234) * ratio)
            draw.rectangle([(0, y), (size, y+1)], fill=(r, g, b, 255))
        
        # 모서리 둥글게 (근사치)
        corner_radius = size // 5
        # 좌상단
        draw.rectangle([(0, 0), (corner_radius, corner_radius)], fill=(0, 0, 0, 0))
        # 우상단
        draw.rectangle([(size-corner_radius, 0), (size, corner_radius)], fill=(0, 0, 0, 0))
        # 좌하단
        draw.rectangle([(0, size-corner_radius), (corner_radius, size)], fill=(0, 0, 0, 0))
        # 우하단
        draw.rectangle([(size-corner_radius, size-corner_radius), (size, size)], fill=(0, 0, 0, 0))
    
    # 로켓 본체
    center_x, center_y = size // 2, int(size * 0.56)
    body_width, body_height = size // 4, size // 2
    draw.ellipse([
        center_x - body_width, center_y - body_height,
        center_x + body_width, center_y + body_height
    ], fill=(255, 255, 255, 255))
    
    # 로켓 창
    window_radius = size // 8
    window_y = int(size * 0.44)
    draw.ellipse([
        center_x - window_radius, window_y - window_radius,
        center_x + window_radius, window_y + window_radius
    ], fill=(66, 153, 225, 255))
    
    # 로켓 날개
    wing_points_left = [
        (int(size * 0.375), int(size * 0.625)),
        (int(size * 0.3125), int(size * 0.8125)),
        (int(size * 0.4375), int(size * 0.75))
    ]
    wing_points_right = [
        (int(size * 0.625), int(size * 0.625)),
        (int(size * 0.6875), int(size * 0.8125)),
        (int(size * 0.5625), int(size * 0.75))
    ]
    draw.polygon(wing_points_left, fill=(226, 232, 240, 255))
    draw.polygon(wing_points_right, fill=(226, 232, 240, 255))
    
    # 로켓 불꽃
    flame_y = int(size * 0.8125)
    flame_width, flame_height = size // 8, int(size * 0.1875)
    draw.ellipse([
        center_x - flame_width, flame_y - flame_height // 2,
        center_x + flame_width, flame_y + flame_height + flame_height // 2
    ], fill=(255, 140, 66, 255))
    
    # 별 장식
    star_size = max(1, size // 16)
    # 별 1
    draw.ellipse([
        int(size * 0.25) - star_size, int(size * 0.25) - star_size,
        int(size * 0.25) + star_size, int(size * 0.25) + star_size
    ], fill=(255, 217, 61, 200))
    # 별 2
    star_size2 = max(1, size // 20)
    draw.ellipse([
        int(size * 0.75) - star_size2, int(size * 0.3125) - star_size2,
        int(size * 0.75) + star_size2, int(size * 0.3125) + star_size2
    ], fill=(255, 217, 61, 150))
    # 별 3
    star_size3 = max(1, size // 24)
    draw.ellipse([
        int(size * 0.1875) - star_size3, int(size * 0.5) - star_size3,
        int(size * 0.1875) + star_size3, int(size * 0.5) + star_size3
    ], fill=(255, 217, 61, 100))
    
    return img

def main():
    """메인 함수"""
    # 32x32 파비콘 생성
    favicon_32 = create_rocket_icon(32)
    favicon_32.save('favicon-32x32.png', 'PNG')
    print("✅ favicon-32x32.png 생성 완료")
    
    # 16x16 파비콘 생성
    favicon_16 = create_rocket_icon(16)
    favicon_16.save('favicon-16x16.png', 'PNG')
    print("✅ favicon-16x16.png 생성 완료")
    
    # 180x180 Apple Touch Icon 생성
    apple_icon = create_rocket_icon(180)
    apple_icon.save('apple-touch-icon.png', 'PNG')
    print("✅ apple-touch-icon.png 생성 완료")
    
    # ICO 파일 생성 (16x16, 32x32 포함)
    favicon_16.save('favicon.ico', format='ICO', sizes=[(16, 16), (32, 32)])
    print("✅ favicon.ico 생성 완료")
    
    print("\n🎉 모든 파비콘 이미지가 생성되었습니다!")
    print("생성된 파일:")
    print("- favicon-16x16.png")
    print("- favicon-32x32.png") 
    print("- apple-touch-icon.png")
    print("- favicon.ico")

if __name__ == "__main__":
    main() 