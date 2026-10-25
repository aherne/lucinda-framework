<?php

namespace Lucinda\Project\STDERR;

use Lucinda\STDERR\DisplayErrors as STDERRDisplayErrors;

final class DisplayErrors implements STDERRDisplayErrors
{
    public function shouldDisplayErrors(): bool
    {
        return true;
    }
}