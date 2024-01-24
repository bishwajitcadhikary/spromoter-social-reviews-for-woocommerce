#!/bin/bash

# Define the path to your PHP file
php_file="spromoter.php"

# Extract the current version from the PHP file
current_version=$(grep -oP "Version:           \K[0-9.]+" "$php_file")

# Increase the version number (assuming a standard version format like x.y.z)
new_version=$(echo "$current_version" | awk -F. '{print $1"."$2"."$3 + 1}')

# Update the version in the PHP file
sed -i "s/Version:           $current_version/Version:           $new_version/" "$php_file"

# Display the updated version
echo "Version increased to $new_version"

echo "Commiting changes to git & pushing to remote"
git add .
git commit -m "Version increased to $new_version"
git push origin main

echo "Creating a new tag"
git tag -a "$new_version" -m "Version $new_version"
git push origin "$new_version"
