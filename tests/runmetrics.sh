#!/bin/bash
pdepend --summary-xml=metrics.xml --ignore=tests,vendor ../
php MC_Metrics.php metrics.xml>metrics.txt