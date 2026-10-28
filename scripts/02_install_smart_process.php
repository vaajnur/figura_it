<?php
/**
 * Создание смарт-процесса, связанного со сделкой,
 * тестовой сделки и элемента СП + наполнение HL-блока.
 *
 * Запуск:
 *   php scripts/02_install_smart_process.php
 */

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('BX_NO_ACCELERATOR_RESET', true);

$docRoot = getenv('BITRIX_DOC_ROOT') ?: dirname(__DIR__);
$_SERVER['DOCUMENT_ROOT'] = $docRoot;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Figura\Equipment\Install\HlblockInstaller;
use Figura\Equipment\Install\SmartProcessInstaller;

if (!ModuleManager::isModuleInstalled('figura.equipment'))
{
	require_once $_SERVER['DOCUMENT_ROOT'] . '/local/modules/figura.equipment/install/index.php';
	(new figura_equipment())->DoInstall();
}

if (!Loader::includeModule('figura.equipment') || !Loader::includeModule('crm'))
{
	fwrite(STDERR, "Required modules are not available\n");
	exit(1);
}

(new HlblockInstaller())->install();

$result = (new SmartProcessInstaller())->install();

echo "Smart process entityTypeId={$result['entityTypeId']}\n";
echo "Test deal ID={$result['dealId']}\n";
echo "Test SPA item ID={$result['itemId']}\n";
echo "HL-block seeded for deal #{$result['dealId']}\n";

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';
