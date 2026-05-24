import Alpine from 'alpinejs';
import { registerNikahPreview } from './nikah-preview';
import './pdp-zoom';

window.Alpine = Alpine;

registerNikahPreview(Alpine);
Alpine.start();

if (document.getElementById('nikah-product-form-root')) {
    import('./admin/nikah-product-form').then(({ mountNikahProductForm }) => {
        mountNikahProductForm();
    });
}
