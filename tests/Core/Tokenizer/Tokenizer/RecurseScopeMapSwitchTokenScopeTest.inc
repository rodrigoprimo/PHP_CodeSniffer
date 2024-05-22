<?php

/* testSwitchNormalSyntax */
switch ($value) {
    case 1:
        echo 'one';
        break;
    default :
        echo 'other';
        break;
}

/* testSwitchAlternativeSyntax */
switch ($value):
    /* testEnsureTestWillNotPickUpWrongColon */
    case 1:
        echo 'one';
        break;
    default:
        echo 'other';
        break;
/* testSwitchAlternativeSyntaxEnd */
endswitch;
