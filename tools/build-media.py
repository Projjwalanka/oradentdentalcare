#!/usr/bin/env python3
"""
Build web assets from the clinic's raw media folder.

Images  -> 800w + 1200w, WebP (served first) and JPEG (fallback), cropped or
           padded to the aspect ratio the slot uses.
Videos  -> a size-sensible MP4 plus a poster frame, so a slide can preload
           nothing and still show something instantly.

Usage:  python3 tools/build-media.py /path/to/Media
"""
import os, subprocess, sys
from PIL import Image, ImageFilter

ROOT  = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
IMGD  = os.path.join(ROOT, 'assets', 'img')
VIDD  = os.path.join(ROOT, 'assets', 'video')
os.makedirs(IMGD, exist_ok=True)
os.makedirs(VIDD, exist_ok=True)

# name, source, aspect (w/h), focus 0..1 along the cropped axis, mode
IMAGE_JOBS = [
    ('clinic-room-wide',   'Image-3.jpeg', 16/10, 0.50, 'crop'),
    ('clinic-doctor-room', 'Image-2.jpeg', 16/10, 0.50, 'crop'),
    ('dr-chairside',       'Image-4.jpeg', 16/10, 0.40, 'pad'),
    ('dr-chairside-port',  'Image-4.jpeg',   4/5, 0.35, 'crop'),
]

# name, source, start seconds, duration seconds, output width
VIDEO_JOBS = [
    ('clinic-tour-1', 'Video-1.mp4', 0, 20, 960),
    ('clinic-tour-2', 'Video-2.mp4', 0, 20, 960),
]

WIDTHS = [800, 1200]


def crop_to(im, aspect, focus):
    w, h = im.size
    cur = w / h
    if cur > aspect:                       # too wide -> trim the sides
        nw = int(round(h * aspect))
        x = int(round((w - nw) * focus))
        return im.crop((x, 0, x + nw, h))
    if cur < aspect:                       # too tall -> trim top/bottom
        nh = int(round(w / aspect))
        y = int(round((h - nh) * focus))
        return im.crop((0, y, w, y + nh))
    return im


def pad_to(im, aspect, width):
    """Letterbox a portrait source onto a blurred fill of itself."""
    height = int(round(width / aspect))
    bg = crop_to(im, aspect, 0.5).resize((width, height), Image.LANCZOS)
    bg = bg.filter(ImageFilter.GaussianBlur(radius=max(8, width // 40)))
    bg = Image.blend(bg, Image.new('RGB', bg.size, (12, 30, 62)), 0.42)
    scale = min(width / im.width, height / im.height)
    fg = im.resize((max(1, int(im.width * scale)), max(1, int(im.height * scale))), Image.LANCZOS)
    bg.paste(fg, ((width - fg.width) // 2, (height - fg.height) // 2))
    return bg


def build_images(src_dir):
    for name, fname, aspect, focus, mode in IMAGE_JOBS:
        path = os.path.join(src_dir, fname)
        if not os.path.isfile(path):
            print(f'  !! missing {fname}')
            continue
        im = Image.open(path).convert('RGB')
        for width in WIDTHS:
            out = pad_to(im, aspect, width) if mode == 'pad' else \
                  crop_to(im, aspect, focus).resize((width, int(round(width / aspect))), Image.LANCZOS)
            out.save(os.path.join(IMGD, f'{name}-{width}.webp'), 'WEBP', quality=82, method=6)
            out.save(os.path.join(IMGD, f'{name}-{width}.jpg'), 'JPEG',
                     quality=80, optimize=True, progressive=True)
            print(f'  {name}-{width}  {out.size[0]}x{out.size[1]}')


def build_videos(src_dir):
    for name, fname, start, dur, width in VIDEO_JOBS:
        path = os.path.join(src_dir, fname)
        if not os.path.isfile(path):
            print(f'  !! missing {fname}')
            continue
        mp4 = os.path.join(VIDD, f'{name}.mp4')
        subprocess.run([
            'ffmpeg', '-v', 'error', '-y', '-ss', str(start), '-t', str(dur), '-i', path,
            '-vf', f'scale={width}:-2',
            '-c:v', 'libx264', '-profile:v', 'main', '-preset', 'slow', '-crf', '30',
            '-pix_fmt', 'yuv420p', '-movflags', '+faststart',   # starts playing before fully downloaded
            '-an',                                              # clinic audio adds weight and says nothing
            mp4,
        ], check=True)

        # poster frame, in the same sizes the slider expects
        poster = os.path.join(IMGD, f'{name}-1200.jpg')
        subprocess.run(['ffmpeg', '-v', 'error', '-y', '-ss', str(start + 1), '-i', path,
                        '-frames:v', '1', '-vf', 'scale=1200:-2', poster], check=True)
        im = Image.open(poster).convert('RGB')
        im = crop_to(im, 16/10, 0.5)
        im.resize((1200, 750), Image.LANCZOS).save(poster, 'JPEG', quality=82, optimize=True)
        im.resize((800, 500), Image.LANCZOS).save(
            os.path.join(IMGD, f'{name}-800.jpg'), 'JPEG', quality=80, optimize=True)
        for w in WIDTHS:
            Image.open(os.path.join(IMGD, f'{name}-{w}.jpg')).save(
                os.path.join(IMGD, f'{name}-{w}.webp'), 'WEBP', quality=82, method=6)

        mb = os.path.getsize(mp4) / 1048576
        print(f'  {name}.mp4  {mb:.1f} MB  ({dur}s @ {width}px, muted)')


if __name__ == '__main__':
    src = sys.argv[1] if len(sys.argv) > 1 else '.'
    print('images:'); build_images(src)
    print('videos:'); build_videos(src)
