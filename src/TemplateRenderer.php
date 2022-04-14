<?php
declare(strict_types = 1);
namespace Regal;

class TemplateRenderer {
    private TemplateEngine $engine;
    private TemplateExpander $expander;

    public function __construct(TemplateEngine $engine) {
        $this->engine = $engine;
        $this->expander = new TemplateExpander;
    }

    /**
     * Get key-value array of properties (attributes with names starting with ':') from a node
     * @param \DOMNode $node Node to get properties from
     * @return array Key-value array of properties
     */
    private function getPropertiesFromNode(\DOMNode $node): array {
        $props = [];
        if (count($node->childNodes) > 0) {
            $props["in"] = Util::domChildrenToString($node);
        }
        foreach ($node->attributes as $name => $attr) {
            if(strlen($name) > 1 && $name[0] == ':') {
                $props[substr($name, 1)] = empty($attr->value) ? true : trim($attr->value);
            }
        }
        return $props;
    }

    /**
     * Create a new template instance based on an element in the DOM
     * @param \DOMElement $element Element to construct TemplateInstance from
     * @return ?TemplateInstance Returns a TemplateInstance if successful, null otherwise
     */
    private function getInstanceFromElement(\DOMElement $element): ?TemplateInstance {
        $path = $element->getAttribute("regal:path");
        $props = $this->getPropertiesFromNode($element);
        return $this->engine->getInstance($path, empty($props) ? null : $props);
    }

    /**
     * Renders a given template instance
     * @param TemplateInstance $instance Template instance
     * @return array|false Returns an array of instance IDs referenced by this instance, or false if an error occurred
     */
    public function renderInstance(TemplateInstance $instance): array|false {
        $dependencies = [];
        $docHtml = $this->expander->expandString($instance, $instance->source->html);
        $doc = new Document;
        $doc->loadHTML($docHtml);
        $instanceTags = $doc->getElementsByTag("instance", Document::DEPTH_ASCENDING);

        if (!is_null($instanceTags)) {
            foreach ($instanceTags as $el) {
                $inst = $this->getInstanceFromElement($el);
                $deps = $this->renderInstance($inst);
                if (is_null($inst) || $deps === false) {
                    echo "Error: Failed to render instance" . PHP_EOL;
                    return false;
                }

                /* Combine dependencies */
                $dependencies = array_merge($dependencies, $deps, [ $inst->instanceId ]);

                /* Replace instance tag with rendered html */
                $instDoc = new Document;
                $instDoc->loadHTML($inst->getHtml());
                $instFrag = $instDoc->createDocumentFragment();
                $instFrag->append($instDoc->childNodes[0]);
                $el->parentNode->replaceChild($doc->importNode($instFrag, true), $el);
            }
        }

        $instance->setHtml($doc->saveHTML());
        $instance->setStyle($this->expander->expandString($instance, Util::trimCss($instance->source->style)));

        return array_values(array_unique($dependencies, SORT_NUMERIC));
    }
}
