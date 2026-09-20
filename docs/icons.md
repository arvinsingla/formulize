---
layout: default
permalink: developers/icons/
title: Icons - Standardization Investigation
---

# Icons in Formulize - Current State and a Proposed Standard

**Status: recommendation only. No code changes accompany this document.** It exists to
answer [issue #150](https://github.com/jegelstaff/formulize/issues/150) ("Investigate
approach to standardize use of icons in formulize and themes") and to give maintainers
something concrete to accept, reject, or amend before any implementation issue is opened.
Nothing described below is built. All line references are against the `new-theme` branch at
the time of writing.

---

## 1. Current state

Formulize does not have *an* icon system. It has **nine** different mechanisms that all
produce something the user perceives as an icon, and they have accumulated rather than
replaced one another.

### 1.1 The `formulize-icons` icon font (core, 33 glyphs)

A [Font Custom](http://fontcustom.com)-generated webfont lives at
`modules/formulize/templates/css/formulize-icons/` (`.eot`, `.woff`, `.ttf`, `.svg` plus the
33 source vectors and `fontcustom.yml` under `fontcustom/`). It is declared at
`modules/formulize/templates/css/formulize.css:4-9` and again in `formulize-admin.css:4-9`,
and exposes `.icon-add` … `.icon-view` classes mapped to private-use codepoints
(`modules/formulize/templates/css/formulize.css:13-150`, e.g. `.icon-edit:before { content:
"\f10d" }` at line 95-96, `.icon-magnifier` `\f114` at line 116-117).

Consumers in end-user code:

- `modules/formulize/templates/css/formulize.css:545-565` - `a.loe-edit-entry` and
  `a.de-edit-icon` set `font-family: "formulize-icons"` and emit the pencil glyph via
  `content: var(--formulize-loe-icon, "\f10d")` / `var(--formulize-de-icon, "\f10d")`.
- `modules/formulize/include/entriesdisplay.php:2299` and
  `modules/formulize/class/subformListingsElement.php:1243` emit the `loe-edit-entry`
  anchor (with `&nbsp;` as its only content - the glyph is entirely a CSS concern).
- `modules/formulize/include/functions.php:7995` and `:8020` emit the `de-edit-icon` anchor.
- `modules/formulize/class/subformListingsElement.php:1406` uses `class='icon-help'`.
- `modules/formulize/include/elementdisplay.php:158` injects `<i class="icon-lock">`.
- `modules/formulize/templates/screens/{default,Anari,Lyris}/default/multiPage/toptemplate.php`
  (lines 6, 8, 8 respectively) use `class='icon-arrow-backward …'`.
- Admin: `modules/formulize/admin/home.php:77-86` (`icon-config`, `icon-form-mini`,
  `icon-screen`, `icon-connection`, `icon-menu`, `icon-screen-form`, `icon-delete`);
  `modules/formulize/templates/admin/screen_list_headings.html:115,118,157,160` and
  `templates/admin/element_type_subformListings.html:103,106` (`icon-edit`,
  `icon-magnifier`).

There is already a small, well-reasoned *override* mechanism for this font:
`formulize_printIconStyleOverride()` at `modules/formulize/include/functions.php:12248`
prints a scoped `<style>` block redefining `--formulize-loe-icon` /`--formulize-de-icon` so
one screen or subform can show the magnifier instead of the pencil
(`modules/formulize/include/entriesdisplay.php:1848,1851`;
`modules/formulize/class/subformListingsElement.php:1033`). This is the closest thing in the
codebase to a deliberate icon abstraction, and it is worth preserving the *idea* (named
icon, resolved late, overridable per context) even if the font goes.

### 1.2 Font Awesome 5, loaded from a CDN, by both themes

`themes/Lyris/theme.html:39` and `themes/Anari/theme.html:55` both load
`https://use.fontawesome.com/releases/v5.3.1/js/all.js`.

It is used for **exactly two things**:

- `themes/Anari/theme.html:168,170` - `<i class="fas fa-envelope">` for the inbox link.
- `modules/formulize/include/functions.php:12465` inside `getSortTitleAndIcon()` -
  `fas fa-sort-amount-down` / `fas fa-sort-amount-up`.

That second one is the important case: **core module code emits Font Awesome class names
and depends on a theme having loaded Font Awesome.** Nothing in `modules/formulize`
loads it. A theme that does not include the CDN script gets an invisible sort indicator, and
there is no error or fallback.

Lyris has already quietly opted out. `templates/screens/Lyris/default/listOfEntries/openlisttemplate.php:106`
calls `list($title,) = getSortTitleAndIcon($elementHandle);` - deliberately discarding the
Font Awesome icon - and then builds its own inline SVG sort glyphs at lines 126-128. The
`default` and `Anari` copies of the same template (`:150` in both) still use core's icon.
So the same conceptual icon is produced two different ways depending on theme, from the
same core function.

### 1.3 Inline SVG in Smarty theme templates

- `themes/Lyris/theme.html:87` - hamburger (three `<line>`s), `width/height="16"`,
  `viewBox="0 0 24 24"`, `stroke="currentColor"`, `stroke-width="2"`.
- `themes/Lyris/theme.html:110` - envelope/inbox, same metrics.
- `themes/Lyris/session-timeout-warning.html:8` - close "×" as `M18 6 6 18` + `m6 6 12 12`
  (an inset ×), 16px.
- `themes/Lyris/session-timeout-warning.html:14` - alert triangle, 20px, with a
  `class="session-timeout-warning-icon"`.
- `themes/Anari/theme.html:108` - close "×" as `M4 4L20 20M4 20L20 4` (a corner-to-corner ×,
  different geometry from Lyris' close for the same concept), `class="w-6 h-6"` with no
  `width`/`height` attributes and `stroke-width` set on the `<path>` rather than the `<svg>`.
- `themes/Anari/theme.html:143` - hamburger `M4 6h16M4 12h16M4 18h16` (a different path
  from Lyris' three `<line>`s, for the same icon), again `w-6 h-6`.

Note `w-6 h-6` are Tailwind utility class names. Tailwind is not used in this codebase;
they are inert leftovers from wherever the markup was copied, and the icons are sized only
by the CSS reset's `img, svg { max-width:100% }` plus SVG's default 300×150 fallback, which
is why they render at a different size from Lyris'.

### 1.4 Inline SVG in PHP screen templates (Lyris list-of-entries)

All in `modules/formulize/templates/screens/Lyris/default/listOfEntries/`:

- `toptemplate.php:32` - filter/sliders icon, 14px.
- `variables/currentViewList.php:8` - view-switcher icon, 14px.
- `variables/moreActionsButton.php:4` - "…" ellipsis, 14px.
- `variables/pageNavControls.php:19-20` - `$chevLeft` / `$chevRight`, 16px, assigned to PHP
  string variables.
- `openlisttemplate.php:126-128` - `$iconSort` (12px, **stroke** outline),
  `$iconAsc` / `$iconDesc` (11px, **filled** triangles, `fill='currentColor'`).

The sort trio at 126-128 is the clearest single-file inconsistency in the codebase: three
icons that appear in the same table header, at three different pixel sizes (12/11/11), in
two different styles (outline vs solid).

### 1.5 Inline SVG in JavaScript string literals

`modules/formulize/include/js/drawer.js:116-117` - `ICON_BACK` (chevron-left) and
`ICON_CLOSE` (×), as raw HTML strings injected via `innerHTML`. `ICON_CLOSE` is
`<line x1="18" y1="6" …>` - a *third* × path variant, different again from
`session-timeout-warning.html:8` and `Anari/theme.html:108`.

`ICON_BACK` is a chevron-left with exactly the same geometry as `$chevLeft` in
`pageNavControls.php:19`, written out a second time.

### 1.6 `data:image/svg+xml` URIs in CSS

`themes/Lyris/css/style.css:735` and `:2132` - the select-element chevron, as a
percent-encoded data URI. The same ~200-byte URI is duplicated verbatim in two rules
(`select` and `select.fz-control`).

**These are the only icons in Lyris with a hardcoded colour**: `stroke='%235b6068'`. They
cannot follow `--c-text-muted`, cannot follow a deployment's configured accent colour, and
would be wrong in any future dark palette. Every other Lyris SVG uses `currentColor`.

### 1.7 Unicode characters used as icons

- `themes/Lyris/css/style.css:1465-1474` - `.formulize-de-save::before { content: "\2713" }`
  (✓) and `.formulize-de-cancel::before { content: "\2715" }` (✕), drawn in
  `var(--c-success)` / `var(--c-text-muted)`.
- `themes/Lyris/css/style.css:3945-3956` - the jQuery UI dialog close button gets
  `content: "\00d7"` (×) after its sprite is suppressed.
- `modules/formulize/class/selectElement.php:1419` and
  `modules/formulize/include/js/autocomplete.js:67-72` - the autocomplete chip's remove
  button is literally `&times;`, styled at `modules/formulize/templates/css/formulize.css:718`
  and re-skinned at `themes/Lyris/css/style.css:3430-3451`.

So "close/cancel" alone exists as: an inline SVG × (three path variants), a CSS `\2715`, a
CSS `\00d7`, an HTML `&times;`, a `x.gif`, a `x-wide.gif`, and a jQuery UI `ui-icon-close`.

### 1.8 Legacy bitmap GIF/PNG

39 distinct files under `modules/formulize/images/`, referenced 121 times across
`modules/formulize`. Most are admin-only (`editdelete.gif`, `kedit.png`, `clone.gif`,
`find.png` …), but several are on end-user screens:

- `modules/formulize/include/entriesdisplay.php:3674-3675` - the inline-edit save/cancel
  controls are `<img src=".../images/check.gif">` and `.../images/x-wide.gif`.
- `modules/formulize/class/selectElement.php:1396` - `magnifying_glass.png` inside the
  autocomplete field.
- `modules/formulize/class/googleFilePickerElement.php:355,402` and
  `modules/formulize/templates/calendar_month.html:58` - `x.gif`;
  `calendar_month.html:54` - `plus.PNG`.
- `modules/formulize/templates/css/formulize.css:370-400` - the list-view action buttons
  (`#formulize_deleteButton`, `#formulize_cloneButton`, `#formulize_notifButton`, …) carry
  PNG icons as `background` images, baked into a coloured button.

Bitmaps cannot be recoloured at all. Lyris' response has been to **hide them and redraw**:

- `themes/Lyris/css/style.css:1453-1455` - `.fz-table .formulize-de-controls > a > img
  { display: none; }` then `::before { content: "\2713" }` at 1465.
- `themes/Lyris/css/style.css:2946` - `background-image: none !important` on the multipage
  prev/next buttons to kill core's arrow PNG.
- `themes/Lyris/css/style.css:3947` - the same trick on the jQuery UI close sprite.

That `display:none` + `content:` pattern, repeated three times, is the symptom this issue is
really about.

### 1.9 jQuery UI sprite icons

`modules/formulize/class/userAccount2FAElement.php:289,303` passes
`icon: 'ui-icon-check'` / `'ui-icon-close'` to jQuery UI dialog buttons, which resolve to
background-position offsets in a PNG sprite
(`modules/formulize/libraries/jquery/css/start/jquery-ui-1.8.2.custom.css:113+`).

### 1.10 Summary of the inconsistencies

| Concept | How it is drawn, today |
|---|---|
| Edit / pencil | icon-font `\f10d`; also `kedit.png` in admin |
| Magnifier | icon-font `\f114`; also `magnifying_glass.png`; also `find.png` |
| Close / cancel | 3 different inline-SVG paths, `\2715`, `\00d7`, `&times;`, `x.gif`, `x-wide.gif`, `ui-icon-close` |
| Confirm / save | `\2713` (Lyris CSS), `check.gif` (core markup), `ui-icon-check` |
| Hamburger | 2 different inline-SVG paths (Lyris `<line>`s vs Anari `M4 6h16…`) |
| Chevron left | inline SVG in `pageNavControls.php:19`, same path again in `drawer.js:116` |
| Chevron down | data-URI with hardcoded `#5b6068`, duplicated at `style.css:735` and `:2132` |
| Sort | Font Awesome in default/Anari, inline SVG in Lyris, three sizes/styles within Lyris |

Sizes in use for nominally-equivalent chrome icons: 11, 12, 14, 16, 20 px, plus "unset"
(Anari). Colour handling: `currentColor` (most inline SVG), hardcoded `%235b6068` (data
URIs), hardcoded `#fff` (`formulize.css:560,564`), token vars (`var(--c-success)`), theme
vars (`var(--button-color)` in `themes/Anari/css/style.css:3194-3197`), and un-themeable
(every bitmap).

**This is not a manufactured problem.** Nine mechanisms, three × glyphs, and three
`display:none`-and-redraw overrides is real duplication with real maintenance cost.

---

## 2. Problem statement

1. **Adding an icon has no obvious right answer.** A contributor touching a screen template
   today must choose between an icon font, Font Awesome, hand-written SVG, a data URI, a
   Unicode character, or a GIF - and will reasonably copy whatever is nearest.

2. **Themes cannot restyle what core hardcodes.** Core bakes `#fff` into the pencil
   (`formulize.css:560,564`) and colour into button PNGs (`formulize.css:370-400`). Lyris
   spends real code undoing this (`style.css:1412-1423`, `:1453-1455`, `:2946`, `:3947`),
   and the comment at `style.css:1397-1409` records that a context *without* such a patch
   showed an invisible white pencil - a shipped bug caused directly by the icon model.

3. **Appearance settings do not reach icons.** `themes/Lyris/appearance/appearance.css` is
   generated from the Appearance admin page and redefines `--c-accent` and friends. Inline
   SVGs using `currentColor` pick that up for free (`.fz-sort-ico--active { color:
   var(--c-accent) }`, `style.css:1585-1588`). Data-URI icons and bitmaps categorically
   cannot. A deployment that sets a custom accent colour gets icons that ignore it.

4. **Cross-theme drift.** The same core function (`getSortTitleAndIcon()`) produces a
   Font Awesome icon for two themes and is deliberately ignored by the third. The same
   concept renders differently depending on which template a user lands on.

5. **A CDN dependency for two icons.** Both themes pull Font Awesome 5.3.1 from
   `use.fontawesome.com` on every page. That is a third-party request on every page load,
   a hard dependency for offline/air-gapped/intranet deployments, and an unpinned
   availability risk - for one envelope and one sort arrow.

6. **Third parties have nothing to extend.** There is no documented way for an application
   author or a new theme to say "give me the standard delete icon". The only abstraction
   that exists - `--formulize-loe-icon` - covers two specific anchors.

---

## 3. Constraints the recommendation has to respect

These were checked against the codebase, not assumed:

- **No front-end build pipeline.** The only `package.json` in the repository is
  `tests/e2e/package.json` (Playwright + dotenv). There is no webpack/gulp/rollup config
  and no npm build script. `docs/scss_sass.md` states outright that "Formulize does not
  currently make use of SCSS and Sass. CSS is written directly in the applicable CSS files."
  **Any recommendation requiring an asset-compilation step before CSS/JS can ship is not
  realistic here.** (This also rules out regenerating the icon font casually: doing so needs
  the Ruby `fontcustom` gem plus FontForge, which nothing in the repo installs or documents.)

- **Two template languages.** Theme chrome is Smarty (`themes/*/theme.html`, with ICMS'
  `<{php}>` escape hatch). Screen templates are plain PHP that `print` markup
  (`modules/formulize/templates/screens/…/*.php`). Some component markup is built in
  JavaScript (`drawer.js`, `autocomplete.js`). **Whatever is recommended has to be callable
  from all three.**

- **Smarty plugins are resolved from core ICMS paths only.**
  `libraries/icms/view/Tpl.php:45-57` sets `plugins_dir` to `libraries/smarty/icms_plugins`,
  `libraries/smarty/plugins`, and optionally `class/smarty/plugins` and
  `class/smarty/xoops_plugins`. **A module cannot register its own plugin directory.**
  Since this repository *is* a full ICMS distribution, a plugin file can be shipped into
  `libraries/smarty/icms_plugins/` - but it must be a thin wrapper whose real logic lives in
  the module, so that a Formulize module installed onto a stock ICMS still works via the PHP
  API even if the Smarty tag is unavailable.

- **Embedded (Drupal) mode delivers markup + stylesheets only.**
  `modules/formulize/templates/css/build-drupal-css.php` concatenates `icms.css`,
  `formulize.css` and every `themes/<Theme>/css/*.css`, rewriting each selector to sit inside
  `#formulize_form` (see its header comment, lines 1-33, and
  `formulize_drupalCssSources()` at line 124). In that mode `theme.html` is never rendered.
  **Anything a theme template injects - including an SVG sprite `<defs>` block - does not
  exist on an embedded screen.** This is decisive for the sprite-vs-inline choice below.

- **Appearance/token system to integrate with.** `themes/Lyris/css/tokens.css:38-44` defines
  `--c-accent`, `--c-accent-hover`, `--c-accent-soft`, etc.;
  `themes/Lyris/appearance/appearance.css` overrides them from admin settings via
  `formulize_renderAppearanceHead()` (called at `themes/Lyris/theme.html:31`). An icon
  system must be able to consume those tokens with no special plumbing.

---

## 4. Recommended approach

**Adopt a single named-icon registry in core, emitted as inline `<svg>` by one PHP helper,
with a thin Smarty wrapper and a small JS companion. Colour exclusively via `currentColor`.
Let themes override or add icons by dropping an `.svg` file in a conventional folder.**

### 4.1 The pieces

**(a) An icon set on disk.** `modules/formulize/icons/<name>.svg` - one plain SVG file per
icon, `viewBox="0 0 24 24"`, no `width`/`height`, `stroke="currentColor"` (or
`fill="currentColor"` for solid icons), `stroke-width="2"`, `stroke-linecap/linejoin="round"`.
That is already the house style of every inline SVG in Lyris; this just makes it the rule.
Files are hand-authorable and hand-reviewable; no toolchain.

**(b) One PHP emitter** in `modules/formulize/include/functions.php` (already required by
both themes at `themes/Lyris/theme.html:24` and loaded on every screen):

```php
formulize_icon($name, $options = array());
```

- resolves `$name` through: `themes/<currentTheme>/icons/<name>.svg` →
  `modules/formulize/icons/<name>.svg` → an inline fallback (empty string + a debug notice);
- reads and caches the file contents in a request-level static, so N uses cost one read;
- injects `width`/`height` (default 16, `size` option), a `class` (default `fz-icon`, plus
  anything passed), and accessibility attributes: `aria-hidden="true"` by default, or
  `role="img"` + a `<title>` child when a `label` option is given;
- never touches `stroke`/`fill` - colour is always inherited.

**(c) A Smarty wrapper** at `libraries/smarty/icms_plugins/function.formulize_icon.php`, ~10
lines, that requires `functions.php` and calls `formulize_icon()`. Usable as
`<{formulize_icon name="menu"}>` in any theme template.

**(d) A JS companion.** `formulize_icon()` gains a mode that emits the whole resolved set as
a JSON map, printed once per page, exposed as `formulizeIcon('close')`. `drawer.js:116-117`
and `autocomplete.js:67-72` then stop carrying literal markup.

**(e) Sizing and colour tokens in `formulize.css`:**

```css
.fz-icon { display:inline-block; vertical-align:middle; flex:0 0 auto; }
```

with size variants (`--icon-sm/md/lg`) rather than per-call-site pixel values, which is what
fixes the 11/12/14/16/20 spread.

### 4.2 Why this, and what was rejected

**Rejected: an SVG sprite with `<svg><use href="#icon-name">`.** This is the option the
issue implicitly points at, and it is the wrong fit *here* for two concrete reasons:

1. The `<symbol>` definitions must be present in the same document. The natural place to put
   them is `theme.html` - and `theme.html` is not rendered in embedded/Drupal mode
   (`build-drupal-css.php` header, and `formulize_drupalCssSources()` at line 124, ship only
   stylesheets). Every icon on an embedded screen would silently render as nothing. Working
   around that means injecting the sprite from PHP at first use, at which point the sprite is
   doing strictly more work than plain inline emission for no benefit at Formulize's icon
   volume (a busy list screen uses roughly a dozen icons).
2. Sprite generation normally implies a build step to concatenate the `<symbol>`s. There is
   no build pipeline (§3), and adding one for icons alone is disproportionate.

The duplication cost that sprites exist to solve - the same path data repeated per instance
in the HTML - is real but small at this scale (a few hundred bytes per page, gzipped to
near nothing since the repeats are identical), and it is entirely a wire-size concern, not a
maintenance one: with a single emitter the path data still lives in exactly one file.

**Rejected: extending the `formulize-icons` webfont.** It is the incumbent, it already
inherits `currentColor`, and it has a working per-context override hook
(`formulize_printIconStyleOverride()`), so it deserves a fair hearing. Against it: adding a
glyph requires the Ruby `fontcustom` gem plus FontForge, neither installed nor documented
here, so in practice nobody adds icons to it - the set has been frozen at 33 glyphs while
every new Lyris icon arrived as inline SVG. It also forces icons into a single flat colour,
renders at font metrics rather than a box (hence the `font-size: 1rem` + `padding: 0.1em`
fudging at `formulize.css:554-557`), is invisible until the font loads, and is announced by
some screen readers as a private-use character. The recommendation is to **freeze** it -
keep it loaded so existing `.icon-*` call sites keep working, stop adding to it, and migrate
call sites opportunistically.

**Rejected as the primary mechanism, recommended as a secondary one: CSS `mask-image`.**
`mask-image` + `background-color: currentColor` gives genuinely themeable icons from pure
CSS, and is the correct fix for icons that are *decoration on another element* rather than
content - specifically the select chevron at `themes/Lyris/css/style.css:735` and `:2132`,
which should become a mask coloured `var(--c-text-muted)` instead of a data URI with
`stroke='%235b6068'` baked in. It is wrong as the *general* mechanism because an icon can
then only appear where someone has also written a CSS rule for it: you cannot ask for an
icon by name from PHP or Smarty. That coupling - markup in one file, icon identity in
another - is precisely what produced the current `display:none`-and-redraw overrides.

**Rejected: keeping Font Awesome.** Two icons do not justify a per-page third-party CDN
request, and `getSortTitleAndIcon()` (`functions.php:12465`) emitting classes that only
exist if a theme happened to load a CDN script is a latent breakage for any new theme.
Remove the `<script>` from both themes, move `fa-envelope` and the sort arrows into the core
set.

### 4.3 Theming and appearance integration

Because every emitted icon uses `currentColor` and no `width`/`height` in the source file:

- **Colour** is whatever `color` computes to at the call site. `color: var(--c-accent)` -
  which `appearance.css` rewrites from the Appearance admin page - therefore works with zero
  icon-specific plumbing. `themes/Lyris/css/style.css:1585-1588` already demonstrates the
  pattern with `.fz-sort-ico--active`.
- **Per-theme restyling** needs no override of the markup: a theme writes
  `.fz-btn--icon .fz-icon { color: var(--c-text-muted); }` and it applies everywhere.
- **Per-theme artwork** is a file drop: `themes/Anari/icons/menu.svg` replaces the core
  `menu` icon for Anari only, because of the resolution order in §4.1(b). Anari's
  corner-to-corner × and Lyris' inset × can legitimately coexist that way instead of by
  accident.
- **Extending the set** is the same file drop: `themes/MyTheme/icons/widget.svg` makes
  `{formulize_icon name="widget"}` work immediately. No registry edit, no build, no fork of
  the core set.

---

## 5. Migration path (sketch, non-binding)

Deliberately incremental. Nothing below has to happen in one PR, and the existing icon font
keeps working throughout.

**Phase 0 - land the mechanism.** Add `modules/formulize/icons/`, `formulize_icon()`, the
Smarty wrapper, the `.fz-icon` CSS, and documentation. No call sites change. Seed the set
from icons that already exist as inline SVG, since those are already in the target style:
`menu`, `mail`, `x`, `chevron-left`, `chevron-right`, `chevron-down`, `alert-triangle`,
`filter`, `view-switch`, `more-horizontal`, `sort`, `sort-asc`, `sort-desc`. Add the
obvious missing ones: `pencil`, `search`, `check`, `plus`, `trash`, `copy`, `download`,
`upload`, `printer`, `help`, `lock`. **Roughly 24 icons**, most of which can be transcribed
from existing markup rather than drawn.

**Phase 1 - de-duplicate the Lyris inline SVGs (pure swap, no visual change intended).**
`themes/Lyris/theme.html:87,110`; `themes/Lyris/session-timeout-warning.html:8,14`;
`templates/screens/Lyris/default/listOfEntries/toptemplate.php:32`,
`variables/currentViewList.php:8`, `variables/moreActionsButton.php:4`,
`variables/pageNavControls.php:19-20`, `openlisttemplate.php:126-128`;
`modules/formulize/include/js/drawer.js:116-117`. **10 files, ~16 call sites.** This alone
collapses the three × variants and the duplicated chevron-left, and normalises the
11/12/14/16px spread onto the size tokens. Small and low-risk; a good first implementation PR.

**Phase 2 - fix the un-themeable icons.** Convert the two select chevrons
(`themes/Lyris/css/style.css:735`, `:2132`) to `mask-image` + `background-color:
var(--c-text-muted)`, deduplicated into one rule. **1 file, 2 rules.** This is the change
that makes the last hardcoded colour in Lyris respond to the theme.

**Phase 3 - retire Font Awesome.** Move the sort arrows in `getSortTitleAndIcon()`
(`modules/formulize/include/functions.php:12465`) and Anari's envelope
(`themes/Anari/theme.html:168,170`) onto `formulize_icon()`, then drop the CDN `<script>`
from `themes/Lyris/theme.html:39` and `themes/Anari/theme.html:55`. Once core no longer
emits Font Awesome classes, Lyris' `list($title,)` workaround at
`openlisttemplate.php:106` can become a normal call. **4 files.** Needs a grep across any
downstream custom application code for `fa-` classes before removing the script.

**Phase 4 - end-user bitmaps.** Replace `check.gif` / `x-wide.gif` at
`modules/formulize/include/entriesdisplay.php:3674-3675` with `formulize_icon('check')` /
`formulize_icon('x')`, which then lets `themes/Lyris/css/style.css:1453-1474` delete its
`display:none` + `\2713` / `\2715` workaround entirely. Same for
`magnifying_glass.png` (`class/selectElement.php:1396`), the autocomplete chip's `&times;`
(`class/selectElement.php:1419`, `include/js/autocomplete.js:67-72`), `x.gif` in
`class/googleFilePickerElement.php:355,402` and `templates/calendar_month.html:58`, and the
button background PNGs at `modules/formulize/templates/css/formulize.css:370-400`.
**~7 files.** Higher risk than the earlier phases because it changes core markup that other
themes style, so it should be its own issue.

**Explicitly out of scope:** the ~100 bitmap references under
`modules/formulize/templates/admin/` and `modules/formulize/admin/`. They are admin-only,
they are consistent with each other, and they are better handled whenever the admin UI is
redesigned rather than as part of this work.

**Rough overall size:** phases 0-3 are the substance and are modest - one new helper, ~24
small SVG files, and mechanical edits across ~15 files. Phase 4 is comparable in size but
carries the compatibility risk.

---

## 6. How third parties would use it

**In a theme template (Smarty):**

```smarty
<button class="fz-btn fz-btn--icon" aria-label="Close">
  <{formulize_icon name="x"}>
</button>

<{formulize_icon name="alert-triangle" size=20 class="my-warning-ico"}>
<{formulize_icon name="trash" label="Delete this entry"}>
```

**In a screen template or custom application code (PHP):**

```php
print "<a class='fz-btn fz-btn--icon' href='$url'>" .
      formulize_icon('pencil', array('size' => 14)) . "</a>";
```

The same call works from anything in `modules/formulize/code/` - custom element code,
`on_before_save` handlers rendering markup, custom screen templates.

**In JavaScript:**

```js
btn.innerHTML = formulizeIcon('chevron-left');
```

**Colouring an icon** - no icon-specific API; it follows text colour:

```css
.my-component__ico { color: var(--c-accent); }      /* follows Appearance settings */
.my-component:hover .my-component__ico { color: var(--c-danger); }
```

**Adding your own icon** - drop a file, use it:

```
themes/MyTheme/icons/rocket.svg     →   <{formulize_icon name="rocket"}>
```

**Overriding a core icon for your theme only** - drop a file with the core icon's name:

```
themes/MyTheme/icons/pencil.svg     →   replaces the core pencil, in MyTheme only
```

---

## 7. Open questions for maintainers

1. Should the icon set live at `modules/formulize/icons/` or under
   `modules/formulize/templates/` alongside the existing icon-font assets? The former reads
   better; the latter keeps all icon assets together.
2. Is removing the Font Awesome CDN script acceptable, or are there downstream deployments
   with custom code relying on `fa-*` classes being available?
3. Is `formulize_icon()` the right name, given `formulize_printIconStyleOverride()` already
   exists and would eventually be superseded by it?
4. Should Phase 4 (core markup changes affecting all themes, not just Lyris) happen at all
   before the admin UI work, or be deferred?

---

*This document is a recommendation only. No functional, CSS, JavaScript, PHP, or template
changes accompany it; the current icon behaviour is unmodified. Implementation should follow
a separate issue once maintainers have decided whether and how to proceed.*
