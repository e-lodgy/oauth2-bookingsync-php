<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $containerConfigurator): void {
    $containerConfigurator->import('vendor/e-lodgy/coding-standard/ecs.php');
};
