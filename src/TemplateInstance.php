<?php
declare(strict_types = 1);
namespace Regal;

class TemplateInstance {
    public readonly int $instanceId;
    public readonly string $instanceBase32;
    public readonly TemplateSource $source;
    public readonly ?array $properties;

    private string $renderedHtml;
    private string $renderedStyle;

    public function __construct(int $instanceId, TemplateSource $source, ?array $properties = null) {
        $this->instanceId = $instanceId;
        $this->instanceBase32 = base_convert(strval($instanceId), 10, 32);
        $this->source = $source;
        $this->properties = $properties;
        $this->renderedHtml = "";
        $this->renderedStyle = "";
    }

    public function setHtml(string $html): void {
        $this->renderedHtml = $html;
    }

    public function getHtml(): string {
        return $this->renderedHtml;
    }

    public function setStyle(string $style): void {
        $this->renderedStyle = $style;
    }

    public function getStyle(): string {
        return $this->renderedStyle;
    }
}
