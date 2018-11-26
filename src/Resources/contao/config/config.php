<?php

$GLOBALS['TL_HOOKS']['generatePage'][] = array('agentur1601com\seosee\JsLoader', 'loadJsToLayout');

$GLOBALS['TL_HOOKS']['generatePage'][] = ['agentur1601com\seosee\StyleLoader', 'loadStyles'];