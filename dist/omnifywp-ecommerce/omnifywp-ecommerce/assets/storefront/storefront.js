/**
 * Storefront and Customer Portal interactivity.
 *
 * @package Omnify_eCommerce
 */

document.addEventListener('DOMContentLoaded', () => {
	const init = (name, fn) => {
		try {
			if (typeof fn === 'function') {
				fn();
			}
		} catch (e) {
			console.error(`Error running ${name}:`, e);
		}
	};

	init('initLocationSelects', initLocationSelects);
	init('initStorefrontFilters', initStorefrontFilters);
	init('initStorefrontBuyButtons', initStorefrontBuyButtons);
	init('initWishlistButtons', initWishlistButtons);
	init('initCompareButtons', initCompareButtons);
	init('initQuickViewButtons', initQuickViewButtons);
	init('initCartButtons', initCartButtons);
	init('initCartPage', initCartPage);
	init('initMiniCarts', initMiniCarts);
	init('initCheckoutPageForm', initCheckoutPageForm);
	init('initPayPalCaptureReturn', initPayPalCaptureReturn);
	init('initPortalTabs', initPortalTabs);
	init('initPortalAuthEnhancements', initPortalAuthEnhancements);
	init('initPremiumCatalog', initPremiumCatalog);
});

const OMNIFY_CART_KEY = 'omnify_cart_items';
const OMNIFY_CHECKOUT_SELECTED_KEY = 'omnify_checkout_selected_items';
const OMNIFY_GUEST_WISHLIST_KEY = 'omnify_guest_wishlist_items';
const OMNIFY_COMPARE_KEY = 'omnify_compare_items';
const OMNIFY_COMPARE_LIMIT = 4;

function getGuestWishlistIds() {
	try {
		const ids = JSON.parse(window.localStorage.getItem(OMNIFY_GUEST_WISHLIST_KEY) || '[]');
		return Array.isArray(ids) ? ids.map((id) => parseInt(id, 10)).filter(Boolean) : [];
	} catch (e) {
		return [];
	}
}

function setGuestWishlistIds(ids) {
	window.localStorage.setItem(OMNIFY_GUEST_WISHLIST_KEY, JSON.stringify(Array.from(new Set(ids.map((id) => parseInt(id, 10)).filter(Boolean)))));
}

function getCompareItems() {
	try {
		const items = JSON.parse(window.localStorage.getItem(OMNIFY_COMPARE_KEY) || '[]');
		return Array.isArray(items) ? items.filter((item) => item && item.id) : [];
	} catch (e) {
		return [];
	}
}

function setCompareItems(items) {
	window.localStorage.setItem(OMNIFY_COMPARE_KEY, JSON.stringify(items.slice(0, OMNIFY_COMPARE_LIMIT)));
}

function initCompareButtons() {
	const buttons = document.querySelectorAll('.omnify-compare-button[data-product-id]');
	if (!buttons.length) return;

	let compareItems = getCompareItems();

	function createCompareUi() {
		if (document.getElementById('omnify-compare-bar')) return;

		const bar = document.createElement('div');
		bar.id = 'omnify-compare-bar';
		bar.className = 'omnify-compare-bar';
		bar.innerHTML = `
			<div class="omnify-compare-bar__summary">
				<strong>Compare products</strong>
				<span id="omnify-compare-count">0 selected</span>
			</div>
			<div class="omnify-compare-bar__items" id="omnify-compare-items"></div>
			<div class="omnify-compare-bar__actions">
				<button type="button" class="omnify-btn omnify-btn--secondary omnify-btn--sm" id="omnify-compare-clear">Clear</button>
				<button type="button" class="omnify-btn omnify-btn--primary omnify-btn--sm" id="omnify-compare-open">Compare</button>
			</div>
		`;

		const modal = document.createElement('div');
		modal.id = 'omnify-compare-modal';
		modal.className = 'omnify-compare-modal';
		modal.setAttribute('aria-hidden', 'true');
		modal.innerHTML = `
			<div class="omnify-compare-modal__panel" role="dialog" aria-modal="true" aria-label="Product comparison">
				<div class="omnify-compare-modal__header">
					<div>
						<h3>Product Compare</h3>
						<p>Compare price, type, rating, categories, and key details side by side.</p>
					</div>
					<button type="button" class="omnify-compare-modal__close" id="omnify-compare-close" aria-label="Close compare">×</button>
				</div>
				<div class="omnify-compare-table-wrap" id="omnify-compare-table-wrap"></div>
			</div>
		`;

		document.body.appendChild(bar);
		document.body.appendChild(modal);

		document.getElementById('omnify-compare-open')?.addEventListener('click', openCompareModal);
		document.getElementById('omnify-compare-clear')?.addEventListener('click', () => {
			compareItems = [];
			setCompareItems(compareItems);
			renderCompare();
		});
		document.getElementById('omnify-compare-close')?.addEventListener('click', closeCompareModal);
		modal.addEventListener('click', (event) => {
			if (event.target === modal) closeCompareModal();
		});
	}

	function productFromButton(button) {
		const card = button.closest('.omnify-product-card');
		return {
			id: String(button.dataset.productId || card?.dataset.productId || ''),
			name: button.dataset.name || card?.dataset.productName || card?.querySelector('.omnify-product-card__title')?.textContent?.trim() || 'Product',
			price: button.dataset.displayPrice || card?.dataset.displayPrice || '',
			type: button.dataset.type || card?.dataset.type || '',
			image: button.dataset.image || card?.dataset.image || '',
			url: button.dataset.url || card?.dataset.url || window.location.href,
			categories: button.dataset.categoriesLabel || card?.dataset.categoriesLabel || '',
			rating: button.dataset.rating || card?.dataset.rating || '',
			reviews: button.dataset.reviews || card?.dataset.reviews || '',
			description: button.dataset.description || card?.dataset.description || ''
		};
	}

	function applyCompareState(productId, selected) {
		document.querySelectorAll(`.omnify-compare-button[data-product-id="${productId}"]`).forEach((button) => {
			button.classList.toggle('is-compared', selected);
			button.setAttribute('aria-pressed', selected ? 'true' : 'false');
			const text = button.querySelector('.omnify-compare-button__text');
			if (text) text.textContent = selected ? 'Comparing' : 'Compare';
		});
	}

	function renderCompare() {
		createCompareUi();
		const bar = document.getElementById('omnify-compare-bar');
		const count = document.getElementById('omnify-compare-count');
		const itemsWrap = document.getElementById('omnify-compare-items');
		const openBtn = document.getElementById('omnify-compare-open');

		if (!bar || !count || !itemsWrap || !openBtn) return;

		bar.classList.toggle('is-visible', compareItems.length > 0);
		count.textContent = `${compareItems.length} selected`;
		openBtn.disabled = compareItems.length < 2;
		itemsWrap.innerHTML = compareItems.map((item) => {
			const letter = item.name ? item.name.charAt(0) : 'P';
			const imgHtml = item.image 
				? `<img src="${escapeHtml(item.image)}" alt="">`
				: `<span class="omnify-compare-chip-placeholder">${escapeHtml(letter)}</span>`;
			return `
				<div class="omnify-compare-chip">
					${imgHtml}
					<strong>${escapeHtml(item.name)}</strong>
					<button type="button" data-remove-compare="${escapeHtml(item.id)}" aria-label="Remove ${escapeHtml(item.name)}">×</button>
				</div>
			`;
		}).join('');

		itemsWrap.querySelectorAll('[data-remove-compare]').forEach((removeBtn) => {
			removeBtn.addEventListener('click', () => {
				const id = String(removeBtn.dataset.removeCompare || '');
				compareItems = compareItems.filter((item) => String(item.id) !== id);
				setCompareItems(compareItems);
				renderCompare();
			});
		});

		buttons.forEach((button) => {
			const productId = String(button.dataset.productId || '');
			applyCompareState(productId, compareItems.some((item) => String(item.id) === productId));
		});
	}

	function compareCell(value) {
		return value ? escapeHtml(value) : '<span class="omnify-compare-muted">Not set</span>';
	}

	function openCompareModal() {
		if (compareItems.length < 2) return;

		const modal = document.getElementById('omnify-compare-modal');
		const wrap = document.getElementById('omnify-compare-table-wrap');
		if (!modal || !wrap) return;

		const productHeaders = compareItems.map((item) => {
			const letter = item.name ? item.name.charAt(0) : 'P';
			const imgHtml = item.image
				? `<img src="${escapeHtml(item.image)}" alt="" class="omnify-compare-table-image">`
				: `<div class="omnify-compare-table-image-placeholder">${escapeHtml(letter)}</div>`;
			return `
				<th>
					<div class="omnify-compare-table-header-card">
						<button type="button" class="omnify-compare-table-remove-btn" data-remove-compare="${escapeHtml(item.id)}" title="Remove from comparison">×</button>
						${imgHtml}
						<strong class="omnify-compare-table-title">${escapeHtml(item.name)}</strong>
						<div class="omnify-compare-table-actions">
							<a href="${escapeHtml(item.url)}" class="omnify-compare-table-link">Details</a>
							<button type="button" class="omnify-compare-table-cart-btn" 
								data-id="${escapeHtml(item.id)}" 
								data-name="${escapeHtml(item.name)}" 
								data-price="${escapeHtml(String(item.price).replace(/[^0-9.]/g, ''))}" 
								data-display-price="${escapeHtml(item.price)}" 
								data-image="${escapeHtml(item.image)}" 
								data-url="${escapeHtml(item.url)}" 
								data-type="${escapeHtml(item.type)}">
								Add to Cart
							</button>
						</div>
					</div>
				</th>
			`;
		}).join('');
		const rows = [
			['Price', (item) => compareCell(item.price)],
			['Type', (item) => compareCell(item.type ? item.type.replace(/_/g, ' ') : '')],
			['Rating', (item) => compareCell(item.rating ? `${item.rating}/5 (${item.reviews || 0})` : '')],
			['Categories', (item) => compareCell(item.categories)],
			['Summary', (item) => compareCell(item.description)]
		].map(([label, renderer]) => `
			<tr>
				<th scope="row">${label}</th>
				${compareItems.map((item) => `<td>${renderer(item)}</td>`).join('')}
			</tr>
		`).join('');

		wrap.innerHTML = `
			<table class="omnify-compare-table">
				<thead>
					<tr><th></th>${productHeaders}</tr>
				</thead>
				<tbody>${rows}</tbody>
			</table>
		`;

		// Bind Remove Button inside Table Header Card
		wrap.querySelectorAll('.omnify-compare-table-remove-btn').forEach((btn) => {
			btn.addEventListener('click', () => {
				const id = String(btn.dataset.removeCompare || '');
				compareItems = compareItems.filter((item) => String(item.id) !== id);
				setCompareItems(compareItems);
				renderCompare();
				if (compareItems.length < 2) {
					closeCompareModal();
				} else {
					openCompareModal();
				}
			});
		});

		// Bind Add to Cart Button inside Table Header Card
		wrap.querySelectorAll('.omnify-compare-table-cart-btn').forEach((btn) => {
			btn.addEventListener('click', (e) => {
				e.preventDefault();
				e.stopPropagation();
				const item = {
					id: btn.dataset.id,
					name: btn.dataset.name,
					price: parseFloat(btn.dataset.price || 0),
					displayPrice: btn.dataset.displayPrice || formatStoreMoney(btn.dataset.price),
					image: btn.dataset.image,
					url: btn.dataset.url,
					quantity: 1
				};
				addItemToCart(item);
				showAddedToCartToast(item);
				closeCompareModal();
			});
		});

		modal.classList.add('is-open');
		modal.setAttribute('aria-hidden', 'false');
	}

	function closeCompareModal() {
		const modal = document.getElementById('omnify-compare-modal');
		if (!modal) return;
		modal.classList.remove('is-open');
		modal.setAttribute('aria-hidden', 'true');
	}

	buttons.forEach((button) => {
		button.addEventListener('click', (event) => {
			event.preventDefault();
			event.stopPropagation();
			const product = productFromButton(button);
			if (!product.id) return;

			const exists = compareItems.some((item) => String(item.id) === String(product.id));
			if (exists) {
				compareItems = compareItems.filter((item) => String(item.id) !== String(product.id));
			} else {
				if (compareItems.length >= OMNIFY_COMPARE_LIMIT) {
					compareItems.shift();
				}
				compareItems.push(product);
			}
			setCompareItems(compareItems);
			renderCompare();
		});
	});

	renderCompare();
}

function initWishlistButtons() {
	const buttons = document.querySelectorAll('.omnify-wishlist-button[data-product-id]');
	if (!buttons.length) return;

	const store = window.omnifyStorefront || {};
	const serverIds = Array.isArray(store.wishlistProductIds) ? store.wishlistProductIds.map((id) => parseInt(id, 10)) : [];
	let wishlistIds = store.isLoggedIn ? serverIds : getGuestWishlistIds();

	function buttonText(button, wishlisted) {
		const textEl = button.querySelector('.omnify-wishlist-button__text');
		if (!textEl) return;
		if (button.closest('#omnify-pane-wishlist')) {
			textEl.textContent = wishlisted ? 'Remove' : 'Save';
			return;
		}
		textEl.textContent = wishlisted ? 'Saved' : (button.classList.contains('omnify-pdp-btn-buy') ? 'Add to Wishlist' : 'Save');
	}

	function applyState(productId, wishlisted) {
		document.querySelectorAll(`.omnify-wishlist-button[data-product-id="${productId}"]`).forEach((button) => {
			button.classList.toggle('is-wishlisted', wishlisted);
			button.setAttribute('aria-pressed', wishlisted ? 'true' : 'false');
			const icon = button.querySelector('.omnify-wishlist-button__icon');
			if (icon) {
				icon.textContent = wishlisted ? '♥' : '♡';
			}
			buttonText(button, wishlisted);
		});
	}

	function refreshAll() {
		buttons.forEach((button) => {
			const productId = parseInt(button.dataset.productId || '0', 10);
			applyState(productId, wishlistIds.includes(productId));
		});
	}

	function toggleGuest(productId, triggerButton) {
		const exists = wishlistIds.includes(productId);
		wishlistIds = exists ? wishlistIds.filter((id) => id !== productId) : [...wishlistIds, productId];
		setGuestWishlistIds(wishlistIds);
		applyState(productId, !exists);
		if (triggerButton.closest('#omnify-pane-wishlist') && exists) {
			triggerButton.closest('.omnify-product-card')?.remove();
		}
	}

	function toggleServer(productId, triggerButton) {
		triggerButton.disabled = true;
		window.fetch(`${store.restUrl}/wishlist/toggle`, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': store.nonce
			},
			body: JSON.stringify({ product_id: productId })
		})
		.then((response) => response.json().then((data) => {
			if (!response.ok) {
				throw new Error(data.message || 'Unable to update wishlist.');
			}
			return data;
		}))
		.then((data) => {
			wishlistIds = Array.isArray(data.product_ids) ? data.product_ids.map((id) => parseInt(id, 10)) : wishlistIds;
			applyState(productId, Boolean(data.wishlisted));
			if (triggerButton.closest('#omnify-pane-wishlist') && !data.wishlisted) {
				triggerButton.closest('.omnify-product-card')?.remove();
			}
		})
		.catch((err) => {
			triggerButton.dataset.originalTitle = triggerButton.title || '';
			triggerButton.title = err.message;
		})
		.finally(() => {
			triggerButton.disabled = false;
		});
	}

	buttons.forEach((button) => {
		button.addEventListener('click', (event) => {
			event.preventDefault();
			event.stopPropagation();
			const productId = parseInt(button.dataset.productId || '0', 10);
			if (!productId) return;

			if (store.isLoggedIn && store.restUrl && store.nonce) {
				toggleServer(productId, button);
			} else {
				toggleGuest(productId, button);
			}
		});
	});

	refreshAll();
}

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

	const wrapper = document.createElement('div');
	wrapper.className = 'omnify-custom-select-wrapper';
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

	function updateInputFromSelect() {
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
			if (el.dataset.value === select.value) {
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
		input.select();
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

function getCartItems() {
	try {
		const raw = window.localStorage.getItem(OMNIFY_CART_KEY);
		const parsed = raw ? JSON.parse(raw) : [];
		return Array.isArray(parsed) ? parsed : [];
	} catch (e) {
		return [];
	}
}

function setCartItems(items) {
	window.localStorage.setItem(OMNIFY_CART_KEY, JSON.stringify(items));
	updateCartCount();
	renderMiniCarts();
	document.dispatchEvent(new CustomEvent('omnify:cart-updated', { detail: { items } }));
}

function formatStoreMoney(amount) {
	const store = window.omnifyStorefront || {};
	const symbol = store.currencySymbol || store.currency || 'USD';
	const value = Number(amount || 0).toFixed(2);
	return store.currencyPosition === 'after' ? `${value} ${symbol}` : `${symbol}${value}`;
}

function escapeHtml(value) {
	return String(value || '')
		.replace(/&/g, '&amp;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;')
		.replace(/"/g, '&quot;')
		.replace(/'/g, '&#039;');
}

function initQuickViewButtons() {
	const buttons = document.querySelectorAll('.omnify-quick-view-button');
	if (!buttons.length) return;

	function createQuickViewModal() {
		let modal = document.getElementById('omnify-quick-view-modal');
		if (modal) return modal;

		modal = document.createElement('div');
		modal.id = 'omnify-quick-view-modal';
		modal.className = 'omnify-quick-view-modal';
		modal.setAttribute('aria-hidden', 'true');
		modal.innerHTML = `
			<div class="omnify-quick-view-modal__panel" role="dialog" aria-modal="true" aria-label="Product quick view">
				<button type="button" class="omnify-quick-view-modal__close" aria-label="Close quick view">×</button>
				<div class="omnify-quick-view-modal__media"></div>
				<div class="omnify-quick-view-modal__content">
					<div class="omnify-quick-view-modal__eyebrow"></div>
					<h2></h2>
					<div class="omnify-quick-view-modal__rating"></div>
					<div class="omnify-quick-view-modal__price"></div>
					<div class="omnify-quick-view-modal__stock"></div>
					<div class="omnify-quick-view-modal__description"></div>
					<div class="omnify-quick-view-modal__swatches-container"></div>
					<div class="omnify-quick-view-modal__actions">
						<div class="omnify-quick-view-qty-selector">
							<button type="button" class="omnify-qty-btn omnify-qty-minus">-</button>
							<input type="number" class="omnify-qty-input" value="1" min="1">
							<button type="button" class="omnify-qty-btn omnify-qty-plus">+</button>
						</div>
						<button type="button" class="omnify-quick-view-modal__cart">Add to Cart</button>
						<a href="#" class="omnify-quick-view-modal__details">View Details</a>
					</div>
				</div>
			</div>
		`;
		document.body.appendChild(modal);

		modal.querySelector('.omnify-quick-view-modal__close')?.addEventListener('click', closeQuickViewModal);
		modal.addEventListener('click', (event) => {
			if (event.target === modal) closeQuickViewModal();
		});
		document.addEventListener('keydown', (event) => {
			if (event.key === 'Escape' && modal.classList.contains('is-open')) {
				closeQuickViewModal();
			}
		});

		return modal;
	}

	function closeQuickViewModal() {
		const modal = document.getElementById('omnify-quick-view-modal');
		if (!modal) return;
		modal.classList.remove('is-open');
		modal.setAttribute('aria-hidden', 'true');
		document.body.classList.remove('omnify-quick-view-open');
	}

	function firstText(card, selector) {
		return card?.querySelector(selector)?.textContent?.trim() || '';
	}

	function productFromQuickView(button) {
		const card = button.closest('.omnify-product-card');
		const image = card?.querySelector('.omnify-product-card__image--primary')?.getAttribute('src') || card?.dataset.image || '';
		const title = card?.dataset.productName || firstText(card, '.omnify-product-card__title') || 'Product';
		const url = card?.dataset.url || button.getAttribute('href') || window.location.href;
		const priceHtml = card?.querySelector('.omnify-product-card__price')?.innerHTML || card?.querySelector('.omnify-product-card__list-price')?.innerHTML || '';
		const ratingHtml = card?.querySelector('.omnify-product-card__rating')?.innerHTML || '';
		const swatchesHtml = card?.querySelector('.omnify-product-card__swatches')?.innerHTML || '';
		const stock = firstText(card, '.omnify-product-card__stock') || firstText(card, '.omnify-product-card__list-stock');
		const description = firstText(card, '.omnify-product-card__excerpt') || card?.dataset.description || '';
		const category = firstText(card, '.omnify-product-card__category-label');

		let attributes = [];
		try {
			attributes = JSON.parse(card?.dataset.attributes || '[]');
		} catch (e) {}

		let variations = [];
		try {
			variations = JSON.parse(card?.dataset.variations || '[]');
		} catch (e) {}

		return {
			id: card?.dataset.productId || button.dataset.productId || '',
			name: title,
			url,
			image,
			priceHtml,
			ratingHtml,
			swatchesHtml,
			stock,
			description,
			category,
			price: card?.dataset.price || '',
			displayPrice: card?.dataset.displayPrice || firstText(card, '.omnify-product-card__price') || '',
			type: card?.dataset.type || '',
			attributes: attributes,
			variations: variations
		};
	}

	function openQuickView(button) {
		const modal = createQuickViewModal();
		const product = productFromQuickView(button);

		const media = modal.querySelector('.omnify-quick-view-modal__media');
		const eyebrow = modal.querySelector('.omnify-quick-view-modal__eyebrow');
		const title = modal.querySelector('h2');
		const rating = modal.querySelector('.omnify-quick-view-modal__rating');
		const price = modal.querySelector('.omnify-quick-view-modal__price');
		const stock = modal.querySelector('.omnify-quick-view-modal__stock');
		const description = modal.querySelector('.omnify-quick-view-modal__description');
		const swatchesContainer = modal.querySelector('.omnify-quick-view-modal__swatches-container');
		const details = modal.querySelector('.omnify-quick-view-modal__details');
		const cart = modal.querySelector('.omnify-quick-view-modal__cart');

		if (media) {
			media.innerHTML = product.image
				? `<img src="${escapeHtml(product.image)}" alt="${escapeHtml(product.name)}">`
				: `<div class="omnify-quick-view-modal__placeholder">${escapeHtml(product.name.charAt(0))}</div>`;
		}
		if (eyebrow) {
			eyebrow.textContent = product.category;
			eyebrow.hidden = !product.category;
		}
		if (title) title.textContent = product.name;
		if (rating) {
			rating.innerHTML = product.ratingHtml;
			rating.hidden = !product.ratingHtml;
		}
		if (price) price.innerHTML = product.priceHtml || escapeHtml(product.displayPrice);
		if (stock) {
			stock.textContent = product.stock;
			stock.hidden = !product.stock;
		}
		if (description) {
			description.textContent = product.description;
			description.hidden = !product.description;
		}

		if (details) details.href = product.url;

		// Option Selectors Setup
		let selectedOptions = {};
		let matchedVariation = null;

		if (swatchesContainer) {
			swatchesContainer.innerHTML = '';
			if (product.type === 'variable' && product.attributes && product.attributes.length > 0) {
				product.attributes.forEach((attr) => {
					const attrName = attr.name;
					const attrNameLc = attrName.toLowerCase();
					const options = attr.options || [];

					const group = document.createElement('div');
					group.className = 'omnify-quick-view-attr-group';
					group.innerHTML = `
						<label class="omnify-quick-view-attr-label">${escapeHtml(attrName)}</label>
						<div class="omnify-quick-view-attr-options" data-attribute="${escapeHtml(attrNameLc)}">
							${options.map((opt) => {
								if (attrNameLc === 'color' || attrNameLc === 'colour') {
									return `
										<button type="button" class="omnify-quick-view-attr-btn omnify-quick-view-attr-btn--color" data-value="${escapeHtml(opt)}" title="${escapeHtml(opt)}" style="background-color: ${escapeHtml(opt.toLowerCase())};"></button>
									`;
								} else {
									return `
										<button type="button" class="omnify-quick-view-attr-btn omnify-quick-view-attr-btn--text" data-value="${escapeHtml(opt)}">${escapeHtml(opt)}</button>
									`;
								}
							}).join('')}
						</div>
					`;
					swatchesContainer.appendChild(group);
				});

				swatchesContainer.querySelectorAll('.omnify-quick-view-attr-btn').forEach((btn) => {
					btn.addEventListener('click', (e) => {
						e.preventDefault();
						const group = btn.closest('.omnify-quick-view-attr-options');
						const attrName = group.dataset.attribute;
						const value = btn.dataset.value;

						group.querySelectorAll('.omnify-quick-view-attr-btn').forEach((b) => b.classList.remove('is-active'));
						btn.classList.add('is-active');

						selectedOptions[attrName] = value;
						updateVariationState();
					});
				});

				swatchesContainer.hidden = false;
			} else {
				swatchesContainer.hidden = true;
			}
		}

		function updateVariationState() {
			if (product.type !== 'variable') {
				matchedVariation = null;
				cart.disabled = false;
				cart.textContent = 'Add to Cart';
				return;
			}

			const allSelected = product.attributes.every(attr => selectedOptions[attr.name.toLowerCase()]);

			if (allSelected) {
				matchedVariation = product.variations.find(v => {
					return Object.keys(v.attributes).every(key => {
						const val = v.attributes[key];
						return String(val).toLowerCase() === String(selectedOptions[key.toLowerCase()]).toLowerCase();
					});
				});

				if (matchedVariation) {
					const actualPrice = matchedVariation.sale_price !== null ? matchedVariation.sale_price : matchedVariation.price;
					const formattedPrice = formatStoreMoney(actualPrice);
					if (price) {
						if (matchedVariation.sale_price !== null && matchedVariation.sale_price < matchedVariation.price) {
							price.innerHTML = `
								<span class="sale" style="color: #ef4444; font-weight: 500;">${formattedPrice}</span>
								<span class="original-price" style="text-decoration: line-through; color: #94a3b8; margin-left: 8px; font-size: 14px;">${formatStoreMoney(matchedVariation.price)}</span>
							`;
						} else {
							price.innerHTML = `<span style="font-weight: 500; color: #0f172a;">${formattedPrice}</span>`;
						}
					}

					if (stock) {
						if (matchedVariation.stock_status === 'outofstock') {
							stock.textContent = 'Out of Stock';
							stock.style.color = '#ef4444';
							stock.hidden = false;
							cart.disabled = true;
							cart.textContent = 'Out of Stock';
						} else {
							stock.textContent = matchedVariation.stock_qty !== null ? `${matchedVariation.stock_qty} in stock` : 'In Stock';
							stock.style.color = '#15803d';
							stock.hidden = false;
							cart.disabled = false;
							cart.textContent = 'Add to Cart';
						}
					}
				} else {
					if (price) price.textContent = 'Unavailable';
					if (stock) stock.hidden = true;
					cart.disabled = true;
					cart.textContent = 'Unavailable';
				}
			} else {
				matchedVariation = null;
				if (price) price.innerHTML = product.priceHtml || escapeHtml(product.displayPrice);
				if (stock) stock.hidden = true;
				cart.disabled = true;
				cart.textContent = 'Select Options';
			}
		}

		// Initial state trigger
		updateVariationState();

		// Qty Stepper setup
		const qtyInput = modal.querySelector('.omnify-qty-input');
		const qtyMinus = modal.querySelector('.omnify-qty-minus');
		const qtyPlus = modal.querySelector('.omnify-qty-plus');

		if (qtyInput) {
			qtyInput.value = 1;
		}

		if (qtyMinus && qtyInput) {
			qtyMinus.onclick = (e) => {
				e.preventDefault();
				const val = parseInt(qtyInput.value || 1, 10);
				if (val > 1) {
					qtyInput.value = val - 1;
				}
			};
		}

		if (qtyPlus && qtyInput) {
			qtyPlus.onclick = (e) => {
				e.preventDefault();
				const val = parseInt(qtyInput.value || 1, 10);
				qtyInput.value = val + 1;
			};
		}

		// Add to Cart setup
		if (cart) {
			cart.hidden = false;
			cart.dataset.id = product.id;
			cart.dataset.name = product.name;
			cart.dataset.price = product.price;
			cart.dataset.displayPrice = product.displayPrice;
			cart.dataset.image = product.image;
			cart.dataset.url = product.url;

			cart.onclick = (e) => {
				e.preventDefault();
				if (cart.disabled) return;

				const qty = qtyInput ? parseInt(qtyInput.value || 1, 10) : 1;

				let cartItem = {
					id: product.id,
					name: product.name,
					price: parseFloat(product.price || 0),
					displayPrice: product.displayPrice || formatStoreMoney(product.price),
					image: product.image,
					url: product.url,
					quantity: qty
				};

				if (product.type === 'variable') {
					if (!matchedVariation) return;

					const attributesLabel = Object.keys(selectedOptions).map(k => {
						const keyNice = product.attributes.find(a => a.name.toLowerCase() === k)?.name || k;
						return `${keyNice}: ${selectedOptions[k]}`;
					}).join(', ');

					cartItem = {
						id: product.id,
						variationId: matchedVariation.id,
						name: `${product.name} - ${attributesLabel}`,
						price: parseFloat(matchedVariation.sale_price !== null ? matchedVariation.sale_price : matchedVariation.price),
						displayPrice: formatStoreMoney(matchedVariation.sale_price !== null ? matchedVariation.sale_price : matchedVariation.price),
						image: product.image,
						url: product.url,
						quantity: qty,
						variationAttributes: selectedOptions
					};
				}

				addItemToCart(cartItem);
				showAddedToCartToast(cartItem);

				try {
					omnifyTrackAddToCart(cartItem);
				} catch (err) {
					console.error('Error tracking add to cart:', err);
				}

				closeQuickViewModal();
			};
		}

		modal.classList.add('is-open');
		modal.setAttribute('aria-hidden', 'false');
		document.body.classList.add('omnify-quick-view-open');
	}

	document.addEventListener('click', (event) => {
		const target = event.target instanceof Element ? event.target : event.target?.parentElement;
		const button = target?.closest('.omnify-quick-view-button');
		if (!button) return;

		event.preventDefault();
		event.stopPropagation();
		openQuickView(button);
	}, true);
}

function initLocationSelects(root) {
	const scope = root || document;
	const store = window.omnifyStorefront || {};
	const countries = store.countries || {};
	const statesByCountry = store.states || {};

	function makeOption(label, value, selectedValue) {
		const opt = document.createElement('option');
		opt.value = value;
		opt.textContent = label;
		if (String(selectedValue || '') === String(value)) opt.selected = true;
		return opt;
	}

	function refreshState(countrySelect) {
		const container = countrySelect.closest('.omnify-portal-form-row') || countrySelect.closest('.omnify-form-row') || countrySelect.closest('div');
		let stateSelect = container ? container.querySelector('.omnify-state-select') : null;
		if (!stateSelect) {
			const form = countrySelect.closest('form') || document;
			const id = countrySelect.id;
			if (id === 'shipping_country') {
				stateSelect = form.querySelector('#shipping_state');
			} else if (id === 'billing_country') {
				stateSelect = form.querySelector('#billing_state');
			} else if (id === 'omnify-shipping-country') {
				stateSelect = form.querySelector('#omnify-shipping-state');
			} else if (id === 'omnify-billing-country') {
				stateSelect = form.querySelector('#omnify-billing-state');
			} else {
				const section = countrySelect.closest('.omnify-portal-section-card') || countrySelect.closest('.omnify-card') || countrySelect.closest('fieldset');
				if (section) {
					stateSelect = section.querySelector('.omnify-state-select');
				}
			}
		}
		if (!stateSelect) return;

		const country = countrySelect.value || '';
		const selected = stateSelect.dataset.selected || stateSelect.value || '';
		const states = country && statesByCountry[country] ? statesByCountry[country] : {};
		stateSelect.innerHTML = '';

		if (!country) {
			stateSelect.appendChild(makeOption('Select country first', '', selected));
			stateSelect.disabled = true;
			stateSelect.dispatchEvent(new Event('change', { bubbles: true }));
			stateSelect.dispatchEvent(new Event('input', { bubbles: true }));
			return;
		}

		stateSelect.disabled = false;
		stateSelect.appendChild(makeOption('Select state / province', '', selected));
		Object.keys(states).forEach((code) => {
			stateSelect.appendChild(makeOption(states[code], code, selected));
		});
		if (!Object.keys(states).length) {
			stateSelect.appendChild(makeOption('Other / not listed', 'OTHER', selected));
		}
		stateSelect.dispatchEvent(new Event('change', { bubbles: true }));
		stateSelect.dispatchEvent(new Event('input', { bubbles: true }));
	}

	scope.querySelectorAll('#omnify-shipping-country, .omnify-country-select').forEach((select) => {
		if (!select.dataset.locationBound) {
			select.addEventListener('change', () => {
				refreshState(select);
				select.dispatchEvent(new Event('input', { bubbles: true }));
			});
			select.dataset.locationBound = '1';
		}
		refreshState(select);
	});

	scope.querySelectorAll('.omnify-searchable-select').forEach((select) => {
		if (window.omnifyInitSearchableSelect) {
			window.omnifyInitSearchableSelect(select);
		}
	});
}

function updateCartCount() {
	const count = getCartItems().reduce((sum, item) => sum + Math.max(1, parseInt(item.quantity || 1, 10) || 1), 0);
	document.querySelectorAll('#omnify-cart-count, .omnify-cart-count').forEach((el) => {
		el.textContent = String(count);
	});
}

function cartSubtotal(items) {
	return items.reduce((sum, item) => {
		const price = parseFloat(item.price || 0) || 0;
		const qty = Math.max(1, parseInt(item.quantity || 1, 10) || 1);
		return sum + (price * qty);
	}, 0);
}

function getCheckoutCartItems() {
	try {
		const selectedItems = JSON.parse(window.sessionStorage.getItem(OMNIFY_CHECKOUT_SELECTED_KEY) || '[]');
		if (Array.isArray(selectedItems) && selectedItems.length) {
			return selectedItems;
		}
	} catch (e) {
		return getCartItems();
	}
	return getCartItems();
}

function setCheckoutCartItems(items) {
	window.sessionStorage.setItem(OMNIFY_CHECKOUT_SELECTED_KEY, JSON.stringify(items));
}

function getOmnifyCartToken() {
	let token = '';
	try {
		token = window.localStorage.getItem('omnify_cart_token') || '';
	} catch (e) {}
	if (!token && typeof document !== 'undefined') {
		const match = document.cookie.match(/(?:^|;\s*)omnify_cart_token=([^;]+)/);
		if (match) {
			token = decodeURIComponent(match[1]);
			try {
				window.localStorage.setItem('omnify_cart_token', token);
			} catch (e) {}
		}
	}
	return token;
}

function setOmnifyCartToken(token) {
	if (!token) return;
	try {
		window.localStorage.setItem('omnify_cart_token', token);
	} catch (e) {}
	try {
		document.cookie = `omnify_cart_token=${encodeURIComponent(token)}; path=/; max-age=2592000; SameSite=Lax`;
	} catch (e) {}
}

function saveCartForCheckout(items) {
	if (!items.length || !window.omnifyStorefront || !window.omnifyStorefront.restUrl) {
		return Promise.reject(new Error('Cart is empty.'));
	}

	const formattedItems = items.map(cartItem => ({
		product_id: parseInt(cartItem.id, 10),
		variation_id: cartItem.variationId ? parseInt(cartItem.variationId, 10) : null,
		quantity: parseInt(cartItem.quantity || 1, 10)
	}));

	return window.fetch(`${window.omnifyStorefront.restUrl}/cart`, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			'X-WP-Nonce': window.omnifyStorefront.nonce
		},
		body: JSON.stringify({ items: formattedItems })
	}).then(res => {
		if (!res.ok) throw new Error('Failed to save cart');
		return res.json();
	}).then(data => {
		if (data.token) {
			setOmnifyCartToken(data.token);
		}
		return data;
	});
}

function addItemToCart(item) {
	const items = getCartItems();
	const existingIndex = items.findIndex((cartItem) => String(cartItem.id) === String(item.id) && String(cartItem.variationId || '') === String(item.variationId || ''));
	const qty = parseInt(item.quantity || 1, 10);
	if (existingIndex >= 0) {
		const existingQty = parseInt(items[existingIndex].quantity || 1, 10);
		items[existingIndex] = { ...items[existingIndex], ...item, quantity: existingQty + qty };
	} else {
		items.push({ ...item, quantity: qty });
	}
	setCartItems(items);
}

function renderMiniCarts() {
	const miniCarts = document.querySelectorAll('.omnify-mini-cart');
	if (!miniCarts.length) return;

	const items = getCartItems();
	const subtotal = cartSubtotal(items);

	miniCarts.forEach((miniCart) => {
		const itemsEl = miniCart.querySelector('.omnify-mini-cart__items');
		const emptyEl = miniCart.querySelector('.omnify-mini-cart__empty');
		const footerEl = miniCart.querySelector('.omnify-mini-cart__footer');
		const subtotalEl = miniCart.querySelector('.omnify-mini-cart__subtotal-value');

		if (subtotalEl) subtotalEl.textContent = formatStoreMoney(subtotal);
		if (emptyEl) emptyEl.style.display = items.length ? 'none' : 'block';
		if (footerEl) footerEl.style.display = items.length ? 'grid' : 'none';

		if (itemsEl) {
			itemsEl.innerHTML = items.map((item) => {
				const qty = Math.max(1, parseInt(item.quantity || 1, 10) || 1);
				const lineTotal = (parseFloat(item.price || 0) || 0) * qty;
				return `
					<div class="omnify-mini-cart-item" data-id="${escapeHtml(item.id)}" data-variation-id="${escapeHtml(item.variationId || '')}">
						<div class="omnify-mini-cart-item__media">${item.image ? `<img src="${escapeHtml(item.image)}" alt="">` : `<span>${escapeHtml(String(item.name || 'P').charAt(0))}</span>`}</div>
						<div class="omnify-mini-cart-item__body">
							<strong>${escapeHtml(item.name || 'Product')}</strong>
							<span>${escapeHtml(item.displayPrice || formatStoreMoney(item.price))}</span>
							${item.variationId ? `<small>Variation #${escapeHtml(item.variationId)}</small>` : ''}
							<div class="omnify-mini-cart-item__qty" aria-label="Quantity">
								<button type="button" class="omnify-mini-cart__qty-btn" data-action="decrease">−</button>
								<span>${qty}</span>
								<button type="button" class="omnify-mini-cart__qty-btn" data-action="increase">+</button>
							</div>
						</div>
						<div class="omnify-mini-cart-item__side">
							<strong>${formatStoreMoney(lineTotal)}</strong>
							<button type="button" class="omnify-mini-cart__remove">Remove</button>
						</div>
					</div>
				`;
			}).join('');
		}
	});
}

function closeMiniCart(miniCart) {
	if (!miniCart) return;
	miniCart.classList.remove('is-open');
	miniCart.querySelector('.omnify-mini-cart__toggle')?.setAttribute('aria-expanded', 'false');
	miniCart.querySelector('.omnify-mini-cart__panel')?.setAttribute('aria-hidden', 'true');
}

function initMiniCarts() {
	const miniCarts = document.querySelectorAll('.omnify-mini-cart');
	if (!miniCarts.length) return;

	miniCarts.forEach((miniCart) => {
		if (miniCart.dataset.ready === '1') return;

		const toggle = miniCart.querySelector('.omnify-mini-cart__toggle');
		const closeBtn = miniCart.querySelector('.omnify-mini-cart__close');
		const checkoutBtn = miniCart.querySelector('.omnify-mini-cart__checkout');

		toggle?.addEventListener('click', (event) => {
			event.preventDefault();
			const willOpen = !miniCart.classList.contains('is-open');
			document.querySelectorAll('.omnify-mini-cart.is-open').forEach(closeMiniCart);
			miniCart.classList.toggle('is-open', willOpen);
			toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
			miniCart.querySelector('.omnify-mini-cart__panel')?.setAttribute('aria-hidden', willOpen ? 'false' : 'true');
			renderMiniCarts();
		});

		closeBtn?.addEventListener('click', () => closeMiniCart(miniCart));

		miniCart.addEventListener('click', (event) => {
			const itemEl = event.target.closest('.omnify-mini-cart-item');
			if (!itemEl) return;

			const qtyBtn = event.target.closest('.omnify-mini-cart__qty-btn');
			const removeBtn = event.target.closest('.omnify-mini-cart__remove');
			if (!qtyBtn && !removeBtn) return;

			event.preventDefault();
			const id = String(itemEl.dataset.id || '');
			const variationId = String(itemEl.dataset.variationId || '');
			let items = getCartItems();

			if (removeBtn) {
				items = items.filter((item) => !(String(item.id) === id && String(item.variationId || '') === variationId));
			} else if (qtyBtn) {
				items = items.map((item) => {
					if (!(String(item.id) === id && String(item.variationId || '') === variationId)) return item;
					const current = Math.max(1, parseInt(item.quantity || 1, 10) || 1);
					const next = qtyBtn.dataset.action === 'increase' ? current + 1 : Math.max(1, current - 1);
					return { ...item, quantity: next };
				});
			}

			setCartItems(items);
		});

		checkoutBtn?.addEventListener('click', () => {
			const items = getCartItems();
			const checkoutUrl = miniCart.dataset.checkoutUrl || (window.omnifyStorefront && window.omnifyStorefront.checkoutUrl) || '';
			if (!items.length || !checkoutUrl) return;

			checkoutBtn.disabled = true;
			const originalText = checkoutBtn.textContent;
			checkoutBtn.textContent = 'Preparing...';

			saveCartForCheckout(items)
				.then(() => {
					window.location.href = `${checkoutUrl}?cart=1`;
				})
				.catch((err) => {
					console.error(err);
					checkoutBtn.disabled = false;
					checkoutBtn.textContent = originalText;
				});
		});

		miniCart.dataset.ready = '1';
	});

	if (document.body && document.body.dataset.omnifyMiniCartOutsideClick !== '1') {
		document.addEventListener('click', (event) => {
			if (!event.target.closest('.omnify-mini-cart')) {
				document.querySelectorAll('.omnify-mini-cart.is-open').forEach(closeMiniCart);
			}
		});
		document.body.dataset.omnifyMiniCartOutsideClick = '1';
	}

	renderMiniCarts();
	updateCartCount();
}

function cartItemFromButton(btn) {
	return {
		id: btn.getAttribute('data-id') || '',
		variationId: btn.getAttribute('data-variation-id') || '',
		name: btn.getAttribute('data-name') || 'Product',
		price: parseFloat(btn.getAttribute('data-price') || '0'),
		displayPrice: btn.getAttribute('data-display-price') || '',
		image: btn.getAttribute('data-image') || '',
		url: btn.getAttribute('data-url') || '',
		checkoutUrl: btn.getAttribute('data-checkout-url') || '',
		quantity: parseInt(btn.getAttribute('data-quantity') || '1', 10)
	};
}

function initStorefrontFilters() {
	const grid = document.getElementById('omnify-storefront-grid');
	if (!grid) return;

	const cards = Array.from(grid.querySelectorAll('.omnify-product-card'));
	const searchInput = document.getElementById('omnify-product-search');
	const categoryFilter = document.getElementById('omnify-category-filter');
	const typeFilter = document.getElementById('omnify-type-filter');
	const customFilters = Array.from(document.querySelectorAll('.omnify-custom-attribute-filter'));
	const sortFilter = document.getElementById('omnify-sort-filter');
	const emptyState = document.getElementById('omnify-storefront-empty-filter');
	const results = document.getElementById('omnify-storefront-results');
	const defaultSort = grid.dataset.defaultSort || 'latest';
	if (sortFilter && !sortFilter.value) {
		sortFilter.value = defaultSort;
	}

	function normalizeSlug(str) {
		return String(str || '')
			.toLowerCase()
			.trim()
			.replace(/[^a-z0-9\-_]/g, '-')
			.replace(/-+/g, '-')
			.replace(/^-+|-+$/g, '');
	}

	const initialLimit = parseInt(grid.dataset.productsLimit || '8', 10);

	function applyFilters(isLoadMore = false) {
		if (isLoadMore !== true) {
			grid.dataset.productsLimit = String(initialLimit);
		}
		const productsLimit = Math.max(0, parseInt(grid.dataset.productsLimit || '0', 10));
		const query = (searchInput?.value || '').trim().toLowerCase();
		let category = categoryFilter?.value || '';
		const sidebarCategoryCheck = document.querySelector('.omnify-sidebar-category-check:checked');
		if (sidebarCategoryCheck) {
			category = sidebarCategoryCheck.value;
		}
		const type = typeFilter?.value || '';
		const matchingCards = [];
		let visible = 0;

		// Premium specific filters state
		const activeColors = Array.from(document.querySelectorAll('.omnify-filter-swatch.color.is-active')).map(s => s.dataset.value);
		const activeSizes = Array.from(document.querySelectorAll('.omnify-filter-swatch.size.is-active')).map(s => s.dataset.value);
		
		const activePriceRange = document.querySelector('.omnify-filter-group[data-group="price"] .omnify-filter-link.is-active');
		let priceMin = activePriceRange && activePriceRange.dataset.min ? parseFloat(activePriceRange.dataset.min) : null;
		let priceMax = activePriceRange && activePriceRange.dataset.max ? parseFloat(activePriceRange.dataset.max) : null;

		const sidebarMin = document.getElementById('omnify-sidebar-price-min');
		const sidebarMax = document.getElementById('omnify-sidebar-price-max');
		if (sidebarMin || sidebarMax) {
			const minVal = sidebarMin ? parseFloat(sidebarMin.value) : 0;
			const maxVal = sidebarMax ? parseFloat(sidebarMax.value) : Infinity;
			priceMin = isNaN(minVal) ? 0 : minVal;
			priceMax = isNaN(maxVal) ? Infinity : maxVal;
		}

		const activeStatuses = Array.from(document.querySelectorAll('.omnify-filter-status-check:checked')).map(c => c.value);
		const activeBrands = Array.from(document.querySelectorAll('.omnify-filter-brand-check:checked')).map(c => c.value);
		const activeBrandLinks = Array.from(document.querySelectorAll('.omnify-filter-group[data-group="brand"] .omnify-filter-link.is-active'))
			.map(link => link.dataset.value || '')
			.filter(Boolean);
		activeBrands.push(...activeBrandLinks);

		const activeLabels = Array.from(document.querySelectorAll('.omnify-sidebar-label-check:checked')).map(c => c.value);

		cards.forEach((card) => {
			const matchesQuery = !query || (card.dataset.search || '').includes(query);
			const matchesCategory = !category || (card.dataset.categories || '').split(/\s+/).includes(category);
			const cardType = card.dataset.type || '';
			const normalizedType = cardType === 'download' ? 'digital' : cardType;
			const matchesType = !type || normalizedType === type || cardType === type;

			// Match custom attribute filters
			let matchesCustomAttrs = true;
			for (const filterSelect of customFilters) {
				const attrSlug = filterSelect.dataset.attribute;
				const selectedVal = filterSelect.value;
				if (!selectedVal) continue; // skip if "All" is selected

				let cardHasAttrMatch = false;
				try {
					const attrs = JSON.parse(card.dataset.attributes || '[]');
					if (Array.isArray(attrs)) {
						cardHasAttrMatch = attrs.some((attr) => {
							const currentAttrSlug = normalizeSlug(attr.name);
							if (currentAttrSlug !== attrSlug) return false;
							const options = Array.isArray(attr.options) ? attr.options : [];
							return options.some((opt) => normalizeSlug(opt) === selectedVal || String(opt).toLowerCase().trim() === selectedVal);
						});
					}
				} catch (e) {
					console.error('Error parsing product attributes', e);
				}

				if (!cardHasAttrMatch) {
					matchesCustomAttrs = false;
					break;
				}
			}

			// Premium Color Matching
			let matchesColor = true;
			if (activeColors.length > 0) {
				matchesColor = false;
				try {
					const attrs = JSON.parse(card.dataset.attributes || '[]');
					if (Array.isArray(attrs)) {
						matchesColor = attrs.some((attr) => {
							const name_lower = attr.name.toLowerCase();
							if (name_lower === 'color' || name_lower === 'colour') {
								return attr.options.some((opt) => activeColors.includes(String(opt).toLowerCase().trim()));
							}
							return false;
						});
					}
				} catch (e) {
					console.error(e);
				}
			}

			// Premium Size Matching
			let matchesSize = true;
			if (activeSizes.length > 0) {
				matchesSize = false;
				try {
					const attrs = JSON.parse(card.dataset.attributes || '[]');
					if (Array.isArray(attrs)) {
						matchesSize = attrs.some((attr) => {
							const name_lower = attr.name.toLowerCase();
							if (name_lower === 'size') {
								return attr.options.some((opt) => activeSizes.includes(String(opt).toLowerCase().trim()));
							}
							return false;
						});
					}
				} catch (e) {
					console.error(e);
				}
			}

			// Premium Price Range Matching
			let matchesPrice = true;
			if (priceMin !== null || priceMax !== null) {
				const cardPrice = parseFloat(card.dataset.price || '0');
				const min = priceMin !== null ? priceMin : 0;
				const max = priceMax !== null ? priceMax : Infinity;
				matchesPrice = cardPrice >= min && cardPrice <= max;
			}

			// Premium Status Matching
			let matchesStatus = true;
			if (activeStatuses.length > 0) {
				if (activeStatuses.includes('onsale')) {
					matchesStatus = matchesStatus && (card.dataset.onsale === '1' || !!card.querySelector('.current-price.sale'));
				}
				if (activeStatuses.includes('instock')) {
					const stockStatus = (card.dataset.stockStatus || '').toLowerCase();
					matchesStatus = matchesStatus && (stockStatus === 'instock' || stockStatus === 'in_stock' || !(card.textContent || '').toLowerCase().includes('out of stock'));
				}
			}

			// Premium Brand Matching
			let matchesBrand = true;
			if (activeBrands.length > 0) {
				const brandSlugs = (card.dataset.brands || '').split(/\s+/).filter(Boolean);
				const nameLower = (card.dataset.productName || '').toLowerCase();
				const searchLower = (card.dataset.search || '').toLowerCase();
				matchesBrand = activeBrands.some((brand) => brandSlugs.includes(normalizeSlug(brand)) || nameLower.includes(brand) || searchLower.includes(brand));
			}

			let matchesLabels = true;
			if (activeLabels.length > 0) {
				matchesLabels = activeLabels.every(label => {
					if (label === 'hot') return card.dataset.isHot === '1';
					if (label === 'new') return card.dataset.isNew === '1';
					if (label === 'sale') return card.dataset.onsale === '1';
					return true;
				});
			}

			const show = matchesQuery && matchesCategory && matchesType && matchesCustomAttrs && 
						 matchesColor && matchesSize && matchesPrice && matchesStatus && matchesBrand && matchesLabels;
			
			if (show) matchingCards.push(card);
			card.style.setProperty('display', 'none', 'important');
		});

		const sorted = [...cards].sort((a, b) => {
			const sort = sortFilter?.value || 'latest';
			if (sort === 'price_asc') return parseFloat(a.dataset.price || '0') - parseFloat(b.dataset.price || '0');
			if (sort === 'price_desc') return parseFloat(b.dataset.price || '0') - parseFloat(a.dataset.price || '0');
			if (sort === 'name_asc') return (a.dataset.productName || '').localeCompare(b.dataset.productName || '');
			return 0;
		});
		sorted.forEach((card) => grid.appendChild(card));
		sorted.forEach((card) => {
			if (!matchingCards.includes(card)) return;
			if (productsLimit && visible >= productsLimit) return;
			card.style.removeProperty('display');
			visible += 1;
		});

		if (emptyState) {
			emptyState.style.display = (cards.length > 0 && matchingCards.length === 0) ? 'block' : 'none';
		}
		if (results) {
			results.textContent = productsLimit && matchingCards.length > visible
				? `${visible} of ${matchingCards.length} results`
				: `${visible} results`;
		}

		const loadMoreBtn = document.getElementById('omnify-load-more-btn');
		if (loadMoreBtn) {
			const remainingMatching = matchingCards.length - visible;
			if (remainingMatching > 0) {
				loadMoreBtn.style.display = '';
			} else {
				loadMoreBtn.style.display = 'none';
			}
		}
	}

	// ── Premium Drawer Toggle & Bindings ─────────────────────────
	const drawer = document.getElementById('omnify-filter-drawer');
	const triggerBtns = document.querySelectorAll('.omnify-filter-trigger-btn');
	triggerBtns.forEach(btn => {
		btn.addEventListener('click', (e) => {
			e.preventDefault();
			const group = btn.dataset.group;
			const isAlreadyActive = btn.classList.contains('is-active');

			triggerBtns.forEach(b => b.classList.remove('is-active'));
			
			if (drawer) {
				const groups = drawer.querySelectorAll('.omnify-filter-group');
				groups.forEach(g => g.style.display = 'none');
				
				if (!isAlreadyActive) {
					btn.classList.add('is-active');
					const targetGroup = drawer.querySelector(`.omnify-filter-group[data-group="${group}"]`);
					if (targetGroup) targetGroup.style.display = 'block';
					
					drawer.style.display = 'block';
					drawer.style.maxHeight = drawer.scrollHeight + 'px';
					drawer.style.opacity = '1';
				} else {
					drawer.style.maxHeight = '0';
					drawer.style.opacity = '0';
					setTimeout(() => { drawer.style.display = 'none'; }, 350);
				}
			}
		});
	});

	// Swatches click selection inside drawer
	document.querySelectorAll('.omnify-filter-swatch').forEach(swatch => {
		swatch.addEventListener('click', (e) => {
			e.preventDefault();
			swatch.classList.toggle('is-active');
			applyFilters();
		});
	});

	// Category links inside drawer selection
	document.querySelectorAll('.omnify-filter-group[data-group="category"] .omnify-filter-link').forEach(link => {
		link.addEventListener('click', (e) => {
			e.preventDefault();
			const val = link.dataset.value;
			
			document.querySelectorAll('.omnify-filter-group[data-group="category"] .omnify-filter-link').forEach(l => l.classList.remove('is-active'));
			link.classList.add('is-active');
			
			const hiddenCatSelect = document.getElementById('omnify-category-filter');
			if (hiddenCatSelect) {
				hiddenCatSelect.value = val;
				hiddenCatSelect.dispatchEvent(new Event('change'));
			}

			// Sync sidebar category checkboxes
			document.querySelectorAll('.omnify-sidebar-category-check').forEach(chk => {
				chk.checked = (chk.value === val);
			});
		});
	});

	// Price links inside drawer selection
	document.querySelectorAll('.omnify-filter-group[data-group="price"] .omnify-filter-link').forEach(link => {
		link.addEventListener('click', (e) => {
			e.preventDefault();
			document.querySelectorAll('.omnify-filter-group[data-group="price"] .omnify-filter-link').forEach(l => l.classList.remove('is-active'));
			link.classList.add('is-active');
			applyFilters();
		});
	});

	// Brand links inside drawer selection
	document.querySelectorAll('.omnify-filter-group[data-group="brand"] .omnify-filter-link').forEach(link => {
		link.addEventListener('click', (e) => {
			e.preventDefault();
			document.querySelectorAll('.omnify-filter-group[data-group="brand"] .omnify-filter-link').forEach(l => l.classList.remove('is-active'));
			link.classList.add('is-active');
			applyFilters();
		});
	});

	// Checkboxes inside drawer selection
	document.querySelectorAll('.omnify-filter-status-check, .omnify-filter-brand-check').forEach(chk => {
		chk.addEventListener('change', applyFilters);
	});

	// Sort Dropdown Interactivity
	const sortToggle = document.getElementById('omnify-sort-toggle');
	const sortOptions = document.getElementById('omnify-sort-options');
	if (sortToggle && sortOptions) {
		sortToggle.addEventListener('click', (e) => {
			e.preventDefault();
			e.stopPropagation();
			sortToggle.classList.toggle('is-active');
			sortOptions.style.display = sortOptions.style.display === 'none' ? 'block' : 'none';
		});
		
		document.addEventListener('click', (e) => {
			if (!e.target.closest('.omnify-sort-dropdown')) {
				sortToggle.classList.remove('is-active');
				sortOptions.style.display = 'none';
			}
		});

		sortOptions.querySelectorAll('a').forEach(opt => {
			opt.addEventListener('click', (e) => {
				e.preventDefault();
				const val = opt.dataset.sort;
				
				sortOptions.querySelectorAll('a').forEach(a => a.classList.remove('is-active'));
				opt.classList.add('is-active');
				
				sortToggle.querySelector('span').textContent = opt.textContent.trim();
				sortToggle.classList.remove('is-active');
				sortOptions.style.display = 'none';

				const hiddenSort = document.getElementById('omnify-sort-filter');
				if (hiddenSort) {
					hiddenSort.value = val;
					hiddenSort.dispatchEvent(new Event('change'));
				}
			});
		});
	}

	// Layout switcher interactivity
	const layoutBtns = document.querySelectorAll('.omnify-layout-btn');
	layoutBtns.forEach(btn => {
		btn.addEventListener('click', (e) => {
			e.preventDefault();
			const cols = btn.dataset.cols;
			
			layoutBtns.forEach(b => b.classList.remove('is-active'));
			btn.classList.add('is-active');

			if (grid) {
				grid.classList.remove(
					'omnify-storefront-grid--grid',
					'omnify-storefront-grid--cols-2',
					'omnify-storefront-grid--cols-3',
					'omnify-storefront-grid--cols-4',
					'omnify-storefront-grid--cols-5',
					'omnify-storefront-grid--list-2',
					'omnify-storefront-grid--list',
					'omnify-cols-2',
					'omnify-cols-3',
					'omnify-cols-4',
					'omnify-cols-5'
				);
				if (cols === 'list') {
					grid.classList.add('omnify-storefront-grid--list');
				} else if (cols === 'list-2') {
					grid.classList.add('omnify-storefront-grid--list-2');
				} else {
					grid.classList.add('omnify-storefront-grid--cols-' + cols);
					grid.classList.add('omnify-cols-' + cols);
				}
			}
		});
	});

	// Sidebar Category Checkbox Selection
	document.querySelectorAll('.omnify-sidebar-category-check').forEach(chk => {
		chk.addEventListener('change', (e) => {
			const val = chk.checked ? chk.value : '';
			if (chk.checked) {
				document.querySelectorAll('.omnify-sidebar-category-check').forEach(other => {
					if (other !== chk) other.checked = false;
				});
			} else {
				const allChk = document.querySelector('.omnify-sidebar-category-check[value=""]');
				if (allChk) {
					allChk.checked = true;
				}
			}
			// Sync top select & active class
			const hiddenCatSelect = document.getElementById('omnify-category-filter');
			if (hiddenCatSelect) {
				hiddenCatSelect.value = val;
			}
			document.querySelectorAll('.omnify-filter-group[data-group="category"] .omnify-filter-link').forEach(l => {
				l.classList.toggle('is-active', (l.dataset.value || '') === val);
			});
			applyFilters();
		});
	});

	// Sidebar Price Range input and slider listener
	const priceMinInput = document.getElementById('omnify-sidebar-price-min');
	const priceMaxInput = document.getElementById('omnify-sidebar-price-max');
	const priceSlider = document.getElementById('omnify-sidebar-price-slider');

	if (priceMinInput) priceMinInput.addEventListener('input', applyFilters);
	if (priceMaxInput) priceMaxInput.addEventListener('input', applyFilters);
	if (priceSlider) {
		priceSlider.addEventListener('input', (e) => {
			const val = e.target.value;
			if (priceMaxInput) {
				priceMaxInput.value = val;
			}
			// Sync top price slider
			const topPriceSlider = document.getElementById('omnify-top-price-slider');
			const topPriceDisplay = document.getElementById('omnify-top-price-display');
			if (topPriceSlider) topPriceSlider.value = val;
			if (topPriceDisplay) topPriceDisplay.textContent = '$' + val;
			applyFilters();
		});
	}

	// Top Price Slider listener
	const topPriceSlider = document.getElementById('omnify-top-price-slider');
	const topPriceDisplay = document.getElementById('omnify-top-price-display');
	if (topPriceSlider) {
		topPriceSlider.addEventListener('input', (e) => {
			const val = topPriceSlider.value;
			if (topPriceDisplay) topPriceDisplay.textContent = '$' + val;
			
			// Sync sidebar
			if (priceSlider) priceSlider.value = val;
			if (priceMaxInput) priceMaxInput.value = val;
			
			applyFilters();
		});
	}

	// Sidebar toggle button click
	const sidebarToggle = document.querySelector('.omnify-sidebar-toggle-btn');
	if (sidebarToggle) {
		sidebarToggle.addEventListener('click', (e) => {
			e.preventDefault();
			const layoutGrid = document.querySelector('.omnify-storefront-layout-grid');
			if (layoutGrid) {
				layoutGrid.classList.toggle('sidebar-hidden');
			}
		});
	}

	// Sidebar labels checkboxes
	document.querySelectorAll('.omnify-sidebar-label-check').forEach(chk => {
		chk.addEventListener('change', applyFilters);
	});

	// Sidebar Clear All trigger
	const clearAllBtn = document.querySelector('.omnify-sidebar-clear-all');
	if (clearAllBtn) {
		clearAllBtn.addEventListener('click', (e) => {
			e.preventDefault();
			
			document.querySelectorAll('.omnify-sidebar-category-check').forEach(chk => {
				chk.checked = chk.value === '';
			});
			if (categoryFilter) categoryFilter.value = '';

			document.querySelectorAll('.omnify-sidebar-label-check').forEach(chk => {
				chk.checked = false;
			});

			document.querySelectorAll('.omnify-filter-swatch').forEach(s => s.classList.remove('is-active'));

			if (searchInput) searchInput.value = '';
			if (priceMinInput) priceMinInput.value = '0';
			const maxVal = priceSlider ? priceSlider.getAttribute('max') || '1000' : '1000';
			if (priceMaxInput) priceMaxInput.value = maxVal;
			if (priceSlider) priceSlider.value = maxVal;

			document.querySelectorAll('.omnify-filter-status-check, .omnify-filter-brand-check').forEach(chk => {
				chk.checked = false;
			});

			applyFilters();
		});
	}

	// Standard filter registers
	[searchInput, categoryFilter, typeFilter, sortFilter, ...customFilters].forEach((control) => {
		if (!control) return;
		control.addEventListener('input', applyFilters);
		control.addEventListener('change', applyFilters);
	});

	// Sidebar Accordion Widget Toggle Collapse/Expand (Event Delegation)
	document.addEventListener('click', (e) => {
		const title = e.target.closest('.omnify-sidebar-widget-title');
		if (title) {
			const widget = title.closest('.omnify-sidebar-widget');
			if (widget) {
				widget.classList.toggle('active');
			}
		}
	});

	// Centralized Load More button logic
	const loadMoreBtn = document.getElementById('omnify-load-more-btn');
	if (loadMoreBtn) {
		loadMoreBtn.addEventListener('click', (e) => {
			e.preventDefault();
			if (loadMoreBtn.disabled) return;

			loadMoreBtn.disabled = true;
			const origHtml = loadMoreBtn.innerHTML;
			loadMoreBtn.innerHTML = `<span class="omnify-btn-spinner"></span> Loading...`;

			setTimeout(() => {
				let currentLimit = parseInt(grid.dataset.productsLimit || '8', 10);
				currentLimit += 8;
				grid.dataset.productsLimit = String(currentLimit);

				applyFilters(true);

				loadMoreBtn.innerHTML = origHtml;
				loadMoreBtn.disabled = false;
			}, 600);
		});
	}

	applyFilters();
	updateCartCount();
}

/**
 * Storefront grid buy button redirection.
 */
function initStorefrontBuyButtons() {
	const buyButtons = document.querySelectorAll('.omnify-buy-button');
	buyButtons.forEach((btn) => {
		btn.addEventListener('click', () => {
			const id = btn.getAttribute('data-id');
			if (omnifyStorefront && omnifyStorefront.checkoutUrl) {
				window.location.href = `${omnifyStorefront.checkoutUrl}?product_id=${id}`;
			}
		});
	});
}

function showAddedToCartToast(item) {
	let container = document.querySelector('.omnify-toast-container');
	if (!container) {
		container = document.createElement('div');
		container.className = 'omnify-toast-container';
		document.body.appendChild(container);
	}

	const toast = document.createElement('div');
	toast.className = 'omnify-toast';
	
	const cartUrl = (window.omnifyStorefront && window.omnifyStorefront.cartUrl) || '#';
	const checkoutUrl = (window.omnifyStorefront && window.omnifyStorefront.checkoutUrl) || '#';

	toast.innerHTML = `
		<div class="omnify-toast__header">
			<span class="omnify-toast__icon">✓</span>
			<span class="omnify-toast__title">Added to Cart</span>
			<button type="button" class="omnify-toast__close" aria-label="Close">&times;</button>
		</div>
		<div style="font-size: 13px; color: var(--omnify-gray-600); line-height: 1.4;">
			<strong>${escapeHtml(item.name)}</strong> has been added to your cart.
		</div>
		<div class="omnify-toast__actions">
			<a href="${escapeHtml(cartUrl)}" class="omnify-btn omnify-btn--secondary">View Cart</a>
			<button type="button" class="omnify-btn omnify-btn--primary omnify-toast-checkout-btn">Checkout</button>
		</div>
	`;

	container.appendChild(toast);

	// Close handler
	const closeBtn = toast.querySelector('.omnify-toast__close');
	const dismiss = () => {
		toast.classList.add('fade-out');
		setTimeout(() => toast.remove(), 300);
	};
	closeBtn.addEventListener('click', dismiss);

	// Auto dismiss after 6 seconds
	const autoDismissTimeout = setTimeout(dismiss, 6000);

	// Checkout handler
	const checkoutBtn = toast.querySelector('.omnify-toast-checkout-btn');
	checkoutBtn.addEventListener('click', (e) => {
		e.preventDefault();
		clearTimeout(autoDismissTimeout);
		checkoutBtn.disabled = true;
		checkoutBtn.textContent = 'Processing...';

		const items = getCartItems();
		if (!items.length || !window.omnifyStorefront || !window.omnifyStorefront.checkoutUrl) {
			window.location.href = checkoutUrl;
			return;
		}

		const formattedItems = items.map(cartItem => ({
			product_id: parseInt(cartItem.id, 10),
			variation_id: cartItem.variationId ? parseInt(cartItem.variationId, 10) : null,
			quantity: parseInt(cartItem.quantity || 1, 10)
		}));

		window.fetch(`${window.omnifyStorefront.restUrl}/cart`, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': window.omnifyStorefront.nonce
			},
			body: JSON.stringify({ items: formattedItems })
		})
		.then(res => {
			if (!res.ok) throw new Error('Failed to save cart');
			return res.json();
		})
		.then(data => {
			if (data.token) {
				setOmnifyCartToken(data.token);
			}
			window.location.href = `${window.omnifyStorefront.checkoutUrl}?cart=1`;
		})
		.catch(err => {
			console.error(err);
			window.location.href = checkoutUrl;
		});
	});
}

function initCartButtons() {
	document.querySelectorAll('.omnify-add-cart-button').forEach((btn) => {
		btn.addEventListener('click', (event) => {
			event.preventDefault();
			event.stopPropagation();
			
			if (btn.disabled || btn.classList.contains('disabled')) {
				return;
			}
			
			const item = cartItemFromButton(btn);
			addItemToCart(item);
			showAddedToCartToast(item);

			// Track Add to Cart
			try {
				omnifyTrackAddToCart(item);
			} catch (e) {
				console.error('Error tracking add to cart:', e);
			}
			
			const original = btn.textContent;
			btn.textContent = 'Added';
			btn.classList.add('is-added');
			window.setTimeout(() => {
				btn.textContent = original;
				btn.classList.remove('is-added');
			}, 1300);
		});
	});
	updateCartCount();
}

function initCartPage() {
	const page = document.querySelector('.omnify-cart-page');
	if (!page) return;

	const itemsEl = document.getElementById('omnify-cart-items');
	const emptyEl = document.getElementById('omnify-cart-empty');
	const contentEl = document.getElementById('omnify-cart-content');
	const subtotalEl = document.getElementById('omnify-cart-subtotal');
	const totalEl = document.getElementById('omnify-cart-total');
	const discountRow = document.getElementById('omnify-cart-discount-row');
	const discountLabel = document.getElementById('omnify-cart-discount-label');
	const discountAmount = document.getElementById('omnify-cart-discount');
	const couponInput = document.getElementById('omnify-cart-coupon-input');
	const couponApplyBtn = document.getElementById('omnify-cart-coupon-apply');
	const couponMsg = document.getElementById('omnify-cart-coupon-message');
	const checkoutBtn = document.getElementById('omnify-cart-checkout');
	const clearBtn = document.getElementById('omnify-cart-clear');
	const selectAllInput = document.getElementById('omnify-cart-select-all');
	const selectedCountEl = document.getElementById('omnify-cart-selected-count');
	const checkoutUrl = page.dataset.checkoutUrl || (window.omnifyStorefront && window.omnifyStorefront.checkoutUrl) || '';
	let appliedCartCoupon = null;
	let selectedCartKeys = new Set();

	// Countdown Timer
	const countdownEl = document.getElementById('omnify-cart-countdown');
	if (countdownEl) {
		const durationMin = (window.omnifyStorefront && window.omnifyStorefront.cart && window.omnifyStorefront.cart.countdownDuration) ? parseInt(window.omnifyStorefront.cart.countdownDuration, 10) : 7;
		let targetTime = window.sessionStorage.getItem('omnify_cart_countdown_target');
		if (!targetTime) {
			targetTime = Date.now() + durationMin * 60 * 1000;
			window.sessionStorage.setItem('omnify_cart_countdown_target', targetTime);
		} else {
			targetTime = parseInt(targetTime, 10);
		}

		function updateCountdown() {
			const remain = targetTime - Date.now();
			if (remain <= 0) {
				countdownEl.textContent = '00m00s';
				window.sessionStorage.removeItem('omnify_cart_countdown_target');
				return;
			}
			const m = Math.floor(remain / 60000);
			const s = Math.floor((remain % 60000) / 1000);
			countdownEl.textContent = `${String(m).padStart(2, '0')}m${String(s).padStart(2, '0')}s`;
			setTimeout(updateCountdown, 1000);
		}
		updateCountdown();
	}

	function cartItemKey(item) {
		return `${String(item.id || item.product_id || '')}:${String(item.variationId || item.variation_id || '')}`;
	}

	function syncSelectedCartKeys(items) {
		const allKeys = items.map(cartItemKey).filter(Boolean);
		const available = new Set(allKeys);
		selectedCartKeys = new Set(Array.from(selectedCartKeys).filter((key) => available.has(key)));
		// Do not auto-force selection here; respect user's explicit choices (including deselecting all)
	}

	function getSelectedCartItems(items = getCartItems()) {
		syncSelectedCartKeys(items);
		return items.filter((item) => selectedCartKeys.has(cartItemKey(item)));
	}

	function updateSelectionUi(items) {
		const selectedItems = getSelectedCartItems(items);
		if (selectedCountEl) {
			const total = items.length;
			const selected = selectedItems.length;
			selectedCountEl.textContent = total ? `${selected} of ${total} selected` : '';
		}
		if (selectAllInput) {
			selectAllInput.checked = Boolean(items.length && selectedItems.length === items.length);
			selectAllInput.indeterminate = Boolean(selectedItems.length && selectedItems.length < items.length);
		}
		if (checkoutBtn) {
			checkoutBtn.disabled = !selectedItems.length;
		}
	}

	function showCartCouponMessage(message, type) {
		if (!couponMsg) return;
		couponMsg.textContent = message;
		couponMsg.classList.remove('is-error', 'is-success', 'is-muted');
		couponMsg.classList.add(type === 'error' ? 'is-error' : (type === 'success' ? 'is-success' : 'is-muted'));
		couponMsg.style.display = message ? 'block' : 'none';
	}

	function resetCartCoupon(options = {}) {
		appliedCartCoupon = null;

		// New applied UI elements (if present)
		const formEl = document.getElementById('omnify-cart-coupon-form');
		const appliedEl = document.getElementById('omnify-cart-coupon-applied');

		if (formEl) formEl.style.display = '';
		if (appliedEl) appliedEl.style.display = 'none';

		if (couponInput) {
			couponInput.disabled = false;
			couponInput.value = options.keepInput ? couponInput.value : '';
		}
		if (couponApplyBtn) {
			couponApplyBtn.disabled = false;
			couponApplyBtn.textContent = 'Apply';
			couponApplyBtn.classList.remove('is-applied');
		}
		if (!options.silent) {
			showCartCouponMessage(options.message || '', options.type || 'muted');
		} else if (couponMsg) {
			showCartCouponMessage('', 'muted');
		}
	}

	function renderCartSummary(subtotal) {
		const discount = appliedCartCoupon ? Math.min(subtotal, Math.max(0, parseFloat(appliedCartCoupon.discount || 0) || 0)) : 0;
		const total = Math.max(0, subtotal - discount);

		if (subtotalEl) subtotalEl.textContent = formatStoreMoney(subtotal);
		if (totalEl) totalEl.textContent = formatStoreMoney(total);
		if (discountRow) discountRow.style.display = discount > 0 ? 'flex' : 'none';
		if (discountLabel && appliedCartCoupon) discountLabel.textContent = `Discount (${appliedCartCoupon.code})`;
		if (discountAmount) discountAmount.textContent = `-${formatStoreMoney(discount)}`;
	}

	function cartCouponPayload(code) {
		const selectedItems = getSelectedCartItems();
		return {
			code,
			items: selectedItems.map(item => ({
				product_id: parseInt(item.id, 10),
				variation_id: item.variationId ? parseInt(item.variationId, 10) : null,
				quantity: Math.max(1, parseInt(item.quantity || 1, 10) || 1)
			}))
		};
	}

	function applyCartCoupon() {
		if (!couponInput || !couponApplyBtn || !window.omnifyStorefront || !window.omnifyStorefront.restUrl) return;
		const code = couponInput.value.trim().toUpperCase();
		const items = getCartItems();
		if (!code) {
			showCartCouponMessage('Please enter a coupon code.', 'error');
			return;
		}
		if (!items.length) {
			showCartCouponMessage('Add a product before applying a coupon.', 'error');
			return;
		}
		if (!getSelectedCartItems(items).length) {
			showCartCouponMessage('Select at least one cart item before applying a coupon.', 'error');
			return;
		}

		couponApplyBtn.disabled = true;
		couponApplyBtn.textContent = '…';
		showCartCouponMessage('', 'muted');

		window.fetch(`${window.omnifyStorefront.restUrl}/checkout/validate-coupon`, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': window.omnifyStorefront.nonce
			},
			body: JSON.stringify(cartCouponPayload(code))
		})
		.then((res) => res.json().then((data) => {
			if (!res.ok) throw new Error(data.message || 'Invalid coupon.');
			return data;
		}))
		.then((data) => {
			appliedCartCoupon = {
				code: data.code || code,
				discount: parseFloat(data.discount || 0) || 0,
				freeShipping: Boolean(data.free_shipping),
				discountType: data.discount_type || '',
				discountValue: data.discount_value || ''
			};

			// New applied UI
			const formEl = document.getElementById('omnify-cart-coupon-form');
			const appliedEl = document.getElementById('omnify-cart-coupon-applied');
			const codeEl = document.getElementById('omnify-applied-coupon-code');

			if (formEl) formEl.style.display = 'none';
			if (appliedEl && codeEl) {
				codeEl.textContent = appliedCartCoupon.code;
				appliedEl.style.display = '';
			}

			if (couponInput) {
				couponInput.value = appliedCartCoupon.code;
				couponInput.disabled = true;
			}
			if (couponApplyBtn) {
				couponApplyBtn.textContent = 'Applied';
				couponApplyBtn.classList.add('is-applied');
			}
			showCartCouponMessage(`Coupon ${appliedCartCoupon.code} applied.`, 'success');
			renderCartSummary(cartSubtotal(getSelectedCartItems()));
		})
		.catch((err) => {
			resetCartCoupon({ keepInput: true, silent: true });
			showCartCouponMessage(err.message || 'Unable to apply this coupon.', 'error');
			renderCartSummary(cartSubtotal(getSelectedCartItems()));
		});
	}

	function renderCart() {
		const items = getCartItems();
		syncSelectedCartKeys(items);
		if (!items.length) {
			if (emptyEl) emptyEl.style.display = 'block';
			if (contentEl) contentEl.style.display = 'none';
			resetCartCoupon({ silent: true });
			renderCartSummary(0);
			updateCartCount();
			return;
		}

		if (emptyEl) emptyEl.style.display = 'none';
		if (contentEl) contentEl.style.display = 'grid';
		if (itemsEl) {
			itemsEl.innerHTML = items.map((item) => {
				const qty = Math.max(1, parseInt(item.quantity || 1, 10) || 1);
				const unitPrice = parseFloat(item.price || 0) || 0;
				const lineTotal = unitPrice * qty;
				const key = cartItemKey(item);
				const isSelected = selectedCartKeys.has(key);
				return `
					<div class="omnify-cart-item ${isSelected ? 'is-selected' : ''}" data-id="${escapeHtml(item.id)}" data-variation-id="${escapeHtml(item.variationId || '')}" data-key="${escapeHtml(key)}">
						<label class="omnify-cart-item__select" aria-label="Select ${escapeHtml(item.name || 'product')} for checkout">
							<input type="checkbox" class="omnify-checkbox omnify-cart-item-select" data-key="${escapeHtml(key)}" ${isSelected ? 'checked' : ''}>
						</label>
						<button type="button" class="omnify-cart-remove" data-id="${escapeHtml(item.id)}" data-variation-id="${escapeHtml(item.variationId || '')}" aria-label="Remove item">✕</button>
						<div class="omnify-cart-item__media">${item.image ? `<img src="${escapeHtml(item.image)}" alt="">` : `<span>${escapeHtml(String(item.name || 'P').charAt(0))}</span>`}</div>
						<div class="omnify-cart-item__body">
							<strong>${escapeHtml(item.name || 'Product')}</strong>
							${item.variationId ? `<small>Variation #${escapeHtml(item.variationId)}</small>` : ''}
							${item.url ? `<a href="${escapeHtml(item.url)}">View Details</a>` : ''}
						</div>
						<div class="omnify-cart-item__price">${escapeHtml(item.displayPrice || formatStoreMoney(item.price))}</div>
						<div class="omnify-cart-item__quantity" aria-label="Quantity">
							<button type="button" class="omnify-cart-qty-btn" data-action="decrease" aria-label="Decrease quantity">−</button>
							<input type="number" class="omnify-cart-qty-input" min="1" value="${qty}" inputmode="numeric" aria-label="Cart item quantity" />
							<button type="button" class="omnify-cart-qty-btn" data-action="increase" aria-label="Increase quantity">+</button>
						</div>
						<div class="omnify-cart-item__total">
							<strong>${formatStoreMoney(lineTotal)}</strong>
						</div>
					</div>
				`;
			}).join('');
		}
		updateSelectionUi(items);
		renderCartSummary(cartSubtotal(getSelectedCartItems(items)));

		// Update Shipping Progress Bar
		const progressWrapper = document.getElementById('omnify-shipping-progress-wrapper');
		const progressFill = document.getElementById('omnify-shipping-progress-fill');
		const progressText = document.getElementById('omnify-shipping-progress-text');
		if (progressWrapper && progressFill && progressText) {
			const selectedItems = getSelectedCartItems(items);
			const subtotal = selectedItems.reduce((acc, item) => acc + (parseFloat(item.price || 0) * (parseInt(item.quantity, 10) || 1)), 0);
			const threshold = (window.omnifyStorefront && window.omnifyStorefront.cart && typeof window.omnifyStorefront.cart.freeShippingThreshold !== 'undefined') ? parseFloat(window.omnifyStorefront.cart.freeShippingThreshold) : 200;
			const diff = threshold - subtotal;
			if (subtotal === 0) {
				progressWrapper.style.display = 'none';
			} else if (diff > 0) {
				const pct = Math.min(100, Math.max(0, (subtotal / threshold) * 100));
				progressFill.style.width = pct + '%';
				progressText.innerHTML = `Spend <strong>${formatStoreMoney(diff)}</strong> more to reach <strong>FREE SHIPPING!</strong> <a href="${storefrontUrl || '#'}">Continue Shopping</a>`;
				progressWrapper.style.display = 'block';
			} else {
				progressFill.style.width = '100%';
				progressText.innerHTML = `🎉 You have reached <strong>FREE SHIPPING!</strong>`;
				progressWrapper.style.display = 'block';
			}
		}

		updateCartCount();
	}

	function updateItemQuantity(itemEl, nextQty) {
		const id = String(itemEl.dataset.id || '');
		const variationId = String(itemEl.dataset.variationId || '');
		const quantity = Math.max(1, parseInt(nextQty || 1, 10) || 1);

		setCartItems(getCartItems().map((item) => {
			if (!(String(item.id) === id && String(item.variationId || '') === variationId)) {
				return item;
			}
			return { ...item, quantity };
		}));
		resetCartCoupon({ silent: true });
		renderCart();
	}

	document.addEventListener('click', (event) => {
		const removeBtn = event.target.closest('.omnify-cart-remove');
		if (removeBtn) {
			const removedKey = `${String(removeBtn.dataset.id || '')}:${String(removeBtn.dataset.variationId || '')}`;
			selectedCartKeys.delete(removedKey);
			setCartItems(getCartItems().filter((item) => !(String(item.id) === String(removeBtn.dataset.id) && String(item.variationId || '') === String(removeBtn.dataset.variationId || ''))));
			resetCartCoupon({ silent: true });
			renderCart();
		}

		const qtyBtn = event.target.closest('.omnify-cart-qty-btn');
		if (qtyBtn) {
			const itemEl = qtyBtn.closest('.omnify-cart-item');
			const qtyInput = itemEl ? itemEl.querySelector('.omnify-cart-qty-input') : null;
			if (itemEl && qtyInput) {
				const current = Math.max(1, parseInt(qtyInput.value || 1, 10) || 1);
				updateItemQuantity(itemEl, qtyBtn.dataset.action === 'increase' ? current + 1 : Math.max(1, current - 1));
			}
		}

		const itemCheckoutBtn = event.target.closest('.omnify-cart-item-checkout');
		if (itemCheckoutBtn && checkoutUrl) {
			const directUrl = itemCheckoutBtn.dataset.checkoutUrl || '';
			if (directUrl) {
				window.location.href = directUrl;
				return;
			}
			const url = new URL(checkoutUrl);
			url.searchParams.set('product_id', itemCheckoutBtn.dataset.id);
			if (itemCheckoutBtn.dataset.variationId) {
				url.searchParams.set('variation_id', itemCheckoutBtn.dataset.variationId);
			}
			window.location.href = url.toString();
		}
	});

	document.addEventListener('change', (event) => {
		const selectInput = event.target.closest('.omnify-cart-item-select');
		if (selectInput) {
			const key = selectInput.dataset.key || '';
			if (selectInput.checked) {
				selectedCartKeys.add(key);
			} else {
				selectedCartKeys.delete(key);
			}
			resetCartCoupon({ silent: true });
			renderCart();
			return;
		}

		const qtyInput = event.target.closest('.omnify-cart-qty-input');
		if (!qtyInput) return;
		const itemEl = qtyInput.closest('.omnify-cart-item');
		if (itemEl) {
			updateItemQuantity(itemEl, qtyInput.value);
		}
	});

	if (selectAllInput) {
		selectAllInput.addEventListener('change', () => {
			const items = getCartItems();
			selectedCartKeys = selectAllInput.checked ? new Set(items.map(cartItemKey)) : new Set();
			resetCartCoupon({ silent: true });
			renderCart();
		});
	}

	if (checkoutBtn) {
		checkoutBtn.addEventListener('click', () => {
			const items = getSelectedCartItems();
			if (!items.length || !checkoutUrl) return;

			checkoutBtn.disabled = true;
			const originalText = checkoutBtn.textContent;
			checkoutBtn.textContent = 'Preparing checkout...';

			const formattedItems = items.map(item => ({
				product_id: parseInt(item.id, 10),
				variation_id: item.variationId ? parseInt(item.variationId, 10) : null,
				quantity: parseInt(item.quantity || 1, 10)
			}));

			window.fetch(`${omnifyStorefront.restUrl}/cart`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': omnifyStorefront.nonce
				},
				body: JSON.stringify({ items: formattedItems })
			})
			.then(res => {
				if (!res.ok) throw new Error('Failed to save cart');
				return res.json();
			})
			.then(data => {
				if (data.token) {
					setOmnifyCartToken(data.token);
				}
				window.sessionStorage.setItem(OMNIFY_CHECKOUT_SELECTED_KEY, JSON.stringify(items));
				if (appliedCartCoupon && appliedCartCoupon.code) {
					window.sessionStorage.setItem('omnify_cart_coupon_code', appliedCartCoupon.code);
				} else {
					window.sessionStorage.removeItem('omnify_cart_coupon_code');
				}
				window.location.href = `${checkoutUrl}?cart=1`;
			})
			.catch(err => {
				console.error(err);
				// Fallback to direct checkout of first item
				const first = items[0];
				const url = new URL(checkoutUrl);
				url.searchParams.set('product_id', first.id);
				if (first.variationId) {
					url.searchParams.set('variation_id', first.variationId);
				}
				window.location.href = url.toString();
			});
		});
	}

	if (clearBtn) {
		clearBtn.addEventListener('click', () => {
			setCartItems([]);
			selectedCartKeys = new Set();
			resetCartCoupon({ silent: true });
			window.sessionStorage.removeItem('omnify_cart_coupon_code');
			window.sessionStorage.removeItem(OMNIFY_CHECKOUT_SELECTED_KEY);
			renderCart();
		});
	}

	if (couponApplyBtn) {
		couponApplyBtn.addEventListener('click', applyCartCoupon);
	}

	if (couponInput) {
		couponInput.addEventListener('keydown', (event) => {
			if (event.key === 'Enter') {
				event.preventDefault();
				applyCartCoupon();
			}
		});
		couponInput.addEventListener('input', () => {
			if (appliedCartCoupon) {
				resetCartCoupon({ keepInput: true, silent: true });
				renderCartSummary(cartSubtotal(getSelectedCartItems()));
			}
		});
	}

	// Coupon remove button (new improved UI)
	const couponRemoveBtn = document.getElementById('omnify-cart-coupon-remove');
	if (couponRemoveBtn) {
		couponRemoveBtn.addEventListener('click', () => {
			resetCartCoupon();
			renderCartSummary(cartSubtotal(getSelectedCartItems()));
		});
	}

	// Default to selecting all items on initial page load.
	// After this, user deselections are respected (no more auto-reselect in sync).
	const initialItems = getCartItems();
	if (initialItems.length && selectedCartKeys.size === 0) {
		selectedCartKeys = new Set(initialItems.map(cartItemKey));
	}
	// Sync checkout final display price and shipping calculations
	const totalDisplay = document.getElementById('omnify-cart-total-display');
	if (totalDisplay && totalEl) {
		const syncDisplay = () => {
			const rawText = totalEl.textContent;
			const matches = rawText.match(/[\d.,]+/);
			if (matches) {
				const sub = parseFloat(matches[0].replace(/,/g, ''));
				if (!isNaN(sub)) {
					const shipping = sub > 0 ? 19.00 : 0;
					const finalTotal = sub + shipping;
					const formatted = rawText.replace(/[\d.,]+/, finalTotal.toFixed(2));
					totalDisplay.textContent = sub > 0 ? formatted : rawText;
				}
			}
		};
		const observer = new MutationObserver(syncDisplay);
		observer.observe(totalEl, { childList: true, characterData: true, subtree: true });
		syncDisplay();
	}

	renderCart();
}

/**
 * Checkout page form submission and interactive fields.
 */
function initCheckoutPageForm() {
	const pageForm = document.getElementById('omnify-checkout-page-form');
	if (!pageForm) return;

	// Track begin checkout
	try {
		const isCartMode = pageForm.getAttribute('data-cart-mode') === '1';
		let checkoutItems = [];
		let totalValue = 0;

		if (isCartMode) {
			const cartItems = getCheckoutCartItems();
			checkoutItems = cartItems.map(item => ({
				item_id: item.variationId ? String(item.variationId) : String(item.id),
				item_name: item.name,
				price: parseFloat(item.price),
				quantity: parseInt(item.quantity || 1, 10)
			}));
			totalValue = checkoutItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
		} else {
			const productId = pageForm.querySelector('#omnify-checkout-product-id')?.value || '0';
			const variationId = pageForm.querySelector('#omnify-checkout-variation-id')?.value || '0';
			const quantity = parseInt(pageForm.querySelector('#omnify-checkout-quantity')?.value || '1', 10);
			const price = parseFloat(pageForm.getAttribute('data-product-price') || '0');
			const name = pageForm.getAttribute('data-product-name') || 'Product';
			const sku = pageForm.getAttribute('data-product-sku') || '';

			const itemId = variationId !== '0' ? variationId : (sku || productId);
			checkoutItems = [{
				item_id: itemId,
				item_name: name,
				price: price,
				quantity: quantity
			}];
			totalValue = price * quantity;
		}

		omnifyTrackBeginCheckout(checkoutItems, totalValue);
	} catch (e) {
		console.error('Error tracking begin checkout:', e);
	}

	const billingSameCheckbox = document.getElementById('omnify-billing-same');
	const billingAddressFields = document.getElementById('omnify-billing-address-fields');

	function updateBillingFieldsRequiredState() {
		const isRequired = !billingSameCheckbox || !billingSameCheckbox.checked;
		if (billingAddressFields) {
			const inputs = billingAddressFields.querySelectorAll('input, select');
			inputs.forEach(input => {
				if (input.id === 'omnify-billing-company' || input.id === 'omnify-billing-phone') {
					return;
				}
				if (isRequired) {
					input.setAttribute('required', 'required');
				} else {
					input.removeAttribute('required');
				}
			});
		}
	}

	if (billingSameCheckbox && billingAddressFields) {
		billingSameCheckbox.addEventListener('change', () => {
			if (billingSameCheckbox.checked) {
				billingAddressFields.style.display = 'none';
			} else {
				billingAddressFields.style.display = 'grid';
			}
			updateBillingFieldsRequiredState();
		});
	}
	updateBillingFieldsRequiredState();

	const createAccountCheckbox = document.getElementById('omnify-create-account');
	const passwordGroup         = document.getElementById('omnify-password-group');
	const passwordInput         = document.getElementById('omnify-password');
	const submitBtn             = document.getElementById('omnify-checkout-submit');
	const errorBox              = document.getElementById('omnify-checkout-error');
	const gridLayout            = document.getElementById('omnify-checkout-page-grid');
	const successBox            = document.getElementById('omnify-checkout-page-success');

	// Coupon elements
	const couponInput      = document.getElementById('omnify-coupon-input');
	const couponApplyBtn   = document.getElementById('omnify-coupon-apply');
	const couponMsg        = document.getElementById('omnify-coupon-message');
	const couponCodeField  = document.getElementById('omnify-checkout-coupon-code');
	const discountRow      = document.getElementById('omnify-summary-discount-row');
	const discountLabel    = document.getElementById('omnify-summary-discount-label');
	const discountAmount   = document.getElementById('omnify-summary-discount-amount');
	const taxAmountEl      = document.getElementById('omnify-summary-tax-amount');
	const taxLabelEl       = document.getElementById('omnify-summary-tax-label');
	const shippingRowEl    = document.getElementById('omnify-summary-shipping-row');
	const shippingLabelEl  = document.getElementById('omnify-summary-shipping-label');
	const shippingAmountEl = document.getElementById('omnify-summary-shipping-amount');
	const shippingMethodsEl = document.getElementById('omnify-shipping-methods');
	const shippingOptionsEl = document.getElementById('omnify-shipping-method-options');
	const shippingUnavailableEl = document.getElementById('omnify-shipping-unavailable');
	const shippingMethodIdEl = document.getElementById('omnify-shipping-method-id');
	const totalEl          = document.getElementById('omnify-summary-total');
	const summaryBreakdown = document.getElementById('omnify-checkout-summary-breakdown');

	// Track applied coupon state
		let appliedDiscount    = 0;
		let appliedCouponCode  = '';
		let appliedFreeShipping = false;

	// Read base values injected by PHP data attributes / content
	const productIdInput = document.getElementById('omnify-checkout-product-id');
	const productId      = productIdInput ? parseInt(productIdInput.value, 10) : 0;
	const quantityInput  = document.getElementById('omnify-checkout-quantity');
	const recoveryParams = new URLSearchParams(window.location.search);
	const recoveredToken = recoveryParams.get('omnify_recover_cart') || '';
	const abandonedCartStorageKey = `omnify_abandoned_cart_${productId}_${document.getElementById('omnify-checkout-variation-id')?.value || 0}`;
	let abandonedCartToken = recoveredToken || window.localStorage.getItem(abandonedCartStorageKey) || '';
	let abandonedCaptureTimer = null;

	if (!abandonedCartToken) {
		abandonedCartToken = (window.crypto && crypto.randomUUID) ? crypto.randomUUID() : `cart_${Date.now()}_${Math.random().toString(16).slice(2)}`;
	}
	window.localStorage.setItem(abandonedCartStorageKey, abandonedCartToken);

	/** Helper: parse currency string from an element e.g. "USD 29.99" → 29.99 */
	function parseAmount(el) {
		if (!el) return 0;
		const text = el.textContent.trim();
		const match = text.match(/[\d,]+\.?\d*/);
		return match ? parseFloat(match[0].replace(/,/g, '')) : 0;
	}

	function normalizeRegionToken(value) {
		const text = String(value || '').trim();
		if (!text || text === '*') return '*';
		const normalized = text.replace(/[^a-z0-9]+/gi, '').toUpperCase();
		const aliases = {
			UNITEDSTATES: 'US',
			USA: 'US',
			UNITEDKINGDOM: 'GB',
			UK: 'GB',
			GREATBRITAIN: 'GB',
			CANADA: 'CA',
			AUSTRALIA: 'AU',
			BANGLADESH: 'BD',
			INDIA: 'IN',
			GERMANY: 'DE',
			FRANCE: 'FR',
			ITALY: 'IT',
			SPAIN: 'ES'
		};
		return aliases[normalized] || normalized;
	}

	function regionListMatches(list, target) {
		return String(list || '*').split(',').some((token) => {
			const normalized = normalizeRegionToken(token);
			return normalized === '*' || normalized === target;
		});
	}

	function regionListIsWildcard(list) {
		return String(list || '*').split(',').every((token) => normalizeRegionToken(token) === '*');
	}

	function destination() {
		return {
			country: normalizeRegionToken(document.getElementById('omnify-shipping-country')?.value || ''),
			state: normalizeRegionToken(document.getElementById('omnify-shipping-state')?.value || '')
		};
	}

	function resolveTaxRule() {
		const store = window.omnifyStorefront || {};
		const rules = Array.isArray(store.taxRules) ? store.taxRules : [];
		const dest = destination();
		const matches = [];

		rules.forEach((rule, index) => {
			if (!rule.enabled) return;
			const country = normalizeRegionToken(rule.country || '*');
			const state = normalizeRegionToken(rule.state || '*');
			if ((country !== '*' && country !== dest.country) || (state !== '*' && state !== dest.state)) return;
			matches.push({
				index,
				specificity: (country !== '*' ? 10 : 0) + (state !== '*' ? 20 : 0),
				priority: parseInt(rule.priority || 10, 10),
				rate: parseFloat(rule.rate || 0),
				label: rule.label || store.taxLabel || 'Tax'
			});
		});

		if (matches.length) {
			matches.sort((a, b) => (b.specificity - a.specificity) || (a.priority - b.priority) || (a.index - b.index));
			return matches[0];
		}

		return {
			rate: parseFloat(store.taxRate || 0),
			label: store.taxLabel || 'Tax'
		};
	}

	function resolveDeliveryRates(netAmount) {
		const isPhysical = summaryBreakdown && summaryBreakdown.dataset.isPhysical === '1';
		if (!isPhysical) return [];

		const store = window.omnifyStorefront || {};
		const zones = Array.isArray(store.deliveryZones) ? store.deliveryZones : [];
		const dest = destination();
		const matches = [];

		zones.forEach((zone, index) => {
			if (!zone.enabled) return;
			if (!regionListMatches(zone.countries || '*', dest.country) || !regionListMatches(zone.states || '*', dest.state)) return;
			let amount = parseFloat(zone.cost || 0);
			const freeMin = parseFloat(zone.free_min || 0);
			const methodType = ['flat_rate', 'free_shipping', 'local_pickup'].includes(zone.method_type) ? zone.method_type : 'flat_rate';
			if (methodType === 'free_shipping' || (freeMin > 0 && netAmount >= freeMin)) amount = 0;
			matches.push({
				id: `zone-${index}-${methodType}`,
				index,
				amount,
				method: zone.method_name || 'Standard delivery',
				type: methodType,
				priority: parseInt(zone.priority || 10, 10),
				specificity: (regionListIsWildcard(zone.countries || '*') ? 0 : 10) + (regionListIsWildcard(zone.states || '*') ? 0 : 20)
			});
		});

		if (!matches.length) return [];
		matches.sort((a, b) => (b.specificity - a.specificity) || (a.priority - b.priority) || (a.index - b.index));
		return matches;
	}

	function renderShippingMethods(rates) {
		const isPhysical = summaryBreakdown && summaryBreakdown.dataset.isPhysical === '1';
		if (!shippingMethodsEl || !shippingOptionsEl || !shippingMethodIdEl) return { amount: 0, method: '' };
		shippingMethodsEl.style.display = isPhysical ? 'grid' : 'none';
		if (!isPhysical) {
			shippingOptionsEl.innerHTML = '';
			shippingMethodIdEl.value = '';
			return { amount: 0, method: '' };
		}

		if (!rates.length) {
			shippingOptionsEl.innerHTML = '';
			shippingMethodIdEl.value = '';
			if (shippingUnavailableEl) shippingUnavailableEl.style.display = 'block';
			return { amount: 0, method: 'Delivery unavailable', unavailable: true };
		}
		if (shippingUnavailableEl) shippingUnavailableEl.style.display = 'none';

		const current = shippingMethodIdEl.value && rates.some((rate) => rate.id === shippingMethodIdEl.value)
			? shippingMethodIdEl.value
			: rates[0].id;
		shippingMethodIdEl.value = current;
		shippingOptionsEl.innerHTML = rates.map((rate) => {
			const checked = rate.id === current ? 'checked' : '';
			const label = rate.type === 'local_pickup' ? `${rate.method} · Local pickup` : rate.method;
			return `<label style="display:flex; align-items:center; justify-content:space-between; gap:10px; border:1px solid var(--omnify-gray-200); border-radius:8px; padding:9px 10px; cursor:pointer; background:#fff;">
				<span style="display:flex; align-items:center; gap:8px;"><input type="radio" name="omnify_shipping_method_choice" value="${escapeHtml(rate.id)}" ${checked} style="margin:0;" /> <strong style="font-size:12px;">${escapeHtml(label)}</strong></span>
				<span style="font-size:12px; font-weight:700;">${formatStoreMoney(rate.amount)}</span>
			</label>`;
		}).join('');
		shippingOptionsEl.querySelectorAll('input[type="radio"]').forEach((input) => {
			input.addEventListener('change', () => {
				shippingMethodIdEl.value = input.value;
				refreshSummary(appliedDiscount);
			});
		});
		return rates.find((rate) => rate.id === shippingMethodIdEl.value) || rates[0];
	}

	/**
	 * Recalculate and render tax + total based on current discount.
	 */
	function refreshSummary(discount) {
		if (!totalEl) return;

		const taxRowEl = document.getElementById('omnify-summary-tax-row');
		const taxRule = resolveTaxRule();
		const taxRate = taxRule.rate || 0;

		const subtotalEl = document.getElementById('omnify-summary-subtotal');
		const subtotal   = parseAmount(subtotalEl);
		const netAmount  = Math.max(0, subtotal - discount);
			const rates = resolveDeliveryRates(netAmount);
			const delivery   = renderShippingMethods(rates);
			if (appliedFreeShipping) {
				delivery.amount = 0;
				delivery.method = delivery.method || 'Free shipping';
			}
		let taxAmount = 0;
		let total = netAmount + delivery.amount;
		const taxShipping = Boolean((window.omnifyStorefront || {}).taxShipping);
		const pricesIncludeTax = Boolean((window.omnifyStorefront || {}).pricesIncludeTax);
		if (taxRate > 0) {
			if (pricesIncludeTax) {
				const productTax = netAmount - (netAmount / (1 + (taxRate / 100)));
				const shippingTax = taxShipping ? delivery.amount * (taxRate / 100) : 0;
				taxAmount = productTax + shippingTax;
				total = netAmount + delivery.amount + shippingTax;
			} else {
				const taxableAmount = netAmount + (taxShipping ? delivery.amount : 0);
				taxAmount = taxableAmount * (taxRate / 100);
				total = netAmount + delivery.amount + taxAmount;
			}
			taxAmount = Math.round(taxAmount * 100) / 100;
			total = Math.round(total * 100) / 100;
		}

		if (taxAmountEl) {
			taxAmountEl.textContent = formatStoreMoney(taxAmount);
		}
		if (taxLabelEl) {
			taxLabelEl.textContent = `${taxRule.label || 'Tax'} (${taxRate.toFixed(2)}%)${pricesIncludeTax ? ' included' : ''}`;
		}
		if (taxRowEl) {
			taxRowEl.style.display = taxRate > 0 ? 'flex' : 'none';
		}
		if (shippingAmountEl) {
			shippingAmountEl.textContent = formatStoreMoney(delivery.amount);
		}
		if (shippingLabelEl) {
			shippingLabelEl.textContent = delivery.method || 'Delivery';
		}
		if (shippingRowEl) {
			shippingRowEl.style.display = summaryBreakdown && summaryBreakdown.dataset.isPhysical === '1' ? 'flex' : 'none';
		}
		totalEl.textContent = formatStoreMoney(total);
	}

	/**
	 * Show coupon feedback message.
	 */
	function showCouponMsg(msg, isError) {
		if (!couponMsg) return;
		couponMsg.textContent = msg;
		couponMsg.style.display = 'block';
		if (isError) {
			couponMsg.style.background = '#fff0ed';
			couponMsg.style.color = '#d82c0d';
			couponMsg.style.border = '1px solid #d82c0d';
		} else {
			couponMsg.style.background = 'rgba(0,128,96,0.08)';
			couponMsg.style.color = '#008060';
			couponMsg.style.border = '1px solid #008060';
		}
	}

	function validCheckoutEmail(value) {
		return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value || '').trim());
	}

	function buildAbandonedCartPayload() {
		const email = document.getElementById('omnify-email')?.value || '';
		if (!validCheckoutEmail(email) || !productId) return null;

		const variationIdInput = document.getElementById('omnify-checkout-variation-id');
		const variationId = variationIdInput ? parseInt(variationIdInput.value, 10) : 0;
		const payload = {
			token: abandonedCartToken,
			product_id: productId,
			quantity: quantityInput ? parseInt(quantityInput.value, 10) || 1 : 1,
			email,
			first_name: document.getElementById('omnify-firstname')?.value || '',
			last_name: document.getElementById('omnify-lastname')?.value || '',
			phone: document.getElementById('omnify-phone')?.value || '',
			coupon_code: appliedCouponCode || couponCodeField?.value || '',
			checkout_url: window.location.href
		};

		if (variationId) {
			payload.variation_id = variationId;
		}

		return payload;
	}

	function captureAbandonedCart() {
		const payload = buildAbandonedCartPayload();
		if (!payload || !window.omnifyStorefront?.restUrl) return Promise.resolve(false);

		return window.fetch(`${omnifyStorefront.restUrl}/checkout/abandoned-cart`, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': omnifyStorefront.nonce,
			},
			body: JSON.stringify(payload),
		})
		.then((res) => res.ok ? res.json() : null)
		.then((data) => {
			if (data && data.token) {
				abandonedCartToken = data.token;
				window.localStorage.setItem(abandonedCartStorageKey, abandonedCartToken);
			}
			return true;
		})
		.catch(() => false);
	}

	function scheduleAbandonedCartCapture() {
		window.clearTimeout(abandonedCaptureTimer);
		abandonedCaptureTimer = window.setTimeout(captureAbandonedCart, 800);
	}

	function clearAbandonedCartToken() {
		window.localStorage.removeItem(abandonedCartStorageKey);
	}

		// Toggle password input based on create account checkbox
		if (createAccountCheckbox && passwordGroup && passwordInput) {
			const accountMode = createAccountCheckbox.dataset.accountMode || 'optional';
			if (accountMode === 'required') {
				passwordGroup.style.display = 'flex';
				passwordInput.required = true;
			}
			createAccountCheckbox.addEventListener('change', () => {
				if (createAccountCheckbox.checked) {
					passwordGroup.style.display = 'flex';
					passwordInput.required = true;
				passwordInput.focus();
			} else {
				passwordGroup.style.display = 'none';
				passwordInput.required = false;
				passwordInput.value = '';
			}
		});
	}

	['omnify-shipping-country', 'omnify-shipping-state'].forEach((id) => {
		const field = document.getElementById(id);
		if (field) {
			field.addEventListener('input', () => refreshSummary(appliedDiscount));
			field.addEventListener('change', () => refreshSummary(appliedDiscount));
		}
	});

	[
		'omnify-email',
		'omnify-firstname',
		'omnify-lastname',
		'omnify-phone',
		'omnify-shipping-country',
		'omnify-shipping-state',
		'omnify-shipping-city',
		'omnify-shipping-postcode'
	].forEach((id) => {
		const field = document.getElementById(id);
		if (field) {
			field.addEventListener('input', scheduleAbandonedCartCapture);
			field.addEventListener('change', scheduleAbandonedCartCapture);
		}
	});

	refreshSummary(appliedDiscount);

	// Coupon Apply
	if (couponApplyBtn && couponInput) {
		couponApplyBtn.addEventListener('click', () => {
			const code = couponInput.value.trim().toUpperCase();
			if (!code) {
				showCouponMsg('Please enter a coupon code.', true);
				return;
			}

			couponApplyBtn.disabled = true;
			couponApplyBtn.textContent = '…';

			const variationIdInput = document.getElementById('omnify-checkout-variation-id');
			const variationId = variationIdInput ? parseInt(variationIdInput.value, 10) : 0;

				const couponPayload = {
					code,
					email: document.getElementById('omnify-email')?.value || ''
				};
			if (pageForm.getAttribute('data-cart-mode') === '1') {
				couponPayload.items = getCheckoutCartItems().map(item => ({
					product_id: parseInt(item.id, 10),
					variation_id: item.variationId ? parseInt(item.variationId, 10) : null,
					quantity: Math.max(1, parseInt(item.quantity || 1, 10) || 1)
				}));
			} else {
				couponPayload.product_id = productId;
				if (variationId) {
					couponPayload.variation_id = variationId;
				}
			}

			window.fetch(`${omnifyStorefront.restUrl}/checkout/validate-coupon`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': omnifyStorefront.nonce,
				},
				body: JSON.stringify(couponPayload),
			})
			.then((res) => res.json().then((data) => {
				if (!res.ok) throw new Error(data.message || 'Invalid coupon.');
				return data;
			}))
			.then((data) => {
					appliedDiscount   = parseFloat(data.discount) || 0;
					appliedCouponCode = data.code || code;
					appliedFreeShipping = Boolean(data.free_shipping);

				// Store in hidden field
				if (couponCodeField) couponCodeField.value = appliedCouponCode;

				// Render discount row
				if (discountRow) {
					discountRow.style.display = 'flex';
				}
				if (discountLabel) {
					discountLabel.textContent = `Discount (${appliedCouponCode})`;
				}
				if (discountAmount) {
					discountAmount.textContent = `-${formatStoreMoney(appliedDiscount)}`;
				}

				refreshSummary(appliedDiscount);

				const discLabel = data.discount_type === 'percent'
					? `${parseFloat(data.discount_value).toFixed(0)}% off`
					: `${formatStoreMoney(parseFloat(data.discount_value))} off`;

				showCouponMsg(`✓ Coupon "${appliedCouponCode}" applied – ${discLabel}!`, false);

				// Lock the coupon input after successful apply + new applied UI
				const formSection = document.getElementById('omnify-checkout-coupon-form');
				const appliedSection = document.getElementById('omnify-checkout-coupon-applied');
				const appliedCodeEl = document.getElementById('omnify-checkout-applied-code');

				if (formSection) formSection.style.display = 'none';
				if (appliedSection && appliedCodeEl) {
					appliedCodeEl.textContent = appliedCouponCode;
					appliedSection.style.display = 'flex';
				}

				couponInput.disabled  = true;
				couponApplyBtn.textContent = 'Applied ✓';
				couponApplyBtn.style.background = 'var(--omnify-success)';
				scheduleAbandonedCartCapture();
			})
			.catch((err) => {
					appliedDiscount   = 0;
					appliedCouponCode = '';
					appliedFreeShipping = false;
				if (couponCodeField) couponCodeField.value = '';
				if (discountRow) discountRow.style.display = 'none';
				refreshSummary(0);
				showCouponMsg(err.message, true);
				couponApplyBtn.disabled = false;
				couponApplyBtn.textContent = 'Apply';
			});
		});

		// Remove coupon in checkout
		const checkoutRemoveBtn = document.getElementById('omnify-checkout-coupon-remove');
		if (checkoutRemoveBtn) {
			checkoutRemoveBtn.addEventListener('click', () => {
				appliedDiscount = 0;
				appliedCouponCode = '';
				appliedFreeShipping = false;
				if (couponCodeField) couponCodeField.value = '';
				if (discountRow) discountRow.style.display = 'none';
				refreshSummary(0);
				if (couponInput) couponInput.disabled = false;
				if (couponApplyBtn) {
					couponApplyBtn.disabled = false;
					couponApplyBtn.textContent = 'Apply';
					couponApplyBtn.style.background = '';
				}
				const formSec = document.getElementById('omnify-checkout-coupon-form');
				const appSec = document.getElementById('omnify-checkout-coupon-applied');
				if (formSec) formSec.style.display = '';
				if (appSec) appSec.style.display = 'none';
			});
		}

		const storedCartCoupon = pageForm.getAttribute('data-cart-mode') === '1'
			? window.sessionStorage.getItem('omnify_cart_coupon_code')
			: '';
		if (storedCartCoupon) {
			couponInput.value = storedCartCoupon;
			window.sessionStorage.removeItem('omnify_cart_coupon_code');
			window.setTimeout(() => couponApplyBtn.click(), 120);
		}
	}

	// Payment method selector switching
	const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
	const paypalNote    = document.getElementById('omnify-paypal-checkout-note');
	const instructionSections = document.querySelectorAll('.omnify-checkout-instructions-section');
	const stripeConfig = (window.omnifyStorefront && window.omnifyStorefront.stripe) || {};
	const paypalConfig = (window.omnifyStorefront && window.omnifyStorefront.paypal) || {};
	const razorpayConfig = (window.omnifyStorefront && window.omnifyStorefront.razorpay) || {};
	const alipayConfig = (window.omnifyStorefront && window.omnifyStorefront.alipay) || {};
	const wechatConfig = (window.omnifyStorefront && window.omnifyStorefront.wechat) || {};
	const sslcommerzConfig = (window.omnifyStorefront && window.omnifyStorefront.sslcommerz) || {};
	const paystackConfig = (window.omnifyStorefront && window.omnifyStorefront.paystack) || {};
	const tapConfig = (window.omnifyStorefront && window.omnifyStorefront.tap) || {};
	const mollieConfig = (window.omnifyStorefront && window.omnifyStorefront.mollie) || {};
	const khaltiConfig = (window.omnifyStorefront && window.omnifyStorefront.khalti) || {};
	const esewaConfig = (window.omnifyStorefront && window.omnifyStorefront.esewa) || {};
	const useStripeCheckout = () => Boolean(stripeConfig.enabled && stripeConfig.checkoutEnabled);
	const usePayPalCheckout = () => Boolean(paypalConfig.enabled && paypalConfig.checkoutEnabled);
	const useRazorpayCheckout = () => Boolean(razorpayConfig.enabled && razorpayConfig.checkoutEnabled);
	const useAlipayCheckout = () => Boolean(alipayConfig.enabled && alipayConfig.checkoutEnabled);
	const useWechatCheckout = () => Boolean(wechatConfig.enabled && wechatConfig.checkoutEnabled);
	const useSslcommerzCheckout = () => Boolean(sslcommerzConfig.enabled && sslcommerzConfig.checkoutEnabled);
	const usePaystackCheckout = () => Boolean(paystackConfig.enabled && paystackConfig.checkoutEnabled);
	const useTapCheckout = () => Boolean(tapConfig.enabled && tapConfig.checkoutEnabled);
	const useMollieCheckout = () => Boolean(mollieConfig.enabled && mollieConfig.checkoutEnabled);
	const useKhaltiCheckout = () => Boolean(khaltiConfig.enabled && khaltiConfig.checkoutEnabled);
	const useEsewaCheckout = () => Boolean(esewaConfig.enabled && esewaConfig.checkoutEnabled);

	if (paymentRadios.length > 0) {
		paymentRadios.forEach(radio => {
			radio.addEventListener('change', () => {
				const val = radio.value;

				// Highlight selected method container border
				paymentRadios.forEach(r => {
					const label = r.closest('.omnify-payment-method-label');
					if (label) {
						if (r.checked) {
							label.style.borderColor = 'var(--omnify-primary)';
							label.style.borderWidth = '1px';
						} else {
							label.style.borderColor = 'var(--omnify-gray-200)';
						}
					}
				});

				if (val === 'card') {
					if (paypalNote) paypalNote.style.display = 'none';
					instructionSections.forEach(sec => sec.style.display = 'none');
				} else if (val === 'paypal') {
					if (paypalNote) paypalNote.style.display = usePayPalCheckout() ? 'block' : 'none';
					instructionSections.forEach(sec => sec.style.display = 'none');
				} else if (val === 'razorpay') {
					if (paypalNote) paypalNote.style.display = 'none';
					instructionSections.forEach(sec => sec.style.display = 'none');
				} else if (['alipay', 'wechat', 'sslcommerz', 'paystack', 'tap', 'mollie', 'khalti', 'esewa'].includes(val)) {
					if (paypalNote) paypalNote.style.display = 'none';
					instructionSections.forEach(sec => sec.style.display = 'none');
				} else {
					if (paypalNote) paypalNote.style.display = 'none';
					instructionSections.forEach(sec => {
						if (sec.id === `omnify-instructions-${val}`) {
							sec.style.display = 'block';
						} else {
							sec.style.display = 'none';
						}
					});
				}
			});
		});
	}

	// Form submit
	pageForm.addEventListener('submit', (e) => {
		e.preventDefault();

		const email     = document.getElementById('omnify-email').value;
		const firstName = document.getElementById('omnify-firstname').value;
		const lastName  = document.getElementById('omnify-lastname').value;
		const phone     = document.getElementById('omnify-phone').value;

			const accountMode   = createAccountCheckbox ? (createAccountCheckbox.dataset.accountMode || 'optional') : 'optional';
			const createAccount = createAccountCheckbox ? (createAccountCheckbox.checked || createAccountCheckbox.value === '1' || accountMode === 'automatic' || accountMode === 'required') : false;
			const password      = passwordInput ? passwordInput.value : '';

		// Get selected payment method
		const selectedPaymentMethodEl = document.querySelector('input[name="payment_method"]:checked');
		const selectedPaymentMethod = selectedPaymentMethodEl ? selectedPaymentMethodEl.value : 'card';
		const shouldUseStripeCheckout = selectedPaymentMethod === 'card' && useStripeCheckout();
		const shouldUsePayPalCheckout = selectedPaymentMethod === 'paypal' && usePayPalCheckout();
		const shouldUseRazorpayCheckout = selectedPaymentMethod === 'razorpay' && useRazorpayCheckout();
		const shouldUseAlipayCheckout = selectedPaymentMethod === 'alipay' && useAlipayCheckout();
		const shouldUseWechatCheckout = selectedPaymentMethod === 'wechat' && useWechatCheckout();
		const shouldUseSslcommerzCheckout = selectedPaymentMethod === 'sslcommerz' && useSslcommerzCheckout();
		const shouldUsePaystackCheckout = selectedPaymentMethod === 'paystack' && usePaystackCheckout();
		const shouldUseTapCheckout = selectedPaymentMethod === 'tap' && useTapCheckout();
		const shouldUseMollieCheckout = selectedPaymentMethod === 'mollie' && useMollieCheckout();
		const shouldUseKhaltiCheckout = selectedPaymentMethod === 'khalti' && useKhaltiCheckout();
		const shouldUseEsewaCheckout = selectedPaymentMethod === 'esewa' && useEsewaCheckout();

		// Terms acceptance check
		const termsCheckbox = document.getElementById('omnify-agree-terms');
		if (termsCheckbox && !termsCheckbox.checked) {
			errorBox.textContent = 'You must agree to the Terms & Conditions to complete your purchase.';
			errorBox.style.display = 'block';
			return;
		}
		const agreeToTerms = termsCheckbox ? (termsCheckbox.checked ? 1 : 0) : 0;

		// Disable submit button and trigger spinner animation
		submitBtn.disabled = true;
		submitBtn.querySelector('.btn-text').style.display = 'none';
		submitBtn.querySelector('.btn-spinner').style.display = 'inline-block';
		errorBox.style.display = 'none';

		const variationIdInput = document.getElementById('omnify-checkout-variation-id');
		const variationId = variationIdInput ? parseInt(variationIdInput.value, 10) : 0;
		const isCartMode = pageForm.getAttribute('data-cart-mode') === '1';

		const payload = {
			email:          email,
			first_name:     firstName,
			last_name:      lastName,
			phone:          phone,
			create_account: createAccount,
			password:       password,
			payment_method: selectedPaymentMethod,
			agree_to_terms: agreeToTerms,
			abandoned_cart_token: abandonedCartToken,
			cart_token:     getOmnifyCartToken(),
		};

		if (isCartMode) {
			const items = getCheckoutCartItems();
			payload.items = items.map(item => ({
				product_id: parseInt(item.id, 10),
				variation_id: item.variationId ? parseInt(item.variationId, 10) : null,
				quantity: parseInt(item.quantity || 1, 10)
			}));
		} else {
			payload.product_id = productId;
			payload.quantity = quantityInput ? parseInt(quantityInput.value, 10) || 1 : 1;
			if (variationId) {
				payload.variation_id = variationId;
			}
		}

		// Gather shipping address details if they exist in the DOM
		const shippingFirstNameEl = document.getElementById('omnify-shipping-firstname');
		if (shippingFirstNameEl) {
			payload.shipping_first_name = shippingFirstNameEl.value;
			payload.shipping_last_name  = document.getElementById('omnify-shipping-lastname').value;
			payload.shipping_address_1  = document.getElementById('omnify-shipping-address-1').value;
			payload.shipping_address_2  = document.getElementById('omnify-shipping-address-2').value;
			payload.shipping_city       = document.getElementById('omnify-shipping-city').value;
			payload.shipping_state      = document.getElementById('omnify-shipping-state').value;
			payload.shipping_postcode   = document.getElementById('omnify-shipping-postcode').value;
			payload.shipping_country    = document.getElementById('omnify-shipping-country').value;
			payload.shipping_phone      = document.getElementById('omnify-shipping-phone').value;
			payload.shipping_method_id  = shippingMethodIdEl ? shippingMethodIdEl.value : '';
		}

		// Gather billing address details
		const billingSameCheckbox = document.getElementById('omnify-billing-same');
		if (billingSameCheckbox && billingSameCheckbox.checked && shippingFirstNameEl) {
			payload.billing_first_name = shippingFirstNameEl.value;
			payload.billing_last_name  = document.getElementById('omnify-shipping-lastname').value;
			payload.billing_company     = '';
			payload.billing_address_1  = document.getElementById('omnify-shipping-address-1').value;
			payload.billing_address_2  = document.getElementById('omnify-shipping-address-2').value;
			payload.billing_city       = document.getElementById('omnify-shipping-city').value;
			payload.billing_state      = document.getElementById('omnify-shipping-state').value;
			payload.billing_postcode   = document.getElementById('omnify-shipping-postcode').value;
			payload.billing_country    = document.getElementById('omnify-shipping-country').value;
			payload.billing_phone      = document.getElementById('omnify-shipping-phone').value;
			payload.billing_same_as_shipping = 1;
		} else {
			const billingFirstNameEl = document.getElementById('omnify-billing-firstname');
			if (billingFirstNameEl) {
				// Validate required fields
				const reqBillingFields = [
					'omnify-billing-firstname',
					'omnify-billing-lastname',
					'omnify-billing-address-1',
					'omnify-billing-country',
					'omnify-billing-state',
					'omnify-billing-city',
					'omnify-billing-postcode'
				];
				for (const fieldId of reqBillingFields) {
					const el = document.getElementById(fieldId);
					if (el && !el.value.trim()) {
						errorBox.textContent = 'Please fill out all required billing fields.';
						errorBox.style.display = 'block';
						submitBtn.disabled = false;
						submitBtn.querySelector('.btn-text').style.display = 'inline';
						submitBtn.querySelector('.btn-spinner').style.display = 'none';
						return;
					}
				}

				payload.billing_first_name = billingFirstNameEl.value;
				payload.billing_last_name  = document.getElementById('omnify-billing-lastname').value;
				payload.billing_company     = document.getElementById('omnify-billing-company')?.value || '';
				payload.billing_address_1  = document.getElementById('omnify-billing-address-1').value;
				payload.billing_address_2  = document.getElementById('omnify-billing-address-2').value;
				payload.billing_city       = document.getElementById('omnify-billing-city').value;
				payload.billing_state      = document.getElementById('omnify-billing-state').value;
				payload.billing_postcode   = document.getElementById('omnify-billing-postcode').value;
				payload.billing_country    = document.getElementById('omnify-billing-country').value;
				payload.billing_phone      = document.getElementById('omnify-billing-phone').value;
			}
			payload.billing_same_as_shipping = 0;
		}

		// Include validated coupon code if one was applied
		if (appliedCouponCode) {
			payload.coupon_code = appliedCouponCode;
		}

		if (shouldUsePayPalCheckout) {
			window.fetch(`${omnifyStorefront.restUrl}/paypal/order`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': omnifyStorefront.nonce
				},
				body: JSON.stringify(payload)
			})
			.then((response) => response.json().then((data) => {
				if (!response.ok) {
					throw new Error(data.message || 'Unable to start PayPal Checkout.');
				}
				return data;
			}))
			.then((res) => {
				if (!res.url) {
					throw new Error('PayPal Checkout did not return an approval URL.');
				}
				clearAbandonedCartToken();
				setCartItems([]); // Clear local cart
				window.sessionStorage.removeItem(OMNIFY_CHECKOUT_SELECTED_KEY);
				window.location.href = res.url;
			})
			.catch((err) => {
				errorBox.textContent = err.message;
				errorBox.style.display = 'block';
				submitBtn.disabled = false;
				submitBtn.querySelector('.btn-text').style.display = 'inline';
				submitBtn.querySelector('.btn-spinner').style.display = 'none';
			});
			return;
		}

		if (shouldUseStripeCheckout) {
			window.fetch(`${omnifyStorefront.restUrl}/stripe/checkout-session`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': omnifyStorefront.nonce
				},
				body: JSON.stringify(payload)
			})
			.then((response) => response.json().then((data) => {
				if (!response.ok) {
					throw new Error(data.message || 'Unable to start Stripe Checkout.');
				}
				return data;
			}))
			.then((res) => {
				if (!res.url) {
					throw new Error('Stripe Checkout did not return a redirect URL.');
				}
				clearAbandonedCartToken();
				setCartItems([]); // Clear local cart
				window.sessionStorage.removeItem(OMNIFY_CHECKOUT_SELECTED_KEY);
				window.location.href = res.url;
			})
			.catch((err) => {
				errorBox.textContent = err.message;
				errorBox.style.display = 'block';
				submitBtn.disabled = false;
				submitBtn.querySelector('.btn-text').style.display = 'inline';
				submitBtn.querySelector('.btn-spinner').style.display = 'none';
			});
			return;
		}

		if (shouldUseRazorpayCheckout) {
			window.fetch(`${omnifyStorefront.restUrl}/razorpay/order`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': omnifyStorefront.nonce
				},
				body: JSON.stringify(payload)
			})
			.then((response) => response.json().then((data) => {
				if (!response.ok) {
					throw new Error(data.message || 'Unable to start Razorpay payment.');
				}
				return data;
			}))
			.then((res) => {
				if (!res.razorpay_order_id || !res.key) {
					throw new Error('Razorpay did not return required order data.');
				}
				clearAbandonedCartToken();
				setCartItems([]);
				window.sessionStorage.removeItem(OMNIFY_CHECKOUT_SELECTED_KEY);

				// Initialize Razorpay Checkout
				const options = {
					key: res.key,
					amount: res.amount,
					currency: res.currency,
					name: res.name || 'Checkout',
					description: res.description || '',
					order_id: res.razorpay_order_id,
					prefill: res.prefill || {},
					handler: function (response) {
						// Verify on server
						window.fetch(`${omnifyStorefront.restUrl}/razorpay/verify`, {
							method: 'POST',
							headers: {
								'Content-Type': 'application/json',
								'X-WP-Nonce': omnifyStorefront.nonce
							},
							body: JSON.stringify({
								order_id: res.order_id,
								razorpay_payment_id: response.razorpay_payment_id,
								razorpay_order_id: response.razorpay_order_id,
								razorpay_signature: response.razorpay_signature,
							})
						})
						.then(vres => vres.json())
						.then(vdata => {
							if (vdata.success) {
								showSuccessPage(vdata.order_number || vdata.order_id);
							} else {
								errorBox.textContent = vdata.message || 'Payment verification failed.';
								errorBox.style.display = 'block';
							}
						})
						.catch(() => {
							errorBox.textContent = 'Payment verification failed. Please contact support.';
							errorBox.style.display = 'block';
						});
					},
					modal: {
						ondismiss: function () {
							// Re-enable button on cancel
							submitBtn.disabled = false;
							submitBtn.querySelector('.btn-text').style.display = 'inline';
							submitBtn.querySelector('.btn-spinner').style.display = 'none';
						}
					},
					theme: {
						color: '#6366f1'
					}
				};
				const rzp = new window.Razorpay(options);
				rzp.open();
			})
			.catch((err) => {
				errorBox.textContent = err.message;
				errorBox.style.display = 'block';
				submitBtn.disabled = false;
				submitBtn.querySelector('.btn-text').style.display = 'inline';
				submitBtn.querySelector('.btn-spinner').style.display = 'none';
			});
			return;
		}

		if (shouldUseAlipayCheckout || shouldUseWechatCheckout || shouldUseSslcommerzCheckout || shouldUsePaystackCheckout || shouldUseTapCheckout || shouldUseMollieCheckout || shouldUseKhaltiCheckout || shouldUseEsewaCheckout) {
			const gateway = selectedPaymentMethod;
			window.fetch(`${omnifyStorefront.restUrl}/${gateway}/order`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': omnifyStorefront.nonce
				},
				body: JSON.stringify(payload)
			})
			.then((response) => response.json().then((data) => {
				if (!response.ok) {
					throw new Error(data.message || `Unable to start ${gateway} payment.`);
				}
				return data;
			}))
			.then((res) => {
				if (res.form_url && res.params) {
					clearAbandonedCartToken();
					setCartItems([]);
					window.sessionStorage.removeItem(OMNIFY_CHECKOUT_SELECTED_KEY);
					const form = document.createElement('form');
					form.method = 'POST';
					form.action = res.form_url;
					for (const key in res.params) {
						if (Object.prototype.hasOwnProperty.call(res.params, key)) {
							const input = document.createElement('input');
							input.type = 'hidden';
							input.name = key;
							input.value = res.params[key];
							form.appendChild(input);
						}
					}
					document.body.appendChild(form);
					form.submit();
					return;
				}
				if (res.redirect_url) {
					clearAbandonedCartToken();
					setCartItems([]);
					window.sessionStorage.removeItem(OMNIFY_CHECKOUT_SELECTED_KEY);
					window.location.href = res.redirect_url;
					return;
				}
				throw new Error('Gateway did not return a redirect URL.');
			})
			.catch((err) => {
				errorBox.textContent = err.message;
				errorBox.style.display = 'block';
				submitBtn.disabled = false;
				submitBtn.querySelector('.btn-text').style.display = 'inline';
				submitBtn.querySelector('.btn-spinner').style.display = 'none';
			});
			return;
		}

		window.fetch(`${omnifyStorefront.restUrl}/checkout`, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': omnifyStorefront.nonce
			},
			body: JSON.stringify(payload)
		})
		.then((response) => response.json().then((data) => {
			if (!response.ok) {
				throw new Error(data.message || 'An error occurred during checkout.');
			}
			return data;
		}))
		.then((res) => {
			// Hide checkout grid and render receipt screen
			gridLayout.style.display = 'none';
			successBox.style.display = 'block';
			clearAbandonedCartToken();
			setCartItems([]); // Clear local cart
			window.sessionStorage.removeItem(OMNIFY_CHECKOUT_SELECTED_KEY);
			document.getElementById('omnify-page-success-order-id').textContent = res.order_number || `#${res.order_id}`;

			// Track Purchase
			try {
				omnifyTrackPurchase(res);
			} catch (e) {
				console.error('Error tracking purchase:', e);
			}

			const filesList = document.getElementById('omnify-page-success-files-list');
			filesList.innerHTML = '';

			if (res.is_physical) {
				const successTitle = successBox.querySelector('h3');
				if (successTitle) successTitle.textContent = 'Order Placed Successfully!';

				const successDesc = successBox.querySelector('p');
				if (successDesc) {
					successDesc.textContent = 'Thank you for your order! We have received your purchase and a confirmation email has been sent to you. Your order will be prepared and shipped shortly.';
				}

				const filesListHeading = successBox.querySelector('h4');
				if (filesListHeading) filesListHeading.style.display = 'none';
				if (filesList) filesList.style.display = 'none';

				const footerPortalLink = successBox.querySelector('p:last-of-type');
				if (footerPortalLink) {
					footerPortalLink.innerHTML = footerPortalLink.innerHTML.replace('access your downloads', 'track your order');
				}
			} else {
				if (!res.files || res.files.length === 0) {
					if (res.is_manual || res.status === 'pending_payment' || res.status === 'on_hold') {
						filesList.innerHTML = '<div style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; border-radius: 8px; padding: 12px 16px; font-size: 13px; line-height: 1.5;">Your download links will become available once your payment has been confirmed by the store.</div>';
					} else {
						filesList.innerHTML = '<p style="color: var(--omnify-gray-600); font-style: italic; font-size: 13px;">No files associated with this purchase.</p>';
					}
				} else {
					res.files.forEach((file) => {
						const row = document.createElement('div');
						row.style.cssText = 'background: var(--omnify-gray-100); border: 1px solid var(--omnify-gray-200); border-radius: 8px; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; gap: 10px;';
						row.innerHTML = `
							<div>
								<span style="font-weight: 600; display: block; font-size: 13px; color: var(--omnify-dark);">${file.file_name}</span>
								<span style="font-size: 11px; display: block; color: var(--omnify-gray-600); margin-top: 2px;">v${file.version} / ${file.size_label}</span>
							</div>
							<a href="${file.download_url}" class="omnify-btn omnify-btn--primary omnify-btn--sm" style="padding: 6px 12px; font-size: 12px;" download>Download</a>
						`;
						filesList.appendChild(row);
					});
				}
			}
		})
		.catch((err) => {
			errorBox.textContent = err.message;
			errorBox.style.display = 'block';
		})
		.finally(() => {
			submitBtn.disabled = false;
			submitBtn.querySelector('.btn-text').style.display = 'inline';
			submitBtn.querySelector('.btn-spinner').style.display = 'none';
		});
	});

	// Checkout page quantity selectors and recalculations
	function updateCheckoutTotalUI() {
		let subtotal = 0;
		document.querySelectorAll('.omnify-checkout-qty-selector').forEach(sel => {
			const qtyInput = sel.querySelector('.omnify-checkout-qty-input');
			const qty = parseInt(qtyInput.value, 10) || 1;
			const price = parseFloat(sel.getAttribute('data-price') || '0');
			subtotal += qty * price;
			
			// Update the display text for this item
			const labelEl = sel.closest('div')?.querySelector('.omnify-checkout-qty-display');
			if (labelEl) {
				labelEl.textContent = `Qty: ${qty} × ${formatStoreMoney(price)}`;
			}
		});
		
		const subtotalEl = document.getElementById('omnify-summary-subtotal');
		if (subtotalEl) {
			subtotalEl.textContent = formatStoreMoney(subtotal);
		}
		
		refreshSummary(appliedDiscount);
	}

	const quantityBtns = document.querySelectorAll('.omnify-checkout-qty-btn');
	if (quantityBtns.length > 0) {
		quantityBtns.forEach((btn) => {
			btn.addEventListener('click', (e) => {
				e.preventDefault();
				const wrapper = btn.closest('.omnify-checkout-qty-selector');
				if (!wrapper) return;
				const input = wrapper.querySelector('.omnify-checkout-qty-input');
				if (!input) return;

				let val = parseInt(input.value, 10) || 1;
				const min = parseInt(input.min, 10) || 1;
				const max = input.max ? parseInt(input.max, 10) : 9999;

				if (btn.classList.contains('checkout-qty-minus')) {
					val = Math.max(min, val - 1);
				} else {
					val = Math.min(max, val + 1);
				}
				input.value = val;

				const isCartMode = pageForm.getAttribute('data-cart-mode') === '1';
				if (isCartMode) {
					const productId = wrapper.getAttribute('data-product-id');
					const variationId = wrapper.getAttribute('data-variation-id') || '';
					
					// Update localStorage cart
					const cartItems = getCartItems();
					const index = cartItems.findIndex(item => String(item.id) === String(productId) && String(item.variationId || '') === String(variationId));
					if (index >= 0) {
						cartItems[index].quantity = val;
						setCartItems(cartItems);

						const checkoutItems = getCheckoutCartItems().map(item => {
							if (String(item.id) === String(productId) && String(item.variationId || '') === String(variationId)) {
								return { ...item, quantity: val };
							}
							return item;
						});
						setCheckoutCartItems(checkoutItems);
						
						// Trigger sync with server-side transient session
						const formattedItems = checkoutItems.map(item => ({
							product_id: parseInt(item.id, 10),
							variation_id: item.variationId ? parseInt(item.variationId, 10) : null,
							quantity: parseInt(item.quantity || 1, 10)
						}));

						// Update UI dynamically
						updateCheckoutTotalUI();

						// Sync with backend
						window.fetch(`${window.omnifyStorefront.restUrl}/cart`, {
							method: 'POST',
							headers: {
								'Content-Type': 'application/json',
								'X-WP-Nonce': window.omnifyStorefront.nonce
							},
							body: JSON.stringify({ items: formattedItems })
						})
						.then(res => res.json())
						.then(data => {
							if (data.token) {
								setOmnifyCartToken(data.token);
							}
						})
						.catch(err => console.error('Error syncing cart quantity:', err));
					}
				} else {
					// Single product mode: update the hidden input #omnify-checkout-quantity
					const hiddenQtyInput = document.getElementById('omnify-checkout-quantity');
					if (hiddenQtyInput) {
						hiddenQtyInput.value = val;
					}
					// Update UI dynamically
					updateCheckoutTotalUI();
				}
			});
		});
	}

	// Login Toggle
	const loginBtn = document.getElementById('omnify-toggle-login-btn');
	const loginDiv = document.getElementById('omnify-checkout-login-dropdown');
	if (loginBtn && loginDiv) {
		loginBtn.addEventListener('click', function(e) {
			e.preventDefault();
			loginDiv.style.display = loginDiv.style.display === 'none' ? 'block' : 'none';
		});
	}

	// Company Name Toggle
	const compBtn = document.getElementById('omnify-toggle-company');
	const compDiv = document.getElementById('omnify-company-input-group');
	if (compBtn && compDiv) {
		compBtn.addEventListener('click', function(e) {
			e.preventDefault();
			compBtn.style.display = 'none';
			compDiv.style.display = 'block';
			const input = document.getElementById('omnify-company');
			if (input) input.focus();
		});
	}

	// Coupon code toggler
	const couponBtn = document.getElementById('omnify-toggle-coupon-btn');
	const couponForm = document.getElementById('omnify-coupon-input-wrapper');
	if (couponBtn && couponForm) {
		couponBtn.addEventListener('click', function(e) {
			e.preventDefault();
			if (couponForm.style.display === 'none') {
				couponForm.style.display = 'flex';
				couponBtn.textContent = 'Close';
			} else {
				couponForm.style.display = 'none';
				couponBtn.textContent = 'Click here to enter';
			}
		});
	}

	// Carousel navigation
	const prevBtn = document.getElementById('coupon-prev');
	const nextBtn = document.getElementById('coupon-next');
	const carousel = document.getElementById('omnify-coupons-carousel');
	if (prevBtn && nextBtn && carousel) {
		prevBtn.addEventListener('click', () => carousel.scrollBy({ left: -180, behavior: 'smooth' }));
		nextBtn.addEventListener('click', () => carousel.scrollBy({ left: 180, behavior: 'smooth' }));
	}

	// Available coupon selection autofill & apply
	document.addEventListener('change', function(e) {
		if (e.target && e.target.name === 'select_avail_coupon') {
			const couponInput = document.getElementById('omnify-coupon-input');
			const applyBtn = document.getElementById('omnify-coupon-apply');
			if (couponInput && applyBtn) {
				couponInput.value = e.target.value;
				applyBtn.click();
			}
		}
	});

	// Sync line total with PDP quantity badge
	const pdpQtyBadge = document.getElementById('omnify-pdp-qty-badge-display');
	const pdpLineTotal = document.getElementById('omnify-pdp-line-total-display');
	const checkoutQtyInput = document.querySelector('.omnify-checkout-qty-input');
	if (pdpQtyBadge && pdpLineTotal && checkoutQtyInput) {
		const syncPdpLineTotal = () => {
			const qty = parseInt(checkoutQtyInput.value, 10) || 1;
			pdpQtyBadge.textContent = qty;
			const selector = document.querySelector('.omnify-checkout-qty-selector');
			const price = selector ? parseFloat(selector.dataset.price) || 0 : 0;
			const total = price * qty;
			const rawText = pdpLineTotal.textContent;
			pdpLineTotal.textContent = rawText.replace(/[\d.,]+/, total.toFixed(2));
		};
		const observer = new MutationObserver(syncPdpLineTotal);
		observer.observe(checkoutQtyInput, { attributes: true, attributeFilter: ['value'] });
	}

	// Saved address autofill
	const trigger = document.querySelector('.omnify-saved-address-trigger');
	const dropdown = document.querySelector('.omnify-saved-address-options');
	const hiddenInput = document.getElementById('omnify-saved-address-select');
	if (trigger && dropdown && hiddenInput) {
		trigger.addEventListener('click', function(e) {
			e.preventDefault();
			e.stopPropagation();
			dropdown.classList.toggle('show');
		});

		document.addEventListener('click', function() {
			dropdown.classList.remove('show');
		});

		dropdown.addEventListener('click', function(e) {
			const option = e.target.closest('.omnify-saved-address-option');
			if (!option) return;

			const value = option.dataset.value;
			hiddenInput.value = value;
			
			const label = trigger.querySelector('.omnify-saved-address-selected-label');
			if (label) {
				label.textContent = option.textContent.trim();
			}

			dropdown.classList.remove('show');

			if (!value) {
				clearManualShippingFields();
			} else {
				autofillFromOption(option);
			}
		});

		const defaultOpt = dropdown.querySelector('.omnify-saved-address-option[data-is-default="true"]');
		if (defaultOpt) {
			setTimeout(() => {
				defaultOpt.click();
			}, 100);
		}

		const useDifferentLink = document.getElementById('omnify-use-different-address');
		if (useDifferentLink) {
			useDifferentLink.addEventListener('click', function(e) {
				e.preventDefault();
				hiddenInput.value = '';
				const label = trigger.querySelector('.omnify-saved-address-selected-label');
				if (label) {
					label.textContent = '— Enter new address —';
				}
				clearManualShippingFields();
			});
		}
	}

	function autofillFromOption(opt) {
		const fields = {
			'omnify-shipping-firstname': 'firstname',
			'omnify-shipping-lastname': 'lastname',
			'omnify-shipping-address-1': 'address1',
			'omnify-shipping-address-2': 'address2',
			'omnify-shipping-city': 'city',
			'omnify-shipping-postcode': 'postcode'
		};
		const map = {
			firstname: opt.dataset.firstname,
			lastname: opt.dataset.lastname,
			address1: opt.dataset.address1,
			address2: opt.dataset.address2,
			city: opt.dataset.city,
			postcode: opt.dataset.postcode
		};
		for (const [id, key] of Object.entries(fields)) {
			const el = document.getElementById(id);
			if (el) el.value = map[key] || '';
		}
		const countryEl = document.getElementById('omnify-shipping-country');
		if (countryEl && opt.dataset.country) {
			countryEl.value = opt.dataset.country;
			countryEl.dispatchEvent(new Event('change', {bubbles: true}));
			setTimeout(function() {
				const stateEl = document.getElementById('omnify-shipping-state');
				if (stateEl && opt.dataset.state) {
					stateEl.value = opt.dataset.state;
					stateEl.dispatchEvent(new Event('change', {bubbles: true}));
				}
			}, 300);
		}
		const phoneEl = document.getElementById('omnify-shipping-phone');
		if (phoneEl && opt.dataset.phone) phoneEl.value = opt.dataset.phone;
	}

	function clearManualShippingFields() {
		const ids = ['omnify-shipping-firstname', 'omnify-shipping-lastname', 'omnify-shipping-address-1', 'omnify-shipping-address-2', 'omnify-shipping-city', 'omnify-shipping-postcode', 'omnify-shipping-phone'];
		ids.forEach(id => {
			const el = document.getElementById(id);
			if (el) el.value = '';
		});
		const country = document.getElementById('omnify-shipping-country');
		const state = document.getElementById('omnify-shipping-state');
		if (country) country.value = '';
		if (state) state.innerHTML = '<option value="">Select country first</option>';
	}
}

function initPayPalCaptureReturn() {
	const notice = document.getElementById('omnify-paypal-capture-notice');
	if (!notice || !window.omnifyStorefront) return;

	const orderId = parseInt(notice.dataset.orderId || '0', 10);
	const paypalOrderId = notice.dataset.paypalOrderId || '';
	if (!orderId || !paypalOrderId) {
		notice.textContent = 'PayPal returned without enough payment data. Please contact support.';
		notice.style.background = '#fff0ed';
		notice.style.borderColor = '#fecaca';
		notice.style.color = '#991b1b';
		return;
	}

	window.fetch(`${omnifyStorefront.restUrl}/paypal/capture`, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			'X-WP-Nonce': omnifyStorefront.nonce
		},
		body: JSON.stringify({
			order_id: orderId,
			paypal_order_id: paypalOrderId
		})
	})
	.then((response) => response.json().then((data) => {
		if (!response.ok) {
			throw new Error(data.message || 'Unable to confirm PayPal payment.');
		}
		return data;
	}))
	.then(() => {
		notice.textContent = 'PayPal payment confirmed. Your order is complete and a confirmation email has been sent.';
	})
	.catch((err) => {
		notice.textContent = err.message;
		notice.style.background = '#fff0ed';
		notice.style.borderColor = '#fecaca';
		notice.style.color = '#991b1b';
	});
}

/**
 * Portal tabs logic.
 */
function initPortalTabs() {
	const tabs  = document.querySelectorAll('.omnify-tab-btn');
	const panes = document.querySelectorAll('.omnify-tab-pane');

	if (tabs.length === 0) return;

	function activateTab(tabName) {
		const targetTab = Array.from(tabs).find(t => t.getAttribute('data-tab') === tabName);
		if (!targetTab) return;

		tabs.forEach((t) => t.classList.remove('active'));
		panes.forEach((p) => p.style.display = 'none');

		targetTab.classList.add('active');
		const activePane = document.getElementById(`omnify-pane-${tabName}`);
		if (activePane) {
			activePane.style.display = 'block';
		}
	}

	tabs.forEach((tab) => {
		tab.addEventListener('click', () => {
			const tabName = tab.getAttribute('data-tab');
			activateTab(tabName);
			// Update URL hash without jumping the page
			history.replaceState(null, '', `#omnify-tab-${tabName}`);
		});
	});

	// Check hash on page load
	const hash = window.location.hash;
	if (hash && hash.startsWith('#omnify-tab-')) {
		const tabName = hash.replace('#omnify-tab-', '');
		activateTab(tabName);
	} else if (hash && hash.startsWith('#omnify-pane-')) {
		const tabName = hash.replace('#omnify-pane-', '');
		activateTab(tabName);
	}

	document.querySelectorAll('[data-omnify-open-tab]').forEach((trigger) => {
		trigger.addEventListener('click', () => {
			const tabName = trigger.getAttribute('data-omnify-open-tab');
			activateTab(tabName);
			history.replaceState(null, '', `#omnify-tab-${tabName}`);
			document.querySelector('.omnify-portal-tabs')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
		});
	});
}

/**
 * Customer portal auth quality-of-life interactions.
 */
function initPortalAuthEnhancements() {
	document.querySelectorAll('[data-omnify-toggle-password]').forEach((button) => {
		button.addEventListener('click', () => {
			const field = button.closest('.omnify-password-field')?.querySelector('input');
			if (!field) return;

			const isPassword = field.type === 'password';
			field.type = isPassword ? 'text' : 'password';
			button.textContent = isPassword ? 'Hide' : 'Show';
			button.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
		});
	});

	document.querySelectorAll('[data-omnify-password-strength]').forEach((field) => {
		const meter = field.closest('form')?.querySelector('[data-omnify-password-meter]');
		if (!meter) return;

		field.addEventListener('input', () => {
			const value = field.value || '';
			let score = 0;
			if (value.length >= 8) score++;
			if (/[a-z]/.test(value) && /[A-Z]/.test(value)) score++;
			if (/\d/.test(value)) score++;
			if (/[^A-Za-z0-9]/.test(value)) score++;

			meter.classList.remove('is-weak', 'is-ok', 'is-strong');
			if (!value) {
				meter.textContent = 'Use at least 8 characters. Mix letters, numbers, and symbols for a stronger password.';
				return;
			}
			if (score <= 1) {
				meter.classList.add('is-weak');
				meter.textContent = 'Weak password. Add more characters and variety.';
			} else if (score <= 3) {
				meter.classList.add('is-ok');
				meter.textContent = 'Good password. A symbol or mixed case will make it stronger.';
			} else {
				meter.classList.add('is-strong');
				meter.textContent = 'Strong password.';
			}
		});
	});
}

// ── Conversion Tracking Helpers ─────────────────────────────────────
function omnifyTrackAddToCart(item) {
	if (!window.omnifyStorefront || !window.omnifyStorefront.tracking) return;
	const tracking = window.omnifyStorefront.tracking;
	const currency = tracking.currency || 'USD';
	const value = parseFloat(item.price) * parseInt(item.quantity || 1, 10);
	const itemId = item.variationId ? String(item.variationId) : String(item.id);

	if (tracking.ga4Enabled && typeof window.gtag === 'function') {
		window.gtag('event', 'add_to_cart', {
			currency: currency,
			value: value,
			items: [{
				item_id: itemId,
				item_name: item.name,
				price: parseFloat(item.price),
				quantity: parseInt(item.quantity || 1, 10)
			}]
		});
	}

	if (tracking.metaEnabled && typeof window.fbq === 'function') {
		window.fbq('track', 'AddToCart', {
			content_ids: [itemId],
			content_name: item.name,
			content_type: 'product',
			value: value,
			currency: currency
		});
	}
}

function omnifyTrackBeginCheckout(items, value) {
	if (!window.omnifyStorefront || !window.omnifyStorefront.tracking) return;
	const tracking = window.omnifyStorefront.tracking;
	const currency = tracking.currency || 'USD';

	if (tracking.ga4Enabled && typeof window.gtag === 'function') {
		window.gtag('event', 'begin_checkout', {
			currency: currency,
			value: parseFloat(value),
			items: items
		});
	}

	if (tracking.metaEnabled && typeof window.fbq === 'function') {
		window.fbq('track', 'InitiateCheckout', {
			content_ids: items.map(i => i.item_id),
			content_type: 'product',
			value: parseFloat(value),
			currency: currency
		});
	}
}

function omnifyTrackPurchase(res) {
	if (!window.omnifyStorefront || !window.omnifyStorefront.tracking) return;
	const tracking = window.omnifyStorefront.tracking;
	const currency = tracking.currency || 'USD';
	const total = parseFloat(res.totals.total);
	const transactionId = String(res.order_number || res.order_id);

	const tracked = JSON.parse(window.sessionStorage.getItem('omnify_tracked_orders') || '[]');
	if (tracked.includes(transactionId)) {
		return;
	}
	tracked.push(transactionId);
	window.sessionStorage.setItem('omnify_tracked_orders', JSON.stringify(tracked));

	let items = [];
	if (res.items && res.items.length) {
		items = res.items.map(item => ({
			item_id: item.variation_id ? String(item.variation_id) : String(item.product_id),
			item_name: item.product_name,
			price: parseFloat(item.price || (res.totals.total / res.items.length)),
			quantity: parseInt(item.quantity || 1, 10)
		}));
	} else if (res.product) {
		const productId = String(res.product.id);
		const variationId = document.getElementById('omnify-checkout-variation-id')?.value || '0';
		const quantity = parseInt(document.getElementById('omnify-checkout-quantity')?.value || '1', 10);
		const itemId = variationId !== '0' ? variationId : productId;
		items = [{
			item_id: itemId,
			item_name: res.product.name,
			price: parseFloat(res.totals.subtotal / quantity),
			quantity: quantity
		}];
	}

	if (tracking.ga4Enabled && typeof window.gtag === 'function') {
		const gaPayload = {
			transaction_id: transactionId,
			value: total,
			tax: parseFloat(res.totals.tax || 0),
			shipping: parseFloat(res.totals.shipping_total || 0),
			currency: currency,
			items: items
		};
		const couponInput = document.getElementById('omnify-coupon-input');
		if (couponInput && couponInput.value.trim()) {
			gaPayload.coupon = couponInput.value.trim();
		}
		window.gtag('event', 'purchase', gaPayload);
	}

	if (tracking.metaEnabled && typeof window.fbq === 'function') {
		window.fbq('track', 'Purchase', {
			value: total,
			currency: currency,
			content_ids: items.map(i => i.item_id),
			content_type: 'product'
		});
	}
}

/**
 * Premium Shop Grid Custom Swatches and Load More interactivity
 */
function initPremiumCatalog() {
	// ── Price Formatter Helper ──────────────────────────────────
	function formatPrice(amount) {
		const symbol = window.omnifyStorefront?.currencySymbol || '$';
		const position = window.omnifyStorefront?.currencyPosition || 'before';
		const formatted = parseFloat(amount).toFixed(2);
		return position === 'after' ? `${formatted} ${symbol}` : `${symbol}${formatted}`;
	}

	// ── Swatches Interaction ────────────────────────────────────
	const cards = document.querySelectorAll('.omnify-product-card--premium');
	
	// Apply background colors to color swatches
	const colorMap = {
		'red': '#e11d48',
		'blue': '#2563eb',
		'green': '#16a34a',
		'yellow': '#ca8a04',
		'black': '#0f172a',
		'white': '#ffffff',
		'gray': '#64748b',
		'grey': '#64748b',
		'pink': '#ec4899',
		'purple': '#a855f7',
		'orange': '#f97316',
		'brown': '#78350f',
		'navy': '#1e3a8a',
		'gold': '#d97706',
		'silver': '#cbd5e1',
		'beige': '#f5f5dc',
		'cream': '#fffdd0',
		'olive': '#808000',
		'teal': '#0d9488',
		'burgundy': '#800020'
	};
	
	document.querySelectorAll('.omnify-swatch-item--color').forEach(swatch => {
		const val = (swatch.dataset.value || '').toLowerCase().trim();
		if (val) {
			const hex = colorMap[val] || colorMap[val.split(/\s+/)[0]] || val;
			swatch.style.backgroundColor = hex;
		}
	});

	cards.forEach((card) => {
		const swatches = card.querySelectorAll('.omnify-swatch-item');
		if (!swatches.length) return;

		const variations = JSON.parse(card.dataset.variations || '[]');
		const primaryImg = card.querySelector('.omnify-product-card__image--primary');
		const priceWrapper = card.querySelector('.omnify-product-card__price');
		const actionBtns = card.querySelectorAll('.omnify-btn-slide-cart, .omnify-product-card__quick-add');

		// Store original product card data
		const originalSrc = primaryImg ? primaryImg.src : '';
		const originalPriceHtml = priceWrapper ? priceWrapper.innerHTML : '';
		const buttonsState = [];
		actionBtns.forEach(btn => {
			buttonsState.push({
				btn: btn,
				html: btn.innerHTML,
				href: btn.getAttribute('href') || '',
				className: btn.className
			});
		});

		swatches.forEach((swatch) => {
			swatch.addEventListener('click', (e) => {
				e.preventDefault();
				e.stopPropagation();

				// Toggle active state
				const list = swatch.closest('.omnify-swatch-list');
				const isAlreadyActive = swatch.classList.contains('is-active');

				list.querySelectorAll('.omnify-swatch-item').forEach((item) => {
					item.classList.remove('is-active');
				});

				if (!isAlreadyActive) {
					swatch.classList.add('is-active');
				}

				// Find active attributes
				const activeColors = Array.from(card.querySelectorAll('.omnify-swatch-item--color.is-active')).map(s => s.dataset.value);
				const activeSizes = Array.from(card.querySelectorAll('.omnify-swatch-item--size.is-active')).map(s => s.dataset.value);

				// Find matched variation
				let matchedVar = null;
				if (activeColors.length > 0 || activeSizes.length > 0) {
					matchedVar = variations.find((v) => {
						return Object.entries(v.attributes).every(([key, val]) => {
							const k = key.toLowerCase();
							const vVal = String(val).toLowerCase().trim();
							if (k === 'color' || k === 'colour') {
								return activeColors.length === 0 || activeColors.includes(vVal);
							}
							if (k === 'size') {
								return activeSizes.length === 0 || activeSizes.includes(vVal);
							}
							return true;
						});
					});
				}

				// Apply updates
				if (matchedVar) {
					// 1. Swap Image
					if (primaryImg) {
						if (!primaryImg.dataset.originalSrc) {
							primaryImg.dataset.originalSrc = originalSrc;
						}
						primaryImg.src = matchedVar.thumbnail_url || originalSrc;
					}

					// 2. Update Price
					if (priceWrapper) {
						if (matchedVar.sale_price !== null && matchedVar.sale_price < matchedVar.price) {
							priceWrapper.innerHTML = `
								<span class="original-price">${formatPrice(matchedVar.price)}</span>
								<span class="current-price sale">${formatPrice(matchedVar.sale_price)}</span>
							`;
						} else {
							priceWrapper.innerHTML = `
								<span class="current-price">${formatPrice(matchedVar.price)}</span>
							`;
						}
					}

					// 3. Convert Select Options -> Add to Cart
					buttonsState.forEach(state => {
						const btn = state.btn;
						btn.removeAttribute('href');
						btn.classList.remove('omnify-select-options-btn');
						btn.classList.add('omnify-add-cart-button');
						if (btn.classList.contains('omnify-product-card__quick-add')) {
							btn.innerHTML = `
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
							`;
						} else {
							btn.innerHTML = `
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
								<span>Add to Cart</span>
							`;
						}
						const actualPrice = matchedVar.sale_price !== null ? matchedVar.sale_price : matchedVar.price;
						btn.dataset.id = card.dataset.productId;
						btn.dataset.variationId = matchedVar.id;
						btn.dataset.name = card.dataset.productName + ' - ' + Object.values(matchedVar.attributes).join(', ');
						btn.dataset.price = actualPrice;
						btn.dataset.displayPrice = formatPrice(actualPrice);
						btn.dataset.image = matchedVar.thumbnail_url || originalSrc;
						btn.dataset.url = card.dataset.url;
					});
				} else {
					// Reset to original
					if (primaryImg) primaryImg.src = primaryImg.dataset.originalSrc || originalSrc;
					if (priceWrapper) priceWrapper.innerHTML = originalPriceHtml;
					buttonsState.forEach(state => {
						const btn = state.btn;
						if (state.href) {
							btn.setAttribute('href', state.href);
						} else {
							btn.removeAttribute('href');
						}
						btn.className = state.className;
						btn.innerHTML = state.html;
						delete btn.dataset.variationId;
					});
				}
			});
		});
	});



	// ── Event Delegation for Dynamically Converted Buttons ──────
	document.addEventListener('click', (event) => {
		const btn = event.target.closest('.omnify-product-card__slide-up-action .omnify-add-cart-button, .omnify-product-card__quick-add-wrap .omnify-add-cart-button');
		if (!btn) return;

		event.preventDefault();
		event.stopPropagation();

		if (btn.disabled || btn.classList.contains('disabled')) {
			return;
		}

		const item = cartItemFromButton(btn);
		addItemToCart(item);
		showAddedToCartToast(item);

		// Track Add to Cart
		try {
			omnifyTrackAddToCart(item);
		} catch (e) {
			console.error('Error tracking add to cart:', e);
		}

		const isQuickAdd = btn.classList.contains('omnify-product-card__quick-add');
		const originalHtml = isQuickAdd ? btn.innerHTML : '';
		const originalText = (!isQuickAdd && btn.querySelector('span')) ? btn.querySelector('span').textContent : 'Add to Cart';
		const span = btn.querySelector('span');

		if (isQuickAdd) {
			btn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>`;
		} else if (span) {
			span.textContent = 'Added';
		}
		btn.classList.add('is-added');

		window.setTimeout(() => {
			if (isQuickAdd) {
				btn.innerHTML = originalHtml;
			} else if (span) {
				span.textContent = originalText;
			}
			btn.classList.remove('is-added');
		}, 1300);
	});
}

/**
 * Single Product Detail page (PDP) interactivity.
 */
function initProductSinglePage() {
	const pdpWrapper = document.querySelector('.omnify-pdp-wrapper');
	if (!pdpWrapper) return;

	const isVariable = pdpWrapper.getAttribute('data-is-variable') === '1';
	const variationsJson = pdpWrapper.getAttribute('data-variations') || '[]';
	let variations = [];
	try {
		variations = JSON.parse(variationsJson);
	} catch (e) {
		console.error('Error parsing product variations:', e);
	}
	const productId = parseInt(pdpWrapper.getAttribute('data-product-id') || '0', 10);
	const omnifyInfiniteScrollEnabled = pdpWrapper.getAttribute('data-infinite-scroll') === '1';

	// ── 1. Gallery View / Video Swap ──────────────────────────────────
	(function(){
		const mainImg = document.getElementById('pdp-main-img-el');
		const mainBox = document.getElementById('pdp-main-image-box');
		const thumbItems = document.querySelectorAll('.omnify-gallery-thumb-item');
		const thumbVideo = document.getElementById('pdp-thumb-video-btn');
		const playBtn = document.getElementById('pdp-video-play-btn');

		if (!mainImg || !mainBox) return;

		function showImage(src, activeThumb) {
			const iframe = mainBox.querySelector('iframe');
			if (iframe) iframe.remove();
			mainImg.style.display = 'block';
			if (playBtn) playBtn.style.display = 'flex';

			mainImg.style.opacity = '0';
			setTimeout(() => {
				mainImg.src = src;
				mainImg.style.opacity = '1';
			}, 150);

			thumbItems.forEach(t => t.classList.remove('is-active'));
			if (activeThumb) activeThumb.classList.add('is-active');
		}

		function showVideo(videoUrl, activeThumb) {
			if (!videoUrl) return;
			mainImg.style.display = 'none';
			if (playBtn) playBtn.style.display = 'none';

			const iframe = mainBox.querySelector('iframe') || document.createElement('iframe');
			iframe.style.width = '100%';
			iframe.style.height = '100%';
			iframe.style.border = 'none';
			iframe.style.position = 'absolute';
			iframe.style.top = '0';
			iframe.style.left = '0';

			let embedUrl = videoUrl;
			const ytMatch = videoUrl.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
			if (ytMatch) {
				embedUrl = `https://www.youtube.com/embed/${ytMatch[1]}?autoplay=1&mute=1`;
			} else {
				const vimeoMatch = videoUrl.match(/vimeo\.com\/(\d+)/);
				if (vimeoMatch) {
					embedUrl = `https://player.vimeo.com/video/${vimeoMatch[1]}?autoplay=1&muted=1`;
				}
			}

			iframe.src = embedUrl;
			iframe.setAttribute('allow', 'autoplay; fullscreen');
			if (!iframe.parentNode) {
				mainBox.appendChild(iframe);
			}

			thumbItems.forEach(t => t.classList.remove('is-active'));
			if (activeThumb) activeThumb.classList.add('is-active');
		}

		thumbItems.forEach(thumb => {
			const img = thumb.querySelector('img');
			if (img) {
				thumb.addEventListener('click', () => showImage(img.getAttribute('data-large') || img.src, thumb));
			}
		});

		if (thumbVideo) {
			thumbVideo.addEventListener('click', () => {
				const url = thumbVideo.getAttribute('data-video-url');
				showVideo(url, thumbVideo);
			});
		}

		if (playBtn) {
			playBtn.addEventListener('click', () => {
				const url = playBtn.getAttribute('data-video-url');
				showVideo(url, thumbVideo);
			});
		}

		window.showImage = showImage;
	})();

	// ── 2. Variable Product Handler ──────────────────────────────────────────
	(function(){
		if (!isVariable || !variations.length) return;

		const swatchBtns = document.querySelectorAll('.omnify-swatch-btn');
		const variationSelects = document.querySelectorAll('.omnify-variation-select');

		const priceDisplay = document.getElementById('pdp-price-display');
		const selectedPriceBox = document.getElementById('pdp-var-selected-price-box');
		const varPriceDisplay = document.getElementById('pdp-var-price-display');

		const varStockBadge = document.getElementById('pdp-var-stock-badge');
		const varSkuEl      = document.getElementById('pdp-var-sku');
		const varMetaRow    = document.getElementById('pdp-var-meta-row');
		const varUnavailable = document.getElementById('pdp-var-unavailable');
		const varNote       = document.getElementById('pdp-var-note');

		const buyBtn        = document.getElementById('pdp-buy-btn');
		const cartBtn       = document.getElementById('pdp-add-cart-btn');

		function formatCurrency(val) {
			const symbol = window.omnifyStorefront?.currencySymbol || '$';
			const pos = window.omnifyStorefront?.currencyPosition || 'before';
			const formatted = parseFloat(val).toFixed(2);
			return pos === 'after' ? `${formatted} ${symbol}` : `${symbol}${formatted}`;
		}

		function getSelectedAttributes() {
			const selected = {};
			swatchBtns.forEach(btn => {
				if (btn.classList.contains('is-selected')) {
					selected[btn.getAttribute('data-attribute-name')] = btn.getAttribute('data-option-value');
				}
			});
			variationSelects.forEach(select => {
				if (select.value) {
					selected[select.name] = select.value;
				}
			});
			return selected;
		}

		function findMatchingVariation(selected) {
			return variations.find(v => {
				return Object.entries(selected).every(([name, val]) => {
					return String(v.attributes[name] || '') === String(val || '');
				});
			});
		}

		function updateUI() {
			const selected = getSelectedAttributes();
			const totalAttrs = document.querySelectorAll('.omnify-swatch-group, .omnify-variation-select-group').length;
			const isComplete = Object.keys(selected).length === totalAttrs;

			if (!isComplete) {
				if (priceDisplay) priceDisplay.style.display = 'block';
				if (selectedPriceBox) selectedPriceBox.style.display = 'none';
				if (varMetaRow) varMetaRow.style.display = 'none';
				if (varUnavailable) varUnavailable.style.display = 'none';
				if (varNote) varNote.style.display = 'none';
				if (buyBtn) buyBtn.classList.add('disabled');
				if (cartBtn) cartBtn.classList.add('disabled');
				return;
			}

			const matching = findMatchingVariation(selected);

			if (!matching) {
				if (priceDisplay) priceDisplay.style.display = 'none';
				if (selectedPriceBox) selectedPriceBox.style.display = 'none';
				if (varMetaRow) varMetaRow.style.display = 'none';
				if (varUnavailable) varUnavailable.style.display = 'block';
				if (varNote) varNote.style.display = 'none';
				if (buyBtn) buyBtn.classList.add('disabled');
				if (cartBtn) cartBtn.classList.add('disabled');
				return;
			}

			if (priceDisplay) priceDisplay.style.display = 'none';
			if (selectedPriceBox) selectedPriceBox.style.display = 'block';
			if (varUnavailable) varUnavailable.style.display = 'none';

			const finalPrice = matching.sale_price !== null ? matching.sale_price : matching.price;
			if (varPriceDisplay) {
				if (matching.sale_price !== null) {
					varPriceDisplay.innerHTML = `<ins class="omnify-pdp-price__current">${formatCurrency(matching.sale_price)}</ins><del class="omnify-pdp-price__regular">${formatCurrency(matching.price)}</del>`;
				} else {
					varPriceDisplay.innerHTML = `<span class="omnify-pdp-price__current">${formatCurrency(matching.price)}</span>`;
				}
			}

			const isOutOfStock = matching.stock_status === 'outofstock';
			const isBackorder = matching.stock_status === 'backorder';
			if (varStockBadge) {
				if (isOutOfStock) {
					varStockBadge.textContent = 'Out of Stock';
					varStockBadge.className = 'omnify-stock-badge omnify-stock-badge--out';
				} else if (isBackorder) {
					varStockBadge.textContent = 'Backorder Available';
					varStockBadge.className = 'omnify-stock-badge omnify-stock-badge--backorder';
				} else {
					varStockBadge.textContent = 'In Stock';
					varStockBadge.className = 'omnify-stock-badge omnify-stock-badge--in';
				}
			}

			if (varSkuEl) {
				varSkuEl.textContent = matching.sku || 'N/A';
			}
			if (varMetaRow) {
				varMetaRow.style.display = 'flex';
			}

			if (varNote) {
				if (matching.preorder === '1' && matching.preorder_note) {
					varNote.textContent = matching.preorder_note;
					varNote.style.display = 'block';
				} else {
					varNote.style.display = 'none';
				}
			}

			// Enable/Disable buttons based on stock
			if (isOutOfStock) {
				if (buyBtn) buyBtn.classList.add('disabled');
				if (cartBtn) cartBtn.classList.add('disabled');
			} else {
				if (buyBtn) {
					buyBtn.classList.remove('disabled');
					if (buyBtn.dataset.baseUrl) {
						const url = new URL(buyBtn.dataset.baseUrl, window.location.origin);
						url.searchParams.set('variation_id', matching.id);
						buyBtn.href = url.toString();
					}
				}
				if (cartBtn) {
					cartBtn.classList.remove('disabled');
					cartBtn.dataset.variationId = matching.id;
				}
			}

			// Swap thumbnail image if matching variation has image URL
			const varImgUrl = matching.image_url || matching.thumbnail_url;
			if (varImgUrl && typeof window.showImage === 'function') {
				window.showImage(varImgUrl, null);
			}
		}

		swatchBtns.forEach(btn => {
			btn.addEventListener('click', function(e) {
				e.preventDefault();
				const parent = btn.closest('.omnify-swatch-group');
				if (parent) {
					parent.querySelectorAll('.omnify-swatch-btn').forEach(b => b.classList.remove('is-selected'));
				}
				btn.classList.add('is-selected');
				updateUI();
			});
		});

		variationSelects.forEach(select => {
			select.addEventListener('change', updateUI);
		});

		updateUI();
	})();

	// ── 3. Quantity Selector ──────────────────────────────────
	(function(){
		const qtyInput = document.getElementById('pdp-qty-input');
		const minusBtn = document.getElementById('pdp-qty-minus');
		const plusBtn  = document.getElementById('pdp-qty-plus');
		const buyBtn   = document.getElementById('pdp-buy-btn');
		const cartBtn  = document.getElementById('pdp-add-cart-btn');
		if (!qtyInput || !minusBtn || !plusBtn) return;
		const minVal = 1;
		const maxVal = qtyInput.max ? parseInt(qtyInput.max, 10) : 9999;
		function updateQty(newVal) {
			newVal = Math.max(minVal, Math.min(maxVal, newVal));
			qtyInput.value = newVal;
			minusBtn.disabled = newVal <= minVal;
			plusBtn.disabled = newVal >= maxVal;
			if (buyBtn && buyBtn.dataset.baseUrl) {
				const url = new URL(buyBtn.dataset.baseUrl, window.location.origin);
				url.searchParams.set('quantity', newVal);
				buyBtn.href = url.toString();
			}
			if (cartBtn) {
				cartBtn.dataset.quantity = newVal;
			}
		}
		minusBtn.addEventListener('click', () => updateQty(parseInt(qtyInput.value, 10) - 1));
		plusBtn.addEventListener('click', () => updateQty(parseInt(qtyInput.value, 10) + 1));
		updateQty(1);
	})();

	// ── 4. Reviews Stars rating form selection ─────────────────────────────
	(function(){
		const starsWrap = document.getElementById('pdp-review-rating-stars');
		const ratingInput = document.getElementById('pdp-rating-input');
		if (!starsWrap || !ratingInput) return;
		const stars = starsWrap.querySelectorAll('.omnify-rating-star-btn');
		stars.forEach(star => {
			star.addEventListener('click', () => {
				const val = parseInt(star.getAttribute('data-val'), 10);
				ratingInput.value = val;
				stars.forEach((s, idx) => {
					if (idx < val) {
						s.classList.add('is-active');
					} else {
						s.classList.remove('is-active');
					}
				});
			});
		});
	})();

	// ── 5. Tabs Navigation ─────────────────────────────────────────────────
	(function(){
		const tabBtns = document.querySelectorAll('.omnify-pdp-tab-btn');
		const tabPanes = document.querySelectorAll('.omnify-pdp-tab-pane');
		tabBtns.forEach(btn => {
			btn.addEventListener('click', () => {
				const tabName = btn.getAttribute('data-tab');
				tabBtns.forEach(b => {
					b.classList.remove('active');
					b.setAttribute('aria-selected', 'false');
				});
				tabPanes.forEach(p => p.classList.remove('active'));
				
				btn.classList.add('active');
				btn.setAttribute('aria-selected', 'true');
				const pane = document.getElementById(`omnify-${tabName}-pane`);
				if (pane) pane.classList.add('active');
			});
		});
	})();

	// ── 6. Recently Viewed Cookie Tracking ────────────────────────────────
	(function(){
		if (!productId) return;
		let recentlyViewed = [];
		const cookieName = 'omnify_recently_viewed';
		const match = document.cookie.match(new RegExp('(^| )' + cookieName + '=([^;]+)'));
		if (match) {
			recentlyViewed = match[2].split(',').map(Number).filter(Boolean);
		}
		recentlyViewed = recentlyViewed.filter(id => id !== productId);
		recentlyViewed.unshift(productId);
		recentlyViewed = recentlyViewed.slice(0, 5);
		document.cookie = cookieName + '=' + recentlyViewed.join(',') + '; path=/; max-age=' + (30 * 24 * 60 * 60) + '; SameSite=Lax';
	})();

	// ── 7. Edit Review Helper ──────────────────────────────────────────────
	window.omnifyEditReview = function(id, rating, content) {
		rating = Math.max(0, Math.min(5, parseInt(rating || 0, 10)));
		content = content || '';

		const reviewsTab = document.querySelector('.omnify-pdp-tab-btn[data-tab="reviews"]');
		if (reviewsTab) reviewsTab.click();

		const form = document.querySelector('.omnify-review-form form');
		if (!form) return;

		const idInput = document.getElementById('pdp-review-id-input');
		if (idInput) idInput.value = id;

		const textarea = form.querySelector('textarea[name="content"]');
		if (textarea) textarea.value = content;

		const ratingInput = document.getElementById('pdp-rating-input');
		if (ratingInput) ratingInput.value = rating;

		const starBtns = document.querySelectorAll('#pdp-review-rating-stars .omnify-rating-star-btn');
		starBtns.forEach(function(btn, idx) {
			if (idx < rating) {
				btn.classList.add('is-active');
			} else {
				btn.classList.remove('is-active');
			}
		});

		const formTitle = document.getElementById('omnify-review-form-title');
		if (formTitle && formTitle.dataset.labelEdit) {
			formTitle.textContent = formTitle.dataset.labelEdit;
		} else if (formTitle) {
			formTitle.textContent = "Edit Your Review";
		}

		const submitBtn = form.querySelector('.omnify-review-submit');
		if (submitBtn) {
			if (submitBtn.dataset.labelUpdate) {
				submitBtn.textContent = submitBtn.dataset.labelUpdate;
			} else {
				submitBtn.textContent = "Update Review";
			}

			if (!document.getElementById('pdp-review-cancel-btn')) {
				const cancelBtn = document.createElement('button');
				cancelBtn.type = 'button';
				cancelBtn.id = 'pdp-review-cancel-btn';
				cancelBtn.className = 'omnify-review-cancel';
				cancelBtn.textContent = cancelBtn.dataset.labelCancel || "Cancel";
				cancelBtn.onclick = function() {
					if (idInput) idInput.value = '';
					if (textarea) textarea.value = '';
					if (ratingInput) ratingInput.value = '0';
					starBtns.forEach(function(btn) {
						btn.classList.remove('is-active');
					});
					const uploadInput = document.getElementById('omnify-review-upload-input');
					if (uploadInput) uploadInput.value = '';
					const fileList = document.getElementById('omnify-review-file-list');
					if (fileList) fileList.innerHTML = '';
					if (formTitle && formTitle.dataset.labelAdd) {
						formTitle.textContent = formTitle.dataset.labelAdd;
					} else if (formTitle) {
						formTitle.textContent = "Add a Review";
					}
					if (submitBtn && submitBtn.dataset.labelSubmit) {
						submitBtn.textContent = submitBtn.dataset.labelSubmit;
					} else if (submitBtn) {
						submitBtn.textContent = "Submit Review";
					}
					cancelBtn.remove();
				};
				submitBtn.parentNode.appendChild(cancelBtn);
			}
		}

		form.scrollIntoView({ behavior: 'smooth', block: 'center' });
		if (textarea) {
			window.setTimeout(function() { textarea.focus(); }, 350);
		}
	};

	// Handle dynamic review images selection change preview list
	const uploadInput = document.getElementById('omnify-review-upload-input');
	const fileList = document.getElementById('omnify-review-file-list');
	if (uploadInput && fileList) {
		uploadInput.addEventListener('change', function() {
			fileList.innerHTML = '';
			Array.from(this.files || []).forEach(file => {
				const item = document.createElement('div');
				item.className = 'omnify-upload-file-item';
				item.innerHTML = `<svg width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><rect x='3' y='3' width='18' height='18' rx='2'/><circle cx='8.5' cy='8.5' r='1.5'/><polyline points='21 15 16 10 5 21'/></svg><span>${file.name}</span>`;
				fileList.appendChild(item);
			});
		});
	}

	// Handle review cancel button click handler (for reviews pre-rendered by PHP)
	const initialCancelBtn = document.getElementById('pdp-review-cancel-btn');
	if (initialCancelBtn) {
		initialCancelBtn.addEventListener('click', function() {
			const idInput = document.getElementById('pdp-review-id-input');
			const textarea = document.getElementById('omnify-review-content');
			const ratingInput = document.getElementById('pdp-rating-input');
			const starBtns = document.querySelectorAll('#pdp-review-rating-stars .omnify-rating-star-btn');
			const formTitle = document.getElementById('omnify-review-form-title');
			const submitBtn = document.querySelector('.omnify-review-submit');

			if (idInput) idInput.value = '';
			if (textarea) textarea.value = '';
			if (ratingInput) ratingInput.value = '0';
			starBtns.forEach(function(btn) {
				btn.classList.remove('is-active');
			});
			if (uploadInput) uploadInput.value = '';
			if (fileList) fileList.innerHTML = '';
			if (formTitle && formTitle.dataset.labelAdd) {
				formTitle.textContent = formTitle.dataset.labelAdd;
			}
			if (submitBtn && submitBtn.dataset.labelSubmit) {
				submitBtn.textContent = submitBtn.dataset.labelSubmit;
			}
			initialCancelBtn.remove();
		});
	}

	document.addEventListener('click', function(event) {
		const editButton = event.target.closest('.omnify-pdp-review-edit-btn');
		if (!editButton) return;
		event.preventDefault();
		window.omnifyEditReview(
			editButton.dataset.reviewId || '',
			editButton.dataset.reviewRating || '0',
			editButton.dataset.reviewContent || ''
		);
	});

	// ── 8. Related & Recently Viewed Scroll Click Handlers ─────────────────
	(function() {
		function initScroll() {
			const relPrev = document.getElementById('omnify-related-prev');
			const relNext = document.getElementById('omnify-related-next');
			const relCarousel = document.getElementById('omnify-related-carousel');
			if (relPrev && relNext && relCarousel) {
				relPrev.addEventListener('click', function(e) {
					e.preventDefault();
					const maxScroll = relCarousel.scrollWidth - relCarousel.clientWidth;
					if (maxScroll <= 10) {
						if (!omnifyInfiniteScrollEnabled) return;
						const last = relCarousel.lastElementChild;
						if (last) {
							last.style.transition = 'opacity 0.2s, transform 0.2s';
							last.style.opacity = '0';
							last.style.transform = 'translateX(20px)';
							setTimeout(() => {
								relCarousel.insertBefore(last, relCarousel.firstElementChild);
								last.style.opacity = '1';
								last.style.transform = 'none';
							}, 200);
						}
					} else {
						relCarousel.scrollBy({ left: -260, behavior: 'smooth' });
					}
				});
				relNext.addEventListener('click', function(e) {
					e.preventDefault();
					const maxScroll = relCarousel.scrollWidth - relCarousel.clientWidth;
					if (relCarousel.scrollLeft >= maxScroll - 10) {
						if (!omnifyInfiniteScrollEnabled) return;
						const first = relCarousel.firstElementChild;
						if (first) {
							first.style.transition = 'opacity 0.2s, transform 0.2s';
							first.style.opacity = '0';
							first.style.transform = 'translateX(-20px)';
							setTimeout(() => {
								relCarousel.appendChild(first);
								first.style.opacity = '1';
								first.style.transform = 'none';
							}, 200);
						}
					} else {
						relCarousel.scrollBy({ left: 260, behavior: 'smooth' });
					}
				});
			}

			const recPrev = document.getElementById('omnify-recently-prev');
			const recNext = document.getElementById('omnify-recently-next');
			const recCarousel = document.getElementById('omnify-recently-carousel');
			if (recPrev && recNext && recCarousel) {
				recPrev.addEventListener('click', function(e) {
					e.preventDefault();
					const maxScroll = recCarousel.scrollWidth - recCarousel.clientWidth;
					if (maxScroll <= 10) {
						if (!omnifyInfiniteScrollEnabled) return;
						const last = recCarousel.lastElementChild;
						if (last) {
							last.style.transition = 'opacity 0.2s, transform 0.2s';
							last.style.opacity = '0';
							last.style.transform = 'translateX(20px)';
							setTimeout(() => {
								recCarousel.insertBefore(last, recCarousel.firstElementChild);
								last.style.opacity = '1';
								last.style.transform = 'none';
							}, 200);
						}
					} else {
						recCarousel.scrollBy({ left: -260, behavior: 'smooth' });
					}
				});
				recNext.addEventListener('click', function(e) {
					e.preventDefault();
					const maxScroll = recCarousel.scrollWidth - recCarousel.clientWidth;
					if (recCarousel.scrollLeft >= maxScroll - 10) {
						if (!omnifyInfiniteScrollEnabled) return;
						const first = recCarousel.firstElementChild;
						if (first) {
							first.style.transition = 'opacity 0.2s, transform 0.2s';
							first.style.opacity = '0';
							first.style.transform = 'translateX(-20px)';
							setTimeout(() => {
								recCarousel.appendChild(first);
								first.style.opacity = '1';
								first.style.transform = 'none';
							}, 200);
						}
					} else {
						recCarousel.scrollBy({ left: 260, behavior: 'smooth' });
					}
				});
			}
		}
		initScroll();
	})();
}

/**
 * Customer portal address page management.
 */
function initPortalAddressesPage() {
	const portalWrapper = document.querySelector('.omnify-portal-wrapper');
	if (!portalWrapper) return;

	// Print receipt
	document.querySelectorAll('.omnify-print-receipt-btn').forEach(btn => {
		btn.addEventListener('click', () => {
			window.print();
		});
	});

	// Address form panel
	const wrapper = document.getElementById('omnify-address-form-wrapper');
	const form = document.getElementById('omnify-address-form');
	const action = document.getElementById('omnify-address-action-input');
	const idInput = document.getElementById('omnify-address-id-input');
	const title = document.getElementById('omnify-address-form-title');
	const addButton = document.getElementById('omnify-add-new-address-btn');
	const cancelButton = document.getElementById('omnify-cancel-address-btn');
	if (!wrapper || !form) {
		return;
	}

	function showForm(address) {
		form.reset();
		action.value = address ? 'omnify_edit_address' : 'omnify_add_address';
		idInput.value = address && address.id ? address.id : '';
		if (title) {
			title.textContent = address ? (title.dataset.labelEdit || 'Edit shipping address') : (title.dataset.labelAdd || 'Add shipping address');
		}
		if (address) {
			Object.keys(address).forEach(function (key) {
				const field = form.querySelector('[name="shipping_' + key + '"]');
				if (field) {
					field.value = address[key] || '';
				}
			});
			const defaultField = form.querySelector('[name="is_default"]');
			if (defaultField) {
				defaultField.checked = Boolean(address.is_default);
			}
		}
		wrapper.style.display = 'block';
		wrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
	}

	addButton?.addEventListener('click', function () {
		showForm(null);
	});
	cancelButton?.addEventListener('click', function () {
		wrapper.style.display = 'none';
		form.reset();
	});
	document.querySelectorAll('.omnify-edit-address-btn').forEach(function (button) {
		button.addEventListener('click', function () {
			try {
				showForm(JSON.parse(button.getAttribute('data-address') || '{}'));
			} catch (error) {
				showForm(null);
			}
		});
	});

	// Toggle shipping address section based on "shipping_same_as_billing" checkbox
	const sameCheckbox = document.querySelector('input[name="shipping_same_as_billing"]');
	const shippingSec = document.getElementById('omnify-portal-shipping-section');
	if (sameCheckbox && shippingSec) {
		const toggleShipping = () => {
			if (sameCheckbox.checked) {
				shippingSec.style.display = 'none';
			} else {
				shippingSec.style.display = 'block';
			}
		};
		sameCheckbox.addEventListener('change', toggleShipping);
		toggleShipping();
	}
}

// Hook it up on Dom load
document.addEventListener('DOMContentLoaded', () => {
	initProductSinglePage();
	initPortalAddressesPage();
});
