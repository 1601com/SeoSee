<?php

$GLOBALS['TL_HOOKS']['generatePage'][] = ['agentur1601com\seosee\JsLoader', 'loadJs'];

$GLOBALS['TL_HOOKS']['generatePage'][] = ['agentur1601com\seosee\StyleLoader', 'loadStyles'];