<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arComponentParameters = [
	'PARAMETERS' => [
		'DEAL_ID' => [
			'PARENT' => 'BASE',
			'NAME' => 'ID сделки',
			'TYPE' => 'STRING',
			'DEFAULT' => '0',
		],
	],
];
