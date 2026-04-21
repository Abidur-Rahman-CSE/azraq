import React, { useEffect, useRef, useState } from 'react';
import { createRoot } from 'react-dom/client';

const STATUS_OPTIONS = [
    { value: 'draft', label: 'Draft' },
    { value: 'active', label: 'Published' },
    { value: 'archived', label: 'Archived' },
];

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

function formatPrice(value) {
    const numericValue = Number(value || 0);

    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        maximumFractionDigits: 2,
    }).format(Number.isFinite(numericValue) ? numericValue : 0);
}

function getError(errors, key) {
    return errors?.[key]?.[0] || '';
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
                    {crumb.href ? (
                        <a href={crumb.href}>{crumb.label}</a>
                    ) : (
                        <span className="is-current">{crumb.label}</span>
                    )}
                </React.Fragment>
            ))}
        </div>
    );
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

function NikahProductForm({ payload }) {
    const {
        product,
        categories,
        relatedCategories,
        tags,
        relatedProducts,
        designs,
        errors,
        page,
    } = payload;
    const initialDesignId = product.selectedDesignId || designs[0]?.id || '';
    const initialDesign = designs.find((design) => String(design.id) === String(initialDesignId)) || null;
    const initialFields = (product.personalizationFields || []).length
        ? product.personalizationFields.map((field, index) => normalizeField(field, index))
        : fieldsFromDesign(initialDesign);

    const [productName, setProductName] = useState(product.name || '');
    const [excerpt, setExcerpt] = useState(product.excerpt || '');
    const [categoryId, setCategoryId] = useState(product.categoryId || '');
    const [selectedTags, setSelectedTags] = useState(product.tagIds || []);
    const [selectedRelatedProducts, setSelectedRelatedProducts] = useState(product.relatedProductIds || []);
    const [selectedRelatedCategories, setSelectedRelatedCategories] = useState(product.relatedCategoryIds || []);
    const [selectedDesignId, setSelectedDesignId] = useState(initialDesignId);
    const [activeMockups, setActiveMockups] = useState(product.activeMockupIds || []);
    const [defaultMockupId, setDefaultMockupId] = useState(product.defaultMockupId || '');
    const [personalizationFields, setPersonalizationFields] = useState(initialFields);
    const [price, setPrice] = useState(product.price || '');
    const [compareAtPrice, setCompareAtPrice] = useState(product.compareAtPrice || '');
    const [status, setStatus] = useState(product.status || 'draft');
    const [draggedFieldId, setDraggedFieldId] = useState(null);
    const previousDesignId = useRef(selectedDesignId);

    const selectedDesign = designs.find((design) => String(design.id) === String(selectedDesignId)) || null;
    const availableMockups = selectedDesign?.mockups || [];
    const activeFrame = availableMockups.find((mockup) => String(mockup.id) === String(defaultMockupId))
        || availableMockups.find((mockup) => activeMockups.includes(mockup.id))
        || availableMockups[0]
        || null;
    const designThumbnail = selectedDesign?.thumbnail_url || selectedDesign?.preview_url || '';
    const zoneBox = getZoneBox(activeFrame);
    const hasDesign = Boolean(selectedDesignId);
    const hasActiveMockups = activeMockups.length > 0;
    const hasZone = Boolean(activeFrame && zoneBox.hasZone);
    const hasFields = personalizationFields.length > 0 && personalizationFields.every((field) => field.label.trim() !== '');
    const canPublish = hasDesign && hasActiveMockups && hasZone && hasFields;

    useEffect(() => {
        const validMockupIds = new Set(availableMockups.map((mockup) => mockup.id));
        const filteredActiveMockups = activeMockups.filter((id) => validMockupIds.has(id));

        if (filteredActiveMockups.length !== activeMockups.length) {
            setActiveMockups(filteredActiveMockups);
        }

        if (!filteredActiveMockups.length && availableMockups.length) {
            setActiveMockups([availableMockups[0].id]);
        }

        if (
            defaultMockupId
            && !validMockupIds.has(defaultMockupId)
            && (filteredActiveMockups[0] || availableMockups[0])
        ) {
            setDefaultMockupId(filteredActiveMockups[0] || availableMockups[0].id);
        }

        if (!defaultMockupId && (filteredActiveMockups[0] || availableMockups[0])) {
            setDefaultMockupId(filteredActiveMockups[0] || availableMockups[0].id);
        }

        if (previousDesignId.current !== selectedDesignId) {
            previousDesignId.current = selectedDesignId;
            setPersonalizationFields(fieldsFromDesign(selectedDesign));
        }
    }, [activeMockups, availableMockups, defaultMockupId, selectedDesign, selectedDesignId]);

    function toggleTag(tagId) {
        setSelectedTags((currentTags) => (
            currentTags.includes(tagId)
                ? currentTags.filter((id) => id !== tagId)
                : [...currentTags, tagId]
        ));
    }

    function toggleRelatedProduct(productId) {
        setSelectedRelatedProducts((currentProducts) => (
            currentProducts.includes(productId)
                ? currentProducts.filter((id) => id !== productId)
                : [...currentProducts, productId]
        ));
    }

    function toggleRelatedCategory(relatedCategoryId) {
        setSelectedRelatedCategories((currentCategories) => (
            currentCategories.includes(relatedCategoryId)
                ? currentCategories.filter((id) => id !== relatedCategoryId)
                : [...currentCategories, relatedCategoryId]
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
            <input type="hidden" name="type" value={product.currentType || 'advanced_personalized'} />
            <input type="hidden" name="status" value={status} />
            <input type="hidden" name="assigned_template_id" value={selectedDesignId || ''} />
            <input type="hidden" name="personalization_fields_blueprint" value={JSON.stringify(personalizationFields)} />
            <input type="hidden" name="proof_notes_enabled" value="1" />
            <input type="hidden" name="font_presets_enabled" value="1" />
            <input type="hidden" name="live_preview_enabled" value="1" />
            <input type="hidden" name="include_mockup_gallery" value="1" />
            <input type="hidden" name="show_flat_preview_first" value="0" />
            <input type="hidden" name="gallery_default_source" value="selected_mockup" />

            {selectedTags.map((tagId) => (
                <input key={tagId} type="hidden" name="tag_ids[]" value={tagId} />
            ))}

            {selectedRelatedProducts.map((relatedProductId) => (
                <input key={relatedProductId} type="hidden" name="related_product_ids[]" value={relatedProductId} />
            ))}

            {selectedRelatedCategories.map((relatedCategoryId) => (
                <input key={relatedCategoryId} type="hidden" name="related_category_ids[]" value={relatedCategoryId} />
            ))}

            {activeMockups.map((mockupId) => (
                <input key={mockupId} type="hidden" name="allowed_mockup_ids[]" value={mockupId} />
            ))}

            <input type="hidden" name="default_mockup_id" value={defaultMockupId || ''} />

            <div className="nikah-form">
                <div className="nikah-form__header">
                    <div className="nikah-form__header-copy">
                        <Breadcrumbs items={page.breadcrumbs} />
                        <h2>{page.heading}</h2>
                        <p>Configure the certificate design, mapped mockup scenes, and publish-readiness in one focused workflow.</p>
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

                <div className="nikah-form__layout">
                    <div className="nikah-form__main">
                        <section className="nikah-step-card">
                            <div className="nikah-step-card__heading">
                                <span className="nikah-step-card__step">1</span>
                                <div>
                                    <h3>Product identity</h3>
                                    <p>Name the product exactly how it should appear in storefront search and on the PDP.</p>
                                </div>
                            </div>

                            <div className="nikah-form__grid nikah-form__grid--two">
                                <label className="nikah-field nikah-form__span-two">
                                    <span>Product name</span>
                                    <input
                                        name="name"
                                        value={productName}
                                        onChange={(event) => setProductName(event.target.value)}
                                        placeholder="Royal Signature Nikahnama"
                                    />
                                    {getError(errors, 'name') ? <small>{getError(errors, 'name')}</small> : null}
                                </label>

                                <label className="nikah-field nikah-form__span-two">
                                    <span>Short description</span>
                                    <textarea
                                        name="excerpt"
                                        rows="3"
                                        value={excerpt}
                                        onChange={(event) => setExcerpt(event.target.value)}
                                        placeholder="Elegant customized Islamic marriage certificate with premium framed mockups."
                                    />
                                    {getError(errors, 'excerpt') ? <small>{getError(errors, 'excerpt')}</small> : null}
                                </label>

                                <label className="nikah-field">
                                    <span>Category</span>
                                    <select
                                        name="category_id"
                                        value={categoryId}
                                        onChange={(event) => setCategoryId(event.target.value)}
                                    >
                                        <option value="">Select a category</option>
                                        {categories.map((category) => (
                                            <option key={category.id} value={category.id}>{category.name}</option>
                                        ))}
                                    </select>
                                    {getError(errors, 'category_id') ? <small>{getError(errors, 'category_id')}</small> : null}
                                </label>

                                <div className="nikah-field">
                                    <span>Tags</span>
                                    <div className="nikah-tag-list">
                                        {tags.map((tag) => {
                                            const active = selectedTags.includes(tag.id);

                                            return (
                                                <button
                                                    key={tag.id}
                                                    type="button"
                                                    className={`nikah-tag ${active ? 'is-active' : ''}`}
                                                    onClick={() => toggleTag(tag.id)}
                                                >
                                                    {tag.name}
                                                </button>
                                            );
                                        })}
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section className="nikah-step-card nikah-step-card--accent-blue">
                            <div className="nikah-step-card__heading">
                                <span className="nikah-step-card__step">2</span>
                                <div>
                                    <h3>Nikahnama design</h3>
                                    <p>Pick the certificate template that drives artwork, field defaults, and storefront preview composition.</p>
                                </div>
                            </div>

                            <div className="nikah-design-grid">
                                {designs.map((design) => {
                                    const isSelected = String(design.id) === String(selectedDesignId);
                                    const preview = design.thumbnail_url || design.preview_url;

                                    return (
                                        <button
                                            key={design.id}
                                            type="button"
                                            className={`nikah-design-card ${isSelected ? 'is-selected' : ''}`}
                                            onClick={() => setSelectedDesignId(design.id)}
                                        >
                                            <div className="nikah-design-card__media">
                                                {preview ? <img src={preview} alt={design.name} /> : <span>No preview</span>}
                                                {isSelected ? <span className="nikah-design-card__check">✓</span> : null}
                                            </div>
                                            <div className="nikah-design-card__meta">
                                                <strong>{design.name}</strong>
                                                <span>{design.fields.length} fields</span>
                                            </div>
                                        </button>
                                    );
                                })}
                            </div>
                            {getError(errors, 'assigned_template_id') ? <small className="nikah-inline-error">{getError(errors, 'assigned_template_id')}</small> : null}
                        </section>

                        <section className="nikah-step-card nikah-step-card--accent-amber">
                            <div className="nikah-step-card__heading">
                                <span className="nikah-step-card__step">3</span>
                                <div>
                                    <h3>Mockup merge</h3>
                                    <p>Choose the active frame set and keep one storefront scene pinned as the lead preview.</p>
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
                                                className={`nikah-stage__certificate ${zoneBox.hasZone ? 'has-zone' : ''}`}
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
                                                    <span>No mockups available for this design.</span>
                                                </div>
                                            );
                                        }

                                        const isActive = activeMockups.includes(mockup.id);
                                        const isLead = String(defaultMockupId) === String(mockup.id);

                                        return (
                                            <div
                                                key={mockup.id}
                                                className={`nikah-frame-card ${isLead ? 'is-lead' : ''} ${isActive ? 'is-active' : ''}`}
                                            >
                                                <button type="button" className="nikah-frame-card__preview" onClick={() => activateFrame(mockup.id)}>
                                                    <img src={mockup.thumb_image_url || mockup.base_image_url} alt={mockup.title} />
                                                </button>
                                                <div className="nikah-frame-card__meta">
                                                    <div>
                                                        <strong>{mockup.title}</strong>
                                                        <span>{mockup.map ? 'Zone mapped' : 'Zone pending'}</span>
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

                            <div className="nikah-callout">
                                Nikahnama will be composited at the saved zone position.
                            </div>
                            {getError(errors, 'allowed_mockup_ids') ? <small className="nikah-inline-error">{getError(errors, 'allowed_mockup_ids')}</small> : null}
                        </section>

                        <section className="nikah-step-card">
                            <div className="nikah-step-card__heading">
                                <span className="nikah-step-card__step">4</span>
                                <div>
                                    <h3>Personalization fields</h3>
                                    <p>Keep the customer-facing field order tidy and publish the exact fields this Nikahnama needs.</p>
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
                                    <p>Set the storefront sell price and optional compare-at price for the finished certificate.</p>
                                </div>
                            </div>

                            <div className="nikah-form__grid nikah-form__grid--two">
                                <label className="nikah-field">
                                    <span>Price</span>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        name="price"
                                        value={price}
                                        onChange={(event) => setPrice(event.target.value)}
                                        placeholder="129.00"
                                    />
                                    {getError(errors, 'price') ? <small>{getError(errors, 'price')}</small> : null}
                                </label>

                                <label className="nikah-field">
                                    <span>Compare-at price</span>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        name="compare_at_price"
                                        value={compareAtPrice}
                                        onChange={(event) => setCompareAtPrice(event.target.value)}
                                        placeholder="159.00"
                                    />
                                    {getError(errors, 'compare_at_price') ? <small>{getError(errors, 'compare_at_price')}</small> : null}
                                </label>
                            </div>
                        </section>

                        <section className="nikah-step-card">
                            <div className="nikah-step-card__heading">
                                <span className="nikah-step-card__step">6</span>
                                <div>
                                    <h3>Related discovery</h3>
                                    <p>Attach supporting products and nearby browse categories so the storefront can cross-link this Nikahnama cleanly.</p>
                                </div>
                            </div>

                            <div className="nikah-form__grid">
                                <SearchableMultiSelect
                                    label="Related products"
                                    placeholder="Search and select related products"
                                    options={relatedProducts}
                                    selectedValues={selectedRelatedProducts}
                                    onToggle={toggleRelatedProduct}
                                />

                                <SearchableMultiSelect
                                    label="Related categories"
                                    placeholder="Search and select related categories"
                                    options={relatedCategories}
                                    selectedValues={selectedRelatedCategories}
                                    onToggle={toggleRelatedCategory}
                                />
                            </div>
                        </section>
                    </div>

                    <aside className="nikah-form__sidebar">
                        <section className="nikah-sidebar-card">
                            <div className="nikah-sidebar-card__heading">
                                <h3>Storefront preview</h3>
                                <p>What customers will see first.</p>
                            </div>

                            <div className="nikah-preview-card">
                                <div className="nikah-stage nikah-stage--sidebar">
                                    {activeFrame?.base_image_url ? <img src={activeFrame.base_image_url} alt={activeFrame.title} className="nikah-stage__scene" /> : null}
                                    {designThumbnail ? (
                                        <div
                                            className="nikah-stage__certificate has-zone"
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
                                <p>Required items turn the publish action on.</p>
                            </div>

                            <div className="nikah-checklist">
                                <div className={`nikah-checklist__item ${hasDesign ? 'is-complete' : ''}`}>
                                    <span className="nikah-checklist__icon">{hasDesign ? '✓' : '○'}</span>
                                    <span>Design selected</span>
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
                                <div className="nikah-checklist__item is-optional">
                                    <span className="nikah-checklist__icon">○</span>
                                    <span>SEO copy optional</span>
                                </div>
                            </div>
                        </section>

                        <section className="nikah-sidebar-card">
                            <div className="nikah-sidebar-card__heading">
                                <h3>Status</h3>
                                <p>Decide how the product should exist in the catalog after save.</p>
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
