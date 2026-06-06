import Alpine from 'alpinejs';
import { registerNikahPreview } from './nikah-preview';
import './pdp-zoom';

window.Alpine = Alpine;

const cartStorageKey = 'azraq.cart.items.v1';

function countCartItems(items) {
    if (!Array.isArray(items)) {
        return 0;
    }

    return items.reduce((total, item) => total + Math.max(0, Number.parseInt(item?.quantity ?? 0, 10) || 0), 0);
}

function updateCartBadges(count) {
    document.querySelectorAll('.js-cart-count').forEach((badge) => {
        const safeCount = Math.max(0, count);
        badge.textContent = safeCount > 99 ? '99+' : String(safeCount);
        badge.dataset.cartCount = String(safeCount);
        badge.classList.toggle('is-empty', safeCount < 1);
    });
}

function readStoredCartItems() {
    try {
        const parsed = JSON.parse(window.localStorage.getItem(cartStorageKey) || '[]');

        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
}

function writeStoredCartItems(items) {
    try {
        if (!Array.isArray(items) || items.length === 0) {
            window.localStorage.removeItem(cartStorageKey);
            updateCartBadges(0);

            return;
        }

        window.localStorage.setItem(cartStorageKey, JSON.stringify(items));
        updateCartBadges(countCartItems(items));
    } catch {
        // Storage can be unavailable in private/restricted browser modes.
    }
}

function syncBrowserCartCache() {
    const stateNode = document.getElementById('azraq-cart-state');

    if (!stateNode || !window.localStorage) {
        return;
    }

    let state = {};

    try {
        state = JSON.parse(stateNode.textContent || '{}');
    } catch {
        state = {};
    }

    const serverItems = Array.isArray(state.items) ? state.items : [];
    const serverCount = countCartItems(serverItems);

    if (state.clearCache) {
        writeStoredCartItems([]);

        return;
    }

    if (serverCount > 0) {
        writeStoredCartItems(serverItems);

        return;
    }

    const storedItems = readStoredCartItems();
    const storedCount = countCartItems(storedItems);

    if (storedCount < 1) {
        updateCartBadges(0);

        return;
    }

    updateCartBadges(storedCount);

    const restoreKey = `azraq.cart.restore.${window.location.pathname}`;

    if (!state.restoreUrl || !state.csrfToken || window.sessionStorage.getItem(restoreKey)) {
        return;
    }

    window.sessionStorage.setItem(restoreKey, '1');

    fetch(state.restoreUrl, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': state.csrfToken,
        },
        body: JSON.stringify({ items: storedItems }),
    })
        .then((response) => response.ok ? response.json() : null)
        .then((payload) => {
            if (!payload) {
                return;
            }

            writeStoredCartItems(payload.items || []);

            if (window.location.pathname === '/cart' || window.location.pathname === '/checkout') {
                window.location.reload();
            }
        })
        .catch(() => {});
}

syncBrowserCartCache();

document.querySelectorAll('[data-combo-jump]').forEach((trigger) => {
    trigger.addEventListener('click', (event) => {
        const target = document.getElementById('curated-combo-options');

        if (!target) {
            return;
        }

        event.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        target.classList.remove('combo-suggestions--pulse');

        window.requestAnimationFrame(() => {
            target.classList.add('combo-suggestions--pulse');
        });
    });
});

registerNikahPreview(Alpine);
Alpine.start();

if (document.getElementById('nikah-product-form-root')) {
    import('./admin/nikah-product-form').then(({ mountNikahProductForm }) => {
        mountNikahProductForm();
    });
}
