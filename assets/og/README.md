# OG images

Place static 1200×630 PNG images here:

- `green.png` · `yellow.png` · `red.png` — per-verdict fallback (recommended)
- `<job-id>.png` — optional per-job override (e.g. `golvvarme.png`)

The plugin (`ampy-behorighetskollen.php`, `ampy_bk_dynamic_og`) picks them up
automatically and sets `og:image`/`twitter:image` when the URL has `?jobb=…`.

If none are present, the tool still works — only the visual share preview will be
generic.
