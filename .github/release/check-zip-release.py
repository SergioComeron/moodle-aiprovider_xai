#!/usr/bin/env python3
"""Fail unless a plugin ZIP's $plugin->release matches a vX.Y.Z tag.

The tag's leading v is removed and compared with $plugin->release in
xai/version.php inside the ZIP. The assignment parser matches build-zip.py.
"""

import re
import sys
import zipfile
from pathlib import Path


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


def release_from_zip(path):
    """Read $plugin->release from xai/version.php in the ZIP."""
    with zipfile.ZipFile(path) as archive:
        try:
            raw = archive.read("xai/version.php")
        except KeyError:
            fail("ZIP is missing xai/version.php: " + path)
    release = plugin_field(raw.decode("utf-8"), "release")
    if release is None or release == "":
        fail("ZIP version.php has no $plugin->release: " + path)
    return release


def main():
    """Compare argv ZIP and TAG. Exit 0 only when they name one release."""
    if len(sys.argv) != 3:
        fail("Usage: check-zip-release.py ZIP TAG")
    zip_path, tag = sys.argv[1], sys.argv[2]
    if not Path(zip_path).is_file():
        fail("ZIP not found: " + zip_path)
    if not tag.startswith("v") or tag == "v":
        fail("Tag " + tag + " has no release name after the leading v.")
    expected = tag[1:]
    actual = release_from_zip(zip_path)
    if actual != expected:
        fail(
            "Tag "
            + tag
            + " does not match $plugin->release "
            + actual
            + " in "
            + zip_path
            + " (expected "
            + expected
            + ")."
        )
    print("ZIP $plugin->release " + actual + " matches tag " + tag)


if __name__ == "__main__":
    main()
