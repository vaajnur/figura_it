<?php

namespace Figura\Equipment\Install;

use Bitrix\Highloadblock\HighloadBlockLangTable;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use CUserFieldEnum;
use CUserTypeEntity;
use Figura\Equipment\Hlblock\EquipmentTable;

/**
 * Создание HL-блока «Оборудование» и пользовательских полей.
 */
class HlblockInstaller
{
	public function install(): int
	{
		if (!Loader::includeModule('highloadblock'))
		{
			throw new SystemException('Module highloadblock is required');
		}

		$existingId = EquipmentTable::getHlblockId();
		if ($existingId)
		{
			return $existingId;
		}

		$result = HighloadBlockTable::add([
			'NAME' => EquipmentTable::HL_NAME,
			'TABLE_NAME' => EquipmentTable::TABLE_NAME,
		]);

		if (!$result->isSuccess())
		{
			throw new SystemException(implode('; ', $result->getErrorMessages()));
		}

		$hlblockId = (int)$result->getId();

		HighloadBlockLangTable::add([
			'ID' => $hlblockId,
			'LID' => 'ru',
			'NAME' => 'Оборудование',
		]);

		$this->createFields($hlblockId);

		return $hlblockId;
	}

	public function uninstall(): void
	{
		if (!Loader::includeModule('highloadblock'))
		{
			return;
		}

		$hlblockId = EquipmentTable::getHlblockId();
		if (!$hlblockId)
		{
			return;
		}

		$entityId = 'HLBLOCK_' . $hlblockId;
		$res = \CUserTypeEntity::GetList([], ['ENTITY_ID' => $entityId]);
		while ($field = $res->Fetch())
		{
			(new CUserTypeEntity())->Delete($field['ID']);
		}

		HighloadBlockTable::delete($hlblockId);
	}

	private function createFields(int $hlblockId): void
	{
		$entityId = 'HLBLOCK_' . $hlblockId;
		$userType = new CUserTypeEntity();

		$nameId = $userType->Add([
			'ENTITY_ID' => $entityId,
			'FIELD_NAME' => EquipmentTable::FIELD_NAME,
			'USER_TYPE_ID' => 'string',
			'XML_ID' => EquipmentTable::FIELD_NAME,
			'SORT' => 100,
			'MULTIPLE' => 'N',
			'MANDATORY' => 'Y',
			'SHOW_FILTER' => 'S',
			'SHOW_IN_LIST' => 'Y',
			'EDIT_IN_LIST' => 'Y',
			'IS_SEARCHABLE' => 'Y',
			'EDIT_FORM_LABEL' => ['ru' => 'Название', 'en' => 'Name'],
			'LIST_COLUMN_LABEL' => ['ru' => 'Название', 'en' => 'Name'],
			'LIST_FILTER_LABEL' => ['ru' => 'Название', 'en' => 'Name'],
		]);

		if (!$nameId)
		{
			global $APPLICATION;
			throw new SystemException($APPLICATION->GetException()?->GetString() ?: 'Failed to create UF_NAME');
		}

		$typeId = $userType->Add([
			'ENTITY_ID' => $entityId,
			'FIELD_NAME' => EquipmentTable::FIELD_TYPE,
			'USER_TYPE_ID' => 'enumeration',
			'XML_ID' => EquipmentTable::FIELD_TYPE,
			'SORT' => 200,
			'MULTIPLE' => 'N',
			'MANDATORY' => 'Y',
			'SHOW_FILTER' => 'I',
			'SHOW_IN_LIST' => 'Y',
			'EDIT_IN_LIST' => 'Y',
			'IS_SEARCHABLE' => 'N',
			'EDIT_FORM_LABEL' => ['ru' => 'Тип оборудования', 'en' => 'Equipment type'],
			'LIST_COLUMN_LABEL' => ['ru' => 'Тип оборудования', 'en' => 'Equipment type'],
			'LIST_FILTER_LABEL' => ['ru' => 'Тип оборудования', 'en' => 'Equipment type'],
		]);

		if (!$typeId)
		{
			global $APPLICATION;
			throw new SystemException($APPLICATION->GetException()?->GetString() ?: 'Failed to create UF_TYPE');
		}

		$enum = new CUserFieldEnum();
		$enum->SetEnumValues($typeId, [
			'n0' => [
				'XML_ID' => EquipmentTable::TYPE_ROUTER,
				'VALUE' => 'Роутер',
				'DEF' => 'N',
				'SORT' => 100,
			],
			'n1' => [
				'XML_ID' => EquipmentTable::TYPE_LAPTOP,
				'VALUE' => 'Ноутбук',
				'DEF' => 'N',
				'SORT' => 200,
			],
			'n2' => [
				'XML_ID' => EquipmentTable::TYPE_MONITOR,
				'VALUE' => 'Монитор',
				'DEF' => 'N',
				'SORT' => 300,
			],
		]);

		$createdId = $userType->Add([
			'ENTITY_ID' => $entityId,
			'FIELD_NAME' => EquipmentTable::FIELD_CREATED_TIME,
			'USER_TYPE_ID' => 'datetime',
			'XML_ID' => EquipmentTable::FIELD_CREATED_TIME,
			'SORT' => 300,
			'MULTIPLE' => 'N',
			'MANDATORY' => 'N',
			'SHOW_FILTER' => 'E',
			'SHOW_IN_LIST' => 'Y',
			'EDIT_IN_LIST' => 'Y',
			'IS_SEARCHABLE' => 'N',
			'EDIT_FORM_LABEL' => ['ru' => 'Дата создания', 'en' => 'Created at'],
			'LIST_COLUMN_LABEL' => ['ru' => 'Дата создания', 'en' => 'Created at'],
			'LIST_FILTER_LABEL' => ['ru' => 'Дата создания', 'en' => 'Created at'],
			'SETTINGS' => [
				'DEFAULT_VALUE' => [
					'TYPE' => 'NOW',
					'VALUE' => '',
				],
			],
		]);

		if (!$createdId)
		{
			global $APPLICATION;
			throw new SystemException($APPLICATION->GetException()?->GetString() ?: 'Failed to create UF_CREATED_TIME');
		}

		$dealId = $userType->Add([
			'ENTITY_ID' => $entityId,
			'FIELD_NAME' => EquipmentTable::FIELD_DEAL_ID,
			'USER_TYPE_ID' => 'integer',
			'XML_ID' => EquipmentTable::FIELD_DEAL_ID,
			'SORT' => 400,
			'MULTIPLE' => 'N',
			'MANDATORY' => 'Y',
			'SHOW_FILTER' => 'I',
			'SHOW_IN_LIST' => 'Y',
			'EDIT_IN_LIST' => 'Y',
			'IS_SEARCHABLE' => 'N',
			'EDIT_FORM_LABEL' => ['ru' => 'ID сделки', 'en' => 'Deal ID'],
			'LIST_COLUMN_LABEL' => ['ru' => 'ID сделки', 'en' => 'Deal ID'],
			'LIST_FILTER_LABEL' => ['ru' => 'ID сделки', 'en' => 'Deal ID'],
		]);

		if (!$dealId)
		{
			global $APPLICATION;
			throw new SystemException($APPLICATION->GetException()?->GetString() ?: 'Failed to create UF_DEAL_ID');
		}
	}

	/**
	 * Возвращает ID значений списка UF_TYPE по XML_ID.
	 *
	 * @return array<string, int>
	 */
	public function getTypeEnumMap(): array
	{
		$hlblockId = EquipmentTable::getHlblockId();
		if (!$hlblockId)
		{
			return [];
		}

		$field = \CUserTypeEntity::GetList([], [
			'ENTITY_ID' => 'HLBLOCK_' . $hlblockId,
			'FIELD_NAME' => EquipmentTable::FIELD_TYPE,
		])->Fetch();

		if (!$field)
		{
			return [];
		}

		$map = [];
		$enum = new CUserFieldEnum();
		$res = $enum->GetList([], ['USER_FIELD_ID' => $field['ID']]);
		while ($item = $res->Fetch())
		{
			$map[(string)$item['XML_ID']] = (int)$item['ID'];
		}

		return $map;
	}
}
