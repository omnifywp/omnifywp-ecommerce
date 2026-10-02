/**
 * Omnify Admin Helper Scripts
 */
// Global helper to initialize a single searchable select
window.omnifyInitSearchableSelect = function(select) {
	// Self-healing: if the select is already wrapped (e.g. from cloning), unwrap it first to register fresh event listeners
	if (select.parentNode && select.parentNode.classList.contains('omnify-custom-select-wrapper')) {
		const wrapper = select.parentNode;
		const parent = wrapper.parentNode;
		if (parent) {
			parent.insertBefore(select, wrapper);
			wrapper.remove();
		}
		select.removeAttribute('data-search-ready');
		delete select.dataset.searchReady;
	}

	if (select.dataset.searchReady) return;
	const isMultiple = select.multiple;

	const wrapper = document.createElement('div');
	wrapper.className = 'omnify-custom-select-wrapper';
	if (isMultiple) {
		wrapper.classList.add('omnify-custom-select-wrapper--multiple');
	}
	select.parentNode.insertBefore(wrapper, select);
	wrapper.appendChild(select);

	const input = document.createElement('input');
	input.type = 'text';
	input.className = 'omnify-custom-select-input';
	input.placeholder = select.getAttribute('placeholder') || select.dataset.placeholder || 'Select option...';
	wrapper.appendChild(input);

	const dropdown = document.createElement('div');
	dropdown.className = 'omnify-custom-select-dropdown';
	wrapper.appendChild(dropdown);

	const empty = document.createElement('div');
	empty.className = 'omnify-custom-select-empty';
	empty.textContent = 'No matches found';
	dropdown.appendChild(empty);

	const customOpt = document.createElement('div');
	customOpt.className = 'omnify-custom-select-option omnify-custom-select-custom-val';
	customOpt.style.display = 'none';
	dropdown.appendChild(customOpt);

	function getSelectedOption() {
		const val = select.value;
		const option = Array.from(select.options).find(o => o.value === val);
		return option || select.options[0];
	}

	function getSelectedOptions() {
		return Array.from(select.options).filter(o => o.selected && o.value !== '');
	}

	function updateInputFromSelect() {
		if (isMultiple) {
			const selected = getSelectedOptions();
			input.value = selected.length ? selected.map(opt => opt.textContent.trim()).join(', ') : '';
			return;
		}
		const opt = getSelectedOption();
		input.value = opt ? opt.textContent.trim() : '';
	}

	function rebuildOptions() {
		dropdown.querySelectorAll('.omnify-custom-select-option:not(.omnify-custom-select-custom-val)').forEach(el => el.remove());

		Array.from(select.options).forEach(opt => {
			const optionDiv = document.createElement('div');
			optionDiv.className = 'omnify-custom-select-option';
			optionDiv.textContent = opt.textContent.trim();
			optionDiv.dataset.value = opt.value;

			if (opt.selected) {
				optionDiv.classList.add('selected');
			}

			optionDiv.addEventListener('mousedown', (e) => {
				e.preventDefault();
				if (isMultiple) {
					opt.selected = !opt.selected;
					select.dispatchEvent(new Event('change', { bubbles: true }));
					select.dispatchEvent(new Event('input', { bubbles: true }));
					rebuildOptions();
					openDropdown();
					input.focus();
					return;
				}
				select.value = opt.value;
				select.dispatchEvent(new Event('change', { bubbles: true }));
				select.dispatchEvent(new Event('input', { bubbles: true }));
				updateInputFromSelect();
				closeDropdown();
			});

			dropdown.insertBefore(optionDiv, customOpt);
		});

		updateInputFromSelect();
	}

	rebuildOptions();

	const observer = new MutationObserver(() => {
		rebuildOptions();
	});
	observer.observe(select, { childList: true });

	function openDropdown() {
		dropdown.style.display = 'block';
		wrapper.classList.add('open');
		dropdown.querySelectorAll('.omnify-custom-select-option').forEach(el => {
			el.classList.remove('hidden');
			if (isMultiple) {
				const option = Array.from(select.options).find(opt => opt.value === el.dataset.value);
				el.classList.toggle('selected', Boolean(option && option.selected));
			} else if (el.dataset.value === select.value) {
				el.classList.add('selected');
			} else {
				el.classList.remove('selected');
			}
		});
		empty.style.display = 'none';
	}

	function closeDropdown() {
		dropdown.style.display = 'none';
		wrapper.classList.remove('open');
	}

	input.addEventListener('focus', () => {
		openDropdown();
		if (isMultiple) {
			input.value = '';
		} else {
			input.select();
		}
	});

	input.addEventListener('blur', () => {
		setTimeout(() => {
			const query = input.value.trim();
			const allowCustomState = false;
			if (allowCustomState && query) {
				const exists = Array.from(select.options).some(o => o.textContent.trim().toLowerCase() === query.toLowerCase() || o.value.toLowerCase() === query.toLowerCase());
				if (!exists) {
					const opt = document.createElement('option');
					opt.value = query;
					opt.textContent = query;
					select.appendChild(opt);
					select.value = query;
					select.dispatchEvent(new Event('change', { bubbles: true }));
					select.dispatchEvent(new Event('input', { bubbles: true }));
					rebuildOptions();
				}
			}
			closeDropdown();
			updateInputFromSelect();
		}, 200);
	});

	customOpt.addEventListener('mousedown', (e) => {
		if (isMultiple) return;
		e.preventDefault();
		const query = input.value.trim();
		if (query) {
			const exists = Array.from(select.options).some(o => o.textContent.trim().toLowerCase() === query.toLowerCase() || o.value.toLowerCase() === query.toLowerCase());
			if (!exists) {
				const opt = document.createElement('option');
				opt.value = query;
				opt.textContent = query;
				select.appendChild(opt);
				select.value = query;
				select.dispatchEvent(new Event('change', { bubbles: true }));
				select.dispatchEvent(new Event('input', { bubbles: true }));
				rebuildOptions();
			} else {
				const matchingOpt = Array.from(select.options).find(o => o.textContent.trim().toLowerCase() === query.toLowerCase() || o.value.toLowerCase() === query.toLowerCase());
				select.value = matchingOpt.value;
				select.dispatchEvent(new Event('change', { bubbles: true }));
				select.dispatchEvent(new Event('input', { bubbles: true }));
			}
			updateInputFromSelect();
			closeDropdown();
		}
	});

	input.addEventListener('input', () => {
		const query = input.value.trim();
		const queryLower = query.toLowerCase();
		let hasMatches = false;
		let exactMatch = false;

		dropdown.querySelectorAll('.omnify-custom-select-option:not(.omnify-custom-select-custom-val)').forEach(el => {
			const text = el.textContent.toLowerCase();
			if (text === queryLower) {
				exactMatch = true;
			}
			if (text.includes(queryLower)) {
				el.classList.remove('hidden');
				hasMatches = true;
			} else {
				el.classList.add('hidden');
			}
		});

		const allowCustomState = false;

		if (allowCustomState && query && !exactMatch) {
			customOpt.textContent = `Use "${query}"`;
			customOpt.style.display = 'block';
			customOpt.classList.remove('hidden');
			hasMatches = true;
		} else {
			customOpt.style.display = 'none';
			customOpt.classList.add('hidden');
		}

		empty.style.display = hasMatches ? 'none' : 'block';
	});

	function syncDisabled() {
		input.disabled = select.disabled;
		if (select.disabled) {
			wrapper.classList.add('disabled');
		} else {
			wrapper.classList.remove('disabled');
		}
	}
	syncDisabled();

	const attrObserver = new MutationObserver((mutations) => {
		mutations.forEach((mutation) => {
			if (mutation.attributeName === 'disabled') {
				syncDisabled();
			}
		});
	});
	attrObserver.observe(select, { attributes: true });

	select.addEventListener('change', () => {
		updateInputFromSelect();
	});

	select.dataset.searchReady = '1';
};

document.addEventListener('DOMContentLoaded', function() {
	if (document.querySelector('.omnify-admin-wrapper')) {
		document.body.classList.add('omnify-admin-page-active');
	}

	function initAdminCancelButtons() {
		const settingsForms = document.querySelectorAll('.omnify-settings-content form');
		settingsForms.forEach(form => {
			const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
			if (!submitButton || form.querySelector('[data-omnify-cancel]')) {
				return;
			}

			const actionRow = submitButton.closest('.omnify-form-actions, .omnify-settings-actions, .omnify-button-group, .omnify-form-footer, p.submit') || submitButton.parentElement;
			if (!actionRow) {
				return;
			}

			const cancelButton = document.createElement('a');
			cancelButton.href = window.location.href;
			cancelButton.className = 'omnify-button omnify-button--secondary omnify-cancel-button';
			cancelButton.dataset.omnifyCancel = '1';
			cancelButton.textContent = 'Cancel';
			actionRow.appendChild(cancelButton);
		});
	}

	function initUnsavedChangesGuard() {
		const message = 'You have unsaved changes. Leave this page without saving?';
		const formSelector = [
			'.omnify-admin-wrapper form[method="post"]',
			'.omnify-product-editor-form',
			'.omnify-settings-content form',
			'.omnify-coupon-form-card form',
			'.omnify-global-attribute-form',
			'#omnify-create-key-form'
		].join(',');
		const excludedSelector = [
			'.omnify-filter-card',
			'form[id^="omnify-bulk-"]',
			'#omnify-test-url-form',
			'#omnify-setup-wizard-form'
		].join(',');
		const guardedForms = Array.from(document.querySelectorAll(formSelector))
			.filter(form => !form.matches(excludedSelector));

		if (!guardedForms.length) {
			return;
		}

		const serializeForm = form => {
			if (window.tinymce && typeof window.tinymce.triggerSave === 'function') {
				window.tinymce.triggerSave();
			}

			const data = [];
			Array.from(form.elements).forEach(field => {
				if (!field.name || field.disabled || field.type === 'file') {
					return;
				}
				if ((field.type === 'checkbox' || field.type === 'radio') && !field.checked) {
					data.push([field.name, '']);
					return;
				}
				if (field.tagName === 'SELECT' && field.multiple) {
					const values = Array.from(field.options).filter(option => option.selected).map(option => option.value);
					data.push([field.name, values.join('|')]);
					return;
				}
				data.push([field.name, field.value]);
			});
			return JSON.stringify(data);
		};

		guardedForms.forEach(form => {
			form.dataset.omnifyInitialState = serializeForm(form);
			form.dataset.omnifyDirty = '0';

			const markDirty = () => {
				if (form.dataset.omnifySubmitting === '1') {
					return;
				}
				form.dataset.omnifyDirty = serializeForm(form) === form.dataset.omnifyInitialState ? '0' : '1';
			};

			form.addEventListener('input', markDirty, true);
			form.addEventListener('change', markDirty, true);
			form.addEventListener('submit', () => {
				form.dataset.omnifySubmitting = '1';
				form.dataset.omnifyDirty = '0';
			});
		});

		const hasDirtyForm = () => guardedForms.some(form => form.dataset.omnifyDirty === '1' && form.dataset.omnifySubmitting !== '1');
		const formIsDirty = form => Boolean(form && form.dataset.omnifyDirty === '1' && form.dataset.omnifySubmitting !== '1');
		const confirmDiscard = () => !hasDirtyForm() || window.confirm(message);

		window.addEventListener('beforeunload', event => {
			if (!hasDirtyForm()) {
				return;
			}
			event.preventDefault();
			event.returnValue = '';
		});

		document.addEventListener('click', event => {
			const submitter = event.target.closest('button[type="submit"], input[type="submit"]');
			if (submitter) {
				return;
			}

			const cancelControl = event.target.closest('a, button, input[type="button"]');
			if (!cancelControl) {
				return;
			}

			const isCancel = /cancel|back/i.test((cancelControl.textContent || cancelControl.value || '').trim())
				|| cancelControl.matches('[data-omnify-cancel], .omnify-cancel, .cancel');
			const link = cancelControl.closest('a[href]');
			const leavesPage = Boolean(link && link.href && link.href !== window.location.href && !link.href.startsWith('#'));
			const dirtyForm = cancelControl.closest(formSelector);

			if ((isCancel && formIsDirty(dirtyForm)) || (leavesPage && hasDirtyForm())) {
				if (!confirmDiscard()) {
					event.preventDefault();
					event.stopImmediatePropagation();
				}
			}
		}, true);
	}

	initUnsavedChangesGuard();
	initAdminCancelButtons();

	function initAdminPushNotifications() {
		const config = window.omnifyAdmin || {};
		if (!config.pushNotificationsEnabled || !('Notification' in window)) return;

		const notice = document.querySelector('.notice, .omnify-notice, #message');
		if (!notice) return;

		const text = notice.textContent.replace(/\s+/g, ' ').trim();
		if (!text) return;

		const showNotification = () => {
			if (Notification.permission === 'granted') {
				new Notification(config.notificationTitle || 'Omnify', { body: text });
			}
		};

		if (Notification.permission === 'default') {
			Notification.requestPermission().then((permission) => {
				if (permission === 'granted') showNotification();
			});
			return;
		}

		showNotification();
	}

	function initConditionalPaymentSettings() {
		function bindGatewayMode(gateway, testSelector, liveSelector) {
			const mode = document.getElementById(`${gateway}_mode`);
			const testFields = document.querySelectorAll(testSelector);
			const liveFields = document.querySelectorAll(liveSelector);
			if (!mode) return;

			const update = () => {
				const showLive = mode.value === 'live';
				testFields.forEach((el) => { el.style.display = showLive ? 'none' : ''; });
				liveFields.forEach((el) => { el.style.display = showLive ? '' : 'none'; });
			};

			mode.addEventListener('change', update);
			update();
		}

		bindGatewayMode('stripe', '[data-stripe-mode-fields="test"]', '[data-stripe-mode-fields="live"]');
		bindGatewayMode('paypal', '[data-paypal-mode-fields="sandbox"]', '[data-paypal-mode-fields="live"]');

		const refundToggle = document.getElementById('refund_enabled');
		const refundFields = document.getElementById('omnify-refund-policy-fields');
		if (refundToggle && refundFields) {
			const updateRefundFields = () => {
				refundFields.style.display = refundToggle.checked ? 'block' : 'none';
			};
			refundToggle.addEventListener('change', updateRefundFields);
			updateRefundFields();
		}
	}

	function initSmartAdminFilters() {
		document.querySelectorAll('.omnify-smart-filters').forEach((form) => {
			const secondary = form.querySelector('.omnify-filter-secondary');
			const toggle = form.querySelector('.omnify-more-filters-toggle');
			if (!secondary || !toggle) return;

			const hasActiveSecondaryFilter = Array.from(secondary.querySelectorAll('input, select')).some((field) => {
				if (field.type === 'hidden') return false;
				if (field.type === 'checkbox' || field.type === 'radio') return field.checked;
				if (field.tagName === 'SELECT') return field.selectedIndex > 0 && String(field.value || '').trim() !== '';
				return String(field.value || '').trim() !== '';
			});

			const setExpanded = (expanded) => {
				secondary.hidden = !expanded;
				form.classList.toggle('is-expanded', expanded);
				toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
				toggle.textContent = expanded ? 'Hide Filters' : 'More Filters';
			};

			toggle.addEventListener('click', () => {
				setExpanded(secondary.hidden);
			});

			setExpanded(hasActiveSecondaryFilter);
		});
	}

	initAdminPushNotifications();
	initConditionalPaymentSettings();
	initSmartAdminFilters();

	document.querySelectorAll('.omnify-searchable-select').forEach(window.omnifyInitSearchableSelect);

	// 1. Copy URL Helper
	const copyButtons = document.querySelectorAll('.js-copy-url');
	copyButtons.forEach(button => {
		button.addEventListener('click', function(e) {
			e.preventDefault();
			const url = this.getAttribute('data-url');
			if (!url) return;

			navigator.clipboard.writeText(url).then(() => {
				const originalText = this.textContent;
				this.textContent = 'Copied!';
				this.classList.add('omnify-button--primary');
				setTimeout(() => {
					this.textContent = originalText;
					this.classList.remove('omnify-button--primary');
				}, 2000);
			}).catch(err => {
				console.error('Failed to copy text: ', err);
			});
		});
	});

	// 2. Delete Confirmation Helper
	const deleteButtons = document.querySelectorAll('.js-confirm-delete');
	deleteButtons.forEach(button => {
		button.addEventListener('click', function(e) {
			const message = this.getAttribute('data-message') || 'Are you sure you want to delete this item?';
			if (!confirm(message)) {
				e.preventDefault();
			}
		});
	});

	// 3. Media Uploader Helper for Product Thumbnail
	const selectThumbButton = document.getElementById('omnify-select-thumbnail');
	const removeThumbButton = document.getElementById('omnify-remove-thumbnail');
	const thumbInput = document.getElementById('omnify-thumbnail-id');
	const thumbPreview = document.getElementById('omnify-thumbnail-preview');

	if (selectThumbButton && thumbInput && thumbPreview) {
		let mediaFrame;
		selectThumbButton.addEventListener('click', function(e) {
			e.preventDefault();

			if (mediaFrame) {
				mediaFrame.open();
				return;
			}

			mediaFrame = wp.media({
				title: 'Select Featured Image',
				button: { text: 'Set as Featured Image' },
				multiple: false
			});

			mediaFrame.on('select', function() {
				const attachment = mediaFrame.state().get('selection').first().toJSON();
				thumbInput.value = attachment.id;
				const imgUrl = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
				thumbPreview.innerHTML = `<img src="${imgUrl}" style="max-width: 100%; max-height: 130px; object-fit: contain; border: 1px solid #e1e3e5; border-radius: 6px; padding: 4px; background:#fff;" />`;
				if (removeThumbButton) {
					removeThumbButton.style.display = 'inline-flex';
				}
			});

			mediaFrame.open();
		});
	}

	if (removeThumbButton && thumbInput && thumbPreview) {
		removeThumbButton.addEventListener('click', function(e) {
			e.preventDefault();
			thumbInput.value = '';
			thumbPreview.innerHTML = '<p class="description" style="margin:0;color:#9ca3af;font-size:12px;">No featured image</p>';
			this.style.display = 'none';
		});
	}

	// 3b. Media Uploader Helper for Product Video Poster
	const selectPosterButton = document.getElementById('omnify-select-video-poster');
	const posterInput = document.getElementById('omnify-video-poster-url');

	if (selectPosterButton && posterInput) {
		let posterFrame;
		selectPosterButton.addEventListener('click', function(e) {
			e.preventDefault();

			if (posterFrame) {
				posterFrame.open();
				return;
			}

			posterFrame = wp.media({
				title: 'Select Video Poster Image',
				button: { text: 'Set as Video Poster' },
				multiple: false
			});

			posterFrame.on('select', function() {
				const attachment = posterFrame.state().get('selection').first().toJSON();
				posterInput.value = attachment.url;
			});

			posterFrame.open();
		});
	}

	const productFilesInput = document.getElementById('product_files');
	const productFilesCount = document.getElementById('omnify-product-files-count');
	if (productFilesInput && productFilesCount) {
		productFilesInput.addEventListener('change', function() {
			const count = this.files ? this.files.length : 0;
			productFilesCount.textContent = count ? `${count} file${count === 1 ? '' : 's'} selected` : '';
		});
	}

	// 4. Gallery Multi-Image Picker
	const addGalleryBtn = document.getElementById('omnify-add-gallery-images');
	const galleryGrid = document.getElementById('omnify-gallery-preview-grid');

	if (addGalleryBtn && galleryGrid) {
		let galleryFrame;

		addGalleryBtn.addEventListener('click', function(e) {
			e.preventDefault();

			if (galleryFrame) {
				galleryFrame.open();
				return;
			}

			galleryFrame = wp.media({
				title: 'Select Gallery Images',
				button: { text: 'Add to Gallery' },
				multiple: 'add',
				library: { type: 'image' }
			});

			galleryFrame.on('select', function() {
				const selection = galleryFrame.state().get('selection');
				selection.each(function(attachment) {
					const data = attachment.toJSON();
					// Skip if already in gallery
					if (galleryGrid.querySelector(`.omnify-gallery-tile[data-id="${data.id}"]`)) return;

					const imgUrl = data.sizes && data.sizes.thumbnail ? data.sizes.thumbnail.url : data.url;
					const tile = document.createElement('div');
					tile.className = 'omnify-gallery-tile';
					tile.setAttribute('data-id', data.id);
					tile.style.cssText = 'position:relative;width:64px;height:64px;border-radius:6px;overflow:hidden;border:1px solid #d1d5db;';
					tile.innerHTML = `
						<img src="${imgUrl}" style="width:100%;height:100%;object-fit:cover;" />
						<input type="hidden" name="gallery_ids[]" value="${data.id}" />
						<button type="button" class="omnify-gallery-remove-btn" style="position:absolute;top:2px;right:2px;width:18px;height:18px;border-radius:50%;background:rgba(0,0,0,0.65);color:#fff;border:none;cursor:pointer;font-size:11px;line-height:1;display:flex;align-items:center;justify-content:center;padding:0;">×</button>
					`;
					galleryGrid.appendChild(tile);
				});
			});

			galleryFrame.open();
		});

		// Remove gallery tile
		galleryGrid.addEventListener('click', function(e) {
			const btn = e.target.closest('.omnify-gallery-remove-btn');
			if (btn) {
				e.preventDefault();
				btn.closest('.omnify-gallery-tile').remove();
			}
		});
	}

	// 5. Video URL Preview
	const videoUrlInput = document.getElementById('omnify-video-url');
	const videoPreview = document.getElementById('omnify-video-preview');

	if (videoUrlInput && videoPreview) {
		function updateVideoPreview(url) {
			if (!url) {
				videoPreview.style.display = 'none';
				videoPreview.innerHTML = '';
				return;
			}

			let embedHtml = '';

			// YouTube
			const ytMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
			if (ytMatch) {
				embedHtml = `<iframe src="https://www.youtube.com/embed/${ytMatch[1]}" style="width:100%;height:100%;border:none;" allowfullscreen></iframe>`;
			}

			// Vimeo
			const vimeoMatch = url.match(/vimeo\.com\/(\d+)/);
			if (!embedHtml && vimeoMatch) {
				embedHtml = `<iframe src="https://player.vimeo.com/video/${vimeoMatch[1]}" style="width:100%;height:100%;border:none;" allowfullscreen></iframe>`;
			}

			// Direct video
			if (!embedHtml && (url.endsWith('.mp4') || url.endsWith('.webm') || url.endsWith('.ogg'))) {
				embedHtml = `<video src="${url}" controls style="width:100%;height:100%;object-fit:cover;"></video>`;
			}

			if (embedHtml) {
				videoPreview.innerHTML = embedHtml;
				videoPreview.style.display = 'block';
			} else {
				videoPreview.style.display = 'none';
				videoPreview.innerHTML = '';
			}
		}

		// Initial preview on load
		updateVideoPreview(videoUrlInput.value.trim());

		let debounceTimer;
		videoUrlInput.addEventListener('input', function() {
			clearTimeout(debounceTimer);
			debounceTimer = setTimeout(() => updateVideoPreview(this.value.trim()), 600);
		});
	}

	// 6. Variation row per-variation image picker (delegated)
	document.addEventListener('click', function(e) {
		const btn = e.target.closest('.omnify-var-pick-image-btn');
		if (!btn) return;
		e.preventDefault();

		const row = btn.closest('.omnify-variation-row');
		if (!row) return;

		const imgInput = row.querySelector('.omnify-var-thumbnail-id-input');
		const imgPreview = row.querySelector('.omnify-var-thumbnail-preview');

		if (!window.wp || !wp.media) return;

		const frame = wp.media({
			title: 'Select Variation Image',
			button: { text: 'Use as Variation Image' },
			multiple: false,
			library: { type: 'image' }
		});

		frame.on('select', function() {
			const attachment = frame.state().get('selection').first().toJSON();
			const imgUrl = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
			if (imgInput) imgInput.value = attachment.id;
			if (imgPreview) {
				imgPreview.innerHTML = `<img src="${imgUrl}" style="width:48px;height:48px;object-fit:cover;border-radius:4px;border:1px solid #d1d5db;" />`;
			}
		});

		frame.open();
	});

	// Remove variation image
	document.addEventListener('click', function(e) {
		const btn = e.target.closest('.omnify-var-remove-image-btn');
		if (!btn) return;
		e.preventDefault();
		const row = btn.closest('.omnify-variation-row');
		if (!row) return;
		const imgInput = row.querySelector('.omnify-var-thumbnail-id-input');
		const imgPreview = row.querySelector('.omnify-var-thumbnail-preview');
		if (imgInput) imgInput.value = '';
	if (imgPreview) imgPreview.innerHTML = '<span style="font-size:11px;color:#9ca3af;">No image</span>';
	});

	// 7. Global attribute option metadata builder
	const attrTypeSelect = document.getElementById('attribute_type');
	const attrOptionsInput = document.getElementById('attribute_options');
	const attrOptionBuilder = document.getElementById('omnify-attribute-option-builder');
	const attrBuilderEmpty = document.getElementById('omnify-attribute-builder-empty');
	const attrAddOptionBtn = document.getElementById('omnify-add-attribute-option');
	const attrAddOptionFromInputBtn = document.getElementById('omnify-add-attribute-option-from-input');
	const attrOptionLabelInput = document.getElementById('omnify-attribute-option-label');
	const attrForm = document.querySelector('.omnify-global-attribute-form');
	const attrNameInput = document.getElementById('attribute_name');
	const attrIndexInput = document.getElementById('attribute_index');
	const attrSubmitLabel = document.getElementById('omnify-attribute-submit-label');
	const attrCancelEditBtn = document.getElementById('omnify-cancel-attribute-edit');
	const attrFormTitle = document.getElementById('omnify-attribute-form-title');

	function parseAttributeOptions(value) {
		return String(value || '').split(',')
			.map(option => option.trim())
			.filter((option, index, list) => option && list.indexOf(option) === index);
	}

	function escapeHtml(value) {
		return String(value || '').replace(/[&<>"']/g, char => ({
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			"'": '&#039;'
		})[char]);
	}

	function getAttributeOptionRows() {
		return attrOptionBuilder ? Array.from(attrOptionBuilder.querySelectorAll('.omnify-attribute-option-meta-row')) : [];
	}

	function syncAttributeOptionNames() {
		if (!attrOptionsInput || !attrOptionBuilder) return;

		const rows = getAttributeOptionRows();
		const labels = [];
		const type = attrTypeSelect ? attrTypeSelect.value : 'button';
		const showColor = type === 'color';
		const showImage = type === 'image';

		rows.forEach(row => {
			const labelInput = row.querySelector('.omnify-attribute-option-label-input');
			const label = labelInput ? labelInput.value.trim() : '';
			const colorField = row.querySelector('.omnify-attribute-color-field');
			const imageField = row.querySelector('.omnify-attribute-image-field');
			const preview = row.querySelector('.omnify-attribute-option-preview');
			const colorInput = row.querySelector('input[type="color"]');
			const imageUrlInput = row.querySelector('[data-image-url]');
			row.classList.toggle('is-empty', !label);
			row.setAttribute('data-attribute-option-type', type);
			if (colorField) colorField.hidden = !showColor;
			if (imageField) imageField.hidden = !showImage;

			if (!label) return;

			labels.push(label);
			row.querySelectorAll('[data-meta-name]').forEach(input => {
				input.name = `attribute_option_meta[${label}][${input.getAttribute('data-meta-name')}]`;
			});

			const swatchLabel = row.querySelector('[data-option-preview-label]');
			if (swatchLabel) swatchLabel.textContent = label;

			if (preview) {
				preview.className = `omnify-attribute-option-preview omnify-attribute-option-preview--${type}`;
				preview.title = label;
				if (showColor && colorInput) {
					preview.style.setProperty('--option-color', colorInput.value || '#6366f1');
					preview.innerHTML = `<span data-option-preview-label>${escapeHtml(label)}</span>`;
				} else if (showImage) {
					preview.style.removeProperty('--option-color');
					if (imageUrlInput && imageUrlInput.value) {
						preview.innerHTML = `<img src="${escapeHtml(imageUrlInput.value)}" alt="" /><span data-option-preview-label>${escapeHtml(label)}</span>`;
					} else {
						preview.innerHTML = `<span data-option-preview-label>${escapeHtml(label || 'Image')}</span>`;
					}
				} else {
					preview.style.removeProperty('--option-color');
					preview.innerHTML = `<span data-option-preview-label>${escapeHtml(label)}</span>`;
				}
			}
		});

		attrOptionsInput.value = labels.join(', ');
		if (attrBuilderEmpty) attrBuilderEmpty.style.display = rows.length ? 'none' : 'grid';
	}

	function createAttributeOptionRow(label = '', meta = {}) {
		if (!attrOptionBuilder) return;

		const color = meta.color || '#6366f1';
		const imageId = meta.image_id || '';
		const imageUrl = meta.image_url || '';
		const fieldId = `omnify-attribute-option-${Date.now()}-${Math.floor(Math.random() * 10000)}`;
		const row = document.createElement('div');
		row.className = 'omnify-attribute-option-meta-row';
		row.innerHTML = `
			<div class="omnify-attribute-option-preview omnify-attribute-option-preview--button">
				<span data-option-preview-label>${escapeHtml(label)}</span>
			</div>
			<div class="omnify-attribute-option-fields">
				<label for="${fieldId}">
					<span>Option label</span>
					<input type="text" id="${fieldId}" class="omnify-attribute-option-label-input" value="${escapeHtml(label)}" placeholder="e.g. Red" />
				</label>
				<label class="omnify-attribute-color-field">
					<span>Swatch color</span>
					<input type="color" data-meta-name="color" value="${escapeHtml(color)}" />
				</label>
				<div class="omnify-attribute-image-field">
					<div class="omnify-attribute-image-preview-box" data-preview>${imageUrl ? `<img src="${escapeHtml(imageUrl)}" alt="" />` : 'No image'}</div>
					<input type="hidden" data-meta-name="image_id" data-image-id value="${escapeHtml(String(imageId))}" />
					<input type="hidden" data-meta-name="image_url" data-image-url value="${escapeHtml(imageUrl)}" />
					<button type="button" class="omnify-button omnify-button--secondary omnify-button--sm omnify-attribute-pick-image">Upload image</button>
					<button type="button" class="omnify-button omnify-button--danger omnify-button--sm omnify-attribute-remove-image" style="${imageUrl ? '' : 'display:none;'}">Remove</button>
				</div>
			</div>
			<button type="button" class="omnify-attribute-option-remove" aria-label="Remove option">×</button>
		`;
		attrOptionBuilder.appendChild(row);
		syncAttributeOptionNames();
		const input = row.querySelector('.omnify-attribute-option-label-input');
		if (input && !label) input.focus();
	}

	function resetAttributeBuilderForm() {
		if (attrForm) attrForm.reset();
		if (attrIndexInput) attrIndexInput.value = '-1';
		if (attrOptionBuilder) attrOptionBuilder.innerHTML = '';
		if (attrOptionsInput) attrOptionsInput.value = '';
		if (attrSubmitLabel) attrSubmitLabel.textContent = 'Add Attribute';
		if (attrFormTitle) attrFormTitle.textContent = 'Add New Attribute';
		if (attrCancelEditBtn) attrCancelEditBtn.style.display = 'none';
		if (attrForm) attrForm.classList.remove('is-editing');
		syncAttributeOptionNames();
	}

	function normalizeAttributeOptions(data, optionMeta) {
		let options = [];

		if (Array.isArray(data.options)) {
			options = data.options.map(option => String(option || '').trim()).filter(Boolean);
		} else if (typeof data.options === 'string') {
			options = parseAttributeOptions(data.options);
		} else if (data.options && typeof data.options === 'object') {
			options = Object.values(data.options).map(option => String(option || '').trim()).filter(Boolean);
		}

		Object.keys(optionMeta || {}).forEach(label => {
			const normalizedLabel = String(label || '').trim();
			if (normalizedLabel && !options.includes(normalizedLabel)) {
				options.push(normalizedLabel);
			}
		});

		return options.filter((option, index, list) => list.indexOf(option) === index);
	}

	function loadAttributeForEdit(data, index) {
		if (!data || !attrOptionBuilder) return;

		if (attrNameInput) attrNameInput.value = data.name || '';
		if (attrTypeSelect) attrTypeSelect.value = ['button', 'dropdown', 'color', 'image'].includes(data.type) ? data.type : 'button';
		if (attrIndexInput) attrIndexInput.value = String(index);
		attrOptionBuilder.innerHTML = '';
		const optionMeta = data.option_meta && typeof data.option_meta === 'object' ? data.option_meta : {};
		const options = normalizeAttributeOptions(data, optionMeta);
		options.forEach(option => createAttributeOptionRow(option, optionMeta[option] || {}));
		if (attrSubmitLabel) attrSubmitLabel.textContent = 'Update Attribute';
		if (attrFormTitle) attrFormTitle.textContent = 'Edit Attribute';
		if (attrCancelEditBtn) attrCancelEditBtn.style.display = 'inline-flex';
		if (attrForm) attrForm.classList.add('is-editing');
		document.getElementById('attribute_name')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
		syncAttributeOptionNames();
	}

	function addAttributeOptionFromInput() {
		const value = attrOptionLabelInput ? attrOptionLabelInput.value.trim() : '';
		if (!value) {
			createAttributeOptionRow('');
			return;
		}
		createAttributeOptionRow(value);
		if (attrOptionLabelInput) attrOptionLabelInput.value = '';
	}

	function updateAttributeOptionBuilder() {
		if (!attrTypeSelect || !attrOptionsInput || !attrOptionBuilder) return;

		syncAttributeOptionNames();
	}

	if (attrTypeSelect && attrOptionsInput && attrOptionBuilder) {
		attrTypeSelect.addEventListener('change', updateAttributeOptionBuilder);
		if (attrAddOptionBtn) attrAddOptionBtn.addEventListener('click', () => createAttributeOptionRow(''));
		if (attrAddOptionFromInputBtn) attrAddOptionFromInputBtn.addEventListener('click', addAttributeOptionFromInput);
		if (attrOptionLabelInput) {
			attrOptionLabelInput.addEventListener('keydown', e => {
				if (e.key === 'Enter') {
					e.preventDefault();
					addAttributeOptionFromInput();
				}
			});
		}
		attrOptionBuilder.addEventListener('input', e => {
			if (e.target.matches('.omnify-attribute-option-label-input, input[type="color"]')) {
				syncAttributeOptionNames();
			}
		});
		attrOptionBuilder.addEventListener('click', e => {
			const removeOption = e.target.closest('.omnify-attribute-option-remove');
			if (removeOption) {
				e.preventDefault();
				removeOption.closest('.omnify-attribute-option-meta-row')?.remove();
				syncAttributeOptionNames();
			}
		});
		if (attrForm) {
			attrForm.addEventListener('submit', e => {
				syncAttributeOptionNames();
				if (!attrOptionsInput.value.trim()) {
					e.preventDefault();
					createAttributeOptionRow('');
				}
			});
		}
		if (attrCancelEditBtn) {
			attrCancelEditBtn.addEventListener('click', resetAttributeBuilderForm);
		}
		document.addEventListener('click', e => {
			const editBtn = e.target.closest('.omnify-edit-global-attribute');
			if (!editBtn) return;
			e.preventDefault();
			try {
				loadAttributeForEdit(JSON.parse(editBtn.getAttribute('data-attribute') || '{}'), parseInt(editBtn.getAttribute('data-index') || '-1', 10));
			} catch (err) {
				console.error(err);
			}
		});
		updateAttributeOptionBuilder();
	}

	document.addEventListener('click', function(e) {
		const pickBtn = e.target.closest('.omnify-attribute-pick-image');
		if (!pickBtn) return;
		e.preventDefault();

		const row = pickBtn.closest('.omnify-attribute-option-meta-row');
		if (!row || !window.wp || !wp.media) return;

		const frame = wp.media({
			title: 'Select Swatch Image',
			button: { text: 'Use as Swatch Image' },
			multiple: false,
			library: { type: 'image' }
		});

		frame.on('select', function() {
			const attachment = frame.state().get('selection').first().toJSON();
			const imgUrl = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
			const preview = row.querySelector('[data-preview]');
			const idInput = row.querySelector('[data-image-id]');
			const urlInput = row.querySelector('[data-image-url]');
			const removeBtn = row.querySelector('.omnify-attribute-remove-image');
			if (idInput) idInput.value = attachment.id;
			if (urlInput) urlInput.value = imgUrl;
			if (preview) preview.innerHTML = `<img src="${imgUrl}" alt="" />`;
			if (removeBtn) removeBtn.style.display = 'inline-flex';
			syncAttributeOptionNames();
		});

		frame.open();
	});

	document.addEventListener('click', function(e) {
		const removeBtn = e.target.closest('.omnify-attribute-remove-image');
		if (!removeBtn) return;
		e.preventDefault();

		const row = removeBtn.closest('.omnify-attribute-option-meta-row');
		if (!row) return;
		const preview = row.querySelector('[data-preview]');
		const idInput = row.querySelector('[data-image-id]');
		const urlInput = row.querySelector('[data-image-url]');
		if (idInput) idInput.value = '';
		if (urlInput) urlInput.value = '';
		if (preview) preview.textContent = 'No image';
		removeBtn.style.display = 'none';
		syncAttributeOptionNames();
	});

	// =========================================================================
	// 8. API & Connection Diagnostics Page Scripts
	// =========================================================================

	// Create API Key Handler
	const createKeyForm = document.getElementById('omnify-create-key-form');
	if (createKeyForm) {
		createKeyForm.addEventListener('submit', function(e) {
			e.preventDefault();
			const descInput = document.getElementById('key-desc');
			const userInput = document.getElementById('key-user');
			const permsInput = document.getElementById('key-perms');
			const btn = document.getElementById('omnify-generate-key-btn');

			if (!descInput.value.trim()) return;

			btn.disabled = true;
			const originalText = btn.innerHTML;
			btn.innerHTML = '⌛ Generating...';

			jQuery.ajax({
				url: omnifyAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'omnify_create_api_key',
					nonce: omnifyAdmin.apiNonce,
					description: descInput.value.trim(),
					user_id: userInput.value,
					permissions: permsInput.value
				},
				success: function(response) {
					btn.disabled = false;
					btn.innerHTML = originalText;
					if (response.success) {
						// Display credentials overlay with copy functions
						document.getElementById('generated-client-key').value = response.data.consumer_key;
						document.getElementById('generated-client-secret').value = response.data.consumer_secret;
						document.getElementById('omnify-credentials-overlay').style.display = 'block';
						
						descInput.value = '';
					} else {
						alert(response.data.message || 'Error generating key');
					}
				},
				error: function() {
					btn.disabled = false;
					btn.innerHTML = originalText;
					alert('Request failed. Please try again.');
				}
			});
		});
	}

	// Copy credentials helper
	const copyBtns = document.querySelectorAll('.copy-credential-btn');
	copyBtns.forEach(btn => {
		btn.addEventListener('click', function() {
			const targetId = btn.getAttribute('data-target');
			const targetInput = document.getElementById(targetId);
			if (targetInput) {
				targetInput.select();
				navigator.clipboard.writeText(targetInput.value).then(() => {
					const originalText = btn.textContent;
					btn.textContent = 'Copied!';
					btn.style.background = 'var(--omnify-success)';
					btn.style.color = 'white';
					setTimeout(() => {
						btn.textContent = originalText;
						btn.style.background = '';
						btn.style.color = '';
					}, 1500);
				});
			}
		});
	});

	// Close credentials overlay
	const closeCredentialsBtn = document.getElementById('close-credentials-btn');
	if (closeCredentialsBtn) {
		closeCredentialsBtn.addEventListener('click', function() {
			document.getElementById('omnify-credentials-overlay').style.display = 'none';
			window.location.reload(); // Reload table to reflect new key entry
		});
	}

	// Revoke API Key Handler
	document.addEventListener('click', function(e) {
		const revokeBtn = e.target.closest('.revoke-key-btn');
		if (!revokeBtn) return;
		e.preventDefault();

		if (!confirm('Are you sure you want to revoke this API key? Any applications using it will lose access immediately.')) {
			return;
		}

		const keyId = revokeBtn.getAttribute('data-key-id');
		revokeBtn.disabled = true;
		revokeBtn.textContent = '...';

		jQuery.ajax({
			url: omnifyAdmin.ajaxUrl,
			type: 'POST',
			data: {
				action: 'omnify_revoke_api_key',
				nonce: omnifyAdmin.apiNonce,
				key_id: keyId
			},
			success: function(response) {
				if (response.success) {
					const row = document.getElementById('api-key-row-' + keyId);
					if (row) {
						row.style.transition = 'all 0.5s ease';
						row.style.opacity = '0';
						setTimeout(() => row.remove(), 500);
					}
				} else {
					revokeBtn.disabled = false;
					revokeBtn.textContent = 'Revoke';
					alert(response.data.message || 'Error revoking key');
				}
			},
			error: function() {
				revokeBtn.disabled = false;
				revokeBtn.textContent = 'Revoke';
				alert('Request failed.');
			}
		});
	});

	// Check Stripe Connection Status
	const testStripeBtn = document.getElementById('test-stripe-connection-btn');
	if (testStripeBtn) {
		testStripeBtn.addEventListener('click', function(e) {
			e.preventDefault();
			const resultsDiv = document.getElementById('stripe-test-results');
			testStripeBtn.disabled = true;
			testStripeBtn.innerHTML = '⌛ Testing...';
			resultsDiv.style.display = 'block';
			resultsDiv.className = '';
			resultsDiv.style.background = '#f3f4f6';
			resultsDiv.style.color = '#4b5563';
			resultsDiv.innerHTML = '<span class="omnify-spinner" style="display:inline-block;width:12px;height:12px;border:2px solid currentColor;border-top-color:transparent;border-radius:50%;animation:spin 1s linear infinite;margin-right:8px;vertical-align:middle;"></span>Testing connectivity to Stripe API...';

			jQuery.ajax({
				url: omnifyAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'omnify_test_stripe_connection',
					nonce: omnifyAdmin.apiNonce
				},
				success: function(response) {
					testStripeBtn.disabled = false;
					testStripeBtn.innerHTML = 'Check Stripe Status';
					if (response.success) {
						resultsDiv.style.background = 'rgba(16, 185, 129, 0.1)';
						resultsDiv.style.color = '#065f46';
						resultsDiv.style.border = '1px solid var(--omnify-success)';
						resultsDiv.innerHTML = '<strong>✅ Connected Successfully</strong><br/>' +
							'Mode: ' + response.data.mode.toUpperCase() + '<br/>' +
							'Account ID: ' + response.data.account_id + '<br/>' +
							'Latency: ' + response.data.latency + ' ms';
					} else {
						resultsDiv.style.background = 'rgba(239, 68, 68, 0.1)';
						resultsDiv.style.color = '#991b1b';
						resultsDiv.style.border = '1px solid var(--omnify-danger, #ef4444)';
						resultsDiv.innerHTML = '<strong>❌ Connection Failed</strong><br/>' +
							(response.data.message || 'Unknown error occurred') + '<br/>' +
							'Latency: ' + (response.data.latency || 0) + ' ms';
					}
				},
				error: function() {
					testStripeBtn.disabled = false;
					testStripeBtn.innerHTML = 'Check Stripe Status';
					resultsDiv.style.background = 'rgba(239, 68, 68, 0.1)';
					resultsDiv.style.color = '#991b1b';
					resultsDiv.style.border = '1px solid var(--omnify-danger, #ef4444)';
					resultsDiv.innerHTML = '<strong>❌ Request Failed</strong><br/>Stripe endpoint request timed out or was blocked by server policies.';
				}
			});
		});
	}

	// Check PayPal Connection Status
	const testPaypalBtn = document.getElementById('test-paypal-connection-btn');
	if (testPaypalBtn) {
		testPaypalBtn.addEventListener('click', function(e) {
			e.preventDefault();
			const resultsDiv = document.getElementById('paypal-test-results');
			testPaypalBtn.disabled = true;
			testPaypalBtn.innerHTML = '⌛ Testing...';
			resultsDiv.style.display = 'block';
			resultsDiv.className = '';
			resultsDiv.style.background = '#f3f4f6';
			resultsDiv.style.color = '#4b5563';
			resultsDiv.innerHTML = '<span class="omnify-spinner" style="display:inline-block;width:12px;height:12px;border:2px solid currentColor;border-top-color:transparent;border-radius:50%;animation:spin 1s linear infinite;margin-right:8px;vertical-align:middle;"></span>Testing PayPal token OAuth authentication...';

			jQuery.ajax({
				url: omnifyAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'omnify_test_paypal_connection',
					nonce: omnifyAdmin.apiNonce
				},
				success: function(response) {
					testPaypalBtn.disabled = false;
					testPaypalBtn.innerHTML = 'Check PayPal Status';
					if (response.success) {
						resultsDiv.style.background = 'rgba(16, 185, 129, 0.1)';
						resultsDiv.style.color = '#065f46';
						resultsDiv.style.border = '1px solid var(--omnify-success)';
						resultsDiv.innerHTML = '<strong>✅ Authenticated Successfully</strong><br/>' +
							'Mode: ' + response.data.mode.toUpperCase() + '<br/>' +
							'Latency: ' + response.data.latency + ' ms';
					} else {
						resultsDiv.style.background = 'rgba(239, 68, 68, 0.1)';
						resultsDiv.style.color = '#991b1b';
						resultsDiv.style.border = '1px solid var(--omnify-danger, #ef4444)';
						resultsDiv.innerHTML = '<strong>❌ Authentication Failed</strong><br/>' +
							(response.data.message || 'Unknown error occurred') + '<br/>' +
							'Latency: ' + (response.data.latency || 0) + ' ms';
					}
				},
				error: function() {
					testPaypalBtn.disabled = false;
					testPaypalBtn.innerHTML = 'Check PayPal Status';
					resultsDiv.style.background = 'rgba(239, 68, 68, 0.1)';
					resultsDiv.style.color = '#991b1b';
					resultsDiv.style.border = '1px solid var(--omnify-danger, #ef4444)';
					resultsDiv.innerHTML = '<strong>❌ Request Failed</strong><br/>PayPal endpoint request timed out or was blocked by server policies.';
				}
			});
		});
	}

	// Arbitrary URL Checker
	const testUrlForm = document.getElementById('omnify-test-url-form');
	if (testUrlForm) {
		testUrlForm.addEventListener('submit', function(e) {
			e.preventDefault();
			const urlInput = document.getElementById('test-url-input');
			const submitBtn = document.getElementById('omnify-test-url-submit');
			const dashboard = document.getElementById('url-test-dashboard');

			const url = urlInput.value.trim();
			if (!url) return;

			submitBtn.disabled = true;
			submitBtn.innerHTML = '⌛ Connecting...';
			dashboard.style.display = 'none';

			jQuery.ajax({
				url: omnifyAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'omnify_test_connection',
					nonce: omnifyAdmin.apiNonce,
					url: url
				},
				success: function(response) {
					submitBtn.disabled = false;
					submitBtn.innerHTML = '📡 Send Ping';
					if (response.success) {
						dashboard.style.display = 'block';
						document.getElementById('report-target-url').textContent = url;
						
						const codeDiv = document.getElementById('report-status-code');
						const msgDiv = document.getElementById('report-status-message');
						const latencyDiv = document.getElementById('report-latency');
						const latencyRating = document.getElementById('report-latency-rating');
						const healthDiv = document.getElementById('report-health');
						const headersPre = document.getElementById('report-headers');

						codeDiv.textContent = response.data.code || 'ERR';
						msgDiv.textContent = response.data.message || 'No response';
						latencyDiv.textContent = response.data.latency + ' ms';

						if (response.data.latency < 250) {
							latencyRating.textContent = 'Excellent (Fast)';
							latencyRating.style.color = 'var(--omnify-success)';
						} else if (response.data.latency < 600) {
							latencyRating.textContent = 'Average (Good)';
							latencyRating.style.color = 'var(--omnify-warning)';
						} else {
							latencyRating.textContent = 'Poor (Slow)';
							latencyRating.style.color = 'var(--omnify-danger)';
						}

						if (response.data.status === 'success') {
							healthDiv.innerHTML = '<span style="background: rgba(16, 185, 129, 0.1); color: var(--omnify-success); padding: 4px 12px; border-radius: 12px; font-size: 13px;">Online</span>';
						} else if (response.data.status === 'warning') {
							healthDiv.innerHTML = '<span style="background: rgba(245, 158, 11, 0.1); color: #d97706; padding: 4px 12px; border-radius: 12px; font-size: 13px;">Status Alert</span>';
						} else {
							healthDiv.innerHTML = '<span style="background: rgba(239, 68, 68, 0.1); color: var(--omnify-danger); padding: 4px 12px; border-radius: 12px; font-size: 13px;">Offline / Err</span>';
						}

						headersPre.textContent = JSON.stringify(response.data.headers, null, 2);
					} else {
						alert(response.data.message || 'Error processing URL ping.');
					}
				},
				error: function() {
					submitBtn.disabled = false;
					submitBtn.innerHTML = '📡 Send Ping';
					alert('Failed to connect to the test connection endpoint.');
				}
			});
		});
	}

	// ── Setup Wizard Logic ─────────────────────────────
	const wizardForm = document.getElementById('omnify-setup-wizard-form');
	if (wizardForm) {
		const steps = document.querySelectorAll('.omnify-setup-step');
		const panels = document.querySelectorAll('.omnify-setup-panel');
		const nextBtns = document.querySelectorAll('.btn-next-step');
		const prevBtns = document.querySelectorAll('.btn-prev-step');
		const generateBtn = document.getElementById('omnify-btn-generate-demo');
		const progressContainer = document.getElementById('omnify-demo-progress-container');
		const progressBar = document.getElementById('omnify-demo-progress-bar');
		const progressStatus = document.getElementById('omnify-demo-progress-status');

		const goToPanel = (panelNum) => {
			panels.forEach(p => p.classList.remove('active'));
			steps.forEach(s => s.classList.remove('active'));

			const targetPanel = document.querySelector(`.omnify-setup-panel[data-panel="${panelNum}"]`);
			if (targetPanel) targetPanel.classList.add('active');

			steps.forEach(s => {
				const stepNum = parseInt(s.getAttribute('data-step'), 10);
				if (stepNum <= panelNum) {
					s.classList.add('active');
				}
			});
		};

		nextBtns.forEach(btn => {
			btn.addEventListener('click', () => {
				const nextPanel = parseInt(btn.getAttribute('data-next'), 10);
				
				if (nextPanel === 2) {
					const nameInput = document.getElementById('store_name');
					const emailInput = document.getElementById('store_email');
					if (nameInput && !nameInput.value.trim()) {
						window.omnifyShowToast('Store Name is required.', 'error');
						nameInput.focus();
						return;
					}
					if (emailInput && (!emailInput.value.trim() || !emailInput.value.includes('@'))) {
						window.omnifyShowToast('A valid Store Email is required.', 'error');
						emailInput.focus();
						return;
					}
				}
				
				goToPanel(nextPanel);
			});
		});

		prevBtns.forEach(btn => {
			btn.addEventListener('click', () => {
				const prevPanel = parseInt(btn.getAttribute('data-prev'), 10);
				goToPanel(prevPanel);
			});
		});

		const paymentToggles = document.querySelectorAll('.omnify-payment-card input[type="checkbox"]');
		paymentToggles.forEach(toggle => {
			toggle.addEventListener('change', () => {
				const card = toggle.closest('.omnify-payment-card');
				if (card) {
					if (toggle.checked) {
						card.classList.add('active');
					} else {
						card.classList.remove('active');
					}
				}
			});
		});

		const runDemoDataSeeding = (btn, bar, statusText, container) => {
			btn.disabled = true;
			container.style.display = 'block';
			
			bar.style.width = '15%';
			statusText.textContent = 'Initializing database tables...';

			setTimeout(() => {
				bar.style.width = '45%';
				statusText.textContent = 'Generating catalog products & variations...';

				setTimeout(() => {
					bar.style.width = '75%';
					statusText.textContent = 'Populating orders, reviews, and customers...';

					jQuery.ajax({
						url: omnifyAdmin.ajaxUrl,
						method: 'POST',
						data: {
							action: 'omnify_generate_demo_data',
							nonce: omnifyAdmin.adminNonce
						},
						success: function(response) {
							if (response.success) {
								bar.style.width = '100%';
								statusText.textContent = 'Success! Seeding complete.';
								window.omnifyShowToast('Demo data successfully generated!', 'success');
								btn.innerHTML = '✅ Demo Data Generated';
							} else {
								bar.style.width = '0%';
								container.style.display = 'none';
								btn.disabled = false;
								window.omnifyShowToast(response.data.message || 'Error generating data', 'error');
							}
						},
						error: function() {
							bar.style.width = '0%';
							container.style.display = 'none';
							btn.disabled = false;
							window.omnifyShowToast('Network error while generating data.', 'error');
						}
					});
				}, 1000);
			}, 1000);
		};

		if (generateBtn) {
			generateBtn.addEventListener('click', () => {
				runDemoDataSeeding(generateBtn, progressBar, progressStatus, progressContainer);
			});
		}

		wizardForm.addEventListener('submit', (e) => {
			e.preventDefault();
			const submitBtn = wizardForm.querySelector('.btn-finish-setup');
			submitBtn.disabled = true;
			submitBtn.innerHTML = 'Saving...';

			jQuery.ajax({
				url: omnifyAdmin.ajaxUrl,
				method: 'POST',
				data: jQuery(wizardForm).serialize() + '&action=omnify_save_wizard&nonce=' + omnifyAdmin.adminNonce,
				success: function(response) {
					if (response.success) {
						window.location.href = 'admin.php?page=omnifywp-ecommerce';
					} else {
						submitBtn.disabled = false;
						submitBtn.innerHTML = 'Save & Go to Dashboard 🚀';
						window.omnifyShowToast(response.data.message || 'Error saving settings.', 'error');
					}
				},
				error: function() {
					submitBtn.disabled = false;
					submitBtn.innerHTML = 'Save & Go to Dashboard 🚀';
					window.omnifyShowToast('Network error. Failed to save setup.', 'error');
				}
			});
		});
	}

	// ── Tools & Demo Data Page Logic ───────────────────
	const toolsGenBtn = document.getElementById('omnify-btn-tools-generate');
	if (toolsGenBtn) {
		const progressBar = document.getElementById('omnify-tools-progress-bar');
		const progressStatus = document.getElementById('omnify-tools-progress-status');
		const progressContainer = document.getElementById('omnify-tools-progress-container');

		toolsGenBtn.addEventListener('click', () => {
			toolsGenBtn.disabled = true;
			progressContainer.style.display = 'block';
			progressBar.style.width = '15%';
			progressStatus.textContent = 'Initializing database tables...';

			setTimeout(() => {
				progressBar.style.width = '45%';
				progressStatus.textContent = 'Generating catalog products & variations...';

				setTimeout(() => {
					progressBar.style.width = '75%';
					progressStatus.textContent = 'Populating orders, reviews, and customers...';

					jQuery.ajax({
						url: omnifyAdmin.ajaxUrl,
						method: 'POST',
						data: {
							action: 'omnify_generate_demo_data',
							nonce: omnifyAdmin.adminNonce
						},
						success: function(response) {
							if (response.success) {
								progressBar.style.width = '100%';
								progressStatus.textContent = 'Success! Seeding complete.';
								window.omnifyShowToast('Demo data successfully generated!', 'success');
								toolsGenBtn.innerHTML = '✅ Demo Data Generated';
							} else {
								progressBar.style.width = '0%';
								progressContainer.style.display = 'none';
								toolsGenBtn.disabled = false;
								window.omnifyShowToast(response.data.message || 'Error generating data', 'error');
							}
						},
						error: function() {
							progressBar.style.width = '0%';
							progressContainer.style.display = 'none';
							toolsGenBtn.disabled = false;
							window.omnifyShowToast('Network error while generating data.', 'error');
						}
					});
				}, 800);
			}, 800);
		});
	}

	const cleanBtns = document.querySelectorAll('.btn-clean-data');
	cleanBtns.forEach(btn => {
		btn.addEventListener('click', () => {
			const type = btn.getAttribute('data-type');
			let confirmMsg = 'Are you sure you want to delete this data? This action is permanent!';
			if (type === 'all') {
				confirmMsg = '🔥 DANGER: Are you absolutely sure you want to delete ALL products, orders, customers, reviews, and transaction records? This will completely empty your store data!';
			}

			if (!confirm(confirmMsg)) {
				return;
			}

			const originalText = btn.innerHTML;
			btn.disabled = true;
			btn.innerHTML = 'Clearing...';

			jQuery.ajax({
				url: omnifyAdmin.ajaxUrl,
				method: 'POST',
				data: {
					action: 'omnify_remove_demo_data',
					nonce: omnifyAdmin.adminNonce,
					type: type
				},
				success: function(response) {
					btn.disabled = false;
					btn.innerHTML = originalText;
					if (response.success) {
						window.omnifyShowToast('Data cleared successfully!', 'success');
						setTimeout(() => window.location.reload(), 1000);
					} else {
						window.omnifyShowToast(response.data.message || 'Error clearing data.', 'error');
					}
				},
				error: function() {
					btn.disabled = false;
					btn.innerHTML = originalText;
					window.omnifyShowToast('Network error. Failed to clear data.', 'error');
				}
			});
		});
	});

	// ── Coupons View Toggle ──
	const couponCreateBtn = document.getElementById('omnify-create-coupon-btn');
	const couponCancelCreateBtn = document.getElementById('omnify-cancel-create-coupon');
	const couponListContainer = document.getElementById('omnify-coupons-list');
	const couponFormContainer = document.getElementById('omnify-coupon-form-container');

	if (couponCreateBtn && couponListContainer && couponFormContainer) {
		couponCreateBtn.addEventListener('click', function() {
			couponListContainer.style.display = 'none';
			couponFormContainer.style.display = 'block';
		});
	}
	if (couponCancelCreateBtn && couponListContainer && couponFormContainer) {
		couponCancelCreateBtn.addEventListener('click', function() {
			const couponForm = couponFormContainer.querySelector('form');
			if (couponForm) {
				couponForm.reset();
				couponForm.dataset.omnifyDirty = '0';
			}
			couponListContainer.style.display = 'block';
			couponFormContainer.style.display = 'none';
		});
	}

	// ── Generic Bulk Select All Handler ──
	document.querySelectorAll('form[id^="omnify-bulk-"]').forEach(form => {
		const selectAlls = form.querySelectorAll('.omnify-bulk-select-all');
		const items = form.querySelectorAll('input[type="checkbox"][name$="[]"]:not(.omnify-bulk-select-all)');
		const countEl = form.querySelector('.selected-count');
		const bulkWrapper = form.querySelector('.omnify-bulk-actions-wrapper');

		if (!selectAlls.length || !items.length) return;

		function updateCount() {
			const checked = form.querySelectorAll('input[type="checkbox"][name$="[]"]:checked:not(.omnify-bulk-select-all)').length;
			if (countEl) countEl.textContent = checked + ' selected';
			if (bulkWrapper) {
				bulkWrapper.style.display = checked > 0 ? 'inline-flex' : 'none';
			}
			selectAlls.forEach(cb => {
				cb.checked = items.length > 0 && checked === items.length;
				cb.indeterminate = checked > 0 && checked < items.length;
			});
		}

		selectAlls.forEach(cb => {
			cb.addEventListener('change', function() {
				items.forEach(item => item.checked = cb.checked);
				updateCount();
			});
		});

		items.forEach(item => {
			item.addEventListener('change', updateCount);
		});

		updateCount();
	});

	// ── Order Detail Refund Recalculation ──
	const refundForm = document.getElementById('omnify-refund-form');
	if (refundForm) {
		const checkboxes = refundForm.querySelectorAll('.omnify-refund-item-checkbox');
		const qtyInputs = refundForm.querySelectorAll('.omnify-refund-item-qty');
		const shippingInput = document.getElementById('refund_shipping');
		const taxInput = document.getElementById('refund_tax');
		const totalDisplay = document.getElementById('omnify-refund-total-display');
		const submitBtn = document.getElementById('omnify-refund-submit-btn');

		const currencySymbol = refundForm.dataset.currencySymbol || '$';
		const maxRefundable = parseFloat(refundForm.dataset.maxRefundable) || 0;
		const confirmMsg = refundForm.dataset.confirmMsg || '';

		function calculateRefund() {
			let total = 0;

			// Item totals
			checkboxes.forEach(chk => {
				const itemId = chk.dataset.itemId;
				const qtyInput = refundForm.querySelector(`input[name="refund_items[${itemId}][qty]"]`);
				if (qtyInput) {
					if (chk.checked) {
						qtyInput.disabled = false;
						const qty = parseInt(qtyInput.value, 10) || 0;
						const unitCost = parseFloat(qtyInput.dataset.unitCost) || 0;
						const itemTotal = qty * unitCost;
						total += itemTotal;

						// Update item total display column
						const itemTotalDisplay = refundForm.querySelector(`.omnify-refund-item-total[data-item-id="${itemId}"]`);
						if (itemTotalDisplay) {
							itemTotalDisplay.textContent = currencySymbol + itemTotal.toFixed(2);
						}
					} else {
						qtyInput.disabled = true;
						// Update item total display column
						const itemTotalDisplay = refundForm.querySelector(`.omnify-refund-item-total[data-item-id="${itemId}"]`);
						if (itemTotalDisplay) {
							itemTotalDisplay.textContent = currencySymbol + '0.00';
						}
					}
				}
			});

			// Shipping total
			if (shippingInput) {
				total += parseFloat(shippingInput.value) || 0;
			}

			// Additional tax total
			if (taxInput) {
				total += parseFloat(taxInput.value) || 0;
			}

			// Enforce maximum refundable
			total = Math.min(total, maxRefundable);

			// Update displays
			if (totalDisplay) {
				totalDisplay.textContent = currencySymbol + total.toFixed(2);
			}
			if (submitBtn) {
				submitBtn.disabled = total <= 0;
				if (total > 0) {
					const issueLabel = submitBtn.dataset.labelIssue || 'Issue Refund';
					submitBtn.textContent = issueLabel + ' (' + currencySymbol + total.toFixed(2) + ')';
				} else {
					submitBtn.textContent = submitBtn.dataset.labelIssue || 'Issue Refund';
				}
			}
		}

		checkboxes.forEach(chk => {
			chk.addEventListener('change', function() {
				const itemId = this.dataset.itemId;
				const qtyInput = refundForm.querySelector(`input[name="refund_items[${itemId}][qty]"]`);
				if (qtyInput) {
					if (this.checked) {
						qtyInput.value = qtyInput.max;
					} else {
						qtyInput.value = 0;
					}
				}
				calculateRefund();
			});
		});

		qtyInputs.forEach(input => {
			input.addEventListener('input', calculateRefund);
			input.addEventListener('change', calculateRefund);
		});

		if (shippingInput) {
			shippingInput.addEventListener('input', calculateRefund);
			shippingInput.addEventListener('change', calculateRefund);
		}

		if (taxInput) {
			taxInput.addEventListener('input', calculateRefund);
			taxInput.addEventListener('change', calculateRefund);
		}

		refundForm.addEventListener('submit', function(e) {
			if (confirmMsg && !confirm(confirmMsg)) {
				e.preventDefault();
			}
		});

		calculateRefund();
	}

	// ── Order Detail Fulfillment Editing Toggle ──
	const editFulfillmentBtn = document.getElementById('omnify-edit-fulfillment-btn');
	const cancelFulfillmentBtn = document.getElementById('omnify-cancel-fulfillment-btn');
	const editFulfillmentForm = document.getElementById('omnify-edit-fulfillment-form');
	if (editFulfillmentBtn && editFulfillmentForm) {
		editFulfillmentBtn.addEventListener('click', function() {
			editFulfillmentBtn.style.display = 'none';
			editFulfillmentForm.style.display = 'block';
		});
	}
	if (cancelFulfillmentBtn && editFulfillmentBtn && editFulfillmentForm) {
		cancelFulfillmentBtn.addEventListener('click', function() {
			editFulfillmentBtn.style.display = 'inline-flex';
			editFulfillmentForm.style.display = 'none';
		});
	}


	// ── Products Quick Edit Modal ──
	const quickEditModal = document.getElementById('omnify-quick-edit-modal');
	const quickEditForm = document.getElementById('omnify-quick-edit-form');
	if (quickEditModal && quickEditForm) {
		const closeBtn = quickEditModal.querySelector('.omnify-modal-close');
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
				quickEditModal.style.display = 'block';
			});
		});

		const hideModal = () => quickEditModal.style.display = 'none';
		if (closeBtn) closeBtn.addEventListener('click', hideModal);
		if (cancelBtn) cancelBtn.addEventListener('click', hideModal);
	}

	// ── Analytics Custom Dates and Chart Rendering ──
	const rangeSelect = document.getElementById('omnify-analytics-range');
	const customDates = document.getElementById('omnify-analytics-custom-dates');
	if (rangeSelect && customDates) {
		const toggleCustomDates = () => {
			customDates.style.display = rangeSelect.value === 'custom' ? 'flex' : 'none';
		};
		rangeSelect.addEventListener('change', toggleCustomDates);
		toggleCustomDates();
	}

	const analyticsTabs = Array.from(document.querySelectorAll('.omnify-analytics-subtab'));
	const analyticsSections = Array.from(document.querySelectorAll('[data-analytics-section]'));
	const analyticsGrids = Array.from(document.querySelectorAll('.omnify-charts-grid, .omnify-bottom-cards-grid'));

	if (analyticsTabs.length && analyticsSections.length) {
		const chartInstances = [];

		function resizeAnalyticsCharts() {
			window.requestAnimationFrame(() => {
				chartInstances.forEach((chart) => {
					if (chart && typeof chart.resize === 'function') {
						chart.resize();
					}
				});
			});
		}

		analyticsTabs.forEach((tab) => {
			tab.addEventListener('click', (e) => {
				e.preventDefault();
				const targetSec = tab.getAttribute('data-section');
				analyticsTabs.forEach((t) => t.classList.remove('active'));
				tab.classList.add('active');

				analyticsSections.forEach((sec) => {
					if (sec.getAttribute('data-analytics-section') === targetSec) {
						sec.style.display = 'block';
						analyticsGrids.forEach(g => {
							if (sec.contains(g)) g.style.display = '';
						});
					} else {
						sec.style.display = 'none';
					}
				});

				resizeAnalyticsCharts();
			});
		});

		window.addEventListener('resize', resizeAnalyticsCharts);

		// Render Fallback Text Charts helper
		function renderAnalyticsFallback(canvasId, fallbackData) {
			const canvas = document.getElementById(canvasId);
			if (!canvas) return;
			const parent = canvas.parentElement;
			if (!parent) return;
			canvas.style.display = 'none';

			const box = document.createElement('div');
			box.className = 'omnify-chart-fallback-box';
			box.style.cssText = 'padding:24px;border:1px solid #f1f5f9;background:#fff;border-radius:12px;margin-top:10px;font-family:sans-serif;max-height:280px;overflow-y:auto;';

			let html = `<h4 style="margin-top:0;font-size:14px;color:#1e293b;border-bottom:1px solid #f1f5f9;padding-bottom:10px;">${fallbackData.title}</h4><table style="width:100%;font-size:12px;border-collapse:collapse;">`;
			fallbackData.rows.forEach(r => {
				html += `<tr style="border-bottom:1px dashed #f8fafc;"><td style="padding:6px 0;color:#64748b;">${r.label}</td><td style="text-align:right;font-weight:600;color:#0f172a;">${r.val}</td></tr>`;
			});
			html += '</table>';
			box.innerHTML = html;
			parent.appendChild(box);
		}

		// Initialize Chart.js
		if (typeof Chart !== 'undefined' && window.omnifyChartData) {
			try {
				const d = window.omnifyChartData;

				// 1. Sales Chart
				const ctxSales = document.getElementById('omnify-sales-chart');
				if (ctxSales) {
					const ctx = ctxSales.getContext('2d');
					const gradient = ctx.createLinearGradient(0, 0, 0, 300);
					gradient.addColorStop(0, 'rgba(20, 119, 108, 0.86)');
					gradient.addColorStop(1, 'rgba(20, 119, 108, 0.46)');

					chartInstances.push(new Chart(ctx, {
						type: 'bar',
						data: {
							labels: d.labels,
							datasets: [
								{
									label: 'Revenue',
									data: d.revenue,
									backgroundColor: gradient,
									borderColor: '#14776c',
									borderWidth: 1,
									yAxisID: 'y'
								},
								{
									label: 'Orders',
									data: d.orders,
									type: 'line',
									borderColor: '#d89616',
									backgroundColor: 'rgba(216, 150, 22, 0.1)',
									borderWidth: 2,
									tension: 0.35,
									yAxisID: 'y1'
								}
							]
						},
						options: {
							responsive: true,
							maintainAspectRatio: false,
							scales: {
								y: { type: 'linear', display: true, position: 'left' },
								y1: { type: 'linear', display: true, position: 'right', grid: { drawOnChartArea: false } }
							}
						}
					}));
				}

				// 2. Categories Chart
				const ctxCategories = document.getElementById('omnify-categories-chart');
				if (ctxCategories) {
					chartInstances.push(new Chart(ctxCategories, {
						type: 'doughnut',
						data: {
							labels: d.catLabels,
							datasets: [{
								data: d.catRevenue,
								backgroundColor: ['#14776c', '#d89616', '#0369a1', '#be185d', '#8b5cf6', '#3b82f6', '#10b981']
							}]
						},
						options: {
							responsive: true,
							maintainAspectRatio: false,
							plugins: { legend: { position: 'right' } }
						}
					}));
				}

				// 3. Payment Methods Chart
				const ctxPayments = document.getElementById('omnify-payments-chart');
				if (ctxPayments) {
					chartInstances.push(new Chart(ctxPayments, {
						type: 'pie',
						data: {
							labels: d.pmLabels,
							datasets: [{
								data: d.pmOrders,
								backgroundColor: ['#14776c', '#0f172a', '#d89616', '#be185d']
							}]
						},
						options: {
							responsive: true,
							maintainAspectRatio: false,
							plugins: { legend: { position: 'right' } }
						}
					}));
				}

				// 4. Weekdays Performance Chart
				const ctxWeekday = document.getElementById('omnify-weekday-chart');
				if (ctxWeekday) {
					chartInstances.push(new Chart(ctxWeekday, {
						type: 'bar',
						data: {
							labels: d.weekdayLabels,
							datasets: [
								{
									label: 'Revenue',
									data: d.weekdayRevenue,
									backgroundColor: 'rgba(20, 119, 108, 0.85)',
									yAxisID: 'y'
								},
								{
									label: 'OrdersCount',
									data: d.weekdayOrders,
									type: 'line',
									borderColor: '#d89616',
									borderWidth: 2,
									tension: 0.3,
									yAxisID: 'y1'
								}
							]
						},
						options: {
							responsive: true,
							maintainAspectRatio: false,
							scales: {
								y: { type: 'linear', display: true, position: 'left' },
								y1: { type: 'linear', display: true, position: 'right', grid: { drawOnChartArea: false } }
							}
						}
					}));
				}
			} catch (err) {
				console.error('ChartJS Render Error:', err);
			}
		} else {
			const d = window.omnifyChartData || { labels: [], revenue: [], orders: [], catLabels: [], catRevenue: [], pmLabels: [], pmOrders: [], weekdayLabels: [], weekdayRevenue: [], weekdayOrders: [] };
			renderAnalyticsFallback('omnify-sales-chart', {
				title: 'Sales & Orders Performance',
				rows: d.labels.map((l, i) => ({ label: l, val: `Revenue: $${d.revenue[i] || '0.00'} (${d.orders[i] || 0} Orders)` }))
			});
			renderAnalyticsFallback('omnify-categories-chart', {
				title: 'Top Categories by Revenue',
				rows: d.catLabels.map((l, i) => ({ label: l, val: `$${d.catRevenue[i] || '0.00'}` }))
			});
			renderAnalyticsFallback('omnify-payments-chart', {
				title: 'Payment Methods breakdown',
				rows: d.pmLabels.map((l, i) => ({ label: l, val: `${d.pmOrders[i] || 0} Orders` }))
			});
			renderAnalyticsFallback('omnify-weekday-chart', {
				title: 'Weekday Performance',
				rows: d.weekdayLabels.map((l, i) => ({ label: l, val: `$${d.weekdayRevenue[i] || '0.00'} (${d.weekdayOrders[i] || 0} Orders)` }))
			});
		}
	}

	// ── Location/Country Selects Initialization ──
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
			stateSelect.innerHTML = '';
			stateSelect.appendChild(option('All states', '*', selected));

			const country = countrySelect.value;
			if (country && data.states[country]) {
				Object.entries(data.states[country]).forEach(([code, name]) => {
					stateSelect.appendChild(option(name, code, selected));
				});
				stateSelect.disabled = false;
			} else {
				stateSelect.disabled = true;
			}

			if (typeof window.omnifyInitSearchableSelect === 'function' && stateSelect.classList.contains('omnify-searchable-select')) {
				window.omnifyInitSearchableSelect(stateSelect);
			}
		}

		scope.querySelectorAll('.omnify-country-select').forEach(cs => {
			cs.addEventListener('change', () => refreshStateSelect(cs));
			refreshStateSelect(cs);
		});
	};

	if (window.omnifyLocationData) {
		window.omnifyInitLocationSelects();
	}

	// ── Quick status update JS handler ──
	document.addEventListener('change', function(e) {
		const select = e.target.closest('select.omnify-quick-status');
		if (!select) return;

		const orderId = select.dataset.orderId;
		const status = select.value;
		const nonce = select.dataset.nonce;
		
		const form = document.createElement('form');
		form.method = 'POST';
		form.action = (window.ajaxurl || '').replace('admin-ajax.php', 'admin-post.php');
		
		const actionInput = document.createElement('input');
		actionInput.type = 'hidden';
		actionInput.name = 'action';
		actionInput.value = 'omnify_quick_update_order_status';
		form.appendChild(actionInput);
		
		const idInput = document.createElement('input');
		idInput.type = 'hidden';
		idInput.name = 'order_id';
		idInput.value = orderId;
		form.appendChild(idInput);
		
		const statusInput = document.createElement('input');
		statusInput.type = 'hidden';
		statusInput.name = 'status';
		statusInput.value = status;
		form.appendChild(statusInput);
		
		const nonceInput = document.createElement('input');
		nonceInput.type = 'hidden';
		nonceInput.name = 'omnify_quick_nonce';
		nonceInput.value = nonce;
		form.appendChild(nonceInput);
		
		document.body.appendChild(form);
		form.submit();
	});

	// ── Timeline search filter ──
	const search = document.getElementById('omnify-timeline-search');
	if (search) {
		search.addEventListener('input', function() {
			const term = this.value.toLowerCase();
			document.querySelectorAll('.omnify-timeline-item').forEach(item => {
				const text = item.textContent.toLowerCase();
				item.style.display = text.includes(term) ? '' : 'none';
			});
		});
	}
});

// Toast Notification Helper
window.omnifyShowToast = function(message, type = 'error') {
	let container = document.getElementById('omnify-toast-container');
	if (!container) {
		container = document.createElement('div');
		container.id = 'omnify-toast-container';
		document.body.appendChild(container);
	}

	const toast = document.createElement('div');
	toast.className = `omnify-toast omnify-toast--${type}`;
	
	let icon = 'ℹ️';
	if (type === 'success') icon = '✅';
	else if (type === 'error') icon = '❌';
	else if (type === 'warning') icon = '⚠️';

	toast.innerHTML = `
		<span class="omnify-toast__icon">${icon}</span>
		<div class="omnify-toast__message">${message}</div>
		<button class="omnify-toast__close">&times;</button>
	`;

	container.appendChild(toast);

	// Trigger reflow for transition
	toast.offsetHeight;
	toast.classList.add('show');

	const closeBtn = toast.querySelector('.omnify-toast__close');
	const dismiss = () => {
		toast.classList.remove('show');
		setTimeout(() => toast.remove(), 400);
	};

	closeBtn.addEventListener('click', dismiss);
	
	// Auto dismiss after 4 seconds
	const timeout = setTimeout(dismiss, 4000);
	toast.addEventListener('mouseenter', () => clearTimeout(timeout));
};

