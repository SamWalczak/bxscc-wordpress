# Dark mode

Code: blocks 1, 3 and 3d of `bxscc-wordpress-enhancements.php`; colors in `css/components.css` (variables) and `css/theme-fixes.css` (theme elements).

## For visitors

A moon button on the header's top row switches the whole site to dark colors and back. The choice is remembered on that device and browser. Light is the default.

## How it works

- **No cookies.** The choice is stored in the browser's `localStorage` under the key `bx-theme` (value `dark`), which never leaves the visitor's device and is not sent to the server. That also keeps it compatible with the full-page cache: every visitor gets the same cached page.
- **No flash of light colors.** A one-line script at the very top of `<head>` (priority 1) reads the stored choice and sets `data-theme="dark"` on the `<html>` element before anything is painted.
- **The button.** Inserted into the header's markup before the page is sent (block 3d), so it does not pop in late. It is a real `<button>` with `aria-pressed` and a label that says what it will do ("Switch to dark mode" / "Switch to light mode"). If storage is unavailable (some private browsing modes), the switch still works for the current page.
- **Colors.** Dark mode redefines the color variables under `html[data-theme="dark"]` in `components.css` (canvas, surface, text, muted text, lines, links, menu background). Theme elements that do not use the variables get explicit dark rules in `theme-fixes.css`. The navy rules that separate sections turn gold in dark mode so they stay visible.

## Changing the dark palette

Edit the variable values in the `html[data-theme="dark"]` block near the top of `components.css` (Appearance > Site CSS > Components).
