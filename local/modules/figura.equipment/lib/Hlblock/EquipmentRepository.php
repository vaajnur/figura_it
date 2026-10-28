<?php

namespace Figura\Equipment\Hlblock;

use Bitrix\Main\Type\DateTime;

class EquipmentRepository
{
	public function getList(array $filter = [], array $order = ['ID' => 'DESC'], int $limit = 0, int $offset = 0): array
	{
		$dataClass = EquipmentTable::getDataClass();

		$params = [
			'select' => ['*'],
			'filter' => $filter,
			'order' => $order,
		];

		if ($limit > 0)
		{
			$params['limit'] = $limit;
			$params['offset'] = $offset;
		}

		$rows = $dataClass::getList($params)->fetchAll();

		return array_map([$this, 'normalizeRow'], $rows);
	}

	public function getCount(array $filter = []): int
	{
		$dataClass = EquipmentTable::getDataClass();

		return (int)$dataClass::getCount($filter);
	}

	public function getByDealId(int $dealId, array $extraFilter = []): array
	{
		$filter = array_merge(
			[EquipmentTable::FIELD_DEAL_ID => $dealId],
			$extraFilter
		);

		return $this->getList($filter);
	}

	public function countByDealId(int $dealId): int
	{
		return $this->getCount([
			EquipmentTable::FIELD_DEAL_ID => $dealId,
		]);
	}

	public function add(array $fields): int
	{
		$dataClass = EquipmentTable::getDataClass();

		if (empty($fields[EquipmentTable::FIELD_CREATED_TIME]))
		{
			$fields[EquipmentTable::FIELD_CREATED_TIME] = new DateTime();
		}

		$result = $dataClass::add($fields);
		if (!$result->isSuccess())
		{
			throw new \RuntimeException(implode('; ', $result->getErrorMessages()));
		}

		return (int)$result->getId();
	}

	private function normalizeRow(array $row): array
	{
		$typeXml = (string)($row[EquipmentTable::FIELD_TYPE] ?? '');
		$labels = EquipmentTable::getTypeLabels();

		$created = $row[EquipmentTable::FIELD_CREATED_TIME] ?? null;
		if ($created instanceof DateTime)
		{
			$createdFormatted = $created->toString();
		}
		else
		{
			$createdFormatted = (string)$created;
		}

		return [
			'ID' => (int)$row['ID'],
			'NAME' => (string)($row[EquipmentTable::FIELD_NAME] ?? ''),
			'TYPE' => $typeXml,
			'TYPE_LABEL' => $labels[$typeXml] ?? $typeXml,
			'CREATED_TIME' => $createdFormatted,
			'DEAL_ID' => (int)($row[EquipmentTable::FIELD_DEAL_ID] ?? 0),
		];
	}
}
