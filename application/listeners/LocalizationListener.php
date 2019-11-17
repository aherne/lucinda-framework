<?php
require("vendor/lucinda/framework-engine/src/internationalization/LocalizationBinder.php");
require("application/models/internationalization/Translate.php");

/**
 * Binds STDOUT MVC with Internationalization API and contents of 'internationalization' tag @ configuration.xml
 * in order to be able to render a view according to client locale.
 */
class LocalizationListener extends \Lucinda\MVC\STDOUT\RequestListener
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\STDOUT\Runnable::run()
     */
    public function run()
    {
        new Lucinda\Framework\LocalizationBinder($this->application, $this->request);
    }
}
