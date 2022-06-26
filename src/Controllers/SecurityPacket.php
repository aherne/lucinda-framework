<?php

namespace Lucinda\Project\Controllers;

use Lucinda\MVC\ConfigurationException;
use Lucinda\MVC\Response\HttpStatus;
use Lucinda\MVC\Response\Redirect;
use Lucinda\STDERR\Controller;
use Lucinda\STDOUT\Request;
use Lucinda\STDOUT\Request\URI;

/**
 * STDERR MVC controller running whenever a Lucinda\Framework\SecurityPacket is thrown during STDOUT phase.
 */
class SecurityPacket extends Controller
{
    /**
     * @var \Lucinda\WebSecurity\SecurityPacket
     */
    private \Throwable $exception;

    /**
     * {@inheritDoc}
     *
     * @throws ConfigurationException
     * @see    \Lucinda\MVC\Runnable::run()
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
     *
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
     * @param  string $status
     * @throws ConfigurationException
     */
    private function html(string $status): void
    {
        if ($redirect = $this->getRedirectURL($status)) {
            $object = new Redirect($redirect);
            $object->setPermanent(false);
            $object->setPreventCaching(true);
            $object->run();
        } else {
            $this->response->view()->setFile($this->getViewPath($status));
        }
    }

    /**
     * Gets redirection link, if any
     *
     * @param  string $status
     * @return string|null
     * @throws ConfigurationException
     */
    private function getRedirectURL(string $status): ?string
    {
        $redirect = (string) $this->application->getTag("application")["redirect"];
        $location = $this->exception->getCallback();
        if ($this->exception->getStatus()!="redirect") {
            $location .= "?status=".$this->exception->getStatus();
        }
        $uri = new Request();
        if ($redirect) {
            if ($status == "unauthorized") {
                $location .= "&source=".urlencode($uri->getURI()->getPage());
            } elseif ($status == "login_ok" && $uri->parameters("source")) {
                $location = $uri->parameters("source");
            } elseif ($penalty = $this->exception->getTimePenalty()) {
                $location .= "&wait=".$penalty;
            }
            return $location;
        } else {
            if (!in_array($status, ["unauthorized", "forbidden", "not_found"])) {
                return $location;
            } else {
                return null;
            }
        }
    }

    /**
     * Gets absolute path to http-status specific view
     *
     * @param  string $status
     * @return string
     * @throws ConfigurationException
     */
    private function getViewPath(string $status): string
    {
        $viewsPath = (string) $this->application->getTag("templating")["templates_path"];
        switch ($status) {
        case "unauthorized":
            return $viewsPath."/401";
        case "forbidden":
            return $viewsPath."/403";
        default:
            return $viewsPath."/404";
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
