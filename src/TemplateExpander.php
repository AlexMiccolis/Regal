<?php
declare(strict_types = 1);
namespace Regal;

class TemplateExpander {
    private const SUBST_PATTERN = "/{{\s*([^\s}]*)\s*}}/";

    public static function hasSubstitutions(string $str): bool {
        return !!preg_match(self::SUBST_PATTERN, $str);
    }
    
    public function __construct() {

    }

    public function expandString(TemplateInstance $instance, string $str): string {
        return preg_replace_callback(self::SUBST_PATTERN, function($match) use($instance) {
            $name = $match[1];
            return match($name) {
                "_id_" => $instance->instanceBase32,
                "_scope_" => "rg{$instance->instanceBase32}",
                default => $instance->properties[$name] ?? ""
            };
        }, $str);
    }

}
