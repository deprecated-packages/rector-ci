#!/bin/bash

orig_branch=$(git rev-parse --abbrev-ref HEAD)
new_branch=${orig_branch}-fix

# Github API - Check if brach exists

git checkout -b ${new_branch}
echo 'Test' > test.txt
git add .
git commit -m "Test commit"
git push --set-upstream origin ${new_branch}

# create pull request from $branch to $originalBranch