<?php

use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses('figura.equipment', [
	'Figura\\Equipment\\Hlblock\\EquipmentTable' => 'lib/Hlblock/EquipmentTable.php',
	'Figura\\Equipment\\Hlblock\\EquipmentRepository' => 'lib/Hlblock/EquipmentRepository.php',
	'Figura\\Equipment\\Install\\HlblockInstaller' => 'lib/Install/HlblockInstaller.php',
	'Figura\\Equipment\\Install\\SmartProcessInstaller' => 'lib/Install/SmartProcessInstaller.php',
	'Figura\\Equipment\\Install\\DemoDataInstaller' => 'lib/Install/DemoDataInstaller.php',
	'Figura\\Equipment\\Crm\\TabManager' => 'lib/Crm/TabManager.php',
	'Figura\\Equipment\\Crm\\DealCardHandler' => 'lib/Crm/DealCardHandler.php',
	'Figura\\Equipment\\Service\\EquipmentCounter' => 'lib/Service/EquipmentCounter.php',
]);
