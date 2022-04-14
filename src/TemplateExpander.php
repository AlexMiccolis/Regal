<?php
declare(strict_types = 1);
namespace Regal;

class TemplateExpander {
    private const SUBST_PATTERN = "/{{\s*([^\s}]*)\s*}}/";

    /**
     * Check if the given string contains substitutions
     * @param string $str String
     * @return bool True if the string contains substitutions, false otherwise
     */
    public static function hasSubstitutions(string $str): bool {
        return !!preg_match(self::SUBST_PATTERN, $str);
    }
    
    /**
     * Do substitutions in a string based on a TemplateInstance
     * @param TemplateInstance $instance Instance
     * @param string $str String to do substitutions in
     */
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
