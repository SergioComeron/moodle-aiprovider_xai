#!/usr/bin/env bash
# Attach the plugin ZIP to a GitHub Release for an existing tag.
# Notes come from a CHANGELOG/CHANGES section for $plugin->release when
# that section exists. Otherwise GitHub generates them from commits.
# This script does not create tags and does not publish to the Moodle
# plugins directory. --verify-tag aborts if the tag is not already on the remote.

set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$repo_root"

: "${TAG_NAME:?TAG_NAME is required}"
: "${RELEASE:?RELEASE is required}"
: "${ZIP_NAME:?ZIP_NAME is required}"

zip_dir="${ZIP_DIR:-dist}"
zip_path="${zip_dir}/${ZIP_NAME}"
if [[ ! -f "$zip_path" ]]; then
  echo "ZIP not found: ${zip_path}" >&2
  exit 1
fi

notes_file="${RUNNER_TEMP:-/tmp}/aiprovider-xai-release-notes.md"
notes_status=0
python3 - "$RELEASE" "$notes_file" <<'PY' || notes_status=$?
import pathlib
import re
import sys

release, destination = sys.argv[1], sys.argv[2]
candidates = ["CHANGELOG.md", "CHANGELOG", "CHANGES.md", "CHANGES"]
source = next((name for name in candidates if pathlib.Path(name).is_file()), None)
if source is None:
    sys.exit(2)

heading = re.compile(r"^#{1,3}\s+")
token = re.compile(r"(?<![0-9A-Za-z.])" + re.escape(release) + r"(?![0-9A-Za-z.-])")
lines = pathlib.Path(source).read_text(encoding="utf-8").splitlines()
start = None
for index, line in enumerate(lines):
    if heading.match(line) and token.search(line):
        start = index
        break
if start is None:
    print("No " + release + " section in " + source + "; using commit notes.", file=sys.stderr)
    sys.exit(2)

chunk = [lines[start]]
for line in lines[start + 1:]:
    if heading.match(line):
        break
    chunk.append(line)
text = "\n".join(chunk).strip() + "\n"
if not text.strip():
    sys.exit(2)
pathlib.Path(destination).write_text(text, encoding="utf-8")
print("Release notes taken from " + source)
PY

if [[ "$notes_status" -ne 0 && "$notes_status" -ne 2 ]]; then
  exit "$notes_status"
fi

if gh release view "$TAG_NAME" --json tagName >/dev/null 2>&1; then
  echo "Release ${TAG_NAME} already exists; uploading ${ZIP_NAME}."
  gh release upload "$TAG_NAME" "$zip_path" --clobber
  exit 0
fi

title="aiprovider_xai ${RELEASE}"
if [[ "$notes_status" -eq 0 ]]; then
  gh release create "$TAG_NAME" "$zip_path" \
    --title "$title" \
    --notes-file "$notes_file" \
    --verify-tag
else
  gh release create "$TAG_NAME" "$zip_path" \
    --title "$title" \
    --generate-notes \
    --verify-tag
fi
