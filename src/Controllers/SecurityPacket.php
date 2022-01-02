<?php
namespace Lucinda\Project\Controllers;

use Lucinda\MVC\ConfigurationException;
use Lucinda\MVC\Response\HttpStatus;
use Lucinda\STDERR\Controller;

/**
 * STDERR MVC controller running whenever a Lucinda\Framework\SecurityPacket is thrown during STDOUT phase.
 */
class SecurityPacket extends Controller
{
    /**
     * @var SecurityPacket
     */
    private \Throwable $exception;

    /**
     * {@inheritDoc}
     * @throws ConfigurationException
     * @see \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        $this->exception = $this->request->getException();
        $this->setResponseStatus();
        $this->setResponseBody();
    }

    /**
     * Sets response HTTP status code according to outcome of security validation
     */
    private function setResponseStatus(): void
    {
        switch ($this->exception->getStatus()) {
            case "unauthorized":
                $this->response->setStatus(HttpStatus::UNAUTHORIZED);
                break;
            case "forbidden":
                $this->response->setStatus(HttpStatus::FORBIDDEN);
                break;
            case "not_found":
                $this->response->setStatus(HttpStatus::NOT_FOUND);
                break;
            default:
                $this->response->setStatus(HttpStatus::OK);
                break;
        }
    }

    /**
     * Sets response body from view file or stream.
     * @throws ConfigurationException
     */
    private function setResponseBody(): void
    {
        // gets packet status
        $status = $this->exception->getStatus();

        // gets default format
        $defaultFormat = $this->application->getDefaultFormat();

        // sets response content
        if ($defaultFormat=="html") {
            $this->html($status);
        } else {
            $this->nonHtml();
        }
    }

    /**
     * Sets up HTML response
     *
     * @param string $status
     * @throws ConfigurationException
     */
    private function html(string $status): void
    {
        $redirect = (string) $this->application->getTag("application")["redirect"];
        $location = $this->exception->getCallback().($this->exception->getStatus()!="redirect" ? "?status=".$this->exception->getStatus() : "");
        if ($redirect) {
            if ($status == "unauthorized") {
                $location .= "&source=".urlencode($_SERVER["REQUEST_URI"]);
            } elseif ($status == "login_ok" && !empty($_GET["source"])) {
                $location = $_GET["source"];
            } elseif ($penalty = $this->exception->getTimePenalty()) {
                $location .= "&wait=".$penalty;
            }
            $this->response::redirect($location, false, true);
        } else {
            $viewsPath = (string) $this->application->getTag("templating")["templates_path"];
            switch ($status) {
                case "unauthorized":
                    $this->response->view()->setFile($viewsPath."/401");
                    break;
                case "forbidden":
                    $this->response->view()->setFile($viewsPath."/403");
                    break;
                case "not_found":
                    $this->response->view()->setFile($viewsPath."/404");
                    break;
                default:
                    $this->response::redirect($location, false, true);
                    break;
            }
        }
    }

    /**
     * Sets up non-HTML response
     */
    private function nonHtml(): void
    {
        $view = $this->response->view();
        $view["status"] = $this->exception->getStatus();
        $view["callback"] = $this->exception->getCallback();
        $view["token"] = $this->exception->getAccessToken();
        $view["penalty"] = $this->exception->getTimePenalty();
    }
}
