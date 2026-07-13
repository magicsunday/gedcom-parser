# AGENTS.md — magicsunday/gedcom-parser

Guide for LLM-based assistants (Claude, Codex, Copilot, …) working in this repository.
**Goal:** reproducible, safe, lean changes with tests, static analysis, and clear guardrails.
**Workflow:** always **branch → atomic commits → merge/PR**; never hand-applied unified-diff patches.

---

## 1) Project scope & principles

**Objective:** a spec-conformant, streaming **GEDCOM** file parser. Today it targets
**GEDCOM 5.5.1**; **GEDCOM 7.0** support is planned (see the tracking issue). The
overriding goal is **100 % conformance to the GEDCOM grammar** — the authoritative
specs are vendored under [`docs/spec/`](docs/spec/) (5.5.1 PDF + errata, 7.0 PDF, and
the machine-readable 7.0 YAML registry).

* Target PHP: see `composer.json` (`require.php`). The codebase is being modernised to
  `^8.3`; until the floor is bumped, write code at the **current floor** — Rector
  upgrades it later.
* `declare(strict_types=1);` in every file; PSR-12; Rector- and php-cs-fixer-clean.
* No `mixed`, no `empty()`, no nested ternaries. Prefer `final` classes and value objects.
* One class per file; the test namespace mirrors the source tree (`MagicSunday\Gedcom\Test\…`).
* PHPDoc + inline comments in **English**. Every method and constant gets a real docblock.
  Note: this repo's php-cs-fixer (`@Symfony` → `phpdoc_annotation_without_dot`) **lowercases**
  the first word of `@param`/`@return`/`@throws`/`@var` descriptions and drops the trailing
  dot — follow that (run `composer ci:cgl`); do not "correct" them to capitalised.
* The parser is in-memory and read-only over a stream; do **not** add network/DB I/O.

**Parser guardrails (conformance-critical)**

* The line tokeniser (`src/Reader.php`) must follow the GEDCOM grammar exactly:
  `level` is `[1-9]?\d` (0–99, no leading zero); a `tag` is `[A-Za-z0-9_]+`; a value is
  **either** a whole `@xref@` pointer (first char alphanum) **or** text — a `@#…@`
  escape is never a pointer; `@@` decodes to a literal `@`.
* Honour the encoding declared in `HEAD.CHAR` (ANSEL / UNICODE / ASCII / UTF-8); strip a
  UTF-8 BOM once at stream start via a prefix check, never `trim()`.
* Parse defensively: a malformed line is rejected with a **specific exception**, the rest
  of a tolerable document keeps parsing where the grammar allows.
* Keep it **streaming**: read line-by-line, emit one level-0 record subtree at a time;
  never slurp the whole file into one tree.
* When in doubt about a structure's allowed sub-tags, cardinality, an enumeration, or a
  size limit, **consult `docs/spec/`** — never invent grammar from memory.

**Error handling**

* Throw **project-specific exceptions** from `MagicSunday\Gedcom\Exception\*`, never bare
  `\RuntimeException` / `\InvalidArgumentException`. Every library exception implements
  `ExceptionInterface` so consumers can `catch (ExceptionInterface $e)`. Carry structured
  context (offending line, line number, …); avoid method names that clash with
  `\Exception` built-ins (`getLine()`/`getCode()` are taken → use `getRawLine()`,
  `getLineNumber()`, …).
* Never `var_dump()` / `print_r()` / `@`-silence / `trigger_error()`.

---

## 2) Tooling & commands

**Runtime:** all PHP/Composer/PHPUnit runs go through the **buildbox container** — never
run PHP on the host:

```shell
docker run --rm -v "$PWD:/app" -w /app --entrypoint php \
    ghcr.io/magicsunday/webtrees-buildbox:8.3 .build/bin/phpunit
```

**Composer scripts** (bin-dir `.build/bin`, vendor-dir `.build/vendor`):

* `composer ci:test` — full local gate (lint + phpstan + rector + phpunit)
* `composer ci:test:php:lint` — `phplint`
* `composer ci:test:php:phpstan` — PHPStan (target `level: max`, no baseline, no ignores)
* `composer ci:test:php:rector` — Rector dry-run
* `composer ci:test:php:unit` — PHPUnit
* `composer ci:cgl` — php-cs-fixer

**Git flow (house rules — override any sibling AGENTS.md that says otherwise):**

* **Branch naming: exactly `GH-<N>`** (bare issue number, no descriptive suffix).
* **Commit subject: `GH-<N>: <Capital-verb imperative>`** (e.g. `GH-26: Tokenise
  two-digit levels`). **No** Conventional-Commit prefixes (`feat:`/`fix:`/`chore:`…),
  no lowercase starts. Commits and all dev-facing GitHub text are **English**.
* **Never** add a `Co-Authored-By:` trailer.
* Granular, logical commits — one concern each; style/CGL fixes separate from features.
* `ci:test` green **before every commit**. Commit only verified-working code.
* Remotes are SSH only (`git@github.com:…`).

---

## 3) Definition of Done (per issue)

* ✅ TDD: a failing test was written first (RED), then the minimal fix (GREEN).
* ✅ PHPUnit green — positive **and** negative/edge paths; **zero** risky/notice/deprecation.
* ✅ New behaviour covered by fixture-driven tests where applicable (the `test/files/*.ged`
  corpus — `allged.ged`, `ansel.ged`, the `LTER*` line-ending set).
* ✅ PHPStan clean (target `level: max`, no baseline, no `@phpstan-ignore`); Rector +
  php-cs-fixer clean.
* ✅ **Full reviewer audit loop** run and converged (see §4).
* ✅ Conformance claims verified against `docs/spec/`.
* ✅ `README.md` / docs updated when behaviour or the public API changes.
* ✅ Tracking issue updated; the issue is linked/closed from the commit (`GH-<N>: …`).

---

## 4) Review audit loop (mandatory)

Every change runs the **full relevant reviewer set** and iterates fix → re-audit until
two consecutive clean rounds. For a parser change that is at least:

* **`gedcom-parser-reviewer`** — GEDCOM 5.5.1/7.0 conformance (rules G1–G13). It is a
  curated subset, not a full-spec oracle; it consults `docs/spec/` for anything else.
* **`php-reviewer`** — universal PHP house style.
* **`test-quality-reviewer`** — test-quality rules.
* plus general correctness / simplification reviewers.

Compose, never hand-pick a subset. See the repository's audit tooling / the user's
`audit-all-reviewers` skill.

---

## 5) Layout

```
src/
    Reader.php, Stream.php, StreamFactory.php   # line tokeniser + PSR-7 stream
    AbstractParser.php, Parser.php              # recursive-descent driver
    Parser/**                                   # per-structure parsers
    Model/**                                    # parsed data objects
    Interfaces/**, Traits/**                    # tag constants + accessors
    Exception/**                                # domain exceptions (ExceptionInterface)
test/
    *Test.php, files/*.ged                      # PHPUnit + the .ged fixture corpus
docs/spec/                                      # vendored GEDCOM specs (export-ignored)
```

> The current per-structure `Parser`/`Model`/`Interfaces`/`Traits` layout and the
> untyped `DataObject` result are being refactored toward a grammar-derived tokeniser +
> declarative per-version schema + typed value objects (see the tracking issue). New work
> should move **toward** typed value objects, not add to the untyped bag.

---

**Owner:** *MagicSunday* (Europe/Berlin) · **License:** MIT
