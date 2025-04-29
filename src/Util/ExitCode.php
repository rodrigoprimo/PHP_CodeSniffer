<?php
/**
 * Exit codes.
 *
 * Note: The "missing" exit codes 8 and 32 are reserved for future use.
 *
 * @author    Juliette Reinders Folmer <phpcs_nospam@adviesenzo.nl>
 * @copyright 2025 PHPCSStandards and contributors
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 */

namespace PHP_CodeSniffer\Util;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Reporter;

final class ExitCode
{

    /**
     * No issues were found in the code under scan.
     *
     * @var int
     */
    public const OKAY = 0;

    /**
     * Exit code to indicate auto-fixable issues were found.
     *
     * @var int
     */
    public const FIXABLE = 1;

    /**
     * Exit code to indicate issues were found, which are not auto-fixable.
     *
     * @var int
     */
    public const NON_FIXABLE = 2;

    /**
     * [CBF only] Exit code to indicate a file failed to fix.
     *
     * Typically, this is caused by a fixer conflict between sniffs.
     *
     * @var int
     */
    public const FAILED_TO_FIX = 4;

    /**
     * Exit code to indicate PHP_CodeSniffer ran into a problem while processing the request.
     *
     * Examples of when this code should be used:
     * - Invalid CLI flag used.
     * - Blocking errors in the ruleset file(s).
     * - A dependency required to generate a report not being available, like git for the gitblame report.
     *
     * @var int
     */
    public const PROCESS_ERROR = 16;

    /**
     * Exit code to indicate the requirements to run PHP_CodeSniffer are not met.
     *
     * This exit code is here purely for documentation purposes.
     * This exit code should only be used in the requirements check (`requirements.php` file), but that
     * file can't use the constant as it would block _this_ file from using modern PHP.
     *
     * {@internal The code in the requirements.php file and below should always stay in sync!}
     *
     * @var int
     */
    public const REQUIREMENTS_NOT_MET = 64;


    /**
     * @TODO: description + figure out if this is needed and whether it should set or return
     */
    public function setFlag(int $flag)
    {
        $this->flags |= $flag;

    }//end setFlag()


    /**
     * @TODO: description + figure out if this is needed and whether it should set or return
     */
    public function unsetFlag(int $flag)
    {
        $this->flags &= ~$flag;

    }//end unsetFlag()


    /**
     * Calculate the exit code based on the results of the run as recorded in the Reporter object.
     *
     * @param \PHP_CodeSniffer\Reporter $reporter Reporter object for the run.
     * @param string                    $cmd      The current command. Either 'phpcs' or 'phpcbf'.
     *
     * @return int
     */
    public static function calculate(Reporter $reporter, $cmd)
    {

/*
TO DECIDE:
Does this need the $cmd parameter ?
Or should this check `PHP_CODESNIFFER_CBF === true` ?
*/

// TODO: This needs work

var_export([
  'files' => $reporter->totalFiles,
  'errors' => $reporter->totalErrors,
  'warnings' => $reporter->totalWarnings,
  'fixable' => $reporter->totalFixable,
//  'fixableErrors' => $reporter->totalFixableErrors,
//  'fixableWarnings' => $reporter->totalFixableWarnings,
  'fixed' => $reporter->totalFixed,
//  'fixedErrors' => $reporter->totalFixedErrors,
//  'fixedWarnings' => $reporter->totalFixedWarnings,
]);

/*
        $ignoreWarnings       = Config::getConfigData('ignore_warnings_on_exit') ?? false;
        $ignoreErrors         = Config::getConfigData('ignore_errors_on_exit') ?? false;
        $ignoreNonAutofixable = Config::getConfigData('ignore_non_auto_fixable_on_exit') ?? false;

        $return = ($reporter->totalErrors + $reporter->totalWarnings);
        if ($ignoreErrors === true) {
            $return -= $reporter->totalErrors;
        }

        if ($ignoreWarnings === true) {
            $return -= $reporter->totalWarnings;
        }

        return $return;
*/


        $ignoreNonAutofixable  = (bool) (Config::getConfigData('ignore_non_auto_fixable_on_exit') ?? false);
        $totalRelevantErrors   = $reporter->totalErrors;
        $totalRelevantWarnings = $reporter->totalWarnings;

        if ($ignoreNonAutofixable === true) {
            $totalRelevantErrors   = $reporter->totalFixableErrors;
            $totalRelevantWarnings = $reporter->totalFixableWarnings;
        }

        $ignoreWarnings       = (bool) (Config::getConfigData('ignore_warnings_on_exit') ?? false);
        $ignoreErrors         = (bool) (Config::getConfigData('ignore_errors_on_exit') ?? false);

        $totalRelevantIssues        = 0;
        $totalRelevantFixableIssues = 0;
        $totalRelevantFixedIssues   = 0;

        if ($ignoreErrors === false) {
            $totalRelevantIssues        += $totalRelevantErrors;
            $totalRelevantFixableIssues += $reporter->totalFixableErrors;
            $totalRelevantFixedIssues   += $reporter->totalFixedErrors;
        }
        if ($ignoreWarnings === false) {
            $totalRelevantIssues        += $totalRelevantWarnings;
            $totalRelevantFixableIssues += $reporter->totalFixableWarnings;
            $totalRelevantFixedIssues   += $reporter->totalFixedWarnings;
        }

        $exitCode = self::OKAY;

        if ($cmd === 'phpcbf'
            && ($reporter->totalFixableErrors + $reporter->totalFixableWarnings) > 0
        ) {
            // Something failed to fix.
            $exitCode |= self::FAILED_TO_FIX;
        }

        // Are there issues which PHPCBF could fix ?
        if ($totalRelevantFixableIssues > 0) {
            $exitCode |= self::FIXABLE;
        }

        // Are there issues which PHPCBF cannot fix ?
// Note: if there is a fixer conflict, this may not work correctly as $totalRelevantFixedIssues may be wrong ?
        if (($totalRelevantIssues - $totalRelevantFixableIssues - $totalRelevantFixedIssues) > 0) {
            $exitCode |= self::NON_FIXABLE;
        }

        return $exitCode;


////////////////////////////////

        if ($numErrors === 0) {
            // No issues found.
            return ExitCode::OKAY;
        } else if ($reporter->totalFixable === $numErrors) {
// This is imprecise - Fixable contains total fixable errors/warnings, but we may be ignoring errors/warnings
            // Issues found, all of which can be fixed by PHPCBF.
            return ExitCode::FIXABLE;
        } else if ($reporter->totalFixable === 0) {
            // Issues found, but none of them can be fixed by PHPCBF.
            return ExitCode::NON_FIXABLE;
        } else {
            // Issues found, and some can be fixed by PHPCBF.
            return (ExitCode::FIXABLE | ExitCode::NON_FIXABLE);
        }

////////////////////////////////

        $totalIssues      = ($reporter->totalErrors + $reporter->totalWarnings);
        $nonFixableIssues = ($totalIssues - $reporter->totalFixable - $reporter->totalFixed);

        if ($reporter->totalFixed === 0) {
            // Nothing was fixed by PHPCBF.
            if ($reporter->totalFixable === 0) {
                // Nothing found that could be fixed.
                if ($nonFixableIssues > 0) {
                    return ExitCode::NON_FIXABLE;
                } else {
                    return ExitCode::OKAY;
                }
            } else {
                // Something failed to fix.
                $exitCode = (ExitCode::FAILED_TO_FIX | ExitCode::FIXABLE);
                if ($nonFixableIssues > 0) {
                    return ($exitCode | ExitCode::NON_FIXABLE);
                } else {
                    return $exitCode;
                }
            }
        }

        if ($reporter->totalFixable === 0) {
            // PHPCBF fixed all fixable errors.
            // Check if there are non-fixable issues remaining.
            if ($nonFixableIssues > 0) {
                return ExitCode::NON_FIXABLE;
            } else {
                return ExitCode::OKAY;
            }
        }

        // PHPCBF fixed some fixable errors, but others failed to fix.
        $exitCode = (ExitCode::FAILED_TO_FIX | ExitCode::FIXABLE);
        if ($nonFixableIssues > 0) {
            return ($exitCode | ExitCode::NON_FIXABLE);
        } else {
            return $exitCode;
        }
    }


}//end class
