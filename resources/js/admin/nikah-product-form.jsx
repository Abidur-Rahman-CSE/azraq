import React, { useEffect, useRef, useState } from 'react';
import { createRoot } from 'react-dom/client';

const ADVANCED_TYPE = 'advanced_personalized';
const LIGHT_TYPE = 'light_customizable';
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
const LIGHT_FIELD_TYPES = [
    { value: 'text', label: 'Short text' },
    { value: 'textarea', label: 'Long text' },
    { value: 'date', label: 'Date' },
    { value: 'number', label: 'Number' },
    { value: 'email', label: 'Email' },
    { value: 'tel', label: 'Phone' },
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

function snakeCaseFieldKey(value) {
    return `${value || ''}`
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '_')
        .replace(/^_+|_+$/g, '');
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
    const presetValues = normalizePresetValues(field.preset_values ?? field.options ?? field.values ?? field.choices ?? []);
    const keyBase = snakeCaseFieldKey(field.field_key || field.key || label) || `field_${index + 1}`;

    return {
        id: field.id || uid('field'),
        label,
        field_key: keyBase,
        type: inferFieldType(field),
        is_required: Boolean(field.is_required ?? field.required ?? false),
        help_text: field.help_text || field.help || '',
        preset_values: presetValues,
        preset_values_text: typeof field.preset_values_text === 'string' ? field.preset_values_text : presetValues.join('\n'),
        allow_custom_value: field.allow_custom_value ?? field.allow_custom ?? true,
    };
}

function normalizePresetValues(value) {
    if (Array.isArray(value)) {
        return value
            .map((item) => (typeof item === 'string' ? item : (item?.value ?? item?.label ?? '')))
            .map((item) => `${item}`.trim())
            .filter(Boolean);
    }

    if (typeof value === 'string') {
        return value
            .split(/\r?\n/)
            .map((item) => item.trim())
            .filter(Boolean);
    }

    return [];
}

function presetValuesText(field) {
    return typeof field.preset_values_text === 'string'
        ? field.preset_values_text
        : normalizePresetValues(field.preset_values).join('\n');
}

function serializePersonalizationFields(fields) {
    return fields.map((field, index) => {
        const label = field.label || `Custom field ${index + 1}`;

        return {
            id: field.id,
            label,
            field_key: snakeCaseFieldKey(label) || `custom_field_${index + 1}`,
            type: field.type || 'text',
            is_required: Boolean(field.is_required),
            help_text: field.help_text || '',
            preset_values: normalizePresetValues(field.preset_values_text ?? field.preset_values ?? []),
            allow_custom_value: field.allow_custom_value ?? true,
        };
    });
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

    function selectedMedia(target) {
        const ids = links[target.key] || [];

        return ids
            .map((id) => mediaOptions.find((media) => media.id === id))
            .filter(Boolean);
    }

    return (
        <section className="variant-media-mapper">
            <div className="variant-media-mapper__heading">
                <div>
                    <h4>{title}</h4>
                    <p>Choose from all assigned mockups, then attach the right scene to each variant option.</p>
                </div>
            </div>

            {mediaOptions.length ? (
                <div className="variant-media-mapper__media-strip" aria-label={`All available ${mediaLabel}`}>
                    {mediaOptions.map((media) => (
                        <article
                            key={media.id}
                            className="variant-media-mapper__media-tile"
                            title={media.label}
                        >
                            <img src={media.thumb} alt={media.label} />
                            <span>{media.label}</span>
                        </article>
                    ))}
                </div>
            ) : null}

            {hasTargets && mediaOptions.length ? (
                <div className="variant-media-mapper__groups">
                    {groups.map((group) => (
                        <section key={group.key} className="variant-media-mapper__group">
                            <h5>{group.label}</h5>
                            <div className="variant-media-mapper__list">
                                {group.targets.map((target) => (
                                    <article key={target.key} className="variant-media-mapper__row">
                                        <div className="variant-media-mapper__row-copy">
                                            <strong>{target.label}</strong>
                                            <span>{selectedLabels(target)}</span>
                                        </div>
                                        <div className="variant-media-mapper__selected">
                                            {selectedMedia(target).length ? selectedMedia(target).map((media) => (
                                                <img key={media.id} src={media.thumb} alt={media.label} title={media.label} />
                                            )) : (
                                                <span>No scene</span>
                                            )}
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
        is_required: item.is_required ?? true,
        default_variant_id: item.default_variant_id ? `${item.default_variant_id}` : '',
        allowed_variant_ids: item.allowed_variant_ids || [],
        variant_change_allowed: Boolean(item.variant_change_allowed),
        discount_eligible: item.discount_eligible ?? true,
        excluded_upgrade: Boolean(item.excluded_upgrade),
        price_mode: item.price_mode || 'add_child_price',
        custom_price: item.custom_price ? `${item.custom_price}` : '',
        display_label: item.display_label || '',
        show_on_hero: item.show_on_hero ?? true,
        show_in_details: item.show_in_details ?? true,
    };
}

function selectedProductForBundle(products, item) {
    return products.find((product) => String(product.id) === String(item.child_product_id)) || null;
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
                        {item.allowed_variant_ids.map((variantId, variantIndex) => (
                            <input key={`${item.id}-allowed-${variantId}`} type="hidden" name={`bundle_items[${index}][allowed_variant_ids][${variantIndex}]`} value={variantId} />
                        ))}
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

                        <label className="nikah-field">
                            <span>Default variant</span>
                            <select
                                name={`bundle_items[${index}][default_variant_id]`}
                                value={item.default_variant_id}
                                onChange={(event) => onUpdateItem(item.id, 'default_variant_id', event.target.value)}
                            >
                                <option value="">Base/default</option>
                                {(selectedProductForBundle(products, item)?.variants || []).map((variant) => (
                                    <option key={variant.id} value={variant.id}>{variant.name}</option>
                                ))}
                            </select>
                        </label>

                        <label className="nikah-field">
                            <span>Display label</span>
                            <input
                                type="text"
                                name={`bundle_items[${index}][display_label]`}
                                value={item.display_label}
                                onChange={(event) => onUpdateItem(item.id, 'display_label', event.target.value)}
                                placeholder="Optional customer-facing label"
                            />
                        </label>

                        <label className="nikah-field">
                            <span>Price mode</span>
                            <select
                                name={`bundle_items[${index}][price_mode]`}
                                value={item.price_mode}
                                onChange={(event) => onUpdateItem(item.id, 'price_mode', event.target.value)}
                            >
                                <option value="add_child_price">Add child product price</option>
                                <option value="included_in_combo_price">Included in combo price</option>
                                <option value="custom_combo_price">Custom combo price</option>
                                <option value="upgrade_price_only">Upgrade price only</option>
                            </select>
                        </label>

                        <label className="nikah-field">
                            <span>Custom price</span>
                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                name={`bundle_items[${index}][custom_price]`}
                                value={item.custom_price}
                                onChange={(event) => onUpdateItem(item.id, 'custom_price', event.target.value)}
                            />
                        </label>

                        {[
                            ['is_required', 'Required'],
                            ['variant_change_allowed', 'Allow variant change'],
                            ['discount_eligible', 'Discount eligible'],
                            ['excluded_upgrade', 'Premium upgrade excluded'],
                            ['show_on_hero', 'Show in hero strip'],
                            ['show_in_details', 'Show in combo details'],
                        ].map(([field, label]) => (
                            <label key={field} className="general-checkbox">
                                <input type="hidden" name={`bundle_items[${index}][${field}]`} value="0" />
                                <input
                                    type="checkbox"
                                    name={`bundle_items[${index}][${field}]`}
                                    value="1"
                                    checked={Boolean(item[field])}
                                    onChange={(event) => onUpdateItem(item.id, field, event.target.checked)}
                                />
                                {label}
                            </label>
                        ))}

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

function ComboPricingEditor({ settings, onUpdate }) {
    return (
        <section className="bundle-item-editor">
            <div className="variant-media-mapper__heading">
                <div>
                    <h4>Combo pricing and marketing</h4>
                    <p>Set the extra bundle saving. Product-level discounts are detected automatically from compare-at and selling prices.</p>
                </div>
            </div>

            <div className="nikah-form__grid nikah-form__grid--two">
                <input type="hidden" name="combo_discount_type" value="percent" />

                <label className="nikah-field">
                    <span>Extra bundle saving percentage</span>
                    <input type="number" min="0" step="0.01" name="combo_discount_value" value={settings.discountValue} onChange={(event) => onUpdate('discountValue', event.target.value)} />
                    <small>Example: 3 means customers get 3% more off after the products' own discounts.</small>
                </label>

                <label className="nikah-field">
                    <span>Rounding rule</span>
                    <select name="combo_rounding_rule" value={settings.roundingRule} onChange={(event) => onUpdate('roundingRule', event.target.value)}>
                        <option value="none">No rounding</option>
                        <option value="nearest_10">Round to nearest 10</option>
                        <option value="nearest_50">Round to nearest 50</option>
                        <option value="nearest_100">Round to nearest 100</option>
                    </select>
                </label>

                <label className="nikah-field">
                    <span>Marketing label</span>
                    <input type="text" name="marketing_label" value={settings.marketingLabel} onChange={(event) => onUpdate('marketingLabel', event.target.value)} placeholder="Best value, Most gifted, Save 12%" />
                </label>

                <label className="nikah-field nikah-form__span-two">
                    <span>Promo headline</span>
                    <input type="text" name="combo_promo_headline" value={settings.promoHeadline} onChange={(event) => onUpdate('promoHeadline', event.target.value)} placeholder="Complete the set and save more" />
                </label>

                <label className="nikah-field nikah-form__span-two">
                    <span>Promo subtitle</span>
                    <textarea name="combo_promo_subtitle" rows="3" value={settings.promoSubtitle} onChange={(event) => onUpdate('promoSubtitle', event.target.value)} />
                </label>

                {[
                    ['showSavingsBadge', 'Show savings badge', 'show_combo_savings_badge'],
                    ['showRelatedCombosOnProduct', 'Show related combos on product pages', 'show_related_combos_on_product'],
                    ['showRelatedCombosInCart', 'Show related combos in cart', 'show_related_combos_in_cart'],
                ].map(([key, label, name]) => (
                    <label key={key} className="general-checkbox">
                        <input type="hidden" name={name} value="0" />
                        <input type="checkbox" name={name} value="1" checked={Boolean(settings[key])} onChange={(event) => onUpdate(key, event.target.checked)} />
                        {label}
                    </label>
                ))}
            </div>
        </section>
    );
}

function normalizeContentItem(item = {}, fallbackTitle = '') {
    return {
        id: item.id || uid('content-item'),
        title: item.title || item.question || fallbackTitle,
        description: item.description || item.answer || item.copy || '',
    };
}

function ContentRepeater({ label, name, items, onChange, titleLabel = 'Title', descriptionLabel = 'Description' }) {
    const normalizedItems = (items || []).map((item) => normalizeContentItem(item));

    function updateItem(itemId, key, value) {
        onChange(normalizedItems.map((item) => item.id === itemId ? { ...item, [key]: value } : item));
    }

    return (
        <section className="nikah-field nikah-form__span-two">
            <input type="hidden" name={name} value={JSON.stringify(normalizedItems.map(({ id, ...item }) => item))} />
            <span>{label}</span>
            <div className="bundle-item-editor__list">
                {normalizedItems.map((item) => (
                    <article key={item.id} className="bundle-item-editor__row">
                        <label className="nikah-field">
                            <span>{titleLabel}</span>
                            <input type="text" value={item.title} onChange={(event) => updateItem(item.id, 'title', event.target.value)} />
                        </label>
                        <label className="nikah-field">
                            <span>{descriptionLabel}</span>
                            <textarea rows="2" value={item.description} onChange={(event) => updateItem(item.id, 'description', event.target.value)} />
                        </label>
                        <button type="button" className="variant-icon-button variant-icon-button--danger" onClick={() => onChange(normalizedItems.filter((currentItem) => currentItem.id !== item.id))} aria-label={`Remove ${label}`}>
                            <span aria-hidden="true">✕</span>
                        </button>
                    </article>
                ))}
            </div>
            <button type="button" className="nikah-add-field" onClick={() => onChange([...normalizedItems, normalizeContentItem()])}>Add {label.toLowerCase()}</button>
        </section>
    );
}

function normalizePolicyItem(item = {}, fallbackTitle = '') {
    return {
        id: item.id || uid('policy-item'),
        label: item.label || item.title || fallbackTitle,
        value: item.value || item.description || item.copy || '',
    };
}

function ProductPolicyEditor({ rows, isCustomized, onRowsChange, onCustomizedChange, defaults }) {
    const visibleRows = (rows?.length ? rows : defaults).map((row, index) => normalizePolicyItem(row, `Policy ${index + 1}`));

    function updateRow(rowId, key, value) {
        onCustomizedChange(true);
        onRowsChange(visibleRows.map((row) => (row.id === rowId ? { ...row, [key]: value } : row)));
    }

    function resetDefaults() {
        onCustomizedChange(false);
        onRowsChange(defaults.map((row, index) => normalizePolicyItem(row, `Policy ${index + 1}`)));
    }

    return (
        <section className="nikah-step-card">
            {isCustomized ? (
                <input type="hidden" name="shipping_care_policy" value={JSON.stringify(visibleRows.map(({ id, ...row }) => row))} />
            ) : null}

            <div className="nikah-step-card__heading">
                <span className="nikah-step-card__step">P</span>
                <div>
                    <h3>Shipping, care, and policy</h3>
                    <p>Default policy text is shown on the storefront. Edit anything here only when this product needs custom policy copy.</p>
                </div>
            </div>

            <div className="bundle-item-editor__list">
                {visibleRows.map((row) => (
                    <article key={row.id} className="bundle-item-editor__row product-copy-row">
                        <label className="nikah-field">
                            <span>Label</span>
                            <input type="text" value={row.label} onChange={(event) => updateRow(row.id, 'label', event.target.value)} />
                        </label>
                        <label className="nikah-field">
                            <span>Value</span>
                            <textarea rows="2" value={row.value} onChange={(event) => updateRow(row.id, 'value', event.target.value)} />
                        </label>
                        <button
                            type="button"
                            className="variant-icon-button variant-icon-button--danger"
                            onClick={() => {
                                onCustomizedChange(true);
                                onRowsChange(visibleRows.filter((currentRow) => currentRow.id !== row.id));
                            }}
                            aria-label="Remove policy row"
                        >
                            <span aria-hidden="true">✕</span>
                        </button>
                    </article>
                ))}
            </div>

            <div className="template-picker-actions">
                <button type="button" className="nikah-add-field" onClick={() => {
                    onCustomizedChange(true);
                    onRowsChange([...visibleRows, normalizePolicyItem()]);
                }}>
                    Add policy row
                </button>
                <button type="button" className="button-ghost" onClick={resetDefaults}>
                    Use defaults
                </button>
            </div>
        </section>
    );
}

function ProductFaqEditor({
    defaultFaqs,
    selectedDefaultFaqIds,
    customFaqs,
    isCustomized,
    onSelectedDefaultFaqIdsChange,
    onCustomFaqsChange,
    onCustomizedChange,
}) {
    const normalizedCustomFaqs = (customFaqs || []).map((item) => normalizeContentItem(item, 'Question'));
    const selectedIds = selectedDefaultFaqIds || [];
    const serializedFaqs = [
        ...defaultFaqs
            .filter((faq) => selectedIds.includes(faq.id))
            .map((faq) => ({ question: faq.question, answer: faq.answer })),
        ...normalizedCustomFaqs.map(({ id, title, description }) => ({ question: title, answer: description })),
    ];

    function toggleDefaultFaq(id) {
        onCustomizedChange(true);
        onSelectedDefaultFaqIdsChange(
            selectedIds.includes(id)
                ? selectedIds.filter((selectedId) => selectedId !== id)
                : [...selectedIds, id],
        );
    }

    function updateCustomFaq(itemId, key, value) {
        onCustomizedChange(true);
        onCustomFaqsChange(normalizedCustomFaqs.map((item) => (item.id === itemId ? { ...item, [key]: value } : item)));
    }

    function resetDefaults() {
        onCustomizedChange(false);
        onSelectedDefaultFaqIdsChange(defaultFaqs.map((faq) => faq.id));
        onCustomFaqsChange([]);
    }

    return (
        <section className="nikah-step-card">
            {isCustomized ? (
                <input type="hidden" name="product_faqs" value={JSON.stringify(serializedFaqs)} />
            ) : null}

            <div className="nikah-step-card__heading">
                <span className="nikah-step-card__step">F</span>
                <div>
                    <h3>FAQ</h3>
                    <p>Default published FAQs are selected automatically. Uncheck or add product-specific questions when needed.</p>
                </div>
            </div>

            <div className="bundle-item-editor__list">
                {defaultFaqs.length ? defaultFaqs.map((faq) => (
                    <label key={faq.id} className="general-checkbox">
                        <input
                            type="checkbox"
                            checked={selectedIds.includes(faq.id)}
                            onChange={() => toggleDefaultFaq(faq.id)}
                        />
                        <span>
                            <strong>{faq.question}</strong>
                            <small>{faq.answer}</small>
                        </span>
                    </label>
                )) : (
                    <div className="nikah-empty-note">No published default FAQs yet. Add custom product FAQs below.</div>
                )}
            </div>

            <div className="bundle-item-editor__list">
                {normalizedCustomFaqs.map((item) => (
                    <article key={item.id} className="bundle-item-editor__row product-copy-row">
                        <label className="nikah-field">
                            <span>Question</span>
                            <input type="text" value={item.title} onChange={(event) => updateCustomFaq(item.id, 'title', event.target.value)} />
                        </label>
                        <label className="nikah-field">
                            <span>Answer</span>
                            <textarea rows="2" value={item.description} onChange={(event) => updateCustomFaq(item.id, 'description', event.target.value)} />
                        </label>
                        <button
                            type="button"
                            className="variant-icon-button variant-icon-button--danger"
                            onClick={() => {
                                onCustomizedChange(true);
                                onCustomFaqsChange(normalizedCustomFaqs.filter((currentItem) => currentItem.id !== item.id));
                            }}
                            aria-label="Remove FAQ"
                        >
                            <span aria-hidden="true">✕</span>
                        </button>
                    </article>
                ))}
            </div>

            <div className="template-picker-actions">
                <button type="button" className="nikah-add-field" onClick={() => {
                    onCustomizedChange(true);
                    onCustomFaqsChange([...normalizedCustomFaqs, normalizeContentItem({}, 'Question')]);
                }}>
                    Add new FAQ
                </button>
                <button type="button" className="button-ghost" onClick={resetDefaults}>
                    Use all default FAQs
                </button>
            </div>
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

                <label className="nikah-field">
                    <span>Minimum notice days</span>
                    <input
                        type="number"
                        min="0"
                        name="service_meta[minimum_notice_days]"
                        value={meta.minimum_notice_days || ''}
                        onChange={(event) => onUpdate('minimum_notice_days', event.target.value)}
                    />
                </label>

                <label className="nikah-field">
                    <span>Max bookings per day</span>
                    <input
                        type="number"
                        min="0"
                        name="service_meta[max_bookings_per_day]"
                        value={meta.max_bookings_per_day || ''}
                        onChange={(event) => onUpdate('max_bookings_per_day', event.target.value)}
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

                <label className="general-checkbox nikah-form__span-two">
                    <input type="hidden" name="service_meta[travel_outside_area_allowed]" value="0" />
                    <input
                        type="checkbox"
                        name="service_meta[travel_outside_area_allowed]"
                        value="1"
                        checked={Boolean(meta.travel_outside_area_allowed)}
                        onChange={(event) => onUpdate('travel_outside_area_allowed', event.target.checked)}
                    />
                    Travel outside area allowed
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

                <label className="nikah-field nikah-form__span-two">
                    <span>Confirmation note</span>
                    <textarea
                        name="service_meta[confirmation_note]"
                        rows="3"
                        value={meta.confirmation_note || ''}
                        onChange={(event) => onUpdate('confirmation_note', event.target.value)}
                        placeholder="Explain availability check, confirmation, and advance payment flow."
                    />
                </label>

                <label className="nikah-field">
                    <span>Available areas</span>
                    <textarea name="service_meta[available_areas]" rows="3" value={meta.available_areas || ''} onChange={(event) => onUpdate('available_areas', event.target.value)} />
                </label>

                <label className="nikah-field">
                    <span>Available days / time slots</span>
                    <textarea name="service_meta[time_slot_options]" rows="3" value={meta.time_slot_options || ''} onChange={(event) => onUpdate('time_slot_options', event.target.value)} />
                </label>

                <label className="nikah-field nikah-form__span-two">
                    <span>Extra charge note</span>
                    <textarea name="service_meta[extra_charge_note]" rows="3" value={meta.extra_charge_note || ''} onChange={(event) => onUpdate('extra_charge_note', event.target.value)} />
                </label>

                <ContentRepeater label="Service includes" name="service_meta[include_items]" items={meta.include_items || []} onChange={(items) => onUpdate('include_items', items)} />
                <ContentRepeater label="Packages / scopes" name="service_meta[packages]" items={meta.packages || []} onChange={(items) => onUpdate('packages', items)} />
                <ContentRepeater label="Before appointment" name="service_meta[before_appointment]" items={meta.before_appointment || []} onChange={(items) => onUpdate('before_appointment', items)} />
                <ContentRepeater label="Pricing notes" name="service_meta[pricing_notes]" items={meta.pricing_notes || []} onChange={(items) => onUpdate('pricing_notes', items)} />
                <ContentRepeater label="Policies" name="service_meta[policies]" items={meta.policies || []} onChange={(items) => onUpdate('policies', items)} />
                <ContentRepeater label="FAQs" name="service_meta[faqs]" items={meta.faqs || []} onChange={(items) => onUpdate('faqs', items)} titleLabel="Question" descriptionLabel="Answer" />

                <label className="nikah-field nikah-form__span-two">
                    <span>Gallery intro text</span>
                    <textarea name="service_meta[gallery_intro_text]" rows="3" value={meta.gallery_intro_text || ''} onChange={(event) => onUpdate('gallery_intro_text', event.target.value)} />
                </label>
            </div>
        </section>
    );
}

function LightCustomizationEditor({ fields, helpText, onHelpTextChange, onAddField, onRemoveField, onUpdateField }) {
    return (
        <section className="nikah-step-card">
            <div className="nikah-step-card__heading">
                <span className="nikah-step-card__step">5</span>
                <div>
                    <h3>Light custom fields</h3>
                    <p>Create simple customer inputs for this product. Preset values act as quick suggestions; customers can still write their own.</p>
                </div>
            </div>

            <label className="nikah-field">
                <span>Customer help text</span>
                <textarea
                    name="personalization_help_text"
                    rows="3"
                    value={helpText}
                    onChange={(event) => onHelpTextChange(event.target.value)}
                    placeholder="Explain what the customer can customize."
                />
            </label>

            <div className="light-field-editor">
                {fields.length ? fields.map((field, index) => (
                    <article key={field.id} className="light-field-card">
                        <div className="light-field-card__header">
                            <div>
                                <strong>{field.label || `Custom field ${index + 1}`}</strong>
                                <span>{field.field_key || `field_${index + 1}`}</span>
                            </div>
                            <button type="button" className="variant-icon-button variant-icon-button--danger" onClick={() => onRemoveField(field.id)} aria-label={`Remove ${field.label || 'field'}`}>
                                ×
                            </button>
                        </div>

                        <div className="nikah-form__grid nikah-form__grid--two">
                            <label className="nikah-field">
                                <span>Field label</span>
                                <input value={field.label} onChange={(event) => onUpdateField(field.id, 'label', event.target.value)} placeholder="Quotation" />
                            </label>

                            <label className="nikah-field">
                                <span>Field key</span>
                                <input value={snakeCaseFieldKey(field.label) || field.field_key || `custom_field_${index + 1}`} readOnly aria-readonly="true" />
                                <small>Generated automatically from the field label.</small>
                            </label>

                            <label className="nikah-field">
                                <span>Field type</span>
                                <select value={field.type || 'text'} onChange={(event) => onUpdateField(field.id, 'type', event.target.value)}>
                                    {LIGHT_FIELD_TYPES.map((type) => (
                                        <option key={type.value} value={type.value}>{type.label}</option>
                                    ))}
                                </select>
                            </label>

                            <label className="general-checkbox">
                                <input
                                    type="checkbox"
                                    checked={Boolean(field.is_required)}
                                    onChange={(event) => onUpdateField(field.id, 'is_required', event.target.checked)}
                                />
                                Required field
                            </label>

                            <label className="nikah-field nikah-form__span-two">
                                <span>Preset values</span>
                                <textarea
                                    rows="3"
                                    value={presetValuesText(field)}
                                    onChange={(event) => onUpdateField(field.id, 'preset_values_text', event.target.value)}
                                    placeholder={'One value per line, for example:\nBismillah quote\nAmeen note\nCustom dua'}
                                />
                                <small>Customers can choose one of these suggestions or type their own answer.</small>
                            </label>

                            <label className="nikah-field nikah-form__span-two">
                                <span>Help text</span>
                                <input value={field.help_text || ''} onChange={(event) => onUpdateField(field.id, 'help_text', event.target.value)} placeholder="Optional hint shown under this field" />
                            </label>
                        </div>
                    </article>
                )) : (
                    <div className="nikah-empty-note">No custom fields yet. Add a field for names, quotations, dates, or any short order detail.</div>
                )}
            </div>

            <button type="button" className="nikah-add-field" onClick={onAddField}>Add custom field</button>
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
        defaultPolicyRows = [],
        defaultProductFaqs = [],
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
    const normalizedDefaultPolicyRows = defaultPolicyRows.map((row, index) => normalizePolicyItem(row, `Policy ${index + 1}`));
    const normalizedDefaultFaqs = defaultProductFaqs.map((faq) => ({
        id: Number(faq.id),
        question: faq.question || '',
        answer: faq.answer || '',
    }));

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
    const [comboSettings, setComboSettings] = useState({
        discountType: product.comboDiscountType || 'percent',
        discountValue: product.comboDiscountValue || '',
        roundingRule: product.comboRoundingRule || 'none',
        showSavingsBadge: product.showComboSavingsBadge ?? true,
        promoHeadline: product.comboPromoHeadline || '',
        promoSubtitle: product.comboPromoSubtitle || '',
        marketingLabel: product.marketingLabel || '',
        showRelatedCombosOnProduct: product.showRelatedCombosOnProduct ?? true,
        showRelatedCombosInCart: product.showRelatedCombosInCart ?? true,
    });
    const [serviceMeta, setServiceMeta] = useState(product.serviceMeta || {});
    const [variantGroups, setVariantGroups] = useState(initialVariantGroups);
    const [selectedDesignId, setSelectedDesignId] = useState(initialDesignId);
    const [activeMockups, setActiveMockups] = useState(product.isNew ? [] : (product.activeMockupIds || []));
    const [defaultMockupId, setDefaultMockupId] = useState(product.isNew ? '' : (product.defaultMockupId || ''));
    const [personalizationFields, setPersonalizationFields] = useState(initialFields);
    const [variantMediaLinks, setVariantMediaLinks] = useState(product.variantMediaLinks || {});
    const [policyRows, setPolicyRows] = useState(
        (product.shippingCarePolicy || normalizedDefaultPolicyRows).map((row, index) => normalizePolicyItem(row, `Policy ${index + 1}`)),
    );
    const [policyCustomized, setPolicyCustomized] = useState(Boolean(product.shippingCarePolicy));
    const [selectedDefaultFaqIds, setSelectedDefaultFaqIds] = useState(
        product.productFaqs
            ? []
            : normalizedDefaultFaqs.map((faq) => faq.id),
    );
    const [customFaqs, setCustomFaqs] = useState(
        (product.productFaqs || []).map((faq) => normalizeContentItem({
            title: faq.question || faq.title,
            description: faq.answer || faq.description,
        }, 'Question')),
    );
    const [faqCustomized, setFaqCustomized] = useState(Boolean(product.productFaqs));
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
    const [templateModalOpen, setTemplateModalOpen] = useState(false);
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
    const isLightMode = currentType === LIGHT_TYPE;
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
    const productImageOptions = [
        ...(savedFeaturedImageUrl ? [{
            id: 'featured',
            label: 'Featured image',
            thumb: savedFeaturedImageUrl,
            alt_text: productName || 'Featured image',
        }] : []),
        ...existingImages.map((image, index) => ({
            id: `${image.id}`,
            label: `${index + 1}. ${image.label || image.alt_text || 'Product image'}`,
            thumb: image.image_url,
            alt_text: image.alt_text,
        })),
    ];
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
        { id: 'mapping', label: 'Mockup mapping' },
        { id: 'policy', label: 'Shipping, care, and policy' },
        { id: 'faq', label: 'FAQ' },
        { id: 'related', label: 'Related' },
        { id: 'seo', label: 'SEO' },
    ];
    const generalTabs = [
        { id: 'setup', label: 'Setup' },
        { id: 'organization', label: 'Organization' },
        { id: 'pricing', label: 'Pricing' },
        { id: 'variants', label: 'Variants' },
        { id: 'image_mapping', label: 'Image mapping' },
        ...(currentType === 'bundle' ? [{ id: 'combo', label: 'Combo' }] : []),
        ...(currentType === 'service' ? [{ id: 'service', label: 'Service' }] : []),
        ...(isLightMode ? [{ id: 'custom_fields', label: 'Custom fields' }] : []),
        { id: 'media', label: 'Media' },
        { id: 'policy', label: 'Shipping, care, and policy' },
        { id: 'faq', label: 'FAQ' },
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

    function selectDesign(designId) {
        setSelectedDesignId(designId ? Number(designId) : '');
        setTemplateModalOpen(false);
    }

    function updateField(fieldId, key, value) {
        setPersonalizationFields((currentFields) => currentFields.map((field) => {
            if (field.id !== fieldId) {
                return field;
            }

            if (key === 'label') {
                return {
                    ...field,
                    label: value,
                    field_key: snakeCaseFieldKey(value),
                };
            }

            if (key === 'preset_values_text') {
                return {
                    ...field,
                    preset_values_text: value,
                    preset_values: normalizePresetValues(value),
                };
            }

            return { ...field, [key]: value };
        }));
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

    function updateComboSetting(key, value) {
        setComboSettings((currentSettings) => ({ ...currentSettings, [key]: value }));
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
                help_text: '',
                preset_values: [],
                preset_values_text: '',
                allow_custom_value: true,
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
            <input type="hidden" name="personalization_fields_blueprint" value={(isAdvancedMode || isLightMode) ? JSON.stringify(serializePersonalizationFields(personalizationFields)) : ''} />

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

                                <div className="template-picker-stack">
                                    {selectedDesign ? (
                                        <div className="selected-design-preview">
                                            <div className="selected-design-preview__thumb">
                                                {designThumbnail ? <img src={designThumbnail} alt={selectedDesign.name} /> : <span>No image</span>}
                                            </div>
                                            <div className="selected-design-preview__meta">
                                                <strong>{selectedDesign.name}</strong>
                                                <span>{selectedDesign.fields.length} personalization fields</span>
                                                <div className="template-picker-actions">
                                                    <button type="button" className="button-ghost" onClick={() => setTemplateModalOpen(true)}>
                                                        Change template
                                                    </button>
                                                    <button type="button" className="button-ghost" onClick={() => selectDesign('')}>
                                                        Clear
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    ) : (
                                        <div className="template-picker-empty">
                                            <div>
                                                <strong>No template selected</strong>
                                                <span>Select one unused personalization template to unlock the field setup. Mockups are assigned separately below.</span>
                                            </div>
                                            <button type="button" className="button-primary" onClick={() => setTemplateModalOpen(true)}>
                                                Choose template
                                            </button>
                                        </div>
                                    )}
                                    {getError(errors, 'assigned_template_id') ? <p className="nikah-inline-error">{getError(errors, 'assigned_template_id')}</p> : null}
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
                            </section>

                            <section className="nikah-step-card nikah-step-card--accent-amber" hidden={advancedTab !== 'mapping'}>
                                <div className="nikah-step-card__heading">
                                    <span className="nikah-step-card__step">7</span>
                                    <div>
                                        <h3>Variant mockup mapping</h3>
                                        <p>Map frame sizes, styles, or finishes to the storefront scene customers should see.</p>
                                    </div>
                                </div>

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

                            <div hidden={advancedTab !== 'policy'}>
                                <ProductPolicyEditor
                                    rows={policyRows}
                                    isCustomized={policyCustomized}
                                    onRowsChange={setPolicyRows}
                                    onCustomizedChange={setPolicyCustomized}
                                    defaults={normalizedDefaultPolicyRows}
                                />
                            </div>

                            <div hidden={advancedTab !== 'faq'}>
                                <ProductFaqEditor
                                    defaultFaqs={normalizedDefaultFaqs}
                                    selectedDefaultFaqIds={selectedDefaultFaqIds}
                                    customFaqs={customFaqs}
                                    isCustomized={faqCustomized}
                                    onSelectedDefaultFaqIdsChange={setSelectedDefaultFaqIds}
                                    onCustomFaqsChange={setCustomFaqs}
                                    onCustomizedChange={setFaqCustomized}
                                />
                            </div>

                            <section className="nikah-step-card" hidden={advancedTab !== 'related'}>
                                <div className="nikah-step-card__heading">
                                    <span className="nikah-step-card__step">8</span>
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

                        </section>

                        <section className="nikah-step-card" hidden={generalTab !== 'image_mapping'}>
                            <div className="nikah-step-card__heading">
                                <span className="nikah-step-card__step">5</span>
                                <div>
                                    <h3>Variant image mapping</h3>
                                    <p>Map each color, size, or option value to the product image customers should see.</p>
                                </div>
                            </div>

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

                                <ComboPricingEditor settings={comboSettings} onUpdate={updateComboSetting} />

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

                        {isLightMode ? (
                            <div hidden={generalTab !== 'custom_fields'}>
                                <LightCustomizationEditor
                                    fields={personalizationFields}
                                    helpText={personalizationHelpText}
                                    onHelpTextChange={setPersonalizationHelpText}
                                    onAddField={addField}
                                    onRemoveField={removeField}
                                    onUpdateField={updateField}
                                />
                            </div>
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

                        <div hidden={generalTab !== 'policy'}>
                            <ProductPolicyEditor
                                rows={policyRows}
                                isCustomized={policyCustomized}
                                onRowsChange={setPolicyRows}
                                onCustomizedChange={setPolicyCustomized}
                                defaults={normalizedDefaultPolicyRows}
                            />
                        </div>

                        <div hidden={generalTab !== 'faq'}>
                            <ProductFaqEditor
                                defaultFaqs={normalizedDefaultFaqs}
                                selectedDefaultFaqIds={selectedDefaultFaqIds}
                                customFaqs={customFaqs}
                                isCustomized={faqCustomized}
                                onSelectedDefaultFaqIdsChange={setSelectedDefaultFaqIds}
                                onCustomFaqsChange={setCustomFaqs}
                                onCustomizedChange={setFaqCustomized}
                            />
                        </div>

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

            {isAdvancedMode && templateModalOpen ? (
                <div className="variant-media-modal" role="dialog" aria-modal="true" aria-label="Choose personalization template">
                    <div className="variant-media-modal__backdrop" onClick={() => setTemplateModalOpen(false)} />
                    <div className="variant-media-modal__panel template-picker-modal">
                        <div className="variant-media-modal__header">
                            <div>
                                <h4>Choose personalization template</h4>
                                <p>Only unused active templates are available for new product assignments.</p>
                            </div>
                            <button type="button" className="variant-icon-button" onClick={() => setTemplateModalOpen(false)} aria-label="Close">
                                <span aria-hidden="true">✕</span>
                            </button>
                        </div>

                        {designs.length ? (
                            <div className="template-picker-modal__grid">
                                {designs.map((design) => {
                                    const previewUrl = design.rendered_preview_url || design.thumbnail_url || design.preview_url || '';
                                    const isSelected = String(design.id) === String(selectedDesignId);

                                    return (
                                        <button
                                            key={design.id}
                                            type="button"
                                            className={`template-picker-card ${isSelected ? 'is-selected' : ''}`}
                                            onClick={() => selectDesign(design.id)}
                                        >
                                            <span className="template-picker-card__image">
                                                {previewUrl ? <img src={previewUrl} alt={design.name} /> : <span>No image</span>}
                                            </span>
                                            <span className="template-picker-card__copy">
                                                <strong>{design.name}</strong>
                                                <span>{design.fields.length} field{design.fields.length === 1 ? '' : 's'}</span>
                                            </span>
                                        </button>
                                    );
                                })}
                            </div>
                        ) : (
                            <div className="nikah-empty-note">No unused active templates available. Create or duplicate a template first, then return to this form.</div>
                        )}

                        <div className="variant-media-modal__footer">
                            <button type="button" className="button-ghost" onClick={() => setTemplateModalOpen(false)}>
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            ) : null}
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
