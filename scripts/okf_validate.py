#!/usr/bin/env python3
"""Validate an Open Knowledge Format (OKF v0.1) documentation bundle.

This docstring is the canonical enumeration of what the validator reports. It is
what the script prints when run with no arguments, so callers and skill files
should link here rather than restate the list -- four separate restatements in
``okf-bundle`` had already drifted before this note existed.

Errors (exit 1):

- a concept file with a missing or malformed YAML frontmatter block
- a concept whose frontmatter has no ``type``, or a ``type`` outside the registry
- a missing reserved ``index.md``, or one that does not declare ``okf_version``

Warnings (exit 0 -- non-blocking per the OKF spec, but the bar for committing is
zero errors *and* zero warnings):

- ``index.md`` declaring an ``okf_version`` other than the pinned one
- a cross-link whose target does not exist, or that escapes the repository root
- a concept whose frontmatter is missing ``title`` or ``description``
- a concept whose frontmatter is missing ``timestamp``, or whose ``timestamp`` is
  not an ISO-8601 UTC instant
- a concept whose frontmatter ``timestamp`` is older than the newest dated line in
  that same file's ``# Citations`` section

Usage:
    python3 scripts/okf_validate.py docs/knowledge
    python3 scripts/okf_validate.py docs/knowledge --repo-root .

``<repo>/scripts/`` is the conventional place for this script, but not a
requirement. Repository-root detection walks upward from the bundle path given
on the command line, never from this script's own location, so the script
behaves identically wherever it is placed — including inside a project-local
skill's ``assets/`` folder when a target project checks the skill in rather
than relying on a personal skill collection.

When no enclosing ``.git`` is found the repository boundary is **unknown**, and
this script says so rather than guessing one. Links are still checked for
existence, but the containment check is skipped -- not narrowed to the bundle.
Narrowing it reinterprets "escapes the repository" as "escapes the bundle" and
flags every legitimate out-of-bundle in-repo link, which is a false positive on
every ``git archive`` export, ``.git``-less Docker build context, tarball
release and vendored bundle. Pass ``--repo-root PATH`` to state the boundary
explicitly and get containment back.

The allowed ``type`` values and the pinned OKF version are read from
``agents/.agent-config.json`` (``documentation.technicalBundle``) when available,
then ``agents/templates/agent-config.example.json``, with sensible defaults otherwise.
A candidate that is unreadable, unparseable, or that carries no usable
``typeRegistry`` is skipped for the next candidate, and every skip is printed as
a ``note:`` line -- an incomplete per-machine config must not silently shadow the
committed template, and a malformed one must not become the registry.

Exit code 0 = bundle conforms (warnings allowed); 1 = one or more errors;
2 = no bundle path was given, or the arguments were not understood.
"""
from __future__ import annotations

import json
import re
import sys
from datetime import datetime
from pathlib import Path
from collections.abc import Sequence
from typing import NamedTuple

RESERVED_FILES = {"index.md"}
DEFAULT_TYPE_REGISTRY = [
    "Architecture",
    "Security Control",
    "Database Table",
    "API Endpoint Group",
    "Feature",
    "Testing Strategy",
    "Style Guide",
    "Integration",
    "Configuration",
    "Playbook",
]
DEFAULT_OKF_VERSION = "0.1"

# A pinned version is printed into the summary line that every gating consumer
# reads as the verdict, so its charset is constrained rather than merely its
# type. Without this a config-supplied string can carry newlines and forge a
# clean sign-off; see ``valid_okf_version``.
OKF_VERSION_RE = re.compile(r"[0-9A-Za-z][0-9A-Za-z._+-]{0,31}")

# The generation of this *script*, distinct from ``okf_version`` above, which is
# the OKF **spec** version and has stayed "0.1" across every change to this file.
# Projects copy this validator rather than referencing it, so an upgrade needs a
# way to ask a copy which generation it is without diffing it. Bump on any change
# to what the validator reports.
#
# 1.0.0 -- type/registry errors, index okf_version, link warnings
# 1.1.0 -- adds the timestamp shape check and the citation-recency cross-check
# 1.2.0 -- an undetermined repo boundary is reported instead of substituted, and
#          ``--repo-root`` states one; config values are shape-checked and a
#          registry-less candidate falls through, both reported as ``note:``
# 1.3.0 -- only ``index.md`` is reserved; ``log.md`` is not part of the bundle
OKF_TOOLING_VERSION = "1.3.0"

# Bounded for the same reason as the harvesting patterns below, and measured for
# the same failure: unbounded, an unmatched "[" run is quadratic (80k "[" takes
# 2.2s against 0.03s bounded), and this one runs over every markdown file in the
# bundle rather than a line at a time. A link whose text or target exceeds the
# bound is not a link anyone wrote by hand.
LINK_RE = re.compile(r"\[[^\]]{0,512}\]\(([^)]{0,512})\)")

# `timestamp` records when a concept's claims were last verified against source,
# not when the file changed -- see SKILL.md, "Concept document format".
TIMESTAMP_RE = re.compile(r"\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z")
TIMESTAMP_FORMAT = "%Y-%m-%dT%H:%M:%SZ"
DATE_FORMAT = "%Y-%m-%d"

# Both `# Citations` and `## Citations` occur in real bundles, so the heading
# level is not pinned. The section ends at the next heading of any level.
CITATIONS_HEADING_RE = re.compile(r"^#{1,6}[ \t]*Citations[ \t]*$")
HEADING_RE = re.compile(r"^#{1,6}[ \t]")
FENCE_RE = re.compile(r"^\s*(`{3,}|~{3,})")

# Anchored so a date cannot be pulled out of a longer digit run. The exclusions
# below matter more: a bundle cites file paths, URLs and header values, and every
# one of those can carry a date-shaped substring that nobody verified anything on.
DATE_RE = re.compile(r"(?<!\d)\d{4}-\d{2}-\d{2}(?!\d)")
INLINE_CODE_RE = re.compile(r"`[^`]*`")
URL_RE = re.compile(r"<?https?://\S+>?")
# The whole link is dropped -- text and target both: a date in the *link text*
# (`[2099-01-01 report](./a.md)`) is a reference too. The bound on each part keeps
# the scan linear -- an unmatched `](` in a long line is otherwise quadratic, and
# this runs over untrusted document text in CI and on fleet sweeps.
MARKDOWN_LINK_RE = re.compile(r"\[[^\]]{0,512}\]\([^)]{0,512}\)")
LINK_TARGET_RE = re.compile(r"\]\([^)]{0,512}\)")


def is_verification_timestamp(value: str) -> bool:
    """Return True when *value* is a real UTC instant in ``YYYY-MM-DDTHH:MM:SSZ``.

    Each half catches what the other cannot. ``fullmatch`` pins the shape, because
    ``strptime`` accepts unpadded fields and would let ``2026-8-17T0:0:0Z`` through
    -- which would break the plain-string sortability the one canonical shape
    exists for. ``strptime`` then rejects values that are well-shaped but not real
    dates, such as ``2026-13-45T99:99:99Z`` or ``2026-02-30T00:00:00Z``, which the
    pattern alone would accept.

    The two do overlap: trailing junk is caught by either. That redundancy is
    deliberate, so neither check is load-bearing alone.

    ``datetime`` is part of the standard library, so this costs the script none of
    its "nothing beyond python3" property -- the pattern is not here to avoid a
    dependency, only to constrain the shape.
    """
    if not TIMESTAMP_RE.fullmatch(value):
        return False
    try:
        datetime.strptime(value, TIMESTAMP_FORMAT)
    except ValueError:
        return False
    return True


def _unfenced_lines(text: str) -> list[str]:
    """*text* split into lines with fenced-code regions blanked out.

    Positions are preserved (fenced lines become empty strings) so a caller can
    still reason about order. Blanking rather than deleting matters because a
    concept *about* the document format embeds example frontmatter and example
    ``# Citations`` sections in fences -- and this bundle contains exactly such a
    concept. Without this, the first fenced example outranks the real section.
    """
    result: list[str] = []
    fence: str | None = None
    for line in text.splitlines():
        match = FENCE_RE.match(line)
        if fence is None:
            if match:
                fence = match.group(1)
                result.append("")
                continue
            result.append(line)
            continue
        result.append("")
        # CommonMark: a fence closes only on a run of the *same* character that is
        # at least as long as the opener. Matching on a fixed three characters lets
        # an inner ``` close a ```` opener -- and four backticks is the standard way
        # to embed a fenced example, which is exactly what a concept about this
        # format contains.
        if match and _closes_fence(line, match, fence):
            fence = None
    return result


def _closes_fence(line: str, match: re.Match[str], fence: str) -> bool:
    """Whether *line* closes an open *fence*, per CommonMark.

    Three conditions, and the two beyond "same character" both matter here. A
    closer may carry **no info string**, so ```` ``` js ```` opens a nested block
    rather than closing one -- without that check a fenced example containing an
    info-string line ends the fence early and its sample dates leak back into
    harvesting. And a closer is indented **at most 3 spaces**; beyond that it is
    indented-code content, not a fence.
    """
    run = match.group(1)
    if run[0] != fence[0] or len(run) < len(fence):
        return False
    if len(line) - len(line.lstrip(" ")) > 3:
        return False
    return line.strip()[len(run) :].strip() == ""


def _date_candidates(line: str) -> list[str]:
    """Dates in *line*, ignoring inline code, URLs, and link targets.

    A citation names sources, and sources carry date-shaped text that records
    nothing about verification: ``report-2099-01-01.md``, a blog URL, or a header
    value such as ``X-GitHub-Api-Version: 2026-05-05``. Harvesting those would
    let the upgrade pass assert a verification nobody performed -- the exact
    failure the two-case rule exists to prevent.
    """
    # Cheap reject first: the exclusion passes are the expensive part, and a line
    # with no date-shaped text at all cannot produce a candidate.
    if not DATE_RE.search(line):
        return []
    stripped = MARKDOWN_LINK_RE.sub(" ", line)
    stripped = INLINE_CODE_RE.sub(" ", stripped)
    stripped = URL_RE.sub(" ", stripped)
    stripped = LINK_TARGET_RE.sub(" ", stripped)
    return DATE_RE.findall(stripped)


def newest_citation_date(text: str) -> tuple[str, str] | None:
    """Return ``(newest_date, deciding_line)`` from *text*'s ``Citations`` section.

    The line is returned with the date rather than looked up afterwards, so the
    evidence a caller shows is by construction the line that decided the value.
    Re-scanning the file for the date instead finds the first coincidental match,
    which is how a correction ends up displaying an unrelated line as its proof.

    Returns ``None`` when the file has no ``Citations`` heading, or when the
    section holds no parseable date -- both are ordinary, not findings. Many
    bundles cite files and symbols rather than verification dates, and a citation
    style that carries no dates must stay silent rather than warn on every concept.

    Plain string ``max`` is correct only because every candidate has already been
    proved a real date in the one zero-padded ISO shape, where lexical and
    chronological order coincide. ``strptime`` does that proving: it drops
    impossible dates such as ``2026-13-45`` that the pattern alone would accept
    and that would otherwise sort above every real date in the file.
    """
    lines = _unfenced_lines(text)
    start = None
    for i, line in enumerate(lines):
        if CITATIONS_HEADING_RE.match(line):
            start = i + 1
            break
    if start is None:
        return None
    # Stop at the next heading of any level so dates belonging to a later
    # section are not attributed to the citations.
    end = len(lines)
    for i in range(start, len(lines)):
        if HEADING_RE.match(lines[i]):
            end = i
            break
    best: str | None = None
    best_line = ""
    for line in lines[start:end]:
        for candidate in _date_candidates(line):
            try:
                datetime.strptime(candidate, DATE_FORMAT)
            except ValueError:
                continue
            if best is None or candidate > best:
                best, best_line = candidate, line.strip()
    return (best, best_line) if best is not None else None


def _is_repo_root(path: Path) -> bool:
    """Whether *path* holds a ``.git`` entry.

    ``exists()`` rather than ``is_dir()``: a git worktree records ``.git`` as a
    file. ``find_repo_root`` is its only caller. ``resolution_report`` does *not*
    use it: a stated boundary is annotated as stated whether or not it holds a
    ``.git``, because "where did this boundary come from" and "does this
    directory look like a repository" are different questions and answering the
    first with the second mislabels ``--repo-root <a real repo>`` as detected.
    """
    return (path / ".git").exists()


def find_repo_root(start: Path) -> Path | None:
    """Walk upward from *start* to the enclosing git repository root.

    Deliberately independent of this script's own location. A target project may
    copy the script to ``scripts/`` (the usual case) or embed it inside its own
    skill's asset folder at any depth, so root detection is anchored to the
    bundle path the caller passes in.

    ``.git`` is tested with ``exists()`` rather than ``is_dir()`` because a git
    worktree records it as a *file*. The same test stops at a submodule's own
    root, which means a bundle inside a submodule reads the submodule's config
    rather than the parent project's — the nearest enclosing repository wins,
    deliberately, since that is the repository whose rules the bundle lives under.

    Returns ``None`` when no enclosing ``.git`` is found. This function detects a
    boundary; it does not invent one. Returning the bundle path instead — as this
    once did — silently substitutes a *policy*, and that policy is wrong:
    every legitimate link from the bundle out into the rest of the source tree
    then reads as escaping the repository. Callers decide what an unknown
    boundary means; ``check_links`` skips containment, and ``main`` falls back to
    the bundle only for the *config search*, which is a different question.
    """
    current = start.resolve()
    for candidate in (current, *current.parents):
        if _is_repo_root(candidate):
            return candidate
    return None


class ConfigResolution(NamedTuple):
    """What ``load_config`` resolved, and what it had to skip on the way.

    ``notes`` is the function's only reporting channel. No config fault reaches
    the caller as an exception -- unreadable, undecodable, unparseable (including
    the ``RecursionError`` and ``MemoryError`` that deep or huge JSON raises past
    a ``ValueError`` clause), wrong-shaped at any level, or registry-less. That
    is deliberate rather than merely tidy: ``typeRegistry`` drives the single
    hard error this validator emits, so a config that could turn a documentation
    gate red would be worse than the config being bad.
    """

    types: set[str]
    version: str
    source: Path | None
    notes: list[str]


def valid_type_registry(raw: object) -> list[str] | None:
    """*raw* as a list of type names, or ``None`` when it is not one.

    Every non-list is rejected outright rather than coerced, because the
    coercions are all silently wrong. ``set("Feature")`` is the motivating case:
    a bare string is iterable, so the registry became the six-letter set
    ``{'F','a','e','r','t','u'}`` and every real type in the bundle then errored
    as unregistered. An ``int`` is not iterable at all and raised ``TypeError``
    straight out of ``load_config``, past an ``except`` clause that did not list
    it. A ``dict`` iterates its keys.

    A list that is empty, or that holds anything other than non-blank strings, is
    rejected **whole** rather than filtered. Filtering would drop the unusable
    entries and leave a registry that looks complete while erroring on exactly
    the types it quietly discarded. ``bool`` needs no special case here: it is an
    ``int``, not a ``str``.

    Entries are returned **stripped**, because the other side of the comparison
    already is: ``parse_frontmatter`` strips every value it reads, and the type
    check is an exact string match. An entry of ``"Feature "`` would otherwise
    pass this guard and still match nothing, rendering an error whose registry
    *visibly contains* the type the author wrote — the misleading-message failure
    this function exists to prevent, reached through it instead of around it.
    """
    if not isinstance(raw, list) or not raw:
        return None
    for entry in raw:
        if not isinstance(entry, str) or not entry.strip():
            return None
    return [entry.strip() for entry in raw]


def valid_okf_version(raw: object) -> str | None:
    """*raw* as a pinned-version string, or ``None`` when it is not a scalar.

    ``str()`` accepts anything, so a dict reached the banner as
    ``OKF v{'x': 1}`` and the comparison against ``index.md``'s declared
    ``okf_version`` could then never match — turning a version pin into a
    permanent warning nobody could satisfy. ``bool`` is excluded explicitly
    because it is an ``int`` subclass and ``OKF vTrue`` is not a version; a JSON
    number is accepted, since ``"okfVersion": 0.1`` is a natural thing to write.
    Note that a number cannot express a trailing zero — ``1.10`` pins as ``1.1``
    and then disagrees with an ``index.md`` declaring ``1.10`` — so a string is
    the shape to prefer.

    The **content** is constrained too, not only the type, and that is the half
    that matters for more than tidiness. This value is interpolated into the
    summary line the whole toolchain treats as the verdict
    (``OKF v{version}: N concept file(s) checked, ...``), as a bare string rather
    than through ``repr``. A version carrying newlines can therefore print a
    forged ``0 error(s), 0 warning(s).`` line *above* the real one — verified
    against a vendored bundle supplying its own config. ``typeRegistry`` is safe
    from the same trick only by accident, because it reaches output through
    ``sorted()`` inside an f-string and is escaped by ``repr``.
    """
    if isinstance(raw, bool) or raw is None:
        return None
    if isinstance(raw, (int, float)):
        raw = str(raw)
    if not isinstance(raw, str):
        return None
    candidate = raw.strip()
    return candidate if OKF_VERSION_RE.fullmatch(candidate) else None


CONFIG_CANDIDATES = (
    ("agents", ".agent-config.json"),
    ("agents", "templates", "agent-config.example.json"),
)


def config_candidates(config_root: Path) -> tuple[Path, ...]:
    """The config files searched under *config_root*, in precedence order."""
    return tuple(config_root.joinpath(*parts) for parts in CONFIG_CANDIDATES)


def find_config_root(bundle_root: Path, repo_root: Path | None) -> Path:
    """Where to look for ``agents/`` config, given a possibly-unknown boundary.

    With a boundary, that is the answer: config belongs to the repository the
    bundle lives in. Without one, this walks upward from the bundle for the
    nearest ancestor that actually holds a config candidate.

    That walk is the other half of the boundary defect, and it is easy to miss
    because it fails in a different colour. Collapsing the config search to the
    bundle — the behaviour this replaces — means a ``git archive`` export, a
    Docker ``COPY`` of source, or a tarball release never finds the **tracked**
    ``agents/templates/agent-config.example.json`` sitting one directory up, so
    it validates against the built-in registry and reports every one of the
    project's own types as unregistered. Those are **errors**, not warnings, so
    the same class of false positive that made links noisy makes types fatal.

    Looking for a real candidate rather than merely an ``agents`` directory
    keeps an unrelated folder of that name from capturing the search. When
    nothing is found the bundle is returned, which resolves to the built-in
    defaults exactly as before — and ``resolution_report`` names the base either
    way, because a config found three levels up is as surprising as one missed.
    """
    if repo_root is not None:
        return repo_root
    current = bundle_root.resolve()
    for candidate in (current, *current.parents):
        if any(path.is_file() for path in config_candidates(candidate)):
            return candidate
    return current


def load_config(repo_root: Path) -> ConfigResolution:
    """Resolve the allowed type set and pinned OKF version under *repo_root*.

    Candidates are ``agents/.agent-config.json`` then
    ``agents/templates/agent-config.example.json``. The first candidate that
    yields a **usable** ``documentation.technicalBundle.typeRegistry`` wins and
    supplies both values; ``source`` names that file, and is ``None`` when no
    candidate did.

    Yielding a registry is the gate, not merely parsing. ``agents/.agent-config.json``
    is gitignored and per-machine, so a well-formed but *incomplete* copy is the
    realistic failure — and returning on it let that copy silently shadow the
    committed template while the run still looked clean. A malformed config
    already fell through correctly; a valid but registry-less one did not.

    Values are shape-checked before they are used rather than after they have
    corrupted the registry — see ``valid_type_registry`` for what each wrong
    shape used to do. Notes name the offending **key**, never its value: a
    config file may carry credentials, and this text is printed.
    """
    notes: list[str] = []
    for config_path in config_candidates(repo_root):
        label = config_path.relative_to(repo_root)
        try:
            if not config_path.is_file():
                continue
            text = config_path.read_text(encoding="utf-8")
        except (OSError, UnicodeDecodeError):
            # UnicodeDecodeError is a ValueError, so it must be caught *here* or
            # a UTF-16 config is reported as bad JSON and the reader edits the
            # wrong thing.
            notes.append(f"{label}: could not be read; skipped")
            continue
        try:
            cfg = json.loads(text)
        except ValueError:
            notes.append(f"{label}: is not valid JSON; skipped")
            continue
        except (RecursionError, MemoryError):
            # Not a ValueError, so the clause above does not cover it. A deeply
            # nested config otherwise escapes this function entirely and exits
            # with a traceback and CPython's exit 1 -- which is the
            # errors-found code, so a documentation gate goes red claiming the
            # bundle is broken. Reachable far sooner on the older interpreters
            # the copied asset is gated for, whose recursion limit is lower.
            notes.append(f"{label}: could not be parsed; skipped")
            continue
        # An explicit ladder rather than ``except (AttributeError, TypeError)``
        # around the whole descent. Both would stop the crash, but only this one
        # can say *which* key is the wrong shape, and a note that cannot be acted
        # on is barely better than the silence it replaced. It is also why
        # ``TypeError`` is absent from the ``except`` clauses above: the guards
        # here and in ``valid_type_registry`` prevent the ``set(5)`` that used to
        # raise it, rather than catching it after the fact.
        if not isinstance(cfg, dict):
            notes.append(f"{label}: top level is not a JSON object; skipped")
            continue
        # Each level is tested for its own shape *before* the ``or {}``
        # normalisation, not after: ``"documentation": []`` is falsy, so
        # normalising first turns a wrong shape into an absent key and the note
        # blames ``typeRegistry`` for a fault two levels up.
        documentation = cfg.get("documentation")
        if documentation is not None and not isinstance(documentation, dict):
            notes.append(f"{label}: documentation is not an object; skipped")
            continue
        bundle = (documentation or {}).get("technicalBundle")
        if bundle is not None and not isinstance(bundle, dict):
            notes.append(
                f"{label}: documentation.technicalBundle is not an object; skipped"
            )
            continue
        bundle = bundle or {}
        raw_types = bundle.get("typeRegistry")
        raw_version = bundle.get("okfVersion")
        types = valid_type_registry(raw_types)
        if types is None:
            # Absent and malformed are different faults with different fixes, so
            # they get different notes -- naming *which* key is wrong is the
            # whole reason this is a ladder rather than one broad ``except``.
            if raw_types is None:
                detail = "declares no documentation.technicalBundle.typeRegistry"
            else:
                detail = (
                    "documentation.technicalBundle.typeRegistry is not a "
                    "non-empty list of type names"
                )
            # A discarded version is named too. The malformed case below already
            # names its key, so staying silent here would report the lesser
            # fault and hide the greater: the file's pin is being dropped.
            lost = "" if raw_version is None else "; its okfVersion is not used"
            notes.append(f"{label}: {detail}; skipped{lost}")
            continue
        version = valid_okf_version(raw_version)
        if version is None and raw_version is not None:
            notes.append(
                f"{label}: documentation.technicalBundle.okfVersion is not a "
                f"version scalar; using {DEFAULT_OKF_VERSION}"
            )
        return ConfigResolution(
            set(types), version or DEFAULT_OKF_VERSION, config_path, notes
        )
    return ConfigResolution(
        set(DEFAULT_TYPE_REGISTRY), DEFAULT_OKF_VERSION, None, notes
    )


def _strip_comment(value: str) -> str:
    """Drop a trailing ``#`` comment from an *unquoted* YAML scalar.

    Only unquoted scalars: inside quotes a ``#`` is content, not a comment.
    Without this, ``timestamp: 2026-01-01T00:00:00Z # verified by hand`` -- valid
    YAML whose value is the instant -- is reported as malformed, telling the
    author to fix a shape that is already correct.
    """
    quote = value[:1]
    if quote in "\"'":
        # Only a `#` *after* the closing quote is a comment; inside, it is content.
        end = value.find(quote, 1)
        if end == -1:
            return value
        tail = value[end + 1 :]
        cut = tail.find("#")
        return value[: end + 1] if cut != -1 else value
    cut = value.find(" #")
    return value[:cut].rstrip() if cut != -1 else value


def _unquote(value: str) -> str:
    """Strip a single pair of matching surrounding quotes from a YAML scalar."""
    if len(value) >= 2 and value[0] == value[-1] and value[0] in "\"'":
        return value[1:-1]
    return value


def parse_frontmatter(text: str) -> dict[str, str] | None:
    """Parse a leading ``---`` fenced YAML block into a flat string dict.

    Returns ``None`` when no frontmatter block is present. Only the shallow
    ``key: value`` pairs needed for validation are extracted.
    """
    lines = text.splitlines()
    if not lines or lines[0].strip() != "---":
        return None
    fm: dict[str, str] = {}
    for line in lines[1:]:
        if line.strip() == "---":
            return fm
        match = re.match(r"^([A-Za-z0-9_]+):\s*(.*)$", line)
        if match:
            fm[match.group(1)] = _unquote(_strip_comment(match.group(2).strip()))
    return None  # unterminated frontmatter block


def iter_markdown(root: Path):
    yield from sorted(root.rglob("*.md"))


def _shown_target(target: str) -> str:
    """*target* rendered safely for a warning line.

    The target comes from untrusted document text and is echoed back into output
    that agents and CI logs read. ``LINK_RE`` admits newlines, so an unrendered
    target can inject whole lines -- the same forgery the pinned-version charset
    closes on the config side, sourced from the document instead. ``repr``
    escapes control characters and quotes the value so its extent is visible;
    the truncation keeps one hostile link from burying the rest of the report.
    """
    collapsed = " ".join(target.split())
    if len(collapsed) > 120:
        collapsed = collapsed[:117] + "..."
    return repr(collapsed)


def check_links(
    path: Path, text: str, root: Path, repo_root: Path | None, warnings: list[str]
) -> None:
    """Warn on broken cross-links (skip anchors, external URLs, mailto).

    A link is broken only if its resolved target does not exist, or if it escapes
    *repo_root*. References to real files outside the bundle but inside the repo
    (code, scripts, sibling docs) are valid and not flagged.

    *repo_root* is ``None`` when no repository boundary could be determined. The
    escape check is then **skipped**, not narrowed to the bundle. Narrowing it
    would flag every legitimate out-of-bundle in-repo link the moment a tree has
    no ``.git`` — a ``git archive`` export, a Docker ``COPY`` of source, a
    tarball release, a vendored bundle — and both the ``okf-migration`` Phase 5
    gate and the user guide treat zero link warnings as sign-off, so those false
    positives block a release rather than merely annoying a reader. The existence
    check still runs: it needs no boundary, and it is the half that catches the
    typo. ``--repo-root`` restores containment for callers who know the answer.
    """
    for target in LINK_RE.findall(text):
        link = target.split("#", 1)[0].strip()
        if not link or "://" in link or link.startswith("mailto:"):
            continue
        shown = _shown_target(target)
        try:
            if link.startswith("/"):
                # OKF convention: leading-slash links are bundle-root-relative.
                resolved = (root / link.lstrip("/")).resolve()
            else:
                resolved = (path.parent / link).resolve()
        except (OSError, ValueError):
            # A NUL byte, an over-long component, or an unreadable parent. This
            # is untrusted document text, so an unresolvable target is a finding
            # about that document -- not a traceback that stops the whole bundle
            # being validated.
            warnings.append(f"{path}: unresolvable link target -> {shown}")
            continue
        if repo_root is not None:
            try:
                resolved.relative_to(repo_root)
            except ValueError:
                warnings.append(f"{path}: link points outside repository -> {shown}")
                continue
        if not resolved.exists():
            warnings.append(f"{path}: broken link -> {shown}")


def resolution_report(
    repo_root: Path | None,
    config_root: Path,
    config_source: Path | None,
    notes: Sequence[str] = (),
    *,
    bundle_root: Path | None = None,
    repo_root_stated: bool = False,
) -> list[str]:
    """Lines naming the resolved repository root and the config file read.

    A wrong root, or a fall-through to the built-in defaults, is otherwise
    indistinguishable from a clean run — and ``typeRegistry`` drives the only
    hard error this validator raises. Returned rather than printed so the
    content can be asserted directly.

    *repo_root* is the link-containment boundary and may be ``None``; *config_root*
    is where the config search ran and is always a real path. They are separate
    parameters because they answer separate questions and only coincide when a
    boundary was found — with none, config still has to be looked for somewhere,
    and conflating the two is what produced the false positives ``None`` exists
    to end. Every ``note:`` from ``load_config`` is printed here, because a
    skipped or malformed config otherwise reads as an ordinary clean run.

    *repo_root_stated* records **provenance**, not shape. Deriving it from
    ``_is_repo_root`` instead — asking whether the boundary holds a ``.git`` —
    silently reports ``--repo-root <a real repository>`` as auto-detected, which
    is the one case where a reader most needs to know a human chose it.

    The unknown boundary is deliberately **not** reported here as a ``warning:``
    line. It is appended to the run's real warnings by ``main`` instead, so it
    reaches the ``N warning(s)`` count every gating consumer keys on. A notice
    that only a careful reader notices is a request; a counted warning is a
    control.
    """
    if config_source is None:
        config_label = "built-in defaults"
    else:
        config_label = str(config_source.relative_to(config_root))
    if repo_root is None:
        root_label = f"not detected (no .git found above {bundle_root})"
    elif repo_root_stated:
        root_label = f"{repo_root} (stated with --repo-root)"
    else:
        root_label = str(repo_root)
    if config_source is None or config_root != repo_root:
        # Name the base whenever it is not simply the boundary. A config found
        # three directories above the bundle is as surprising as one that was
        # missed, and "built-in defaults" with no explanation is the line that
        # made a wrong registry look like a project with no config at all.
        config_label = f"{config_label} (searched from {config_root})"
    lines = [f"repo root: {root_label}", f"config:    {config_label}"]
    lines.extend(f"note:      {note}" for note in notes)
    return lines


def _contains(root: Path, child: Path) -> bool:
    """Whether *child* lies inside *root*, both already resolved.

    The lexical test comes first and answers almost every call. The fallback
    exists because ``resolve()`` normalises symlinks but **not case**, and macOS
    filesystems are case-insensitive by default: ``--repo-root /x/repo`` against
    a bundle under ``/x/Repo`` is the same directory, and the lexical test
    refuses it with a message whose two paths visibly contain one another. The
    guard built to prevent a false-positive flood then *is* one.

    ``samefile`` compares device and inode, so it answers the question the
    filesystem would. Only the auto-detected path is immune to this, because
    there both sides derive from one ``Path`` object and their casing cannot
    diverge — a separately typed ``--repo-root`` is the whole exposure.
    """
    try:
        child.relative_to(root)
        return True
    except ValueError:
        pass
    for candidate in (child, *child.parents):
        try:
            if candidate.samefile(root):
                return True
        except OSError:
            return False
    return False


def parse_args(argv: list[str]) -> tuple[Path, Path | None] | None:
    """Return ``(bundle_root, stated_repo_root)``, or ``None`` on a usage error.

    Hand-rolled rather than ``argparse`` because the usage case must print this
    module's docstring to **stdout** and exit 2. That docstring is the canonical
    enumeration of what the validator reports — skill files link to it rather
    than restate it — and ``argparse`` would print its own summary to stderr
    instead, where the callers that read this output would not see it.

    Anything unrecognised is a usage error rather than a guess, including a
    repeated ``--repo-root`` (in either spelling), a second positional argument,
    an empty value, and a flag where ``--repo-root``'s value should be. That
    last one matters more than it looks: swallowing the next flag as the value
    lands in ``ERROR: --repo-root not found: --whatever`` and exit **1**, which
    is the errors-found code, rather than the usage text and exit 2.

    An empty positional is refused for the same reason. ``Path("")`` is
    ``Path(".")``, so a wrapper invoking ``okf_validate.py "$BUNDLE"`` with
    ``BUNDLE`` unset would silently validate the current working directory and
    report a missing ``index.md`` in a bundle nobody named.

    ``--`` ends option parsing, which is what makes a bundle path beginning with
    ``-`` reachable at all.
    """
    bundle: str | None = None
    stated_root: str | None = None
    rest = list(argv[1:])
    options = True
    while rest:
        arg = rest.pop(0)
        if options and arg == "--":
            options = False
        elif options and arg == "--repo-root":
            if not rest or stated_root is not None or rest[0].startswith("-"):
                return None
            stated_root = rest.pop(0)
        elif options and arg.startswith("--repo-root="):
            if stated_root is not None:
                return None
            stated_root = arg.split("=", 1)[1]
        elif options and arg.startswith("-"):
            return None
        elif bundle is None:
            bundle = arg
        else:
            return None
    if not bundle or stated_root == "":
        return None
    return Path(bundle), Path(stated_root) if stated_root is not None else None


def main(argv: list[str]) -> int:
    parsed = parse_args(argv)
    if parsed is None:
        print(__doc__)
        return 2
    bundle_root, stated_root = parsed
    if not bundle_root.exists():
        print(f"ERROR: bundle root not found: {bundle_root}")
        return 1
    if not bundle_root.is_dir():
        print(f"ERROR: bundle root is not a directory: {bundle_root}")
        return 1

    if stated_root is None:
        repo_root = find_repo_root(bundle_root)
    else:
        if not stated_root.exists():
            print(f"ERROR: --repo-root not found: {stated_root}")
            return 1
        if not stated_root.is_dir():
            print(f"ERROR: --repo-root is not a directory: {stated_root}")
            return 1
        repo_root = stated_root.resolve()
        if not _contains(repo_root, bundle_root.resolve()):
            # Refuse rather than accept: a boundary that does not contain the
            # bundle makes *every* link escape it, which is precisely the
            # false-positive flood this flag exists to prevent.
            print(
                f"ERROR: bundle {bundle_root.resolve()} is not inside "
                f"--repo-root {repo_root}"
            )
            return 1

    config_root = find_config_root(bundle_root, repo_root)
    config = load_config(config_root)
    allowed_types, okf_version = config.types, config.version
    # Print the frame before the findings: when a wrong root produces spurious
    # escape warnings, the root that explains them must not sit below the list.
    for line in resolution_report(
        repo_root,
        config_root,
        config.source,
        config.notes,
        bundle_root=bundle_root.resolve(),
        repo_root_stated=stated_root is not None,
    ):
        print(line)

    errors: list[str] = []
    warnings: list[str] = []
    concept_count = 0

    if repo_root is None:
        # Counted, not merely printed. Every gating consumer -- okf-bundle's
        # "zero of each" bar, okf-migration Phase 5, documentation, pii-scrubbing
        # -- reads the "N warning(s)" summary, so a bundle whose containment was
        # never checked must not be able to report a clean sign-off.
        warnings.append(
            "link containment not checked: no repository boundary "
            "(no .git found; pass --repo-root PATH to state one)"
        )

    index = bundle_root / "index.md"
    if not index.is_file():
        errors.append(f"{bundle_root}/index.md: required reserved file is missing")
    else:
        fm = parse_frontmatter(index.read_text(encoding="utf-8"))
        if not fm or "okf_version" not in fm:
            errors.append(f"{index}: index.md must declare okf_version in frontmatter")
        elif fm["okf_version"] != okf_version:
            warnings.append(
                f"{index}: okf_version {fm['okf_version']} != pinned {okf_version}"
            )

    for path in iter_markdown(bundle_root):
        text = path.read_text(encoding="utf-8")
        check_links(path, text, bundle_root, repo_root, warnings)
        if path.name in RESERVED_FILES:
            continue
        concept_count += 1
        fm = parse_frontmatter(text)
        if fm is None:
            errors.append(f"{path}: missing or malformed YAML frontmatter block")
            continue
        ctype = fm.get("type")
        if not ctype:
            errors.append(f"{path}: frontmatter missing required 'type'")
        elif ctype not in allowed_types:
            errors.append(
                f"{path}: type '{ctype}' not in registry {sorted(allowed_types)}"
            )
        if not fm.get("title"):
            warnings.append(f"{path}: frontmatter missing 'title'")
        if not fm.get("description"):
            warnings.append(f"{path}: frontmatter missing 'description'")
        timestamp = fm.get("timestamp")
        if not timestamp:
            warnings.append(f"{path}: frontmatter missing 'timestamp'")
        elif not is_verification_timestamp(timestamp):
            warnings.append(
                f"{path}: timestamp '{timestamp}' is not ISO-8601 UTC "
                "(expected YYYY-MM-DDTHH:MM:SSZ)"
            )
        else:
            # Only cross-check a timestamp already proved well-formed: comparing
            # against a malformed value would add a second warning about the same
            # defect, and the date slice below assumes the canonical shape.
            citation = newest_citation_date(text)
            if citation is not None and citation[0] > timestamp[:10]:
                warnings.append(
                    f"{path}: frontmatter timestamp '{timestamp}' is older than "
                    f"its newest citation date '{citation[0]}'"
                )

    for w in warnings:
        print(f"WARN  {w}")
    for e in errors:
        print(f"ERROR {e}")

    print(
        f"\nOKF v{okf_version}: {concept_count} concept file(s) checked, "
        f"{len(errors)} error(s), {len(warnings)} warning(s)."
    )
    return 1 if errors else 0


if __name__ == "__main__":
    sys.exit(main(sys.argv))
