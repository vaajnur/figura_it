<?php
/**
 * AJAX-подгрузка содержимого вкладки «Оборудование» в карточке СП.
 * Подключается через loader.serviceUrl события onEntityDetailsTabsInitialized.
 */

define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC', 'Y');
define('NO_AGENT_CHECK', true);
define('PUBLIC_AJAX_MODE', true);
define('DisableEventsCheck', true);

$siteId = isset($_REQUEST['site']) ? substr(preg_replace('/[^a-z0-9_]/i', '', (string)$_REQUEST['site']), 0, 2) : '';
if ($siteId)
{
	define('SITE_ID', $siteId);
}

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Figura\Equipment\Crm\TabManager;

if (!check_bitrix_sessid())
{
	echo 'Access denied';
	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';
	die();
}

if (!Loader::includeModule('figura.equipment') || !Loader::includeModule('crm'))
{
	echo 'Modules not installed';
	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';
	die();
}

$request = Application::getInstance()->getContext()->getRequest();
$dealId = (int)$request->get('DEAL_ID');
$entityTypeId = (int)$request->get('ENTITY_TYPE_ID');
$entityId = (int)$request->get('ENTITY_ID');

if ($dealId <= 0 && $entityTypeId > 0 && $entityId > 0)
{
	$dealId = TabManager::resolveDealId($entityTypeId, $entityId);
}

/** @var CMain $APPLICATION */
$APPLICATION->ShowAjaxHead();
$APPLICATION->IncludeComponent(
	'figura:equipment.list',
	'',
	[
		'DEAL_ID' => $dealId,
	],
	false,
	['HIDE_ICONS' => 'Y']
);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';
