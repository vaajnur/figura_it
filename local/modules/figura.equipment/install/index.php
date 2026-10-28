<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Figura\Equipment\Install\HlblockInstaller;

Loc::loadMessages(__FILE__);

class figura_equipment extends CModule
{
	public $MODULE_ID = 'figura.equipment';
	public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $MODULE_NAME = 'Figura: Оборудование';
	public $MODULE_DESCRIPTION = 'HL-блок оборудования, вкладка в смарт-процессе и счётчик в карточке сделки';
	public $PARTNER_NAME = 'Figura';
	public $PARTNER_URI = 'https://example.local';

	public function __construct()
	{
		$arModuleVersion = [];
		include __DIR__ . '/version.php';
		$this->MODULE_VERSION = $arModuleVersion['VERSION'];
		$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
	}

	public function DoInstall()
	{
		ModuleManager::registerModule($this->MODULE_ID);
		Loader::includeModule($this->MODULE_ID);

		(new HlblockInstaller())->install();
		$this->InstallEvents();
	}

	public function DoUninstall()
	{
		Loader::includeModule($this->MODULE_ID);
		$this->UnInstallEvents();
		(new HlblockInstaller())->uninstall();
		ModuleManager::unRegisterModule($this->MODULE_ID);
	}

	public function InstallEvents()
	{
		$eventManager = EventManager::getInstance();

		$eventManager->registerEventHandler(
			'crm',
			'onEntityDetailsTabsInitialized',
			$this->MODULE_ID,
			'\\Figura\\Equipment\\Crm\\TabManager',
			'onEntityDetailsTabsInitialized'
		);

		$eventManager->registerEventHandlerCompatible(
			'main',
			'OnEpilog',
			$this->MODULE_ID,
			'\\Figura\\Equipment\\Crm\\DealCardHandler',
			'onEpilog'
		);
	}

	public function UnInstallEvents()
	{
		$eventManager = EventManager::getInstance();

		$eventManager->unRegisterEventHandler(
			'crm',
			'onEntityDetailsTabsInitialized',
			$this->MODULE_ID,
			'\\Figura\\Equipment\\Crm\\TabManager',
			'onEntityDetailsTabsInitialized'
		);

		$eventManager->unRegisterEventHandler(
			'main',
			'OnEpilog',
			$this->MODULE_ID,
			'\\Figura\\Equipment\\Crm\\DealCardHandler',
			'onEpilog'
		);
	}
}
