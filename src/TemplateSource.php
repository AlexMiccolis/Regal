<?php
declare(strict_types = 1);
namespace Regal;

class TemplateSource {
    public readonly string $filePath;
    public readonly string $templatePath;
    public readonly string $html;
    public readonly string $style;

    private function getTagContent(string $fileContent, string $tagName): string {
        $tagContent = "";
        $matches = [];
        if (preg_match_all("/<".$tagName."[^>]*>([\s\S]*?(?=<\/".$tagName.">))<\/".$tagName.">/",
            $fileContent, $matches, PREG_SET_ORDER)) {
            foreach($matches as $match) {
                $tagContent .= $match[1];
            }
        }
        return $tagContent;
    }

    public function __construct(string $filePath, string $templatePath) {
        if (!(file_exists($filePath) && ($content = file_get_contents($filePath)))) {
            echo "Error: Failed to open template source '$filePath'" . PHP_EOL;
            exit(1);
        }
        $content = Util::trimHtml($content);
        $this->filePath = $filePath;
        $this->templatePath = $templatePath;
        $this->html = $this->getTagContent($content, "template");
        $this->style = Util::trimCss($this->getTagContent($content, "style"));
    }
}
