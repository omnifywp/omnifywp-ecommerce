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
				if (yesRadio && yesRadio.checked) {
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
				// Don't toggle if clicking directly on the Yes/No radio toggle inside the header
				if (e && (e.target.closest('.omnify-pm-actions .omnify-yes-no-group') || e.target.closest('.omnify-pm-actions input') || e.target.closest('.omnify-pm-toggle-wrap'))) {
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

			// Listen for Yes/No radio changes on this card
			card.querySelectorAll('.omnify-pm-actions input[type="radio"], .omnify-pm-header input[type="radio"]').forEach(radio => {
				radio.addEventListener('change', () => {
					updateActiveCount();
					if (radio.value === '1' && radio.checked) {
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
						copyBtn.innerHTML = '<span>✓ Copied!</span>';
						setTimeout(() => {
							copyBtn.classList.remove('is-copied');
							copyBtn.innerHTML = origText;
						}, 2000);
					}
				}
			}

			// 7. Password Eye Show/Hide Toggle
			const pwToggle = e.target.closest('.omnify-pw-toggle-btn');
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

		// Initial count
		updateActiveCount();
	}

	initPaymentGatewaysDirectory();
});
