/**
 * Вставляет неинтерактивное поле «Оборудование» под блоком «Клиент»
 * в карточке сделки CRM.
 */
(function () {
	'use strict';

	function createField(config) {
		var wrap = document.createElement('div');
		wrap.className = 'figura-equipment-deal-field';
		wrap.setAttribute('data-figura-equipment-count', 'Y');
		wrap.setAttribute('title', config.title || '');
		wrap.setAttribute('aria-readonly', 'true');

		if (config.highlight) {
			wrap.classList.add('figura-equipment-deal-field--highlight');
		}

		var label = document.createElement('span');
		label.className = 'figura-equipment-deal-field__label';
		label.textContent = config.label || 'Оборудование';

		var value = document.createElement('span');
		value.className = 'figura-equipment-deal-field__value';
		value.textContent = String(config.count != null ? config.count : 0);

		wrap.appendChild(label);
		wrap.appendChild(value);

		return wrap;
	}

	function findClientSection(root) {
		var selectors = [
			'[data-cid="CLIENT"]',
			'[data-field-tag="CLIENT"]',
			'.crm-entity-widget-content-block[data-cid="CLIENT"]',
			'.ui-entity-editor-section [data-cid="CLIENT"]',
			'.crm-entity-widget-client',
		];

		for (var i = 0; i < selectors.length; i++) {
			var node = root.querySelector(selectors[i]);
			if (node) {
				return node.closest('.crm-entity-widget-content-block, .ui-entity-editor-section-edit, .ui-entity-editor-content-block, .crm-entity-widget') || node;
			}
		}

		// Поиск по тексту заголовка «Клиент»
		var titles = root.querySelectorAll(
			'.crm-entity-widget-content-block-title, .ui-entity-editor-header-title, .ui-entity-editor-block-title'
		);
		for (var j = 0; j < titles.length; j++) {
			var text = (titles[j].textContent || '').trim().toLowerCase();
			if (text === 'клиент' || text.indexOf('клиент') === 0) {
				return titles[j].closest(
					'.crm-entity-widget-content-block, .ui-entity-editor-section-edit, .ui-entity-editor-content-block, .crm-entity-widget'
				);
			}
		}

		return null;
	}

	function insertField(config) {
		if (document.querySelector('[data-figura-equipment-count="Y"]')) {
			return true;
		}

		var root = document.querySelector('.crm-entity-card-container, .crm-entity-section, #crm-entity-details') || document.body;
		var clientSection = findClientSection(root);
		var field = createField(config);

		if (clientSection && clientSection.parentNode) {
			if (clientSection.nextSibling) {
				clientSection.parentNode.insertBefore(field, clientSection.nextSibling);
			} else {
				clientSection.parentNode.appendChild(field);
			}
			return true;
		}

		// Fallback: верх карточки, если блок «Клиент» ещё не отрисован
		var column = root.querySelector('.crm-entity-card-container-row, .crm-entity-card-widget, .ui-entity-editor-column-content');
		if (column) {
			column.insertBefore(field, column.firstChild);
			return true;
		}

		return false;
	}

	function init(config) {
		config = config || {};

		var attempts = 0;
		var maxAttempts = 40;

		var tryInsert = function () {
			if (insertField(config) || attempts >= maxAttempts) {
				return;
			}
			attempts += 1;
			setTimeout(tryInsert, 250);
		};

		tryInsert();

		if (window.BX && BX.addCustomEvent) {
			BX.addCustomEvent('BX.Crm.EntityEditor:onRefreshLayout', function () {
				insertField(config);
			});
			BX.addCustomEvent('SidePanel.Slider:onLoad', function () {
				setTimeout(function () {
					insertField(config);
				}, 300);
			});
		}
	}

	window.BX = window.BX || {};
	BX.FiguraEquipmentDealCard = {
		init: init,
	};
})();
