# EMAF205 Landing

**A minimal FTP-first landing page system for workshops, services, launches and small independent projects.**

A project by [EmaF205](https://linktr.ee/emaf205).  
Made with ♥ in Milan.

Created by **EmaF205**, generative AI professor in Milan.  
Contact: **emagumroad@gmail.com**

---

## What it is

EMAF205 Landing lets you publish a clear landing page by editing simple text files.

No database.  
No admin panel.  
No build step.

The runtime is intentionally small:

```txt
index.php
page.txt
style.txt
custom.css
```

`custom.css` is optional and advanced.

---

## Download

For normal use, download the ready-to-upload ZIP from:

```txt
dist/EMAF205-Landing.zip
```

Upload the contents to your server with FTP.

---

## How it works

| File | Purpose |
|---|---|
| `index.php` | Landing engine |
| `page.txt` | Content and optional sections |
| `style.txt` | Colors and font |
| `custom.css` | Optional advanced CSS |

The structure is fixed but intelligent: optional sections disappear automatically when empty.

---

## Landing structure

```txt
Hero
Points
How it works
Details
Final CTA
Footer
```

---

## Use cases

- Workshops
- Services
- Events
- Digital products
- Small launches
- Independent projects
- Download pages

---

## Quick start

1. Download `dist/EMAF205-Landing.zip`.
2. Open `page.txt`.
3. Replace the sample content.
4. Open `style.txt`.
5. Set colors and font.
6. Upload the files by FTP.
7. Open the page in a browser.

---

## Documentation

- [Install guide](docs/INSTALL.md)
- [page.txt guide](docs/PAGE-TXT-GUIDE.md)
- [style.txt guide](docs/STYLE-TXT-GUIDE.md)
- [custom.css guide](docs/CUSTOM-CSS.md)

---

## Examples

- `examples/workshop-page.txt`
- `examples/service-page.txt`
- `examples/download-page.txt`
- `examples/light-style.txt`
- `examples/dark-style.txt`

---

## Demo

Static previews are available in:

```txt
demo/light.html
demo/dark.html
```

They are rendered from the actual PHP system and sample text files.

---

## Requirements

- PHP 8 or newer recommended
- Basic web hosting
- FTP access

---

## Security

The tool escapes page content before rendering it.

Unsafe URL schemes such as `javascript:`, `data:` and `vbscript:` are blocked.

Only simple HEX colors are accepted in `style.txt`.

---

## License

MIT License.

---

## Credits

A project by [EmaF205](https://linktr.ee/emaf205).  
Made with ♥ in Milan.
