# Media folders

**One folder = one section of the site.** Drop a file in, it appears on the next
page load. Delete it, it goes. Nothing in the code needs editing, ever.

Each section reads only its own folder, so adding a gallery photo can never
change the hero, and swapping the doctor's portrait can never reorder the
gallery.

```
assets/media/
├── doctor/            Portraits of Dr. Chaklader
│   ├── 01-….jpg           ← the FIRST file is the hero portrait
│   └── captions.php
├── clinic/            Clinic photographs
│   ├── 01-….jpg  02-….jpg  …
│   └── captions.php
└── videos/            Clinic videos
    ├── 01-….mp4
    ├── posters/           ← optional 01-….jpg poster for each clip
    └── captions.php
```

## Where each folder appears

| Folder | Shown on |
|---|---|
| `doctor/` | Home hero (first file), "Meet the dentist" (first file), About page |
| `clinic/` | Gallery page, and the clinic section low on the home page |
| `videos/` | Gallery page, "Videos" tab |

## The four rules

1. **Order follows the filename.** Use `01-`, `02-`, `03-` prefixes. The *first*
   file is the one used wherever a section shows a single image — so to change
   the hero portrait, rename the file you want to `01-…`.
2. **The filename becomes the caption.** `02-treatment-room.jpg` displays as
   "Treatment room". Hyphens and underscores become spaces; the number prefix is
   dropped. For better wording, edit that folder's `captions.php`.
3. **Any size is fine.** A photo straight off a phone works. Each image is
   resized once into that folder's `.cache/` and served as a static file after
   that. `.cache/` is generated and gitignored — never edit or commit it.
4. **Videos want a poster.** Put `posters/<same-name>.jpg` beside the clip, or it
   shows its own first frame. Keep clips short and compressed (about 1 MB;
   `ffmpeg -i in.mp4 -crf 30 -movflags +faststart -an out.mp4`).

## Accepted file types

Images `.jpg` `.jpeg` `.png` `.webp` · Videos `.mp4` `.webm`

## Adding a photo — the whole procedure

1. Copy the file into `assets/media/clinic/` and name it `07-waiting-area.jpg`.
2. Commit and push.

That is all. No code change, no rebuild, no cache to clear.

## Before you add a photo of a patient

Anything committed here is public, whether or not a page links to it. Do not
commit photographs or video in which a patient or a member of the public is
identifiable, unless they have given written consent.
