<?php
declare(strict_types = 1);
namespace Regal;

class TemplateEngine {
    private string $templateDir;
    private string $outputDir;

    private array $sources;
    private array $instances;
    private TemplateRenderer $renderer;

    public function __construct(string $templateDir, string $outputDir) {
        $this->templateDir = Util::normalizeDirectory($templateDir);
        $this->outputDir = Util::normalizeDirectory($outputDir);
        $this->sources = [];
        $this->instances = [];
        
        if (!is_dir($outputDir)) {
            mkdir($outputDir);
        }

        $this->renderer = new TemplateRenderer($this);
    }

    /**
     * Generate an instance ID from a template path and optional property array
     * 
     * @param string $templatePath Instance template path
     * @param ?array $properties Key-value properties
     * @return int ID unique to this combination of template path and properties
     */
    public function genInstanceId(string $templatePath, ?array $properties = null): int {
        $str = trim(Util::removeExtension($templatePath));
        if (!empty($properties)) {
            foreach ($properties as $prop => $val) {
                $v = is_string($val) ? trim($val) : $val;
                if (is_string($prop)) {
                    $str .= trim($prop) . "=>$v";
                } else {
                    $str .= "$prop=>" . $v;
                }
            }
        }
        return crc32($str);
    }

    /**
     * Get the TemplateSource associated with the given template path
     * 
     * @param string $templatePath Template path
     * @return ?TemplateSource Template source or null if it couldn't be loaded
     */
    public function getSource(string $templatePath): ?TemplateSource {
        if (!isset($this->sources[$templatePath])) {
            $filePath = Util::resolveTemplatePath($templatePath, $this->templateDir);
            $src = new TemplateSource($filePath, $templatePath);
            $this->sources[$templatePath] = $src;
            return $src;
        }
        return $this->sources[$templatePath];
    }

    /**
     * Create a TemplateInstance for the given template path and properties
     * 
     * @param string $templatePath Template path
     * @param ?array $properties Key-value properties
     * @return ?TemplateInstance Instance or null if it couldn't be created
     */
    public function getInstance(string $templatePath, ?array $properties = null): ?TemplateInstance {
        if (empty($templatePath)) {
            return null;
        }

        $instanceId = $this->genInstanceId($templatePath, $properties);
        
        if (!isset($this->instances[$instanceId])) {
            $source = $this->getSource($templatePath);
            if (is_null($source)) {
                return null;
            }
            $instance = new TemplateInstance($instanceId, $source, $properties);
            $this->instances[$instanceId] = $instance;
            return $instance;
        }
        return $this->instances[$instanceId];
    }

    /**
     * Get an existing template instance by its ID
     * 
     * @param int $instanceId
     * @return ?TemplateInstance
     */
    public function getInstanceById(int $instanceId): ?TemplateInstance {
        return $this->instances[$instanceId] ?? null;
    }

    /**
     * Render the given template as a document and write it to the output path
     * 
     * @param string $document Template path to document
     * @param ?string $output Output filename (Optional)
     * @return string|false Returns rendered HTML if successful or false if an error occurred
     */
    public function renderDocument(string $document, ?string $output = null): string|false {
        $instance = $this->getInstance($document);
        if ($instance !== null) {
            $dependencies = $this->renderer->renderInstance($instance);
            $docStyle = $instance->getStyle();
            $styles = [];

            /* TODO: Smarter CSS handling (possibly on a per-rule basis? :^) ) */
            foreach ($dependencies as $id) {
                $dep = $this->getInstanceById($id);
                $styles[] = $dep->getStyle();
            }
            foreach(array_unique($styles, SORT_STRING) as $style) {
                $docStyle .= $style;
            }

            $docHtml = Util::trimHtml(Util::appendHead(<<<HTML
                <!DOCTYPE html>
                <html lang="en">
                {$instance->getHtml()}
                </html>
                HTML, "<style>$docStyle</style>"));
            file_put_contents($this->outputDir . $document . ".html", $docHtml);
            return $docHtml;
        }
        return false;
    }

}
