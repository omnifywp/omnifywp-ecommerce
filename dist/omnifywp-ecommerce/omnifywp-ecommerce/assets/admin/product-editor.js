
			// Multistep Wizard Logic
			(function() {
				const initialStep = Math.max(1, Math.min(4, parseInt(new URLSearchParams(window.location.search).get('wizard_step') || '1', 10) || 1));
				let currentStep = initialStep;
				const totalSteps = 4;

				const panels = document.querySelectorAll('.omnify-wizard-panel');
				const steps = document.querySelectorAll('.omnify-wizard-step');
				const prevBtn = document.getElementById('wizard-prev');
				const nextBtn = document.getElementById('wizard-next');
				const saveBtn = document.getElementById('wizard-save');
				const progressBars = document.querySelectorAll('#omnify-wizard-progress, #omnify-wizard-progress-nav');
				const stepText = document.getElementById('wizard-step-text');

				function updateWizard() {
					panels.forEach(p => p.classList.remove('active'));
					const activePanel = document.querySelector('.omnify-wizard-panel[data-panel="' + currentStep + '"]');
					if (activePanel) activePanel.classList.add('active');

					steps.forEach((s, idx) => {
						s.classList.remove('active', 'completed');
						const stepNum = idx + 1;
						if (stepNum < currentStep) s.classList.add('completed');
						if (stepNum === currentStep) s.classList.add('active');
					});

					const pct = Math.round((currentStep / totalSteps) * 100);
					progressBars.forEach((progress) => {
						progress.style.width = pct + '%';
					});
					if (stepText) stepText.textContent = currentStep + ' / ' + totalSteps;

					prevBtn.disabled = currentStep === 1;
					if (currentStep === totalSteps) {
						nextBtn.style.setProperty('display', 'none', 'important');
						saveBtn.style.setProperty('display', 'inline-flex', 'important');
					} else {
						nextBtn.style.setProperty('display', 'inline-flex', 'important');
						saveBtn.style.setProperty('display', 'none', 'important');
					}
				}

				function initStepEditors(step) {
					if (typeof tinymce === 'undefined') return;

					if (step === 3) {
						document.querySelectorAll('.omnify-var-description-textarea').forEach(function(ta) {
							if (!ta.id) {
								ta.id = 'var-desc-' + Math.random().toString(36).substr(2, 9);
							}
							if (!tinymce.get(ta.id)) {
								tinymce.init({
									selector: '#' + ta.id,
									menubar: false,
									branding: false,
									plugins: 'lists link',
									toolbar: 'bold italic underline | bullist numlist | link',
									height: 100,
									setup: function(editor) {
										editor.on('change keyup', function() {
											editor.save();
										});
									}
								});
							}
						});
					}

					if (step === 4) {
						const step4Textareas = [
							'#refund_policy_text',
							'#delivery_info',
							'#return_info',
							'#trust_badge_text_simple'
						];
						step4Textareas.forEach(function(sel) {
							const ta = document.querySelector(sel);
							if (ta && !tinymce.get(ta.id)) {
								tinymce.init({
									selector: sel,
									menubar: false,
									branding: false,
									plugins: 'lists link',
									toolbar: 'bold italic underline | bullist numlist | link',
									height: 120,
									setup: function(editor) {
										editor.on('change keyup', function() {
											editor.save();
										});
									}
								});
							}
						});
					}
				}

				function showWizardError(message) {
					const alertBox = document.getElementById('omnify-product-form-alert');
					if (alertBox) {
						alertBox.textContent = message;
						alertBox.hidden = false;
						alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
					}
				}

				function clearWizardError() {
					const alertBox = document.getElementById('omnify-product-form-alert');
					if (alertBox) {
						alertBox.hidden = true;
						alertBox.textContent = '';
					}
				}

				function validateCurrentStep() {
					if (typeof tinymce !== 'undefined') {
						tinymce.triggerSave();
					}
					
					if (currentStep === 1) {
						const nameInput = document.getElementById('name');
						if (nameInput && !nameInput.value.trim()) {
							showWizardError((window.omnifyProductL10n?.titleRequired || 'Product title is required.'));
							return false;
						}
					}
					
					if (currentStep === 3) {
						const descInput = document.getElementById('description');
						if (descInput) {
							const cleanDesc = descInput.value.replace(/<[^>]*>/g, '').trim();
							if (!cleanDesc) {
								showWizardError((window.omnifyProductL10n?.descRequired || 'Product description is required.'));
								return false;
							}
						}
					}
					
					clearWizardError();
					return true;
				}

				function goToStep(step) {
					if (step > currentStep) {
						for (let s = currentStep; s < step; s++) {
							const originalStep = currentStep;
							currentStep = s;
							const isValid = validateCurrentStep();
							currentStep = originalStep;
							if (!isValid) {
								return;
							}
						}
					}
					currentStep = Math.max(1, Math.min(totalSteps, step));
					updateWizard();
					initStepEditors(currentStep);
				}

				steps.forEach(s => {
					s.addEventListener('click', () => {
						const target = parseInt(s.getAttribute('data-step') || '1', 10);
						goToStep(target);
					});
				});

				prevBtn && prevBtn.addEventListener('click', () => goToStep(currentStep - 1));
				nextBtn && nextBtn.addEventListener('click', () => goToStep(currentStep + 1));

				// Keyboard support
				document.addEventListener('keydown', function(e) {
					if (e.key === 'Enter' && e.target.id === 'name') {
						e.preventDefault();
						goToStep(currentStep + 1);
					}
				});

				// Initialize
				updateWizard();
				initStepEditors(currentStep);

				const wizardForm = document.getElementById('omnify-product-wizard-form');
				if (wizardForm) {
					wizardForm.addEventListener('submit', function(e) {
						if (typeof tinymce !== 'undefined') {
							tinymce.triggerSave();
						}
						
						// Run final validation on submit
						const nameInput = document.getElementById('name');
						if (nameInput && !nameInput.value.trim()) {
							e.preventDefault();
							showWizardError((window.omnifyProductL10n?.titleRequired || 'Product title is required.'));
							goToStep(1);
							return false;
						}
						
						const descInput = document.getElementById('description');
						if (descInput) {
							const cleanDesc = descInput.value.replace(/<[^>]*>/g, '').trim();
							if (!cleanDesc) {
								e.preventDefault();
								showWizardError((window.omnifyProductL10n?.descRequired || 'Product description is required.'));
								goToStep(3);
								return false;
							}
						}
					});
				}

				// Re-apply product kind/structure visibility when changing steps
				const origGo = window.OmnifyWizard ? window.OmnifyWizard.goToStep : null;
				// Expose for debugging
				window.OmnifyWizard = { goToStep: function(s) { if (origGo) origGo(s); else goToStep(s); if (typeof toggleProductFields === 'function') toggleProductFields(); } };

				if (typeof toggleProductFields === 'function') {
					// ensure initial
					setTimeout(toggleProductFields, 50);
				}
			})();
			


			document.addEventListener('DOMContentLoaded', function() {
				const typeSelect = document.getElementById('type');
				const variationKindInput = document.getElementById('variation_product_kind');
				const productKindInputs = document.querySelectorAll('input[name="product_kind"]');
				const productStructureInputs = document.querySelectorAll('input[name="product_structure"]');
				const digitalSections = document.querySelectorAll('.omnify-digital-product-section');
				const inventoryCard = document.getElementById('omnify-inventory-card');
				const variationsCard = document.getElementById('omnify-variations-card');
				const dimensionsRow = document.getElementById('omnify-dimensions-row');
				const manageStock = document.getElementById('manage_stock');
				const stockQtyGroup = document.getElementById('omnify-stock-qty-group');
				const preorderEnabled = document.getElementById('preorder_enabled');
				const preorderFields = document.getElementById('omnify-preorder-fields');
				const contentPanel = document.getElementById('omnify-content-panel');
				const contentCards = [
					document.getElementById('omnify-product-media-card'),
					document.getElementById('omnify-product-categories-card'),
					document.getElementById('omnify-product-tags-card'),
					document.getElementById('omnify-product-brands-card'),
					document.getElementById('omnify-simple-trust-message-card')
				].filter(Boolean);
				const swatchStyleSelect = document.getElementById('variation_selector_style');
				const swatchSettingFields = document.querySelectorAll('.omnify-swatch-setting-field');

				const simpleTrustCard = document.getElementById('omnify-simple-trust-message-card');
				const simpleTrustTitle = document.getElementById('trust_badge_title_simple');
				const simpleTrustText = document.getElementById('trust_badge_text_simple');

				const defaultPhysicalTitle = (window.omnifyProductL10n?.defaultPhysicalTitle || 'Secure Delivery');
				const defaultPhysicalText  = (window.omnifyProductL10n?.defaultPhysicalText || 'Order confirmed. Shipped fast.');
				const defaultDigitalTitle  = (window.omnifyProductL10n?.defaultDigitalTitle || 'Instant Secure Access');
				const defaultDigitalText   = (window.omnifyProductL10n?.defaultDigitalText || 'Files available for download immediately.');

				function checkedValue(inputs, fallback) {
					const checked = Array.from(inputs).find(input => input.checked);
					return checked ? checked.value : fallback;
				}

				function moveContentCardsIntoContentStep() {
					if (!contentPanel) return;
					const taxonomyRow = document.getElementById('omnify-taxonomy-row');
					contentCards.forEach(function(card) {
						if (card.id === 'omnify-product-categories-card' || card.id === 'omnify-product-brands-card' || card.id === 'omnify-product-tags-card') {
							if (taxonomyRow && card.parentElement !== taxonomyRow) {
								taxonomyRow.appendChild(card);
							}
						} else {
							if (card.parentElement !== contentPanel) {
								contentPanel.appendChild(card);
							}
						}
					});
				}

				function toggleSwatchSettings() {
					const showSwatches = swatchStyleSelect && swatchStyleSelect.value === 'swatches';
					swatchSettingFields.forEach(function(field) {
						field.style.display = showSwatches ? '' : 'none';
						field.querySelectorAll('input, select, textarea').forEach(function(input) {
							input.disabled = !showSwatches;
						});
					});
				}

				function toggleProductFields() {
					const productKind = checkedValue(productKindInputs, 'digital');
					const productStructure = checkedValue(productStructureInputs, 'simple');
					const isPhysical = productKind === 'physical';
					const isBundle = productKind === 'bundle';
					if (variationKindInput) variationKindInput.value = productKind;

					// Hide/show sales type group if bundle
					const salesTypeGroup = productStructureInputs[0] ? productStructureInputs[0].closest('.omnify-form-group') : null;
					if (isBundle) {
						productStructureInputs.forEach(input => {
							if (input.value === 'simple') {
								input.checked = true;
							}
						});
						if (salesTypeGroup) salesTypeGroup.style.display = 'none';
					} else {
						if (salesTypeGroup) salesTypeGroup.style.display = 'block';
					}

					// Update isVariable since we might have forced it to simple
					const updatedStructure = checkedValue(productStructureInputs, 'simple');
					const updatedIsVariable = updatedStructure === 'variable';
					const canonicalType = updatedIsVariable ? 'variable' : (isBundle ? 'bundle' : (isPhysical ? 'physical' : 'download'));
					if (typeSelect) typeSelect.value = canonicalType;

					digitalSections.forEach(function(section) {
						section.style.display = (isPhysical || isBundle) ? 'none' : 'block';
					});
					const bundleCard = document.getElementById('omnify-bundle-products-card');
					if (bundleCard) {
						bundleCard.style.display = isBundle ? 'block' : 'none';
					}
					if (inventoryCard) inventoryCard.style.display = isPhysical ? 'block' : 'none';
					if (dimensionsRow) dimensionsRow.style.display = isPhysical ? 'block' : 'none';
					if (variationsCard) variationsCard.style.display = updatedIsVariable ? 'block' : 'none';

					// Storefront Trust Message Sync & Toggle
					const currentDefaultTitle = isPhysical ? defaultPhysicalTitle : defaultDigitalTitle;
					const currentDefaultText  = isPhysical ? defaultPhysicalText : defaultDigitalText;

					if (simpleTrustTitle) simpleTrustTitle.placeholder = currentDefaultTitle;
					if (simpleTrustText) simpleTrustText.placeholder = currentDefaultText;

					if (simpleTrustCard) simpleTrustCard.style.display = 'block';
					if (simpleTrustTitle) simpleTrustTitle.disabled = false;
					if (simpleTrustText) simpleTrustText.disabled = false;

					moveContentCardsIntoContentStep();
					toggleSwatchSettings();
				}

				// Initial call for conditionals (kind/structure)
				toggleProductFields();

				productKindInputs.forEach(input => input.addEventListener('change', toggleProductFields));
				productStructureInputs.forEach(input => input.addEventListener('change', toggleProductFields));
				if (swatchStyleSelect) {
					swatchStyleSelect.addEventListener('change', toggleSwatchSettings);
				}

				// Also re-apply when navigating to step 4 in wizard
				const origUpdate = window.OmnifyWizard && window.OmnifyWizard.updateWizard || null;

				if (manageStock && stockQtyGroup) {
					manageStock.addEventListener('change', function() {
						stockQtyGroup.style.display = manageStock.checked ? 'block' : 'none';
					});
				}
				if (preorderEnabled && preorderFields) {
					preorderEnabled.addEventListener('change', function() {
						preorderFields.style.display = preorderEnabled.checked ? 'block' : 'none';
					});
				}

				const refundEnabled = document.getElementById('refund_enabled');
				const refundFields = document.getElementById('omnify-refund-policy-fields');
				if (refundEnabled && refundFields) {
					refundEnabled.addEventListener('change', function() {
						refundFields.style.display = refundEnabled.checked ? 'block' : 'none';
					});
				}

				// --- Bidirectional Sync functions for categories/tags ---
				function syncCheckboxesFromInput(inputId, checkboxClass) {
					const input = document.getElementById(inputId);
					if (!input) return;
					const current = input.value.split(',').map(s => s.trim()).filter(s => s !== '');
					const lowerCurrent = current.map(s => s.toLowerCase());
					
					document.querySelectorAll(checkboxClass).forEach(function(cb) {
						const name = cb.getAttribute('data-name');
						cb.checked = lowerCurrent.includes(name.toLowerCase());
					});
				}

				function syncInputFromCheckbox(inputId, checkboxClass) {
					const input = document.getElementById(inputId);
					if (!input) return;
					let current = input.value.split(',').map(s => s.trim()).filter(s => s !== '');
					
					document.querySelectorAll(checkboxClass).forEach(function(cb) {
						const name = cb.getAttribute('data-name');
						const lowerName = name.toLowerCase();
						const index = current.findIndex(item => item.toLowerCase() === lowerName);
						
						if (cb.checked) {
							if (index === -1) {
								current.push(name);
							}
						} else {
							if (index !== -1) {
								current.splice(index, 1);
							}
						}
					});
					input.value = current.join(', ');
				}

				// --- Quick Add Category/Tag Term ---
				document.querySelectorAll('.js-quick-add-term').forEach(function(btn) {
					btn.addEventListener('click', function(e) {
						e.preventDefault();
						const targetId = btn.getAttribute('data-target');
						const val = btn.getAttribute('data-val');
						const input = document.getElementById(targetId);
						if (! input) return;
						let current = input.value.split(',').map(s => s.trim()).filter(s => s !== '');
						if (! current.includes(val)) {
							current.push(val);
							input.value = current.join(', ');
							input.dispatchEvent(new Event('input'));
						}
					});
				});

				// --- Category/Tag Checkbox Change Listeners ---
				document.querySelectorAll('.js-global-cat-checkbox').forEach(function(cb) {
					cb.addEventListener('change', function() {
						syncInputFromCheckbox('categories', '.js-global-cat-checkbox');
					});
				});
				document.querySelectorAll('.js-global-tag-checkbox').forEach(function(cb) {
					cb.addEventListener('change', function() {
						syncInputFromCheckbox('tags', '.js-global-tag-checkbox');
					});
				});

				document.querySelectorAll('.js-global-brand-checkbox').forEach(function(cb) {
					cb.addEventListener('change', function() {
						syncInputFromCheckbox('brands', '.js-global-brand-checkbox');
					});
				});

				// --- Category/Tag Input Sync Listeners ---
				const catInput = document.getElementById('categories');
				if (catInput) {
					catInput.addEventListener('input', function() {
						syncCheckboxesFromInput('categories', '.js-global-cat-checkbox');
					});
					syncCheckboxesFromInput('categories', '.js-global-cat-checkbox');
				}
				const tagInput = document.getElementById('tags');
				if (tagInput) {
					tagInput.addEventListener('input', function() {
						syncCheckboxesFromInput('tags', '.js-global-tag-checkbox');
					});
					syncCheckboxesFromInput('tags', '.js-global-tag-checkbox');
				}

				const brandInput = document.getElementById('brands');
				if (brandInput) {
					brandInput.addEventListener('input', function() {
						syncCheckboxesFromInput('brands', '.js-global-brand-checkbox');
					});
					syncCheckboxesFromInput('brands', '.js-global-brand-checkbox');
				}

				// --- Specs rows (add/remove) ---
				const specsContainer = document.getElementById('omnify-specs-container');
				const addSpecBtn = document.getElementById('omnify-add-spec');
				if (specsContainer && addSpecBtn) {
					function reindexSpecifications() {
						const rows = specsContainer.querySelectorAll('.omnify-spec-row');
						rows.forEach(function(row, idx) {
							row.querySelectorAll('input').forEach(function(input) {
								if (input.name.includes('[key]')) input.name = `specifications[${idx}][key]`;
								if (input.name.includes('[value]')) input.name = `specifications[${idx}][value]`;
							});
						});
					}

					addSpecBtn.addEventListener('click', function() {
						const row = document.createElement('div');
						row.className = 'omnify-spec-row';
						row.style.cssText = 'display:flex; gap:8px; align-items:center;';
						row.innerHTML = `
							<input type="text" name="specifications[][key]" placeholder="Key e.g. Brand, Weight" style="flex:1;" />
							<input type="text" name="specifications[][value]" placeholder="Value" style="flex:2;" />
							<button type="button" class="omnify-spec-remove" style="background:#fee2e2; border:0; color:#b91c1c; padding:2px 6px; border-radius:4px; cursor:pointer;">&times;</button>
						`;
						specsContainer.appendChild(row);
						reindexSpecifications();
					});
					specsContainer.addEventListener('click', function(e) {
						if (e.target.classList.contains('omnify-spec-remove')) {
							e.target.closest('.omnify-spec-row').remove();
							reindexSpecifications();
						}
					});
				}

				// --- Attribute Builder JS ---
				const attrContainer = document.getElementById('omnify-attributes-container');
				const addAttrBtn = document.getElementById('omnify-add-attribute');
				const addGlobalAttrBtn = document.getElementById('omnify-add-global-attribute');
				const globalAttrSelect = document.getElementById('omnify-global-attribute-select');

				function addAttributeRow(name = '', options = '', type = 'button', optionMeta = {}) {
					const index = attrContainer.children.length;
					const row = document.createElement('div');
					row.className = 'omnify-attribute-row';
					row.setAttribute('data-index', index);
					row.style.cssText = 'display:grid; grid-template-columns: 1fr 1fr 2fr auto; gap:12px; align-items:end; background:#ffffff; border:1px solid var(--omnify-gray-200); padding:10px; border-radius:6px;';
					row.innerHTML = `
						<div class="omnify-form-group" style="margin-bottom:0;">
							<label style="font-size:11px; font-weight:600; margin-bottom:4px;">Attribute Name</label>
							<input type="text" name="attributes[${index}][name]" class="omnify-attr-name" placeholder="e.g. Size" />
						</div>
						<div class="omnify-form-group" style="margin-bottom:0;">
							<label style="font-size:11px; font-weight:600; margin-bottom:4px;">Variation Type</label>
							<select name="attributes[${index}][type]" class="omnify-attr-type">
								<option value="button">Buttons</option>
								<option value="dropdown">Dropdown</option>
								<option value="color">Color</option>
								<option value="image">Image</option>
							</select>
							<input type="hidden" name="attributes[${index}][option_meta_json]" class="omnify-attr-option-meta" />
						</div>
						<div class="omnify-form-group" style="margin-bottom:0;">
							<label style="font-size:11px; font-weight:600; margin-bottom:4px;">Options (comma-separated)</label>
							<input type="text" name="attributes[${index}][options]" class="omnify-attr-options" placeholder="e.g. S, M, L" />
						</div>
						<button type="button" class="omnify-button omnify-button--danger omnify-button--sm omnify-remove-attr" style="margin-bottom: 2px;">&times;</button>
					`;
					row.querySelector('.omnify-attr-name').value = name;
					row.querySelector('.omnify-attr-options').value = options;
					row.querySelector('.omnify-attr-type').value = ['button', 'dropdown', 'color', 'image'].includes(type) ? type : 'button';
					row.querySelector('.omnify-attr-option-meta').value = JSON.stringify(optionMeta || {});
					attrContainer.appendChild(row);
				}

				if (addAttrBtn && attrContainer) {
					addAttrBtn.addEventListener('click', function(e) {
						e.preventDefault();
						addAttributeRow('', '');
					});

					attrContainer.addEventListener('click', function(e) {
						if (e.target.classList.contains('omnify-remove-attr')) {
							e.preventDefault();
							e.target.closest('.omnify-attribute-row').remove();
							reindexAttributes();
						}
					});
				}

				if (addGlobalAttrBtn && globalAttrSelect && attrContainer) {
					addGlobalAttrBtn.addEventListener('click', function(e) {
						e.preventDefault();
						const val = globalAttrSelect.value;
						if (!val) {
							alert('Please select a global attribute to add.');
							return;
						}
						try {
							const data = JSON.parse(val);
							const name = data.name || '';
							const options = (data.options || []).join(', ');
							addAttributeRow(name, options, data.type || 'button', data.option_meta || {});
							globalAttrSelect.value = '';
						} catch (err) {
							console.error(err);
						}
					});
				}

				function reindexAttributes() {
					const rows = attrContainer.querySelectorAll('.omnify-attribute-row');
					rows.forEach(function(row, idx) {
						row.setAttribute('data-index', idx);
						row.querySelector('.omnify-attr-name').name = `attributes[${idx}][name]`;
						row.querySelector('.omnify-attr-type').name = `attributes[${idx}][type]`;
						row.querySelector('.omnify-attr-option-meta').name = `attributes[${idx}][option_meta_json]`;
						row.querySelector('.omnify-attr-options').name = `attributes[${idx}][options]`;
					});
				}

				// --- Variations JS ---
				const varContainer = document.getElementById('omnify-variations-container');
				const addVarBtn = document.getElementById('omnify-add-variation');
				const genVarBtn = document.getElementById('omnify-generate-variations');

				if (varContainer) {
					// Toggle Stock Qty / Stock Status for variations
					varContainer.addEventListener('change', function(e) {
						if (e.target.classList.contains('omnify-var-manage-stock')) {
							const row = e.target.closest('.omnify-variation-row');
							const qtyGroup = row.querySelector('.omnify-var-stock-qty-group');
							const statusGroup = row.querySelector('.omnify-var-stock-status-group');
							if (qtyGroup) qtyGroup.style.display = e.target.checked ? 'block' : 'none';
							if (statusGroup) statusGroup.style.display = e.target.checked ? 'none' : 'block';
						}
						if (e.target.classList.contains('omnify-var-preorder-toggle')) {
							const row = e.target.closest('.omnify-variation-row');
							row.querySelectorAll('.omnify-var-preorder-field').forEach(field => {
								field.style.display = e.target.checked ? 'block' : 'none';
							});
						}
					});

					// Remove variation
					varContainer.addEventListener('click', function(e) {
						if (e.target.closest('.omnify-remove-variation')) {
							e.preventDefault();
							if (confirm('Are you sure you want to remove this variation?')) {
								e.target.closest('.omnify-variation-row').remove();
								reindexVariations();
							}
						}
					});
				}

				if (addVarBtn) {
					addVarBtn.addEventListener('click', function(e) {
						e.preventDefault();
						addVariationRow({});
					});
				}

				if (genVarBtn) {
					genVarBtn.addEventListener('click', function(e) {
						e.preventDefault();
						// Read defined attributes & options
						const attrs = [];
						attrContainer.querySelectorAll('.omnify-attribute-row').forEach(function(row) {
							const name = row.querySelector('.omnify-attr-name').value.trim();
							const optsText = row.querySelector('.omnify-attr-options').value;
							const options = optsText.split(',').map(s => s.trim()).filter(s => s !== '');
							if (name !== '' && options.length > 0) {
								attrs.push({ name: name, options: options });
							}
						});

						if (attrs.length === 0) {
							alert('Please define at least one attribute with options first.');
							return;
						}

						// Generate combinations
						const combinations = cartesianProduct(attrs.map(a => a.options.map(o => ({ attr: a.name, val: o }))));
						
						if (combinations.length === 0) return;

						if (confirm(`Generate ${combinations.length} variations? This will clear any unsaved variations.`)) {
							varContainer.innerHTML = '';
							combinations.forEach(function(comb) {
								const selected = {};
								comb.forEach(function(c) {
									selected[c.attr] = c.val;
								});
								addVariationRow({ attributes: selected });
							});
						}
					});
				}

				function cartesianProduct(arr) {
					return arr.reduce((a, b) => a.flatMap(d => b.map(e => [d, e].flat())), [[]]);
				}

				function addVariationRow(data) {
					const index = varContainer.children.length;
					const selectedAttrs = data.attributes || {};
					
					// Get current attributes definition to render select options
					const attrs = [];
					attrContainer.querySelectorAll('.omnify-attribute-row').forEach(function(row) {
						const name = row.querySelector('.omnify-attr-name').value.trim();
						const optsText = row.querySelector('.omnify-attr-options').value;
						const options = optsText.split(',').map(s => s.trim()).filter(s => s !== '');
						if (name !== '' && options.length > 0) {
							attrs.push({ name: name, options: options });
						}
					});

					let selectsHtml = '';
					attrs.forEach(function(attr) {
						const selectedVal = selectedAttrs[attr.name] || '';
						let optionsHtml = `<option value="">Select...</option>`;
						attr.options.forEach(function(opt) {
							optionsHtml += `<option value="${opt}" ${opt === selectedVal ? 'selected' : ''}>${opt}</option>`;
						});
							selectsHtml += `
								<div class="omnify-variation-attribute-control" style="display:flex; align-items:center; gap:4px;">
									<span>${attr.name}:</span>
									<select name="variations[${index}][attributes][${attr.name}]" class="omnify-variation-attr-select" required>
										${optionsHtml}
								</select>
							</div>
						`;
					});

					const row = document.createElement('div');
					row.className = 'omnify-variation-row';
					row.style.cssText = 'background:#ffffff; border:1px solid var(--omnify-gray-200); border-radius:6px; padding:16px; position:relative;';
					row.innerHTML = `
						<input type="hidden" name="variations[${index}][id]" value="" />
							<div class="omnify-variation-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; padding-bottom:8px; border-bottom:1px solid var(--omnify-gray-100);">
								<div class="omnify-variation-attributes" style="display:flex; gap:8px; flex-wrap:wrap; font-weight:600; font-size:12px;">
									${selectsHtml || '<span style="color:red; font-style:italic;">No attributes defined. Define attributes first.</span>'}
								</div>
							<button type="button" class="omnify-button omnify-button--danger omnify-button--sm omnify-remove-variation" style="padding: 2px 8px !important; height: 24px !important; line-height: 22px !important; display: inline-flex; align-items: center; justify-content: center;"><span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px;"></span></button>
						</div>

						<div class="omnify-form-row omnify-form-row--thirds omnify-variation-grid-pricing">
							<div class="omnify-form-group">
								<label style="font-size:11px; font-weight:600; margin-bottom:4px;">SKU</label>
								<input type="text" name="variations[${index}][sku]" value="${data.sku || ''}" placeholder="e.g. SIZE-S" />
							</div>
							<div class="omnify-form-group">
								<label style="font-size:11px; font-weight:600; margin-bottom:4px;">Regular Price (${document.getElementById('price') ? document.querySelector('label[for="price"]').textContent.match(/\(([^)]+)\)/)[1] : '$'}) *</label>
								<input type="number" step="0.01" min="0" name="variations[${index}][price]" value="${data.price || '0.00'}" required />
							</div>
							<div class="omnify-form-group">
								<label style="font-size:11px; font-weight:600; margin-bottom:4px;">Sale Price</label>
								<input type="number" step="0.01" min="0" name="variations[${index}][sale_price]" value="${data.sale_price || ''}" />
							</div>
						</div>

						<div class="omnify-form-row omnify-variation-grid-stock" style="margin-top:10px; align-items: center;">
							<div class="omnify-form-group" style="margin-bottom: 0;">
								<label class="omnify-toggle">
									<input type="checkbox" name="variations[${index}][manage_stock]" class="omnify-var-manage-stock" value="1" />
									<span class="omnify-toggle__label" style="font-size:11px;">Manage stock level</span>
								</label>
							</div>
							<div class="omnify-form-group omnify-var-stock-qty-group" style="margin-bottom: 0; display:none;">
								<label style="font-size:11px; font-weight:600; margin-bottom:4px;">Stock Qty</label>
								<input type="number" name="variations[${index}][stock_qty]" value="0" style="padding: 4px 8px !important; height: 28px !important;" />
							</div>
							<div class="omnify-form-group omnify-var-stock-status-group" style="margin-bottom: 0;">
								<label style="font-size:11px; font-weight:600; margin-bottom:4px;">Stock Status</label>
								<select name="variations[${index}][stock_status]" style="padding: 4px 8px !important; height: 28px !important;">
									<option value="instock">In Stock</option>
									<option value="outofstock">Out of Stock</option>
									<option value="onbackorder">On Backorder</option>
								</select>
							</div>
						</div>

						<div class="omnify-form-row omnify-variation-grid-preorder" style="margin-top:10px; align-items:flex-end;">
							<div class="omnify-form-group" style="margin-bottom:0;">
								<label class="omnify-toggle">
									<input  type="checkbox" name="variations[${index}][allow_backorders]" value="1" />
									<span class="omnify-toggle__label" style="font-size:11px;">Allow backorders</span>
								</label>
							</div>
							<div class="omnify-form-group" style="margin-bottom:0;">
								<label class="omnify-toggle">
									<input type="checkbox" name="variations[${index}][preorder_enabled]" class="omnify-var-preorder-toggle" value="1" />
									<span class="omnify-toggle__label" style="font-size:11px;">Preorder</span>
								</label>
							</div>
							<div class="omnify-form-group omnify-var-preorder-field" style="margin-bottom:0; display:none;">
								<label style="font-size:11px; font-weight:600; margin-bottom:4px;">Release Date</label>
								<input type="date" name="variations[${index}][preorder_release_date]" style="padding: 4px 8px !important; height: 28px !important;" />
							</div>
							<div class="omnify-form-group omnify-var-preorder-field" style="margin-bottom:0; display:none;">
								<label style="font-size:11px; font-weight:600; margin-bottom:4px;">Limit</label>
								<input type="number" min="1" name="variations[${index}][preorder_limit]" style="padding: 4px 8px !important; height: 28px !important;" />
							</div>
							<div class="omnify-form-group omnify-var-preorder-field" style="margin-bottom:0; display:none;">
								<label style="font-size:11px; font-weight:600; margin-bottom:4px;">Message</label>
								<input type="text" name="variations[${index}][preorder_message]" placeholder="Ships when available" style="padding: 4px 8px !important; height: 28px !important;" />
							</div>
						</div>

						<div class="omnify-form-row omnify-variation-grid-media" style="margin-top:10px; gap:12px; align-items:flex-start;">
							<div class="omnify-form-group" style="margin-bottom:0; flex:0 0 auto; width:80px;">
								<label style="font-size:11px; font-weight:600; margin-bottom:4px;">Image</label>
								<input type="hidden" class="omnify-var-thumbnail-id-input" name="variations[${index}][thumbnail_id]" value="" />
								<div class="omnify-var-thumbnail-preview" style="margin-bottom:4px;"><span style="font-size:11px;color:#9ca3af;">No image</span></div>
								<div style="display:flex;gap:4px;flex-direction:column;">
									<button type="button" class="omnify-button omnify-button--secondary omnify-var-pick-image-btn" style="padding:2px 6px !important;font-size:10px;height:auto!important;">Select</button>
									<button type="button" class="omnify-button omnify-button--danger omnify-var-remove-image-btn" style="padding:2px 6px !important;font-size:10px;height:auto!important;">Remove</button>
								</div>
							</div>
							<div class="omnify-form-group" style="margin-bottom:0; flex:1;">
								<label style="font-size:11px; font-weight:600; margin-bottom:4px;">Variation Note</label>
								<textarea name="variations[${index}][description]" class="omnify-var-description-textarea" rows="2" placeholder="e.g. Ships from Japan, limited edition" style="width:100%;font-size:12px;resize:vertical;min-height:54px;"></textarea>
							</div>
						</div>
					`;
					varContainer.appendChild(row);

					if (typeof tinymce !== 'undefined') {
						const ta = row.querySelector('.omnify-var-description-textarea');
						if (ta) {
							ta.id = 'var-desc-' + Math.random().toString(36).substr(2, 9);
							tinymce.init({
								selector: '#' + ta.id,
								menubar: false,
								branding: false,
								plugins: 'lists link',
								toolbar: 'bold italic underline | bullist numlist | link',
								height: 100,
								setup: function(editor) {
									editor.on('change keyup', function() {
										editor.save();
									});
								}
							});
						}
					}
				}

				function reindexVariations() {
					const rows = varContainer.querySelectorAll('.omnify-variation-row');
					rows.forEach(function(row, idx) {
						const hiddenInputs = row.querySelectorAll('input[type="hidden"]');
						hiddenInputs.forEach(function(inp) {
							if (inp.name && inp.name.includes('[id]')) inp.name = `variations[${idx}][id]`;
							if (inp.name && inp.classList.contains('omnify-var-thumbnail-id-input')) inp.name = `variations[${idx}][thumbnail_id]`;
						});
						row.querySelectorAll('.omnify-variation-attr-select').forEach(function(sel) {
							const nameAttr = sel.name;
							const matches = nameAttr.match(/\[attributes\]\[([^\]]+)\]/);
							if (matches && matches[1]) {
								sel.name = `variations[${idx}][attributes][${matches[1]}]`;
							}
						});
						const skuInput = row.querySelector('input[name*="[sku]"]'); if (skuInput) skuInput.name = `variations[${idx}][sku]`;
						const priceInput = row.querySelector('input[name*="[price]"][type="number"]'); if (priceInput && !priceInput.name.includes('[sale_price]')) priceInput.name = `variations[${idx}][price]`;
						const salePriceInput = row.querySelector('input[name*="[sale_price]"]'); if (salePriceInput) salePriceInput.name = `variations[${idx}][sale_price]`;
						const manageStockInput = row.querySelector('input[name*="[manage_stock]"]'); if (manageStockInput) manageStockInput.name = `variations[${idx}][manage_stock]`;
						const stockQtyInput = row.querySelector('input[name*="[stock_qty]"]'); if (stockQtyInput) stockQtyInput.name = `variations[${idx}][stock_qty]`;
						const stockStatusSel = row.querySelector('select[name*="[stock_status]"]'); if (stockStatusSel) stockStatusSel.name = `variations[${idx}][stock_status]`;
						const allowBackordersInput = row.querySelector('input[name*="[allow_backorders]"]'); if (allowBackordersInput) allowBackordersInput.name = `variations[${idx}][allow_backorders]`;
						const preorderEnabledInput = row.querySelector('input[name*="[preorder_enabled]"]'); if (preorderEnabledInput) preorderEnabledInput.name = `variations[${idx}][preorder_enabled]`;
						const preorderDateInput = row.querySelector('input[name*="[preorder_release_date]"]'); if (preorderDateInput) preorderDateInput.name = `variations[${idx}][preorder_release_date]`;
						const preorderLimitInput = row.querySelector('input[name*="[preorder_limit]"]'); if (preorderLimitInput) preorderLimitInput.name = `variations[${idx}][preorder_limit]`;
						const preorderMessageInput = row.querySelector('input[name*="[preorder_message]"]'); if (preorderMessageInput) preorderMessageInput.name = `variations[${idx}][preorder_message]`;
						const descTa = row.querySelector('textarea[name*="[description]"]'); if (descTa) descTa.name = `variations[${idx}][description]`;
					});
				}

				// --- Bundle products search filtering ---
				const bundleSearchInput = document.getElementById('omnify-bundle-search');
				if (bundleSearchInput) {
					bundleSearchInput.addEventListener('input', function(e) {
						const q = e.target.value.toLowerCase();
						document.querySelectorAll('.omnify-bundle-product-item').forEach(function(item) {
							const text = item.textContent.toLowerCase();
							if (text.includes(q)) {
								item.style.display = 'flex';
							} else {
								item.style.display = 'none';
							}
						});
					});
				}

				// --- Upsells search filtering ---
				const upsellSearchInput = document.getElementById('omnify-upsell-search');
				if (upsellSearchInput) {
					upsellSearchInput.addEventListener('input', function(e) {
						const q = e.target.value.toLowerCase();
						document.querySelectorAll('.omnify-upsell-product-item').forEach(function(item) {
							const text = item.textContent.toLowerCase();
							if (text.includes(q)) {
								item.style.display = 'flex';
							} else {
								item.style.display = 'none';
							}
						});
					});
				}

				// --- Cross-sells search filtering ---
				const crosssellSearchInput = document.getElementById('omnify-crosssell-search');
				if (crosssellSearchInput) {
					crosssellSearchInput.addEventListener('input', function(e) {
						const q = e.target.value.toLowerCase();
						document.querySelectorAll('.omnify-crosssell-product-item').forEach(function(item) {
							const text = item.textContent.toLowerCase();
							if (text.includes(q)) {
								item.style.display = 'flex';
							} else {
								item.style.display = 'none';
							}
						});
					});
				}
			});
			


document.addEventListener('DOMContentLoaded', function() {
	const modal = document.getElementById('omnify-quick-edit-modal');
	const form = document.getElementById('omnify-quick-edit-form');
	const closeBtn = modal.querySelector('.omnify-modal-close');
	const cancelBtn = document.getElementById('qe-cancel');

	document.querySelectorAll('.omnify-quick-edit').forEach(btn => {
		btn.addEventListener('click', function(e) {
			e.preventDefault();
			const id = this.dataset.id;
			document.getElementById('qe-id').value = id;
			document.getElementById('qe-name').value = this.dataset.name || '';
			document.getElementById('qe-price').value = this.dataset.price || '';
			document.getElementById('qe-sale-price').value = this.dataset['sale-price'] || '';
			document.getElementById('qe-status').value = this.dataset.status || 'draft';
			document.getElementById('qe-sku').value = this.dataset.sku || '';
			modal.style.display = 'block';
		});
	});

	function closeModal() {
		modal.style.display = 'none';
	}

	closeBtn.addEventListener('click', closeModal);
	cancelBtn.addEventListener('click', closeModal);
	window.addEventListener('click', function(e) {
		if (e.target === modal) closeModal();
	});

	// AJAX submit for quick edit
	if (form) {
		form.addEventListener('submit', function(e) {
			e.preventDefault();
			const formData = new FormData(form);

			const actionUrl = form.getAttribute('action') || ((window.ajaxurl || '').replace('admin-ajax.php', 'admin-post.php'));

			fetch(actionUrl, {
				method: 'POST',
				body: formData,
				credentials: 'same-origin'
			})
			.then(response => {
				if (response.redirected) {
					closeModal();
					window.location.href = response.url;
				} else {
					closeModal();
					location.reload();
				}
			})
			.catch(() => {
				form.submit(); // fallback
			});
		});
	}

	// Bulk select all + count for modern toolbar
	const bulkForm = document.getElementById('omnify-bulk-products-form');
	if (bulkForm) {
		const selectAlls = bulkForm.querySelectorAll('.omnify-bulk-select-all');
		const items = bulkForm.querySelectorAll('input[name="product_ids[]"]');
		const countEl = bulkForm.querySelector('.selected-count');
		const bulkWrapper = bulkForm.querySelector('.omnify-bulk-actions-wrapper');

		function updateCount() {
			if (!countEl) return;
			const checked = bulkForm.querySelectorAll('input[name="product_ids[]"]:checked').length;
			countEl.textContent = checked + ' selected';
			if (bulkWrapper) {
				bulkWrapper.style.display = checked > 0 ? 'inline-flex' : 'none';
			}
			// Sync the state of all "Select All" checkboxes
			selectAlls.forEach(cb => {
				cb.checked = items.length > 0 && checked === items.length;
				cb.indeterminate = checked > 0 && checked < items.length;
			});
		}

		selectAlls.forEach(selectAll => {
			selectAll.addEventListener('change', function() {
				items.forEach(i => i.checked = selectAll.checked);
				updateCount();
			});
		});
		items.forEach(i => i.addEventListener('change', updateCount));
		updateCount();
	}
});
