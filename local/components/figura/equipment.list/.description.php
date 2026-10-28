<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arComponentDescription = [
	'NAME' => 'Список оборудования',
	'DESCRIPTION' => 'Таблица оборудования из HL-блока с фильтром по сделке (main.ui.grid / main.ui.filter)',
	'PATH' => [
		'ID' => 'figura',
		'NAME' => 'Figura',
	],
	'CACHE_PATH' => 'Y',
	'COMPLEX' => 'N',
];
