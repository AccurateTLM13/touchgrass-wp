#!/bin/bash
# Build reproducible installable ZIPs from the reviewed source.
#
# Usage: ./build.sh [output-dir]
# Produces: touchgrass-theme-2.2.0.zip, touchgrass-core-2.2.0.zip
#
# Reproducible: fixed file order (sorted), fixed permissions, no timestamps
# beyond the embedded version. Requires: zip.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")" && pwd)"
OUT="${1:-$ROOT/dist}"
VERSION="2.2.0"

command -v zip >/dev/null || { echo "zip is required"; exit 1; }

rm -rf "$OUT"
mkdir -p "$OUT"

build_zip() { # name srcdir
	local name="$1"
	local src="$2"
	local zipfile="$OUT/$name-$VERSION.zip"
	# Zip with the slug as the top-level folder (WordPress convention):
	# touchgrass-theme/style.css, touchgrass-core/touchgrass-core.php.
	local parentdir="$(dirname "$src")"
	local slugdir="$(basename "$src")"
	( cd "$parentdir" && find "$slugdir" -type f \
		-not -path "*/.*" \
		-not -name "*.map" \
		| LC_ALL=C sort | zip -q -X -9 "$zipfile" -@ )
	echo "built $zipfile ($(du -h "$zipfile" | cut -f1))"
}

build_zip "touchgrass-theme" "$ROOT/theme/touchgrass"
build_zip "touchgrass-core"  "$ROOT/plugins/touchgrass-core"

# Sanity: the zips must contain the versioned headers.
unzip -p "$OUT/touchgrass-theme-$VERSION.zip" touchgrass/style.css | grep -q "Version: $VERSION" || { echo "theme version mismatch"; exit 1; }
unzip -p "$OUT/touchgrass-core-$VERSION.zip" touchgrass-core/touchgrass-core.php | grep -q "Version: $VERSION" || { echo "plugin version mismatch"; exit 1; }
echo "OK: both zips carry version $VERSION"
