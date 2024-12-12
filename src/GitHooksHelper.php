<?php

declare(strict_types=1);

namespace DigitalSector\CsLaravel;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Json\JsonManipulator;

class GitHooksHelper
{
    public const PRE_COMMIT_FILE = '.git-pre-commit.sh';
    public const COMMIT_MSG = '.git-commit-msg.sh';

    public const HOOKS_LIST = [
        self::PRE_COMMIT_FILE,
        self::COMMIT_MSG,
    ];

    private Composer $composer;

    private IOInterface $io;

    public function __construct(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public function installHooks(JsonManipulator $jsonManipulator): void
    {
        $jsonManipulator->addSubNode('extra', 'hooks.pre-commit', ['./' . self::PRE_COMMIT_FILE]);
        $jsonManipulator->addSubNode('extra', 'hooks.commit-msg', ['./' . self::COMMIT_MSG . ' $1']);
        $jsonManipulator->addSubNode('extra', 'config.stop-on-failure', ['pre-commit']);
    }

    public function copy(): void
    {
        $vendorDir = $this->composer->getConfig()->get('vendor-dir');

        foreach (self::HOOKS_LIST as $hook) {
            $hookVendorPath = realpath($vendorDir . Plugin::PLUGIN_VENDOR_PATH . $hook);
            $newHookPath = $vendorDir . '/../' . $hook;

            if (!file_exists(realpath($newHookPath))) {
                $this->io->write('[digitaldsdev/codestyle]: Copy ' . $hook . ' to project directory');
                file_put_contents($newHookPath, fopen($hookVendorPath, 'r'));
            }
        }
    }

    public function delete(): void
    {
        foreach (self::HOOKS_LIST as $hook) {
            if (!file_exists($hook)) {
                $this->io->write('[digitaldsdev/codestyle]: Remove ' . $hook . ' from project directory');
                unlink($hook);
            }
        }
    }
}
