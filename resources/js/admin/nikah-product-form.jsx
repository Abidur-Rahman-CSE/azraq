import React, { useEffect, useRef, useState } from 'react';
import { createRoot } from 'react-dom/client';

const ADVANCED_TYPE = 'advanced_personalized';
const GENERAL_DEFAULT_TYPE = 'standard';
const META_TITLE_SUFFIX = ' | Azraq Bridal';
const STATUS_OPTIONS = [
    { value: 'draft', label: 'Draft' },
    { value: 'active', label: 'Published' },
    { value: 'archived', label: 'Archived' },
];
const EXISTING_IMAGE_LABELS = ['front', 'detail', 'lifestyle', 'mockup', 'size-guide', 'gallery'];
let perspectiveModuleLoader = null;
const stagePreviewImageCache = new Map();
const DEFAULT_FIELDS = [
    { label: 'Bride name', field_key: 'bride_name', type: 'text', is_required: true },
    { label: 'Groom name', field_key: 'groom_name', type: 'text', is_required: true },
    { label: 'Nikah date', field_key: 'nikah_date', type: 'date', is_required: true },
];

function createCanvas(width, height) {
    const canvas = document.createElement('canvas');
    canvas.width = Math.max(1, Math.round(width));
    canvas.height = Math.max(1, Math.round(height));

    return canvas;
}

function clamp(value, min, max) {
    return Math.min(max, Math.max(min, value));
}

function getContainGeometry(image, width, height) {
    const sourceRatio = image.naturalWidth / Math.max(1, image.naturalHeight);
    const targetRatio = width / Math.max(1, height);

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

    return { drawWidth, drawHeight, offsetX, offsetY };
}

async function loadPerspectiveModule() {
    if (!perspectiveModuleLoader) {
        perspectiveModuleLoader = import('perspectivejs').then((module) => module.default ?? module);
    }

    return perspectiveModuleLoader;
}

async function loadStageImage(url) {
    if (!url) {
        return null;
    }

    if (stagePreviewImageCache.has(url)) {
        return stagePreviewImageCache.get(url);
    }

    const promise = new Promise((resolve, reject) => {
        const image = new Image();
        image.decoding = 'async';
        image.onload = () => resolve(image);
        image.onerror = reject;
        image.src = url;
    });

    stagePreviewImageCache.set(url, promise);

    return promise;
}

function createMediaPreviews(fileList) {
    return Array.from(fileList || [])
        .filter((file) => file?.type?.startsWith('image/'))
        .map((file, index) => ({
            id: `${file.name}-${file.lastModified}-${index}`,
            name: file.name,
            url: URL.createObjectURL(file),
        }));
}

function revokeMediaPreviews(previews) {
    previews.forEach((preview) => URL.revokeObjectURL(preview.url));
}

function getZonePoints(map, geometry) {
    if (!map || !geometry) {
        return [];
    }

    return [
        [geometry.offsetX + (Number(map.top_left_x || 0) * geometry.drawWidth), geometry.offsetY + (Number(map.top_left_y || 0) * geometry.drawHeight)],
        [geometry.offsetX + (Number(map.top_right_x || 0) * geometry.drawWidth), geometry.offsetY + (Number(map.top_right_y || 0) * geometry.drawHeight)],
        [geometry.offsetX + (Number(map.bottom_right_x || 0) * geometry.drawWidth), geometry.offsetY + (Number(map.bottom_right_y || 0) * geometry.drawHeight)],
        [geometry.offsetX + (Number(map.bottom_left_x || 0) * geometry.drawWidth), geometry.offsetY + (Number(map.bottom_left_y || 0) * geometry.drawHeight)],
    ];
}

function MockupStagePreview({
    sceneUrl,
    overlayUrl,
    maskUrl,
    certificateUrl,
    map,
    title,
    compact = false,
}) {
    const stageRef = useRef(null);
    const canvasRef = useRef(null);
    const [zoneOverlay, setZoneOverlay] = useState({ points: [], label: null, hasZone: false });

    useEffect(() => {
        let cancelled = false;
        const stage = stageRef.current;
        const canvas = canvasRef.current;

        if (!stage || !canvas) {
            return undefined;
        }

        const renderPreview = async () => {
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

            try {
                const [sceneImage, overlayImage, maskImage, certificateImage, Perspective] = await Promise.all([
                    loadStageImage(sceneUrl),
                    loadStageImage(overlayUrl),
                    loadStageImage(maskUrl),
                    loadStageImage(certificateUrl),
                    map && certificateUrl ? loadPerspectiveModule() : Promise.resolve(null),
                ]);

                if (cancelled || !sceneImage) {
                    return;
                }

                const geometry = drawContainedImage(ctx, sceneImage, width, height);
                const zonePoints = getZonePoints(map, geometry);

                if (zonePoints.length === 4 && certificateImage && Perspective) {
                    ctx.save();
                    ctx.globalAlpha = clamp(Number(map?.opacity ?? 0.96), 0.1, 1);
                    const perspective = new Perspective(ctx, certificateImage);
                    perspective.draw(zonePoints);
                    ctx.restore();
                }

                if (overlayImage) {
                    ctx.save();
                    ctx.globalAlpha = clamp(Number(map?.highlight_strength ?? 0.14), 0.12, 1);
                    drawContainedImage(ctx, overlayImage, width, height);
                    ctx.restore();
                }

                if (maskImage) {
                    ctx.save();
                    ctx.globalAlpha = clamp(Number(map?.highlight_strength ?? 0.14) * 0.9, 0.12, 1);
                    drawContainedImage(ctx, maskImage, width, height);
                    ctx.restore();
                }

                if (!cancelled) {
                    if (zonePoints.length === 4) {
                        const xs = zonePoints.map((point) => point[0]);
                        const ys = zonePoints.map((point) => point[1]);
                        setZoneOverlay({
                            points: zonePoints,
                            hasZone: true,
                            label: {
                                left: Math.min(...xs) + 10,
                                top: Math.min(...ys) + 10,
                            },
                        });
                    } else {
                        setZoneOverlay({ points: [], label: null, hasZone: false });
                    }
                }
            } catch (error) {
                if (!cancelled) {
                    setZoneOverlay({ points: [], label: null, hasZone: false });
                }
            }
        };

        const resizeObserver = new ResizeObserver(() => {
            renderPreview().catch(() => {});
        });

        resizeObserver.observe(stage);
        renderPreview().catch(() => {});

        return () => {
            cancelled = true;
            resizeObserver.disconnect();
        };
    }, [certificateUrl, map, maskUrl, overlayUrl, sceneUrl]);

    return (
        <div ref={stageRef} className={`nikah-stage ${compact ? 'nikah-stage--sidebar' : ''}`}>
            <canvas ref={canvasRef} className="nikah-stage__canvas" aria-label={title || 'Mockup preview canvas'} />
            <div className="nikah-stage__hud" aria-hidden="true">
                {zoneOverlay.hasZone && zoneOverlay.points.length === 4 ? (
                    <>
                        <svg className="nikah-stage__hud-svg" viewBox={`0 0 ${Math.max(1, Math.round(stageRef.current?.clientWidth || 1))} ${Math.max(1, Math.round(stageRef.current?.clientHeight || 1))}`} preserveAspectRatio="none">
                            <polygon
                                className="nikah-stage__zone-polygon"
                                points={zoneOverlay.points.map((point) => point.join(',')).join(' ')}
                            />
                        </svg>
                        {zoneOverlay.label ? (
                            <span
                                className="nikah-stage__zone-badge"
                                style={{ left: `${zoneOverlay.label.left}px`, top: `${zoneOverlay.label.top}px` }}
                            >
                                Zone defined ✓
                            </span>
                        ) : null}
                    </>
                ) : (
                    <span className="nikah-stage__zone-badge nikah-stage__zone-badge--missing">Zone missing</span>
                )}
            </div>
        </div>
    );
}

function uid(prefix) {
    return `${prefix}-${Date.now()}-${Math.random().toString(16).slice(2)}`;
}

function inferFieldType(field) {
    const explicitType = field.type || field.field_type || field.input_type || field.settings?.input_type;

    if (explicitType) {
        return explicitType;
    }

    const key = `${field.field_key || field.key || field.label || ''}`.toLowerCase();

    if (key.includes('date') || key.includes('day')) {
        return 'date';
    }

    return 'text';
}

function normalizeField(field, index = 0) {
    const label = field.label || field.name || `Field ${index + 1}`;
    const keyBase = field.field_key || field.key || label.toLowerCase().replace(/[^a-z0-9]+/g, '_');

    return {
        id: field.id || uid('field'),
        label,
        field_key: keyBase || `field_${index + 1}`,
        type: inferFieldType(field),
        is_required: Boolean(field.is_required ?? field.required ?? false),
    };
}

function fieldsFromDesign(design) {
    const mappedFields = (design?.fields || []).map((field, index) => normalizeField(field, index));

    if (mappedFields.length) {
        return mappedFields;
    }

    return DEFAULT_FIELDS.map((field, index) => normalizeField(field, index));
}

function resolvedFields(design, existingFields = []) {
    if ((existingFields || []).length) {
        return existingFields.map((field, index) => normalizeField(field, index));
    }

    if (! design) {
        return [];
    }

    return fieldsFromDesign(design);
}

function getError(errors, key) {
    return errors?.[key]?.[0] || '';
}

function slugifyValue(value) {
    return `${value || ''}`
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function skuifyValue(value) {
    return `${value || ''}`
        .toUpperCase()
        .trim()
        .replace(/[^A-Z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function truncateText(value, maxLength) {
    const normalizedValue = `${value || ''}`.replace(/\s+/g, ' ').trim();

    if (normalizedValue.length <= maxLength) {
        return normalizedValue;
    }

    return `${normalizedValue.slice(0, Math.max(0, maxLength - 1)).trim()}…`;
}

function buildMetaTitle(value) {
    const normalizedValue = `${value || ''}`.replace(/\s+/g, ' ').trim();

    if (!normalizedValue) {
        return '';
    }

    const maxNameLength = Math.max(0, 60 - META_TITLE_SUFFIX.length);
    const trimmedName = truncateText(normalizedValue, maxNameLength);

    return `${trimmedName}${META_TITLE_SUFFIX}`;
}

function formatPrice(value) {
    const numericValue = Number(value || 0);

    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        maximumFractionDigits: 2,
    }).format(Number.isFinite(numericValue) ? numericValue : 0);
}

function getZoneBox(mockup) {
    const map = mockup?.map;

    if (!map) {
        return {
            left: 24,
            top: 18,
            width: 52,
            height: 66,
            hasZone: false,
        };
    }

    const xs = [map.top_left_x, map.top_right_x, map.bottom_right_x, map.bottom_left_x].map(Number);
    const ys = [map.top_left_y, map.top_right_y, map.bottom_right_y, map.bottom_left_y].map(Number);

    return {
        left: Math.min(...xs) * 100,
        top: Math.min(...ys) * 100,
        width: (Math.max(...xs) - Math.min(...xs)) * 100,
        height: (Math.max(...ys) - Math.min(...ys)) * 100,
        hasZone: true,
    };
}

function Breadcrumbs({ items }) {
    return (
        <div className="nikah-form__breadcrumbs">
            {items.map((crumb, index) => (
                <React.Fragment key={`${crumb.label}-${index}`}>
                    {index > 0 ? <span>/</span> : null}
                    {crumb.href ? <a href={crumb.href}>{crumb.label}</a> : <span className="is-current">{crumb.label}</span>}
                </React.Fragment>
            ))}
        </div>
    );
}

function normalizeVariant(variant, index = 0) {
    const rawOptions = Array.isArray(variant.option_values)
        ? variant.option_values
        : `${variant.option_values || ''}`
            .split(',')
            .map((value) => value.trim())
            .filter(Boolean);

    const options = rawOptions.length
        ? rawOptions.map((entry, optionIndex) => {
            const [rawName, ...rawValueParts] = entry.split(':');
            const hasNamedOption = rawValueParts.length > 0;

            return {
                id: uid(`variant-option-${optionIndex + 1}`),
                name: hasNamedOption ? rawName.replace(/_/g, ' ') : `Option ${optionIndex + 1}`,
                value: hasNamedOption ? rawValueParts.join(':').trim() : rawName.trim(),
            };
        })
        : [];

    return {
        id: variant.id || uid(`variant-${index + 1}`),
        name: variant.name || '',
        sku: variant.sku || '',
        options,
        price: variant.price || '',
        compare_at_price: variant.compare_at_price || '',
        stock_quantity: variant.stock_quantity || '0',
        is_default: Boolean(variant.is_default),
    };
}

function serializeVariantOptions(options = []) {
    return options
        .filter((option) => option.name.trim() || option.value.trim())
        .filter((option) => option.name.trim() && option.value.trim())
        .map((option) => `${option.name.toLowerCase().trim().replace(/[^a-z0-9]+/g, '_')}:${option.value.trim()}`)
        .join(', ');
}

function variantMediaKey(name, value) {
    const key = `${name || ''}`.toLowerCase().trim().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
    const cleanValue = `${value || ''}`.trim();

    return key && cleanValue ? `${key}:${cleanValue}` : '';
}

function variantMediaTargets(variants = []) {
    const groups = new Map();

    variants.forEach((variant) => {
        (variant.options || []).forEach((option) => {
            const key = variantMediaKey(option.name, option.value);
            const groupKey = `${option.name || ''}`.toLowerCase().trim().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');

            if (!key || !groupKey) {
                return;
            }

            if (!groups.has(groupKey)) {
                groups.set(groupKey, {
                    key: groupKey,
                    label: option.name,
                    targets: new Map(),
                });
            }

            const group = groups.get(groupKey);

            if (!group.targets.has(key)) {
                group.targets.set(key, {
                    key,
                    label: option.value,
                    fullLabel: `${option.name}: ${option.value}`,
                });
            }
        });
    });

    return Array.from(groups.values()).map((group) => ({
        ...group,
        targets: Array.from(group.targets.values()),
    }));
}

function createVariantOption(name = '', value = '') {
    return {
        id: uid('variant-option'),
        name,
        value,
    };
}

function normalizeVariantGroup(group, index = 0) {
    return {
        id: group.id || uid(`variant-group-${index + 1}`),
        name: group.name || '',
        valuesText: group.valuesText || '',
    };
}

function parseVariantGroupValues(valuesText) {
    return `${valuesText || ''}`
        .split(',')
        .map((value) => value.trim())
        .filter(Boolean);
}

function buildVariantSignature(options = []) {
    return options
        .map((option) => `${option.name.toLowerCase().trim()}:${option.value.toLowerCase().trim()}`)
        .sort()
        .join('|');
}

function buildVariantName(options = []) {
    return options
        .map((option) => option.value.trim())
        .filter(Boolean)
        .join(' / ');
}

function buildVariantSku(baseSku, options = []) {
    const prefix = skuifyValue(baseSku || 'AZR');
    const suffix = skuifyValue(options.map((option) => option.value).join('-'));

    return suffix ? `${prefix}-${suffix}` : prefix;
}

function inferVariantGroupsFromVariants(variants = []) {
    const groups = new Map();

    variants.forEach((variant) => {
        (variant.options || []).forEach((option) => {
            const key = option.name.toLowerCase().trim();

            if (!groups.has(key)) {
                groups.set(key, {
                    name: option.name,
                    values: new Set(),
                });
            }

            if (option.value.trim()) {
                groups.get(key).values.add(option.value.trim());
            }
        });
    });

    return Array.from(groups.values()).map((group, index) => normalizeVariantGroup({
        name: group.name,
        valuesText: Array.from(group.values).join(', '),
    }, index));
}

function cartesianProduct(arrays) {
    return arrays.reduce((accumulator, currentArray) => (
        accumulator.flatMap((item) => currentArray.map((value) => [...item, value]))
    ), [[]]);
}

function buildVariantsFromGroups(groups, currentVariants, baseSku) {
    const validGroups = groups
        .map((group) => ({
            name: group.name.trim(),
            values: parseVariantGroupValues(group.valuesText),
        }))
        .filter((group) => group.name && group.values.length);

    if (!validGroups.length) {
        return currentVariants;
    }

    const existingBySignature = new Map(
        currentVariants.map((variant) => [buildVariantSignature(variant.options), variant]),
    );

    const combinations = cartesianProduct(validGroups.map((group) => group.values))
        .map((values) => values.map((value, index) => ({
            id: uid(`variant-option-${index + 1}`),
            name: validGroups[index].name,
            value,
        })));

    const nextVariants = combinations.map((options, index) => {
        const signature = buildVariantSignature(options);
        const existingVariant = existingBySignature.get(signature);

        return normalizeVariant({
            id: existingVariant?.id,
            name: existingVariant?.name || buildVariantName(options),
            sku: existingVariant?.sku || buildVariantSku(baseSku, options),
            option_values: serializeVariantOptions(options),
            price: existingVariant?.price || '',
            compare_at_price: existingVariant?.compare_at_price || '',
            stock_quantity: existingVariant?.stock_quantity || '0',
            is_default: existingVariant?.is_default || false,
        }, index);
    });

    if (nextVariants.length && !nextVariants.some((variant) => variant.is_default)) {
        nextVariants[0] = { ...nextVariants[0], is_default: true };
    }

    return nextVariants;
}

function SearchableMultiSelect({
    label,
    placeholder,
    options,
    selectedValues,
    onToggle,
}) {
    const [isOpen, setIsOpen] = useState(false);
    const [search, setSearch] = useState('');
    const containerRef = useRef(null);

    useEffect(() => {
        function handleClickOutside(event) {
            if (containerRef.current && !containerRef.current.contains(event.target)) {
                setIsOpen(false);
            }
        }

        document.addEventListener('mousedown', handleClickOutside);

        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, []);

    const normalizedSearch = search.trim().toLowerCase();
    const filteredOptions = normalizedSearch
        ? options.filter((option) => option.name.toLowerCase().includes(normalizedSearch))
        : options;
    const selectedOptions = options.filter((option) => selectedValues.includes(option.id));

    return (
        <div className="nikah-multiselect" ref={containerRef}>
            <span className="nikah-multiselect__label">{label}</span>

            <div className="nikah-multiselect__capsules">
                {selectedOptions.length ? selectedOptions.map((option) => (
                    <button
                        key={option.id}
                        type="button"
                        className="nikah-multiselect__capsule"
                        onClick={() => onToggle(option.id)}
                    >
                        <span>{option.name}</span>
                        <span className="nikah-multiselect__capsule-remove">×</span>
                    </button>
                )) : (
                    <div className="nikah-empty-note">Nothing selected yet.</div>
                )}
            </div>

            <button
                type="button"
                className={`nikah-multiselect__trigger ${isOpen ? 'is-open' : ''}`}
                onClick={() => setIsOpen((current) => !current)}
            >
                <span>{placeholder}</span>
                <span>{isOpen ? '−' : '+'}</span>
            </button>

            {isOpen ? (
                <div className="nikah-multiselect__panel">
                    <input
                        type="text"
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        className="nikah-multiselect__search"
                        placeholder="Search..."
                    />

                    <div className="nikah-multiselect__options">
                        {filteredOptions.length ? filteredOptions.map((option) => {
                            const isSelected = selectedValues.includes(option.id);

                            return (
                                <button
                                    key={option.id}
                                    type="button"
                                    className={`nikah-multiselect__option ${isSelected ? 'is-selected' : ''}`}
                                    onClick={() => onToggle(option.id)}
                                >
                                    <span>{option.name}</span>
                                    <span>{isSelected ? 'Selected' : 'Add'}</span>
                                </button>
                            );
                        }) : (
                            <div className="nikah-empty-note">No matches found.</div>
                        )}
                    </div>
                </div>
            ) : null}
        </div>
    );
}

function ExistingImageRow({ image }) {
    return (
        <article className="general-media-row">
            <div className="general-media-row__thumb">
                <img src={image.image_url} alt={image.alt_text || 'Product image'} />
            </div>
            <div className="general-media-row__body">
                <div className="general-media-row__summary">
                    <strong>{image.label || 'gallery'}</strong>
                    {image.is_primary ? <span>Primary image</span> : <span>Gallery image</span>}
                </div>

                <label className="nikah-field">
                    <span>Label</span>
                    <select name={`existing_images[${image.id}][label]`} defaultValue={image.label || 'gallery'}>
                        {EXISTING_IMAGE_LABELS.map((label) => (
                            <option key={label} value={label}>{label}</option>
                        ))}
                    </select>
                </label>

                <div className="general-media-row__actions">
                    <label className="general-checkbox">
                        <input type="hidden" name={`existing_images[${image.id}][is_primary]`} value="0" />
                        <input type="checkbox" name={`existing_images[${image.id}][is_primary]`} value="1" defaultChecked={image.is_primary} />
                        Primary
                    </label>

                    <label className="general-checkbox general-checkbox--danger">
                        <input type="hidden" name={`existing_images[${image.id}][remove]`} value="0" />
                        <input type="checkbox" name={`existing_images[${image.id}][remove]`} value="1" />
                        Remove
                    </label>
                </div>

                <details className="general-media-row__details">
                    <summary>Alt text and order</summary>
                    <div className="general-media-row__fields">
                        <label className="nikah-field">
                            <span>Sort order</span>
                            <input type="number" min="0" name={`existing_images[${image.id}][position]`} defaultValue={image.position ?? 0} />
                        </label>

                        <label className="nikah-field">
                            <span>Alt text</span>
                            <input type="text" name={`existing_images[${image.id}][alt_text]`} defaultValue={image.alt_text || ''} />
                        </label>
                    </div>
                </details>
            </div>
        </article>
    );
}

function SectionTabs({ tabs, activeTab, onChange }) {
    return (
        <div className="editor-section-tabs" role="tablist" aria-label="Product form sections">
            {tabs.map((tab) => (
                <button
                    key={tab.id}
                    type="button"
                    role="tab"
                    aria-selected={activeTab === tab.id}
                    className={`editor-section-tabs__tab ${activeTab === tab.id ? 'is-active' : ''}`}
                    onClick={() => onChange(tab.id)}
                >
                    {tab.label}
                </button>
            ))}
        </div>
    );
}

function VariantPlanner({
    groups,
    onUpdateGroup,
    onAddGroup,
    onRemoveGroup,
    onGenerate,
}) {
    return (
        <section className="variant-planner">
            <div className="variant-planner__header">
                <div>
                    <strong>Variant groups</strong>
                    <p>Define attributes first, then generate all combinations automatically.</p>
                </div>
                <button type="button" className="variant-option-add" onClick={onGenerate}>Generate combinations</button>
            </div>

            <div className="variant-group-list">
                {groups.length ? groups.map((group) => (
                    <div key={group.id} className="variant-group-card">
                        <input
                            type="text"
                            value={group.name}
                            onChange={(event) => onUpdateGroup(group.id, 'name', event.target.value)}
                            placeholder="Size / Style / Frame type"
                        />
                        <textarea
                            rows="2"
                            value={group.valuesText}
                            onChange={(event) => onUpdateGroup(group.id, 'valuesText', event.target.value)}
                            placeholder="Small, Medium, Large"
                        />
                        <button type="button" className="variant-option-row__remove" onClick={() => onRemoveGroup(group.id)}>
                            Remove group
                        </button>
                    </div>
                )) : (
                    <div className="nikah-empty-note">No variant groups yet. Start with Size, Style, or Frame type.</div>
                )}
            </div>

            <button type="button" className="variant-option-add" onClick={onAddGroup}>Add group</button>
        </section>
    );
}

function VariantEditor({
    variants,
    manageStock,
    onAddVariant,
    onRemoveVariant,
    onUpdateVariant,
    onSetDefaultVariant,
    onAddVariantOption,
    onUpdateVariantOption,
    onRemoveVariantOption,
    onCopyPricing,
    onPastePricing,
    hasCopiedPricing,
    emptyMessage,
}) {
    const [expandedVariantIds, setExpandedVariantIds] = useState([]);

    useEffect(() => {
        setExpandedVariantIds((currentExpandedIds) => currentExpandedIds.filter((id) => variants.some((variant) => variant.id === id)));
    }, [variants]);

    function toggleExpanded(variantId) {
        setExpandedVariantIds((currentExpandedIds) => (
            currentExpandedIds.includes(variantId)
                ? currentExpandedIds.filter((id) => id !== variantId)
                : [...currentExpandedIds, variantId]
        ));
    }

    return (
        <>
            <div className="general-variant-list">
                {variants.length ? variants.map((variant, index) => (
                    <article key={variant.id} className={`general-variant-card ${expandedVariantIds.includes(variant.id) ? 'is-expanded' : 'is-collapsed'}`}>
                        <div className="general-variant-card__header">
                            <div className="general-variant-card__summary">
                                <div className="general-variant-card__title-wrap">
                                    <strong>{variant.name || `Variant ${index + 1}`}</strong>
                                    {variant.is_default ? <span className="general-variant-card__badge">Default</span> : null}
                                </div>
                                <div className="general-variant-card__meta general-variant-card__meta--inputs">
                                    <label className="general-variant-card__mini-field">
                                        <span>Price</span>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            name={`variants[${index}][price]`}
                                            value={variant.price}
                                            onChange={(event) => onUpdateVariant(variant.id, 'price', event.target.value)}
                                        />
                                    </label>
                                    <label className="general-variant-card__mini-field">
                                        <span>Compare</span>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            name={`variants[${index}][compare_at_price]`}
                                            value={variant.compare_at_price}
                                            onChange={(event) => onUpdateVariant(variant.id, 'compare_at_price', event.target.value)}
                                        />
                                    </label>
                                </div>
                            </div>
                            <div className="general-variant-card__actions">
                                <button
                                    type="button"
                                    className="variant-icon-button variant-icon-button--price"
                                    onClick={() => onCopyPricing(variant.id)}
                                    title="Copy pricing"
                                    aria-label="Copy pricing"
                                >
                                    <span aria-hidden="true">⧉</span>
                                    <span>Price</span>
                                </button>
                                <button
                                    type="button"
                                    className="variant-icon-button variant-icon-button--price"
                                    onClick={() => onPastePricing(variant.id)}
                                    disabled={!hasCopiedPricing}
                                    title="Paste pricing"
                                    aria-label="Paste pricing"
                                >
                                    <span aria-hidden="true">⇪</span>
                                    <span>Price</span>
                                </button>
                                <button
                                    type="button"
                                    className="variant-icon-button"
                                    onClick={() => toggleExpanded(variant.id)}
                                    title={expandedVariantIds.includes(variant.id) ? 'Collapse' : 'Expand'}
                                    aria-label={expandedVariantIds.includes(variant.id) ? 'Collapse' : 'Expand'}
                                >
                                    <span aria-hidden="true">{expandedVariantIds.includes(variant.id) ? '▾' : '▸'}</span>
                                </button>
                                <button
                                    type="button"
                                    className="variant-icon-button variant-icon-button--danger"
                                    onClick={() => onRemoveVariant(variant.id)}
                                    title="Delete"
                                    aria-label="Delete"
                                >
                                    <span aria-hidden="true">✕</span>
                                </button>
                            </div>
                        </div>

                        <div className="nikah-form__grid nikah-form__grid--two" hidden={!expandedVariantIds.includes(variant.id)}>
                            <label className="nikah-field">
                                <span>Variant name</span>
                                <input
                                    type="text"
                                    name={`variants[${index}][name]`}
                                    value={variant.name}
                                    onChange={(event) => onUpdateVariant(variant.id, 'name', event.target.value)}
                                    placeholder="Basic (9 x 13 inch), Framed, Natural Pine Wood"
                                />
                            </label>

                            <label className="nikah-field">
                                <span>Variant SKU</span>
                                <input
                                    type="text"
                                    name={`variants[${index}][sku]`}
                                    value={variant.sku}
                                    onChange={(event) => onUpdateVariant(variant.id, 'sku', event.target.value.toUpperCase())}
                                    placeholder="AZR-BASIC-FRAMED"
                                />
                            </label>

                            <div className="nikah-field nikah-form__span-two">
                                <span>Variant options</span>
                                <div className="variant-option-list">
                                    {variant.options.length ? variant.options.map((option) => (
                                        <div key={option.id} className="variant-option-row">
                                            <input
                                                type="text"
                                                value={option.name}
                                                onChange={(event) => onUpdateVariantOption(variant.id, option.id, 'name', event.target.value)}
                                                placeholder="Size / Style / Frame type"
                                            />
                                            <input
                                                type="text"
                                                value={option.value}
                                                onChange={(event) => onUpdateVariantOption(variant.id, option.id, 'value', event.target.value)}
                                                placeholder="Basic / Framed / Standard"
                                            />
                                            <button type="button" className="variant-option-row__remove" onClick={() => onRemoveVariantOption(variant.id, option.id)}>
                                                Remove
                                            </button>
                                        </div>
                                    )) : (
                                        <div className="nikah-empty-note">No option groups added yet.</div>
                                    )}
                                </div>

                                <button type="button" className="variant-option-add" onClick={() => onAddVariantOption(variant.id)}>
                                    Add option group
                                </button>
                                <input type="hidden" name={`variants[${index}][option_values]`} value={serializeVariantOptions(variant.options)} />
                            </div>

                            {manageStock ? (
                                <label className="nikah-field">
                                    <span>Variant stock</span>
                                    <input
                                        type="number"
                                        min="0"
                                        name={`variants[${index}][stock_quantity]`}
                                        value={variant.stock_quantity}
                                        onChange={(event) => onUpdateVariant(variant.id, 'stock_quantity', event.target.value)}
                                    />
                                </label>
                            ) : (
                                <input type="hidden" name={`variants[${index}][stock_quantity]`} value={variant.stock_quantity} />
                            )}

                            <label className="general-checkbox">
                                <input
                                    type="checkbox"
                                    name={`variants[${index}][is_default]`}
                                    value="1"
                                    checked={variant.is_default}
                                    onChange={() => onSetDefaultVariant(variant.id)}
                                />
                                Default variant
                            </label>
                        </div>
                    </article>
                )) : (
                    <div className="nikah-empty-note">{emptyMessage}</div>
                )}
            </div>

            <button type="button" className="nikah-add-field" onClick={onAddVariant}>Add variant</button>
        </>
    );
}

function VariantMediaMapper({
    variants,
    mediaOptions,
    links,
    onChange,
    title,
    emptyMessage,
    mediaLabel,
}) {
    const [activeTarget, setActiveTarget] = useState(null);
    const groups = variantMediaTargets(variants);
    const hasTargets = groups.some((group) => group.targets.length);
    const selectedIds = activeTarget ? (links[activeTarget.key] || []) : [];

    function toggleMedia(mediaId) {
        if (!activeTarget) {
            return;
        }

        const currentIds = links[activeTarget.key] || [];
        const nextIds = currentIds.includes(mediaId)
            ? currentIds.filter((id) => id !== mediaId)
            : [...currentIds, mediaId];

        onChange({
            ...links,
            [activeTarget.key]: nextIds,
        });
    }

    function selectedLabels(target) {
        const ids = links[target.key] || [];

        if (!ids.length) {
            return `No ${mediaLabel} selected`;
        }

        return ids
            .map((id) => mediaOptions.find((media) => media.id === id)?.label)
            .filter(Boolean)
            .join(', ');
    }

    return (
        <section className="variant-media-mapper">
            <div className="variant-media-mapper__heading">
                <div>
                    <h4>{title}</h4>
                    <p>Map one option value once. Every combination using that value will follow it.</p>
                </div>
            </div>

            {hasTargets && mediaOptions.length ? (
                <div className="variant-media-mapper__groups">
                    {groups.map((group) => (
                        <section key={group.key} className="variant-media-mapper__group">
                            <h5>{group.label}</h5>
                            <div className="variant-media-mapper__list">
                                {group.targets.map((target) => (
                                    <article key={target.key} className="variant-media-mapper__row">
                                        <div>
                                            <strong>{target.label}</strong>
                                            <span>{selectedLabels(target)}</span>
                                        </div>
                                        <button type="button" className="variant-option-add" onClick={() => setActiveTarget(target)}>
                                            Select {mediaLabel}
                                        </button>
                                    </article>
                                ))}
                            </div>
                        </section>
                    ))}
                </div>
            ) : (
                <div className="nikah-empty-note">{emptyMessage}</div>
            )}

            {activeTarget ? (
                <div className="variant-media-modal" role="dialog" aria-modal="true" aria-label={`Select ${mediaLabel}`}>
                    <div className="variant-media-modal__backdrop" onClick={() => setActiveTarget(null)} />
                    <div className="variant-media-modal__panel">
                        <div className="variant-media-modal__header">
                            <div>
                                <h4>{activeTarget.fullLabel}</h4>
                                <p>Select one or more {mediaLabel}.</p>
                            </div>
                            <button type="button" className="variant-icon-button" onClick={() => setActiveTarget(null)} aria-label="Close">
                                <span aria-hidden="true">✕</span>
                            </button>
                        </div>

                        <div className="variant-media-modal__grid">
                            {mediaOptions.map((media) => {
                                const isSelected = selectedIds.includes(media.id);

                                return (
                                    <button
                                        key={media.id}
                                        type="button"
                                        className={`variant-media-tile ${isSelected ? 'is-selected' : ''}`}
                                        onClick={() => toggleMedia(media.id)}
                                    >
                                        <img src={media.thumb} alt={media.label} />
                                        <span>{media.label}</span>
                                    </button>
                                );
                            })}
                        </div>

                        <div className="variant-media-modal__footer">
                            <button type="button" className="button-ghost" onClick={() => onChange({ ...links, [activeTarget.key]: [] })}>
                                Clear selection
                            </button>
                            <button type="button" className="button-primary" onClick={() => setActiveTarget(null)}>
                                Done
                            </button>
                        </div>
                    </div>
                </div>
            ) : null}
        </section>
    );
}

function normalizeBundleItem(item = {}) {
    return {
        id: item.id || uid('bundle-item'),
        child_product_id: item.child_product_id ? `${item.child_product_id}` : '',
        quantity: item.quantity ? `${item.quantity}` : '1',
    };
}

function BundleItemEditor({
    items,
    products,
    onAddItem,
    onRemoveItem,
    onUpdateItem,
}) {
    return (
        <section className="bundle-item-editor">
            <div className="variant-media-mapper__heading">
                <div>
                    <h4>Combo items</h4>
                    <p>Select the products included in this combo and set each quantity.</p>
                </div>
            </div>

            <div className="bundle-item-editor__list">
                {items.length ? items.map((item, index) => (
                    <article key={item.id} className="bundle-item-editor__row">
                        <label className="nikah-field">
                            <span>Included product</span>
                            <select
                                name={`bundle_items[${index}][child_product_id]`}
                                value={item.child_product_id}
                                onChange={(event) => onUpdateItem(item.id, 'child_product_id', event.target.value)}
                            >
                                <option value="">Select product</option>
                                {products.map((product) => (
                                    <option key={product.id} value={product.id}>{product.name}</option>
                                ))}
                            </select>
                        </label>

                        <label className="nikah-field">
                            <span>Quantity</span>
                            <input
                                type="number"
                                min="1"
                                name={`bundle_items[${index}][quantity]`}
                                value={item.quantity}
                                onChange={(event) => onUpdateItem(item.id, 'quantity', event.target.value)}
                            />
                        </label>

                        <button type="button" className="variant-icon-button variant-icon-button--danger" onClick={() => onRemoveItem(item.id)} aria-label="Remove combo item">
                            <span aria-hidden="true">✕</span>
                        </button>
                    </article>
                )) : (
                    <div className="nikah-empty-note">No combo items yet. Add the products customers will receive together.</div>
                )}
            </div>

            <button type="button" className="nikah-add-field" onClick={onAddItem}>Add combo item</button>
        </section>
    );
}

function ServiceMetaEditor({ meta, onUpdate }) {
    return (
        <section className="bundle-item-editor">
            <div className="variant-media-mapper__heading">
                <div>
                    <h4>Service details</h4>
                    <p>These details power the booking product page and booking request rules.</p>
                </div>
            </div>

            <div className="nikah-form__grid nikah-form__grid--two">
                <label className="nikah-field">
                    <span>Service type</span>
                    <input
                        type="text"
                        name="service_meta[service_type]"
                        value={meta.service_type || ''}
                        onChange={(event) => onUpdate('service_type', event.target.value)}
                        placeholder="Mehendi, styling, consultation"
                    />
                </label>

                <label className="nikah-field">
                    <span>Duration label</span>
                    <input
                        type="text"
                        name="service_meta[duration_label]"
                        value={meta.duration_label || ''}
                        onChange={(event) => onUpdate('duration_label', event.target.value)}
                        placeholder="2 hours, half day, full day"
                    />
                </label>

                <label className="nikah-field">
                    <span>Location scope</span>
                    <input
                        type="text"
                        name="service_meta[location_scope]"
                        value={meta.location_scope || ''}
                        onChange={(event) => onUpdate('location_scope', event.target.value)}
                        placeholder="Dhaka city, studio, client venue"
                    />
                </label>

                <label className="nikah-field">
                    <span>Advance amount</span>
                    <input
                        type="number"
                        min="0"
                        step="0.01"
                        name="service_meta[advance_payment_amount]"
                        value={meta.advance_payment_amount || ''}
                        onChange={(event) => onUpdate('advance_payment_amount', event.target.value)}
                    />
                </label>

                <label className="general-checkbox nikah-form__span-two">
                    <input type="hidden" name="service_meta[requires_advance_payment]" value="0" />
                    <input
                        type="checkbox"
                        name="service_meta[requires_advance_payment]"
                        value="1"
                        checked={Boolean(meta.requires_advance_payment)}
                        onChange={(event) => onUpdate('requires_advance_payment', event.target.checked)}
                    />
                    Requires advance payment after confirmation
                </label>

                <label className="nikah-field nikah-form__span-two">
                    <span>Booking notes / service details</span>
                    <textarea
                        name="service_meta[booking_notes]"
                        rows="5"
                        value={meta.booking_notes || ''}
                        onChange={(event) => onUpdate('booking_notes', event.target.value)}
                        placeholder="Explain what is included, preparation requirements, or confirmation process."
                    />
                </label>
            </div>
        </section>
    );
}

function SharedSeoCard({ metaTitle, onMetaTitleChange, metaDescription, onMetaDescriptionChange, errors, step }) {
    return (
        <section className="nikah-step-card">
            <div className="nikah-step-card__heading">
                <span className="nikah-step-card__step">{step}</span>
                <div>
                    <h3>SEO</h3>
                    <p>Control the search result headline and description for this product page.</p>
                </div>
            </div>

            <div className="nikah-form__grid">
                <label className="nikah-field">
                    <span>Meta title</span>
                    <input
                        type="text"
                        name="meta_title"
                        value={metaTitle}
                        onChange={(event) => onMetaTitleChange(event.target.value)}
                        placeholder="Premium Nikahnama | Azraq Bridal"
                    />
                    {getError(errors, 'meta_title') ? <small>{getError(errors, 'meta_title')}</small> : null}
                </label>

                <label className="nikah-field">
                    <span>Meta description</span>
                    <textarea
                        name="meta_description"
                        rows="4"
                        value={metaDescription}
                        onChange={(event) => onMetaDescriptionChange(event.target.value)}
                        placeholder="Describe the product for search engines and social link previews."
                    />
                    {getError(errors, 'meta_description') ? <small>{getError(errors, 'meta_description')}</small> : null}
                </label>
            </div>
        </section>
    );
}

function NikahProductForm({ payload }) {
    const {
        product,
        productTypes,
        categories,
        relatedCategories,
        tags,
        collections,
        relatedProducts,
        existingImages,
        designs,
        mockups,
        errors,
        page,
    } = payload;

    const initialDesignId = product.isNew ? (product.selectedDesignId || '') : (product.selectedDesignId || designs[0]?.id || '');
    const initialDesign = designs.find((design) => String(design.id) === String(initialDesignId)) || null;
    const initialFields = resolvedFields(initialDesign, product.personalizationFields || []);
    const initialVariants = (product.variants || []).map((variant, index) => normalizeVariant(variant, index));
    const initialBundleItems = (product.bundleItems || []).map((item) => normalizeBundleItem(item));
    const initialVariantGroups = inferVariantGroupsFromVariants(initialVariants);
    const initialGeneratedSlug = slugifyValue(product.name || '');
    const initialGeneratedSku = skuifyValue(product.name || '');
    const initialGeneratedMetaTitle = buildMetaTitle(product.name || '');
    const initialGeneratedMetaDescription = truncateText(product.excerpt || '', 160);

    const [currentType, setCurrentType] = useState(product.currentType || GENERAL_DEFAULT_TYPE);
    const [lastGeneralType, setLastGeneralType] = useState(
        product.currentType && product.currentType !== ADVANCED_TYPE ? product.currentType : GENERAL_DEFAULT_TYPE,
    );
    const [productName, setProductName] = useState(product.name || '');
    const [slug, setSlug] = useState(product.slug || '');
    const [sku, setSku] = useState(product.sku || '');
    const [excerpt, setExcerpt] = useState(product.excerpt || '');
    const [description, setDescription] = useState(product.description || '');
    const [categoryId, setCategoryId] = useState(product.categoryId || '');
    const [selectedCollections, setSelectedCollections] = useState(product.collectionIds || []);
    const [selectedTags, setSelectedTags] = useState(product.tagIds || []);
    const [selectedRelatedProducts, setSelectedRelatedProducts] = useState(product.relatedProductIds || []);
    const [selectedRelatedCategories, setSelectedRelatedCategories] = useState(product.relatedCategoryIds || []);
    const [variants, setVariants] = useState(initialVariants);
    const [bundleItems, setBundleItems] = useState(initialBundleItems);
    const [serviceMeta, setServiceMeta] = useState(product.serviceMeta || {});
    const [variantGroups, setVariantGroups] = useState(initialVariantGroups);
    const [selectedDesignId, setSelectedDesignId] = useState(initialDesignId);
    const [activeMockups, setActiveMockups] = useState(product.isNew ? [] : (product.activeMockupIds || []));
    const [defaultMockupId, setDefaultMockupId] = useState(product.isNew ? '' : (product.defaultMockupId || ''));
    const [personalizationFields, setPersonalizationFields] = useState(initialFields);
    const [variantMediaLinks, setVariantMediaLinks] = useState(product.variantMediaLinks || {});
    const [price, setPrice] = useState(product.price || '');
    const [compareAtPrice, setCompareAtPrice] = useState(product.compareAtPrice || '');
    const [leadTimeDays, setLeadTimeDays] = useState(product.leadTimeDays || '');
    const [status, setStatus] = useState(product.status || 'draft');
    const [manageStock, setManageStock] = useState(Boolean(product.manageStock ?? true));
    const [stockQuantity, setStockQuantity] = useState(product.stockQuantity || '0');
    const [lowStockThreshold, setLowStockThreshold] = useState(product.lowStockThreshold || '0');
    const [isFeatured, setIsFeatured] = useState(Boolean(product.isFeatured));
    const [videoUrl, setVideoUrl] = useState(product.videoUrl || '');
    const [featuredImagePreview, setFeaturedImagePreview] = useState([]);
    const [galleryPreviews, setGalleryPreviews] = useState([]);
    const [personalizationHelpText, setPersonalizationHelpText] = useState(product.personalizationHelpText || '');
    const [metaTitle, setMetaTitle] = useState(product.metaTitle || '');
    const [metaDescription, setMetaDescription] = useState(product.metaDescription || '');
    const [advancedTab, setAdvancedTab] = useState('identity');
    const [generalTab, setGeneralTab] = useState('setup');
    const [copiedVariantPricing, setCopiedVariantPricing] = useState(null);
    const [draggedFieldId, setDraggedFieldId] = useState(null);
    const previousDesignId = useRef(selectedDesignId);
    const featuredImagePreviewRef = useRef([]);
    const galleryPreviewsRef = useRef([]);
    const slugManualRef = useRef(Boolean(product.slug) && product.slug !== initialGeneratedSlug);
    const skuManualRef = useRef(Boolean(product.sku) && product.sku !== initialGeneratedSku);
    const metaTitleManualRef = useRef(Boolean(product.metaTitle) && product.metaTitle !== initialGeneratedMetaTitle);
    const metaDescriptionManualRef = useRef(Boolean(product.metaDescription) && product.metaDescription !== initialGeneratedMetaDescription);

    const isAdvancedMode = currentType === ADVANCED_TYPE;
    const editorHeading = isAdvancedMode
        ? page.advancedHeading
        : page.generalHeading;
    const selectedDesign = designs.find((design) => String(design.id) === String(selectedDesignId)) || null;
    const availableMockups = mockups || [];
    const activeFrame = availableMockups.find((mockup) => String(mockup.id) === String(defaultMockupId))
        || availableMockups.find((mockup) => activeMockups.includes(mockup.id))
        || null;
    const designThumbnail = selectedDesign?.rendered_preview_url || selectedDesign?.thumbnail_url || selectedDesign?.preview_url || '';
    const zoneBox = getZoneBox(activeFrame);
    const hasDesign = Boolean(selectedDesignId);
    const hasActiveMockups = activeMockups.length > 0;
    const hasZone = Boolean(activeFrame && zoneBox.hasZone);
    const hasFields = personalizationFields.length > 0 && personalizationFields.every((field) => field.label.trim() !== '');
    const basePublishReady = Boolean(productName.trim() && categoryId && `${price}`.trim() !== '');
    const primaryExistingImage = existingImages.find((image) => image.is_primary) || existingImages[0] || null;
    const savedFeaturedImageUrl = product.featuredImageUrl || primaryExistingImage?.image_url || '';
    const productImageOptions = existingImages.map((image, index) => ({
        id: `${image.id}`,
        label: `${index + 1}. ${image.label || image.alt_text || 'Product image'}`,
        thumb: image.image_url,
        alt_text: image.alt_text,
    }));
    const mockupOptions = availableMockups
        .filter((mockup) => activeMockups.includes(mockup.id))
        .map((mockup) => ({
            id: `${mockup.id}`,
            label: mockup.title,
            thumb: mockup.thumb_image_url || mockup.base_image_url,
        }));
    const canPublish = isAdvancedMode
        ? basePublishReady && hasDesign && hasActiveMockups && hasZone && hasFields
        : basePublishReady;
    const advancedTabs = [
        { id: 'identity', label: 'Identity' },
        { id: 'personalization', label: 'Template' },
        { id: 'mockups', label: 'Mockups' },
        { id: 'fields', label: 'Fields' },
        { id: 'pricing', label: 'Pricing' },
        { id: 'variants', label: 'Variants' },
        { id: 'related', label: 'Related' },
        { id: 'seo', label: 'SEO' },
    ];
    const generalTabs = [
        { id: 'setup', label: 'Setup' },
        { id: 'organization', label: 'Organization' },
        { id: 'pricing', label: 'Pricing' },
        { id: 'variants', label: 'Variants' },
        ...(currentType === 'bundle' ? [{ id: 'combo', label: 'Combo' }] : []),
        ...(currentType === 'service' ? [{ id: 'service', label: 'Service' }] : []),
        { id: 'media', label: 'Media' },
        { id: 'seo', label: 'SEO' },
    ];

    useEffect(() => {
        const validMockupIds = new Set(availableMockups.map((mockup) => mockup.id));
        const filteredActiveMockups = activeMockups.filter((id) => validMockupIds.has(id));

        if (filteredActiveMockups.length !== activeMockups.length) {
            setActiveMockups(filteredActiveMockups);
        }

        if (!product.isNew && isAdvancedMode && !filteredActiveMockups.length && availableMockups.length) {
            setActiveMockups([availableMockups[0].id]);
        }

        if (
            !product.isNew
            && 
            isAdvancedMode
            && defaultMockupId
            && !validMockupIds.has(defaultMockupId)
            && (filteredActiveMockups[0] || availableMockups[0])
        ) {
            setDefaultMockupId(filteredActiveMockups[0] || availableMockups[0].id);
        }

        if (!product.isNew && isAdvancedMode && !defaultMockupId && (filteredActiveMockups[0] || availableMockups[0])) {
            setDefaultMockupId(filteredActiveMockups[0] || availableMockups[0].id);
        }

        if (previousDesignId.current !== selectedDesignId) {
            previousDesignId.current = selectedDesignId;
            setPersonalizationFields(selectedDesign ? fieldsFromDesign(selectedDesign) : []);
        }
    }, [activeMockups, availableMockups, defaultMockupId, isAdvancedMode, product.isNew, selectedDesign, selectedDesignId]);

    useEffect(() => {
        const nextSlug = slugifyValue(productName);

        if (!slugManualRef.current || slug.trim() === '') {
            setSlug(nextSlug);
        }

        const nextSku = skuifyValue(productName);

        if (!skuManualRef.current || sku.trim() === '') {
            setSku(nextSku);
        }
    }, [productName, slug, sku]);

    useEffect(() => {
        const nextMetaTitle = buildMetaTitle(productName);

        if (!metaTitleManualRef.current || metaTitle.trim() === '') {
            setMetaTitle(nextMetaTitle);
        }
    }, [metaTitle, productName]);

    useEffect(() => {
        const nextMetaDescription = truncateText(excerpt, 160);

        if (!metaDescriptionManualRef.current || metaDescription.trim() === '') {
            setMetaDescription(nextMetaDescription);
        }
    }, [excerpt, metaDescription]);

    useEffect(() => () => {
        revokeMediaPreviews(featuredImagePreviewRef.current);
        revokeMediaPreviews(galleryPreviewsRef.current);
    }, []);

    function switchEditorMode(mode) {
        if (mode === 'advanced') {
            if (currentType !== ADVANCED_TYPE) {
                setLastGeneralType(currentType || GENERAL_DEFAULT_TYPE);
                setCurrentType(ADVANCED_TYPE);

                if (product.isNew && manageStock && `${stockQuantity}`.trim() === '0' && `${lowStockThreshold}`.trim() === '0') {
                    setManageStock(false);
                }
            }

            return;
        }

        if (currentType === ADVANCED_TYPE) {
            setCurrentType(lastGeneralType || GENERAL_DEFAULT_TYPE);
        }
    }

    function handleGeneralTypeChange(nextType) {
        setLastGeneralType(nextType);
        setCurrentType(nextType);
    }

    function handleSlugChange(value) {
        setSlug(value);
        slugManualRef.current = Boolean(value.trim()) && value !== slugifyValue(productName);
    }

    function handleSkuChange(value) {
        setSku(value.toUpperCase());
        skuManualRef.current = Boolean(value.trim()) && value.toUpperCase() !== skuifyValue(productName);
    }

    function handleMetaTitleChange(value) {
        setMetaTitle(value);
        metaTitleManualRef.current = Boolean(value.trim()) && value !== buildMetaTitle(productName);
    }

    function handleMetaDescriptionChange(value) {
        setMetaDescription(value);
        metaDescriptionManualRef.current = Boolean(value.trim()) && value !== truncateText(excerpt, 160);
    }

    function handleFeaturedImageChange(event) {
        const nextPreviews = createMediaPreviews(event.target.files).slice(0, 1);

        revokeMediaPreviews(featuredImagePreviewRef.current);
        featuredImagePreviewRef.current = nextPreviews;
        setFeaturedImagePreview(nextPreviews);
    }

    function handleGalleryUploadsChange(event) {
        const nextPreviews = createMediaPreviews(event.target.files);

        revokeMediaPreviews(galleryPreviewsRef.current);
        galleryPreviewsRef.current = nextPreviews;
        setGalleryPreviews(nextPreviews);
    }

    function toggleArrayValue(setter, id) {
        setter((currentValues) => (
            currentValues.includes(id)
                ? currentValues.filter((value) => value !== id)
                : [...currentValues, id]
        ));
    }

    function toggleMockup(mockupId) {
        setActiveMockups((currentMockups) => {
            if (currentMockups.includes(mockupId)) {
                return currentMockups.filter((id) => id !== mockupId);
            }

            return [...currentMockups, mockupId];
        });

        if (!activeMockups.includes(mockupId)) {
            setDefaultMockupId(mockupId);
        } else if (String(defaultMockupId) === String(mockupId)) {
            const nextMockup = activeMockups.find((id) => String(id) !== String(mockupId));
            setDefaultMockupId(nextMockup || '');
        }
    }

    function activateFrame(mockupId) {
        if (!activeMockups.includes(mockupId)) {
            setActiveMockups((currentMockups) => [...currentMockups, mockupId]);
        }

        setDefaultMockupId(mockupId);
    }

    function updateField(fieldId, key, value) {
        setPersonalizationFields((currentFields) => currentFields.map((field) => (
            field.id === fieldId ? { ...field, [key]: value } : field
        )));
    }

    function addVariant(presetOptionNames = []) {
        setVariants((currentVariants) => {
            const nextVariants = [
                ...currentVariants,
                normalizeVariant({
                    option_values: presetOptionNames.map((name) => `${name}:`).join(', '),
                    is_default: currentVariants.length === 0,
                }, currentVariants.length),
            ];

            if (!nextVariants.some((variant) => variant.is_default) && nextVariants[0]) {
                nextVariants[0] = { ...nextVariants[0], is_default: true };
            }

            return nextVariants;
        });
    }

    function updateVariantGroup(groupId, key, value) {
        setVariantGroups((currentGroups) => currentGroups.map((group) => (
            group.id === groupId ? { ...group, [key]: value } : group
        )));
    }

    function addVariantGroup(presetName = '') {
        setVariantGroups((currentGroups) => [
            ...currentGroups,
            normalizeVariantGroup({ name: presetName, valuesText: '' }, currentGroups.length),
        ]);
    }

    function removeVariantGroup(groupId) {
        setVariantGroups((currentGroups) => currentGroups.filter((group) => group.id !== groupId));
    }

    function generateVariantCombinations() {
        setVariants((currentVariants) => buildVariantsFromGroups(variantGroups, currentVariants, sku || productName));
    }

    function updateVariant(variantId, key, value) {
        setVariants((currentVariants) => currentVariants.map((variant) => (
            variant.id === variantId ? { ...variant, [key]: value } : variant
        )));
    }

    function setDefaultVariant(variantId) {
        setVariants((currentVariants) => currentVariants.map((variant) => ({
            ...variant,
            is_default: variant.id === variantId,
        })));
    }

    function removeVariant(variantId) {
        setVariants((currentVariants) => {
            const nextVariants = currentVariants.filter((variant) => variant.id !== variantId);

            if (nextVariants.length && !nextVariants.some((variant) => variant.is_default)) {
                nextVariants[0] = { ...nextVariants[0], is_default: true };
            }

            return nextVariants;
        });
    }

    function addBundleItem() {
        setBundleItems((currentItems) => [...currentItems, normalizeBundleItem()]);
    }

    function updateBundleItem(itemId, key, value) {
        setBundleItems((currentItems) => currentItems.map((item) => (
            item.id === itemId ? { ...item, [key]: value } : item
        )));
    }

    function removeBundleItem(itemId) {
        setBundleItems((currentItems) => currentItems.filter((item) => item.id !== itemId));
    }

    function updateServiceMeta(key, value) {
        setServiceMeta((currentMeta) => ({ ...currentMeta, [key]: value }));
    }

    function addVariantOption(variantId, presetName = '') {
        setVariants((currentVariants) => currentVariants.map((variant) => (
            variant.id === variantId
                ? { ...variant, options: [...variant.options, createVariantOption(presetName, '')] }
                : variant
        )));
    }

    function updateVariantOption(variantId, optionId, key, value) {
        setVariants((currentVariants) => currentVariants.map((variant) => (
            variant.id === variantId
                ? {
                    ...variant,
                    options: variant.options.map((option) => (
                        option.id === optionId ? { ...option, [key]: value } : option
                    )),
                }
                : variant
        )));
    }

    function removeVariantOption(variantId, optionId) {
        setVariants((currentVariants) => currentVariants.map((variant) => (
            variant.id === variantId
                ? { ...variant, options: variant.options.filter((option) => option.id !== optionId) }
                : variant
        )));
    }

    function copyVariantPricing(variantId) {
        const variant = variants.find((item) => item.id === variantId);

        if (!variant) {
            return;
        }

        setCopiedVariantPricing({
            price: variant.price,
            compare_at_price: variant.compare_at_price,
            stock_quantity: variant.stock_quantity,
        });
    }

    function pasteVariantPricing(variantId) {
        if (!copiedVariantPricing) {
            return;
        }

        setVariants((currentVariants) => currentVariants.map((variant) => (
            variant.id === variantId
                ? { ...variant, ...copiedVariantPricing }
                : variant
        )));
    }

    function addField() {
        setPersonalizationFields((currentFields) => [
            ...currentFields,
            {
                id: uid('field'),
                label: `Custom field ${currentFields.length + 1}`,
                field_key: `custom_field_${currentFields.length + 1}`,
                type: 'text',
                is_required: false,
            },
        ]);
    }

    function removeField(fieldId) {
        setPersonalizationFields((currentFields) => currentFields.filter((field) => field.id !== fieldId));
    }

    function moveField(fieldId, targetFieldId) {
        if (!fieldId || !targetFieldId || fieldId === targetFieldId) {
            return;
        }

        setPersonalizationFields((currentFields) => {
            const nextFields = [...currentFields];
            const sourceIndex = nextFields.findIndex((field) => field.id === fieldId);
            const targetIndex = nextFields.findIndex((field) => field.id === targetFieldId);

            if (sourceIndex === -1 || targetIndex === -1) {
                return currentFields;
            }

            const [movedField] = nextFields.splice(sourceIndex, 1);
            nextFields.splice(targetIndex, 0, movedField);

            return nextFields;
        });
    }

    return (
        <>
            <input type="hidden" name="type" value={currentType || GENERAL_DEFAULT_TYPE} />
            <input type="hidden" name="status" value={status} />
            <input type="hidden" name="manage_stock" value={manageStock ? '1' : '0'} />
            <input type="hidden" name="is_featured" value={isFeatured ? '1' : '0'} />
            <input type="hidden" name="proof_notes_enabled" value={isAdvancedMode ? '1' : '0'} />
            <input type="hidden" name="font_presets_enabled" value={isAdvancedMode ? '1' : '0'} />
            <input type="hidden" name="live_preview_enabled" value={isAdvancedMode ? '1' : '0'} />
            <input type="hidden" name="include_mockup_gallery" value={isAdvancedMode ? '1' : '0'} />
            <input type="hidden" name="show_flat_preview_first" value="0" />
            <input type="hidden" name="gallery_default_source" value={isAdvancedMode ? 'selected_mockup' : 'manual_featured_image'} />
            <input type="hidden" name="assigned_template_id" value={isAdvancedMode ? selectedDesignId || '' : ''} />
            <input type="hidden" name="default_mockup_id" value={isAdvancedMode ? defaultMockupId || '' : ''} />
            <input type="hidden" name="personalization_fields_blueprint" value={isAdvancedMode ? JSON.stringify(personalizationFields) : ''} />

            {selectedCollections.map((collectionId) => (
                <input key={`collection-${collectionId}`} type="hidden" name="collection_ids[]" value={collectionId} />
            ))}

            {selectedTags.map((tagId) => (
                <input key={`tag-${tagId}`} type="hidden" name="tag_ids[]" value={tagId} />
            ))}

            {selectedRelatedProducts.map((relatedProductId) => (
                <input key={`related-product-${relatedProductId}`} type="hidden" name="related_product_ids[]" value={relatedProductId} />
            ))}

            {selectedRelatedCategories.map((relatedCategoryId) => (
                <input key={`related-category-${relatedCategoryId}`} type="hidden" name="related_category_ids[]" value={relatedCategoryId} />
            ))}

            {variants.map((variant, index) => (
                <input key={`variant-default-${variant.id}`} type="hidden" name={`variants[${index}][is_default]`} value="0" />
            ))}

            {isAdvancedMode ? activeMockups.map((mockupId) => (
                <input key={`mockup-${mockupId}`} type="hidden" name="allowed_mockup_ids[]" value={mockupId} />
            )) : null}

            <input type="hidden" name="variant_media_links" value={JSON.stringify(variantMediaLinks)} />

            <div className="nikah-form">
                <div className="nikah-form__header">
                    <div className="nikah-form__header-copy">
                        <Breadcrumbs items={page.breadcrumbs} />
                        <h2>{editorHeading}</h2>
                        <p>Use the Nikahnama workflow for advanced personalization, or switch to the general ecommerce form for everything else.</p>
                    </div>
                    <div className="nikah-form__header-actions">
                        <button type="submit" name="save_mode" value="draft" className="button-ghost">Save draft</button>
                        <button
                            type="submit"
                            name="save_mode"
                            value="publish"
                            className={`button-primary ${!canPublish ? 'nikah-form__button-disabled' : ''}`}
                            disabled={!canPublish}
                        >
                            Publish product
                        </button>
                    </div>
                </div>

                <section className="nikah-step-card">
                    <div className="nikah-step-card__heading">
                        <span className="nikah-step-card__step">0</span>
                        <div>
                            <h3>Form type</h3>
                            <p>Choose the product editor experience first. `Advanced customization` is for Nikahnama products only.</p>
                        </div>
                    </div>

                    <div className="editor-mode-toggle">
                        <button
                            type="button"
                            className={`editor-mode-toggle__option ${!isAdvancedMode ? 'is-active' : ''}`}
                            onClick={() => switchEditorMode('general')}
                        >
                            <strong>General</strong>
                            <span>Normal ecommerce product fields</span>
                        </button>
                        <button
                            type="button"
                            className={`editor-mode-toggle__option ${isAdvancedMode ? 'is-active' : ''}`}
                            onClick={() => switchEditorMode('advanced')}
                        >
                            <strong>Advanced customization</strong>
                            <span>Nikahnama personalization workflow</span>
                        </button>
                    </div>
                </section>

                {isAdvancedMode ? (
                    <div className="nikah-form__layout">
                        <div className="nikah-form__main">
                            <SectionTabs tabs={advancedTabs} activeTab={advancedTab} onChange={setAdvancedTab} />

                            <section className="nikah-step-card" hidden={advancedTab !== 'identity'}>
                                <div className="nikah-step-card__heading">
                                    <span className="nikah-step-card__step">1</span>
                                    <div>
                                        <h3>Product identity</h3>
                                        <p>Set the storefront naming, catalog placement, and short copy for this Nikahnama product.</p>
                                    </div>
                                </div>

                                <div className="nikah-form__grid nikah-form__grid--two">
                                    <label className="nikah-field nikah-form__span-two">
                                        <span>Product name</span>
                                        <input name="name" value={productName} onChange={(event) => setProductName(event.target.value)} />
                                        {getError(errors, 'name') ? <small>{getError(errors, 'name')}</small> : null}
                                    </label>

                                    <label className="nikah-field">
                                        <span>Slug</span>
                                        <input name="slug" value={slug} onChange={(event) => handleSlugChange(event.target.value)} placeholder="Auto-generated from product name" />
                                        {getError(errors, 'slug') ? <small>{getError(errors, 'slug')}</small> : null}
                                    </label>

                                    <label className="nikah-field">
                                        <span>SKU</span>
                                        <input name="sku" value={sku} onChange={(event) => handleSkuChange(event.target.value)} placeholder="Auto-generated from product name" />
                                        {getError(errors, 'sku') ? <small>{getError(errors, 'sku')}</small> : null}
                                    </label>

                                    <label className="nikah-field nikah-form__span-two">
                                        <span>Short description</span>
                                        <textarea name="excerpt" rows="3" value={excerpt} onChange={(event) => setExcerpt(event.target.value)} />
                                    </label>

                                    <label className="nikah-field">
                                        <span>Category</span>
                                        <select name="category_id" value={categoryId} onChange={(event) => setCategoryId(event.target.value)}>
                                            <option value="">Select a category</option>
                                            {categories.map((category) => (
                                                <option key={category.id} value={category.id}>{category.name}</option>
                                            ))}
                                        </select>
                                        {getError(errors, 'category_id') ? <small>{getError(errors, 'category_id')}</small> : null}
                                    </label>

                                    <label className="nikah-field">
                                        <span>Status</span>
                                        <select value={status} onChange={(event) => setStatus(event.target.value)}>
                                            {STATUS_OPTIONS.map((option) => (
                                                <option key={option.value} value={option.value}>{option.label}</option>
                                            ))}
                                        </select>
                                    </label>
                                </div>
                            </section>

                            <section className="nikah-step-card nikah-step-card--accent-blue" hidden={advancedTab !== 'personalization'}>
                                <div className="nikah-step-card__heading">
                                    <span className="nikah-step-card__step">2</span>
                                    <div>
                                        <h3>Personalization</h3>
                                        <p>Choose exactly one active Nikahnama personalization template for this product.</p>
                                    </div>
                                </div>

                                <div className="nikah-form__grid">
                                    <label className="nikah-field">
                                        <span>Personalization template</span>
                                        <select value={selectedDesignId} onChange={(event) => setSelectedDesignId(Number(event.target.value) || '')}>
                                            <option value="">Select a template</option>
                                            {designs.map((design) => (
                                                <option key={design.id} value={design.id}>{design.name}</option>
                                            ))}
                                        </select>
                                        {getError(errors, 'assigned_template_id') ? <small>{getError(errors, 'assigned_template_id')}</small> : null}
                                    </label>

                                    {selectedDesign ? (
                                        <div className="selected-design-preview">
                                            <div className="selected-design-preview__thumb">
                                                {designThumbnail ? <img src={designThumbnail} alt={selectedDesign.name} /> : null}
                                            </div>
                                            <div className="selected-design-preview__meta">
                                                <strong>{selectedDesign.name}</strong>
                                                <span>{selectedDesign.fields.length} personalization fields</span>
                                            </div>
                                        </div>
                                    ) : (
                                        <div className="nikah-empty-note">Select one personalization template to unlock the field setup. Mockups are assigned separately below.</div>
                                    )}
                                </div>
                            </section>

                            <section className="nikah-step-card nikah-step-card--accent-amber" hidden={advancedTab !== 'mockups'}>
                                <div className="nikah-step-card__heading">
                                    <span className="nikah-step-card__step">3</span>
                                    <div>
                                        <h3>Mockup merge</h3>
                                        <p>Pick any reusable mockups for this product and choose the main storefront scene.</p>
                                    </div>
                                </div>

                                <div className="nikah-mockup-layout">
                                    <div className="nikah-active-frame">
                                        <div className="nikah-active-frame__header">
                                            <strong>Default storefront preview</strong>
                                            <span>{activeFrame?.title || 'Choose a mockup scene'}</span>
                                        </div>
                                        <MockupStagePreview
                                            sceneUrl={activeFrame?.base_image_url}
                                            overlayUrl={activeFrame?.overlay_image_url}
                                            maskUrl={activeFrame?.mask_image_url}
                                            certificateUrl={designThumbnail}
                                            map={activeFrame?.map}
                                            title={activeFrame?.title}
                                        />
                                    </div>

                                    <div className="nikah-frame-grid">
                                        {(availableMockups.length ? availableMockups : [{ id: 'placeholder', title: 'No mockups yet' }]).map((mockup) => {
                                            if (!mockup.base_image_url) {
                                                return (
                                                    <div key={mockup.id} className="nikah-frame-card is-placeholder">
                                                        <span>No reusable mockups available yet.</span>
                                                    </div>
                                                );
                                            }

                                            const isActive = activeMockups.includes(mockup.id);
                                            const isLead = String(defaultMockupId) === String(mockup.id);

                                            return (
                                                <div key={mockup.id} className={`nikah-frame-card ${isLead ? 'is-lead' : ''} ${isActive ? 'is-active' : ''}`}>
                                                    <button type="button" className="nikah-frame-card__preview" onClick={() => activateFrame(mockup.id)}>
                                                        <img src={mockup.thumb_image_url || mockup.base_image_url} alt={mockup.title} />
                                                    </button>
                                                    <div className="nikah-frame-card__meta">
                                                        <div>
                                                            <strong>{mockup.title}</strong>
                                                            <span>
                                                                {mockup.template_name ? `${mockup.template_name} · ` : 'Reusable scene · '}
                                                                {mockup.map ? 'Zone mapped' : 'Zone pending'}
                                                            </span>
                                                            </div>
                                                            <div className="nikah-frame-card__actions">
                                                                <button type="button" className="nikah-frame-card__link" onClick={() => activateFrame(mockup.id)}>
                                                                    {isLead ? 'Default preview' : 'Set as default'}
                                                                </button>
                                                            <button
                                                                type="button"
                                                                className={`nikah-frame-card__toggle ${isActive ? 'is-selected' : ''}`}
                                                                onClick={() => toggleMockup(mockup.id)}
                                                            >
                                                                {isActive ? 'Selected' : 'Select'}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                </div>

                                <div className="nikah-callout">Nikahnama will be composited at the saved zone position.</div>
                            </section>

                            <section className="nikah-step-card" hidden={advancedTab !== 'fields'}>
                                <div className="nikah-step-card__heading">
                                    <span className="nikah-step-card__step">4</span>
                                    <div>
                                        <h3>Personalization fields</h3>
                                        <p>Arrange and refine the customer-facing fields for this certificate.</p>
                                    </div>
                                </div>

                                <div className="nikah-fields-list">
                                    {personalizationFields.map((field) => (
                                        <div
                                            key={field.id}
                                            className="nikah-field-row"
                                            draggable
                                            onDragStart={() => setDraggedFieldId(field.id)}
                                            onDragOver={(event) => event.preventDefault()}
                                            onDrop={() => {
                                                moveField(draggedFieldId, field.id);
                                                setDraggedFieldId(null);
                                            }}
                                        >
                                            <span className="nikah-field-row__handle">⋮⋮</span>
                                            <input
                                                type="text"
                                                value={field.label}
                                                className="nikah-field-row__name"
                                                onChange={(event) => updateField(field.id, 'label', event.target.value)}
                                            />
                                            <span className="nikah-badge">{field.type}</span>
                                            <label className="nikah-field-row__required">
                                                <input
                                                    type="checkbox"
                                                    checked={field.is_required}
                                                    onChange={(event) => updateField(field.id, 'is_required', event.target.checked)}
                                                />
                                                Required
                                            </label>
                                            <button type="button" className="nikah-field-row__remove" onClick={() => removeField(field.id)}>
                                                Remove
                                            </button>
                                        </div>
                                    ))}
                                </div>

                                <button type="button" className="nikah-add-field" onClick={addField}>Add field</button>
                            </section>

                            <section className="nikah-step-card" hidden={advancedTab !== 'pricing'}>
                                <div className="nikah-step-card__heading">
                                    <span className="nikah-step-card__step">5</span>
                                    <div>
                                        <h3>Pricing</h3>
                                        <p>Set the live sell price and decide whether this Nikahnama is made to order or stock tracked.</p>
                                    </div>
                                </div>

                                <div className="nikah-form__grid nikah-form__grid--two">
                                    <label className="nikah-field">
                                        <span>Price</span>
                                        <input type="number" step="0.01" min="0" name="price" value={price} onChange={(event) => setPrice(event.target.value)} />
                                        {getError(errors, 'price') ? <small>{getError(errors, 'price')}</small> : null}
                                    </label>

                                    <label className="nikah-field">
                                        <span>Compare-at price</span>
                                        <input type="number" step="0.01" min="0" name="compare_at_price" value={compareAtPrice} onChange={(event) => setCompareAtPrice(event.target.value)} />
                                    </label>

                                    <label className="nikah-field">
                                        <span>Lead time (days)</span>
                                        <input type="number" min="0" name="lead_time_days" value={leadTimeDays} onChange={(event) => setLeadTimeDays(event.target.value)} />
                                    </label>

                                    <label className="general-checkbox">
                                        <input type="checkbox" checked={manageStock} onChange={(event) => setManageStock(event.target.checked)} />
                                        Track stock for this personalized product
                                    </label>

                                    {!manageStock ? (
                                        <div className="nikah-empty-note nikah-form__span-two">This product will behave as made to order. Customers will not see a stock count.</div>
                                    ) : null}

                                    {manageStock ? (
                                        <>
                                            <label className="nikah-field">
                                                <span>Stock quantity</span>
                                                <input type="number" min="0" name="stock_quantity" value={stockQuantity} onChange={(event) => setStockQuantity(event.target.value)} />
                                            </label>

                                            <label className="nikah-field">
                                                <span>Low stock threshold</span>
                                                <input type="number" min="0" name="low_stock_threshold" value={lowStockThreshold} onChange={(event) => setLowStockThreshold(event.target.value)} />
                                            </label>
                                        </>
                                    ) : null}
                                </div>
                            </section>

                            <section className="nikah-step-card" hidden={advancedTab !== 'variants'}>
                                <div className="nikah-step-card__heading">
                                    <span className="nikah-step-card__step">6</span>
                                    <div>
                                        <h3>Variants</h3>
                                        <p>Add frame sizes, styles, and finishes with their own SKU, price, and optional stock count.</p>
                                    </div>
                                </div>

                                <VariantPlanner
                                    groups={variantGroups}
                                    onUpdateGroup={updateVariantGroup}
                                    onAddGroup={() => addVariantGroup()}
                                    onRemoveGroup={removeVariantGroup}
                                    onGenerate={generateVariantCombinations}
                                />

                                <VariantEditor
                                    variants={variants}
                                    manageStock={manageStock}
                                    onAddVariant={() => addVariant(['Size', 'Style', 'Frame type'])}
                                    onRemoveVariant={removeVariant}
                                    onUpdateVariant={updateVariant}
                                    onSetDefaultVariant={setDefaultVariant}
                                    onAddVariantOption={(variantId) => addVariantOption(variantId)}
                                    onUpdateVariantOption={updateVariantOption}
                                    onRemoveVariantOption={removeVariantOption}
                                    onCopyPricing={copyVariantPricing}
                                    onPastePricing={pasteVariantPricing}
                                    hasCopiedPricing={Boolean(copiedVariantPricing)}
                                    emptyMessage="No variants added yet. Leave this empty for a single-option Nikahnama product."
                                />

                                <VariantMediaMapper
                                    variants={variants}
                                    mediaOptions={mockupOptions}
                                    links={variantMediaLinks}
                                    onChange={setVariantMediaLinks}
                                    title="Variant mockup mapping"
                                    mediaLabel="mockups"
                                    emptyMessage="Add variants and assign mockups first, then map option values to scenes."
                                />
                            </section>

                            <section className="nikah-step-card" hidden={advancedTab !== 'related'}>
                                <div className="nikah-step-card__heading">
                                    <span className="nikah-step-card__step">7</span>
                                    <div>
                                        <h3>Related discovery</h3>
                                        <p>Cross-link this Nikahnama with supporting products and relevant browse categories.</p>
                                    </div>
                                </div>

                                <div className="nikah-form__grid">
                                    <SearchableMultiSelect
                                        label="Related products"
                                        placeholder="Search and select related products"
                                        options={relatedProducts}
                                        selectedValues={selectedRelatedProducts}
                                        onToggle={(id) => toggleArrayValue(setSelectedRelatedProducts, id)}
                                    />
                                    <SearchableMultiSelect
                                        label="Related categories"
                                        placeholder="Search and select related categories"
                                        options={relatedCategories}
                                        selectedValues={selectedRelatedCategories}
                                        onToggle={(id) => toggleArrayValue(setSelectedRelatedCategories, id)}
                                    />
                                </div>
                            </section>

                            <div hidden={advancedTab !== 'seo'}>
                                <SharedSeoCard
                                    metaTitle={metaTitle}
                                    onMetaTitleChange={handleMetaTitleChange}
                                    metaDescription={metaDescription}
                                    onMetaDescriptionChange={handleMetaDescriptionChange}
                                    errors={errors}
                                    step="8"
                                />
                            </div>
                        </div>

                        <aside className="nikah-form__sidebar">
                            <section className="nikah-sidebar-card">
                                <div className="nikah-sidebar-card__heading">
                                    <h3>Storefront preview</h3>
                                    <p>What the customer sees first.</p>
                                </div>

                                <div className="nikah-preview-card">
                                    <MockupStagePreview
                                        sceneUrl={activeFrame?.base_image_url}
                                        overlayUrl={activeFrame?.overlay_image_url}
                                        maskUrl={activeFrame?.mask_image_url}
                                        certificateUrl={designThumbnail}
                                        map={activeFrame?.map}
                                        title={activeFrame?.title}
                                        compact
                                    />

                                    <div className="nikah-preview-card__meta">
                                        <strong>{productName || 'Untitled Nikahnama'}</strong>
                                        <span>{selectedDesign?.name || 'Design not selected'}</span>
                                        <div className="nikah-preview-card__stats">
                                            <span>{activeMockups.length} frame{activeMockups.length === 1 ? '' : 's'}</span>
                                            <span>{formatPrice(price)}</span>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section className="nikah-sidebar-card">
                                <div className="nikah-sidebar-card__heading">
                                    <h3>Publish checklist</h3>
                                    <p>All required items must be complete.</p>
                                </div>

                                <div className="nikah-checklist">
                                    <div className={`nikah-checklist__item ${hasDesign ? 'is-complete' : ''}`}>
                                        <span className="nikah-checklist__icon">{hasDesign ? '✓' : '○'}</span>
                                        <span>Template selected</span>
                                    </div>
                                    <div className={`nikah-checklist__item ${hasZone ? 'is-complete' : ''}`}>
                                        <span className="nikah-checklist__icon">{hasZone ? '✓' : '○'}</span>
                                        <span>Zone defined</span>
                                    </div>
                                    <div className={`nikah-checklist__item ${hasActiveMockups ? 'is-complete' : ''}`}>
                                        <span className="nikah-checklist__icon">{hasActiveMockups ? '✓' : '○'}</span>
                                        <span>Mockups active</span>
                                    </div>
                                    <div className={`nikah-checklist__item ${hasFields ? 'is-complete' : ''}`}>
                                        <span className="nikah-checklist__icon">{hasFields ? '✓' : '○'}</span>
                                        <span>Fields set</span>
                                    </div>
                                    <div className={`nikah-checklist__item ${metaTitle || metaDescription ? 'is-complete' : 'is-optional'}`}>
                                        <span className="nikah-checklist__icon">{metaTitle || metaDescription ? '✓' : '○'}</span>
                                        <span>SEO added</span>
                                    </div>
                                </div>
                            </section>

                            <section className="nikah-sidebar-card">
                                <div className="nikah-sidebar-card__heading">
                                    <h3>Status</h3>
                                    <p>Keep this product in draft or publish it live.</p>
                                </div>

                                <label className="nikah-field">
                                    <span>Catalog status</span>
                                    <select value={status} onChange={(event) => setStatus(event.target.value)}>
                                        {STATUS_OPTIONS.map((option) => (
                                            <option key={option.value} value={option.value}>{option.label}</option>
                                        ))}
                                    </select>
                                </label>
                            </section>
                        </aside>
                    </div>
                ) : (
                    <div className="general-editor">
                        <SectionTabs tabs={generalTabs} activeTab={generalTab} onChange={setGeneralTab} />

                        <section className="nikah-step-card" hidden={generalTab !== 'setup'}>
                            <div className="nikah-step-card__heading">
                                <span className="nikah-step-card__step">1</span>
                                <div>
                                    <h3>General product setup</h3>
                                    <p>Use the standard ecommerce form for regular catalog products.</p>
                                </div>
                            </div>

                            <div className="nikah-form__grid nikah-form__grid--two">
                                <label className="nikah-field">
                                    <span>Product type</span>
                                    <select value={currentType} onChange={(event) => handleGeneralTypeChange(event.target.value)}>
                                        {productTypes.map((type) => (
                                            <option key={type.value} value={type.value}>{type.label}</option>
                                        ))}
                                    </select>
                                </label>

                                <label className="nikah-field">
                                    <span>Status</span>
                                    <select value={status} onChange={(event) => setStatus(event.target.value)}>
                                        {STATUS_OPTIONS.map((option) => (
                                            <option key={option.value} value={option.value}>{option.label}</option>
                                        ))}
                                    </select>
                                </label>

                                <label className="nikah-field nikah-form__span-two">
                                    <span>Product name</span>
                                    <input name="name" value={productName} onChange={(event) => setProductName(event.target.value)} />
                                    {getError(errors, 'name') ? <small>{getError(errors, 'name')}</small> : null}
                                </label>

                                <label className="nikah-field">
                                    <span>Slug</span>
                                    <input name="slug" value={slug} onChange={(event) => handleSlugChange(event.target.value)} placeholder="Auto-generated from product name" />
                                    {getError(errors, 'slug') ? <small>{getError(errors, 'slug')}</small> : null}
                                </label>

                                <label className="nikah-field">
                                    <span>SKU</span>
                                    <input name="sku" value={sku} onChange={(event) => handleSkuChange(event.target.value)} placeholder="Auto-generated from product name" />
                                    {getError(errors, 'sku') ? <small>{getError(errors, 'sku')}</small> : null}
                                </label>

                                <label className="nikah-field nikah-form__span-two">
                                    <span>Short description</span>
                                    <textarea name="excerpt" rows="3" value={excerpt} onChange={(event) => setExcerpt(event.target.value)} />
                                </label>

                                <label className="nikah-field nikah-form__span-two">
                                    <span>Description</span>
                                    <textarea name="description" rows="6" value={description} onChange={(event) => setDescription(event.target.value)} />
                                </label>

                                <label className="nikah-field">
                                    <span>Category</span>
                                    <select name="category_id" value={categoryId} onChange={(event) => setCategoryId(event.target.value)}>
                                        <option value="">Select a category</option>
                                        {categories.map((category) => (
                                            <option key={category.id} value={category.id}>{category.name}</option>
                                        ))}
                                    </select>
                                    {getError(errors, 'category_id') ? <small>{getError(errors, 'category_id')}</small> : null}
                                </label>

                                <label className="general-checkbox">
                                    <input type="checkbox" checked={isFeatured} onChange={(event) => setIsFeatured(event.target.checked)} />
                                    Mark as featured
                                </label>
                            </div>
                        </section>

                        <section className="nikah-step-card" hidden={generalTab !== 'organization'}>
                            <div className="nikah-step-card__heading">
                                <span className="nikah-step-card__step">2</span>
                                <div>
                                    <h3>Catalog organization</h3>
                                    <p>Organize the product with collections, tags, and optional cross-links.</p>
                                </div>
                            </div>

                            <div className="nikah-form__grid">
                                <SearchableMultiSelect
                                    label="Collections"
                                    placeholder="Search and select collections"
                                    options={collections}
                                    selectedValues={selectedCollections}
                                    onToggle={(id) => toggleArrayValue(setSelectedCollections, id)}
                                />
                                <SearchableMultiSelect
                                    label="Tags"
                                    placeholder="Search and select tags"
                                    options={tags}
                                    selectedValues={selectedTags}
                                    onToggle={(id) => toggleArrayValue(setSelectedTags, id)}
                                />
                                <SearchableMultiSelect
                                    label="Related products"
                                    placeholder="Search and select related products"
                                    options={relatedProducts}
                                    selectedValues={selectedRelatedProducts}
                                    onToggle={(id) => toggleArrayValue(setSelectedRelatedProducts, id)}
                                />
                                <SearchableMultiSelect
                                    label="Related categories"
                                    placeholder="Search and select related categories"
                                    options={relatedCategories}
                                    selectedValues={selectedRelatedCategories}
                                    onToggle={(id) => toggleArrayValue(setSelectedRelatedCategories, id)}
                                />
                            </div>
                        </section>

                        <section className="nikah-step-card" hidden={generalTab !== 'pricing'}>
                            <div className="nikah-step-card__heading">
                                <span className="nikah-step-card__step">3</span>
                                <div>
                                    <h3>Pricing and inventory</h3>
                                    <p>Set the sell price, compare-at price, lead time, and stock controls.</p>
                                </div>
                            </div>

                            <div className="nikah-form__grid nikah-form__grid--two">
                                <label className="nikah-field">
                                    <span>Price</span>
                                    <input type="number" step="0.01" min="0" name="price" value={price} onChange={(event) => setPrice(event.target.value)} />
                                    {getError(errors, 'price') ? <small>{getError(errors, 'price')}</small> : null}
                                </label>

                                <label className="nikah-field">
                                    <span>Compare-at price</span>
                                    <input type="number" step="0.01" min="0" name="compare_at_price" value={compareAtPrice} onChange={(event) => setCompareAtPrice(event.target.value)} />
                                </label>

                                <label className="nikah-field">
                                    <span>Lead time (days)</span>
                                    <input type="number" min="0" name="lead_time_days" value={leadTimeDays} onChange={(event) => setLeadTimeDays(event.target.value)} />
                                </label>

                                <label className="general-checkbox">
                                    <input type="checkbox" checked={manageStock} onChange={(event) => setManageStock(event.target.checked)} />
                                    Track stock for this product
                                </label>

                                {manageStock ? (
                                    <>
                                        <label className="nikah-field">
                                            <span>Stock quantity</span>
                                            <input type="number" min="0" name="stock_quantity" value={stockQuantity} onChange={(event) => setStockQuantity(event.target.value)} />
                                        </label>

                                        <label className="nikah-field">
                                            <span>Low stock threshold</span>
                                            <input type="number" min="0" name="low_stock_threshold" value={lowStockThreshold} onChange={(event) => setLowStockThreshold(event.target.value)} />
                                        </label>
                                    </>
                                ) : null}

                                {currentType === 'light_customizable' ? (
                                    <label className="nikah-field nikah-form__span-two">
                                        <span>Personalization help text</span>
                                        <textarea
                                            name="personalization_help_text"
                                            rows="4"
                                            value={personalizationHelpText}
                                            onChange={(event) => setPersonalizationHelpText(event.target.value)}
                                            placeholder="Explain what the customer can customize."
                                        />
                                    </label>
                                ) : null}
                            </div>
                        </section>

                        <section className="nikah-step-card" hidden={generalTab !== 'variants'}>
                            <div className="nikah-step-card__heading">
                                <span className="nikah-step-card__step">4</span>
                                <div>
                                    <h3>Variants</h3>
                                    <p>Add purchasable options with their own SKU, price, and optional stock count.</p>
                                </div>
                            </div>

                            <VariantPlanner
                                groups={variantGroups}
                                onUpdateGroup={updateVariantGroup}
                                onAddGroup={() => addVariantGroup()}
                                onRemoveGroup={removeVariantGroup}
                                onGenerate={generateVariantCombinations}
                            />

                            <VariantEditor
                                variants={variants}
                                manageStock={manageStock}
                                onAddVariant={() => addVariant([])}
                                onRemoveVariant={removeVariant}
                                onUpdateVariant={updateVariant}
                                onSetDefaultVariant={setDefaultVariant}
                                onAddVariantOption={(variantId) => addVariantOption(variantId)}
                                onUpdateVariantOption={updateVariantOption}
                                onRemoveVariantOption={removeVariantOption}
                                onCopyPricing={copyVariantPricing}
                                onPastePricing={pasteVariantPricing}
                                hasCopiedPricing={Boolean(copiedVariantPricing)}
                                emptyMessage="No variants added yet. Leave this empty for a single-price product."
                            />

                            <VariantMediaMapper
                                variants={variants}
                                mediaOptions={productImageOptions}
                                links={variantMediaLinks}
                                onChange={setVariantMediaLinks}
                                title="Variant image mapping"
                                mediaLabel="images"
                                emptyMessage="Add variants and upload gallery images first, then map option values to images."
                            />
                        </section>

                        {currentType === 'bundle' ? (
                            <section className="nikah-step-card" hidden={generalTab !== 'combo'}>
                                <div className="nikah-step-card__heading">
                                    <span className="nikah-step-card__step">5</span>
                                    <div>
                                        <h3>Combo products</h3>
                                        <p>Build the bundle by selecting the products included in the package.</p>
                                    </div>
                                </div>

                                <BundleItemEditor
                                    items={bundleItems}
                                    products={relatedProducts}
                                    onAddItem={addBundleItem}
                                    onRemoveItem={removeBundleItem}
                                    onUpdateItem={updateBundleItem}
                                />
                            </section>
                        ) : null}

                        {currentType === 'service' ? (
                            <section className="nikah-step-card" hidden={generalTab !== 'service'}>
                                <div className="nikah-step-card__heading">
                                    <span className="nikah-step-card__step">5</span>
                                    <div>
                                        <h3>Service / booking setup</h3>
                                        <p>Add the details customers see before submitting a booking request.</p>
                                    </div>
                                </div>

                                <ServiceMetaEditor meta={serviceMeta} onUpdate={updateServiceMeta} />
                            </section>
                        ) : null}

                        <section className="nikah-step-card" hidden={generalTab !== 'media'}>
                            <div className="nikah-step-card__heading">
                                <span className="nikah-step-card__step">5</span>
                                <div>
                                    <h3>Media</h3>
                                    <p>Add product photos and preview them before saving.</p>
                                </div>
                            </div>

                            <div className="media-upload-panel">
                                <div className="media-upload-card">
                                    <label className="nikah-field">
                                        <span>Featured image</span>
                                        <input type="file" name="featured_image_upload" accept="image/*" onChange={handleFeaturedImageChange} />
                                        {getError(errors, 'featured_image_upload') ? <small>{getError(errors, 'featured_image_upload')}</small> : null}
                                    </label>

                                    <div className="media-preview-card media-preview-card--large">
                                        {featuredImagePreview[0] ? (
                                            <>
                                                <img src={featuredImagePreview[0].url} alt={featuredImagePreview[0].name} />
                                                <span>New featured image</span>
                                            </>
                                        ) : savedFeaturedImageUrl ? (
                                            <>
                                                <img src={savedFeaturedImageUrl} alt="Current featured product" />
                                                <span>Current featured image</span>
                                            </>
                                        ) : (
                                            <div className="media-preview-card__empty">No featured image</div>
                                        )}
                                    </div>
                                </div>

                                <div className="media-upload-card">
                                    <label className="nikah-field">
                                        <span>Gallery images</span>
                                        <input type="file" name="gallery_uploads[]" accept="image/*" multiple onChange={handleGalleryUploadsChange} />
                                    </label>

                                    {galleryPreviews.length ? (
                                        <div className="media-preview-grid">
                                            {galleryPreviews.map((preview) => (
                                                <article key={preview.id} className="media-preview-card">
                                                    <img src={preview.url} alt={preview.name} />
                                                    <span>{preview.name}</span>
                                                </article>
                                            ))}
                                        </div>
                                    ) : (
                                        <div className="media-preview-card__empty">Choose gallery images to preview</div>
                                    )}
                                </div>

                                <label className="nikah-field media-upload-panel__video">
                                    <span>Video URL</span>
                                    <input type="url" name="video_url" value={videoUrl} onChange={(event) => setVideoUrl(event.target.value)} placeholder="https://..." />
                                </label>
                            </div>

                            <div className="general-media-list__heading">
                                <h4>Saved gallery</h4>
                                <span>{existingImages.length} image{existingImages.length === 1 ? '' : 's'}</span>
                            </div>

                            <div className="general-media-list">
                                {existingImages.length ? existingImages.map((image) => (
                                    <ExistingImageRow key={image.id} image={image} />
                                )) : (
                                    <div className="nikah-empty-note">No saved gallery images yet.</div>
                                )}
                            </div>
                        </section>

                        <div hidden={generalTab !== 'seo'}>
                            <SharedSeoCard
                                metaTitle={metaTitle}
                                onMetaTitleChange={handleMetaTitleChange}
                                metaDescription={metaDescription}
                                onMetaDescriptionChange={handleMetaDescriptionChange}
                                errors={errors}
                                step="6"
                            />
                        </div>
                    </div>
                )}
            </div>
        </>
    );
}

export function mountNikahProductForm() {
    const rootElement = document.getElementById('nikah-product-form-root');
    const payloadElement = document.getElementById('nikah-product-form-payload');

    if (!rootElement || !payloadElement) {
        return;
    }

    const payload = JSON.parse(payloadElement.textContent);
    const root = createRoot(rootElement);

    root.render(<NikahProductForm payload={payload} />);
}
