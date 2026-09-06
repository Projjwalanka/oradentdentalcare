#!/usr/bin/env python3
"""
Blur every face in a clinic video.

Patients are reclined, so their faces sit at roughly 90 degrees to the frame and
every upright-trained detector misses them. What actually works here:

  * MTCNN (weights bundled in the pip package, nothing to download) rather than
    Haar cascades — far fewer false positives, so no giant spurious blobs
  * each sampled frame re-run at 0 / +-45 / +-90 degrees with boxes mapped back,
    which is what catches a patient lying back in the chair
  * dense sampling (every ~0.2s), detections linked into tracks and
    INTERPOLATED across gaps, so a face stays covered on frames where the
    detector blinked
  * boxes grown generously; blur is pixelate + Gaussian through a soft ellipse,
    which is not reversible

Detection is cached to JSON so the render can be re-tuned without re-detecting.

Usage:
    python3 tools/blur_faces.py IN.mp4 OUT.mp4 --cache c.json [--dur 20]
    python3 tools/blur_faces.py IN.mp4 --probe --cache c.json
"""
import argparse, json, os, subprocess
import cv2
import numpy as np

ROTATIONS  = [0, -90, 90, -45, 45]
CONF       = 0.85   # MTCNN is precise; this keeps false positives near zero
SAMPLE_SEC = 0.2    # how often detection runs
GROW       = 2.0    # box expansion — over-covering is the safe error
MAX_GAP_S  = 2.0    # longest detector blackout bridged by interpolation
PAD_S      = 0.4    # keep blurring this long around a track's first/last sighting

_det = None


def detector():
    global _det
    if _det is None:
        from mtcnn import MTCNN
        _det = MTCNN()
    return _det


def rot_pair(w, h, deg):
    M = cv2.getRotationMatrix2D((w / 2, h / 2), deg, 1.0)
    return M, cv2.invertAffineTransform(M)


def detect(frame_bgr):
    """Face boxes (x, y, w, h) in frame coordinates, across all rotations."""
    rgb = cv2.cvtColor(frame_bgr, cv2.COLOR_BGR2RGB)
    h, w = rgb.shape[:2]
    out = []
    for deg in ROTATIONS:
        if deg == 0:
            img, M_inv = rgb, None
        else:
            M, M_inv = rot_pair(w, h, deg)
            img = cv2.warpAffine(rgb, M, (w, h), flags=cv2.INTER_LINEAR)
        try:
            res = detector().detect_faces(img)
        except Exception:
            continue
        for f in res:
            if f['confidence'] < CONF:
                continue
            x, y, bw, bh = f['box']
            if M_inv is None:
                out.append([int(x), int(y), int(bw), int(bh)])
            else:
                cx, cy = x + bw / 2, y + bh / 2
                p = M_inv @ np.array([cx, cy, 1.0])
                side = int(max(bw, bh))          # rotation makes the box square-ish
                out.append([int(p[0] - side / 2), int(p[1] - side / 2), side, side])
    return out


def centre(b):
    return (b[0] + b[2] / 2, b[1] + b[3] / 2)


def link_tracks(per_frame, max_gap_frames, tol):
    """Group detections into tracks, so gaps can be interpolated along a path."""
    tracks = []
    for f in sorted(per_frame):
        for box in per_frame[f]:
            cx, cy = centre(box)
            best, bestd = None, tol
            for t in tracks:
                lf, lb = t[-1]
                if f - lf > max_gap_frames:
                    continue
                lx, ly = centre(lb)
                d = ((cx - lx) ** 2 + (cy - ly) ** 2) ** 0.5
                if d < bestd:
                    best, bestd = t, d
            if best is None:
                tracks.append([(f, box)])
            else:
                best.append((f, box))
    return tracks


def build_coverage(tracks, count, max_gap_frames, pad_frames):
    """frame index -> boxes, with in-track gaps interpolated."""
    cover = {}
    for t in tracks:
        t.sort()
        for (f1, b1), (f2, b2) in zip(t, t[1:]):
            if f2 - f1 > max_gap_frames:
                continue
            for f in range(f1, f2 + 1):
                a = 0.0 if f2 == f1 else (f - f1) / (f2 - f1)
                cover.setdefault(f, []).append(
                    [int(b1[i] + (b2[i] - b1[i]) * a) for i in range(4)])
        for f0, b0 in ((t[0][0], t[0][1]), (t[-1][0], t[-1][1])):
            for f in range(max(0, f0 - pad_frames), min(count, f0 + pad_frames + 1)):
                cover.setdefault(f, []).append(b0)
    return cover


def grow(box, w, h, factor=GROW):
    x, y, bw, bh = box
    cx, cy = x + bw / 2, y + bh / 2
    nw, nh = bw * factor, bh * factor
    x0, y0 = max(0, int(cx - nw / 2)), max(0, int(cy - nh / 2))
    return [x0, y0, min(int(nw), w - x0), min(int(nh), h - y0)]


def apply_blur(frame, boxes):
    h, w = frame.shape[:2]
    for b in boxes:
        x, y, bw, bh = grow(b, w, h)
        x2, y2 = min(w, x + bw), min(h, y + bh)
        if x2 - x < 4 or y2 - y < 4:
            continue
        roi = frame[y:y2, x:x2]
        small = cv2.resize(roi, (max(1, roi.shape[1] // 14), max(1, roi.shape[0] // 14)),
                           interpolation=cv2.INTER_LINEAR)
        pix = cv2.resize(small, (roi.shape[1], roi.shape[0]), interpolation=cv2.INTER_NEAREST)
        pix = cv2.GaussianBlur(pix, (0, 0), max(5, roi.shape[1] / 12))
        mask = np.zeros(roi.shape[:2], np.uint8)
        cv2.ellipse(mask, (roi.shape[1] // 2, roi.shape[0] // 2),
                    (roi.shape[1] // 2, roi.shape[0] // 2), 0, 0, 360, 255, -1)
        mask = (cv2.GaussianBlur(mask, (0, 0), 7) / 255.0)[..., None]
        frame[y:y2, x:x2] = (pix * mask + roi * (1 - mask)).astype(np.uint8)
    return frame


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument('src'); ap.add_argument('dst', nargs='?')
    ap.add_argument('--start', type=float, default=0)
    ap.add_argument('--dur', type=float, default=20)
    ap.add_argument('--width', type=int, default=960)
    ap.add_argument('--cache', default=None)
    ap.add_argument('--probe', action='store_true')
    a = ap.parse_args()

    cap = cv2.VideoCapture(a.src)
    fps = cap.get(cv2.CAP_PROP_FPS) or 30.0
    first, count = int(a.start * fps), int(a.dur * fps)
    sw = int(cap.get(cv2.CAP_PROP_FRAME_WIDTH)); sh = int(cap.get(cv2.CAP_PROP_FRAME_HEIGHT))
    ow = a.width; oh = int(round(sh * ow / sw / 2) * 2)
    step = max(1, int(round(SAMPLE_SEC * fps)))

    # ------------------------------------------------------- detect --------
    if a.cache and os.path.isfile(a.cache):
        raw = json.load(open(a.cache))
        per_frame = {int(k): v for k, v in raw['boxes'].items()}
        count = raw['count']
        print(f'loaded detections for {len(per_frame)} frames from {a.cache}')
    else:
        per_frame = {}
        cap.set(cv2.CAP_PROP_POS_FRAMES, first)
        for i in range(count):
            ok, fr = cap.read()
            if not ok:
                count = i; break
            if i % step:
                continue
            b = detect(fr)
            if b:
                sc = ow / sw
                per_frame[i] = [[int(v * sc) for v in box] for box in b]
            if i % (step * 10) == 0:
                print(f'  {i/fps:5.1f}s  {len(b)} face(s)', flush=True)
        if a.cache:
            json.dump({'count': count, 'boxes': {str(k): v for k, v in per_frame.items()}},
                      open(a.cache, 'w'))
    cap.release()

    sampled = len(range(0, count, step))
    print(f'detected on {len(per_frame)}/{sampled} sampled frames')

    tracks = link_tracks(per_frame, int(MAX_GAP_S * fps), tol=ow * 0.25)
    cover = build_coverage(tracks, count, int(MAX_GAP_S * fps), int(PAD_S * fps))
    print(f'{len(tracks)} track(s); {len(cover)}/{count} frames covered '
          f'({100*len(cover)/max(count,1):.1f}%)')

    if a.probe:
        gaps, run = [], 0
        for i in range(count):
            if i in cover:
                if run: gaps.append(run); run = 0
            else: run += 1
        if run: gaps.append(run)
        print(f'uncovered runs: {len(gaps)}' +
              (f', longest {max(gaps)} frames ({max(gaps)/fps:.2f}s)' if gaps else ''))
        return

    # ------------------------------------------------------- render --------
    ff = subprocess.Popen([
        'ffmpeg', '-v', 'error', '-y',
        '-f', 'rawvideo', '-pix_fmt', 'bgr24', '-s', f'{ow}x{oh}', '-r', str(fps), '-i', '-',
        '-c:v', 'libx264', '-profile:v', 'main', '-preset', 'slow', '-crf', '30',
        '-pix_fmt', 'yuv420p', '-movflags', '+faststart', '-an', a.dst,
    ], stdin=subprocess.PIPE)
    cap = cv2.VideoCapture(a.src)
    cap.set(cv2.CAP_PROP_POS_FRAMES, first)
    for i in range(count):
        ok, fr = cap.read()
        if not ok:
            break
        fr = cv2.resize(fr, (ow, oh), interpolation=cv2.INTER_AREA)
        if i in cover:
            fr = apply_blur(fr, cover[i])
        ff.stdin.write(fr.tobytes())
    cap.release(); ff.stdin.close(); ff.wait()
    print(f'wrote {a.dst}  ({os.path.getsize(a.dst)/1048576:.1f} MB)')


if __name__ == '__main__':
    main()
