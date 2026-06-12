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
        const section = document.getElementById('curated-combo-options');
        const comboId = trigger.dataset.comboId;
        const target = comboId
            ? document.querySelector(`[data-combo-card="${CSS.escape(comboId)}"]`)
            : null;
        const pulseTarget = target || section;

        if (!section || !pulseTarget) {
            return;
        }

        event.preventDefault();
        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        pulseTarget.classList.remove('combo-suggestions--pulse', 'combo-card--shake');

        window.setTimeout(() => {
            window.requestAnimationFrame(() => {
                pulseTarget.classList.add(target ? 'combo-card--shake' : 'combo-suggestions--pulse');
            });
        }, 280);
    });
});

document.addEventListener('contextmenu', (event) => {
    if (event.target?.closest?.('[data-protected-image]')) {
        event.preventDefault();
    }
});

document.addEventListener('dragstart', (event) => {
    if (event.target?.closest?.('[data-protected-image]')) {
        event.preventDefault();
    }
});

function solveLinearSystem(matrix) {
    const size = matrix.length;

    for (let column = 0; column < size; column += 1) {
        let pivot = column;

        for (let row = column + 1; row < size; row += 1) {
            if (Math.abs(matrix[row][column]) > Math.abs(matrix[pivot][column])) {
                pivot = row;
            }
        }

        if (Math.abs(matrix[pivot][column]) < 1e-10) {
            return null;
        }

        if (pivot !== column) {
            [matrix[column], matrix[pivot]] = [matrix[pivot], matrix[column]];
        }

        const divisor = matrix[column][column];

        for (let item = column; item <= size; item += 1) {
            matrix[column][item] /= divisor;
        }

        for (let row = 0; row < size; row += 1) {
            if (row === column) {
                continue;
            }

            const factor = matrix[row][column];

            for (let item = column; item <= size; item += 1) {
                matrix[row][item] -= factor * matrix[column][item];
            }
        }
    }

    return matrix.map((row) => row[size]);
}

function perspectiveMatrixForRect(width, height, points) {
    const source = [
        [0, 0],
        [width, 0],
        [width, height],
        [0, height],
    ];
    const rows = [];

    source.forEach(([x, y], index) => {
        const [targetX, targetY] = points[index];

        rows.push([x, y, 1, 0, 0, 0, -targetX * x, -targetX * y, targetX]);
        rows.push([0, 0, 0, x, y, 1, -targetY * x, -targetY * y, targetY]);
    });

    const solution = solveLinearSystem(rows);

    if (!solution) {
        return null;
    }

    const [a, b, c, d, e, f, g, h] = solution;

    return `matrix3d(${[
        a, d, 0, g,
        b, e, 0, h,
        0, 0, 1, 0,
        c, f, 0, 1,
    ].map((value) => Number(value).toFixed(10)).join(',')})`;
}

function initializeCardMockups() {
    const stages = document.querySelectorAll('[data-card-mockup-stage]');

    stages.forEach((stage) => {
        if (stage.dataset.cardMockupReady === '1') {
            return;
        }

        stage.dataset.cardMockupReady = '1';

        const template = stage.querySelector('[data-card-mockup-template]');

        if (!template) {
            return;
        }

        let map = null;

        try {
            map = JSON.parse(stage.dataset.map || '{}');
        } catch {
            map = null;
        }

        const update = () => {
            const width = stage.clientWidth;
            const height = stage.clientHeight;
            const imageWidth = Number(stage.dataset.imageWidth || 0) || 1600;
            const imageHeight = Number(stage.dataset.imageHeight || 0) || 1600;
            const templateRatio = template.naturalWidth && template.naturalHeight
                ? template.naturalHeight / template.naturalWidth
                : 13 / 9;
            const sourceWidth = Math.max(1, width);
            const sourceHeight = Math.max(1, sourceWidth * templateRatio);

            if (!width || !height || !map) {
                template.classList.remove('is-ready');

                return;
            }

            const imageRatio = imageWidth / Math.max(1, imageHeight);
            const boxRatio = width / Math.max(1, height);
            const rendered = imageRatio > boxRatio
                ? {
                    width: height * imageRatio,
                    height,
                    left: (width - (height * imageRatio)) / 2,
                    top: 0,
                }
                : {
                    width,
                    height: width / imageRatio,
                    left: 0,
                    top: (height - (width / imageRatio)) / 2,
                };
            const points = [
                [map.top_left_x, map.top_left_y],
                [map.top_right_x, map.top_right_y],
                [map.bottom_right_x, map.bottom_right_y],
                [map.bottom_left_x, map.bottom_left_y],
            ].map(([x, y]) => [
                rendered.left + (Number(x) || 0) * rendered.width,
                rendered.top + (Number(y) || 0) * rendered.height,
            ]);
            const matrix = perspectiveMatrixForRect(sourceWidth, sourceHeight, points);

            if (!matrix) {
                template.classList.remove('is-ready');

                return;
            }

            template.style.width = `${sourceWidth}px`;
            template.style.height = `${sourceHeight}px`;
            template.style.transform = matrix;
            template.classList.add('is-ready');
        };

        update();

        if ('ResizeObserver' in window) {
            const observer = new ResizeObserver(update);
            observer.observe(stage);
        } else {
            window.addEventListener('resize', update, { passive: true });
        }

        stage.querySelectorAll('img').forEach((image) => {
            if (!image.complete) {
                image.addEventListener('load', update, { once: true });
            }
        });
    });
}

initializeCardMockups();
window.addEventListener('load', initializeCardMockups, { once: true });

registerNikahPreview(Alpine);
Alpine.start();

if (document.getElementById('nikah-product-form-root')) {
    import('./admin/nikah-product-form').then(({ mountNikahProductForm }) => {
        mountNikahProductForm();
    });
}
