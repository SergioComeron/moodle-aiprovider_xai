#!/usr/bin/env bash
# Submit the plugin ZIP to the Moodle plugins directory.
# Release notes are the CHANGES.md body under a heading "## $TAG".
# When that section is missing, the notes are "Release $TAG".
# MOODLE_MARKETPLACE_TOKEN is sent only as the Authorization header.
# xtrace stays off so that header is not written to the log.

set -euo pipefail
set +x

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$repo_root"

: "${TAG:?TAG is required}"
: "${ZIP_NAME:?ZIP_NAME is required}"
: "${GITHUB_REPOSITORY:?GITHUB_REPOSITORY is required}"

zip_dir="${ZIP_DIR:-dist}"
zip_path="${zip_dir}/${ZIP_NAME}"
if [[ ! -f "$zip_path" ]]; then
  echo "ZIP not found: ${zip_path}" >&2
  exit 1
fi

python3 .github/release/check-zip-release.py "$zip_path" "$TAG"

notes_file="${RUNNER_TEMP:-/tmp}/aiprovider-xai-marketplace-notes.txt"
notes=""
if [[ -f CHANGES.md ]]; then
  notes="$(awk -v tag="$TAG" '
    $0 == "## " tag { found = 1; next }
    found && /^## / { exit }
    found { print }
  ' CHANGES.md)"
else
  echo "CHANGES.md is missing; using the fallback release notes." >&2
fi

if [[ -z "${notes//[[:space:]]/}" ]]; then
  notes="Release ${TAG}"
fi
printf '%s\n' "$notes" > "$notes_file"
echo "Marketplace release notes:"
cat "$notes_file"

if [[ -z "${MKPL_TOKEN:-}" ]]; then
  echo "::error::Repository secret MOODLE_MARKETPLACE_TOKEN is missing or empty." >&2
  exit 1
fi

echo "Submitting ${ZIP_NAME} for ${TAG} to the Moodle Marketplace"

response_file="${RUNNER_TEMP:-/tmp}/aiprovider-xai-marketplace-response.json"
# Read release notes from a file. An inline -F value is cut at the first
# semicolon. set +x again so the Authorization header is not traced.
set +x
http_status="$(curl -sS --retry 3 --retry-delay 2 \
  --proto '=https' --proto-redir '=https' \
  -o "$response_file" -w "%{http_code}" \
  -X POST \
  -H "Authorization: Bearer ${MKPL_TOKEN}" \
  -F "file=@${zip_path};type=application/zip" \
  -F "releaseNotes=<${notes_file}" \
  --form-string "vcsRepositoryUrl=https://github.com/${GITHUB_REPOSITORY}" \
  --form-string "vcsTag=${TAG}" \
  "https://marketplace.moodle.com/api/plugins/aiprovider_xai/versions")"

echo "Response HTTP status: ${http_status}"
if python3 -c 'import json,sys; json.load(open(sys.argv[1], encoding="utf-8"))' "$response_file" >/dev/null 2>&1; then
  python3 -m json.tool "$response_file"
else
  cat "$response_file"
fi

if [[ "$http_status" != "201" ]]; then
  echo "::error::Moodle Marketplace rejected the version (HTTP ${http_status})." >&2
  python3 - "$response_file" <<'PY' || true
import json
import sys

path = sys.argv[1]
try:
    data = json.load(open(path, encoding="utf-8"))
except Exception:
    sys.exit(0)
violations = data.get("violations") if isinstance(data, dict) else None
if violations:
    print("Validation violations:", file=sys.stderr)
    print(json.dumps(violations, indent=2), file=sys.stderr)
PY
  exit 1
fi

python3 - "$response_file" <<'PY'
import json
import sys

try:
    data = json.load(open(sys.argv[1], encoding="utf-8"))
except Exception:
    data = {}
if not isinstance(data, dict):
    data = {}
print(
    "Submitted version "
    + str(data.get("version", ""))
    + ", status "
    + str(data.get("status", ""))
)
PY
