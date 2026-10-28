<?php

namespace Figura\Equipment\Hlblock;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\SystemException;

/**
 * Фасад к DataManager HL-блока «Оборудование».
 * Конкретный класс генерируется Bitrix по имени HL-блока Equipment.
 */
class EquipmentTable
{
	public const HL_NAME = 'Equipment';
	public const TABLE_NAME = 'figura_equipment';

	public const FIELD_NAME = 'UF_NAME';
	public const FIELD_TYPE = 'UF_TYPE';
	public const FIELD_CREATED_TIME = 'UF_CREATED_TIME';
	public const FIELD_DEAL_ID = 'UF_DEAL_ID';

	public const TYPE_ROUTER = 'ROUTER';
	public const TYPE_LAPTOP = 'LAPTOP';
	public const TYPE_MONITOR = 'MONITOR';

	/** @var class-string<DataManager>|null */
	private static ?string $entityClass = null;

	/**
	 * @return class-string<DataManager>
	 * @throws SystemException
	 */
	public static function getDataClass(): string
	{
		if (self::$entityClass !== null)
		{
			return self::$entityClass;
		}

		if (!Loader::includeModule('highloadblock'))
		{
			throw new SystemException('Module highloadblock is not installed');
		}

		$hlblock = HighloadBlockTable::getList([
			'filter' => ['=NAME' => self::HL_NAME],
			'limit' => 1,
		])->fetch();

		if (!$hlblock)
		{
			throw new SystemException('HL-block Equipment was not found');
		}

		$entity = HighloadBlockTable::compileEntity($hlblock);
		self::$entityClass = $entity->getDataClass();

		return self::$entityClass;
	}

	public static function getTypeLabels(): array
	{
		return [
			self::TYPE_ROUTER => 'Роутер',
			self::TYPE_LAPTOP => 'Ноутбук',
			self::TYPE_MONITOR => 'Монитор',
		];
	}

	public static function getHlblockId(): ?int
	{
		if (!Loader::includeModule('highloadblock'))
		{
			return null;
		}

		$row = HighloadBlockTable::getList([
			'filter' => ['=NAME' => self::HL_NAME],
			'select' => ['ID'],
			'limit' => 1,
		])->fetch();

		return $row ? (int)$row['ID'] : null;
	}
}
