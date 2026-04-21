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
const DEFAULT_FIELDS = [
    { label: 'Bride name', field_key: 'bride_name', type: 'text', is_required: true },
    { label: 'Groom name', field_key: 'groom_name', type: 'text', is_required: true },
    { label: 'Nikah date', field_key: 'nikah_date', type: 'date', is_required: true },
];

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

function createVariantOption(name = '', value = '') {
    return {
        id: uid('variant-option'),
        name,
        value,
    };
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
            <div className="general-media-row__fields">
                <label className="nikah-field">
                    <span>Label</span>
                    <select name={`existing_images[${image.id}][label]`} defaultValue={image.label || 'gallery'}>
                        {EXISTING_IMAGE_LABELS.map((label) => (
                            <option key={label} value={label}>{label}</option>
                        ))}
                    </select>
                </label>

                <label className="nikah-field">
                    <span>Sort order</span>
                    <input type="number" min="0" name={`existing_images[${image.id}][position]`} defaultValue={image.position ?? 0} />
                </label>

                <label className="nikah-field general-media-row__wide">
                    <span>Alt text</span>
                    <input type="text" name={`existing_images[${image.id}][alt_text]`} defaultValue={image.alt_text || ''} />
                </label>

                <label className="general-checkbox">
                    <input type="hidden" name={`existing_images[${image.id}][is_primary]`} value="0" />
                    <input type="checkbox" name={`existing_images[${image.id}][is_primary]`} value="1" defaultChecked={image.is_primary} />
                    Mark as primary
                </label>

                <label className="general-checkbox general-checkbox--danger">
                    <input type="hidden" name={`existing_images[${image.id}][remove]`} value="0" />
                    <input type="checkbox" name={`existing_images[${image.id}][remove]`} value="1" />
                    Remove image
                </label>
            </div>
        </article>
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
    emptyMessage,
}) {
    return (
        <>
            <div className="general-variant-list">
                {variants.length ? variants.map((variant, index) => (
                    <article key={variant.id} className="general-variant-card">
                        <div className="general-variant-card__header">
                            <strong>Variant {index + 1}</strong>
                            <button type="button" className="nikah-field-row__remove" onClick={() => onRemoveVariant(variant.id)}>
                                Remove
                            </button>
                        </div>

                        <div className="nikah-form__grid nikah-form__grid--two">
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

                            <label className="nikah-field">
                                <span>Variant price</span>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name={`variants[${index}][price]`}
                                    value={variant.price}
                                    onChange={(event) => onUpdateVariant(variant.id, 'price', event.target.value)}
                                />
                            </label>

                            <label className="nikah-field">
                                <span>Compare-at price</span>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name={`variants[${index}][compare_at_price]`}
                                    value={variant.compare_at_price}
                                    onChange={(event) => onUpdateVariant(variant.id, 'compare_at_price', event.target.value)}
                                />
                            </label>

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
    const [variants, setVariants] = useState((product.variants || []).map((variant, index) => normalizeVariant(variant, index)));
    const [selectedDesignId, setSelectedDesignId] = useState(initialDesignId);
    const [activeMockups, setActiveMockups] = useState(product.isNew ? [] : (product.activeMockupIds || []));
    const [defaultMockupId, setDefaultMockupId] = useState(product.isNew ? '' : (product.defaultMockupId || ''));
    const [personalizationFields, setPersonalizationFields] = useState(initialFields);
    const [price, setPrice] = useState(product.price || '');
    const [compareAtPrice, setCompareAtPrice] = useState(product.compareAtPrice || '');
    const [leadTimeDays, setLeadTimeDays] = useState(product.leadTimeDays || '');
    const [status, setStatus] = useState(product.status || 'draft');
    const [manageStock, setManageStock] = useState(Boolean(product.manageStock ?? true));
    const [stockQuantity, setStockQuantity] = useState(product.stockQuantity || '0');
    const [lowStockThreshold, setLowStockThreshold] = useState(product.lowStockThreshold || '0');
    const [isFeatured, setIsFeatured] = useState(Boolean(product.isFeatured));
    const [videoUrl, setVideoUrl] = useState(product.videoUrl || '');
    const [personalizationHelpText, setPersonalizationHelpText] = useState(product.personalizationHelpText || '');
    const [metaTitle, setMetaTitle] = useState(product.metaTitle || '');
    const [metaDescription, setMetaDescription] = useState(product.metaDescription || '');
    const [draggedFieldId, setDraggedFieldId] = useState(null);
    const previousDesignId = useRef(selectedDesignId);
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
    const designThumbnail = selectedDesign?.thumbnail_url || selectedDesign?.preview_url || '';
    const zoneBox = getZoneBox(activeFrame);
    const hasDesign = Boolean(selectedDesignId);
    const hasActiveMockups = activeMockups.length > 0;
    const hasZone = Boolean(activeFrame && zoneBox.hasZone);
    const hasFields = personalizationFields.length > 0 && personalizationFields.every((field) => field.label.trim() !== '');
    const basePublishReady = Boolean(productName.trim() && categoryId && `${price}`.trim() !== '');
    const canPublish = isAdvancedMode
        ? basePublishReady && hasDesign && hasActiveMockups && hasZone && hasFields
        : basePublishReady;

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
                            <section className="nikah-step-card">
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

                            <section className="nikah-step-card nikah-step-card--accent-blue">
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

                            <section className="nikah-step-card nikah-step-card--accent-amber">
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
                                            <strong>Active frame</strong>
                                            <span>{activeFrame?.title || 'Choose a frame'}</span>
                                        </div>
                                        <div className="nikah-stage">
                                            {activeFrame?.base_image_url ? <img src={activeFrame.base_image_url} alt={activeFrame.title} className="nikah-stage__scene" /> : null}
                                            {designThumbnail ? (
                                                <div
                                                    className="nikah-stage__certificate"
                                                    style={{
                                                        left: `${zoneBox.left}%`,
                                                        top: `${zoneBox.top}%`,
                                                        width: `${zoneBox.width}%`,
                                                        height: `${zoneBox.height}%`,
                                                    }}
                                                >
                                                    <img src={designThumbnail} alt={selectedDesign?.name || 'Selected design'} />
                                                </div>
                                            ) : null}
                                            <div
                                                className={`nikah-stage__zone ${zoneBox.hasZone ? 'is-defined' : 'is-missing'}`}
                                                style={{
                                                    left: `${zoneBox.left}%`,
                                                    top: `${zoneBox.top}%`,
                                                    width: `${zoneBox.width}%`,
                                                    height: `${zoneBox.height}%`,
                                                }}
                                            >
                                                <span>{zoneBox.hasZone ? 'Zone defined ✓' : 'Zone missing'}</span>
                                            </div>
                                            {activeFrame?.overlay_image_url ? <img src={activeFrame.overlay_image_url} alt="" className="nikah-stage__overlay" /> : null}
                                        </div>
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
                                                                {isLead ? 'Active frame' : 'Make active'}
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

                            <section className="nikah-step-card">
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

                            <section className="nikah-step-card">
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

                            <section className="nikah-step-card">
                                <div className="nikah-step-card__heading">
                                    <span className="nikah-step-card__step">6</span>
                                    <div>
                                        <h3>Variants</h3>
                                        <p>Add frame sizes, styles, and finishes with their own SKU, price, and optional stock count.</p>
                                    </div>
                                </div>

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
                                    emptyMessage="No variants added yet. Leave this empty for a single-option Nikahnama product."
                                />
                            </section>

                            <section className="nikah-step-card">
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

                            <SharedSeoCard
                                metaTitle={metaTitle}
                                onMetaTitleChange={handleMetaTitleChange}
                                metaDescription={metaDescription}
                                onMetaDescriptionChange={handleMetaDescriptionChange}
                                errors={errors}
                                step="8"
                            />
                        </div>

                        <aside className="nikah-form__sidebar">
                            <section className="nikah-sidebar-card">
                                <div className="nikah-sidebar-card__heading">
                                    <h3>Storefront preview</h3>
                                    <p>What the customer sees first.</p>
                                </div>

                                <div className="nikah-preview-card">
                                    <div className="nikah-stage nikah-stage--sidebar">
                                        {activeFrame?.base_image_url ? <img src={activeFrame.base_image_url} alt={activeFrame.title} className="nikah-stage__scene" /> : null}
                                        {designThumbnail ? (
                                            <div
                                                className="nikah-stage__certificate"
                                                style={{
                                                    left: `${zoneBox.left}%`,
                                                    top: `${zoneBox.top}%`,
                                                    width: `${zoneBox.width}%`,
                                                    height: `${zoneBox.height}%`,
                                                }}
                                            >
                                                <img src={designThumbnail} alt={selectedDesign?.name || 'Selected design'} />
                                            </div>
                                        ) : null}
                                        {activeFrame?.overlay_image_url ? <img src={activeFrame.overlay_image_url} alt="" className="nikah-stage__overlay" /> : null}
                                    </div>

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
                        <section className="nikah-step-card">
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

                        <section className="nikah-step-card">
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

                        <section className="nikah-step-card">
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

                        <section className="nikah-step-card">
                            <div className="nikah-step-card__heading">
                                <span className="nikah-step-card__step">4</span>
                                <div>
                                    <h3>Variants</h3>
                                    <p>Add purchasable options with their own SKU, price, and optional stock count.</p>
                                </div>
                            </div>

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
                                emptyMessage="No variants added yet. Leave this empty for a single-price product."
                            />
                        </section>

                        <section className="nikah-step-card">
                            <div className="nikah-step-card__heading">
                                <span className="nikah-step-card__step">5</span>
                                <div>
                                    <h3>Media</h3>
                                    <p>Upload the featured image, gallery media, and optional supporting video URL.</p>
                                </div>
                            </div>

                            <div className="nikah-form__grid nikah-form__grid--two">
                                <label className="nikah-field">
                                    <span>Featured image</span>
                                    <input type="file" name="featured_image_upload" accept="image/*" />
                                    {getError(errors, 'featured_image_upload') ? <small>{getError(errors, 'featured_image_upload')}</small> : null}
                                </label>

                                <label className="nikah-field">
                                    <span>Gallery uploads</span>
                                    <input type="file" name="gallery_uploads[]" accept="image/*" multiple />
                                </label>

                                <label className="nikah-field nikah-form__span-two">
                                    <span>Video URL</span>
                                    <input type="url" name="video_url" value={videoUrl} onChange={(event) => setVideoUrl(event.target.value)} />
                                </label>
                            </div>

                            <div className="general-media-list">
                                {existingImages.length ? existingImages.map((image) => (
                                    <ExistingImageRow key={image.id} image={image} />
                                )) : (
                                    <div className="nikah-empty-note">No gallery images uploaded yet.</div>
                                )}
                            </div>
                        </section>

                        <SharedSeoCard
                            metaTitle={metaTitle}
                            onMetaTitleChange={handleMetaTitleChange}
                            metaDescription={metaDescription}
                            onMetaDescriptionChange={handleMetaDescriptionChange}
                            errors={errors}
                            step="6"
                        />
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
