# AGENTS.md — InlineSVG (MediaWiki extension)

Guidance for humans and agents working in this repository. Findings from
static reviews are kept separately in [REVIEW.md](REVIEW.md); read its
"Known issues" before changing the code touched there.

## What this repository is

A MediaWiki extension (minimum MediaWiki 1.35) that registers an `‎<svg>` parser tag and
returns the tag contents sanitized with
[enshrined/svg-sanitize](https://github.com/darylldoyle/svg-sanitizer).

- `extension.json` — manifest v2: namespace autoload for `MediaWiki\Extension\InlineSVG\` → `src/`,
  `ParserFirstCallInit` hook → `src/Hooks.php`, config `$wgInlineSVG`, `load_composer_autoloader`.
- `src/InlineSVG.php` — `renderSVG()`: strips an XML declaration, wraps bare fragments in an
  `<svg>` tag built from the parser-tag attributes, sanitizes, returns the parse fragment or an
  error `<span class="error">`.
- `src/Hooks.php` — registers the `‎<svg>` tag.
- `tests/phpunit/unit/` — PHPUnit unit tests (`MediaWikiUnitTestCase`).
- `i18n/` — messages (`inlinesvg-desc`, `inlinesvg-error`), checked by grunt-banana-checker.
- No JavaScript or CSS/LESS sources exist; the JS/LESS linters run against an empty set today.

## Environment

- PHP ≥ 7.3.19 for the extension itself; **PHP ≥ 8.1 for the dev toolchain** (`composer test`):
  codesniffer 48 and phan-config 0.20 (phan 6) require it (REVIEW.md D11/D13). Verified on
  PHP 8.5.12 / Composer 2.10.3 / Node 24.
- Composer, Node.js and npm, for the dev tooling below.
- Composer plugins `composer/installers` and `dealerdirect/phpcodesniffer-composer-installer`
  are allow-listed in `composer.json` `config.allow-plugins` — keep that when touching the
  manifest (Composer 2.2+ otherwise refuses to run the install).
- Running the PHP unit tests (`composer cover`) additionally requires a MediaWiki 1.35 checkout
  that this directory sits in as `extensions/InlineSVG` (or `MW_INSTALL_PATH` pointing at one),
  because MediaWiki core classes and the test entry point live there.

## Installing dependencies — single funnel rule

**All dependency installs go through the two manifests and nothing else:**

- PHP tools and the sanitizer: `composer install` (from `composer.json`).
- JS/LESS linters: `npm install` (from `package.json`).

Never add ad-hoc install commands (curl | sh, pip, manual phar drops, `composer require`/`npm
install` inside scripts) around build or test steps. If a new tool is needed, add it to
`composer.json` (`require-dev`, exact version pins, matching the existing pins) or
`package.json` (`devDependencies`, exact versions for the wikimedia lint configs) and install via
the manifest. `composer.lock` is committed (REVIEW.md D2): after changing `composer.json`, run
`composer update` and commit the updated lock; day-to-day installs use `composer install`, which
reproduces the locked versions exactly.

**Version validation rule:** when a setup or bootstrap script installs any tool, runtime, or
dependency whose version matters, it must verify the installed version against the expected
version afterwards and fail fast with a clear error on mismatch (e.g.
`composer --version`/`node --version` checks in a bootstrap script, or
`php -r 'exit(version_compare(PHP_VERSION, "7.3.19", ">=") ? 0 : 1);'` before proceeding).
Do not continue a setup after installing the wrong version.

**Shell script rule:** any shell script added to this repository must be strictly POSIX sh —
shebang `#!/bin/sh`, `[ ]` tests (never `[[ ]]`), quoted variable expansions (`"$var"`), and
`set -eux` for setup scripts. Verify every shell script with `shellcheck` and `dash -n`
where those tools are available. No bashisms (`[[`, `==`, arrays, `local` in functions is fine
only because dash supports it — prefer avoiding it; `source` → use `.`).

## Running tests, linters, and fixes

PHP (all paths relative to this repository root):

| Command | What it runs |
| --- | --- |
| `composer test` | parallel-lint (syntax), phpcs, minus-x check, phan |
| `composer phpcs` | PHP codesniffer (MediaWiki standard, `.phpcs.xml`), cached |
| `composer phan` | static analysis (`.phan/config.php`), `--allow-polyfill-parser` |
| `composer fix` | minus-x fix + phpcbf (auto-fix PHP style) |
| `composer cover` | runs `tests/phpunit` with MediaWiki's phpunit entry point — needs a MediaWiki checkout next to this directory or `MW_INSTALL_PATH` set |
| `php -d zend_extension=xdebug.so vendor/bin/phpunit …` | not used here; unit tests run through `composer cover` |

Notes:

- `composer cover` runs the whole `tests/phpunit` tree; currently that is only unit tests.
- Known issue: the test namespaces are not listed under `TestAutoloadNamespaces` in
  `extension.json` (see REVIEW.md), so the tests rely on the test runner's own autoloading.
- phan's default config expects MediaWiki core at `../..`; set `MW_INSTALL_PATH` (and optionally
  `MW_VENDOR_PATH`) when analyzing outside a full checkout.

JavaScript / LESS / i18n:

| Command | What it runs |
| --- | --- |
| `npm test` | `grunt test`: eslint (`wikimedia/client-es5`, es5 — no modern JS syntax), stylelint (`stylelint-config-wikimedia`), banana-checker on `i18n/` |
| `grunt --fix`-style eslint fix | `npx grunt eslint --fix` if ever needed |

There is no build step: the extension ships source files only. "Build" in the usual sense does
not exist here; treat `composer test` + `npm test` as the complete verification gate. Run both
before declaring work done.

## Coding conventions

- **PHP:** MediaWiki core conventions, enforced by `mediawiki-codesniffer` 48.0.2 via
  `.phpcs.xml` (bumped from 39.0.0 per REVIEW.md D11; phpcs fixes run through `composer fix`).
  Practical rules you will notice in the source: tabs for indentation, spaces
  inside parentheses and braces in control structures and calls, `array( )` literal syntax,
  Yoda conditions where phpcs asks for them, Doxygen comment blocks (`/** */` with `@param`,
  `@return`, `@var`). `.phpcs.xml` excludes
  `MediaWiki.Commenting.RedundantVarName.Found` and
  `MediaWiki.WhiteSpace.SpaceBeforeSingleLineComment.NewLineComment` — keep those exclusions
  when touching the ruleset.
- **Namespaces/autoloading:** classes live in `MediaWiki\Extension\InlineSVG` and are autoloaded
  from `src/` via `extension.json` `AutoloadNamespaces`. Do not add `require`/`include` for
  first-party classes; add the file under `src/` instead. Tests currently use
  `MediaWiki\Extension\InlineSVG` and `MediaWiki\Extension\InlineSVG\Tests` (inconsistent —
  see REVIEW.md).
- **PHP version floor:** the extension ships to MediaWiki 1.35, so write code that runs on PHP
  7.3.19. Not available at that floor: arrow functions (7.4), typed properties (7.4), `match`
  (8.0), constructor property promotion (8.0), enums (8.1), named arguments (8.0). Fine: null
  coalescing `??` (7.0), scalar/return type declarations (7.0), `list()` destructuring (7.1),
  nullable types (7.1). The dev tooling itself runs on modern PHP (8.1+) — that does not raise
  the floor of shipped code.
- **JavaScript:** none exists; if any is added it must be ES5 client code per
  `wikimedia/client-es5`, with MediaWiki globals allowed per `wikimedia/mediawiki`.
- **i18n:** every user-facing string is a message in `i18n/` (en + qqq at minimum); qqq entries
  must keep `{{desc|…}}` documentation valid for banana-checker.
- **Licensing:** every PHP file carries the GPL-2.0-or-later header block; keep it on new files.

## Known issues

See [REVIEW.md](REVIEW.md) for the full record. Applied 2026-11-07: D1 (PHP floor 7.3.19 in
both manifests), D2 (sanitizer pinned to `^1.1.1`; `composer.lock` un-ignored, to be committed
once generated by `composer update`), D3 (attribute escaping in `renderSVG`), D4 (sanitizer
issues rendered as escaped messages), D5 (unit test rewritten; `removeXMLTag( true )`),
S1 (`InlineSVG::class` callback), S2 (inline `createMock()`), S5 (`phpcs -p -s --cache` +
ignored cache), D11 (codesniffer 48.0.2 — vulnerable phpcs 3.6.2 out), D12 (phpcs ruleset
extension filter + tool-dir excludes), D13 (phan stack 0.20.0/6.0.2 + minus-x 2.0.1; phan
config scans own vendor), D14 (`package.json` name → `inlinesvg`), D15 (generic
XML-declaration strip on input; Inkscape's `standalone="no"` header used to break the render).
Realized by the D5 rewrite: S3, S4. Declined: S9. Open: D6–D10 (descriptive findings), S8
(qqq typo, trivial). Both gates verified green (composer test / npm test exit 0 on PHP
8.5.12/Node 24, last 2026-11-10); composer.lock committed; cover still needs MW checkout
(D10). Check REVIEW.md's status markers before assuming what the code does.
