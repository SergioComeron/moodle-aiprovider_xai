#!/usr/bin/env python3
"""Build the installable aiprovider_xai ZIP from the git tree.

The archive root is xai/, via git archive --prefix. export-ignore rules in
.gitattributes decide what is left out. On a tag push (EVENT_NAME=push) the
tag must be v plus $plugin->release from version.php.
"""

import os
import re
import subprocess
import sys
import zipfile
from pathlib import Path

REPO = Path(__file__).resolve().parents[2]


def plugin_field(text, name):
    """Return the assigned value of $plugin->name, or None."""
    match = re.search(
        r"^\s*\$plugin->"
        + re.escape(name)
        + r"\s*=\s*(?:'([^']*)'|\"([^\"]*)\"|([0-9]+))\s*;",
        text,
        re.MULTILINE,
    )
    if not match:
        return None
    return next(group for group in match.groups() if group is not None)


def fail(message):
    """Print message to stderr and exit."""
    print(message, file=sys.stderr)
    sys.exit(1)


def check_archive(path):
    """Reject a ZIP that is not an installable xai/ plugin package."""
    with zipfile.ZipFile(path) as archive:
        names = archive.namelist()
    if not names:
        fail("ZIP is empty")

    has_version = False
    has_english = False
    has_tests = False
    errors = []
    for name in names:
        if name.endswith("/"):
            relname = name[:-1]
        else:
            relname = name
        if not relname.startswith("xai/") and relname != "xai":
            errors.append("path outside xai/: " + name)
            continue
        rel = relname[len("xai/"):] if relname.startswith("xai/") else ""
        parts = [part for part in rel.split("/") if part]
        if any(part == ".." for part in parts):
            errors.append("unsafe path: " + name)
            continue
        if rel == "version.php":
            has_version = True
        if rel.startswith("lang/en/") and rel.endswith(".php"):
            has_english = True
        if rel.startswith("tests/") and rel.endswith(".php"):
            has_tests = True

        base = parts[-1] if parts else ""
        if (
            any(part.startswith(".git") or part in {".github", "node_modules"} for part in parts)
            or rel in {".gitattributes", ".gitignore"}
            or rel.startswith("docs/")
            or rel == "docs"
            or rel.startswith("lang/es/")
            or rel == "lang/es"
            or base in {"phpunit.xml", "phpunit.xml.dist"}
            or base.endswith(".dist")
            or ".dist." in base
        ):
            errors.append("excluded path shipped: " + name)

    if not has_version:
        errors.append("missing xai/version.php")
    if not has_english:
        errors.append("missing xai/lang/en")
    if not has_tests:
        errors.append("tests were omitted")
    if errors:
        fail("ZIP check failed:\n" + "\n".join(errors))

    print("ZIP contents:")
    for name in names:
        print(name)


def main():
    """Read version.php, optionally check the tag, and write the ZIP."""
    os.chdir(REPO)
    archive_ref = os.environ.get("ARCHIVE_REF", "HEAD")
    text = subprocess.check_output(
        ["git", "show", archive_ref + ":version.php"],
        text=True,
    )
    version = plugin_field(text, "version")
    release = plugin_field(text, "release")
    if version is None or re.fullmatch(r"[0-9]+", version) is None:
        fail("version.php has no numeric $plugin->version")
    if release is None or release == "":
        fail("version.php has no $plugin->release")
    if re.search(r"[\s/\\]", release) or ".." in release:
        fail("$plugin->release cannot be used in a tag")

    event = os.environ.get("EVENT_NAME", "workflow_dispatch")
    tag = os.environ.get("TAG_NAME", "")
    if event == "push":
        if tag == "":
            fail("TAG_NAME is empty on a tag push")
        expected = "v" + release
        if tag != expected:
            fail(
                "Tag "
                + tag
                + " does not match $plugin->release "
                + release
                + " (expected "
                + expected
                + ")"
            )

    zipname = "aiprovider_xai_" + version + ".zip"
    zip_dir = Path(os.environ.get("ZIP_DIR", str(REPO)))
    zip_dir.mkdir(parents=True, exist_ok=True)
    zip_path = zip_dir / zipname
    if zip_path.exists():
        zip_path.unlink()

    subprocess.run(
        [
            "git",
            "archive",
            "--format=zip",
            "--prefix=xai/",
            "--output=" + str(zip_path),
            archive_ref,
        ],
        check=True,
    )
    check_archive(zip_path)
    print("Built " + str(zip_path))

    output = os.environ.get("GITHUB_OUTPUT")
    if output:
        with open(output, "a", encoding="utf-8") as handle:
            handle.write("version=" + version + "\n")
            handle.write("release=" + release + "\n")
            handle.write("zipname=" + zipname + "\n")


if __name__ == "__main__":
    main()
