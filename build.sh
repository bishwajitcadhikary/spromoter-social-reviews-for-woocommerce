#!/bin/bash

# Define the path to your PHP file
php_file="spromoter.php"

# Extract the current version from the PHP file
current_version=$(grep -oP "Version:           \K[0-9.]+" "$php_file")

# Increase the version number (assuming a standard version format like x.y.z)
new_version=$(echo "$current_version" | awk -F. '{print $1"."$2"."$3 + 1}')

# Update the version in the PHP file
sed -i "s/Version:           $current_version/Version:           $new_version/" "$php_file"

# Display the updated version in green
echo -e "\e[32mVersion increased to $new_version\e[0m"

# Display git-related messages in cyan
echo -e "\e[36mCommitting changes to git & pushing to remote\e[0m"
git add .
git commit -m "Version increased to $new_version"
git push origin main

# Display tag-related messages in yellow
echo -e "\e[33mCreating a new tag\e[0m"
git tag -a "$new_version" -m "Version $new_version"
git push origin "$new_version"
