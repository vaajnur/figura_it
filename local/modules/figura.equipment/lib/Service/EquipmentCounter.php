<?php

namespace Figura\Equipment\Service;

use Figura\Equipment\Hlblock\EquipmentRepository;

/**
 * Подсчёт оборудования, привязанного к сделке.
 */
class EquipmentCounter
{
	public function __construct(
		private readonly EquipmentRepository $repository = new EquipmentRepository(),
	)
	{
	}

	public function countByDealId(int $dealId): int
	{
		if ($dealId <= 0)
		{
			return 0;
		}

		return $this->repository->countByDealId($dealId);
	}
}
