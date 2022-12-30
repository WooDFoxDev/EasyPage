<?php

namespace Easypage\Kernel\View;

use Easypage\Kernel\Abstractions\View;

/**
 * Simple templating engine
 * 
 * Capabilities:
 * - Extending templates
 * {% extends layout.html %}
 * 
 * - Block declaraion
 * {% yield title %}
 * 
 * - Block content replacement
 * {% block title %}Title{% endblock %}
 * 
 * - Parent block content wrapping
 * {% block content %}
 * @parent
 * <p>Extends content block!</p>
 * {% endblock %}
 * 
 * - Execute PHP code
 * {% foreach ($var as $key => $value): %}
 * {% endforeach; %}
 * 
 * - Echo variable
 * {{ $variable }}
 * 
 * - Safe echo
 * {{{ $variable }}}
 * 
 * - Inclusion
 * {% include forms.html %}
 */


class EPView extends View
{
    private static ?View $instance = null;
    private $templates_path = '/views';
    private $templates_extension = 'html';
    private $cache_enabled = true;
    private $cache_path = '/cache';
    private array $blocks = [];

    public static function getInstance(): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function setTemplatesPath(string $path): void
    {
        $this->templates_path = $path;
    }

    public function setTemplatesExtension(string $extension): void
    {
        $this->templates_extension = $extension;
    }

    public function setCacheEnabled(bool $enabled): void
    {
        $this->cache_enabled = $enabled;
    }

    public function setCachePath(string $path): void
    {
        $this->cache_path = $path;
    }

    public function render(string $template_name, array $data = []): string
    {
        $this->blocks = [];

        // Check all locations, create
        $this->checkLocations();

        // Get template path
        // This also check cache and compile template
        $template = self::getCachedPath($template_name);

        extract($data, EXTR_SKIP);

        ob_start();
        require($template);
        $page = ob_get_clean();

        return $page;
    }

    public function getCachedPath(string $template_name): string
    {
        $template_path = $this->getTemplatePath($template_name);
        $cached_tempalte_path = $this->getTemplateCachePath($template_path);

        if (!$this->cache_enabled || !file_exists($cached_tempalte_path) || filemtime($cached_tempalte_path) < filemtime($template_path)) {
            $this->compileTemplate($template_path, $cached_tempalte_path);
        }

        return $cached_tempalte_path;
    }

    private function getTemplatePath(string $template_name): string
    {
        return $this->getTemplatesPath() . $template_name . '.' . $this->templates_extension;
    }

    private function getTemplateCachePath(string $template_path): string
    {
        return $this->getCachePath() . md5($template_path) . '.php';
    }

    private function compileTemplate(string $template_path, string $cached_tempalte_path): void
    {
        $template_code = $this->templateLoad($template_path);

        $this->requreInclusions($template_code);

        $this->compileBlock($template_code);
        $this->compileYield($template_code);
        $this->compileEscapedEchos($template_code);
        // Only for CSS
        if ($this->templates_extension == 'css') {
            $this->compileEchosCSS($template_code);
            $this->compilePHPCSS($template_code);
        }
        $this->compileEchos($template_code);
        $this->compilePHP($template_code);

        if (!file_put_contents($cached_tempalte_path, $template_code)) {
            throw new \ErrorException('Cannot save compiled template');
        }
    }

    private function templateLoad(string $template_path): string
    {
        if (!file_exists($template_path)) {
            throw new \InvalidArgumentException('Cannot locate template file');
        }

        return file_get_contents($template_path);
    }

    private function requreInclusions(string &$template_code): void
    {
        preg_match_all('/{% ?(extends|include) ?\'?(.*?)\'? ?%}/i', $template_code, $matches, PREG_SET_ORDER);

        // TODO: IMPROVE IMPORTING

        // TODO: Params passing to included
        // preg_match_all('/{% ?(extends|include) ?\'?([^\s]*?)\'? ({.*?})? ?%}/i', $template_code, $matches, PREG_SET_ORDER);

        foreach ($matches as $value) {
            $inclusion_code = $this->templateLoad($this->getTemplatePath($value[2]));
            $this->requreInclusions($inclusion_code);

            // TODO: Params passing to included
            // if ($value[1] == 'include' && isset($value[3])) {
            //     $_params = json_decode($value[3], true);
            // }

            $template_code = str_replace($value[0], $inclusion_code, $template_code);
        }

        $template_code = preg_replace('/{% ?(extends|include) ?\'?(.*?)\'? ?%}/i', '', $template_code);
    }

    private function compilePHP(string &$template_code): void
    {
        $template_code = preg_replace('~\{%\s*(.+?)\s*\%}~is', '<?php $1 ?>', $template_code);
    }

    private function compilePHPCSS(string &$template_code): void
    {
        $template_code = preg_replace('~\"{%\s*(.+?)\s*\%}"~is', '<?php $1 ?>', $template_code);
    }

    private function compileEchos(string &$template_code): void
    {
        $template_code = preg_replace('~\{{\s*(.+?)\s*\}}~is', '<?php echo $1 ?>', $template_code);
    }

    private function compileEchosCSS(string &$template_code): void
    {
        $template_code = preg_replace('~\"{{\s*(.+?)\s*\}}"~is', '<?php echo $1 ?>', $template_code);
    }

    private function compileEscapedEchos(string &$template_code): void
    {
        $template_code = preg_replace('~\{{{\s*(.+?)\s*\}}}~is', '<?php echo htmlentities($1, ENT_QUOTES, \'UTF-8\') ?>', $template_code);
    }

    private function compileBlock(string &$template_code): void
    {
        preg_match_all('/{% ?block ?(.*?) ?%}(.*?){% ?endblock ?%}/is', $template_code, $matches, PREG_SET_ORDER);

        foreach ($matches as $value) {
            if (!array_key_exists($value[1], $this->blocks)) $this->blocks[$value[1]] = '';

            if (strpos($value[2], '@parent') === false) {
                $this->blocks[$value[1]] = $value[2];
            } else {
                $this->blocks[$value[1]] = str_replace('@parent', $this->blocks[$value[1]], $value[2]);
            }

            $template_code = str_replace($value[0], '', $template_code);
        }
    }

    private function compileYield(string &$template_code): void
    {
        foreach ($this->blocks as $block => $value) {
            $template_code = preg_replace('/{% ?yield ?' . $block . ' ?%}/', $value, $template_code);
        }

        $template_code = preg_replace('/{% ?yield ?(.*?) ?%}/i', '', $template_code);
    }

    private function checkLocations()
    {
        $templates_path = $this->getTemplatesPath();
        $cache_path = $this->getCachePath();

        if (!file_exists($templates_path)) {
            mkdir($templates_path, recursive: true);
        }

        if (!file_exists($cache_path)) {
            mkdir($cache_path, recursive: true);
        }
    }

    private function getTemplatesPath(): string
    {
        return ROOT_PATH . '/' . trim($this->templates_path, '/') . '/';
    }

    private function getCachePath(): string
    {
        return ROOT_PATH . '/' . trim($this->cache_path, '/') . '/epview/';
    }

    public function clearCache()
    {
        foreach (glob($this->getCachePath() . '*') as $file) {
            unlink($file);
        }
    }
}
