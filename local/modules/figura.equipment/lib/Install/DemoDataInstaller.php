<?php

namespace Figura\Equipment\Install;

use Bitrix\Main\Type\DateTime;
use Figura\Equipment\Hlblock\EquipmentRepository;
use Figura\Equipment\Hlblock\EquipmentTable;

/**
 * Наполнение HL-блока тестовыми записями оборудования.
 */
class DemoDataInstaller
{
	public function __construct(
		private readonly HlblockInstaller $hlblockInstaller = new HlblockInstaller(),
		private readonly EquipmentRepository $repository = new EquipmentRepository(),
	)
	{
	}

	/**
	 * @param int $dealId ID сделки, к которой привязываются записи
	 * @return int[] созданные ID
	 */
	public function seedForDeal(int $dealId): array
	{
		$typeMap = $this->hlblockInstaller->getTypeEnumMap();
		if (count($typeMap) < 3)
		{
			throw new \RuntimeException('UF_TYPE enumeration is not ready');
		}

		$items = [
			[
				EquipmentTable::FIELD_NAME => 'Cisco ISR 4331',
				EquipmentTable::FIELD_TYPE => $typeMap[EquipmentTable::TYPE_ROUTER],
				EquipmentTable::FIELD_CREATED_TIME => new DateTime('2026-10-01 10:00:00'),
				EquipmentTable::FIELD_DEAL_ID => $dealId,
			],
			[
				EquipmentTable::FIELD_NAME => 'Lenovo ThinkPad T14',
				EquipmentTable::FIELD_TYPE => $typeMap[EquipmentTable::TYPE_LAPTOP],
				EquipmentTable::FIELD_CREATED_TIME => new DateTime('2026-10-05 12:30:00'),
				EquipmentTable::FIELD_DEAL_ID => $dealId,
			],
			[
				EquipmentTable::FIELD_NAME => 'Dell UltraSharp U2720Q',
				EquipmentTable::FIELD_TYPE => $typeMap[EquipmentTable::TYPE_MONITOR],
				EquipmentTable::FIELD_CREATED_TIME => new DateTime('2026-10-10 09:15:00'),
				EquipmentTable::FIELD_DEAL_ID => $dealId,
			],
			[
				EquipmentTable::FIELD_NAME => 'Mikrotik hAP ax3',
				EquipmentTable::FIELD_TYPE => $typeMap[EquipmentTable::TYPE_ROUTER],
				EquipmentTable::FIELD_CREATED_TIME => new DateTime('2026-10-12 16:45:00'),
				EquipmentTable::FIELD_DEAL_ID => $dealId,
			],
			[
				EquipmentTable::FIELD_NAME => 'Apple MacBook Pro 14',
				EquipmentTable::FIELD_TYPE => $typeMap[EquipmentTable::TYPE_LAPTOP],
				EquipmentTable::FIELD_CREATED_TIME => new DateTime('2026-10-20 11:00:00'),
				EquipmentTable::FIELD_DEAL_ID => $dealId,
			],
		];

		$ids = [];
		foreach ($items as $fields)
		{
			$ids[] = $this->repository->add($fields);
		}

		return $ids;
	}
}
