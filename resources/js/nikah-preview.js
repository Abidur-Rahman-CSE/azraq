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

    return { drawWidth, drawHeight, offsetX, offsetY };
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
    const width = Math.max(18, Math.max(24, layer.widthPx) - 12);
    const height = Math.max(14, Math.max(18, layer.heightPx) - 10);
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
    const fontScale = Number(field.font_scale || 1);

    return {
        ...field,
        widthPx: (width * Number(field.width || 50)) / 100,
        heightPx: (height * Number(field.height || 8)) / 100,
        font_size_min: Math.max(8, Number(field.font_size_min || 12) * fontScale),
        font_size_max: Math.max(8, Number(field.font_size_max || 24) * fontScale),
        letter_spacing: Number(field.letter_spacing || 0) * fontScale,
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

        const ctx = visibleCanvas.getContext('2d');
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';
        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.clearRect(0, 0, width, height);

        const flatCanvas = await this.renderFlat(fields, fontKey, fieldFonts);

        if (!flatCanvas) {
            return null;
        }

        if (mode === 'flat' || !this.mockups[mockupIndex]) {
            drawContainedImage(ctx, flatCanvas, width, height);
            return flatCanvas;
        }

        const scene = this.mockups[mockupIndex];
        const [backgroundImage, overlayImage, maskImage, Perspective] = await Promise.all([
            loadImage(scene.base_image_url || scene.image_url),
            loadImage(scene.overlay_image_url || scene.overlay_url),
            loadImage(scene.mask_image_url || scene.mask_url),
            (scene.map || scene.zone_points) ? loadPerspective() : Promise.resolve(null),
        ]);

        if (!backgroundImage || !Perspective) {
            drawContainedImage(ctx, flatCanvas, width, height);
            return flatCanvas;
        }

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

    async renderFlat(fields, fontKey, fieldFonts = {}) {
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
        const fontScale = Math.max(1, width / Math.max(1, Number(this.template.editor_canvas_width || 980)));

        sortedLayers.forEach((field) => {
            const layer = buildFieldLayer({ ...field, font_scale: fontScale }, width, height);
            const selectedFontKey = fieldFonts[field.name] ?? fieldFonts[field.field_key] ?? fontKey;
            const selectedFont = this.fontConfig(selectedFontKey);
            const fontStyle = {
                fontFamily: field.settings?.font_family_override || selectedFont.css_family || 'serif',
                fontWeight: field.settings?.font_weight || selectedFont.font_weight || '600',
                fontStyle: field.settings?.font_style || selectedFont.font_style || 'normal',
                lineHeight: Number(selectedFont.line_height || field.line_height || 1.2),
                letterSpacing: Number(selectedFont.letter_spacing ?? field.letter_spacing ?? 0),
                textTransform: field.settings?.text_transform || selectedFont.text_transform || 'none',
                allowMultiline: Boolean(field.settings?.allow_multiline ?? true),
                maxLines: Number(field.settings?.max_lines || 3),
                overflowBehavior: field.settings?.overflow_behavior || 'shrink_then_wrap',
            };
            const fieldKey = field.name ?? field.field_key;
            const fieldValue = fields[fieldKey] ?? fields[field.field_key] ?? fields[field.name];
            const rawText = `${fieldValue || field.default_value || field.preview_sample_value || this.template.preview_data_presets?.[fieldKey] || field.placeholder || ''}`.trim();
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
            const boxTop = y - (layer.heightPx / 2);
            const startY = boxTop + Math.max(4, (layer.heightPx - totalHeight) / 2) + (fontSize * 0.82);
            const align = field.text_align === 'start' ? 'left' : (field.text_align === 'end' ? 'right' : 'center');
            const drawX = align === 'left' ? boxLeft + 6 : (align === 'right' ? boxLeft + layer.widthPx - 6 : x);

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
        activePreviewIndex: 0,
        activeImage: 0,
        fields: cloneData(config.fields ?? {}),
        fieldFonts: cloneData(config.fieldFonts ?? {}),
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

            if (this.hasFlatPreview && this.activePreviewIndex === 0) {
                return 'Flat certificate preview';
            }

            return config.mockups?.[this.activeMockup]?.name ?? 'Selected mockup preview';
        },
        get previewPositionLabel() {
            if (this.isCustomizable) {
                return `${this.activePreviewIndex + 1} / ${this.previewCount}`;
            }

            return `${this.activeImage + 1} / ${Math.max(1, this.generalImageCount)}`;
        },
        switchMode(mode) {
            if (mode === 'flat') {
                this.selectPreview(0);
                return;
            }

            this.selectPreview(Math.max(1, this.activePreviewIndex || 1));
        },
        selectMockup(index) {
            this.activeMockup = index;
            this.mode = 'mockup';
            this.activePreviewIndex = index + 1;
            this.renderPreview();
        },
        selectPreview(index) {
            if (!this.isCustomizable) {
                this.selectImage(index);
                return;
            }

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

            this.renderPreview();
        },
        selectImage(index) {
            this.activeImage = index;
        },
        nextPreview() {
            if (this.isCustomizable) {
                if (this.previewCount <= 1) {
                    return;
                }

                this.selectPreview((this.activePreviewIndex + 1) % this.previewCount);
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

                this.selectPreview((this.activePreviewIndex - 1 + this.previewCount) % this.previewCount);
                return;
            }

            if (this.generalImageCount <= 1) {
                return;
            }

            this.selectImage((this.activeImage - 1 + this.generalImageCount) % this.generalImageCount);
        },
        primaryFontId() {
            const selectedFont = Object.values(this.fieldFonts ?? {}).find((value) => `${value ?? ''}`.trim().length > 0);

            return selectedFont || this.activeFont || '';
        },
        setFieldFont(fieldKey, fontId) {
            this.fieldFonts[fieldKey] = `${fontId}`;
            this.activeFont = this.primaryFontId();
            this.renderPreview();
        },
        async renderPreview() {
            if (!this.isCustomizable) {
                return;
            }

            this.previewReady = false;
            await window.NikahPreview.render(this.fields, this.activeFont, this.activeMockup, this.mode, this.fieldFonts);
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

                    this.activeFont = this.primaryFontId() || config.activeFont || null;
                    const defaultMockupIndex = (config.mockups ?? []).findIndex((mockup) => `${mockup.id}` === `${config.defaultMockupId}`);

                    if ((config.galleryDefaultSource === 'selected_mockup' || !this.hasFlatPreview) && defaultMockupIndex >= 0) {
                        this.activeMockup = defaultMockupIndex;
                        this.activePreviewIndex = defaultMockupIndex + (this.hasFlatPreview ? 1 : 0);
                        this.mode = 'mockup';
                    } else if (!this.hasFlatPreview && (config.mockups?.length ?? 0) > 0) {
                        this.activeMockup = 0;
                        this.activePreviewIndex = 0;
                        this.mode = 'mockup';
                    }

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
