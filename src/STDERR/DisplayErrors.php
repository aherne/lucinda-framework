<?php

namespace Lucinda\Project\STDERR;

use Lucinda\STDERR\DisplayErrors as STDERRDisplayErrors;

/**
 * Defines whether application error responses include diagnostics
 *
 * This development policy enables exception details for every request.
 * Applications should adapt the policy before exposing diagnostics publicly.
 */
final class DisplayErrors implements STDERRDisplayErrors
{
    /**
     * Gets the application policy for displaying exception diagnostics
     *
     * @return bool True to include debug details in rendered error views
     */
    public function shouldDisplayErrors(): bool
    {
        return true;
    }
}
