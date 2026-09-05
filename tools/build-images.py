#!/usr/bin/env python3
"""
Build the responsive image set for the site.

Source photos supplied by the clinic are cropped to the aspect ratio each slot
uses, resized to 800w and 1200w, and written as both WebP (served first) and
JPEG (fallback). Portrait sources that must fill a landscape slot are centred on
a blurred fill of themselves rather than being cropped to nothing.

Usage:  python3 tools/build-images.py <source-dir>
"""
import sys, os
from PIL import Image, ImageFilter

OUT = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), 'assets', 'img')
os.makedirs(OUT, exist_ok=True)

# name, source file, target aspect (w/h), crop focus (0..1 across the wider axis), mode
JOBS = [
    # storefront window graphics — wide, crop to 4:3 around the right-hand panel
    ('clinic-exterior',        '4bdf0e19-image.jpg', 4/3, 0.50, 'crop'),
    # operatory — hero image, 4:3
    ('clinic-operatory',       '0807c32f-image.jpg', 4/3, 0.20, 'crop'),
    # doctor at her desk — portrait 4:5 crop centred on her
    ('dr-nabanita-chaklader',  '1e55ba9b-image.jpg', 4/5, 0.39, 'crop'),
    # entry door — very tall; pad onto a blurred fill instead of destroying it
    ('clinic-entrance',        '3ab8647d-image.jpg', 4/3, 0.50, 'pad'),
]

WIDTHS = [800, 1200]


def crop_to(im, aspect, focus):
    w, h = im.size
    target = aspect
    cur = w / h
    if cur > target:                      # too wide -> trim sides
        new_w = int(round(h * target))
        max_x = w - new_w
        x = int(round(max_x * focus))
        return im.crop((x, 0, x + new_w, h))
    if cur < target:                      # too tall -> trim top/bottom
        new_h = int(round(w / target))
        max_y = h - new_h
        y = int(round(max_y * focus))
        return im.crop((0, y, w, y + new_h))
    return im


def pad_to(im, aspect, width):
    height = int(round(width / aspect))
    bg = crop_to(im, aspect, 0.5).resize((width, height), Image.LANCZOS)
    bg = bg.filter(ImageFilter.GaussianBlur(radius=max(6, width // 45)))
    bg = Image.blend(bg, Image.new('RGB', bg.size, (238, 243, 249)), 0.35)

    scale = min(width / im.width, height / im.height)
    fg = im.resize((max(1, int(im.width * scale)), max(1, int(im.height * scale))), Image.LANCZOS)
    bg.paste(fg, ((width - fg.width) // 2, (height - fg.height) // 2))
    return bg


def main(src_dir):
    for name, fname, aspect, focus, mode in JOBS:
        path = os.path.join(src_dir, fname)
        if not os.path.isfile(path):
            print(f'  !! missing {path}')
            continue
        im = Image.open(path).convert('RGB')

        for width in WIDTHS:
            if mode == 'pad':
                out = pad_to(im, aspect, width)
            else:
                out = crop_to(im, aspect, focus)
                out = out.resize((width, int(round(width / aspect))), Image.LANCZOS)

            out.save(os.path.join(OUT, f'{name}-{width}.webp'), 'WEBP', quality=82, method=6)
            out.save(os.path.join(OUT, f'{name}-{width}.jpg'), 'JPEG',
                     quality=80, optimize=True, progressive=True)
            print(f'  {name}-{width}  {out.size[0]}x{out.size[1]}')

    # -------------------------------------------------- social share image --
    base = Image.open(os.path.join(src_dir, '0807c32f-image.jpg')).convert('RGB')
    og = crop_to(base, 1200 / 630, 0.42).resize((1200, 630), Image.LANCZOS)
    shade = Image.new('RGB', og.size, (11, 27, 61))
    og = Image.blend(og, shade, 0.34)
    og.save(os.path.join(OUT, 'og-default.jpg'), 'JPEG', quality=84, optimize=True)
    print('  og-default 1200x630')

    # ------------------------------------------------------ touch icon -----
    icon = Image.new('RGB', (180, 180), (11, 27, 61))
    icon.save(os.path.join(OUT, 'apple-touch-icon.png'), 'PNG')
    print('  apple-touch-icon 180x180 (placeholder background)')


if __name__ == '__main__':
    main(sys.argv[1] if len(sys.argv) > 1 else '.')
