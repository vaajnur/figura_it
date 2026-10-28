<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */
/** @var CMain $APPLICATION */

use Bitrix\Main\UI\Extension;

Extension::load(['ui.buttons', 'ui.forms']);

if (!empty($arResult['ERROR']))
{
	ShowError($arResult['ERROR']);
	return;
}
?>

<div class="figura-equipment-list">
	<div class="figura-equipment-list__toolbar">
		<span class="figura-equipment-list__total">
			Всего: <?= (int)$arResult['TOTAL_COUNT'] ?>
		</span>
	</div>

	<?php
	$APPLICATION->IncludeComponent(
		'bitrix:main.ui.filter',
		'',
		[
			'FILTER_ID' => $arResult['FILTER_ID'],
			'GRID_ID' => $arResult['GRID_ID'],
			'FILTER' => $arResult['FILTER_FIELDS'],
			'ENABLE_LIVE_SEARCH' => true,
			'ENABLE_LABEL' => true,
		],
		false,
		['HIDE_ICONS' => 'Y']
	);

	$APPLICATION->IncludeComponent(
		'bitrix:main.ui.grid',
		'',
		[
			'GRID_ID' => $arResult['GRID_ID'],
			'COLUMNS' => $arResult['COLUMNS'],
			'ROWS' => $arResult['ROWS'],
			'NAV_OBJECT' => $arResult['NAV_OBJECT'],
			'AJAX_MODE' => 'Y',
			'AJAX_OPTION_JUMP' => 'N',
			'AJAX_OPTION_HISTORY' => 'N',
			'ALLOW_COLUMNS_SORT' => true,
			'ALLOW_COLUMNS_RESIZE' => true,
			'ALLOW_HORIZONTAL_SCROLL' => true,
			'ALLOW_SORT' => true,
			'ALLOW_PIN_HEADER' => true,
			'SHOW_CHECK_ALL_CHECKBOXES' => false,
			'SHOW_ROW_CHECKBOXES' => false,
			'SHOW_ROW_ACTIONS_MENU' => false,
			'SHOW_GRID_SETTINGS_MENU' => true,
			'SHOW_NAVIGATION_PANEL' => true,
			'SHOW_PAGINATION' => true,
			'SHOW_SELECTED_COUNTER' => false,
			'SHOW_TOTAL_COUNTER' => true,
			'TOTAL_ROWS_COUNT' => (int)$arResult['TOTAL_COUNT'],
			'SHOW_PAGESIZE' => true,
			'PAGE_SIZES' => [
				['NAME' => '5', 'VALUE' => '5'],
				['NAME' => '10', 'VALUE' => '10'],
				['NAME' => '20', 'VALUE' => '20'],
				['NAME' => '50', 'VALUE' => '50'],
			],
		],
		false,
		['HIDE_ICONS' => 'Y']
	);
	?>
</div>

<style>
	.figura-equipment-list__toolbar {
		margin-bottom: 12px;
		display: flex;
		align-items: center;
		gap: 12px;
	}
	.figura-equipment-list__total {
		color: #525c69;
		font-size: 14px;
	}
</style>
