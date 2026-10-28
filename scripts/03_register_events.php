<?php
/**
 * Повторная регистрация событий модуля (вкладка СП + карточка сделки).
 *
 * Запуск:
 *   php scripts/03_register_events.php
 */

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);

$docRoot = getenv('BITRIX_DOC_ROOT') ?: dirname(__DIR__);
$_SERVER['DOCUMENT_ROOT'] = $docRoot;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/modules/figura.equipment/install/index.php';

$module = new figura_equipment();
$module->UnInstallEvents();
$module->InstallEvents();

echo "Events re-registered for figura.equipment\n";

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';
