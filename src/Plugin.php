<?php

declare(strict_types=1);

namespace DigitalSector\CsLaravel;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Json\JsonManipulator;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;
use DigitalSector\CsLaravel\Enum\Commands;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    public const PLUGIN_VENDOR_PATH = '/futsalshuffle/cs-laravel/';

    private Composer $composer;

    private IOInterface $io;


    private ComposerHelper $composerHelper;

    private GitHooksHelper $gitHooksHelper;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->composerHelper = new ComposerHelper(
            $composer,
            $io,
            new JsonManipulator(file_get_contents(Factory::getComposerFile()))
        );
        $this->gitHooksHelper = new GitHooksHelper($composer, $io);
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
        // TODO: Implement deactivate() method.
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
        $this->composerHelper->getManipulator()->removeSubNode('scripts', Commands::CODE_STYLE_PHPLINT);
        $this->composerHelper->getManipulator()->removeSubNode('scripts', Commands::CODE_STYLE_FIX);
        $this->composerHelper->getManipulator()->removeSubNode('scripts', Commands::CODE_STYLE_CHECK);
        $this->composerHelper->getManipulator()->removeSubNode('scripts', Commands::CODE_STYLE_ANALYZE);

        $this->composerHelper->getManipulator()->removeSubNode('extra', 'hooks');
        $this->gitHooksHelper->delete();

        $this->composerHelper->writeComposerJson();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => 'postInstallCmd',
            ScriptEvents::POST_UPDATE_CMD => 'postUpdateCmd',
        ];
    }

    public function postInstallCmd(): void
    {
        $this->configureProject();
        $this->copyPhpstan();
        $this->copyGitHooks();
    }

    public function postUpdateCmd(): void
    {
        $this->configureProject();
        $this->copyPhpstan();
        $this->copyGitHooks();
    }

    private function configureProject(): void
    {
        $this->gitHooksHelper->installHooks($this->composerHelper->getManipulator());

        $composerJsonContent = JsonFile::parseJson($this->composerHelper->getComposerJsonContent());
        $postInstallCmd = $composerJsonContent['scripts'][Commands::POST_INSTALL_CMD_NAME] ?? [];
        $postUpdateCmd = $composerJsonContent['scripts'][Commands::POST_UPDATE_CMD_NAME] ?? [];

        if (empty(array_intersect($postInstallCmd, Commands::POST_INSTALL_CMD))) {
            $postInstallCmd = array_merge($postInstallCmd, Commands::POST_INSTALL_CMD);

            $this->composerHelper
                ->getManipulator()
                ->addSubNode('scripts', Commands::POST_INSTALL_CMD_NAME, $postInstallCmd);
        }

        if (empty(array_intersect($postUpdateCmd, Commands::POST_UPDATE_CMD))) {
            $postUpdateCmd = array_merge($postUpdateCmd, Commands::POST_UPDATE_CMD);

            $this->composerHelper
                ->getManipulator()
                ->addSubNode('scripts', Commands::POST_UPDATE_CMD_NAME, $postUpdateCmd);
        }

        $this->composerHelper
            ->getManipulator()
            ->addSubNode('scripts', Commands::CODE_STYLE_PHPLINT_NAME, Commands::CODE_STYLE_PHPLINT);
        $this->composerHelper
            ->getManipulator()
            ->addSubNode('scripts', Commands::CODE_STYLE_FIX_NAME, Commands::CODE_STYLE_FIX);
        $this->composerHelper
            ->getManipulator()
            ->addSubNode('scripts', Commands::CODE_STYLE_CHECK_NAME, Commands::CODE_STYLE_CHECK);
        $this->composerHelper
            ->getManipulator()
            ->addSubNode('scripts', Commands::CODE_STYLE_ANALYZE_NAME, Commands::CODE_STYLE_ANALYZE);

        $this->composerHelper->writeComposerJson();

        $this->composerHelper->updateComposerLock();
    }

    private function copyPhpstan(): void
    {
        $vendorPath = $this->composer->getConfig()->get('vendor-dir');
        $phpstan = realpath($vendorPath . self::PLUGIN_VENDOR_PATH . 'phpstan.neon');
        $newPhpstan = $vendorPath . '/../phpstan.neon';

        if (!realpath($newPhpstan) || !file_exists(realpath($newPhpstan))) {
            $this->io->write('[digitaldsdev/codestyle]: Copy phpstan.neon to project directory');
            file_put_contents($newPhpstan, fopen($phpstan, 'r'));
        }
    }

    private function copyGitHooks(): void
    {
        $this->gitHooksHelper->copy();
    }
}
