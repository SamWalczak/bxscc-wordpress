# Video previews

Code: block 10 of `bxscc-wordpress-enhancements.php`.

An embedded YouTube player loads about a megabyte of scripts and sets YouTube's cookies the moment a page opens, even if nobody plays the video. A preview box shows the video's thumbnail with a play button instead, and loads the real player only when clicked.

## Markup

```html
<div class="bx-videos">
  <figure>
    <div class="bx-video" data-yt="VIDEO_ID" aria-label="Opening ceremony video"></div>
    <figcaption>Opening ceremony, July 16, 2026</figcaption>
  </figure>
  <figure>
    <div class="bx-video" data-yt="VIDEO_ID" data-start="95"></div>
    <figcaption>Second video, starting at 1:35</figcaption>
  </figure>
</div>
```

| Attribute | Meaning |
|---|---|
| `data-yt` | The YouTube video ID (the part after `v=` in its address). |
| `data-start` | Optional. Start time in seconds. |
| `aria-label` | Optional. Read by screen readers and used as the player's title; defaults to "Play video". |

`bx-videos` lays the previews out two across, one across on phones. A single `bx-video` can also be used on its own.

## Behavior

- The box keeps a 16:9 shape and shows the thumbnail (`i.ytimg.com/vi/ID/hqdefault.jpg`) with a navy and gold play button.
- Clicking, or pressing Enter or Space when it has keyboard focus, replaces the thumbnail with the player from `youtube-nocookie.com` (YouTube's privacy-enhanced mode), already playing.
- The box is a keyboard-focusable button (`role="button"`, `tabindex="0"`).
