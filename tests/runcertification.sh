#!/bin/bash

TESTDIR=$(dirname $0);

CODECOVERAGE=1 \
COVERAGE='--configuration phpunit.xml --coverage-clover '$TESTDIR'/certification/coverage-clover.xml' \
$TESTDIR/runtests.sh

$TESTDIR/../../../../vendor/bin/oxmd $TESTDIR/../ $TESTDIR/certification/coverage-clover.xml text \
--reportfile-text certification/certification.txt \
--exclude docs/,out/,tests/,translations/,vendor/,views/,metadata.php
