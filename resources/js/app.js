import Alpine from 'alpinejs';
import { mountNikahProductForm } from './admin/nikah-product-form';
import { registerNikahPreview } from './nikah-preview';

window.Alpine = Alpine;

registerNikahPreview(Alpine);
Alpine.start();
mountNikahProductForm();
