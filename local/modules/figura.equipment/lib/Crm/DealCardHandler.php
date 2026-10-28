<?php

namespace Figura\Equipment\Crm;

use Bitrix\Main\Context;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;
use Figura\Equipment\Service\EquipmentCounter;

/**
 * Подключает скрипт отображения количества оборудования в карточке сделки.
 * Поле вставляется под блоком «Клиент», только для чтения.
 * Для пользователя с ID = 5 применяется визуальное выделение.
 */
class DealCardHandler
{
	public const HIGHLIGHT_USER_ID = 5;

	public static function onEpilog(): void
	{
		if (!Loader::includeModule('figura.equipment') || !Loader::includeModule('crm'))
		{
			return;
		}

		$dealId = self::resolveDealIdFromRequest();
		if ($dealId <= 0)
		{
			return;
		}

		try
		{
			$count = (new EquipmentCounter())->countByDealId($dealId);
		}
		catch (\Throwable)
		{
			$count = 0;
		}

		$userId = (int)CurrentUser::get()->getId();
		$highlight = $userId === self::HIGHLIGHT_USER_ID;

		Extension::load(['figura.equipment.deal-card']);

		$config = \Bitrix\Main\Web\Json::encode([
			'dealId' => $dealId,
			'count' => $count,
			'highlight' => $highlight,
			'label' => 'Оборудование',
			'title' => 'Количество оборудования, привязанного к сделке',
		]);

		Asset::getInstance()->addString(
			'<script>BX.ready(function(){ if (window.BX && BX.FiguraEquipmentDealCard) { BX.FiguraEquipmentDealCard.init(' . $config . '); } });</script>',
			true
		);
	}

	private static function resolveDealIdFromRequest(): int
	{
		$request = Context::getCurrent()->getRequest();
		$path = (string)$request->getRequestedPage();

		// Классический и SPA-URL карточки сделки: /crm/deal/details/{ID}/
		if (preg_match('#/crm/deal/details/(\d+)/?#', $path, $m))
		{
			return (int)$m[1];
		}

		$uri = (string)$request->getRequestUri();
		if (preg_match('#/crm/deal/details/(\d+)/?#', $uri, $m))
		{
			return (int)$m[1];
		}

		$dealId = (int)$request->get('id');
		if ($dealId > 0 && str_contains($uri, '/crm/deal/'))
		{
			return $dealId;
		}

		return 0;
	}
}
