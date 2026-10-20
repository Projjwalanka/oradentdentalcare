# Photos and videos

**One folder per place on the site. Photos live in `assets/images/`, videos in
`assets/videos/`.** Drop a file in and it appears on the next page load. Delete
it and it disappears. You never need to edit any code.

```
assets/
├── images/
│   ├── hero/        Home page, the big portrait at the top        (first file)
│   ├── dentist/     Home page, "Meet your dentist"                 (first file)
│   ├── about/       About page portrait                            (first file)
│   ├── slider/      Home page slider, "Inside the clinic"          (every file)
│   ├── gallery/     Gallery page, Photos tab                       (every file)
│   └── brand/       logo.jpeg (makes every logo and icon), social-share.jpg
└── videos/
    ├── slider/      Home page slider: clips play between the photos (every file)
    │   └── posters/
    └── gallery/     Gallery page, Videos tab                       (every file)
        └── posters/
```

Every folder reads only its own files. Adding a gallery photo can't change the
hero, and replacing the portrait can't reorder the gallery.

## Common jobs

| I want to… | Do this |
|---|---|
| Change the home page photo | Put the new photo in `images/hero/` named `01-something.jpg`, and delete or rename the old one. The **first** file is the one shown. |
| Add a photo to the slider | Copy it into `images/slider/` as e.g. `06-waiting-area.jpg`. |
| Add a video to the slider | Copy it into `videos/slider/` as e.g. `03-clinic-tour.mp4`. It plays between photos `02-…` and `04-…`. |
| Add a photo to the Gallery | Copy it into `images/gallery/`. |
| Add a video to the Gallery | Copy it into `videos/gallery/`. A Videos tab appears as soon as there is one. |
| Remove anything | Delete the file. |

Then commit and push. You don't need to rebuild anything or clear a cache.

## The rules

1. **The filename sets the order.** Use `01-`, `02-`, `03-` prefixes. In the
   slider, photos and videos share one numbering, so they can be interleaved.
2. **The filename becomes the caption.** `02-treatment-room.jpg` displays as
   "Treatment room". For better wording, and proper alt text for search engines
   and screen readers, add an entry to that folder's `captions.php`.
3. **Any size is fine.** A photo straight off a phone works. It is turned
   upright, resized once into the folder's `.cache/` and served from there.
   `.cache/` is generated and gitignored. Never edit or commit it.
4. **Give videos a poster.** Put `posters/<same-name>.jpg` beside the clip, or
   the player shows the clip's first frame. Keep clips short and compressed,
   ideally under about 4 MB.

Accepted types: photos `.jpg` `.jpeg` `.png` `.webp`, videos `.mp4` `.webm`.

## Watermark

Photos and video posters in **slider** and **gallery** are stamped with the
site's domain (`oradentdentalcare.com`) in the top-right corner, and the
domain is overlaid on the video player. The portraits (hero, dentist, about)
are left clean.

- The stamp goes on the copies the site serves. **Your original files are never
  changed**, so you can turn it off or change the text at any time. Fresh
  copies are made automatically.
- Settings are in `config/config.php` under `WATERMARK`: on/off, the text, the
  opacity, and which folders get it.
- The overlay on a video is part of the web page, not the file, so a
  downloaded clip would not carry it. To burn the domain into the clip itself,
  run this on your own computer (it needs [ffmpeg](https://ffmpeg.org)):

  ```bash
  tools/watermark-video.sh ~/Downloads/clip.mov gallery 03-sterilisation
  ```

  That writes the compressed, watermarked `03-sterilisation.mp4` and its poster
  straight into `assets/videos/gallery/`.

## Brand files

`images/brand/` holds two files, each read by its fixed name:

| File | Used for |
|---|---|
| `logo.jpeg` (or `logo.png` / `.jpg` / `.webp`) | **Every** logo and icon. See below. |
| `social-share.jpg` | Preview picture when a page is shared on WhatsApp, Facebook etc. (1200×630) |

**To change the logo, replace `logo.jpeg` with the new one.** You don't need
to convert anything. On the next page load the site builds everything from it:

- It crops off the white margin, and any dark bars from a screenshot.
- It makes the white background transparent.
- It cuts out the emblem (the picture above the name) for the header, the
  footer, the browser-tab icon and the phone home-screen icon, because the full
  logo with its words is unreadable at those sizes.
- It keeps the full logo, with the name and tagline, for Google's search results.

A PNG with a transparent background gives the cleanest result. If there is
both a `logo.png` and a `logo.jpeg`, the PNG is used. The generated files go
in `brand/.cache/` (gitignored).

## Before you add a photo or video of a patient

Anything committed here is public, whether or not a page links to it. Do not
commit photos or video in which a patient or a member of the public can be
identified unless they have given written consent.
