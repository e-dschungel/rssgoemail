#!/usr/bin/env python3
#script to create a ZIP release file

import subprocess
import tempfile
import shutil
import os

# config
COMPOSER = "composer.phar"
REPO_URL = "https://github.com/e-dschungel/rssgoemail.git"
ZIP_FILE_NAME = "rssgoemail.zip"
EXCLUDE_PATTERN = ["*.git/*", "create_zip_release.py", "*.gitignore", "mypear.xml", "mypsr12.xml", "phpstan-neon", "check_codingstyle.sh", "phpdoc.dist.xml"]

# create ZIP
cwd = os.getcwd()
with tempfile.TemporaryDirectory() as tempdir:
    os.chdir(tempdir)
    subprocess.call(["git", "clone", REPO_URL, tempdir])
    subprocess.call([COMPOSER, "install", "--no-dev"])
    subprocess.call(["zip", "-r", ZIP_FILE_NAME, ".",
                     "-x"] + EXCLUDE_PATTERN)
    abs_zip_path = os.path.join(cwd,ZIP_FILE_NAME)
    if os.path.isfile(abs_zip_path):
        os.remove(abs_zip_path)
    shutil.move(ZIP_FILE_NAME, abs_zip_path)

print("Created ZIP release file {}".format(ZIP_FILE_NAME))
