#!/usr/bin/env bash
#
# Burn the site's domain into a video clip and add it to a placeholder folder,
# ready for the web: H.264, max 1280px wide, starts playing before it has fully
# downloaded, plus a poster frame.
#
#   tools/watermark-video.sh <input-video> <placeholder> <name> [--keep-audio]
#
#   tools/watermark-video.sh ~/Downloads/IMG_2041.mov gallery 03-sterilisation
#     -> assets/videos/gallery/03-sterilisation.mp4
#        assets/videos/gallery/posters/03-sterilisation.jpg
#
# The website already overlays the domain on every player in a watermarked
# placeholder (see WATERMARK in config/config.php). Burning it in as well means
# the mark survives when someone downloads the file itself.
#
# Needs ffmpeg on this computer (not on the server). Run it from Git Bash on
# Windows, or any terminal on macOS/Linux. Override the text with
# WATERMARK_TEXT="example.com" tools/watermark-video.sh …
#
# PRIVACY: the repository is public. Never add a clip in which a patient or a
# member of the public can be identified without their written consent.

set -euo pipefail

if [ $# -lt 3 ]; then
  sed -n '3,12p' "$0" | sed 's/^# \{0,1\}//'
  exit 1
fi

in=$1 placeholder=$2 name=${3%.mp4} audio=(-an)
[ "${4:-}" = "--keep-audio" ] && audio=(-c:a aac -b:a 96k)

command -v ffmpeg >/dev/null || { echo "ffmpeg is not installed (https://ffmpeg.org/download.html)" >&2; exit 1; }
[ -f "$in" ] || { echo "No such file: $in" >&2; exit 1; }

root=$(cd "$(dirname "$0")/.." && pwd)
in=$(cd "$(dirname "$in")" && pwd)/$(basename "$in")
cd "$root"                                     # relative font path: no Windows drive-letter escaping

text=${WATERMARK_TEXT:-$(sed -n "s/.*env('SITE_URL', 'https\?:\/\/\(www\.\)\?\([^'/]*\).*/\2/p" config/config.php | head -1)}
text=${text:-oradentdentalcare.com}
font=includes/fonts/DejaVuSans-Bold.ttf
out=assets/videos/$placeholder
mkdir -p "$out/posters"

# Bottom-right, ~3.5% of the frame height, white at 60% with a soft shadow —
# the same look as the stamp on the site's photos.
mark="drawtext=fontfile=$font:text='$text':fontcolor=white@0.6:shadowcolor=black@0.35:shadowx=2:shadowy=2:fontsize=h*0.035:x=w-tw-h*0.035:y=h-th-h*0.035"

ffmpeg -v error -stats -y -i "$in" \
  -vf "scale='min(1280,iw)':-2,$mark" \
  -c:v libx264 -preset slow -crf 28 -pix_fmt yuv420p -movflags +faststart \
  "${audio[@]}" "$out/$name.mp4"

# Poster from the source, not the output: the site stamps posters itself, so
# taking it from the burned clip would mark it twice.
ffmpeg -v error -y -ss 1 -i "$in" -frames:v 1 -vf "scale='min(1400,iw)':-2" -q:v 3 "$out/posters/$name.jpg"

size=$(du -k "$out/$name.mp4" | cut -f1)
echo "Wrote $out/$name.mp4 (${size} KB) and $out/posters/$name.jpg — watermark: $text"
[ "$size" -gt 4096 ] && echo "Over 4 MB: consider trimming it (add -t 30 to the first ffmpeg line)." || true
