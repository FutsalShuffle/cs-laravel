<?php

use PhpCsFixer\ConfigInterface;
use PhpCsFixer\Finder;

class CsFixerConfigurator
{
    private const RULES = [
        '@PER-CS' => true,
        '@PHP81Migration' => true,
        '@PHP82Migration' => true,
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_unused_imports' => true,
        'strict_comparison' => false,
    ];

    private const EXCLUDES = [
        'bitrix',
        'config',
        'public',
        'swagger',
        'docker',
        'bootstrap',
        'cache',
        'var',
        'database',
        'migrations',
        'storage',
        'resources',
    ];

    private const ANALYZE_PATH = './';
    private const COMPOSER_JSON_PATH = './composer.json';

    /**
     * @var string[]
     */
    private $excludes = [];
    private $analyzePath = self::ANALYZE_PATH;

    /**
     * @return ConfigInterface
     */
    public function createConfiguration()
    {
        return (new \PhpCsFixer\Config())
                ->setRules(self::RULES)
                ->setFinder($this->createFinder())
            ;
    }

    /**
     * @return $this
     *
     * @throws JsonException
     */
    public function loadExtraSettings()
    {
        if (is_file(self::COMPOSER_JSON_PATH)) {
            $composer = json_decode(
                file_get_contents(self::COMPOSER_JSON_PATH),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
            $extraConfig = $composer['extra']['code-style'] ?? [];
            $this->excludes = $extraConfig['finder']['excludes'] ?? [];

            if (isset($extraConfig['analyze-path'])) {
                $this->analyzePath = $extraConfig['analyze-path'];
            }
        }

        return $this;
    }

    /**
     * @return Finder
     */
    private function createFinder()
    {
        return (new Finder())
            ->exclude(array_merge(self::EXCLUDES, $this->excludes))
            ->in($this->analyzePath);
    }
}

return (new CsFixerConfigurator())
    ->loadExtraSettings()
    ->createConfiguration();
