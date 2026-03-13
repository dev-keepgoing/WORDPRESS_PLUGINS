#!/usr/bin/env bash
#
# Create WordPress plugin zip(s) for installation.
# Select one plugin or "All" to zip. Zips are written to install/ and overwrite if they exist.
#

set -e
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"
INSTALL_DIR="$SCRIPT_DIR/install"

# Plugin dirs: subdirs that contain at least one .php file (likely the main plugin file)
list_plugins() {
  for d in "$SCRIPT_DIR"/*/; do
    [ -d "$d" ] || continue
    name=$(basename "$d")
    # Skip non-plugin dirs
    [[ "$name" == .* || "$name" == install || "$name" == node_modules ]] && continue
    # Must contain at least one .php file
    if compgen -G "$d*.php" > /dev/null 2>&1; then
      echo "$name"
    fi
  done
}

plugins=($(list_plugins))
if [ ${#plugins[@]} -eq 0 ]; then
  echo "No plugin directories found."
  exit 1
fi

mkdir -p "$INSTALL_DIR"

echo ""
echo "Plugins in this project:"
echo ""
for i in "${!plugins[@]}"; do
  echo "  $((i + 1))) ${plugins[$i]}"
done
echo "  $(( ${#plugins[@]} + 1 ))) All (create one zip per plugin)"
echo "  0)  Quit"
echo ""
read -rp "Select plugin to zip (0-$(( ${#plugins[@]} + 1 ))): " choice

if [[ ! "$choice" =~ ^[0-9]+$ ]]; then
  echo "Invalid input."
  exit 1
fi

if [ "$choice" -eq 0 ]; then
  echo "Done."
  exit 0
fi

zip_one() {
  local name="$1"
  local zip_path="$INSTALL_DIR/${name}.zip"
  local src="$SCRIPT_DIR/$name"
  if [ ! -d "$src" ]; then
    echo "  Skip $name: directory not found."
    return 1
  fi
  # Create zip with contents of the plugin dir (so WordPress sees one folder inside the zip)
  (cd "$SCRIPT_DIR" && zip -r -q "$zip_path" "$name" -x "*.DS_Store" "*.git*" "node_modules/*" "*node_modules*")
  echo "  Created: $zip_path"
}

if [ "$choice" -le "${#plugins[@]}" ]; then
  name="${plugins[$((choice - 1))]}"
  echo ""
  zip_one "$name"
  echo ""
  echo "Done. Zip is in: $INSTALL_DIR"
else
  if [ "$choice" -ne $(( ${#plugins[@]} + 1 )) ]; then
    echo "Invalid selection."
    exit 1
  fi
  echo ""
  for name in "${plugins[@]}"; do
    zip_one "$name"
  done
  echo ""
  echo "Done. Zips are in: $INSTALL_DIR"
fi
