<?php
/**
 * Test handling of the <include-pattern> element.
 *
 * @copyright 2024 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 */

namespace PHP_CodeSniffer\Tests\Core\Ruleset;

use PHP_CodeSniffer\Ruleset;
use PHP_CodeSniffer\Tests\ConfigDouble;

/**
 * Test handling of the <include-pattern> element.
 *
 * @covers \PHP_CodeSniffer\Ruleset::processRule
 */
final class ProcessRuleIncludePatternTest extends AbstractRulesetTestCase
{

    /**
     * The Ruleset object.
     *
     * @var \PHP_CodeSniffer\Ruleset
     */
    private static $ruleset;


    /**
     * Initialize the config and ruleset objects for this test.
     *
     * @before
     *
     * @return void
     */
    protected function initializeConfigAndRuleset()
    {
        if (isset(self::$ruleset) === false) {
            // Set up the ruleset.
            $standard      = __DIR__.'/ProcessRuleIncludePatternTest.xml';
            $config        = new ConfigDouble(["--standard=$standard"]);
            self::$ruleset = new Ruleset($config);
        }

    }//end initializeConfigAndRuleset()


    /**
     * Verify that <include-patterns> are set.
     *
     * @return void
     */
    public function testProcessRuleShouldSetIncludePatterns()
    {
        $expectedPatterns = [];
        $this->assertSame($expectedPatterns, self::$ruleset->includePatterns);

    }//end testProcessRuleShouldSetIncludePatterns()


}//end class
