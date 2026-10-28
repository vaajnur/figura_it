<?php
/**
 * Подключение модуля figura.equipment.
 * События регистрируются при установке модуля (install/index.php).
 * Файл можно подключить из /local/php_interface/init.php сайта
 * или оставить как точку входа локальной поставки.
 */

use Bitrix\Main\Loader;

if (Loader::includeModule('figura.equipment'))
{
	// Модуль загружен: автозагрузка классов и обработчики событий активны.
}
