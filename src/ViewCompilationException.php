<?php

namespace Lucinda\Project;

/**
 * Exception thrown when ViewLanguage compilation fails: file is ready, but php exits with error while parsing it
 */
class ViewCompilationException extends \Exception
{
    private $templateTrace = [];

    /**
     * Detects template trace based on file and line @ compilation file where error occurred
     *
     * @param string $file
     * @param int $line
     * @return void
     */
    public function setTemplateTrace(string $file, int $line): void
    {
        $results = [];

        $handle = fopen($file, "r");
        $nr = 1;
        while (!feof($handle)) {
            $lineBody = fgets($handle);
            preg_match("/<!-- VL:(START|END):\s*(.*?)\s*-->/", $lineBody, $matches);
            if (!empty($matches)) {
                $results[$matches[1]][$matches[2]][] = $matches[2];
            }
            if ($nr === $line) {
                break;
            }
            $nr++;
        }
        fclose($handle);

        if (!empty($results["START"])) {
            foreach ($results["START"] as $file => $versions) {
                if (!isset($results["END"][$file]) || count($versions) != count($results["END"][$file])) {
                    $this->templateTrace[] = str_replace(dirname(__DIR__, 2), "", $file);
                }
            }
        }
    }

    /**
     * Gets template trace detected
     *
     * @return array
     */
    public function getTemplateTrace(): array
    {
        return $this->templateTrace;
    }
}