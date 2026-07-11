import * as THREE from 'three';

const MAX_DEVICE_PIXEL_RATIO = 1.5;

const supportsWebGl = (): boolean => {
    try {
        const canvas = document.createElement('canvas');
        return Boolean(canvas.getContext('webgl2') ?? canvas.getContext('webgl'));
    } catch {
        return false;
    }
};

export const mountThreePreview = (container: HTMLElement): void => {
    if (!supportsWebGl()) {
        throw new Error('WebGL is unavailable.');
    }

    const canvasHost = container.querySelector<HTMLElement>('[data-three-preview-canvas]');
    if (!canvasHost) {
        throw new Error('The Three.js canvas host is missing.');
    }

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(40, 1, 0.1, 20);
    camera.position.set(0, 0.4, 3.2);

    const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: false, powerPreference: 'low-power' });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, MAX_DEVICE_PIXEL_RATIO));
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    renderer.domElement.setAttribute('aria-hidden', 'true');
    canvasHost.append(renderer.domElement);

    const geometry = new THREE.BoxGeometry(1.15, 1.15, 1.15);
    const material = new THREE.MeshNormalMaterial({ flatShading: true });
    const placeholder = new THREE.Mesh(geometry, material);
    scene.add(placeholder);

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let visible = true;
    let animationFrame: number | undefined;

    const resize = (): void => {
        const width = Math.max(canvasHost.clientWidth, 1);
        const height = Math.max(canvasHost.clientHeight, 1);
        renderer.setSize(width, height, false);
        camera.aspect = width / height;
        camera.updateProjectionMatrix();
    };

    const render = (): void => {
        animationFrame = undefined;
        if (!visible) {
            return;
        }

        if (!reducedMotion) {
            placeholder.rotation.y += 0.006;
        }
        renderer.render(scene, camera);

        if (!reducedMotion) {
            animationFrame = requestAnimationFrame(render);
        }
    };

    const visibilityObserver = new IntersectionObserver((entries) => {
        visible = entries.some((entry) => entry.isIntersecting);
        if (visible && animationFrame === undefined) {
            render();
        } else if (!visible && animationFrame !== undefined) {
            cancelAnimationFrame(animationFrame);
            animationFrame = undefined;
        }
    });

    const resizeObserver = new ResizeObserver(resize);
    resizeObserver.observe(canvasHost);
    visibilityObserver.observe(container);
    resize();
    render();
};
