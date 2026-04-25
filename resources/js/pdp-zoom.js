class ImageZoom {
    constructor(container, canvas) {
        this.container = container;
        this.canvas = canvas;
        this.boundZoom = () => this.zoom();
        this.boundPan = (event) => this.pan(event);
        this.boundUnzoom = () => this.unzoom();
        this.boundOpenLightbox = () => this.openLightbox();
        this.boundEsc = (event) => {
            if (event.key === 'Escape') {
                this.closeLightbox();
            }
        };

        this.container.addEventListener('mouseenter', this.boundZoom);
        this.container.addEventListener('mousemove', this.boundPan);
        this.container.addEventListener('mouseleave', this.boundUnzoom);
        this.container.addEventListener('click', this.boundOpenLightbox);
    }

    zoom() {
        this.canvas.style.transition = 'transform 300ms ease';
        this.canvas.style.transform = 'scale(2)';
        this.canvas.style.transformOrigin = '50% 50%';
    }

    pan(event) {
        const rect = this.container.getBoundingClientRect();
        const x = ((event.clientX - rect.left) / rect.width) * 100;
        const y = ((event.clientY - rect.top) / rect.height) * 100;

        this.canvas.style.transformOrigin = `${x}% ${y}%`;
    }

    unzoom() {
        this.canvas.style.transform = 'scale(1)';
        this.canvas.style.transformOrigin = '50% 50%';
    }

    openLightbox() {
        this.closeLightbox();

        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 z-[9999] flex cursor-zoom-out items-center justify-center bg-black/90 p-6';
        overlay.dataset.pdpZoomOverlay = 'true';

        const image = new Image();
        image.src = this.canvas.toDataURL('image/png');
        image.alt = 'Expanded product preview';
        image.className = 'max-h-[90vh] max-w-[90vw] rounded-lg object-contain shadow-2xl';

        overlay.appendChild(image);
        overlay.addEventListener('click', (event) => {
            if (event.target === overlay) {
                this.closeLightbox();
            }
        });

        document.addEventListener('keydown', this.boundEsc);
        document.body.appendChild(overlay);
        this.overlay = overlay;
    }

    closeLightbox() {
        if (this.overlay) {
            this.overlay.remove();
            this.overlay = null;
        }

        document.removeEventListener('keydown', this.boundEsc);
    }

    destroy() {
        this.closeLightbox();
        this.container.removeEventListener('mouseenter', this.boundZoom);
        this.container.removeEventListener('mousemove', this.boundPan);
        this.container.removeEventListener('mouseleave', this.boundUnzoom);
        this.container.removeEventListener('click', this.boundOpenLightbox);
    }
}

export function initPdpZoom(container, canvas) {
    if (!container || !canvas) {
        return null;
    }

    if (container.__pdpZoomInstance) {
        return container.__pdpZoomInstance;
    }

    container.__pdpZoomInstance = new ImageZoom(container, canvas);

    return container.__pdpZoomInstance;
}

window.initPdpZoom = initPdpZoom;
