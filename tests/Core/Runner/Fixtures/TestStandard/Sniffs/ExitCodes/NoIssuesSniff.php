<?php
/**
 * Test fixture.
 *
 * @see \PHP_CodeSniffer\Tests\Core\Runner\ExitCodesTest
 */

namespace Fixtures\TestStandard\Sniffs\ExitCodes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class NoIssuesSniff implements Sniff
{

    public function register()
    {
        return [T_ECHO];
    }

    public function process(File $phpcsFile, $stackPtr)
    {
        // Do nothing.
    }
}
