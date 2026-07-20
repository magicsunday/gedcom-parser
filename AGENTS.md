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

* Target PHP: `^8.3` (see `composer.json` `require.php`). Write code for the 8.3 floor —
  typed class constants, `readonly`, enums, `match`, first-class callables are all
  available; Rector's `UP_TO_PHP_83` set enforces the level. The `<8.4.0` upper bound is
  gone — the library runs on 8.3/8.4/8.5.
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

* `composer ci:test` — full local gate (lint + unit + phpstan + rector + cgl + cpd)
* `composer ci:test:php:lint` — `phplint`
* `composer ci:test:php:unit` — PHPUnit
* `composer ci:test:php:phpstan` — PHPStan (`level: max`, no baseline — a hard gate; also runs
  the `phpat` architecture rules in `tests/Architecture/ArchitectureTest.php`)
* `composer ci:test:php:rector` — Rector dry-run
* `composer ci:test:php:cgl` — php-cs-fixer dry-run
* `composer ci:test:php:cpd` — `jscpd` copy/paste detection
* `composer ci:test:php:mutation` — `infection` mutation testing (**local-only**, not in `ci.yml`;
  `minCoveredMsi: 70`, config in `infection.json5`)
* `composer ci:cgl` — php-cs-fixer (apply)
* `composer ci:rector` — Rector (apply)

GitHub Actions (`.github/workflows/ci.yml`) runs these granular steps on PHP 8.3 / 8.4 /
8.5. Every step is a **hard gate** — the typed-model refactor (GH-20) removed the untyped
`DataObject` bag (so PHPStan is clean at `level: max`) and its per-structure boilerplate
(so `cpd` finds no duplicates), and the former `continue-on-error` on `phpstan` / `cpd`
is gone.

**Git flow (the shared magicsunday convention — identical across the sibling
repositories, so a difference here is a defect rather than a local rule):**

* **Branch naming: exactly `GH-<N>`** (bare issue number, no descriptive suffix).
* **Commit subject:** a subject starting with `GH-` must match `^GH-\d+: [A-ZÄÖÜ]`;
  every other subject must match `^[A-ZÄÖÜ]` — a capitalised imperative either way
  (`GH-26: Tokenise two-digit levels`, or `Bump the composer group` for work that
  belongs to no issue). The patterns check only the leading capital; two starts are
  banned whatever their case: **Conventional-Commit prefixes** (`feat:`, `Fix:`,
  `chore:`…) and path-like starts (`src/Reader.php: …`, `Src/Reader.php: …`).
    * The two patterns are deliberately kept separate: `^(GH-\d+: )?[A-ZÄÖÜ]` (wrong)
      stops enforcing the capital *after* the prefix, because the optional group can be
      skipped and the `G` of `GH-` then satisfies `[A-ZÄÖÜ]` on its own —
      `GH-12: fix typo` would pass. Keying on the subject rather than on the branch
      also keeps this check decidable for commits already on `main`, where the issue
      branch no longer exists.
    * The same two patterns apply to the **pull-request title**, which under
      squash-merge is the subject that reaches `main`.
    * The normative definition lives in
      `magicsunday/.github/.github/workflows/commit-convention.yml@main`, which
      self-tests a decision table before applying it. No workflow here calls that gate,
      so the rule in this repository is documentation only; wherever it is wired, the
      workflow is authoritative and this text is what gets fixed.
* The `GH-<N>: ` prefix marks work that belongs to the issue — a commit on that branch
  whose concern is something else (a drive-by lint fix, a dependency bump) keeps its own
  unprefixed subject. Merge and revert commits keep the subject git generates. Not every
  git-written subject is exempt, though: `fixup!` and `squash!` start lowercase and
  violate the rule, so autosquash them before opening the PR.
* The PR body closes the issue with a `Closes #<N>` keyword. The `GH-<N>: ` subject
  prefix is not a GitHub link and closes nothing.
* Commits and all dev-facing GitHub text are **English**.
* **Never** add a `Co-Authored-By:` trailer.
* Granular, logical commits — one concern each; style/CGL fixes separate from features.
* `ci:test` green **before every commit**. Commit only verified-working code.
* Remotes are SSH only (`git@github.com:…`).

---

## 3) Definition of Done (per issue)

* ✅ TDD: a failing test was written first (RED), then the minimal fix (GREEN).
* ✅ PHPUnit green — positive **and** negative/edge paths; **zero** risky/notice/deprecation.
* ✅ New behaviour covered by fixture-driven tests where applicable (the `tests/files/*.ged`
  corpus — `allged.ged`, `ansel.ged`, the `LTER*` line-ending set).
* ✅ PHPStan runs clean at `level: max` (no baseline, no `@phpstan-ignore`); Rector +
  php-cs-fixer clean; `jscpd` finds no duplicates.
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
    Parser.php                                  # public entry point → typed GedcomDocument
    Parse/**                                    # generic node-tree reader
    Schema/**                                   # registry-compiled per-version schema
    Mapping/**                                  # schema-driven mapper + typed parser/reader
    Model/**                               # immutable typed records
    ValueObject/**                              # parsed value objects (date/place/age/name/…)
    Encoding/**                                 # ANSEL decoder
    Exception/**                                # domain exceptions (ExceptionInterface)
tests/
    *Test.php, files/*.ged                      # PHPUnit + the .ged fixture corpus
docs/spec/                                      # vendored GEDCOM specs (export-ignored)
```

> The current per-structure `Parser`/`Model`/`Interfaces`/`Traits` layout and the
> untyped `DataObject` result are being refactored toward a grammar-derived tokeniser +
> declarative per-version schema + typed value objects (see the tracking issue). New work
> should move **toward** typed value objects, not add to the untyped bag.

---

**Owner:** *MagicSunday* (Europe/Berlin) · **License:** MIT
