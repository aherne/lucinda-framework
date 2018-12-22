<?php
require_once("vendor/lucinda/framework-engine/src/validation/ValidationBinder.php");
/**
 * Binds STDOUT MVC API with Parameters Validation API based on contents of <routes> stdout.xml tag for request/path parameters validation.
 *
 * Sets attribute:
 * - validation_results: (Lucinda\ParameterValidator\ResultsList) stores validation results for each parameter
 */
class SecurityListener extends Lucinda\MVC\STDOUT\RequestListener {
    /**
     * {@inheritDoc}
     * @see Lucinda\MVC\STDOUT\Runnable::run()
     */
    public function run() {
        $binder = new Lucinda\Framework\ValidationBinder($this->request);
        $this->request->attributes()->set("validation_results", $binder->getResults());
    }
}