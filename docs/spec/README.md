# Vendored GEDCOM specifications

This directory contains pinned, offline copies of the authoritative GEDCOM
specifications used as the normative reference for this parser. They are the
source of truth for:

- the conformance rules enforced during development (and by the
  `gedcom-parser-reviewer`),
- the declarative per-version schema of the parser (structures, cardinalities,
  enumerations, calendars, data types), and
- the conformance test corpus.

**This directory is excluded from the Composer/`git archive` distribution**
(`docs/spec/** export-ignore` in `.gitattributes`); it ships in the repository
only, not in the installed package.

## Contents

| Path | Source | Notes |
|------|--------|-------|
| `ged551.pdf` | <https://gedcom.io/specifications/ged551.pdf> | The GEDCOM 5.5.1 standard (15 Nov 2019). |
| `ged551-errata.html` | <https://gedcom.io/gedcom551-errata/> | Published 5.5.1 errata list. |
| `FamilySearchGEDCOMv7.pdf` | <https://gedcom.io/specifications/FamilySearchGEDCOMv7.html> | The FamilySearch GEDCOM 7.0 specification. |
| `gedcom7-registries/` | <https://github.com/FamilySearch/GEDCOM-registries> | Machine-readable YAML registry (structures, enumerations, calendars, months, data types, URIs, manifest). |

## Pinned snapshot provenance

- **GEDCOM 7.0 registry** snapshot taken from `FamilySearch/GEDCOM-registries`
  at commit `99a12690052468e3ff192a0c0ef39998bd12f944`
  (committed 2026-07-11), retrieved 2026-07-13.
  Only the normative directories are vendored: `structure/`, `enumeration/`,
  `enumeration-set/`, `calendar/`, `month/`, `data-type/`, `uri/`, `manifest/`
  (plus the upstream `README.upstream.md` and `tips.md`). The upstream
  `registry_tools/`, `generated_files/` and CI config are intentionally omitted.

To refresh the registry snapshot, re-download the tarball at a new pinned
commit and update the SHA + date above:

```shell
gh api repos/FamilySearch/GEDCOM-registries/tarball/<sha> > registries.tar.gz
```

## Licensing

- **GEDCOM 5.5.1** (`ged551.pdf`): © The Church of Jesus Christ of Latter-day
  Saints. Per the notice on its title page, the document *"may be copied for
  purposes of review or programming of genealogical software, provided this
  notice is included."* The notice is retained inside the PDF.
- **GEDCOM 7.0** specification and the machine-readable registry: published by
  FamilySearch. The specification source repository
  (`FamilySearch/GEDCOM`) is licensed **Apache-2.0**; the registry repository
  does not carry an auto-detected SPDX license file — consult the upstream
  repository for the exact terms before redistributing beyond this repository.
  The pinned source and commit are recorded above.

These files are included for reference only and are not part of the licensed
source code of this package (see the repository `LICENSE`).
