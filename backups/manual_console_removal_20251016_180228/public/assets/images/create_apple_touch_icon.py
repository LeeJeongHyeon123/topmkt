#!/usr/bin/env python3
"""
Apple Touch Icon 생성 스크립트
기존 favicon.svg를 기반으로 180x180 PNG 아이콘 생성
"""

import xml.etree.ElementTree as ET
from PIL import Image, ImageDraw, ImageFont
import os

def create_apple_touch_icon():
    """Apple Touch Icon (180x180) 생성"""
    
    # 180x180 크기의 이미지 생성
    size = 180
    img = Image.new('RGBA', (size, size), (0, 0, 0, 0))
    draw = ImageDraw.Draw(img)
    
    # 배경 그라디언트 (로켓 테마)
    # 그라디언트 효과를 위한 단순한 방법
    for y in range(size):
        for x in range(size):
            # 코너 라운딩 (iOS 스타일)
            corner_radius = 32
            if (x < corner_radius and y < corner_radius):
                if (x - corner_radius)**2 + (y - corner_radius)**2 > corner_radius**2:
                    continue
            elif (x > size - corner_radius and y < corner_radius):
                if (x - (size - corner_radius))**2 + (y - corner_radius)**2 > corner_radius**2:
                    continue
            elif (x < corner_radius and y > size - corner_radius):
                if (x - corner_radius)**2 + (y - (size - corner_radius))**2 > corner_radius**2:
                    continue
            elif (x > size - corner_radius and y > size - corner_radius):
                if (x - (size - corner_radius))**2 + (y - (size - corner_radius))**2 > corner_radius**2:
                    continue
            
            # 그라디언트 색상 계산
            ratio = (x + y) / (2 * size)
            r = int(102 + (118 - 102) * ratio)  # 667eea -> 764ba2
            g = int(126 + (75 - 126) * ratio)
            b = int(234 + (162 - 234) * ratio)
            
            img.putpixel((x, y), (r, g, b, 255))
    
    # 로켓 본체 (중앙에 크게)
    center_x, center_y = size // 2, size // 2 + 10
    rocket_width, rocket_height = 28, 56
    
    # 로켓 본체 (타원)
    draw.ellipse([
        center_x - rocket_width//2, center_y - rocket_height//2,
        center_x + rocket_width//2, center_y + rocket_height//2
    ], fill=(255, 255, 255, 255))
    
    # 로켓 날개
    wing_points_left = [
        (center_x - rocket_width//2, center_y + 10),
        (center_x - rocket_width//2 - 15, center_y + 35),
        (center_x - rocket_width//2 + 8, center_y + 25)
    ]
    wing_points_right = [
        (center_x + rocket_width//2, center_y + 10),
        (center_x + rocket_width//2 + 15, center_y + 35),
        (center_x + rocket_width//2 - 8, center_y + 25)
    ]
    
    draw.polygon(wing_points_left, fill=(226, 232, 240, 255))
    draw.polygon(wing_points_right, fill=(226, 232, 240, 255))
    
    # 로켓 창
    draw.ellipse([
        center_x - 12, center_y - 20,
        center_x + 12, center_y + 4
    ], fill=(66, 153, 225, 255))
    
    # 로켓 불꽃 (화염)
    flame_y = center_y + rocket_height//2 + 5
    for i in range(5):
        flame_height = 20 - i * 3
        flame_width = 8 - i
        flame_alpha = 255 - i * 40
        
        # 화염 색상 (주황/빨강/노랑)
        colors = [
            (255, 123, 123, flame_alpha),  # 빨강
            (255, 140, 66, flame_alpha),   # 주황
            (255, 217, 61, flame_alpha)    # 노랑
        ]
        
        color = colors[i % 3]
        draw.ellipse([
            center_x - flame_width, flame_y + i * 3,
            center_x + flame_width, flame_y + i * 3 + flame_height
        ], fill=color)
    
    # 별 장식들
    stars = [
        (35, 35, 4),
        (145, 45, 3),
        (25, 90, 2),
        (155, 130, 3),
        (40, 140, 2)
    ]
    
    for star_x, star_y, star_size in stars:
        draw.ellipse([
            star_x - star_size, star_y - star_size,
            star_x + star_size, star_y + star_size
        ], fill=(255, 217, 61, 200))
    
    # 탑마케팅 텍스트 (하단에 작게)
    try:
        # 폰트 로드 시도 (시스템 기본 폰트 사용)
        font_size = 16
        # PIL 기본 폰트 사용
        text = "TOP MKT"
        text_bbox = draw.textbbox((0, 0), text)
        text_width = text_bbox[2] - text_bbox[0]
        text_x = (size - text_width) // 2
        text_y = size - 25
        
        # 텍스트 그림자
        draw.text((text_x + 1, text_y + 1), text, fill=(0, 0, 0, 100))
        # 메인 텍스트
        draw.text((text_x, text_y), text, fill=(255, 255, 255, 255))
        
    except Exception as e:
        print(f"텍스트 추가 중 오류: {e}")
    
    return img

def main():
    """메인 실행 함수"""
    try:
        # Apple Touch Icon 생성
        print("🍎 Apple Touch Icon 생성 중...")
        icon = create_apple_touch_icon()
        
        # 저장 경로
        output_path = '/workspace/public/assets/images/apple-touch-icon.png'
        
        # PNG로 저장
        icon.save(output_path, 'PNG', optimize=True)
        print(f"✅ Apple Touch Icon 생성 완료: {output_path}")
        
        # 추가 크기들도 생성
        sizes = [57, 60, 72, 76, 114, 120, 144, 152, 167, 180]
        base_path = '/workspace/public/assets/images/'
        
        for size in sizes:
            resized = icon.resize((size, size), Image.Resampling.LANCZOS)
            filename = f"apple-touch-icon-{size}x{size}.png"
            resized.save(base_path + filename, 'PNG', optimize=True)
            print(f"✅ {filename} 생성 완료")
        
        print("\n🎉 모든 Apple Touch Icon 생성 완료!")
        print("📱 iOS 기기에서 홈 화면에 추가할 때 아이콘이 표시됩니다.")
        
    except Exception as e:
        print(f"❌ 오류 발생: {e}")
        return False
    
    return True

if __name__ == "__main__":
    main()