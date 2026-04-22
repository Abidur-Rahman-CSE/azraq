import Alpine from 'alpinejs';
import { mountNikahProductForm } from './admin/nikah-product-form';
import { registerMockupPreview } from './mockup-preview';

window.Alpine = Alpine;

registerMockupPreview(Alpine);
Alpine.start();
mountNikahProductForm();
