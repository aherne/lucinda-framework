<?php
namespace Lucinda\Project\Controllers;

use Lucinda\STDERR\Controller;

/**
 * STDERR MVC controller running whenever a Lucinda\Framework\SecurityPacket is thrown during STDOUT phase.
 */
class SecurityPacket extends Controller
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        $this->setResponseStatus();
        $this->setResponseBody();
    }
    
    /**
     * Sets response HTTP status code according to outcome of security validation
     */
    private function setResponseStatus(): void
    {
        switch ($this->request->getException()->getStatus()) {
            case "unauthorized":
                $this->response->setStatus(401);
                break;
            case "forbidden":
                $this->response->setStatus(403);
                break;
            case "not_found":
                $this->response->setStatus(404);
                break;
            default:
                $this->response->setStatus(200);
                break;
        }
    }
    
    /**
     * Sets response body from view file or stream.
     */
    private function setResponseBody(): void
    {
        // gets packet status
        $status = $this->request->getException()->getStatus();
        
        // gets wrapped exception
        $exception = $this->request->getException();
        
        // gets default format
        $defaultFormat = $this->application->getDefaultFormat();
        
        // sets response content
        if ($defaultFormat=="html") {
            $redirect = (string) $this->application->getTag("application")["redirect"];
            $location = $exception->getCallback().($exception->getStatus()!="redirect"?"?status=".$exception->getStatus():"");
            if ($redirect) {
                if ($status == "unauthorized") {
                    $location .= "&source=".urlencode($_SERVER["REQUEST_URI"]);
                } elseif ($status == "login_ok" && !empty($_GET["source"])) {
                    $location = $_GET["source"];
                } elseif ($penalty = $this->request->getException()->getTimePenalty()) {
                    $location .= "&wait=".$penalty;
                }
                $this->response::redirect($location, false, true);
            } else {
                switch ($status) {
                    case "unauthorized":
                        $this->response->view()->setFile($this->application->getViewsPath()."/401");
                        break;
                    case "forbidden":
                        $this->response->view()->setFile($this->application->getViewsPath()."/403");
                        break;
                    case "not_found":
                        $this->response->view()->setFile($this->application->getViewsPath()."/404");
                        break;
                    default:
                        $this->response::redirect($location, false, true);
                        break;
                }
            }
        } else {
            $view = $this->response->view();
            $view["status"] = $exception->getStatus();
            $view["callback"] = $exception->getCallback();
            $view["token"] = $exception->getAccessToken();
            $view["penalty"] = $exception->getTimePenalty();
        }
    }
}
