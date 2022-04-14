<?php
declare(strict_types = 1);
namespace Regal;

class Util {

    /**
     * Append text to the end of the head element in an HTML string
     * @param string $html
     * @param string $append
     * @return string
     */
    public static function appendHead(string $html, string $append): string {
        return preg_replace("/<\/head>/", $append . PHP_EOL . "</head>", $html);
    }


    public static function domChildrenToString(\DOMNode $node): string {
        $doc = new Document;
        foreach ($node->childNodes as $child) {
            switch ($child->nodeType) {
                case XML_TEXT_NODE:
                case XML_ELEMENT_NODE:
                    $doc->appendChild($doc->importNode($child, true));
                default:
                    break;
            }
        }
        return $doc->saveHTML();
    }

    /**
     * Remove <html></html> tags from a string
     * @param string $html
     * @return string
     */
    public static function unwrapHtml(string $html): string {
        return preg_replace("/<\/?html>/S", "", $html);
    }

    /**
     * Remove superfluous whitespace from HTML
     * @param string $html
     * @return string
     */
    public static function trimHtml(string $html, bool $aggressive = false): string {
        if ($aggressive) {
            return preg_replace("/^\s*|\s*$/Sm", "", $html);
        }
        return preg_replace("/^\s*|[ \t]*$/Sm", "", $html);
    }

    /**
     * Remove superfluous whitespace from CSS
     * @param string $css
     * @return string
     */
    public static function trimCss(string $css): string {
        return preg_replace("/^\s*|\n|\s*$/Sm", "", $css);
    }

    public static function normalizeDirectory(string $path): string {
        $len = strlen($path);
        if ($path[$len - 1] === '/' || $path[$len - 1] === '\\') {
            return $path;
        }
        return $path . DIRECTORY_SEPARATOR;
    }

    public static function resolveTemplatePath(string $templatePath, string $templateDir): string {
        $path = self::normalizeDirectory($templateDir) . $templatePath;
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        if (empty($ext)) {
            $path = $path . ".html";
        }
        return $path;
    }

    public static function removeExtension(string $path): string {
        return substr($path, 0, strrpos($path, ".") ?: null);
    }

}
