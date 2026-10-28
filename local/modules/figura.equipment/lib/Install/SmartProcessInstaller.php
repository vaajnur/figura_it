<?php

namespace Figura\Equipment\Install;

use Bitrix\Crm\Model\Dynamic\TypeTable;
use Bitrix\Crm\Relation\RelationManager;
use Bitrix\Crm\RelationIdentifier;
use Bitrix\Crm\Service\Container;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use CCrmDeal;
use CCrmOwnerType;
use Figura\Equipment\Hlblock\EquipmentTable;

/**
 * Создание смарт-процесса, связанного со сделкой,
 * тестовой сделки и элемента СП с привязкой к сделке.
 */
class SmartProcessInstaller
{
	public const OPTION_ENTITY_TYPE_ID = 'spa_entity_type_id';
	public const OPTION_TEST_DEAL_ID = 'test_deal_id';
	public const OPTION_TEST_ITEM_ID = 'test_spa_item_id';
	public const MODULE_ID = 'figura.equipment';

	public const SPA_NAME = 'Оборудование по сделке';
	public const SPA_TITLE_FIELD = 'TITLE';

	/**
	 * @return array{entityTypeId:int, dealId:int, itemId:int}
	 */
	public function install(): array
	{
		if (!Loader::includeModule('crm'))
		{
			throw new SystemException('Module crm is required');
		}

		$entityTypeId = $this->ensureSmartProcess();
		$dealId = $this->ensureTestDeal();
		$itemId = $this->ensureTestItem($entityTypeId, $dealId);

		// После появления сделки наполняем HL-блок демо-записями.
		$existingCount = (new \Figura\Equipment\Hlblock\EquipmentRepository())->countByDealId($dealId);
		if ($existingCount === 0)
		{
			(new DemoDataInstaller())->seedForDeal($dealId);
		}

		return [
			'entityTypeId' => $entityTypeId,
			'dealId' => $dealId,
			'itemId' => $itemId,
		];
	}

	public function getEntityTypeId(): ?int
	{
		$value = \Bitrix\Main\Config\Option::get(self::MODULE_ID, self::OPTION_ENTITY_TYPE_ID, '');
		return $value !== '' ? (int)$value : null;
	}

	public function getTestDealId(): ?int
	{
		$value = \Bitrix\Main\Config\Option::get(self::MODULE_ID, self::OPTION_TEST_DEAL_ID, '');
		return $value !== '' ? (int)$value : null;
	}

	private function ensureSmartProcess(): int
	{
		$existing = $this->getEntityTypeId();
		if ($existing)
		{
			$type = TypeTable::getByEntityTypeId($existing)->fetchObject();
			if ($type)
			{
				return $existing;
			}
		}

		$factory = Container::getInstance()->getDynamicTypeDataClass();

		$type = $factory::createObject();
		$type->setTitle(self::SPA_NAME);
		$type->setName('FiguraEquipmentSpa');
		$type->setIsCategoriesEnabled(false);
		$type->setIsStagesEnabled(true);
		$type->setIsBeginCloseDatesEnabled(false);
		$type->setIsClientEnabled(true);
		$type->setIsLinkWithProductsEnabled(false);
		$type->setIsCrmTrackingEnabled(false);
		$type->setIsMycompanyEnabled(false);
		$type->setIsDocumentsEnabled(false);
		$type->setIsSourceEnabled(false);
		$type->setIsObserversEnabled(false);
		$type->setIsRecurringEnabled(false);
		$type->setIsAutomationEnabled(true);
		$type->setIsBizProcEnabled(false);
		$type->setIsSetOpenPermissions(true);

		$result = $type->save();
		if (!$result->isSuccess())
		{
			throw new SystemException(implode('; ', $result->getErrorMessages()));
		}

		$entityTypeId = (int)$type->getEntityTypeId();

		$this->bindToDeal($entityTypeId);

		\Bitrix\Main\Config\Option::set(self::MODULE_ID, self::OPTION_ENTITY_TYPE_ID, (string)$entityTypeId);

		return $entityTypeId;
	}

	/**
	 * Связь смарт-процесса со сделкой через Relation API CRM.
	 */
	private function bindToDeal(int $entityTypeId): void
	{
		if (!class_exists(RelationManager::class))
		{
			return;
		}

		$relationManager = Container::getInstance()->getRelationManager();
		$identifier = new RelationIdentifier(
			CCrmOwnerType::Deal,
			$entityTypeId
		);

		if ($relationManager->areTypesBound($identifier))
		{
			return;
		}

		$result = $relationManager->bindTypes(
			\Bitrix\Crm\Relation::create(
				$identifier,
				\Bitrix\Crm\Relation\Settings::createDefault()
					->setIsChildrenListEnabled(true)
			)
		);

		if (!$result->isSuccess())
		{
			// Не блокируем установку: связь можно настроить вручную в CRM.
			AddMessage2Log(
				'Figura equipment SPA relation warning: ' . implode('; ', $result->getErrorMessages()),
				self::MODULE_ID
			);
		}
	}

	private function ensureTestDeal(): int
	{
		$existing = $this->getTestDealId();
		if ($existing > 0)
		{
			$deal = CCrmDeal::GetByID($existing, false);
			if ($deal)
			{
				return $existing;
			}
		}

		$fields = [
			'TITLE' => 'Тестовая сделка: поставка оборудования',
			'STAGE_ID' => 'NEW',
			'CURRENCY_ID' => 'RUB',
			'OPPORTUNITY' => 150000,
			'OPENED' => 'Y',
			'COMMENTS' => 'Сделка создана установщиком модуля figura.equipment',
		];

		$deal = new CCrmDeal(false);
		$dealId = (int)$deal->Add($fields, true, [
			'CURRENT_USER' => 1,
			'DISABLE_USER_FIELD_CHECK' => true,
		]);

		if ($dealId <= 0)
		{
			throw new SystemException($deal->LAST_ERROR ?: 'Failed to create test deal');
		}

		\Bitrix\Main\Config\Option::set(self::MODULE_ID, self::OPTION_TEST_DEAL_ID, (string)$dealId);

		return $dealId;
	}

	private function ensureTestItem(int $entityTypeId, int $dealId): int
	{
		$existingItemId = (int)\Bitrix\Main\Config\Option::get(self::MODULE_ID, self::OPTION_TEST_ITEM_ID, '0');
		$factory = Container::getInstance()->getFactory($entityTypeId);
		if (!$factory)
		{
			throw new SystemException("Factory for entityTypeId={$entityTypeId} not found");
		}

		if ($existingItemId > 0)
		{
			$item = $factory->getItem($existingItemId);
			if ($item)
			{
				return $existingItemId;
			}
		}

		$item = $factory->createItem([
			'TITLE' => 'Заявка на комплект оборудования',
			'PARENT_ID_' . CCrmOwnerType::Deal => $dealId,
		]);

		// На случай, если PARENT_ID_* недоступен — пробуем универсальное поле связи.
		if (!$item->hasField('PARENT_ID_' . CCrmOwnerType::Deal))
		{
			$item->set('TITLE', 'Заявка на комплект оборудования (сделка #' . $dealId . ')');
		}

		$context = new \Bitrix\Crm\Service\Context();
		$context->setUserId(1);

		$operation = $factory->getAddOperation($item, $context);
		$result = $operation->launch();

		if (!$result->isSuccess())
		{
			throw new SystemException(implode('; ', $result->getErrorMessages()));
		}

		$itemId = (int)$item->getId();
		\Bitrix\Main\Config\Option::set(self::MODULE_ID, self::OPTION_TEST_ITEM_ID, (string)$itemId);

		// Дополнительно сохраняем связь через Binding API, если PARENT_ID не сработал.
		$this->bindItemToDeal($entityTypeId, $itemId, $dealId);

		return $itemId;
	}

	private function bindItemToDeal(int $entityTypeId, int $itemId, int $dealId): void
	{
		if (!class_exists(\Bitrix\Crm\ItemIdentifier::class))
		{
			return;
		}

		$parent = new \Bitrix\Crm\ItemIdentifier(CCrmOwnerType::Deal, $dealId);
		$child = new \Bitrix\Crm\ItemIdentifier($entityTypeId, $itemId);

		$relationManager = Container::getInstance()->getRelationManager();
		if ($relationManager->areItemsBound($parent, $child))
		{
			return;
		}

		$relationManager->bindItems($parent, $child);
	}
}
