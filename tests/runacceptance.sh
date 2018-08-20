#!/bin/bash
cd functional/
casperjs --ssl-protocol=any \
         --direct \
         --log-level=debug \
         --fail-fast \
         test tests/
cd ../
