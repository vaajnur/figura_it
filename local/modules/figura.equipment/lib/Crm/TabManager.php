<?php

namespace Figura\Equipment\Crm;

use Bitrix\Crm\Item;
use Bitrix\Crm\Service\Container;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Loader;
use CCrmOwnerType;
use Figura\Equipment\Install\SmartProcessInstaller;

/**
 * Добавляет вкладку «Оборудование» в карточку элемента смарт-процесса.
 * На вкладке показывается компонент figura:equipment.list,
 * отфильтрованный по сделке, связанной с текущим элементом СП.
 */
class TabManager
{
	public const TAB_ID = 'figura_equipment_tab';

	public static function onEntityDetailsTabsInitialized(Event $event): EventResult
	{
		$entityTypeId = (int)$event->getParameter('entityTypeID');
		$entityId = (int)$event->getParameter('entityID');
		$tabs = $event->getParameter('tabs');

		if (!is_array($tabs))
		{
			$tabs = [];
		}

		if (!Loader::includeModule('figura.equipment') || !Loader::includeModule('crm'))
		{
			return new EventResult(EventResult::SUCCESS, ['tabs' => $tabs]);
		}

		$spaEntityTypeId = (new SmartProcessInstaller())->getEntityTypeId();
		if (!$spaEntityTypeId || $entityTypeId !== $spaEntityTypeId)
		{
			return new EventResult(EventResult::SUCCESS, ['tabs' => $tabs]);
		}

		$dealId = self::resolveDealId($entityTypeId, $entityId);
		$loaderUrl = '/local/components/figura/equipment.list/lazyload.ajax.php'
			. '?site=' . urlencode(SITE_ID)
			. '&sessid=' . bitrix_sessid()
			. '&DEAL_ID=' . $dealId
			. '&ENTITY_TYPE_ID=' . $entityTypeId
			. '&ENTITY_ID=' . $entityId;

		$tabs[] = [
			'id' => self::TAB_ID,
			'name' => 'Оборудование',
			'loader' => [
				'serviceUrl' => $loaderUrl,
				'componentData' => [
					'template' => '',
					'params' => [
						'DEAL_ID' => $dealId,
					],
				],
			],
		];

		return new EventResult(EventResult::SUCCESS, [
			'tabs' => $tabs,
		]);
	}

	/**
	 * Получает ID связанной сделки для элемента смарт-процесса.
	 */
	public static function resolveDealId(int $entityTypeId, int $entityId): int
	{
		if ($entityId <= 0)
		{
			return 0;
		}

		$factory = Container::getInstance()->getFactory($entityTypeId);
		if (!$factory)
		{
			return 0;
		}

		$item = $factory->getItem($entityId);
		if (!$item)
		{
			return 0;
		}

		$parentDealField = Item::FIELD_NAME_PARENT_ID . CCrmOwnerType::Deal;
		if ($item->hasField($parentDealField))
		{
			$dealId = (int)$item->get($parentDealField);
			if ($dealId > 0)
			{
				return $dealId;
			}
		}

		// Fallback: RelationManager
		$relationManager = Container::getInstance()->getRelationManager();
		$child = new \Bitrix\Crm\ItemIdentifier($entityTypeId, $entityId);
		$parentElements = $relationManager->getParentElements($child);

		foreach ($parentElements as $parent)
		{
			if ($parent->getEntityTypeId() === CCrmOwnerType::Deal)
			{
				return $parent->getEntityId();
			}
		}

		// Fallback на тестовую сделку из опций модуля (для демо).
		return (int)(new SmartProcessInstaller())->getTestDealId();
	}
}
