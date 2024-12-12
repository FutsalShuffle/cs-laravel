<?php

declare(strict_types=1);

namespace DigitalSector\CsLaravel\Enum;

final class Commands
{
    public const POST_INSTALL_CMD_NAME = 'post-install-cmd';
    public const POST_UPDATE_CMD_NAME = 'post-update-cmd';
    public const CODE_STYLE_PHPLINT_NAME = 'code-style:phplint';
    public const CODE_STYLE_FIX_NAME = 'code-style:fix';
    public const CODE_STYLE_CHECK_NAME = 'code-style:check';
    public const CODE_STYLE_ANALYZE_NAME = 'code-style:analyze';

    public const POST_INSTALL_CMD = ['vendor/bin/cghooks add --git-dir=.git'];
    public const POST_UPDATE_CMD = ['vendor/bin/cghooks update --git-dir=.git'];
    public const CODE_STYLE_PHPLINT = 'php -l -f';
    public const CODE_STYLE_FIX = 'docker run -it --rm -v $(pwd):/code ghcr.io/php-cs-fixer/php-cs-fixer:3-php8.3 fix --path-mode=intersection --config .php_cs-fixer.php --allow-risky=yes';
    public const CODE_STYLE_CHECK = 'docker run -it --rm -v $(pwd):/code ghcr.io/php-cs-fixer/php-cs-fixer:3-php8.3 fix --path-mode=intersection --config .php_cs-fixer.php --allow-risky=yes --dry-run';
    public const CODE_STYLE_ANALYZE = 'docker run --rm -v .:/app ghcr.io/phpstan/phpstan:2-php8.3 analyse -c phpstan.neon --ansi';
}
