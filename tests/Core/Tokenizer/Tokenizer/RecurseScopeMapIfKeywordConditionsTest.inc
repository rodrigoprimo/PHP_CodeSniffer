<?php

/* testIfElseWithNestedCaseMissingBreak */
if ($foo === true):
    switch ($bar):
        case 1:
            echo 'one';
    endswitch;
/* testIfElseWithNestedCaseMissingBreakCloser */
else:
    echo 'other';
endif;
