<?php
/**
 * Скрипт установки HL-блока «Оборудование» и тестовых записей.
 *
 * Запуск из корня сайта Bitrix:
 *   php local/modules/figura.equipment/../../../scripts/01_install_hlblock.php
 * или:
 *   php scripts/01_install_hlblock.php
 *
 * Ожидает DOCUMENT_ROOT с установленным Bitrix и модулем figura.equipment.
 */

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('BX_NO_ACCELERATOR_RESET', true);

$docRoot = getenv('BITRIX_DOC_ROOT') ?: dirname(__DIR__);
$_SERVER['DOCUMENT_ROOT'] = $docRoot;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Figura\Equipment\Install\DemoDataInstaller;
use Figura\Equipment\Install\HlblockInstaller;

if (!ModuleManager::isModuleInstalled('figura.equipment'))
{
	$moduleFile = $_SERVER['DOCUMENT_ROOT'] . '/local/modules/figura.equipment/install/index.php';
	require_once $moduleFile;
	$module = new figura_equipment();
	$module->DoInstall();
}

if (!Loader::includeModule('figura.equipment'))
{
	fwrite(STDERR, "Cannot include module figura.equipment\n");
	exit(1);
}

$installer = new HlblockInstaller();
$hlId = $installer->install();

echo "HL-block Equipment installed, ID={$hlId}\n";

// Тестовые записи создаются после появления сделки (см. пункт 2).
// Здесь можно передать DEAL_ID через аргумент CLI.
$dealId = isset($argv[1]) ? (int)$argv[1] : 0;
if ($dealId > 0)
{
	$ids = (new DemoDataInstaller())->seedForDeal($dealId);
	echo 'Seeded equipment IDs: ' . implode(', ', $ids) . "\n";
}
else
{
	echo "Pass deal ID as first argument to seed demo rows, e.g.: php scripts/01_install_hlblock.php 1\n";
}

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';
