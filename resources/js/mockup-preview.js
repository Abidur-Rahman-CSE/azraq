let perspectiveLoader = null;

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

function getContainGeometry(image, width, height) {
    const sourceRatio = image.naturalWidth / image.naturalHeight;
    const targetRatio = width / height;

    let drawWidth = width;
    let drawHeight = height;
    let offsetX = 0;
    let offsetY = 0;

    if (sourceRatio > targetRatio) {
        drawWidth = width;
        drawHeight = drawWidth / sourceRatio;
        offsetY = (height - drawHeight) / 2;
    } else {
        drawHeight = height;
        drawWidth = drawHeight * sourceRatio;
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
    const baseWidth = ctx.measureText(text).width;

    return baseWidth + Math.max(0, text.length - 1) * spacing;
}

function wrapTextLines(ctx, text, maxWidth, letterSpacing) {
    const paragraphs = `${text ?? ''}`.split(/\r?\n/);
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

function fitTextBlock(ctx, text, layer, fontFamily) {
    const width = Math.max(40, layer.widthPx);
    const height = Math.max(24, layer.heightPx);
    const minSize = Math.max(8, Number(layer.font_size_min || 12));
    const maxSize = Math.max(minSize, Number(layer.font_size_max || 24));
    const lineHeight = Math.max(1, Number(layer.line_height || 1.2));
    const letterSpacing = Number(layer.letter_spacing || 0);

    for (let fontSize = maxSize; fontSize >= minSize; fontSize -= 1) {
        ctx.font = `${fontSize}px ${fontFamily}`;
        const lines = wrapTextLines(ctx, text, width, letterSpacing);
        const tallestLine = fontSize * lineHeight * lines.length;
        const widestLine = Math.max(...lines.map((line) => measureTextWithSpacing(ctx, line, letterSpacing)));

        if (tallestLine <= height && widestLine <= width) {
            return { fontSize, lines };
        }
    }

    ctx.font = `${minSize}px ${fontFamily}`;

    return {
        fontSize: minSize,
        lines: wrapTextLines(ctx, text, width, letterSpacing),
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

function rotatePoints(points, angleDegrees) {
    if (!angleDegrees) {
        return points;
    }

    const angle = (angleDegrees * Math.PI) / 180;
    const centerX = points.reduce((sum, point) => sum + point[0], 0) / points.length;
    const centerY = points.reduce((sum, point) => sum + point[1], 0) / points.length;

    return points.map(([x, y]) => {
        const dx = x - centerX;
        const dy = y - centerY;

        return [
            centerX + (dx * Math.cos(angle)) - (dy * Math.sin(angle)),
            centerY + (dx * Math.sin(angle)) + (dy * Math.cos(angle)),
        ];
    });
}

async function loadImage(url, cache) {
    if (!url) {
        return null;
    }

    if (cache.has(url)) {
        return cache.get(url);
    }

    const promise = new Promise((resolve, reject) => {
        const image = new Image();
        image.decoding = 'async';
        image.onload = () => resolve(image);
        image.onerror = reject;
        image.src = url;
    });

    cache.set(url, promise);

    return promise;
}

function buildFieldLayer(field, width, height) {
    return {
        ...field,
        widthPx: (width * Number(field.width || 50)) / 100,
        heightPx: (height * Number(field.height || 8)) / 100,
    };
}

export function registerMockupPreview(Alpine) {
    Alpine.data('nikahMockupPreview', (config) => ({
        galleryItems: config.galleryItems ?? [],
        activeSlideId: config.activeSlideId,
        selectedMockupId: config.selectedMockupId,
        selectedFont: config.selectedFont,
        sceneFont: config.selectedFont,
        fields: cloneData(config.fields),
        sceneFields: cloneData(config.fields),
        fonts: config.fonts ?? [],
        templateFields: config.templateFields ?? [],
        templateImageUrl: config.templateImageUrl ?? '',
        sceneRefreshTimer: null,
        imageCache: new Map(),
        renderFrame: null,
        resizeObserver: null,
        activeSlide() {
            return this.galleryItems.find((item) => item.id === this.activeSlideId) ?? this.galleryItems[0] ?? null;
        },
        fontFamily(id) {
            return this.fonts.find((item) => item.id == id)?.font_family ?? 'Poppins, sans-serif';
        },
        selectSlide(id) {
            this.activeSlideId = id;
            const slide = this.galleryItems.find((item) => item.id === id);

            if (slide?.kind === 'mockup' && slide.mockup_id) {
                this.selectedMockupId = slide.mockup_id;
            }

            this.queueMockupRender();
        },
        selectMockup(id) {
            this.selectedMockupId = id;
            this.activeSlideId = `mockup-${id}`;
            this.queueMockupRender();
        },
        scheduleSceneRefresh() {
                window.clearTimeout(this.sceneRefreshTimer);
                this.sceneRefreshTimer = window.setTimeout(() => {
                    this.sceneFields = cloneData(this.fields);
                    this.sceneFont = this.selectedFont;
                    this.queueMockupRender();
                }, 140);
        },
        flushSceneRefresh() {
            window.clearTimeout(this.sceneRefreshTimer);
            this.sceneFields = cloneData(this.fields);
            this.sceneFont = this.selectedFont;
            this.queueMockupRender();
        },
        queueMockupRender() {
            window.cancelAnimationFrame(this.renderFrame);
            this.renderFrame = window.requestAnimationFrame(() => {
                this.renderMockupCanvas().catch(() => {});
            });
        },
        async renderFlatCertificate() {
            const templateImage = await loadImage(this.templateImageUrl, this.imageCache);

            if (!templateImage) {
                return null;
            }

            const width = templateImage.naturalWidth || templateImage.width;
            const height = templateImage.naturalHeight || templateImage.height;
            const canvas = createCanvas(width, height);
            const ctx = canvas.getContext('2d');

            ctx.drawImage(templateImage, 0, 0, width, height);

            const sortedLayers = [...this.templateFields].sort((left, right) => Number(left.z_index || 1) - Number(right.z_index || 1));
            const fontFamily = this.fontFamily(this.sceneFont);

            sortedLayers.forEach((field) => {
                const layer = buildFieldLayer(field, width, height);
                const text = `${this.sceneFields[field.field_key] || field.placeholder || ''}`.trim();

                if (!text) {
                    return;
                }

                const x = (width * Number(field.position_x || 50)) / 100;
                const y = (height * Number(field.position_y || 50)) / 100;
                const { fontSize, lines } = fitTextBlock(ctx, text, layer, fontFamily);
                const lineHeight = Math.max(1, Number(field.line_height || 1.2));
                const totalHeight = fontSize * lineHeight * lines.length;
                const boxLeft = x - (layer.widthPx / 2);
                const startY = y - (totalHeight / 2) + fontSize;
                const align = field.text_align === 'start' ? 'left' : (field.text_align === 'end' ? 'right' : 'center');
                const drawX = align === 'left' ? boxLeft : (align === 'right' ? boxLeft + layer.widthPx : x);

                ctx.save();
                ctx.translate(x, y);
                ctx.rotate((Number(field.rotation || 0) * Math.PI) / 180);
                ctx.translate(-x, -y);
                ctx.fillStyle = field.text_color || '#780000';
                ctx.textBaseline = 'alphabetic';
                ctx.textAlign = align;
                ctx.font = `${fontSize}px ${fontFamily}`;

                lines.forEach((line, index) => {
                    const lineY = startY + (index * fontSize * lineHeight);
                    drawLineWithSpacing(ctx, line, drawX, lineY, align, field.letter_spacing || 0);
                });

                ctx.restore();
            });

            return canvas;
        },
        async renderMockupCanvas() {
            const slide = this.activeSlide();

            if (!slide || slide.kind !== 'mockup' || !slide.map || !this.$refs.mockupStage || !this.$refs.mockupCanvas) {
                return;
            }

            const stage = this.$refs.mockupStage;
            const canvas = this.$refs.mockupCanvas;
            const width = Math.max(1, Math.round(stage.clientWidth));
            const height = Math.max(1, Math.round(stage.clientHeight));

            if (!width || !height) {
                return;
            }

            const devicePixelRatio = window.devicePixelRatio || 1;
            canvas.width = Math.round(width * devicePixelRatio);
            canvas.height = Math.round(height * devicePixelRatio);
            canvas.style.width = `${width}px`;
            canvas.style.height = `${height}px`;

            const ctx = canvas.getContext('2d');
            ctx.setTransform(devicePixelRatio, 0, 0, devicePixelRatio, 0, 0);
            ctx.clearRect(0, 0, width, height);

            const [baseImage, overlayImage, maskImage, flatCanvas, Perspective] = await Promise.all([
                loadImage(slide.scene, this.imageCache),
                loadImage(slide.overlay, this.imageCache),
                loadImage(slide.mask, this.imageCache),
                this.renderFlatCertificate(),
                loadPerspective(),
            ]);

            if (!baseImage || !flatCanvas || !Perspective) {
                return;
            }

            drawContainedImage(ctx, baseImage, width, height);

            const { drawWidth, drawHeight, offsetX, offsetY } = getContainGeometry(baseImage, width, height);

            const points = rotatePoints([
                [offsetX + (slide.map.top_left_x * drawWidth), offsetY + (slide.map.top_left_y * drawHeight)],
                [offsetX + (slide.map.top_right_x * drawWidth), offsetY + (slide.map.top_right_y * drawHeight)],
                [offsetX + (slide.map.bottom_right_x * drawWidth), offsetY + (slide.map.bottom_right_y * drawHeight)],
                [offsetX + (slide.map.bottom_left_x * drawWidth), offsetY + (slide.map.bottom_left_y * drawHeight)],
            ], Number(slide.map.manual_rotation || 0));

            ctx.save();
            ctx.globalAlpha = clamp(Number(slide.map.opacity ?? 0.95), 0.1, 1);
            const perspective = new Perspective(ctx, flatCanvas);
            perspective.draw(points);
            ctx.restore();

            if (overlayImage) {
                ctx.save();
                ctx.globalAlpha = clamp(Number(slide.map.highlight_strength ?? 0.16), 0.12, 1);
                drawContainedImage(ctx, overlayImage, width, height);
                ctx.restore();
            }

            if (maskImage) {
                ctx.save();
                ctx.globalAlpha = clamp(Number(slide.map.highlight_strength ?? 0.16) * 0.9, 0.12, 1);
                drawContainedImage(ctx, maskImage, width, height);
                ctx.restore();
            }
        },
        init() {
            this.resizeObserver = new ResizeObserver(() => this.queueMockupRender());

            if (this.$refs.mockupStage) {
                this.resizeObserver.observe(this.$refs.mockupStage);
            }

            this.queueMockupRender();
        },
        destroy() {
            window.cancelAnimationFrame(this.renderFrame);
            window.clearTimeout(this.sceneRefreshTimer);
            this.resizeObserver?.disconnect();
        },
    }));
}
