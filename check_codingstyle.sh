#!/bin/sh

PHPCS_EXEC="./vendor/bin/phpcs"
PHPCS_IGNORE_PATHS="*/vendor/*"
MIN_PHP_VERSION="5.6"
#PEAR_EXCLUDED_RULES="PEAR.WhiteSpace.ScopeIndent,Generic.Files.LineLength"

echo "Check PHP Coding Standard PSR12"
$PHPCS_EXEC --ignore=$PHPCS_IGNORE_PATHS --standard=psr12 .

echo "Check PHP Coding Standard PEAR (modified)"
$PHPCS_EXEC --ignore=$PHPCS_IGNORE_PATHS -s --standard=mypear.xml .

echo "Check PHP Compatibility > $MIN_PHP_VERSION"
$PHPCS_EXEC --ignore=$PHPCS_IGNORE_PATHS --standard=PHPCompatibility --runtime-set testVersion $MIN_PHP_VERSION .
