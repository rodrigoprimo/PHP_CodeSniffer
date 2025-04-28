<?php
/**
 * Tests the exit codes generated in the runPHPCS() and runPHPCBF() functions.
 *
 * @copyright 2025 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 */

namespace PHP_CodeSniffer\Tests\Core\Runner;

use PHP_CodeSniffer\Runner;
use PHP_CodeSniffer\Tests\Core\Runner\AbstractRunnerTestCase;
use PHP_CodeSniffer\Tests\Core\StatusWriterTestHelper;

/**
 * Tests the exit codes generated in the runPHPCS() and runPHPCBF() functions.
 *
 * @covers \PHP_CodeSniffer\Runner::runPHPCS
 * @covers \PHP_CodeSniffer\Runner::runPHPCBF
 */
final class ExitCodesTest extends AbstractRunnerTestCase
{
    // Using the Helper to catch output send to STDERR. For this test, we don't care about the output.
    use StatusWriterTestHelper;

    private const SOURCE_DIR = __DIR__.'/Fixtures/ExitCodesTest/';


    /**
     * Clean up after the tests.
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        $globPattern = self::SOURCE_DIR.'/*.inc.fixed';
        $globPattern = str_replace('/', DIRECTORY_SEPARATOR, $globPattern);

        $fixedFiles = glob($globPattern, GLOB_NOESCAPE);

        foreach ($fixedFiles as $file) {
            @unlink($file);
        }

    }//end tearDownAfterClass()


    /**
     * Verify generated exit codes (PHPCS).
     *
     * @param array<string> $extraArgs Any extra arguments to pass on the command line.
     * @param int           $expected  The expected exit code (return value).
     *
     * @dataProvider dataPhpcs
     *
     * @return void
     */
    public function testPhpcsNoParallel($extraArgs, $expected)
    {
        $extraArgs[] = self::SOURCE_DIR.'mix-errors-warnings.inc';

        $this->runPhpcsAndCheckExitCode($extraArgs, $expected);

    }//end testPhpcsNoParallel()


    /**
     * Verify generated exit codes (PHPCS) when using parallel processing.
     *
     * @param array<string> $extraArgs Any extra arguments to pass on the command line.
     * @param int           $expected  The expected exit code (return value).
     *
     * @dataProvider dataPhpcs
     *
     * @return void
     */
    public function testPhpcsParallel($extraArgs, $expected)
    {
        // Deliberately using `parallel=3` to scan 5 files to make sure that the results get recorded correctly.
        $extraArgs[] = self::SOURCE_DIR;
        $extraArgs[] = '--parallel=3';

        $this->runPhpcsAndCheckExitCode($extraArgs, $expected);

    }//end testPhpcsParallel()


    /**
     * Verify generated exit codes (PHPCS) when caching of results is used.
     *
     * @param array<string> $extraArgs Any extra arguments to pass on the command line.
     * @param int           $expected  The expected exit code (return value).
     *
     * @dataProvider dataPhpcs
     *
     * @return void
     */
    public function testPhpcsWithCache($extraArgs, $expected)
    {
        $extraArgs[] = self::SOURCE_DIR.'mix-errors-warnings.inc';
        $extraArgs[] = '--cache';

        // First run with these arguments to create the cache.
        $this->runPhpcsAndCheckExitCode($extraArgs, $expected);

        // Second run to verify the exit code is the same when the results are taking from the cache.
        $this->runPhpcsAndCheckExitCode($extraArgs, $expected);

    }//end testPhpcsWithCache()


    /**
     * Test Helper: run PHPCS and verify the generated exit code.
     *
     * @param array<string> $extraArgs Any extra arguments to pass on the command line.
     * @param int           $expected  The expected exit code (return value).
     *
     * @dataProvider dataPhpcs
     *
     * @return void
     */
    public function runPhpcsAndCheckExitCode($extraArgs, $expected)
    {
        if (PHP_CODESNIFFER_CBF === true) {
            $this->markTestSkipped('This test needs CS mode to run');
        }

        $this->setupTest('phpcs', $extraArgs);

        // Catch & discard the screen output. That's not what we're interested in for this test.
        ob_start();
        $runner = new Runner();
        $actual = $runner->runPHPCS();
        ob_end_clean();

        $this->assertSame($expected, $actual);

    }//end runPhpcsAndCheckExitCode()


    /**
     * Data provider.
     *
     * @return array<string, array<string, int|array<string>>>
     */
    public static function dataPhpcs()
    {
        return [
            'No issues'                                                              => [
                'extraArgs' => ['--sniffs=TestStandard.ExitCodes.NoIssues'],
                'expected'  => 0,
            ],
            'Only auto-fixable issues'                                               => [
                'extraArgs' => ['--sniffs=TestStandard.ExitCodes.FixableError,TestStandard.ExitCodes.FixableWarning'],
                'expected'  => 1,
            ],
            'Only non-fixable issues'                                                => [
                'extraArgs' => ['--sniffs=TestStandard.ExitCodes.Error,TestStandard.ExitCodes.Warning'],
                'expected'  => 2,
            ],
            'Both auto-fixable + non-fixable issues'                                 => [
                'extraArgs' => [],
                'expected'  => 3,
            ],

            // In both the below cases, we still have both fixable and non-fixable issues, so exit code = 3.
            'Only errors'                                                            => [
                'extraArgs' => ['--exclude=TestStandard.ExitCodes.FixableWarning,TestStandard.ExitCodes.Warning'],
                'expected'  => 3,
            ],
            'Only warnings'                                                          => [
                'extraArgs' => ['--exclude=TestStandard.ExitCodes.FixableError,TestStandard.ExitCodes.Error'],
                'expected'  => 3,
            ],

            // In both the below cases, we still have 1 fixable and 1 non-fixable issue which we need to take into account, so exit code = 3.
            'Mix of errors and warnings, ignoring warnings for exit code'            => [
                'extraArgs' => ['--runtime-set ignore_warnings_on_exit 1'],
                'expected'  => 3,
            ],
            'Mix of errors and warnings, ignoring errors for exit code'              => [
                'extraArgs' => ['--runtime-set ignore_warnings_on_exit 1'],
                'expected'  => 3,
            ],

            'Fixable error and non-fixable warning, ignoring errors for exit code'   => [
                'extraArgs' => [
                    '--sniffs=TestStandard.ExitCodes.FixableError,TestStandard.ExitCodes.Warning',
                    '--runtime-set ignore_errors_on_exit 1',
                ],
                'expected'  => 2,
            ],
            'Non-fixable error and fixable warning, ignoring errors for exit code'   => [
                'extraArgs' => [
                    '--sniffs=TestStandard.ExitCodes.Error,TestStandard.ExitCodes.FixableWarning',
                    '--runtime-set ignore_errors_on_exit 1',
                ],
                'expected'  => 1,
            ],

            'Fixable error and non-fixable warning, ignoring warnings for exit code' => [
                'extraArgs' => [
                    '--sniffs=TestStandard.ExitCodes.FixableError,TestStandard.ExitCodes.Warning',
                    '--runtime-set ignore_errors_on_exit 1',
                ],
                'expected'  => 1,
            ],
            'Non-fixable error and fixable warning, ignoring warnings for exit code' => [
                'extraArgs' => [
                    '--sniffs=TestStandard.ExitCodes.Error,TestStandard.ExitCodes.FixableWarning',
                    '--runtime-set ignore_errors_on_exit 1',
                ],
                'expected'  => 2,
            ],
            'Process error'                                                          => [
                'extraArgs' => ['--bootstrap=filedoesnotexist.php'],
                'expected'  => 16,
            ],
        ];

    }//end dataPhpcs()


    /**
     * Verify generated exit codes (PHPCBF).
     *
     * @param array<string> $extraArgs Any extra arguments to pass on the command line.
     * @param int           $expected  The expected exit code (return value).
     *
     * @dataProvider dataPhpcbf
     * @group        CBF
     *
     * @return void
     */
    public function testPhpcbfNoParallel($extraArgs, $expected)
    {
        $extraArgs[] = self::SOURCE_DIR.'mix-errors-warnings.inc';

        $this->runPhpcbfAndCheckExitCode($extraArgs, $expected);

    }//end testPhpcbfNoParallel()


    /**
     * Verify generated exit codes (PHPCBF) when using parallel processing.
     *
     * @param array<string> $extraArgs Any extra arguments to pass on the command line.
     * @param int           $expected  The expected exit code (return value).
     *
     * @dataProvider dataPhpcbf
     * @group        CBF
     *
     * @return void
     */
    public function testPhpcbfParallel($extraArgs, $expected)
    {
        // Deliberately using `parallel=3` to scan 5 files to make sure that the results get recorded correctly.
        $extraArgs[] = self::SOURCE_DIR;
        $extraArgs[] = '--parallel=3';

        $this->runPhpcbfAndCheckExitCode($extraArgs, $expected);

    }//end testPhpcbfParallel()


    /**
     * Test Helper: run PHPCBF and verify the generated exit code.
     *
     * @param array<string> $extraArgs Any extra arguments to pass on the command line.
     * @param int           $expected  The expected exit code (return value).
     *
     * @dataProvider dataPhpcs
     *
     * @return void
     */
    public function runPhpcbfAndCheckExitCode($extraArgs, $expected)
    {
        if (PHP_CODESNIFFER_CBF === false) {
            $this->markTestSkipped('This test needs CBF mode to run');
        }

        $this->setupTest('phpcbf', $extraArgs);

        // Catch & discard the screen output. That's not what we're interested in for this test.
        ob_start();
        $runner = new Runner();
        $actual = $runner->runPHPCBF();
        ob_end_clean();

        $this->assertSame($expected, $actual);

    }//end runPhpcbfAndCheckExitCode()


    /**
     * Data provider.
     *
     * @return array<string, array<string, int|array<string>>>
     */
    public static function dataPhpcbf()
    {
        return [
            'No issues'                                                                                      => [
                'extraArgs' => ['--sniffs=TestStandard.ExitCodes.NoIssues'],
                'expected'  => 0,
            ],
            'Fixed all auto-fixable issues, no issues left'                                                  => [
                'extraArgs' => ['--sniffs=TestStandard.ExitCodes.FixableError,TestStandard.ExitCodes.FixableWarning'],
                'expected'  => 0,
            ],
            'Fixed all auto-fixable issues, has non-autofixable issues left'                                 => [
                'extraArgs' => ['--exclude=TestStandard.ExitCodes.FailToFix'],
                'expected'  => 2,
            ],
            'Fixed all auto-fixable issues, has non-autofixable issues left, ignoring those for exit code'   => [
                'extraArgs' => [
                    '--exclude=TestStandard.ExitCodes.FailToFix',
                    '--runtime-set ignore_non_auto_fixable_on_exit 1',
                ],
                'expected'  => 0,
            ],
            'Failed to fix, only fixable issues remaining'                                                   => [
                'extraArgs' => ['--exclude=TestStandard.ExitCodes.Error,TestStandard.ExitCodes.Warning'],
                'expected'  => 5,
            ],
            'Failed to fix, both fixable + non-fixable issues remaining'                                     => [
                'extraArgs' => [],
                'expected'  => 7,
            ],
            'Failed to fix, both fixable + non-fixable issues remaining, ignoring non-fixable for exit code' => [
                'extraArgs' => ['--runtime-set ignore_non_auto_fixable_on_exit 1'],
                'expected'  => 5,
            ],

            // In both the below cases, we still have 1 non-fixable issue which we need to take into account, so exit code = 2.
            'Only errors'                                                                                    => [
                'extraArgs' => ['--exclude=TestStandard.ExitCodes.FailToFix,TestStandard.ExitCodes.FixableWarning,TestStandard.ExitCodes.Warning'],
                'expected'  => 2,
            ],
            'Only warnings'                                                                                  => [
                'extraArgs' => ['--exclude=TestStandard.ExitCodes.FailToFix,TestStandard.ExitCodes.FixableError,TestStandard.ExitCodes.Error'],
                'expected'  => 2,
            ],

            // In both the below cases, we still have 1 non-fixable issue which we need to take into account, so exit code = 2.
            'Mix of errors and warnings, ignoring warnings for exit code'                                    => [
                'extraArgs' => [
                    '--exclude=TestStandard.ExitCodes.FailToFix',
                    '--runtime-set ignore_warnings_on_exit 1',
                ],
                'expected'  => 2,
            ],
            'Mix of errors and warnings, ignoring errors for exit code'                                      => [
                'extraArgs' => [
                    '--exclude=TestStandard.ExitCodes.FailToFix',
                    '--runtime-set ignore_warnings_on_exit 1',
                ],
                'expected'  => 2,
            ],

            'Mix of fixable error and non-fixable warning, ignoring errors for exit code'                    => [
                'extraArgs' => [
                    '--sniffs=TestStandard.ExitCodes.FixableError,TestStandard.ExitCodes.Warning',
                    '--runtime-set ignore_errors_on_exit 1',
                ],
                'expected'  => 2,
            ],
            'Mix of non-fixable error and fixable warning, ignoring errors for exit code'                    => [
                'extraArgs' => [
                    '--sniffs=TestStandard.ExitCodes.Error,TestStandard.ExitCodes.FixableWarning',
                    '--runtime-set ignore_errors_on_exit 1',
                ],
                'expected'  => 2,
            ],

            'Mix of fixable error and non-fixable warning, ignoring warnings for exit code'                  => [
                'extraArgs' => [
                    '--sniffs=TestStandard.ExitCodes.FixableError,TestStandard.ExitCodes.Warning',
                    '--runtime-set ignore_errors_on_exit 1',
                ],
                'expected'  => 0,
            ],
            'Mix of non-fixable error and fixable warning, ignoring warnings for exit code'                  => [
                'extraArgs' => [
                    '--sniffs=TestStandard.ExitCodes.Error,TestStandard.ExitCodes.FixableWarning',
                    '--runtime-set ignore_errors_on_exit 1',
                ],
                'expected'  => 2,
            ],

            'Process error'                                                                                  => [
                'extraArgs' => ['--bootstrap=filedoesnotexist.php'],
                'expected'  => 16,
            ],
        ];

    }//end dataPhpcbf()


    /**
     * Helper method to prepare the test.
     *
     * @param string        $cmd       The command. Either 'phpcs' or 'phpcbf'.
     * @param array<string> $extraArgs Any extra arguments to pass on the command line.
     *
     * @return void
     */
    private function setupTest($cmd, $extraArgs)
    {
        $standard = __DIR__.'/ExitCodesTest.xml';

        $_SERVER['argv'] = [
            $cmd,
            "--standard=$standard",
            '--report-width=80',
            '--suffix=.fixed',
        ];

        foreach ($extraArgs as $arg) {
            $_SERVER['argv'][] = $arg;
        }

    }//end setupTest()


}//end class
