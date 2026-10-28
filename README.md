# Figura Equipment — тестовое задание Bitrix

Модуль для Bitrix Framework / Bitrix24 CRM: учёт оборудования в HL-блоке, смарт-процесс со связью со сделкой, таблица с фильтром во вкладке СП и счётчик оборудования в карточке сделки.

## Что это

Решение закрывает тестовое задание Bitrix-разработчика:

1. HL-блок «Оборудование» с полями названия, типа, даты создания и ID сделки.
2. Смарт-процесс, связанный со сделкой, плюс тестовые сущности.
3. Компонент `figura:equipment.list` на `main.ui.grid` + `main.ui.filter`.
4. Вкладка «Оборудование» в карточке элемента смарт-процесса.
5. Неинтерактивный счётчик оборудования в карточке сделки (под «Клиент», с подсветкой для пользователя ID = 5).

## Как устроено

| Слой | Назначение |
|------|------------|
| `local/modules/figura.equipment` | Модуль: установщики, репозиторий HL, CRM-обработчики |
| `local/components/figura/equipment.list` | UI-компонент списка + AJAX lazy-load для вкладки |
| `local/js/figura/equipment/deal-card` | JS-расширение поля в карточке сделки |
| `scripts/` | CLI-скрипты установки на портале Bitrix |
| `local/php_interface/init.php` | Подключение модуля |

Поток данных:

```
Сделка (Deal)
   ↑ UF_DEAL_ID
HL-блок Equipment  →  figura:equipment.list  →  вкладка СП
   ↓ count
Карточка сделки (поле под «Клиент»)
```

События регистрируются при установке модуля:

- `crm:onEntityDetailsTabsInitialized` → `TabManager` (вкладка СП)
- `main:OnEpilog` → `DealCardHandler` (скрипт счётчика в сделке)

## Как запустить

Требуется портал Bitrix с модулями `main`, `crm`, `highloadblock`.

1. Скопируйте каталог `local/` в document root сайта (рядом с `/bitrix`).
2. Установите модуль в админке **Настройки → Настройки продукта → Модули → Figura: Оборудование**  
   или выполните скрипты:

```bash
# из document root портала
export BITRIX_DOC_ROOT=/path/to/site

php scripts/01_install_hlblock.php
php scripts/02_install_smart_process.php
php scripts/03_register_events.php
```

3. Откройте созданный элемент смарт-процесса — вкладка **Оборудование**.
4. Откройте тестовую сделку — под блоком **Клиент** отобразится количество оборудования.
5. Войдите пользователем с ID `5`, чтобы увидеть визуальное выделение поля.

Пример вызова компонента вручную:

```php
$APPLICATION->IncludeComponent('figura:equipment.list', '', [
    'DEAL_ID' => 123,
]);
```

## Структура файлов

```
.
├── README.md
├── task.txt
├── scripts/
│   ├── 01_install_hlblock.php
│   ├── 02_install_smart_process.php
│   └── 03_register_events.php
└── local/
    ├── php_interface/init.php
    ├── components/figura/equipment.list/
    │   ├── class.php
    │   ├── lazyload.ajax.php
    │   ├── .parameters.php
    │   ├── .description.php
    │   └── templates/.default/
    ├── js/figura/equipment/deal-card/
    │   ├── config.php
    │   ├── src/
    │   └── dist/deal-card.bundle.js
    └── modules/figura.equipment/
        ├── include.php
        ├── install/
        └── lib/
            ├── Hlblock/          # EquipmentTable, EquipmentRepository
            ├── Install/          # HL / SPA / demo seeders
            ├── Crm/              # TabManager, DealCardHandler
            └── Service/          # EquipmentCounter
```

## Ключевые точки кода (для ревью)

- Компонент списка: `local/components/figura/equipment.list/class.php`
- Вкладка СП: `local/modules/figura.equipment/lib/Crm/TabManager.php` + `lazyload.ajax.php`
- Счётчик в сделке: `DealCardHandler.php` + расширение `figura.equipment.deal-card`
- Подключение скрипта: регистрация `OnEpilog` в `install/index.php`, вызов `Extension::load` в обработчике

## Обоснование решений

- **HL-блок** вместо инфоблока — проще схема и достаточно для справочника оборудования с UF-полями.
- **Relation API + PARENT_ID** для связи СП со сделкой — штатный механизм CRM Dynamic Types.
- **main.ui.grid / main.ui.filter** — требование задания и привычный UX Bitrix24.
- **JS-вставка поля в карточке сделки** — надёжнее кастомного UF для точного размещения «под Клиентом» и read-only поведения без возможности редактирования.
