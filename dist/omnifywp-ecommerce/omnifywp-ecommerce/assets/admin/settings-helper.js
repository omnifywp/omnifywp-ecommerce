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
			optionsHtml += `<option value="${code}">${name}</option>`;
		});
		return optionsHtml;
	}

	// Tax rules table adding/removing rows dynamically
	const taxBody = document.getElementById('omnify-tax-rules-body');
	const addTaxBtn = document.getElementById('omnify-add-tax-rule');
	if (taxBody && addTaxBtn) {
		let ruleIndex = taxBody.querySelectorAll('tr').length;
		addTaxBtn.addEventListener('click', () => {
			const tr = document.createElement('tr');
			const countriesHtml = getCountriesOptionsHtml();
			tr.innerHTML = `
				<td style="text-align: center; vertical-align: middle;"><input type="checkbox" name="tax_rules[${ruleIndex}][enabled]" value="1" checked /></td>
				<td><input type="text" name="tax_rules[${ruleIndex}][label]" value="" style="width: 100%;" required /></td>
				<td>
					<select name="tax_rules[${ruleIndex}][country]" class="omnify-country-select" style="width: 100%;">
						<option value="*">${window.omnifySettingsL10n?.allCountries || 'All countries (*)'}</option>
						${countriesHtml}
					</select>
				</td>
				<td>
					<select name="tax_rules[${ruleIndex}][state]" class="omnify-state-select" data-selected="*" style="width: 100%;">
						<option value="*">${window.omnifySettingsL10n?.allStates || 'All states (*)'}</option>
					</select>
				</td>
				<td>
					<div class="omnify-input-wrapper">
						<input type="number" step="0.0001" min="0" max="100" name="tax_rules[${ruleIndex}][rate]" value="" style="width: 100%;" required />
						<span class="omnify-input-suffix">%</span>
					</div>
				</td>
				<td><input type="text" name="tax_rules[${ruleIndex}][reporting_code]" value="" style="width: 100%;" /></td>
				<td><input type="number" min="1" name="tax_rules[${ruleIndex}][priority]" value="10" style="width: 100%;" required /></td>
				<td style="text-align: center;"><button type="button" class="omnify-remove-row" style="background: none; border: none; color: #ef4444; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 6px; transition: all 0.2s;"><span class="dashicons dashicons-trash"></span></button></td>
			`;
			taxBody.appendChild(tr);
			window.omnifyInitLocationSelects(tr);
			ruleIndex++;
		});

		taxBody.addEventListener('click', (e) => {
			const removeBtn = e.target.closest('.omnify-remove-row');
			if (removeBtn) {
				const tr = removeBtn.closest('tr');
				if (tr) tr.remove();
			}
		});
	}

	// Delivery zones adding/removing rows dynamically
	const zonesContainer = document.getElementById('omnify-delivery-zones-container');
	const addZoneBtn = document.getElementById('omnify-add-delivery-zone');
	if (zonesContainer && addZoneBtn) {
		let zoneIndex = zonesContainer.querySelectorAll('.omnify-delivery-zone-card').length;
		addZoneBtn.addEventListener('click', () => {
			const card = document.createElement('div');
			card.className = 'omnify-delivery-zone-card';
			card.dataset.zoneIndex = zoneIndex;
			const countriesHtml = getCountriesOptionsHtml();
			card.innerHTML = `
				<div class="omnify-modern-form-row">
					<div class="omnify-modern-form-group">
						<label>${window.omnifySettingsL10n?.zoneName || 'Zone Name'}</label>
						<input type="text" name="delivery_zones[${zoneIndex}][name]" value="" required />
					</div>
					<div class="omnify-modern-form-group">
						<label>${window.omnifySettingsL10n?.status || 'Status'}</label>
						<div class="omnify-modern-toggle-wrapper">
							<div class="omnify-yes-no-group">
								<label class="omnify-yes-no-btn">
									<input type="radio" name="delivery_zones[${zoneIndex}][enabled]" value="1" checked />
									<span>${window.omnifySettingsL10n?.yes || 'Yes'}</span>
								</label>
								<label class="omnify-yes-no-btn">
									<input type="radio" name="delivery_zones[${zoneIndex}][enabled]" value="0" />
									<span>${window.omnifySettingsL10n?.no || 'No'}</span>
								</label>
							</div>
						</div>
					</div>
				</div>

				<div class="omnify-modern-form-row">
					<div class="omnify-modern-form-group">
						<label>${window.omnifySettingsL10n?.countryCode || 'Country Code'}</label>
						<select name="delivery_zones[${zoneIndex}][countries]" class="omnify-country-select" style="width: 100%;">
							<option value="*">${window.omnifySettingsL10n?.allCountries || 'All countries (*)'}</option>
							${countriesHtml}
						</select>
					</div>
					<div class="omnify-modern-form-group">
						<label>${window.omnifySettingsL10n?.stateCode || 'State Code'}</label>
						<select name="delivery_zones[${zoneIndex}][states]" class="omnify-state-select" data-selected="*" style="width: 100%;">
							<option value="*">${window.omnifySettingsL10n?.allStates || 'All states (*)'}</option>
						</select>
					</div>
				</div>

				<div class="omnify-modern-form-row">
					<div class="omnify-modern-form-group">
						<label>${window.omnifySettingsL10n?.rateCost || 'Rate Cost'}</label>
						<input type="number" step="0.01" min="0" name="delivery_zones[${zoneIndex}][rate]" value="" required />
					</div>
					<div class="omnify-modern-form-group" style="justify-content: flex-end; align-items: flex-end;">
						<button type="button" class="button omnify-remove-zone" style="display: inline-flex; align-items: center; gap: 6px; color: #ef4444; border-color: #fca5a5; background: #fff;"><span class="dashicons dashicons-trash" style="font-size: 15px; width: 15px; height: 15px; line-height: 15px; display: inline-flex; align-items: center; justify-content: center; margin: 0;"></span><span>${window.omnifySettingsL10n?.deleteZone || 'Delete Zone'}</span></button>
					</div>
				</div>
			`;
			zonesContainer.appendChild(card);
			window.omnifyInitLocationSelects(card);
			zoneIndex++;
		});

		zonesContainer.addEventListener('click', (e) => {
			const removeBtn = e.target.closest('.omnify-remove-zone');
			if (removeBtn) {
				const card = removeBtn.closest('.omnify-delivery-zone-card');
				if (card) card.remove();
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
	const designTabs = document.querySelectorAll('.omnify-settings-section-tab');
	const designPanels = document.querySelectorAll('.omnify-modern-design-panel');
	designTabs.forEach(tab => {
		tab.addEventListener('click', function() {
			designTabs.forEach(t => t.classList.remove('is-active'));
			designPanels.forEach(p => p.style.display = 'none');
			
			this.classList.add('is-active');
			const activeTab = this.dataset.tab;
			const targetPanel = document.getElementById('omnify-design-panel-' + activeTab);
			if (targetPanel) {
				targetPanel.style.display = 'block';
			}
		});
	});
});
