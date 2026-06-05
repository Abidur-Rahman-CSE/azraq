let perspectiveLoader = null;

const imageCache = new Map();
const clamp = (value, min, max) => Math.min(max, Math.max(min, value));
const cloneData = (value) => JSON.parse(JSON.stringify(value ?? {}));

function createCanvas(width, height) {
    const canvas = document.createElement('canvas');
    canvas.width = Math.max(1, Math.round(width));
    canvas.height = Math.max(1, Math.round(height));

    return canvas;
}

async function loadPerspective() {
    if (!perspectiveLoader) {
        perspectiveLoader = import('perspectivejs').then((module) => module.default ?? module);
    }

    return perspectiveLoader;
}

async function loadImage(url) {
    if (!url) {
        return null;
    }

    if (imageCache.has(url)) {
        return imageCache.get(url);
    }

    const promise = new Promise((resolve, reject) => {
        const image = new Image();
        image.decoding = 'async';
        image.onload = () => resolve(image);
        image.onerror = () => resolve(null);
        image.src = url;
    });

    imageCache.set(url, promise);

    return promise;
}

function preloadMockupAssets(scene) {
    if (!scene) {
        return Promise.resolve();
    }

    return Promise.all([
        loadImage(scene.base_image_url || scene.image_url),
        loadImage(scene.overlay_image_url || scene.overlay_url),
        loadImage(scene.mask_image_url || scene.mask_url),
    ]).then(() => undefined);
}

function whenIdle(callback) {
    if ('requestIdleCallback' in window) {
        window.requestIdleCallback(callback, { timeout: 1200 });
        return;
    }

    window.setTimeout(callback, 80);
}

function nextFrame() {
    return new Promise((resolve) => window.requestAnimationFrame(resolve));
}

function getContainGeometry(image, width, height) {
    const sourceWidth = image.naturalWidth || image.width;
    const sourceHeight = image.naturalHeight || image.height;
    const sourceRatio = sourceWidth / sourceHeight;
    const targetRatio = width / height;

    let drawWidth = width;
    let drawHeight = height;
    let offsetX = 0;
    let offsetY = 0;

    if (sourceRatio > targetRatio) {
        drawHeight = width / sourceRatio;
        offsetY = (height - drawHeight) / 2;
    } else {
        drawWidth = height * sourceRatio;
        offsetX = (width - drawWidth) / 2;
    }

    return { drawWidth, drawHeight, offsetX, offsetY };
}

function drawContainedImage(ctx, image, width, height) {
    const { drawWidth, drawHeight, offsetX, offsetY } = getContainGeometry(image, width, height);
    ctx.drawImage(image, offsetX, offsetY, drawWidth, drawHeight);

    return { drawWidth, drawHeight, offsetX, offsetY };
}

function drawBlurredCoverFill(ctx, image, width, height) {
    ctx.save();
    ctx.filter = 'blur(24px)';
    ctx.globalAlpha = 0.62;
    drawCoverImage(ctx, image, width, height);
    ctx.restore();

    ctx.save();
    ctx.fillStyle = 'rgba(255, 250, 242, 0.28)';
    ctx.fillRect(0, 0, width, height);
    ctx.restore();
}

function drawCoverImage(ctx, image, width, height) {
    const sourceWidth = image.naturalWidth || image.width;
    const sourceHeight = image.naturalHeight || image.height;
    const sourceRatio = sourceWidth / Math.max(1, sourceHeight);
    const targetRatio = width / Math.max(1, height);

    let drawWidth = width;
    let drawHeight = height;
    let offsetX = 0;
    let offsetY = 0;

    if (sourceRatio > targetRatio) {
        drawHeight = height;
        drawWidth = height * sourceRatio;
        offsetX = (width - drawWidth) / 2;
    } else {
        drawWidth = width;
        drawHeight = width / sourceRatio;
        offsetY = (height - drawHeight) / 2;
    }

    ctx.drawImage(image, offsetX, offsetY, drawWidth, drawHeight);

    return { drawWidth, drawHeight, offsetX, offsetY };
}

async function canvasToPerspectiveSource(canvas) {
    if (window.createImageBitmap) {
        return window.createImageBitmap(canvas);
    }

    return new Promise((resolve, reject) => {
        const image = new Image();
        image.decoding = 'async';
        image.onload = () => resolve(image);
        image.onerror = reject;
        image.src = canvas.toDataURL('image/png');
    });
}

function measureTextWithSpacing(ctx, text, letterSpacing) {
    if (!text) {
        return 0;
    }

    const spacing = Number(letterSpacing || 0);

    return ctx.measureText(text).width + Math.max(0, text.length - 1) * spacing;
}

function fontDeclaration(fontStyle, fontSize) {
    return `${fontStyle.fontStyle || 'normal'} ${fontStyle.fontWeight || '600'} ${fontSize}px ${fontStyle.fontFamily}`;
}

async function ensureCanvasFont(fontStyle, text, fontSize) {
    if (!document.fonts?.load) {
        return;
    }

    try {
        await document.fonts.load(fontDeclaration(fontStyle, fontSize), text || 'Preview');
        await document.fonts.ready;
    } catch (error) {
        // Canvas can still render with a fallback font if the browser rejects a custom family string.
    }
}

function applyTextTransform(text, textTransform) {
    const value = `${text ?? ''}`;

    switch (textTransform) {
        case 'uppercase':
            return value.toUpperCase();
        case 'lowercase':
            return value.toLowerCase();
        case 'capitalize':
            return value.replace(/\b\w/g, (character) => character.toUpperCase());
        default:
            return value;
    }
}

function splitLongWord(ctx, word, maxWidth, letterSpacing) {
    const characters = [...`${word ?? ''}`];
    const chunks = [];
    let current = '';

    characters.forEach((character) => {
        const next = `${current}${character}`;

        if (current && measureTextWithSpacing(ctx, next, letterSpacing) > maxWidth) {
            chunks.push(current);
            current = character;
            return;
        }

        current = next;
    });

    if (current) {
        chunks.push(current);
    }

    return chunks;
}

function wrapTextLines(ctx, text, maxWidth, letterSpacing, allowMultiline = true) {
    const source = `${text ?? ''}`;

    if (!allowMultiline) {
        return [source.replace(/\r?\n/g, ' ').replace(/\s+/g, ' ').trim()];
    }

    const paragraphs = source.split(/\r?\n/);
    const lines = [];

    paragraphs.forEach((paragraph) => {
        const words = paragraph.trim() === '' ? [''] : paragraph.split(/\s+/);
        let currentLine = '';

        words.forEach((word) => {
            if (measureTextWithSpacing(ctx, word, letterSpacing) > maxWidth) {
                if (currentLine) {
                    lines.push(currentLine);
                    currentLine = '';
                }

                const chunks = splitLongWord(ctx, word, maxWidth, letterSpacing);
                lines.push(...chunks.slice(0, -1));
                currentLine = chunks[chunks.length - 1] || '';

                return;
            }

            const nextLine = currentLine ? `${currentLine} ${word}` : word;

            if (measureTextWithSpacing(ctx, nextLine, letterSpacing) <= maxWidth || !currentLine) {
                currentLine = nextLine;
                return;
            }

            lines.push(currentLine);
            currentLine = word;
        });

        lines.push(currentLine);
    });

    return lines.length ? lines : [''];
}

function fitTextBlock(ctx, text, layer, fontStyle) {
    const width = Math.max(18, Math.max(24, layer.widthPx) - Number(layer.safeInsetX || 12));
    const height = Math.max(14, Math.max(18, layer.heightPx) - Number(layer.safeInsetY || 10));
    const minSize = Math.max(8, Number(layer.font_size_min || 12));
    const maxSize = Math.max(minSize, Number(layer.font_size_max || 24));
    const lineHeight = Math.max(1, Number(fontStyle.lineHeight || layer.line_height || 1.2));
    const letterSpacing = Number(fontStyle.letterSpacing ?? layer.letter_spacing ?? 0);
    const allowMultiline = Boolean(fontStyle.allowMultiline);
    const maxLines = Math.max(1, Number(fontStyle.maxLines || 1));
    const canWrap = allowMultiline && fontStyle.overflowBehavior !== 'shrink_only';
    const drawCanWrap = fontStyle.fitMode === 'admin_width_wrap' ? allowMultiline : canWrap;
    const resolveDrawLines = (fontSize, lines) => {
        if (fontStyle.fitMode !== 'admin_width_wrap') {
            return lines;
        }

        ctx.font = fontDeclaration(fontStyle, fontSize);

        return wrapTextLines(ctx, text, width, letterSpacing, drawCanWrap);
    };

    for (let fontSize = maxSize; fontSize >= minSize; fontSize -= 1) {
        ctx.font = fontDeclaration(fontStyle, fontSize);
        const lines = wrapTextLines(ctx, text, width, letterSpacing, canWrap);
        const tallestLine = fontSize * lineHeight * lines.length;
        const widestLine = Math.max(...lines.map((line) => measureTextWithSpacing(ctx, line, letterSpacing)));

        if (lines.length <= maxLines && tallestLine <= height && widestLine <= width) {
            return { fontSize, lines: resolveDrawLines(fontSize, lines) };
        }
    }

    ctx.font = fontDeclaration(fontStyle, minSize);

    return {
        fontSize: minSize,
        lines: resolveDrawLines(minSize, wrapTextLines(ctx, text, width, letterSpacing, canWrap).slice(0, maxLines)),
    };
}

function drawLineWithSpacing(ctx, text, x, y, align, letterSpacing) {
    const spacing = Number(letterSpacing || 0);

    if (!text || spacing === 0) {
        ctx.fillText(text, x, y);
        return;
    }

    const characters = [...text];
    const totalWidth = characters.reduce((sum, character, index) => {
        const charWidth = ctx.measureText(character).width;
        const gap = index === characters.length - 1 ? 0 : spacing;

        return sum + charWidth + gap;
    }, 0);

    let drawX = x;

    if (align === 'center') {
        drawX -= totalWidth / 2;
    } else if (align === 'right') {
        drawX -= totalWidth;
    }

    characters.forEach((character, index) => {
        ctx.fillText(character, drawX, y);
        drawX += ctx.measureText(character).width;

        if (index !== characters.length - 1) {
            drawX += spacing;
        }
    });
}

function buildFieldLayer(field, width, height) {
    const fontScale = Number(field.font_scale || 1);
    const safeInsetX = 18 * fontScale;
    const safeInsetY = 10 * fontScale;

    return {
        ...field,
        widthPx: (width * Number(field.width || 50)) / 100,
        heightPx: (height * Number(field.height || 8)) / 100,
        font_size_min: Math.max(8, Number(field.font_size_min || 12) * fontScale),
        font_size_max: Math.max(8, Number(field.font_size_max || 24) * fontScale),
        letter_spacing: Number(field.letter_spacing || 0) * fontScale,
        safeInsetX,
        safeInsetY,
    };
}

function normalizeOptionKey(value) {
    return `${value ?? ''}`
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '_')
        .replace(/^_+|_+$/g, '');
}

function normalizeOptionValue(value) {
    return `${value ?? ''}`
        .trim()
        .toLowerCase()
        .replace(/\s+/g, ' ');
}

function mediaLinksForOption(links, key, value) {
    const normalizedKey = normalizeOptionKey(key);
    const rawValue = `${value ?? ''}`.trim();
    const direct = links?.[`${normalizedKey}:${rawValue}`];

    if (Array.isArray(direct) && direct.length) {
        return direct;
    }

    const normalizedValue = normalizeOptionValue(value);
    const match = Object.entries(links ?? {}).find(([linkKey, ids]) => {
        if (!Array.isArray(ids) || !ids.length) {
            return false;
        }

        const [candidateKey, ...candidateValueParts] = `${linkKey}`.split(':');

        return normalizeOptionKey(candidateKey) === normalizedKey
            && normalizeOptionValue(candidateValueParts.join(':')) === normalizedValue;
    });

    return match?.[1] ?? [];
}

function firstMappedMediaIndex(items, mappedMediaIds) {
    return mappedMediaIds
        .map((mediaId) => (items ?? []).findIndex((item) => `${item.id}` === `${mediaId}`))
        .find((index) => index >= 0);
}

function humanizeOptionKey(value) {
    return `${value ?? ''}`
        .replace(/[_-]+/g, ' ')
        .replace(/\b\w/g, (character) => character.toUpperCase())
        .trim();
}

function normalizeVariantOptionValues(rawOptionValues) {
    if (Array.isArray(rawOptionValues)) {
        return rawOptionValues.reduce((options, entry, index) => {
            if (typeof entry !== 'string') {
                return options;
            }

            const [rawKey, ...rawValueParts] = entry.split(':');

            if (!rawValueParts.length) {
                return options;
            }

            const key = normalizeOptionKey(rawKey || `option_${index + 1}`);
            const value = rawValueParts.join(':').trim();

            if (key && value) {
                options[key] = value;
            }

            return options;
        }, {});
    }

    if (rawOptionValues && typeof rawOptionValues === 'object') {
        return Object.entries(rawOptionValues).reduce((options, [key, value]) => {
            const normalizedKey = normalizeOptionKey(key);
            const normalizedValue = `${value ?? ''}`.trim();

            if (normalizedKey && normalizedValue) {
                options[normalizedKey] = normalizedValue;
            }

            return options;
        }, {});
    }

    if (typeof rawOptionValues === 'string' && rawOptionValues.trim()) {
        return normalizeVariantOptionValues(rawOptionValues.split(',').map((entry) => entry.trim()).filter(Boolean));
    }

    return {};
}

function inferVariantGroupType(key) {
    return /frame_type|material|color/i.test(`${key ?? ''}`) ? 'swatch' : 'pill';
}

function sanitizeVariantGroupValue(rawValue) {
    const label = `${rawValue?.label ?? rawValue?.value ?? rawValue ?? ''}`.trim();
    const value = `${rawValue?.value ?? rawValue?.label ?? rawValue ?? ''}`.trim();

    if (!label || !value) {
        return null;
    }

    return {
        label,
        value,
        variant_id: rawValue?.variant_id ?? null,
        available: rawValue?.available !== false,
        tooltip: rawValue?.tooltip ?? null,
        swatch: rawValue?.swatch ?? label,
    };
}

function buildVariantGroups(variants, providedGroups = []) {
    const groups = new Map();

    providedGroups.forEach((rawGroup, index) => {
        const key = normalizeOptionKey(rawGroup?.key || rawGroup?.name || `group_${index + 1}`);
        const name = `${rawGroup?.name ?? humanizeOptionKey(key)}`.trim() || `Option ${index + 1}`;
        const values = (rawGroup?.values ?? [])
            .map((value) => sanitizeVariantGroupValue(value))
            .filter(Boolean);

        groups.set(key, {
            key,
            name,
            type: rawGroup?.type || inferVariantGroupType(key),
            values,
        });
    });

    variants.forEach((variant) => {
        Object.entries(variant.option_values ?? {}).forEach(([rawKey, rawValue]) => {
            const key = normalizeOptionKey(rawKey);
            const value = `${rawValue ?? ''}`.trim();

            if (!key || !value) {
                return;
            }

            if (!groups.has(key)) {
                groups.set(key, {
                    key,
                    name: humanizeOptionKey(key),
                    type: inferVariantGroupType(key),
                    values: [],
                });
            }

            const group = groups.get(key);

            if (!group.values.some((groupValue) => `${groupValue.value}` === value)) {
                group.values.push({
                    label: value,
                    value,
                    variant_id: null,
                    available: true,
                    tooltip: null,
                    swatch: value,
                });
            }
        });
    });

    return Array.from(groups.values()).filter((group) => (group.values ?? []).length > 0);
}

function normalizeVariantRecord(variant) {
    return {
        ...variant,
        option_values: normalizeVariantOptionValues(variant?.option_values),
        available: variant?.available !== false,
    };
}

function exportCanvasSize(template, fallbackImage) {
    const ratioWidth = Math.max(1, Number(template.export_ratio_width || 9));
    const ratioHeight = Math.max(1, Number(template.export_ratio_height || 13));
    const fallbackWidth = fallbackImage?.naturalWidth || fallbackImage?.width || 2400;
    const targetWidth = Math.max(2400, Math.min(3200, Number(template.export_width || fallbackWidth || 2400)));

    return {
        width: Math.round(targetWidth),
        height: Math.round(targetWidth * (ratioHeight / ratioWidth)),
    };
}

function resolvePoint(point, scene, drawWidth, drawHeight, offsetX, offsetY) {
    const x = Number(point?.x || 0);
    const y = Number(point?.y || 0);
    const normalized = x <= 1 && y <= 1;

    return [
        offsetX + ((normalized ? x : x / Math.max(1, Number(scene.image_width || 1))) * drawWidth),
        offsetY + ((normalized ? y : y / Math.max(1, Number(scene.image_height || 1))) * drawHeight),
    ];
}

function resolveNaturalPoint(point, scene, width, height) {
    const x = Number(point?.x || 0);
    const y = Number(point?.y || 0);
    const normalized = x <= 1 && y <= 1;

    return [
        (normalized ? x : x / Math.max(1, Number(scene.image_width || width || 1))) * width,
        (normalized ? y : y / Math.max(1, Number(scene.image_height || height || 1))) * height,
    ];
}

function getAdminMapPoints(scene, geometry) {
    const map = scene.map;

    if (map) {
        return [
            [geometry.offsetX + (Number(map.top_left_x || 0) * geometry.drawWidth), geometry.offsetY + (Number(map.top_left_y || 0) * geometry.drawHeight)],
            [geometry.offsetX + (Number(map.top_right_x || 0) * geometry.drawWidth), geometry.offsetY + (Number(map.top_right_y || 0) * geometry.drawHeight)],
            [geometry.offsetX + (Number(map.bottom_right_x || 0) * geometry.drawWidth), geometry.offsetY + (Number(map.bottom_right_y || 0) * geometry.drawHeight)],
            [geometry.offsetX + (Number(map.bottom_left_x || 0) * geometry.drawWidth), geometry.offsetY + (Number(map.bottom_left_y || 0) * geometry.drawHeight)],
        ];
    }

    return [
        resolvePoint(scene.zone_points?.tl, scene, geometry.drawWidth, geometry.drawHeight, geometry.offsetX, geometry.offsetY),
        resolvePoint(scene.zone_points?.tr, scene, geometry.drawWidth, geometry.drawHeight, geometry.offsetX, geometry.offsetY),
        resolvePoint(scene.zone_points?.br, scene, geometry.drawWidth, geometry.drawHeight, geometry.offsetX, geometry.offsetY),
        resolvePoint(scene.zone_points?.bl, scene, geometry.drawWidth, geometry.drawHeight, geometry.offsetX, geometry.offsetY),
    ];
}

const NikahPreview = {
    canvasId: 'nikah-preview-canvas',
    mockups: [],
    template: {},
    fonts: [],

    configure({ canvasId, mockups, template, fonts }) {
        this.canvasId = canvasId || this.canvasId;
        this.mockups = mockups ?? [];
        this.template = template ?? {};
        this.fonts = fonts ?? [];
    },

    fontConfig(key) {
        return this.fonts.find((font) => `${font.key}` === `${key}`) ?? this.fonts[0] ?? {};
    },

    async renderSceneCanvas(targetCanvas, fields, fontKey, mockupIndex = 0, mode = 'flat', fieldFonts = {}) {
        const width = Math.max(1, targetCanvas.width || 1);
        const height = Math.max(1, targetCanvas.height || 1);
        const ctx = targetCanvas.getContext('2d');
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';
        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.clearRect(0, 0, width, height);

        const flatCanvas = await this.renderFlat(fields, fontKey, fieldFonts, {
            mode,
            scene: this.mockups[mockupIndex] ?? null,
        });

        if (!flatCanvas) {
            return null;
        }

        if (mode === 'flat' || !this.mockups[mockupIndex]) {
            drawBlurredCoverFill(ctx, flatCanvas, width, height);
            drawContainedImage(ctx, flatCanvas, width, height);
            return flatCanvas;
        }

        const scene = this.mockups[mockupIndex];
        const [backgroundImage, overlayImage, maskImage, Perspective] = await Promise.all([
            loadImage(scene.base_image_url || scene.image_url),
            loadImage(scene.overlay_image_url || scene.overlay_url),
            loadImage(scene.mask_image_url || scene.mask_url),
            (scene.map || scene.zone_points) ? loadPerspective().catch(() => null) : Promise.resolve(null),
        ]);

        if (!backgroundImage || !Perspective) {
            drawBlurredCoverFill(ctx, flatCanvas, width, height);
            drawContainedImage(ctx, flatCanvas, width, height);
            return flatCanvas;
        }

        drawBlurredCoverFill(ctx, backgroundImage, width, height);
        const geometry = drawContainedImage(ctx, backgroundImage, width, height);
        const points = getAdminMapPoints(scene, geometry);

        if (points.length === 4) {
            const certificateImage = await canvasToPerspectiveSource(flatCanvas);

            ctx.save();
            ctx.globalAlpha = clamp(Number(scene.map?.opacity ?? scene.opacity ?? 0.96), 0.1, 1);
            const perspective = new Perspective(ctx, certificateImage);
            perspective.draw(points);
            ctx.restore();
        }

        if (overlayImage) {
            ctx.save();
            ctx.globalAlpha = clamp(Number(scene.map?.highlight_strength ?? scene.highlight_strength ?? 0.14), 0.12, 1);
            drawContainedImage(ctx, overlayImage, width, height);
            ctx.restore();
        }

        if (maskImage) {
            ctx.save();
            ctx.globalAlpha = clamp(Number(scene.map?.highlight_strength ?? scene.highlight_strength ?? 0.14) * 0.9, 0.12, 1);
            drawContainedImage(ctx, maskImage, width, height);
            ctx.restore();
        }

        return flatCanvas;
    },

    async render(fields, fontKey, mockupIndex = 0, mode = 'flat', fieldFonts = {}) {
        const visibleCanvas = document.getElementById(this.canvasId);

        if (!visibleCanvas) {
            return null;
        }

        const stage = visibleCanvas.parentElement;
        const displayWidth = Math.max(1, Math.round(stage?.clientWidth || visibleCanvas.clientWidth || 1));
        const displayHeight = Math.max(1, Math.round(stage?.clientHeight || visibleCanvas.clientHeight || 1));
        const renderScale = Math.min(3, Math.max(2, window.devicePixelRatio || 1));
        const width = Math.round(displayWidth * renderScale);
        const height = Math.round(displayHeight * renderScale);

        visibleCanvas.width = width;
        visibleCanvas.height = height;
        visibleCanvas.style.width = `${displayWidth}px`;
        visibleCanvas.style.height = `${displayHeight}px`;
        return this.renderSceneCanvas(visibleCanvas, fields, fontKey, mockupIndex, mode, fieldFonts);
    },

    async renderThumbnail(fields, fontKey, mockupIndex = 0, mode = 'flat', fieldFonts = {}, size = 160) {
        const canvas = createCanvas(size, size);
        await this.renderSceneCanvas(canvas, fields, fontKey, mockupIndex, mode, fieldFonts);

        return canvas.toDataURL('image/png');
    },

    async renderFlat(fields, fontKey, fieldFonts = {}, options = {}) {
        const snapshotUrl = this.template.rendered_preview_url || this.template.thumbnail_image_url;
        const baseUrl = this.template.base_template_url || this.template.preview_image_url || snapshotUrl;
        const baseImage = await loadImage(baseUrl);

        if (!baseImage) {
            return null;
        }

        const { width, height } = exportCanvasSize(this.template, baseImage);
        const canvas = createCanvas(width, height);
        const ctx = canvas.getContext('2d');
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';

        ctx.fillStyle = '#FDF0D5';
        ctx.fillRect(0, 0, width, height);
        drawCoverImage(ctx, baseImage, width, height);

        const sortedLayers = [...(this.template.fields ?? [])].sort((left, right) => Number(left.z_index || 1) - Number(right.z_index || 1));
        const editorCanvasWidth = Math.max(1, Number(this.template.editor_canvas_width || 980));
        const baseFontScale = Math.max(1, width / editorCanvasWidth);
        const storefrontTextScale = options.mode === 'mockup'
            ? clamp(Number(this.template.storefront_text_scale || 1), 1, 3)
            : 1;
        const fontScale = baseFontScale * storefrontTextScale;

        for (const field of sortedLayers) {
            const layer = buildFieldLayer({ ...field, font_scale: fontScale }, width, height);
            const fieldFontKey = fieldFonts[field.name] ?? fieldFonts[field.field_key];
            const hasFieldFont = `${fieldFontKey ?? ''}`.trim().length > 0;
            const selectedFontKey = hasFieldFont ? fieldFontKey : fontKey;
            const selectedFont = this.fontConfig(selectedFontKey);
            const fontStyle = {
                fontFamily: hasFieldFont
                    ? (selectedFont.css_family || field.settings?.font_family_override || 'serif')
                    : (field.settings?.font_family_override || '"Poppins", sans-serif'),
                fontWeight: hasFieldFont
                    ? (selectedFont.font_weight || field.settings?.font_weight || '600')
                    : (field.settings?.font_weight || '600'),
                fontStyle: hasFieldFont
                    ? (selectedFont.font_style || field.settings?.font_style || 'normal')
                    : (field.settings?.font_style || 'normal'),
                lineHeight: Number(field.line_height || 1.2),
                letterSpacing: Number(field.letter_spacing || 0) * fontScale,
                textTransform: hasFieldFont
                    ? (selectedFont.text_transform || field.settings?.text_transform || 'none')
                    : (field.settings?.text_transform || 'none'),
                allowMultiline: Boolean(field.settings?.allow_multiline ?? true),
                maxLines: Number(field.settings?.max_lines || 3),
                overflowBehavior: field.settings?.overflow_behavior || 'shrink_then_wrap',
                fitMode: 'admin_width_wrap',
            };
            const fieldKey = field.name ?? field.field_key;
            // Static fields use admin-set default_value; others read from user input
            const isStaticField = field.field_type === 'static';
            const fieldValue = isStaticField
                ? (field.default_value || '')
                : (fields[fieldKey] ?? fields[field.field_key] ?? fields[field.name]);
            const prefix  = ((field.prefix  ?? field.settings?.prefix  ?? '') + '').trim();
            const postfix = ((field.postfix ?? field.settings?.postfix ?? '') + '').trim();
            const baseText = `${fieldValue || (!isStaticField ? (field.default_value || field.preview_sample_value || this.template.preview_data_presets?.[fieldKey] || field.placeholder || '') : '')}`.trim();

            // Resolve relative weight given a base weight + delta step
            const resolveWeight = (baseWeight, delta) => {
                const weights = [400, 500, 600, 700, 800];
                const base = Number(baseWeight || 600);
                const idx = weights.indexOf(base);
                const baseIdx = idx >= 0 ? idx : weights.findIndex((w) => w >= base) ?? 2;
                return String(weights[Math.max(0, Math.min(weights.length - 1, baseIdx + Number(delta || 0)))]);
            };
            // Resolve italic given mode: 'auto' inherits, 'italic' forces, 'normal' forces
            const resolveItalic = (mode, inherited) => {
                if (mode === 'italic') return 'italic';
                if (mode === 'normal') return 'normal';
                return inherited || 'normal'; // auto
            };

            // Build prefix/postfix fontStyle specs (relative to field's rendered style)
            const prefixFontStyle = prefix ? {
                ...fontStyle,
                fontWeight:    resolveWeight(fontStyle.fontWeight, field.settings?.prefix_weight_delta ?? 0),
                fontStyle:     resolveItalic(field.settings?.prefix_italic_mode ?? 'auto', fontStyle.fontStyle),
                textTransform: field.settings?.prefix_transform || 'none',
                _color:        field.settings?.prefix_color  || field.text_color || '#780000',
                _sizeOffset:   Number(field.settings?.prefix_size ?? 0),
            } : null;
            const postfixFontStyle = postfix ? {
                ...fontStyle,
                fontWeight:    resolveWeight(fontStyle.fontWeight, field.settings?.postfix_weight_delta ?? 0),
                fontStyle:     resolveItalic(field.settings?.postfix_italic_mode ?? 'auto', fontStyle.fontStyle),
                textTransform: field.settings?.postfix_transform || 'none',
                _color:        field.settings?.postfix_color  || field.text_color || '#780000',
                _sizeOffset:   Number(field.settings?.postfix_size ?? 0),
            } : null;

            // Always inline: prefix + main + postfix on same line
            const hasStyledSegments = !!(prefix || postfix); // per-segment canvas draw when prefix/postfix present
            const rawText = [prefix, baseText, postfix].filter(Boolean).join(' ');
            const text = applyTextTransform(rawText, fontStyle.textTransform);

            if (!text) {
                continue;
            }

            const x = (width * Number(field.position_x || 50)) / 100;
            const y = (height * Number(field.position_y || 50)) / 100;
            await ensureCanvasFont(fontStyle, text, Number(layer.font_size_max || 24));
            const { fontSize, lines } = fitTextBlock(ctx, text, layer, fontStyle);
            const lineHeight = Math.max(1, Number(fontStyle.lineHeight || 1.2));
            const totalHeight = fontSize * lineHeight * lines.length;
            const boxLeft = x - (layer.widthPx / 2);
            const boxTop = y - (layer.heightPx / 2);
            const contentPadX = Number(layer.safeInsetX || 12) / 2;
            const contentLeft = boxLeft + contentPadX;
            const contentWidth = Math.max(18, layer.widthPx - Number(layer.safeInsetX || 12));
            const verticalOffset = Math.max(4, (layer.heightPx - totalHeight) / 2);
            const startY = boxTop + verticalOffset + (fontSize * 0.82);
            const align = field.text_align === 'start' ? 'left' : (field.text_align === 'end' ? 'right' : 'center');
            const drawX = align === 'left' ? contentLeft : (align === 'right' ? contentLeft + contentWidth : x);

            // Helper to draw a single-line label (prefix or postfix) at given Y offset from box center
            const drawLabel = async (text, labelStyle, yOffset) => {
                if (!text) return 0;
                const lSize = Math.max(6, fontSize + (labelStyle._sizeOffset || 0));
                await ensureCanvasFont(labelStyle, text, lSize);
                const transformed = applyTextTransform(text, labelStyle.textTransform);
                ctx.save();
                ctx.translate(x, y);
                ctx.rotate((Number(field.rotation || 0) * Math.PI) / 180);
                ctx.translate(-x, -y);
                ctx.fillStyle = labelStyle._color || field.text_color || '#780000';
                ctx.textBaseline = 'alphabetic';
                ctx.textAlign = align;
                ctx.font = fontDeclaration(labelStyle, lSize);
                drawLineWithSpacing(ctx, transformed, drawX, yOffset, align, 0);
                ctx.restore();
                return lSize * Math.max(1, Number(labelStyle.lineHeight || 1.1));
            };

            ctx.save();
            ctx.translate(x, y);
            ctx.rotate((Number(field.rotation || 0) * Math.PI) / 180);
            ctx.translate(-x, -y);
            ctx.fillStyle = field.text_color || '#8B2635';
            ctx.textBaseline = 'alphabetic';
            ctx.textAlign = align;
            ctx.font = fontDeclaration(fontStyle, fontSize);
            ctx.beginPath();
            ctx.rect(contentLeft, -height, contentWidth, height * 3);
            ctx.clip();

            if (hasStyledSegments && lines.length === 1) {
                // ── Per-segment single-line draw ──────────────────────────────
                // Draw prefix / main / postfix individually so each can have its
                // own size, weight, and italic offset.
                const prefSize = prefixFontStyle  ? Math.max(6, fontSize + prefixFontStyle._sizeOffset)  : 0;
                const pofSize  = postfixFontStyle ? Math.max(6, fontSize + postfixFontStyle._sizeOffset) : 0;

                const prefTx = prefix  ? applyTextTransform(prefix,  prefixFontStyle?.textTransform  || 'none') : '';
                const mainTx = applyTextTransform(baseText, fontStyle.textTransform);
                const pofTx  = postfix ? applyTextTransform(postfix, postfixFontStyle?.textTransform || 'none') : '';

                // Measure each segment (with inter-segment spaces baked in)
                let prefW = 0, mainW = 0, pofW = 0;
                if (prefTx && prefixFontStyle) {
                    await ensureCanvasFont(prefixFontStyle, prefTx, prefSize);
                    ctx.font = fontDeclaration(prefixFontStyle, prefSize);
                    prefW = ctx.measureText(prefTx + (mainTx || pofTx ? ' ' : '')).width;
                }
                if (mainTx) {
                    await ensureCanvasFont(fontStyle, mainTx, fontSize);
                    ctx.font = fontDeclaration(fontStyle, fontSize);
                    mainW = ctx.measureText(mainTx).width;
                }
                if (pofTx && postfixFontStyle) {
                    await ensureCanvasFont(postfixFontStyle, pofTx, pofSize);
                    ctx.font = fontDeclaration(postfixFontStyle, pofSize);
                    pofW = ctx.measureText((mainTx || prefTx ? ' ' : '') + pofTx).width;
                }

                const totalW = prefW + mainW + pofW;
                let curX = align === 'center' ? x - totalW / 2
                         : align === 'right'  ? contentLeft + contentWidth - totalW
                         :                      contentLeft;

                ctx.textAlign = 'left';

                if (prefTx && prefixFontStyle) {
                    await ensureCanvasFont(prefixFontStyle, prefTx, prefSize);
                    ctx.fillStyle = prefixFontStyle._color || field.text_color || '#780000';
                    ctx.font = fontDeclaration(prefixFontStyle, prefSize);
                    ctx.fillText(prefTx + (mainTx || pofTx ? ' ' : ''), curX, startY);
                    curX += prefW;
                }
                if (mainTx) {
                    await ensureCanvasFont(fontStyle, mainTx, fontSize);
                    ctx.fillStyle = field.text_color || '#780000';
                    ctx.font = fontDeclaration(fontStyle, fontSize);
                    ctx.fillText(mainTx, curX, startY);
                    curX += mainW;
                }
                if (pofTx && postfixFontStyle) {
                    await ensureCanvasFont(postfixFontStyle, pofTx, pofSize);
                    ctx.fillStyle = postfixFontStyle._color || field.text_color || '#780000';
                    ctx.font = fontDeclaration(postfixFontStyle, pofSize);
                    ctx.fillText((mainTx || prefTx ? ' ' : '') + pofTx, curX, startY);
                }
            } else {
                // ── Standard multi-line / no-segment draw ─────────────────────
                lines.forEach((line, index) => {
                    const lineY = startY + (index * fontSize * lineHeight);
                    drawLineWithSpacing(ctx, line, drawX, lineY, align, fontStyle.letterSpacing || 0);
                });
            }

            ctx.restore();
        }

        return canvas;
    },
};

window.NikahPreview = NikahPreview;

export function registerNikahPreview(Alpine) {
    const moneyFormatter = new Intl.NumberFormat('en-BD', {
        style: 'currency',
        currency: 'BDT',
        maximumFractionDigits: 0,
    });

    Alpine.data('storefrontPdp', (config) => ({
        isCustomizable: Boolean(config.isCustomizable),
        mode: 'flat',
        activeMockup: 0,
        activeThumb: 0,
        activePreviewIndex: 0,
        activeImage: 0,
        fields: cloneData(config.fields ?? {}),
        fieldFonts: cloneData(config.fieldFonts ?? {}),
        activeFont: config.activeFont ?? null,
        previewReady: false,
        previewBusy: false,
        previewRenderToken: 0,
        thumbnailRenderToken: 0,
        proofNote: config.proofNote ?? '',
        selectedVariant: config.selectedVariant ?? '',
        selectedVariants: cloneData(config.selectedVariants ?? {}),
        variants: (cloneData(config.variants ?? [])).map((variant) => normalizeVariantRecord(variant)),
        variantGroups: buildVariantGroups(
            (cloneData(config.variants ?? [])).map((variant) => normalizeVariantRecord(variant)),
            cloneData(config.variantGroups ?? []),
        ),
        quantity: Number(config.quantity ?? 1) || 1,
        submitting: false,
        showStickyBar: false,
        zoomInstance: null,
        stickyObserver: null,
        recentlyViewedItems: [],
        previewThumbs: {},
        thumbnailRenderToken: 0,
        onResize: null,
        formatMoney(value) {
            const amount = Number(value ?? 0);

            return moneyFormatter.format(Number.isFinite(amount) ? amount : 0).replace('.00', '');
        },
        get hasInput() {
            return Object.values(this.fields).some((value) => `${value ?? ''}`.trim().length > 0);
        },
        get currentMockup() {
            return config.mockups?.[this.activeMockup] ?? null;
        },
        get hasGroupedVariants() {
            return this.variantGroups.length > 0;
        },
        get activeVariant() {
            if (this.hasGroupedVariants && this.variants.length) {
                return this.findMatchingVariant(this.selectedVariants) ?? null;
            }

            if (this.selectedVariant) {
                return this.variants.find((variant) => `${variant.id}` === `${this.selectedVariant}`) ?? null;
            }

            return this.variants.find((variant) => variant.is_default) ?? this.variants[0] ?? null;
        },
        get displayPrice() {
            return Number(this.activeVariant?.price ?? config.basePrice ?? 0);
        },
        get displayComparePrice() {
            const compare = this.activeVariant?.compare_at_price ?? config.baseComparePrice ?? null;

            return compare ? Number(compare) : null;
        },
        get savePercent() {
            if (!this.displayComparePrice || this.displayComparePrice <= this.displayPrice) {
                return 0;
            }

            return Math.round(((this.displayComparePrice - this.displayPrice) / this.displayComparePrice) * 100);
        },
        get activeGeneralImage() {
            return config.generalImages?.[this.activeImage] ?? config.generalImages?.[0] ?? null;
        },
        get previewCount() {
            return Math.max(1, (this.hasFlatPreview ? 1 : 0) + (config.mockups?.length ?? 0));
        },
        get hasFlatPreview() {
            return Boolean(config.showFlatPreviewFirst ?? false);
        },
        get generalImageCount() {
            return config.generalImages?.length ?? 0;
        },
        get currentPreviewTitle() {
            if (!this.isCustomizable) {
                return this.activeGeneralImage?.label ?? 'Product image';
            }

            if (this.hasFlatPreview && this.activeThumb === 0) {
                return 'Template preview';
            }

            return config.mockups?.[this.activeMockup]?.name ?? 'Selected mockup preview';
        },
        get activePreviewAspect() {
            if (!this.isCustomizable) {
                return '4 / 5';
            }

            if (this.mode === 'flat') {
                const template = config.template ?? {};
                const width = Math.max(1, Number(template.export_ratio_width || 9));
                const height = Math.max(1, Number(template.export_ratio_height || 13));

                return `${width} / ${height}`;
            }

            const scene = config.mockups?.[this.activeMockup] ?? {};
            const width = Math.max(1, Number(scene.image_width || 4));
            const height = Math.max(1, Number(scene.image_height || 5));

            return `${width} / ${height}`;
        },
        get previewPositionLabel() {
            if (this.isCustomizable) {
                return `${this.activeThumb + 1} / ${this.previewCount}`;
            }

            return `${this.activeImage + 1} / ${Math.max(1, this.generalImageCount)}`;
        },
        normalizedGroupKey(key) {
            return normalizeOptionKey(key);
        },
        groupIndex(groupKey) {
            return this.variantGroups.findIndex((group) => group.key === this.normalizedGroupKey(groupKey));
        },
        visibleValuesForGroup(groupKey, selection = this.selectedVariants) {
            const normalizedKey = this.normalizedGroupKey(groupKey);
            const groupIndex = this.groupIndex(normalizedKey);
            const group = this.variantGroups[groupIndex];

            if (!group) {
                return [];
            }

            const priorSelections = this.variantGroups
                .slice(0, Math.max(0, groupIndex))
                .reduce((carry, candidateGroup) => {
                    const selectedValue = selection?.[candidateGroup.key];

                    if (`${selectedValue ?? ''}`.trim()) {
                        carry[candidateGroup.key] = `${selectedValue}`;
                    }

                    return carry;
                }, {});

            return (group.values ?? []).filter((value) => this.variants.some((variant) => {
                if (`${variant.option_values?.[normalizedKey] ?? ''}` !== `${value.value}`) {
                    return false;
                }

                return Object.entries(priorSelections).every(([candidateKey, candidateValue]) => `${variant.option_values?.[candidateKey] ?? ''}` === `${candidateValue}`);
            }));
        },
        selectedValueLabel(groupKey) {
            const normalizedKey = this.normalizedGroupKey(groupKey);
            const selectedValue = this.selectedVariants?.[normalizedKey];
            const visibleValues = this.visibleValuesForGroup(normalizedKey, this.selectedVariants);
            const selected = visibleValues.find((value) => `${value.value}` === `${selectedValue}`)
                ?? this.variantGroups
                    .find((group) => group.key === normalizedKey)
                    ?.values?.find((value) => `${value.value}` === `${selectedValue}`);

            return selected?.label ?? selectedValue ?? '—';
        },
        frameTypeChip(group) {
            return /frame_type|material|color/i.test(`${group?.key ?? ''}`) || `${group?.type ?? ''}` === 'swatch';
        },
        swatchColor(value) {
            const palette = {
                black: '#1a1a1a',
                pine: '#A0784A',
                'natural pine wood': '#A0784A',
                gold: '#C4A882',
                'antique gold': '#C4A882',
                white: '#F5F5F0',
                brown: '#6B4226',
                standard: '#D6C7B2',
            };

            return palette[`${value?.swatch ?? value?.label ?? value?.value ?? ''}`.trim().toLowerCase()] ?? '#CFC6BB';
        },
        findMatchingVariant(selection = this.selectedVariants) {
            const requiredGroups = this.variantGroups.map((group) => group.key);

            if (!requiredGroups.length) {
                return this.variants.find((variant) => variant.is_default) ?? this.variants[0] ?? null;
            }

            return this.variants.find((variant) => requiredGroups.every((groupKey) => `${variant.option_values?.[groupKey] ?? ''}` === `${selection?.[groupKey] ?? ''}`)) ?? null;
        },
        syncVariantFromSelections() {
            if (!this.hasGroupedVariants) {
                return;
            }

            const matchedVariant = this.findMatchingVariant(this.selectedVariants);

            if (matchedVariant?.id) {
                this.selectedVariant = `${matchedVariant.id}`;
            }
        },
        reconcileSelections(startIndex = 0) {
            if (!this.hasGroupedVariants) {
                return;
            }

            const nextSelections = { ...(this.selectedVariants ?? {}) };

            this.variantGroups.forEach((group, index) => {
                if (index < startIndex) {
                    return;
                }

                const visibleValues = this.visibleValuesForGroup(group.key, nextSelections);
                const currentValue = nextSelections[group.key];

                if (currentValue && visibleValues.some((value) => `${value.value}` === `${currentValue}`)) {
                    return;
                }

                if (visibleValues[0]) {
                    nextSelections[group.key] = `${visibleValues[0].value}`;
                    return;
                }

                delete nextSelections[group.key];
            });

            this.selectedVariants = nextSelections;
            this.syncVariantFromSelections();
        },
        initializeVariantState() {
            if (this.hasGroupedVariants) {
                if (!Object.keys(this.selectedVariants ?? {}).length && this.selectedVariant) {
                    const selected = this.variants.find((variant) => `${variant.id}` === `${this.selectedVariant}`);

                    if (selected?.option_values) {
                        this.selectedVariants = { ...selected.option_values };
                    }
                }

                this.reconcileSelections(0);
            }

            if (!this.selectedVariant && this.activeVariant?.id) {
                this.selectedVariant = `${this.activeVariant.id}`;
            }
        },
        selectVariant(option, value, variantId = null) {
            const normalizedOption = this.normalizedGroupKey(option);
            const nextSelections = { ...(this.selectedVariants ?? {}), [normalizedOption]: value };
            const changedIndex = this.groupIndex(normalizedOption);

            this.selectedVariants = nextSelections;

            if (this.hasGroupedVariants) {
                this.reconcileSelections(Math.max(0, changedIndex + 1));
                this.syncPreviewFromActiveVariant();
                return;
            }

            if (variantId) {
                this.selectedVariant = `${variantId}`;
                this.syncPreviewFromActiveVariant();
                return;
            }

            const match = this.variants.find((variant) => Object.entries(nextSelections).every(([groupKey, groupValue]) => `${variant.option_values?.[groupKey] ?? ''}` === `${groupValue}`));

            if (match?.id) {
                this.selectedVariant = `${match.id}`;
                this.syncPreviewFromActiveVariant();
            }
        },
        syncPreviewFromActiveVariant() {
            const variant = this.activeVariant;

            if (!variant) {
                return false;
            }

            const mappedMediaIds = Object.entries(variant.option_values ?? {})
                .map(([key, value]) => mediaLinksForOption(config.variantMediaLinks, key, value))
                .flat()
                .filter(Boolean);

            if (!this.isCustomizable) {
                const imageIndex = firstMappedMediaIndex(config.generalImages, mappedMediaIds);

                if (imageIndex !== undefined) {
                    this.selectImage(imageIndex);
                    return true;
                }

                return false;
            }

            if (this.isCustomizable) {
                const mockupIndex = firstMappedMediaIndex(config.mockups, mappedMediaIds);

                if (mockupIndex !== undefined) {
                    this.selectMockup(mockupIndex);
                    return true;
                }
            }

            return false;
        },
        openSizeGuide() {
            document.getElementById('shipping-care-policy')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },
        switchMode(mode) {
            if (mode === 'flat') {
                this.selectThumb(0);
                return;
            }

            this.selectThumb(Math.max(1, this.activeThumb || 1));
        },
        selectMockup(index) {
            this.activeMockup = index;
            this.mode = 'mockup';
            this.activeThumb = index + (this.hasFlatPreview ? 1 : 0);
            this.activePreviewIndex = this.activeThumb;
            this.renderPreview({ refreshThumbs: false });
        },
        selectThumb(index) {
            if (!this.isCustomizable) {
                this.selectImage(index);
                return;
            }

            this.activeThumb = index;
            this.activePreviewIndex = index;

            if (index === 0) {
                if (this.hasFlatPreview) {
                    this.mode = 'flat';
                } else {
                    this.activeMockup = 0;
                    this.mode = (config.mockups?.length ?? 0) > 0 ? 'mockup' : 'flat';
                }
            } else {
                this.activeMockup = Math.max(0, index - (this.hasFlatPreview ? 1 : 0));
                this.mode = 'mockup';
            }

            this.renderPreview({ refreshThumbs: false });
        },
        selectPreview(index) {
            this.selectThumb(index);
        },
        selectImage(index) {
            this.activeImage = index;
        },
        nextPreview() {
            if (this.isCustomizable) {
                if (this.previewCount <= 1) {
                    return;
                }

                this.selectThumb((this.activeThumb + 1) % this.previewCount);
                return;
            }

            if (this.generalImageCount <= 1) {
                return;
            }

            this.selectImage((this.activeImage + 1) % this.generalImageCount);
        },
        previousPreview() {
            if (this.isCustomizable) {
                if (this.previewCount <= 1) {
                    return;
                }

                this.selectThumb((this.activeThumb - 1 + this.previewCount) % this.previewCount);
                return;
            }

            if (this.generalImageCount <= 1) {
                return;
            }

            this.selectImage((this.activeImage - 1 + this.generalImageCount) % this.generalImageCount);
        },
        primaryFontId() {
            const selectedFont = Object.values(this.fieldFonts ?? {}).find((value) => `${value ?? ''}`.trim().length > 0);

            return this.activeFont || selectedFont || '';
        },
        isNameFontField(fieldKey, templateField = null) {
            const source = [
                fieldKey,
                templateField?.field_key,
                templateField?.name,
                templateField?.label,
            ].filter(Boolean).join(' ').toLowerCase();

            return source.includes('bride') || source.includes('groom');
        },
        applyNameFont(fontId) {
            this.activeFont = `${fontId}`;
            const nextFieldFonts = { ...(this.fieldFonts ?? {}) };
            const templateFields = config.template?.fields ?? [];

            Object.keys(nextFieldFonts).forEach((fieldKey) => {
                const templateField = templateFields.find((field) => field.field_key === fieldKey || field.name === fieldKey);

                if (!this.isNameFontField(fieldKey, templateField)) {
                    delete nextFieldFonts[fieldKey];
                }
            });

            Object.keys(this.fields ?? {}).forEach((fieldKey) => {
                const templateField = templateFields.find((field) => field.field_key === fieldKey || field.name === fieldKey);
                const fieldType = templateField?.field_type ?? templateField?.settings?.field_type ?? 'text';

                if (fieldType !== 'static' && this.isNameFontField(fieldKey, templateField)) {
                    nextFieldFonts[fieldKey] = `${fontId}`;
                }
            });

            this.fieldFonts = nextFieldFonts;
            this.renderPreview();
        },
        setFieldFont(fieldKey, fontId) {
            this.fieldFonts = { ...(this.fieldFonts ?? {}), [fieldKey]: `${fontId}` };
            this.activeFont = this.primaryFontId();
            this.renderPreview();
        },
        // Auto-convert an English (Gregorian) date field to Bangla (Bengali calendar)
        // and Hijri (Arabic/Islamic calendar), then push into this.fields so the canvas renders them.
        computeAutoDates(key, fieldSettings) {
            const raw = this.fields[key];         // ISO date string "YYYY-MM-DD" from <input type="date">
            if (!raw) return;

            // Only process ISO format from the date input
            if (!/^\d{4}-\d{2}-\d{2}$/.test(raw)) return;
            const date = new Date(raw + 'T00:00:00');
            if (isNaN(date)) return;

            const y = date.getFullYear(), m = date.getMonth() + 1, d = date.getDate();

            // ── Helpers ───────────────────────────────────────────────────────
            const EN_M    = ['January','February','March','April','May','June','July','August','September','October','November','December'];
            const EN_DAYS = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
            const ordinal = n => { const s=['th','st','nd','rd'], v=n%100; return n+(s[(v-20)%10]||s[v]||s[0]); };
            const mn      = EN_M[m - 1];
            const dayName = EN_DAYS[date.getDay()];

            // ── Format English date ───────────────────────────────────────────
            const fmt = fieldSettings?.date_format ?? 'long';
            let enFormatted;
            if (fmt === 'us')           enFormatted = `${mn} ${d}, ${y}`;
            else if (fmt === 'numeric') enFormatted = `${String(d).padStart(2,'0')}/${String(m).padStart(2,'0')}/${y}`;
            else if (fmt === 'ordinal') enFormatted = `${ordinal(d)} ${mn} ${y}, ${dayName}`;
            else                        enFormatted = `${d} ${mn} ${y}`;   // 'long' default
            this.fields[key] = enFormatted;

            // Auto-detect companion fields from template config (key_bangla / key_arabic)
            const templateFields = window.__TEMPLATE__?.fields ?? [];
            const hasBangla = templateFields.some(f => f.field_key === key + '_bangla');
            const hasArabic = templateFields.some(f => f.field_key === key + '_arabic');

            // ── Bengali calendar (বঙ্গাব্দ) ────────────────────────────────────
            if (hasBangla) {
                const BN_MONTHS = ['বৈশাখ','জ্যৈষ্ঠ','আষাঢ়','শ্রাবণ','ভাদ্র','আশ্বিন','কার্তিক','অগ্রহায়ণ','পৌষ','মাঘ','ফাল্গুন','চৈত্র'];
                // Gregorian month start days for Bengali months (approx, non-leap year)
                const BN_START = [[4,14],[5,15],[6,15],[7,16],[8,17],[9,17],[10,18],[11,17],[12,16],[1,14],[2,13],[3,14]];
                // Determine Bengali month and day
                let bnMonth = -1, bnDay = 0, bnYear = y - 593;
                const dt = new Date(y, m - 1, d);
                for (let i = 0; i < 12; i++) {
                    const [sm, sd] = BN_START[i];
                    const [nm, nd] = BN_START[(i + 1) % 12];
                    // If Bengali month starts in a month LATER than the input month,
                    // it started in the PREVIOUS Gregorian year (e.g. পৌষ starts Dec,
                    // so a Jan input date falls in পৌষ that started last December)
                    const curYear = sm > m ? y - 1 : y;
                    const cur = new Date(curYear, sm - 1, sd);
                    // Next month boundary: cross year when next month number < current start month number
                    const nxtYear = nm < sm ? curYear + 1 : curYear;
                    const nxt = new Date(nxtYear, nm - 1, nd);
                    if (dt >= cur && dt < nxt) { bnMonth = i; bnDay = Math.floor((dt - cur) / 86400000) + 1; break; }
                }
                // If January-March, Bengali year is y - 594 before Bangla New Year
                if (m < 4 || (m === 4 && d < 14)) bnYear = y - 594;
                if (bnMonth >= 0) {
                    const toBn = n => String(n).replace(/\d/g, x => '০১২৩৪৫৬৭৮৯'[x]);
                    this.fields[key + '_bangla'] = `${toBn(bnDay)} ${BN_MONTHS[bnMonth]} ${toBn(bnYear)}`;
                }
            }

            // ── Hijri calendar (Islamic, in English) ─────────────────────────
            if (hasArabic) {
                const HJ_MONTHS = ['Muharram','Safar','Rabi al-Awwal','Rabi al-Thani','Jumada al-Awwal','Jumada al-Thani','Rajab',"Sha'ban",'Ramadan','Shawwal','Dhul Qadah','Dhul Hijjah'];
                // Kuwaiti algorithm for approximate Hijri date
                const jd = Math.floor((1461 * (y + 4800 + Math.floor((m - 14) / 12))) / 4)
                         + Math.floor((367 * (m - 2 - 12 * Math.floor((m - 14) / 12))) / 12)
                         - Math.floor((3 * Math.floor((y + 4900 + Math.floor((m - 14) / 12)) / 100)) / 4)
                         + d - 32075;
                let l = jd - 1948440 + 10632;
                const n = Math.floor((l - 1) / 10631);
                l = l - 10631 * n + 354;
                const j = Math.floor((10985 - l) / 5316) * Math.floor((50 * l) / 17719) + Math.floor(l / 5670) * Math.floor((43 * l) / 15238);
                l = l - Math.floor((30 - j) / 15) * Math.floor((17719 * j) / 50) - Math.floor(j / 16) * Math.floor((15238 * j) / 43) + 29;
                const hMonth = Math.floor((24 * l) / 709);
                const hDay   = l - Math.floor((709 * hMonth) / 24);
                const hYear  = 30 * n + j - 30;
                this.fields[key + '_arabic'] = `${ordinal(hDay)} ${HJ_MONTHS[hMonth - 1]} ${hYear} AH`;
            }
        },

        preloadPreviewMedia() {
            if (!this.isCustomizable) {
                (config.generalImages ?? []).forEach((image) => loadImage(image.url || image.thumb));
                return;
            }

            (config.mockups ?? []).forEach((scene) => {
                preloadMockupAssets(scene).catch(() => {});
            });

            if ((config.mockups ?? []).some((scene) => scene.map || scene.zone_points)) {
                loadPerspective().catch(() => {});
            }
        },
        async renderPreview(options = {}) {
            if (!this.isCustomizable) {
                return;
            }

            const { refreshThumbs = true } = options;
            const token = Date.now() + Math.random();
            this.previewRenderToken = token;
            this.previewReady = false;
            this.previewBusy = true;

            try {
                await nextFrame();
                if (this.mode === 'mockup') {
                    await preloadMockupAssets(config.mockups?.[this.activeMockup]);
                }

                if (this.previewRenderToken !== token) {
                    return;
                }

                await window.NikahPreview.render(this.fields, this.activeFont, this.activeMockup, this.mode, this.fieldFonts);
            } finally {
                if (this.previewRenderToken === token) {
                    this.previewReady = true;
                    this.previewBusy = false;
                }
            }

            if (refreshThumbs && this.previewRenderToken === token) {
                whenIdle(() => this.renderThumbnailRail().catch(() => {}));
            }
        },
        async renderThumbnailRail() {
            if (!this.isCustomizable || !window.NikahPreview) {
                return;
            }

            const token = Date.now();
            this.thumbnailRenderToken = token;
            const nextThumbs = {};

            if (this.hasFlatPreview) {
                nextThumbs.flat = await window.NikahPreview.renderThumbnail(this.fields, this.activeFont, 0, 'flat', this.fieldFonts, 160).catch(() => null);
            }

            for (let index = 0; index < (config.mockups?.length ?? 0); index += 1) {
                nextThumbs[`mockup-${index}`] = await window.NikahPreview.renderThumbnail(this.fields, this.activeFont, index, 'mockup', this.fieldFonts, 160).catch(() => null);
            }

            if (this.thumbnailRenderToken === token) {
                this.previewThumbs = nextThumbs;
            }
        },
        syncRecentlyViewed() {
            const key = 'recently_viewed_products';
            const current = config.currentProduct ?? null;

            if (!current?.id) {
                return;
            }

            try {
                const existing = JSON.parse(window.localStorage.getItem(key) ?? '[]');
                const next = [current, ...existing.filter((item) => `${item.id}` !== `${current.id}`)].slice(0, 8);

                window.localStorage.setItem(key, JSON.stringify(next));
                this.recentlyViewedItems = next.filter((item) => `${item.id}` !== `${current.id}`).slice(0, 8);
            } catch (error) {
                this.recentlyViewedItems = [];
            }
        },
        initZoom() {
            if (!this.isCustomizable || !this.$refs.previewStage || !this.$refs.previewCanvas || !window.initPdpZoom) {
                return;
            }

            this.zoomInstance = window.initPdpZoom(this.$refs.previewStage, this.$refs.previewCanvas);
        },
        observeStickyBar() {
            if (!this.$refs.ctaAnchor) {
                return;
            }

            this.stickyObserver?.disconnect();
            this.stickyObserver = new IntersectionObserver(([entry]) => {
                this.showStickyBar = window.innerWidth < 1024 && !entry.isIntersecting;
            }, {
                threshold: 0.2,
            });

            this.stickyObserver.observe(this.$refs.ctaAnchor);
        },
        init() {
            this.$nextTick(() => {
                this.initializeVariantState();
                this.$watch('selectedVariant', () => this.syncPreviewFromActiveVariant());
                this.syncRecentlyViewed();
                this.observeStickyBar();

                if (this.isCustomizable) {
                    window.__MOCKUPS__ = config.mockups ?? [];
                    window.__TEMPLATE__ = config.template ?? {};

                    window.NikahPreview.configure({
                        canvasId: config.canvasId ?? 'nikah-preview-canvas',
                        mockups: config.mockups ?? [],
                        template: config.template ?? {},
                        fonts: config.fonts ?? [],
                    });

                    this.preloadPreviewMedia();
                    this.activeFont = this.primaryFontId() || config.activeFont || null;
                    const defaultMockupIndex = (config.mockups ?? []).findIndex((mockup) => `${mockup.id}` === `${config.defaultMockupId}`);

                    if ((config.galleryDefaultSource === 'selected_mockup' || !this.hasFlatPreview) && defaultMockupIndex >= 0) {
                        this.activeMockup = defaultMockupIndex;
                        this.activeThumb = defaultMockupIndex + (this.hasFlatPreview ? 1 : 0);
                        this.activePreviewIndex = this.activeThumb;
                        this.mode = 'mockup';
                    } else if (!this.hasFlatPreview && (config.mockups?.length ?? 0) > 0) {
                        this.activeMockup = 0;
                        this.activeThumb = 0;
                        this.activePreviewIndex = 0;
                        this.mode = 'mockup';
                    }

                    const syncedVariantPreview = this.syncPreviewFromActiveVariant();
                    this.initZoom();

                    if (syncedVariantPreview) {
                        whenIdle(() => this.renderThumbnailRail().catch(() => {}));
                    } else {
                        this.renderPreview();
                    }
                } else {
                    this.preloadPreviewMedia();
                    this.syncPreviewFromActiveVariant();
                }
            });

            this.onResize = () => {
                this.observeStickyBar();

                if (this.isCustomizable) {
                    this.renderPreview();
                }
            };

            window.addEventListener('resize', this.onResize);
        },
        destroy() {
            this.stickyObserver?.disconnect();
            this.zoomInstance?.destroy?.();

            if (this.onResize) {
                window.removeEventListener('resize', this.onResize);
            }
        },
    }));
}
