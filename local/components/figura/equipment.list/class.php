<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Grid\Options as GridOptions;
use Figura\Equipment\Hlblock\EquipmentRepository;
use Figura\Equipment\Hlblock\EquipmentTable;

Loc::loadMessages(__FILE__);

/**
 * Компонент списка оборудования из HL-блока.
 * Использует main.ui.grid и main.ui.filter, фильтрует по UF_DEAL_ID.
 */
class FiguraEquipmentListComponent extends CBitrixComponent implements Controllerable
{
	private const GRID_ID = 'figura_equipment_list';
	private const FILTER_ID = 'figura_equipment_filter';

	private EquipmentRepository $repository;

	public function configureActions(): array
	{
		return [];
	}

	public function onPrepareComponentParams($arParams): array
	{
		$arParams['DEAL_ID'] = (int)($arParams['DEAL_ID'] ?? 0);
		$arParams['CACHE_TIME'] = (int)($arParams['CACHE_TIME'] ?? 0);

		return $arParams;
	}

	public function executeComponent(): void
	{
		try
		{
			$this->checkModules();
			$this->repository = new EquipmentRepository();

			$this->arResult['GRID_ID'] = self::GRID_ID . '_' . $this->arParams['DEAL_ID'];
			$this->arResult['FILTER_ID'] = self::FILTER_ID . '_' . $this->arParams['DEAL_ID'];
			$this->arResult['DEAL_ID'] = $this->arParams['DEAL_ID'];
			$this->arResult['COLUMNS'] = $this->getColumns();
			$this->arResult['FILTER_FIELDS'] = $this->getFilterFields();

			[$filter, $nav, $sort] = $this->prepareFilterAndNav();
			$rows = $this->repository->getList(
				$filter,
				$sort,
				$nav->getLimit(),
				$nav->getOffset()
			);

			$total = $this->repository->getCount($filter);
			$nav->setRecordCount($total);

			$this->arResult['ROWS'] = $this->prepareRows($rows);
			$this->arResult['NAV_OBJECT'] = $nav;
			$this->arResult['TOTAL_COUNT'] = $total;
			$this->arResult['ERROR'] = null;
		}
		catch (\Throwable $e)
		{
			$this->arResult['ERROR'] = $e->getMessage();
			$this->arResult['ROWS'] = [];
			$this->arResult['COLUMNS'] = $this->getColumns();
			$this->arResult['FILTER_FIELDS'] = $this->getFilterFields();
			$this->arResult['GRID_ID'] = self::GRID_ID;
			$this->arResult['FILTER_ID'] = self::FILTER_ID;
			$this->arResult['NAV_OBJECT'] = null;
			$this->arResult['TOTAL_COUNT'] = 0;
		}

		$this->includeComponentTemplate();
	}

	private function checkModules(): void
	{
		if (!Loader::includeModule('figura.equipment'))
		{
			throw new \RuntimeException('Module figura.equipment is not installed');
		}

		if (!Loader::includeModule('highloadblock'))
		{
			throw new \RuntimeException('Module highloadblock is not installed');
		}
	}

	private function getColumns(): array
	{
		return [
			['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => true],
			['id' => 'NAME', 'name' => 'Название', 'sort' => EquipmentTable::FIELD_NAME, 'default' => true],
			['id' => 'TYPE_LABEL', 'name' => 'Тип оборудования', 'sort' => EquipmentTable::FIELD_TYPE, 'default' => true],
			['id' => 'CREATED_TIME', 'name' => 'Дата создания', 'sort' => EquipmentTable::FIELD_CREATED_TIME, 'default' => true],
			['id' => 'DEAL_ID', 'name' => 'ID сделки', 'sort' => EquipmentTable::FIELD_DEAL_ID, 'default' => true],
		];
	}

	private function getFilterFields(): array
	{
		$typeItems = [];
		foreach (EquipmentTable::getTypeLabels() as $xmlId => $label)
		{
			$typeItems[$xmlId] = $label;
		}

		return [
			['id' => 'NAME', 'name' => 'Название', 'type' => 'string', 'default' => true],
			[
				'id' => 'TYPE',
				'name' => 'Тип оборудования',
				'type' => 'list',
				'items' => $typeItems,
				'params' => ['multiple' => 'Y'],
				'default' => true,
			],
			[
				'id' => 'CREATED_TIME',
				'name' => 'Дата создания',
				'type' => 'date',
				'default' => true,
			],
		];
	}

	/**
	 * @return array{0: array, 1: PageNavigation, 2: array}
	 */
	private function prepareFilterAndNav(): array
	{
		$dealId = (int)$this->arParams['DEAL_ID'];
		if ($dealId <= 0)
		{
			throw new \InvalidArgumentException('Параметр DEAL_ID обязателен');
		}

		$gridId = $this->arResult['GRID_ID'];
		$filterId = $this->arResult['FILTER_ID'];

		$filterOption = new FilterOptions($filterId);
		$filterData = $filterOption->getFilter($this->arResult['FILTER_FIELDS']);

		$runtimeFilter = [
			EquipmentTable::FIELD_DEAL_ID => $dealId,
		];

		if (!empty($filterData['NAME']))
		{
			$runtimeFilter['%' . EquipmentTable::FIELD_NAME] = $filterData['NAME'];
		}

		if (!empty($filterData['FIND']))
		{
			$runtimeFilter['%' . EquipmentTable::FIELD_NAME] = $filterData['FIND'];
		}

		if (!empty($filterData['TYPE']) && is_array($filterData['TYPE']))
		{
			$enumMap = (new \Figura\Equipment\Install\HlblockInstaller())->getTypeEnumMap();
			$enumIds = [];
			foreach ($filterData['TYPE'] as $xmlId)
			{
				if (isset($enumMap[$xmlId]))
				{
					$enumIds[] = $enumMap[$xmlId];
				}
			}
			if ($enumIds)
			{
				$runtimeFilter['@' . EquipmentTable::FIELD_TYPE] = $enumIds;
			}
		}

		if (!empty($filterData['CREATED_TIME_from']))
		{
			$runtimeFilter['>=' . EquipmentTable::FIELD_CREATED_TIME] = $filterData['CREATED_TIME_from'];
		}
		if (!empty($filterData['CREATED_TIME_to']))
		{
			$runtimeFilter['<=' . EquipmentTable::FIELD_CREATED_TIME] = $filterData['CREATED_TIME_to'];
		}

		$gridOptions = new GridOptions($gridId);
		$sorting = $gridOptions->getSorting([
			'sort' => ['ID' => 'DESC'],
			'vars' => ['by' => 'by', 'order' => 'order'],
		]);
		$sort = $sorting['sort'];

		$navParams = $gridOptions->getNavParams(['nPageSize' => 10]);
		$nav = new PageNavigation($gridId);
		$nav->allowAllRecords(false)
			->setPageSize((int)$navParams['nPageSize'])
			->initFromUri();

		return [$runtimeFilter, $nav, $sort];
	}

	private function prepareRows(array $items): array
	{
		$rows = [];
		foreach ($items as $item)
		{
			$rows[] = [
				'id' => $item['ID'],
				'data' => $item,
				'columns' => [
					'ID' => $item['ID'],
					'NAME' => htmlspecialcharsbx($item['NAME']),
					'TYPE_LABEL' => htmlspecialcharsbx($item['TYPE_LABEL']),
					'CREATED_TIME' => htmlspecialcharsbx($item['CREATED_TIME']),
					'DEAL_ID' => $item['DEAL_ID'],
				],
			];
		}

		return $rows;
	}
}
