<?php
declare(strict_types = 1);
namespace Regal;

class Document extends \DOMDocument {
    public const DEPTH_UNORDERED = 0;
    public const DEPTH_ASCENDING = 1;
    public const DEPTH_DESCENDING = 2;
    private const DOC_FLAGS = LIBXML_NOERROR | LIBXML_NOBLANKS
        | LIBXML_NOENT | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD;

    public function __construct() {
        parent::__construct("1.0", "utf-8");
        $this->preserveWhiteSpace = false;
    }

    public function loadHTML(string $source, int $options = self::DOC_FLAGS): bool|\DOMDocument {
        $result = parent::loadHTML("<html>$source</html>", $options);
        return is_bool($result) ? $result : true;
    }

    public function saveHTML(?\DOMNode $node = null): string|false {
        $html = parent::saveHTML($node);
        return Util::trimHtml(Util::unwrapHtml($html), false);
    }

    public function getElementsByTag(string $tag, int $depthOrder = self::DEPTH_UNORDERED): ?array {
        $elementList = parent::getElementsByTagName($tag);
        if ($elementList->length == 0) {
            return null;
        }
        
        $elements = iterator_to_array($elementList);
        if ($depthOrder == self::DEPTH_UNORDERED) {
            return $elements;
        }
        
        usort($elements, function(\DOMElement $a, \DOMElement $b) use($depthOrder) {
            $al = substr_count($a->getNodePath(), '/');
            $bl = substr_count($b->getNodePath(), '/');
            if ($al == $bl) {
                return 0;
            }

            if ($depthOrder == Document::DEPTH_ASCENDING) {
                return (($al < $bl) ? 1 : -1);
            } else {
                return (($al > $bl) ? 1 : -1);
            }
        });
        return $elements;
    }
}
