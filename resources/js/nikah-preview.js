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
        image.onerror = reject;
        image.src = url;
    });

    imageCache.set(url, promise);

    return promise;
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
}

function measureTextWithSpacing(ctx, text, letterSpacing) {
    if (!text) {
        return 0;
    }

    const spacing = Number(letterSpacing || 0);

    return ctx.measureText(text).width + Math.max(0, text.length - 1) * spacing;
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
    const width = Math.max(40, layer.widthPx);
    const height = Math.max(24, layer.heightPx);
    const minSize = Math.max(8, Number(layer.font_size_min || 12));
    const maxSize = Math.max(minSize, Number(layer.font_size_max || 24));
    const lineHeight = Math.max(1, Number(fontStyle.lineHeight || layer.line_height || 1.2));
    const letterSpacing = Number(fontStyle.letterSpacing ?? layer.letter_spacing ?? 0);
    const allowMultiline = Boolean(fontStyle.allowMultiline);
    const maxLines = Math.max(1, Number(fontStyle.maxLines || 1));
    const canWrap = allowMultiline && fontStyle.overflowBehavior !== 'shrink_only';

    for (let fontSize = maxSize; fontSize >= minSize; fontSize -= 1) {
        ctx.font = `${fontStyle.fontStyle || 'normal'} ${fontStyle.fontWeight || '600'} ${fontSize}px ${fontStyle.fontFamily}`;
        const lines = wrapTextLines(ctx, text, width, letterSpacing, canWrap);
        const tallestLine = fontSize * lineHeight * lines.length;
        const widestLine = Math.max(...lines.map((line) => measureTextWithSpacing(ctx, line, letterSpacing)));

        if (lines.length <= maxLines && tallestLine <= height && widestLine <= width) {
            return { fontSize, lines };
        }
    }

    ctx.font = `${fontStyle.fontStyle || 'normal'} ${fontStyle.fontWeight || '600'} ${minSize}px ${fontStyle.fontFamily}`;

    return {
        fontSize: minSize,
        lines: wrapTextLines(ctx, text, width, letterSpacing, canWrap).slice(0, maxLines),
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
    return {
        ...field,
        widthPx: (width * Number(field.width || 50)) / 100,
        heightPx: (height * Number(field.height || 8)) / 100,
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

    async render(fields, fontKey, mockupIndex = 0, mode = 'flat') {
        const visibleCanvas = document.getElementById(this.canvasId);

        if (!visibleCanvas) {
            return null;
        }

        const stage = visibleCanvas.parentElement;
        const width = Math.max(1, Math.round(stage?.clientWidth || visibleCanvas.clientWidth || 1));
        const height = Math.max(1, Math.round(stage?.clientHeight || visibleCanvas.clientHeight || 1));
        const devicePixelRatio = window.devicePixelRatio || 1;

        visibleCanvas.width = Math.round(width * devicePixelRatio);
        visibleCanvas.height = Math.round(height * devicePixelRatio);
        visibleCanvas.style.width = `${width}px`;
        visibleCanvas.style.height = `${height}px`;

        const ctx = visibleCanvas.getContext('2d');
        ctx.setTransform(devicePixelRatio, 0, 0, devicePixelRatio, 0, 0);
        ctx.clearRect(0, 0, width, height);

        const flatCanvas = await this.renderFlat(fields, fontKey);

        if (!flatCanvas) {
            return null;
        }

        if (mode === 'flat' || !this.mockups[mockupIndex]) {
            drawContainedImage(ctx, flatCanvas, width, height);
            return flatCanvas;
        }

        const scene = this.mockups[mockupIndex];
        const [backgroundImage, overlayImage, Perspective] = await Promise.all([
            loadImage(scene.image_url),
            loadImage(scene.overlay_url),
            loadPerspective(),
        ]);

        if (!backgroundImage || !Perspective) {
            drawContainedImage(ctx, flatCanvas, width, height);
            return flatCanvas;
        }

        drawContainedImage(ctx, backgroundImage, width, height);

        const { drawWidth, drawHeight, offsetX, offsetY } = getContainGeometry(backgroundImage, width, height);
        const points = [
            resolvePoint(scene.zone_points?.tl, scene, drawWidth, drawHeight, offsetX, offsetY),
            resolvePoint(scene.zone_points?.tr, scene, drawWidth, drawHeight, offsetX, offsetY),
            resolvePoint(scene.zone_points?.br, scene, drawWidth, drawHeight, offsetX, offsetY),
            resolvePoint(scene.zone_points?.bl, scene, drawWidth, drawHeight, offsetX, offsetY),
        ];

        ctx.save();
        ctx.globalAlpha = clamp(Number(scene.opacity ?? 0.96), 0.1, 1);
        const perspective = new Perspective(ctx, flatCanvas);
        perspective.draw(points);
        ctx.restore();

        if (overlayImage) {
            ctx.save();
            ctx.globalAlpha = clamp(Number(scene.highlight_strength ?? 0.14), 0.1, 1);
            drawContainedImage(ctx, overlayImage, width, height);
            ctx.restore();
        }

        return flatCanvas;
    },

    async renderFlat(fields, fontKey) {
        const baseUrl = this.template.preview_image_url || this.template.base_template_url;
        const baseImage = await loadImage(baseUrl);

        if (!baseImage) {
            return null;
        }

        const width = baseImage.naturalWidth || baseImage.width;
        const height = baseImage.naturalHeight || baseImage.height;
        const canvas = createCanvas(width, height);
        const ctx = canvas.getContext('2d');

        ctx.drawImage(baseImage, 0, 0, width, height);

        const font = this.fontConfig(fontKey);
        const sortedLayers = [...(this.template.fields ?? [])].sort((left, right) => Number(left.z_index || 1) - Number(right.z_index || 1));

        sortedLayers.forEach((field) => {
            const layer = buildFieldLayer(field, width, height);
            const fontStyle = {
                fontFamily: field.settings?.font_family_override || font.css_family || 'serif',
                fontWeight: field.settings?.font_weight || font.font_weight || '600',
                fontStyle: field.settings?.font_style || 'normal',
                lineHeight: Number(field.line_height || 1.2),
                letterSpacing: Number(field.letter_spacing ?? 0),
                textTransform: field.settings?.text_transform || 'none',
                allowMultiline: Boolean(field.settings?.allow_multiline ?? true),
                maxLines: Number(field.settings?.max_lines || 3),
                overflowBehavior: field.settings?.overflow_behavior || 'shrink_then_wrap',
            };
            const rawText = `${fields[field.name] ?? fields[field.field_key] ?? field.placeholder ?? ''}`.trim();
            const text = applyTextTransform(rawText, fontStyle.textTransform);

            if (!text) {
                return;
            }

            const x = (width * Number(field.position_x || 50)) / 100;
            const y = (height * Number(field.position_y || 50)) / 100;
            const { fontSize, lines } = fitTextBlock(ctx, text, layer, fontStyle);
            const lineHeight = Math.max(1, Number(fontStyle.lineHeight || 1.2));
            const totalHeight = fontSize * lineHeight * lines.length;
            const boxLeft = x - (layer.widthPx / 2);
            const startY = y - (totalHeight / 2) + fontSize;
            const align = field.text_align === 'start' ? 'left' : (field.text_align === 'end' ? 'right' : 'center');
            const drawX = align === 'left' ? boxLeft : (align === 'right' ? boxLeft + layer.widthPx : x);

            ctx.save();
            ctx.translate(x, y);
            ctx.rotate((Number(field.rotation || 0) * Math.PI) / 180);
            ctx.translate(-x, -y);
            ctx.fillStyle = field.text_color || '#8B2635';
            ctx.textBaseline = 'alphabetic';
            ctx.textAlign = align;
            ctx.font = `${fontStyle.fontStyle || 'normal'} ${fontStyle.fontWeight || '600'} ${fontSize}px ${fontStyle.fontFamily}`;

            lines.forEach((line, index) => {
                const lineY = startY + (index * fontSize * lineHeight);
                drawLineWithSpacing(ctx, line, drawX, lineY, align, fontStyle.letterSpacing || 0);
            });

            ctx.restore();
        });

        return canvas;
    },
};

window.NikahPreview = NikahPreview;

export function registerNikahPreview(Alpine) {
    Alpine.data('storefrontPdp', (config) => ({
        isCustomizable: Boolean(config.isCustomizable),
        mode: 'flat',
        activeMockup: 0,
        activeImage: 0,
        fields: cloneData(config.fields ?? {}),
        activeFont: config.activeFont ?? null,
        previewReady: false,
        selectedVariant: config.selectedVariant ?? '',
        quantity: Number(config.quantity ?? 1) || 1,
        showStickyBar: false,
        stickyObserver: null,
        onResize: null,
        get hasInput() {
            return Object.values(this.fields).some((value) => `${value ?? ''}`.trim().length > 0);
        },
        get activeGeneralImage() {
            return config.generalImages?.[this.activeImage] ?? config.generalImages?.[0] ?? null;
        },
        switchMode(mode) {
            this.mode = mode;
            this.renderPreview();
        },
        selectMockup(index) {
            this.activeMockup = index;
            this.mode = 'mockup';
            this.renderPreview();
        },
        selectImage(index) {
            this.activeImage = index;
        },
        async renderPreview() {
            if (!this.isCustomizable) {
                return;
            }

            this.previewReady = false;
            await window.NikahPreview.render(this.fields, this.activeFont, this.activeMockup, this.mode);
            this.previewReady = true;
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

                    this.renderPreview();
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

            if (this.onResize) {
                window.removeEventListener('resize', this.onResize);
            }
        },
    }));
}
