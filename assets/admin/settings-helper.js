window.omnifyInitLocationSelects = function(root) {
	const scope = root || document;
	const data = window.omnifyLocationData || { countries: {}, states: {} };

	function option(label, value, selectedValue) {
		const opt = document.createElement('option');
		opt.value = value;
		opt.textContent = label;
		if (String(selectedValue || '') === String(value)) {
			opt.selected = true;
		}
		return opt;
	}

	function refreshStateSelect(countrySelect) {
		const container = countrySelect.closest('tr') || countrySelect.closest('.omnify-modern-form-row') || countrySelect.closest('.omnify-delivery-zone-card') || countrySelect.closest('div');
		const stateSelect = container ? container.querySelector('.omnify-state-select') : null;
		if (!stateSelect) return;

		const selected = stateSelect.dataset.selected || stateSelect.value || '*';
		const country = countrySelect.value || '*';
		const states = country !== '*' && data.states[country] ? data.states[country] : {};
		stateSelect.innerHTML = '';
		stateSelect.appendChild(option(window.omnifySettingsL10n?.allStates || 'All states (*)', '*', selected));
		Object.keys(states).forEach(function(code) {
			stateSelect.appendChild(option(states[code], code, selected));
		});
		stateSelect.dataset.selected = stateSelect.value || '*';
		if (typeof window.omnifyInitSearchableSelect === 'function' && stateSelect.classList.contains('omnify-searchable-select')) {
			window.omnifyInitSearchableSelect(stateSelect);
		}
	}

	scope.querySelectorAll('.omnify-country-select').forEach(function(select) {
		refreshStateSelect(select);
		if (!select.dataset.locationBound) {
			select.addEventListener('change', function() {
				refreshStateSelect(select);
			});
			select.dataset.locationBound = '1';
		}
	});

	scope.querySelectorAll('.omnify-searchable-select').forEach(function(select) {
		if (window.omnifyInitSearchableSelect) {
			window.omnifyInitSearchableSelect(select);
		}
	});
};

document.addEventListener('DOMContentLoaded', function() {
	window.omnifyInitLocationSelects(document);

	// Generic SaaS-style Conditional Fields Controller
	function initConditionalFields() {
		function updateConditions() {
			document.querySelectorAll('[data-cond-field]').forEach(el => {
				const fieldName = el.dataset.condField;
				const expectedVal = el.dataset.condVal;
				
				// Find active input value (radio group checked, checkbox checked, select, or text input)
				let actualVal = '';
				const radioChecked = document.querySelector(`input[name="${fieldName}"]:checked`);
				if (radioChecked) {
					actualVal = radioChecked.value;
				} else {
					const checkbox = document.querySelector(`input[name="${fieldName}"][type="checkbox"]`);
					if (checkbox) {
						actualVal = checkbox.checked ? '1' : '0';
					} else {
						const inputField = document.querySelector(`select[name="${fieldName}"], input[name="${fieldName}"]`);
						if (inputField) {
							actualVal = inputField.value;
						}
					}
				}
				
				const isMatch = String(actualVal) === String(expectedVal);
				if (isMatch) {
					el.style.removeProperty('display');
				} else {
					el.style.setProperty('display', 'none', 'important');
				}
			});
		}

		// Bind change/input listeners to all controlling fields
		document.querySelectorAll('[data-cond-field]').forEach(el => {
			const fieldName = el.dataset.condField;
			document.querySelectorAll(`input[name="${fieldName}"], select[name="${fieldName}"]`).forEach(input => {
				if (!input.dataset.condBound) {
					input.addEventListener('change', updateConditions);
					input.addEventListener('input', updateConditions);
					input.dataset.condBound = '1';
				}
			});
		});

		updateConditions();
	}
	
	initConditionalFields();

	// Countries select options builder helper
	function getCountriesOptionsHtml() {
		let optionsHtml = '';
		const countriesData = window.omnifyLocationData?.countries || {};
		Object.entries(countriesData).forEach(([code, name]) => {
			optionsHtml += `<option value="${code}">${name} (${code})</option>`;
		});
		return optionsHtml;
	}

	// ══ Modern Tax Rules Table Manager ════════════════════════════════
	const taxBody = document.getElementById('omnify-tax-rules-body');
	const addTaxBtn = document.getElementById('omnify-add-tax-rule');
	const taxEmptyState = document.getElementById('omnify-tax-rules-empty');
	const taxTableWrapper = document.getElementById('omnify-tax-table-wrapper');

	function escapeHtml(str) {
		return String(str || '')
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	}

	function refreshTaxRuleIndices() {
		if (!taxBody) return;
		const rows = taxBody.querySelectorAll('tr');

		if (taxEmptyState && taxTableWrapper) {
			if (rows.length === 0) {
				taxEmptyState.style.display = 'block';
				taxTableWrapper.style.display = 'none';
			} else {
				taxEmptyState.style.display = 'none';
				taxTableWrapper.style.display = 'block';
			}
		}

		rows.forEach((tr, idx) => {
			tr.querySelectorAll('input, select').forEach(input => {
				const name = input.getAttribute('name');
				if (name && name.includes('tax_rules[')) {
					const updatedName = name.replace(/tax_rules\[\d+\]/, `tax_rules[${idx}]`);
					input.setAttribute('name', updatedName);
				}
			});
		});
	}

	function addTaxRuleRow(preset = {}) {
		if (!taxBody) return;
		const ruleIndex = taxBody.querySelectorAll('tr').length;
		const countriesHtml = getCountriesOptionsHtml();
		const tr = document.createElement('tr');

		const label = preset.label || '';
		const country = preset.country || '*';
		const state = preset.state || '*';
		const rate = preset.rate !== undefined ? preset.rate : '';
		const priority = preset.priority !== undefined ? preset.priority : 10;
		const code = preset.code || '';

		tr.innerHTML = `
			<td style="text-align: center; vertical-align: middle;">
				<label class="omnify-switch">
					<input type="checkbox" name="tax_rules[${ruleIndex}][enabled]" value="1" checked />
					<span class="omnify-slider"></span>
				</label>
			</td>
			<td><input type="text" name="tax_rules[${ruleIndex}][label]" value="${escapeHtml(label)}" placeholder="e.g. Sales Tax, VAT" style="width: 100%;" required /></td>
			<td>
				<select name="tax_rules[${ruleIndex}][country]" class="omnify-country-select omnify-searchable-select" style="width: 100%;">
					<option value="*" ${country === '*' ? 'selected' : ''}>${window.omnifySettingsL10n?.allCountries || 'All countries (*)'}</option>
					${countriesHtml}
				</select>
			</td>
			<td>
				<select name="tax_rules[${ruleIndex}][state]" class="omnify-state-select omnify-searchable-select" data-selected="${escapeHtml(state)}" style="width: 100%;">
					<option value="*" ${state === '*' ? 'selected' : ''}>${window.omnifySettingsL10n?.allStates || 'All states (*)'}</option>
				</select>
			</td>
			<td>
				<div class="omnify-input-wrapper omnify-input-with-suffix">
					<input type="number" step="0.0001" min="0" max="100" name="tax_rules[${ruleIndex}][rate]" value="${escapeHtml(String(rate))}" placeholder="0.00" style="border: none !important; border-radius: 0 !important; box-shadow: none !important; background: transparent !important; outline: none !important; width: 100%;" required />
					<span class="omnify-input-suffix">%</span>
				</div>
			</td>
			<td><input type="text" name="tax_rules[${ruleIndex}][reporting_code]" value="${escapeHtml(code)}" placeholder="e.g. TAX-STD" style="width: 100%;" /></td>
			<td><input type="number" min="1" name="tax_rules[${ruleIndex}][priority]" value="${escapeHtml(String(priority))}" style="width: 100%;" required /></td>
			<td style="text-align: center;">
				<button type="button" class="omnify-tax-remove-btn omnify-remove-row" title="Delete Rule" aria-label="Delete Rule">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<polyline points="3 6 5 6 21 6"></polyline>
						<path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
					</svg>
				</button>
			</td>
		`;

		taxBody.appendChild(tr);

		if (country && country !== '*') {
			const cSelect = tr.querySelector('.omnify-country-select');
			if (cSelect) cSelect.value = country;
		}

		window.omnifyInitLocationSelects(tr);
		refreshTaxRuleIndices();

		tr.querySelector('input[type="text"]')?.focus();
	}

	if (taxBody) {
		if (addTaxBtn) {
			addTaxBtn.addEventListener('click', () => addTaxRuleRow());
		}

		document.querySelectorAll('.omnify-trigger-add-tax-rule').forEach(btn => {
			btn.addEventListener('click', (e) => {
				e.preventDefault();
				addTaxRuleRow();
			});
		});

		document.querySelectorAll('.omnify-tax-preset-btn').forEach(btn => {
			btn.addEventListener('click', (e) => {
				e.preventDefault();
				addTaxRuleRow({
					label: btn.dataset.presetLabel || '',
					country: btn.dataset.presetCountry || '*',
					state: btn.dataset.presetState || '*',
					rate: btn.dataset.presetRate || '0.00',
					code: btn.dataset.presetCode || '',
					priority: btn.dataset.presetPriority || 10
				});
			});
		});

		taxBody.addEventListener('click', (e) => {
			const removeBtn = e.target.closest('.omnify-remove-row');
			if (removeBtn) {
				const tr = removeBtn.closest('tr');
				if (tr) {
					tr.style.transition = 'all 0.2s ease';
					tr.style.opacity = '0';
					setTimeout(() => {
						tr.remove();
						refreshTaxRuleIndices();
					}, 200);
				}
			}
		});
	}

	// ══ Modern Delivery & Shipping Zones Manager ══════════════════════
	const zonesContainer = document.getElementById('omnify-delivery-zones-container');
	const addZoneBtn = document.getElementById('omnify-add-delivery-zone');
	const emptyState = document.getElementById('omnify-delivery-zones-empty');

	function bindZoneCardEvents(card) {
		if (!card || card.dataset.eventsBound) return;
		card.dataset.eventsBound = '1';

		const nameInput = card.querySelector('.omnify-zone-name-input');
		const titleDisplay = card.querySelector('.omnify-zone-title-display');
		const geoPill = card.querySelector('.omnify-zone-geo-pill');
		const countrySelect = card.querySelector('.omnify-country-select');
		const stateSelect = card.querySelector('.omnify-state-select');
		const methodTypeSelect = card.querySelector('.omnify-method-type-select');
		const rateGroup = card.querySelector('.omnify-zone-rate-group');
		const rateInput = rateGroup ? rateGroup.querySelector('input[type="number"]') : null;
		const enableToggle = card.querySelector('.omnify-zone-enable-toggle');
		const statusLabel = card.querySelector('.omnify-zone-status-label');

		// 1. Live Title Synchronizer
		if (nameInput && titleDisplay) {
			nameInput.addEventListener('input', () => {
				const val = nameInput.value.trim();
				titleDisplay.textContent = val || 'Untitled Zone';
			});
		}

		// 2. Geographic Preview Pill Synchronizer
		function updateGeoPreview() {
			if (!geoPill) return;
			const countryVal = countrySelect ? countrySelect.value : '*';
			const stateVal = stateSelect ? stateSelect.value : '*';

			if (!countryVal || countryVal === '*') {
				geoPill.textContent = 'Worldwide (*)';
				return;
			}

			const countryText = countrySelect.options[countrySelect.selectedIndex]?.text?.split(' (')[0] || countryVal;
			if (!stateVal || stateVal === '*') {
				geoPill.textContent = `${countryText} · All states`;
			} else {
				geoPill.textContent = `${countryText} (${stateVal})`;
			}
		}

		if (countrySelect) countrySelect.addEventListener('change', updateGeoPreview);
		if (stateSelect) stateSelect.addEventListener('change', updateGeoPreview);

		// 3. Method Type Selection Handler
		if (methodTypeSelect && rateGroup) {
			methodTypeSelect.addEventListener('change', () => {
				const isFree = methodTypeSelect.value === 'free_shipping';
				if (isFree) {
					rateGroup.classList.add('is-disabled-rate');
					if (rateInput) rateInput.value = '0.00';
				} else {
					rateGroup.classList.remove('is-disabled-rate');
					if (rateInput && (!rateInput.value || parseFloat(rateInput.value) === 0)) {
						rateInput.value = '5.00';
					}
				}
			});
		}

		// 4. Status Toggle Switch Handler
		if (enableToggle && statusLabel) {
			enableToggle.addEventListener('change', () => {
				const isEnabled = enableToggle.checked;
				statusLabel.textContent = isEnabled ? 'Active' : 'Disabled';
				card.classList.toggle('is-zone-disabled', !isEnabled);
			});
		}
	}

	function refreshZoneIndices() {
		if (!zonesContainer) return;
		const cards = zonesContainer.querySelectorAll('.omnify-delivery-zone-card');

		if (emptyState) {
			emptyState.style.display = cards.length === 0 ? 'block' : 'none';
		}

		cards.forEach((card, idx) => {
			card.dataset.zoneIndex = idx;
			const badge = card.querySelector('.omnify-zone-index-badge');
			if (badge) badge.textContent = `Zone #${idx + 1}`;

			// Re-index all form input names
			card.querySelectorAll('input, select, textarea').forEach(input => {
				const name = input.getAttribute('name');
				if (name && name.includes('delivery_zones[')) {
					const updatedName = name.replace(/delivery_zones\[\d+\]/, `delivery_zones[${idx}]`);
					input.setAttribute('name', updatedName);
				}
			});
		});
	}

	function addNewDeliveryZone() {
		if (!zonesContainer) return;
		const zoneIndex = zonesContainer.querySelectorAll('.omnify-delivery-zone-card').length;
		const countriesHtml = getCountriesOptionsHtml();
		const currencySymbol = document.querySelector('.omnify-input-prefix')?.textContent?.trim() || '$';

		const card = document.createElement('div');
		card.className = 'omnify-delivery-zone-card';
		card.dataset.zoneIndex = zoneIndex;

		card.innerHTML = `
			<div class="omnify-zone-card-header">
				<div class="omnify-zone-card-header-left">
					<span class="omnify-zone-grip-icon" title="Drag to reorder match priority">
						<svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor">
							<circle cx="4" cy="3" r="1.5"/><circle cx="12" cy="3" r="1.5"/>
							<circle cx="4" cy="8" r="1.5"/><circle cx="12" cy="8" r="1.5"/>
							<circle cx="4" cy="13" r="1.5"/><circle cx="12" cy="13" r="1.5"/>
						</svg>
					</span>
					<span class="omnify-zone-index-badge">Zone #${zoneIndex + 1}</span>
					<h4 class="omnify-zone-title-display">New Shipping Zone</h4>
					<span class="omnify-zone-geo-pill" data-role="geo-preview">Worldwide (*)</span>
				</div>
				<div class="omnify-zone-card-header-right">
					<div class="omnify-zone-status-toggle">
						<span class="omnify-zone-status-label">Active</span>
						<label class="omnify-switch">
							<input type="checkbox" name="delivery_zones[${zoneIndex}][enabled]" value="1" checked class="omnify-zone-enable-toggle" />
							<span class="omnify-slider"></span>
						</label>
					</div>
					<button type="button" class="omnify-zone-delete-btn omnify-remove-zone" title="Delete Zone" aria-label="Delete Zone">
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<polyline points="3 6 5 6 21 6"></polyline>
							<path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
							<line x1="10" y1="11" x2="10" y2="17"></line>
							<line x1="14" y1="11" x2="14" y2="17"></line>
						</svg>
					</button>
				</div>
			</div>

			<div class="omnify-zone-card-body">
				<!-- Section 1: Region & Priority -->
				<div class="omnify-zone-field-grid omnify-grid-2">
					<div class="omnify-modern-form-group">
						<label class="omnify-field-label">
							<span>Zone Name</span>
							<span class="omnify-required-asterisk">*</span>
						</label>
						<input type="text" class="omnify-zone-name-input" name="delivery_zones[${zoneIndex}][name]" value="New Shipping Zone" placeholder="e.g. Domestic (United States) or Europe Express" required />
					</div>

					<div class="omnify-modern-form-group">
						<label class="omnify-field-label">
							<span>Match Priority</span>
							<span class="omnify-field-hint">(Lower number = evaluated first)</span>
						</label>
						<input type="number" min="1" max="999" name="delivery_zones[${zoneIndex}][priority]" value="${(zoneIndex + 1) * 10}" />
					</div>
				</div>

				<div class="omnify-zone-field-grid omnify-grid-2" style="margin-top: 14px;">
					<div class="omnify-modern-form-group">
						<label class="omnify-field-label">Country / Region Scope</label>
						<select name="delivery_zones[${zoneIndex}][countries]" class="omnify-country-select omnify-searchable-select" style="width: 100%;">
							<option value="*" selected>All Countries & Regions (*)</option>
							${countriesHtml}
						</select>
					</div>

					<div class="omnify-modern-form-group">
						<label class="omnify-field-label">State / Province Scope</label>
						<select name="delivery_zones[${zoneIndex}][states]" class="omnify-state-select omnify-searchable-select" data-selected="*" style="width: 100%;">
							<option value="*" selected>All States / Provinces (*)</option>
						</select>
					</div>
				</div>

				<!-- Section Divider -->
				<div class="omnify-zone-section-divider">
					<span>Shipping Rates & Methods</span>
				</div>

				<!-- Section 2: Method, Rate & Threshold -->
				<div class="omnify-zone-field-grid omnify-grid-2">
					<div class="omnify-modern-form-group">
						<label class="omnify-field-label">Method Title</label>
						<input type="text" name="delivery_zones[${zoneIndex}][method_name]" value="Standard Delivery" placeholder="e.g. Standard Shipping" />
						<span class="help-text">The shipping method title displayed to shoppers at checkout.</span>
					</div>

					<div class="omnify-modern-form-group">
						<label class="omnify-field-label">Method Type</label>
						<select name="delivery_zones[${zoneIndex}][method_type]" class="omnify-method-type-select">
							<option value="flat_rate">Flat Rate Delivery</option>
							<option value="free_shipping">Free Shipping (Always $0)</option>
							<option value="local_pickup">Local Pickup / Store Collection</option>
						</select>
						<span class="help-text">Select charging model or collection arrangement for this rate.</span>
					</div>
				</div>

				<div class="omnify-zone-field-grid omnify-grid-2" style="margin-top: 14px;">
					<div class="omnify-modern-form-group omnify-zone-rate-group">
						<label class="omnify-field-label">Shipping Cost</label>
						<div class="omnify-input-wrapper omnify-input-with-prefix">
							<span class="omnify-input-prefix">${currencySymbol}</span>
							<input type="number" step="0.01" min="0" name="delivery_zones[${zoneIndex}][cost]" value="5.00" placeholder="0.00" required style="border: none !important; border-radius: 0 !important; box-shadow: none !important; background: transparent !important; outline: none !important;" />
						</div>
						<span class="help-text">Base delivery fee charged on orders in this zone.</span>
					</div>

					<div class="omnify-modern-form-group">
						<label class="omnify-field-label">Free Shipping Order Minimum</label>
						<div class="omnify-input-wrapper omnify-input-with-prefix">
							<span class="omnify-input-prefix">${currencySymbol}</span>
							<input type="number" step="0.01" min="0" name="delivery_zones[${zoneIndex}][free_min]" value="50.00" placeholder="0.00" style="border: none !important; border-radius: 0 !important; box-shadow: none !important; background: transparent !important; outline: none !important;" />
						</div>
						<span class="help-text">Orders at or above this amount unlock free shipping. Set to 0 to disable.</span>
					</div>
				</div>
			</div>
		`;

		zonesContainer.appendChild(card);
		window.omnifyInitLocationSelects(card);
		bindZoneCardEvents(card);
		refreshZoneIndices();

		// Smooth scroll to new card
		card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
		card.querySelector('.omnify-zone-name-input')?.focus();
	}

	if (zonesContainer) {
		// Initialize all pre-existing cards
		zonesContainer.querySelectorAll('.omnify-delivery-zone-card').forEach(bindZoneCardEvents);

		if (addZoneBtn) {
			addZoneBtn.addEventListener('click', addNewDeliveryZone);
		}

		// Also handle header "Add Shipping Zone" buttons
		document.querySelectorAll('.omnify-trigger-add-zone').forEach(btn => {
			btn.addEventListener('click', (e) => {
				e.preventDefault();
				addNewDeliveryZone();
			});
		});

		// Delegate delete button click with smooth fadeout
		zonesContainer.addEventListener('click', (e) => {
			const removeBtn = e.target.closest('.omnify-remove-zone');
			if (removeBtn) {
				const card = removeBtn.closest('.omnify-delivery-zone-card');
				if (card) {
					card.style.transition = 'all 0.2s ease';
					card.style.opacity = '0';
					card.style.transform = 'scale(0.97)';
					setTimeout(() => {
						card.remove();
						refreshZoneIndices();
					}, 200);
				}
			}
		});
	}

	// Custom manual payment methods repeatable adding/removing
	const customPmContainer = document.getElementById('omnify-custom-payment-methods-container');
	const addPmBtn = document.getElementById('omnify-add-custom-pm');
	if (customPmContainer && addPmBtn) {
		addPmBtn.addEventListener('click', () => {
			const uniqueId = 'custom_' + Date.now();
			const card = document.createElement('div');
			card.className = 'omnify-delivery-zone-card omnify-custom-pm-card';
			card.dataset.pmId = uniqueId;
			card.style.cssText = 'grid-column: span 2; border: 1px solid rgba(226, 232, 240, 0.8); padding: 24px; border-radius: 12px; margin-bottom: 16px; background: #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.01); transition: all 0.2s ease;';
			card.innerHTML = `
				<div class="omnify-modern-form-row" style="grid-template-columns: 1fr 1fr; gap: 20px 30px;">
					<div class="omnify-modern-form-group">
						<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
							<div style="display: flex; align-items: center; gap: 10px;">
								<span class="omnify-pm-icon-wrap" style="display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 50%; background: rgba(13, 148, 136, 0.08); color: #0d9488;">
									<span class="dashicons dashicons-admin-generic" style="font-size: 15px; width: 15px; height: 15px;"></span>
								</span>
								<label style="font-size: 14px; font-weight: 600; color: #0f172a; margin: 0;">${window.omnifySettingsL10n?.customPayment || 'Custom / Manual Payment'}</label>
							</div>
							<button type="button" class="omnify-remove-pm-icon-btn omnify-remove-pm" title="${window.omnifySettingsL10n?.delete || 'Delete'}" style="display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.15); color: #ef4444; cursor: pointer; transition: all 0.2s ease; padding: 0;">
								<span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px; display: inline-flex; align-items: center; justify-content: center; margin: 0;"></span>
							</button>
						</div>
						<p style="font-size: 13px; color: #64748b; margin: 0 0 16px 0; line-height: 1.5;">${window.omnifySettingsL10n?.paypalOffline || 'Offline arrangement'}</p>
						
						<div data-cond-field="payment_methods[${uniqueId}][enabled]" data-cond-val="1">
							<label for="pm_name_${uniqueId}">${window.omnifySettingsL10n?.displayName || 'Display Name'}</label>
							<input type="text" id="pm_name_${uniqueId}" name="payment_methods[${uniqueId}][name]" value="${window.omnifySettingsL10n?.manualPayment || 'Manual Payment'}" required />
						</div>
					</div>
					
					<div class="omnify-modern-form-group">
						<label>${window.omnifySettingsL10n?.status || 'Status'}</label>
						<div class="omnify-modern-toggle-wrapper">
							<div class="omnify-yes-no-group">
								<label class="omnify-yes-no-btn">
									<input type="radio" name="payment_methods[${uniqueId}][enabled]" value="1" checked />
									<span>${window.omnifySettingsL10n?.yes || 'Yes'}</span>
								</label>
								<label class="omnify-yes-no-btn">
									<input type="radio" name="payment_methods[${uniqueId}][enabled]" value="0" />
									<span>${window.omnifySettingsL10n?.no || 'No'}</span>
								</label>
							</div>
						</div>
						
						<div data-cond-field="payment_methods[${uniqueId}][enabled]" data-cond-val="1" style="margin-top: 20px; width: 100%;">
							<label for="pm_instr_${uniqueId}">${window.omnifySettingsL10n?.instructions || 'Instructions'}</label>
							<textarea id="pm_instr_${uniqueId}" name="payment_methods[${uniqueId}][instructions]" rows="3"></textarea>
						</div>
					</div>
				</div>
			`;
			customPmContainer.appendChild(card);
			
			// Re-initialize event listeners for new radio toggles
			initConditionalFields();
		});

		customPmContainer.addEventListener('click', (e) => {
			const removeBtn = e.target.closest('.omnify-remove-pm');
			if (removeBtn) {
				const card = removeBtn.closest('.omnify-custom-pm-card');
				if (card) card.remove();
			}
		});
	}

	// Design tab switching logic
	const designTabs = Array.from(document.querySelectorAll('.omnify-settings-section-tab'));
	const designPanels = Array.from(document.querySelectorAll('.omnify-modern-design-panel'));
	if (designTabs.length > 0) {
		designTabs.forEach(tab => {
			tab.addEventListener('click', function(e) {
				if (e) e.preventDefault();
				const activeTab = this.dataset.tab;
				if (!activeTab) return;

				designTabs.forEach(t => t.classList.remove('is-active'));
				designPanels.forEach(p => {
					p.style.display = 'none';
					p.classList.remove('is-active');
				});
				
				this.classList.add('is-active');
				const targetPanel = document.getElementById('omnify-design-panel-' + activeTab);
				if (targetPanel) {
					targetPanel.style.display = 'block';
					targetPanel.classList.add('is-active');
				}
			});
		});
	}

	// ══ PAYMENT GATEWAYS DIRECTORY & ACCORDION CONTROLLER ══════════════
	function initPaymentGatewaysDirectory() {
		const directory = document.querySelector('.omnify-pm-directory');
		if (!directory) return;

		const cards = Array.from(directory.querySelectorAll('.omnify-pm-card'));
		const searchInput = document.getElementById('omnify-pm-search') || document.getElementById('omnify-pm-search-input');
		const filterTabs = Array.from(directory.querySelectorAll('.omnify-pm-tab'));
		const toggleAllBtn = document.getElementById('omnify-pm-expand-all');
		const activeCounter = document.getElementById('omnify-active-pm-count') || document.getElementById('omnify-pm-active-count');

		// 1. Recount Active Gateways
		function updateActiveCount() {
			let activeCount = 0;
			cards.forEach(card => {
				const yesRadio = card.querySelector('.omnify-pm-actions input[type="radio"][value="1"]') || card.querySelector('input[type="radio"][value="1"]');
				const toggleCheck = card.querySelector('.omnify-pm-actions input[type="checkbox"]') || card.querySelector('.omnify-pm-toggle-wrap input[type="checkbox"]');
				const isChecked = (yesRadio && yesRadio.checked) || (toggleCheck && toggleCheck.checked);
				if (isChecked) {
					activeCount++;
					card.classList.add('is-active-gateway', 'is-enabled');
					const badge = card.querySelector('.omnify-pm-status-badge');
					if (badge) {
						badge.textContent = 'Active';
						badge.className = 'omnify-pm-status-badge is-active';
					}
				} else {
					card.classList.remove('is-active-gateway', 'is-enabled');
					const badge = card.querySelector('.omnify-pm-status-badge');
					if (badge) {
						badge.textContent = 'Disabled';
						badge.className = 'omnify-pm-status-badge is-disabled';
					}
				}
			});

			if (activeCounter) {
				activeCounter.textContent = String(activeCount);
			}

			// Update tab counts
			const globalCount = cards.filter(c => (c.dataset.category === 'global' || c.dataset.group === 'global')).length;
			const regionalCount = cards.filter(c => (c.dataset.category === 'regional' || c.dataset.group === 'regional')).length;
			const manualCount = cards.filter(c => (c.dataset.category === 'manual' || c.dataset.category === 'offline' || c.dataset.group === 'manual' || c.dataset.group === 'offline')).length;

			const tabAllCount = directory.querySelector('.omnify-pm-tab[data-filter="all"] .omnify-pm-tab-count, .omnify-pm-tab[data-group="all"] .omnify-pm-tab-count');
			if (tabAllCount) tabAllCount.textContent = String(cards.length);
			const tabGlobalCount = directory.querySelector('.omnify-pm-tab[data-filter="global"] .omnify-pm-tab-count, .omnify-pm-tab[data-group="global"] .omnify-pm-tab-count');
			if (tabGlobalCount) tabGlobalCount.textContent = String(globalCount);
			const tabRegCount = directory.querySelector('.omnify-pm-tab[data-filter="regional"] .omnify-pm-tab-count, .omnify-pm-tab[data-group="regional"] .omnify-pm-tab-count');
			if (tabRegCount) tabRegCount.textContent = String(regionalCount);
			const tabManCount = directory.querySelector('.omnify-pm-tab[data-filter="manual"] .omnify-pm-tab-count, .omnify-pm-tab[data-group="offline"] .omnify-pm-tab-count, .omnify-pm-tab[data-group="manual"] .omnify-pm-tab-count');
			if (tabManCount) tabManCount.textContent = String(manualCount);
		}

		// 2. Accordion Card Header / Configure Click
		cards.forEach(card => {
			const header = card.querySelector('.omnify-pm-header');
			const configBtn = card.querySelector('.omnify-pm-configure-btn, .omnify-pm-collapse-btn');
			const closeBtn = card.querySelector('.omnify-pm-drawer-close');

			function toggleCard(e) {
				// Don't toggle if clicking directly on the toggle switch/radio or button inside the actions
				if (e && (e.target.closest('.omnify-pm-actions .omnify-yes-no-group') || e.target.closest('.omnify-pm-actions input') || e.target.closest('.omnify-pm-toggle-wrap') || e.target.closest('.omnify-toggle-switch'))) {
					return;
				}
				const isExpanded = card.classList.contains('is-expanded');
				if (isExpanded) {
					card.classList.remove('is-expanded');
					if (configBtn) configBtn.setAttribute('aria-expanded', 'false');
				} else {
					card.classList.add('is-expanded');
					if (configBtn) configBtn.setAttribute('aria-expanded', 'true');
				}
			}

			if (header) {
				header.addEventListener('click', toggleCard);
			}
			if (closeBtn) {
				closeBtn.addEventListener('click', (e) => {
					e.preventDefault();
					card.classList.remove('is-expanded');
					if (configBtn) configBtn.setAttribute('aria-expanded', 'false');
				});
			}

			// Listen for toggle switch or radio changes on this card
			card.querySelectorAll('.omnify-pm-actions input, .omnify-pm-header input').forEach(input => {
				input.addEventListener('change', () => {
					updateActiveCount();
					const isEnabled = (input.type === 'checkbox' && input.checked) || (input.type === 'radio' && input.value === '1' && input.checked);
					if (isEnabled) {
						// Auto-expand on enable so user can review/set keys
						card.classList.add('is-expanded');
						if (configBtn) configBtn.setAttribute('aria-expanded', 'true');
					}
				});
			});

			// Listen for Environment Mode changes (Live vs Sandbox)
			const modeSelect = card.querySelector('.omnify-mode-select, select[id$="_mode"]');
			const modeBadge = card.querySelector('.omnify-pm-mode-badge');
			if (modeSelect && modeBadge && !modeBadge.classList.contains('is-offline') && !modeBadge.classList.contains('mode-offline')) {
				modeSelect.addEventListener('change', () => {
					const isLive = modeSelect.value === 'live';
					modeBadge.textContent = isLive ? 'Live' : 'Sandbox';
					modeBadge.className = 'omnify-pm-mode-badge ' + (isLive ? 'is-live' : 'is-test');
				});
			}
		});

		// 3. Category Filter Tabs
		filterTabs.forEach(tab => {
			tab.addEventListener('click', () => {
				filterTabs.forEach(t => t.classList.remove('is-active'));
				tab.classList.add('is-active');

				const filter = tab.dataset.filter || tab.dataset.group || 'all';
				const query = (searchInput ? searchInput.value : '').toLowerCase().trim();

				cards.forEach(card => {
					const cat = card.dataset.category || card.dataset.group || '';
					const text = card.textContent.toLowerCase();
					const matchCat = (filter === 'all' || cat === filter || (filter === 'manual' && cat === 'offline') || (filter === 'offline' && cat === 'manual'));
					const matchSearch = (!query || text.includes(query));

					if (matchCat && matchSearch) {
						card.style.display = '';
					} else {
						card.style.display = 'none';
					}
				});
			});
		});

		// 4. Instant Search Box
		if (searchInput) {
			searchInput.addEventListener('input', () => {
				const query = searchInput.value.toLowerCase().trim();
				const activeTab = directory.querySelector('.omnify-pm-tab.is-active');
				const filter = activeTab ? (activeTab.dataset.filter || activeTab.dataset.group || 'all') : 'all';

				cards.forEach(card => {
					const cat = card.dataset.category || card.dataset.group || '';
					const text = card.textContent.toLowerCase();
					const matchCat = (filter === 'all' || cat === filter || (filter === 'manual' && cat === 'offline') || (filter === 'offline' && cat === 'manual'));
					const matchSearch = (!query || text.includes(query));

					if (matchCat && matchSearch) {
						card.style.display = '';
					} else {
						card.style.display = 'none';
					}
				});
			});
		}

		// 5. Expand All / Collapse All Toggle
		if (toggleAllBtn) {
			let allOpen = false;
			toggleAllBtn.addEventListener('click', (e) => {
				e.preventDefault();
				allOpen = !allOpen;
				cards.forEach(card => {
					if (card.style.display !== 'none') {
						if (allOpen) {
							card.classList.add('is-expanded');
						} else {
							card.classList.remove('is-expanded');
						}
					}
				});
				toggleAllBtn.textContent = allOpen ? 'Collapse All' : 'Expand All';
			});
		}

		// 6. Copy Webhook URL Buttons
		directory.addEventListener('click', (e) => {
			const copyBtn = e.target.closest('.omnify-copy-btn');
			if (copyBtn) {
				e.preventDefault();
				const textToCopy = copyBtn.dataset.copy || '';
				if (textToCopy) {
					if (navigator.clipboard && navigator.clipboard.writeText) {
						navigator.clipboard.writeText(textToCopy).then(showCopied).catch(fallbackCopy);
					} else {
						fallbackCopy();
					}

					function fallbackCopy() {
						const input = copyBtn.closest('.omnify-copy-url-group')?.querySelector('input');
						if (input) {
							input.select();
							document.execCommand('copy');
							showCopied();
						}
					}

					function showCopied() {
						const origText = copyBtn.innerHTML;
						copyBtn.classList.add('is-copied');
						copyBtn.innerHTML = '<span>Copied!</span>';
						setTimeout(() => {
							copyBtn.classList.remove('is-copied');
							copyBtn.innerHTML = origText;
						}, 2000);
					}
				}
			}

			// 7. Password Eye Show/Hide Toggle
			const pwToggle = e.target.closest('.omnify-pw-toggle-btn, .omnify-toggle-pw-btn');
			if (pwToggle) {
				e.preventDefault();
				const wrap = pwToggle.closest('.omnify-password-field-wrap');
				const input = wrap ? wrap.querySelector('input') : null;
				if (input) {
					const isPw = input.type === 'password';
					input.type = isPw ? 'text' : 'password';
					pwToggle.innerHTML = isPw 
						? '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>'
						: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
				}
			}
		});

		// 8. Checkbox Chip Interactive Sync
		directory.addEventListener('change', (e) => {
			const chip = e.target.closest('.omnify-checkbox-chip');
			if (chip && e.target.type === 'checkbox') {
				chip.classList.toggle('is-checked', e.target.checked);
			}
		});

		// Initial count
		updateActiveCount();
	}

	initPaymentGatewaysDirectory();

	// Setting Toggle Cards Dynamic Active State
	document.addEventListener('change', (e) => {
		const card = e.target.closest('.omnify-setting-toggle-card');
		if (card && e.target.type === 'checkbox') {
			card.classList.toggle('is-active', e.target.checked);
		}
	});

	// ══ COLOR PICKER BIDIRECTIONAL SYNC ═══════════════════════════════
	function initColorPickers() {
		document.querySelectorAll('.omnify-color-picker-wrap').forEach(wrap => {
			const colorInput = wrap.querySelector('input[type="color"]');
			const hexInput = wrap.querySelector('.omnify-color-hex-input');
			if (!colorInput || !hexInput) return;

			colorInput.addEventListener('input', () => {
				hexInput.value = colorInput.value.toUpperCase();
			});

			function syncHexToSwatch() {
				let val = hexInput.value.trim();
				if (!val.startsWith('#')) {
					val = '#' + val;
				}
				if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
					colorInput.value = val;
				}
			}

			hexInput.addEventListener('input', syncHexToSwatch);
			hexInput.addEventListener('change', syncHexToSwatch);

			hexInput.addEventListener('blur', () => {
				let val = hexInput.value.trim();
				if (!val.startsWith('#')) {
					val = '#' + val;
				}
				if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
					colorInput.value = val;
					hexInput.value = val.toUpperCase();
				} else {
					hexInput.value = colorInput.value.toUpperCase();
				}
			});
		});
	}

	initColorPickers();

	// Live Checkout & Order Number Preview Handlers
	function initCheckoutPreviewHelpers() {
		const prefixInput = document.getElementById('order_number_prefix');
		const suffixInput = document.getElementById('order_number_suffix');
		const paddingInput = document.getElementById('order_number_padding');
		const sampleEl = document.getElementById('omnify-order-num-sample');

		function updateOrderNumberPreview() {
			if (!sampleEl) return;
			const prefix = prefixInput ? prefixInput.value : 'OMN-';
			const suffix = suffixInput ? suffixInput.value : '';
			const pad = paddingInput ? parseInt(paddingInput.value, 10) || 4 : 4;
			const sampleDigits = String(42).padStart(Math.max(1, Math.min(10, pad)), '0');
			sampleEl.textContent = prefix + sampleDigits + suffix;
		}

		if (prefixInput) prefixInput.addEventListener('input', updateOrderNumberPreview);
		if (suffixInput) suffixInput.addEventListener('input', updateOrderNumberPreview);
		if (paddingInput) paddingInput.addEventListener('input', updateOrderNumberPreview);

		// Order bump live preview
		const bumpTitleInput = document.getElementById('order_bump_title');
		const bumpPriceInput = document.getElementById('order_bump_price');
		const bumpDescInput = document.getElementById('order_bump_desc');
		const bumpTitlePreview = document.getElementById('omnify-bump-title-preview');
		const bumpPricePreview = document.getElementById('omnify-bump-price-preview');
		const bumpDescPreview = document.getElementById('omnify-bump-desc-preview');

		function updateBumpPreview() {
			if (bumpTitlePreview && bumpTitleInput) {
				bumpTitlePreview.textContent = bumpTitleInput.value.trim() || 'VIP Priority Processing';
			}
			if (bumpPricePreview && bumpPriceInput) {
				const symbol = bumpPricePreview.getAttribute('data-symbol') || '$';
				const priceVal = parseFloat(bumpPriceInput.value) || 0;
				bumpPricePreview.textContent = symbol + priceVal.toFixed(2);
			}
			if (bumpDescPreview && bumpDescInput) {
				bumpDescPreview.textContent = bumpDescInput.value.trim() || 'Skip fulfillment line with priority dispatch.';
			}
		}

		if (bumpTitleInput) bumpTitleInput.addEventListener('input', updateBumpPreview);
		if (bumpPriceInput) bumpPriceInput.addEventListener('input', updateBumpPreview);
		if (bumpDescInput) bumpDescInput.addEventListener('input', updateBumpPreview);
	}

	initCheckoutPreviewHelpers();

	// Dynamic Email Token Pill Click-to-Copy
	function initTokenPillCopy() {
		document.addEventListener('click', function(e) {
			const pill = e.target.closest('.omnify-token-pill');
			if (!pill) return;

			const token = pill.getAttribute('data-token') || pill.textContent.trim();
			if (!token) return;

			function showCopiedFeedback() {
				const originalText = pill.textContent;
				pill.classList.add('is-copied');
				pill.textContent = 'Copied!';
				setTimeout(function() {
					pill.textContent = originalText;
					pill.classList.remove('is-copied');
				}, 1200);
			}

			if (navigator.clipboard && window.isSecureContext) {
				navigator.clipboard.writeText(token).then(showCopiedFeedback).catch(function() {
					fallbackCopy(token);
				});
			} else {
				fallbackCopy(token);
			}

			function fallbackCopy(text) {
				const ta = document.createElement('textarea');
				ta.value = text;
				ta.style.position = 'fixed';
				ta.style.left = '-9999px';
				ta.style.top = '0';
				document.body.appendChild(ta);
				ta.focus();
				ta.select();
				try {
					document.execCommand('copy');
					showCopiedFeedback();
				} catch (err) {}
				document.body.removeChild(ta);
			}
		});
	}

	initTokenPillCopy();

	// Modern Email Settings Hub Controller: Filtering, Search, Dynamic Badges, Accordion & Preview Modal
	function initEmailSettingsHub() {
		const toolbar = document.querySelector('.omnify-email-toolbar');
		if (!toolbar) return;

		const filterTabs = document.querySelectorAll('.omnify-email-filter-tab');
		const emailCards = document.querySelectorAll('.omnify-email-card');
		const headings = document.querySelectorAll('.omnify-email-section-heading');
		const logsSection = document.getElementById('omnify-email-logs-section');

		function applyFilters() {
			const activeTab = document.querySelector('.omnify-email-filter-tab.is-active');
			const activeCategory = activeTab ? activeTab.getAttribute('data-email-filter') : 'all';
			const searchInput = document.getElementById('omnify-email-search-input');
			const query = searchInput ? searchInput.value.toLowerCase().trim() : '';

			emailCards.forEach(card => {
				const cardCat = card.getAttribute('data-email-category') || 'customer';
				const cardTitle = (card.querySelector('.omnify-email-card-title')?.textContent || '').toLowerCase();
				const cardSubtitle = (card.querySelector('.omnify-email-card-subtitle')?.textContent || '').toLowerCase();

				let matchesCategory = false;
				if (activeCategory === 'all') {
					matchesCategory = true;
				} else if (activeCategory === 'logs') {
					matchesCategory = false;
				} else {
					matchesCategory = (cardCat === activeCategory);
				}

				let matchesSearch = true;
				if (query) {
					matchesSearch = cardTitle.includes(query) || cardSubtitle.includes(query);
				}

				if (matchesCategory && matchesSearch) {
					card.style.removeProperty('display');
				} else {
					card.style.setProperty('display', 'none', 'important');
				}
			});

			headings.forEach(heading => {
				const headCat = heading.getAttribute('data-email-category') || '';
				if (activeCategory === 'all' || activeCategory === headCat) {
					heading.style.removeProperty('display');
				} else {
					heading.style.setProperty('display', 'none', 'important');
				}
			});

			if (logsSection) {
				if (activeCategory === 'all' || activeCategory === 'logs') {
					logsSection.style.removeProperty('display');
					if (activeCategory === 'logs') {
						logsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
					}
				} else {
					logsSection.style.setProperty('display', 'none', 'important');
				}
			}
		}

		filterTabs.forEach(tab => {
			tab.addEventListener('click', function() {
				filterTabs.forEach(t => t.classList.remove('is-active'));
				tab.classList.add('is-active');
				applyFilters();
			});
		});

		const searchInput = document.getElementById('omnify-email-search-input');
		if (searchInput) {
			searchInput.addEventListener('input', applyFilters);
		}

		// Dynamic Status Badge Updating
		emailCards.forEach(card => {
			const toggleInput = card.querySelector('input[type="checkbox"]');
			const statusBadge = card.querySelector('.omnify-email-card-status-badge');
			if (toggleInput && statusBadge) {
				toggleInput.addEventListener('change', function() {
					if (toggleInput.checked) {
						statusBadge.textContent = 'Active';
						statusBadge.className = 'omnify-email-card-status-badge is-active';
					} else {
						statusBadge.textContent = 'Paused';
						statusBadge.className = 'omnify-email-card-status-badge is-paused';
					}
					updateActiveCountBadge();
				});
			}
		});

		function updateActiveCountBadge() {
			const activeCountEl = document.getElementById('omnify-active-triggers-stat');
			if (activeCountEl) {
				let count = 0;
				emailCards.forEach(c => {
					const cb = c.querySelector('input[type="checkbox"]');
					if (cb && cb.checked) count++;
				});
				activeCountEl.textContent = count + ' Active';
			}
		}

		// Expand / Collapse All
		const expandAllBtn = document.getElementById('omnify-email-expand-all');
		if (expandAllBtn) {
			let isAllExpanded = false;
			expandAllBtn.addEventListener('click', function() {
				isAllExpanded = !isAllExpanded;
				emailCards.forEach(card => {
					if (isAllExpanded) {
						card.classList.remove('is-collapsed');
					} else {
						card.classList.add('is-collapsed');
					}
				});
				expandAllBtn.textContent = isAllExpanded ? 'Collapse All' : 'Expand All';
			});
		}

		// Individual Card Collapse
		document.addEventListener('click', function(e) {
			const collapseBtn = e.target.closest('.omnify-email-card-collapse-btn');
			if (collapseBtn) {
				const card = collapseBtn.closest('.omnify-email-card');
				if (card) {
					card.classList.toggle('is-collapsed');
				}
			}
		});

		// Interactive Preview Modal
		const modal = document.getElementById('omnify-email-preview-modal');
		const modalCloseBtn = document.getElementById('omnify-email-modal-close-btn');
		const modalFrom = document.getElementById('omnify-modal-from');
		const modalSubject = document.getElementById('omnify-modal-subject');
		const modalContent = document.getElementById('omnify-modal-content');
		const modalTitle = document.getElementById('omnify-modal-title');

		function closeModal() {
			if (modal) modal.classList.remove('is-open');
		}

		if (modalCloseBtn) modalCloseBtn.addEventListener('click', closeModal);
		if (modal) {
			modal.addEventListener('click', function(e) {
				if (e.target === modal) closeModal();
			});
		}

		document.addEventListener('click', function(e) {
			const previewBtn = e.target.closest('.omnify-email-preview-btn');
			if (!previewBtn || !modal) return;

			const card = previewBtn.closest('.omnify-email-card');
			if (!card) return;

			const triggerName = card.querySelector('.omnify-email-card-title')?.textContent.trim() || 'Email Preview';
			const subjectInput = card.querySelector('input[name*="subject"]');
			const bodyInput = card.querySelector('textarea');
			const fromName = document.getElementById('email_from_name')?.value || 'Store Support';
			const fromEmail = document.getElementById('email_from_address')?.value || 'store@example.com';

			let subject = subjectInput ? subjectInput.value : triggerName;
			let bodyText = bodyInput ? bodyInput.value : '';

			const storeName = document.querySelector('input[name="store_name"]')?.value || 'Omnify Store';
			const demoTokens = {
				'{site_name}': storeName,
				'{order_id}': '#1048',
				'{order_number}': '#1048',
				'{customer_name}': 'Alex Taylor',
				'{customer_first_name}': 'Alex',
				'{customer_email}': 'alex.taylor@example.com',
				'{order_total}': '$149.00',
				'{order_status}': 'Completed',
				'{old_status}': 'Processing',
				'{new_status}': 'Completed',
				'{refund_amount}': '$49.00',
				'{tracking_carrier}': 'FedEx Express',
				'{tracking_number}': '794642891048',
				'{tracking_url}': '#',
				'{store_name}': storeName,
				'{store_address}': '123 Commerce Way, New York, NY 10001',
				'{store_phone}': '+1 (555) 234-5678',
				'{product_name}': 'Premium Wireless Headphones',
				'{variation}': 'Color: Matte Black, Size: Over-Ear',
				'{sku}': 'WH-1000-BLK',
				'{stock_qty}': '2',
				'{threshold}': '5',
				'{failure_reason}': 'Card was declined by issuing bank (Insufficient funds)',
				'{fraud_score}': '84/100',
				'{fraud_reasons}': 'High order value ($890.00), Disposable email domain flagged, Country mismatch (IP: GB, Billing: US)',
				'{order_note}': 'Your package has been packaged with extra protective padding as requested and dispatched via express courier.',
				'{customer_portal_url}': '#',
				'{portal_url}': '#',
				'{login_url}': '#',
				'{recovery_url}': '#'
			};

			for (const [k, v] of Object.entries(demoTokens)) {
				subject = subject.replaceAll(k, v);
				bodyText = bodyText.replaceAll(k, v);
			}

			if (modalTitle) modalTitle.textContent = triggerName;
			if (modalFrom) modalFrom.textContent = fromName + ' <' + fromEmail + '>';
			if (modalSubject) modalSubject.textContent = subject;

			let html = '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #334155;">';
			html += '<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">';
			html += '<div style="border-bottom: 2px solid #0f766e; padding-bottom: 12px; margin-bottom: 18px; display: flex; justify-content: space-between; align-items: center;">';
			html += '<h2 style="margin: 0; font-size: 20px; color: #0f172a; font-weight: 700;">' + escapeHtml(storeName) + '</h2>';
			html += '<span style="font-size: 12px; color: #64748b;">Order #1048</span>';
			html += '</div>';

			html += '<div style="font-size: 14.5px; line-height: 1.7; color: #1e293b; margin-bottom: 20px;">' + escapeHtml(bodyText).replace(/\n/g, '<br />') + '</div>';

			html += '<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 14px 16px; margin: 18px 0;">';
			html += '<div style="font-weight: 600; color: #0f172a; font-size: 13.5px; margin-bottom: 6px;">Sample Item: Premium Wireless Headphones &times; 1</div>';
			html += '<div style="font-size: 12.5px; color: #64748b;">Subtotal: $140.00 &bull; Shipping: $9.00 &bull; Total: <strong>$149.00</strong></div>';
			html += '</div>';

			const footerText = document.getElementById('email_footer_text')?.value || 'Thank you for shopping with ' + storeName + '.';
			html += '<div style="border-top: 1px solid #e2e8f0; padding-top: 14px; margin-top: 24px; font-size: 12px; color: #94a3b8; text-align: center;">';
			html += escapeHtml(footerText.replaceAll('{site_name}', storeName).replaceAll('{year}', new Date().getFullYear()));
			html += '</div>';

			html += '</div></div>';

			if (modalContent) modalContent.innerHTML = html;
			modal.classList.add('is-open');
		});

		function escapeHtml(str) {
			return String(str).replace(/[&<>"']/g, function(m) {
				return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
			});
		}
	}

	initEmailSettingsHub();

	// ══ GLOBAL SETTINGS SEARCH ════════════════════════════════════════
	function initSettingsSearch() {
		const input = document.getElementById('omnify-settings-search');
		if (!input) return;

		const root = document.querySelector('.omnify-settings-page .omnify-modern-content-card');
		if (!root) return;

		const wrapper = input.closest('.omnify-modern-settings-search');
		const clearBtn = document.querySelector('.omnify-settings-search__clear');
		const countEl = document.querySelector('.omnify-settings-search__count');
		const emptyEl = document.getElementById('omnify-settings-search-empty');
		const resultsEl = document.getElementById('omnify-settings-search-results');
		const L = window.omnifySettingsL10n || {};
		const indexData = window.omnifySettingsSearch || {};
		const indexItems = Array.isArray(indexData.items) ? indexData.items : [];
		const currentSection = new URLSearchParams(window.location.search).get('section') || 'general';
		const MAX_SUGGESTIONS = 10;

		// Smallest user-facing "setting" units across every section layout.
		const UNIT_SELECTOR = [
			'.omnify-modern-form-group',
			'.omnify-setting-toggle-card',
			'.omnify-settings-section-block',
			'.omnify-modern-card-section',
			'.omnify-card-feature-strip',
			'.omnify-feature-trigger-card',
			'.omnify-integration-card',
			'.omnify-checkout-card',
			'.omnify-page-item-card',
			'.omnify-delivery-zone-card',
			'.omnify-capability-box',
			'.omnify-email-card',
			'.omnify-email-metric-card',
			'.omnify-status-override-card',
			'.omnify-tool-card',
			'.omnify-pm-card'
		].join(', ');

		function getUnits() {
			return Array.from(root.querySelectorAll(UNIT_SELECTOR));
		}

		function unitLabel(el) {
			const node = el.querySelector('.omnify-setting-toggle-card__title, .omnify-card-feature-strip-title, .omnify-checkout-card-title, .omnify-page-item-card__title, .omnify-email-card-title, .omnify-pm-title, .omnify-settings-section-divider__title, .omnify-modern-card-title-block h3');
			if (node) return node.textContent.trim();
			const label = el.querySelector('label');
			if (label) return label.textContent.trim();
			const heading = el.querySelector('h4, h3, strong');
			if (heading) return heading.textContent.trim();
			return el.textContent.trim().slice(0, 60);
		}

		// Reveal matches hidden inside collapsed email / gateway cards.
		function revealCollapsed(el) {
			const emailCard = el.closest('.omnify-email-card.is-collapsed');
			if (emailCard && !emailCard.dataset.omnifySearchOpened) {
				emailCard.dataset.omnifySearchOpened = '1';
				emailCard.classList.remove('is-collapsed');
			}
			const pmCard = el.closest('.omnify-pm-card');
			if (pmCard && !pmCard.classList.contains('is-expanded') && !pmCard.dataset.omnifySearchOpened) {
				pmCard.dataset.omnifySearchOpened = '1';
				pmCard.classList.add('is-expanded');
			}
		}

		function restoreCollapsed() {
			root.querySelectorAll('[data-omnify-search-opened]').forEach((card) => {
				delete card.dataset.omnifySearchOpened;
				if (card.classList.contains('omnify-email-card')) card.classList.add('is-collapsed');
				if (card.classList.contains('omnify-pm-card')) card.classList.remove('is-expanded');
			});
		}

		function focusElement(el) {
			if (!el) return;

			// Open the relevant Storefront Design tab if the match lives in a hidden panel.
			const panel = el.closest('.omnify-modern-design-panel');
			if (panel && panel.style.display === 'none') {
				const tabKey = (panel.id || '').replace('omnify-design-panel-', '');
				const tabBtn = document.querySelector('.omnify-settings-section-tab[data-tab="' + tabKey + '"]');
				if (tabBtn) tabBtn.click();
			}

			revealCollapsed(el);
			el.classList.add('omnify-search-focus');
			el.scrollIntoView({ behavior: 'smooth', block: 'center' });
			window.setTimeout(() => el.classList.remove('omnify-search-focus'), 2600);
		}

		function findUnitByLabel(label) {
			const needle = String(label).toLowerCase().trim();
			if (!needle) return null;
			const words = needle.split(/\s+/).filter((w) => w.length > 2);
			let best = null;
			let bestScore = 0;
			getUnits().forEach((el) => {
				const text = unitLabel(el).toLowerCase();
				let score = 0;
				if (text === needle) score = 100;
				else if (text.indexOf(needle) === 0) score = 80;
				else if (text.indexOf(needle) !== -1) score = 60;
				else if (needle.indexOf(text) !== -1 && text.length > 2) score = 40;
				else if (words.length && words.every((w) => el.textContent.toLowerCase().indexOf(w) !== -1)) score = 30;
				if (score > bestScore || (score === bestScore && best && el.contains(best))) {
					bestScore = score;
					best = el;
				}
			});
			return bestScore > 0 ? best : null;
		}

		function matchScore(haystack, needle) {
			if (!haystack) return 0;
			if (haystack === needle) return 3;
			if (haystack.indexOf(needle) === 0) return 2;
			if (haystack.indexOf(needle) !== -1) return 1;
			return 0;
		}

		function buildSuggestions(query) {
			const q = query.toLowerCase();
			const out = [];
			const seen = new Set();

			// Settings rendered on the current page (ground truth, jump in place).
			getUnits().forEach((el) => {
				const label = unitLabel(el);
				if (!label) return;
				const s = matchScore(label.toLowerCase(), q);
				if (s > 0) {
					const key = 'local::' + label.toLowerCase();
					if (!seen.has(key)) {
						seen.add(key);
						out.push({ label: label, group: L.searchThisPage || 'This page', element: el, score: s + 0.5, local: true });
					}
				}
			});

			// Every other settings screen (from the server-side index). The current
			// screen is already covered by the live DOM matches above.
			indexItems.forEach((item) => {
				const labelText = String(item.label || '');
				if (!labelText || item.section === currentSection) return;
				const s = Math.max(
					matchScore(labelText.toLowerCase(), q),
					matchScore(String(item.keywords || '').toLowerCase(), q),
					matchScore(String(item.group || '').toLowerCase(), q)
				);
				if (s > 0) {
					const key = 'global::' + item.section + '::' + labelText.toLowerCase();
					if (!seen.has(key)) {
						seen.add(key);
						out.push({ label: labelText, group: item.group || item.section, section: item.section, score: s, local: false });
					}
				}
			});

			out.sort((a, b) => (b.score - a.score) || (a.local === b.local ? 0 : (a.local ? -1 : 1)) || a.label.localeCompare(b.label));
			return out.slice(0, MAX_SUGGESTIONS);
		}

		let suggestions = [];
		let activeIndex = -1;

		function setActive(i) {
			if (!resultsEl) return;
			const buttons = Array.from(resultsEl.querySelectorAll('.omnify-settings-search__result'));
			buttons.forEach((b, bi) => b.classList.toggle('is-active', bi === i));
			activeIndex = i;
			if (i >= 0 && buttons[i]) buttons[i].scrollIntoView({ block: 'nearest' });
		}

		function hideSuggestions() {
			if (resultsEl) resultsEl.hidden = true;
			suggestions = [];
			activeIndex = -1;
		}

		function navigateTo(section, label) {
			const base = L.settingsUrl || window.location.pathname;
			const sep = base.indexOf('?') === -1 ? '?' : '&';
			window.location.href = base + sep + 'section=' + encodeURIComponent(section) + '&omnify_focus=' + encodeURIComponent(label);
		}

		function choose(item) {
			if (!item) return;
			if (item.local && item.element) {
				focusElement(item.element);
				hideSuggestions();
				input.focus();
			} else {
				navigateTo(item.section, item.label);
			}
		}

		function renderSuggestions(list, query) {
			if (!resultsEl) return;
			resultsEl.innerHTML = '';
			if (!query) {
				hideSuggestions();
				return;
			}
			if (!list.length) {
				const none = document.createElement('div');
				none.className = 'omnify-settings-search__no-results';
				none.textContent = L.searchNoResults || 'No settings match your search.';
				resultsEl.appendChild(none);
				resultsEl.hidden = false;
				suggestions = [];
				activeIndex = -1;
				return;
			}
			list.forEach((item, i) => {
				const btn = document.createElement('button');
				btn.type = 'button';
				btn.className = 'omnify-settings-search__result';
				btn.setAttribute('role', 'option');
				const labelEl = document.createElement('span');
				labelEl.className = 'omnify-settings-search__result-label';
				labelEl.textContent = item.label;
				const groupEl = document.createElement('span');
				groupEl.className = 'omnify-settings-search__result-group';
				groupEl.textContent = item.group;
				btn.appendChild(labelEl);
				btn.appendChild(groupEl);
				btn.addEventListener('mousedown', (e) => {
					e.preventDefault();
					choose(item);
				});
				btn.addEventListener('mouseenter', () => setActive(i));
				resultsEl.appendChild(btn);
			});
			resultsEl.hidden = false;
			setActive(0);
		}

		function reset() {
			getUnits().forEach((el) => el.classList.remove('omnify-search-hidden'));
			restoreCollapsed();
			hideSuggestions();
			if (emptyEl) emptyEl.hidden = true;
			if (countEl) countEl.textContent = '';
			if (clearBtn) clearBtn.hidden = true;
		}

		function apply() {
			const query = input.value.trim().toLowerCase();

			if (!query) {
				reset();
				return;
			}

			if (clearBtn) clearBtn.hidden = false;

			const units = getUnits();
			const visible = [];

			units.forEach((el) => {
				const match = el.textContent.toLowerCase().indexOf(query) !== -1;
				el.classList.toggle('omnify-search-hidden', !match);
				if (match) {
					visible.push(el);
					revealCollapsed(el);
				}
			});

			const anyVisible = visible.length > 0;

			// Count only the deepest visible units so nested cards are not double-counted.
			const leafCount = visible.filter((el) => !visible.some((other) => other !== el && el.contains(other))).length;

			if (emptyEl) emptyEl.hidden = anyVisible;
			if (countEl) {
				countEl.textContent = anyVisible ? (leafCount + ' ' + (leafCount === 1 ? (L.searchResult || 'result') : (L.searchResults || 'results'))) : '';
			}

			suggestions = buildSuggestions(query);
			renderSuggestions(suggestions, query);
		}

		let rafId = 0;
		input.addEventListener('input', () => {
			if (rafId) cancelAnimationFrame(rafId);
			rafId = requestAnimationFrame(apply);
		});
		input.addEventListener('search', apply);
		input.addEventListener('focus', () => {
			if (input.value.trim() && suggestions.length) renderSuggestions(suggestions, input.value.trim().toLowerCase());
		});
		input.addEventListener('keydown', (e) => {
			if (e.key === 'ArrowDown' && suggestions.length) {
				e.preventDefault();
				setActive(Math.min(activeIndex + 1, suggestions.length - 1));
			} else if (e.key === 'ArrowUp' && suggestions.length) {
				e.preventDefault();
				setActive(Math.max(activeIndex - 1, 0));
			} else if (e.key === 'Enter') {
				if (suggestions.length) {
					e.preventDefault();
					choose(suggestions[activeIndex >= 0 ? activeIndex : 0]);
				}
			} else if (e.key === 'Escape') {
				if (resultsEl && !resultsEl.hidden) {
					hideSuggestions();
				} else if (input.value !== '') {
					e.preventDefault();
					input.value = '';
					apply();
				}
			}
		});
		if (clearBtn) {
			clearBtn.addEventListener('click', () => {
				input.value = '';
				apply();
				input.focus();
			});
		}
		document.addEventListener('mousedown', (e) => {
			if (wrapper && !wrapper.contains(e.target)) hideSuggestions();
		});

		// Highlight a setting when arriving from a global search suggestion.
		function handleIncomingFocus() {
			const params = new URLSearchParams(window.location.search);
			const focusLabel = params.get('omnify_focus');
			if (!focusLabel) return;
			params.delete('omnify_focus');
			const qs = params.toString();
			window.history.replaceState({}, '', window.location.pathname + (qs ? '?' + qs : ''));
			const target = findUnitByLabel(focusLabel);
			if (target) window.setTimeout(() => focusElement(target), 160);
		}

		if (L.searchHint) input.title = L.searchHint;

		reset();
		handleIncomingFocus();
	}

	initSettingsSearch();
});

