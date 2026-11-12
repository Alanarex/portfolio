import * as THREE from 'three';
import { GLTFLoader } from 'three/examples/jsm/loaders/GLTFLoader.js';
import { MeshoptDecoder } from 'three/examples/jsm/libs/meshopt_decoder.module.js';
import { RoomEnvironment } from 'three/examples/jsm/environments/RoomEnvironment.js';

const MAX_DEVICE_PIXEL_RATIO = 1.5;
const MODEL_URL = '/models/amg-gt-roadster.glb';
const CAR_LENGTH = 4.6;
const ROAD_LENGTH = 120;
const SIGN_LABELS = ['Projets', 'Preuves', 'Aperçu 3D', 'Contact'];

const PAINT = new THREE.Color('#5a0b0d');

const supportsWebGl = (): boolean => {
    try {
        const canvas = document.createElement('canvas');
        return Boolean(canvas.getContext('webgl2') ?? canvas.getContext('webgl'));
    } catch {
        return false;
    }
};

const makeRoadTexture = (): THREE.CanvasTexture => {
    const canvas = document.createElement('canvas');
    canvas.width = 256;
    canvas.height = 512;
    const ctx = canvas.getContext('2d');
    if (ctx) {
        ctx.fillStyle = '#131012';
        ctx.fillRect(0, 0, 256, 512);
        for (let i = 0; i < 2600; i++) {
            const g = 20 + Math.random() * 22;
            ctx.fillStyle = `rgb(${g + 4}, ${g}, ${g})`;
            ctx.fillRect(Math.random() * 256, Math.random() * 512, 2, 2);
        }
        ctx.fillStyle = '#d3d3d3';
        ctx.fillRect(10, 0, 5, 512);
        ctx.fillRect(241, 0, 5, 512);
        ctx.fillStyle = '#e5383b';
        ctx.fillRect(124, 40, 8, 200);
    }
    const texture = new THREE.CanvasTexture(canvas);
    texture.wrapS = THREE.RepeatWrapping;
    texture.wrapT = THREE.RepeatWrapping;
    texture.colorSpace = THREE.SRGBColorSpace;
    texture.anisotropy = 4;
    return texture;
};

const makeSign = (label: string, index: number): THREE.Group => {
    const canvas = document.createElement('canvas');
    canvas.width = 512;
    canvas.height = 256;
    const ctx = canvas.getContext('2d');
    if (ctx) {
        ctx.fillStyle = '#161a1d';
        ctx.fillRect(0, 0, 512, 256);
        ctx.strokeStyle = '#e5383b';
        ctx.lineWidth = 10;
        ctx.strokeRect(12, 12, 488, 232);
        ctx.fillStyle = '#b1a7a6';
        ctx.font = '500 34px monospace';
        ctx.fillText(`0${index + 1} / 0${SIGN_LABELS.length}`, 40, 76);
        ctx.fillStyle = '#f5f3f4';
        ctx.font = '700 70px sans-serif';
        ctx.fillText(label, 40, 170);
    }
    const texture = new THREE.CanvasTexture(canvas);
    texture.colorSpace = THREE.SRGBColorSpace;
    const group = new THREE.Group();
    const panel = new THREE.Mesh(new THREE.PlaneGeometry(2.8, 1.4), new THREE.MeshBasicMaterial({ map: texture }));
    panel.position.y = 3.2;
    const post = new THREE.Mesh(
        new THREE.CylinderGeometry(0.06, 0.06, 3.2, 8),
        new THREE.MeshStandardMaterial({ color: '#2a2f33', metalness: 0.6, roughness: 0.5 }),
    );
    post.position.y = 1.6;
    group.add(panel, post);
    return group;
};

const restyleCar = (car: THREE.Object3D): { rear: THREE.Vector3; front: THREE.Vector3 } => {
    const rear = new THREE.Vector3();
    const front = new THREE.Vector3();
    let rearCount = 0;
    let frontCount = 0;
    car.updateMatrixWorld(true);

    car.traverse((object) => {
        const mesh = object as THREE.Mesh;
        if (!mesh.isMesh) {
            return;
        }
        const material = mesh.material as THREE.MeshStandardMaterial;
        const name = material.name;
        const box = new THREE.Box3().setFromObject(mesh);
        const center = box.getCenter(new THREE.Vector3());

        if (name === 'body') {
            const paint = new THREE.MeshPhysicalMaterial({
                color: PAINT,
                metalness: 0.75,
                roughness: 0.28,
                clearcoat: 1,
                clearcoatRoughness: 0.05,
            });
            mesh.material = paint;
        } else if (name === 'glass' || name === 'd_glass') {
            material.opacity = 0.35;
            material.transparent = true;
            material.depthWrite = false;
        } else if (name.startsWith('brakes')) {
            material.color = new THREE.Color('#e5383b');
        } else if (name === 'r_lights') {
            material.emissive = new THREE.Color('#ff2a2e');
            material.emissiveIntensity = 1.3;
        }

        if (name === 'r_lights') {
            rear.add(center);
            rearCount++;
        } else if (name === 'lights') {
            front.add(center);
            frontCount++;
        }
    });

    if (rearCount) {
        rear.divideScalar(rearCount);
    }
    if (frontCount) {
        front.divideScalar(frontCount);
    }

    return { rear, front };
};

const loadCar = async (): Promise<THREE.Object3D> => {
    const loader = new GLTFLoader();
    loader.setMeshoptDecoder(MeshoptDecoder);
    const gltf = await loader.loadAsync(MODEL_URL);
    const model = gltf.scene;

    const { rear, front } = restyleCar(model);
    const box = new THREE.Box3().setFromObject(model);
    const size = box.getSize(new THREE.Vector3());
    const scale = CAR_LENGTH / Math.max(size.x, size.z);
    const wrapper = new THREE.Group();

    model.scale.setScalar(scale);
    model.position.sub(box.getCenter(new THREE.Vector3()).multiplyScalar(scale));

    const longAxisIsX = size.x > size.z;
    const axis = longAxisIsX ? 'x' : 'z';
    const rearIsPositive = rear[axis] > front[axis];

    // On veut la longueur sur Z, l'arrière vers la caméra (+Z).
    if (longAxisIsX) {
        model.rotation.y = rearIsPositive ? -Math.PI / 2 : Math.PI / 2;
    } else if (!rearIsPositive) {
        model.rotation.y = Math.PI;
    }

    wrapper.add(model);
    const placed = new THREE.Box3().setFromObject(wrapper);
    wrapper.position.y -= placed.min.y;
    return wrapper;
};

export const mountThreePreview = async (container: HTMLElement): Promise<void> => {
    if (!supportsWebGl()) {
        throw new Error('WebGL is unavailable.');
    }

    const canvasHost = container.querySelector<HTMLElement>('[data-three-preview-canvas]');
    if (!canvasHost) {
        throw new Error('The Three.js canvas host is missing.');
    }

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true, powerPreference: 'high-performance' });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, MAX_DEVICE_PIXEL_RATIO));
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.05;
    renderer.domElement.setAttribute('aria-hidden', 'true');
    canvasHost.append(renderer.domElement);

    const scene = new THREE.Scene();
    scene.background = new THREE.Color('#0b090a');
    scene.fog = new THREE.Fog('#0b090a', 18, 85);

    const pmrem = new THREE.PMREMGenerator(renderer);
    scene.environment = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;
    scene.environmentIntensity = 0.55;

    const camera = new THREE.PerspectiveCamera(38, 1, 0.1, 200);
    camera.position.set(0, 1.15, 5.6);
    camera.lookAt(0, 0.75, -6);

    scene.add(new THREE.HemisphereLight('#6b7a8f', '#1a0b0c', 0.35));
    const rim = new THREE.DirectionalLight('#e5383b', 1.1);
    rim.position.set(-5, 3, -4);
    scene.add(rim);
    const key = new THREE.DirectionalLight('#f5f3f4', 1.1);
    key.position.set(4, 6, 6);
    scene.add(key);

    // Route
    const roadTexture = makeRoadTexture();
    roadTexture.repeat.set(1, ROAD_LENGTH / 8);
    const road = new THREE.Mesh(
        new THREE.PlaneGeometry(9, ROAD_LENGTH),
        new THREE.MeshStandardMaterial({ map: roadTexture, roughness: 0.55, metalness: 0.2 }),
    );
    road.rotation.x = -Math.PI / 2;
    road.position.z = -ROAD_LENGTH / 2 + 10;
    scene.add(road);

    // Étoiles
    const starCount = 1400;
    const starPositions = new Float32Array(starCount * 3);
    for (let i = 0; i < starCount; i++) {
        const theta = Math.random() * Math.PI * 2;
        const phi = Math.random() * 0.48 * Math.PI;
        const r = 120;
        starPositions[i * 3] = r * Math.sin(phi) * Math.cos(theta);
        starPositions[i * 3 + 1] = 4 + r * Math.cos(phi);
        starPositions[i * 3 + 2] = -Math.abs(r * Math.sin(phi) * Math.sin(theta)) - 20;
    }
    const starGeometry = new THREE.BufferGeometry();
    starGeometry.setAttribute('position', new THREE.BufferAttribute(starPositions, 3));
    const stars = new THREE.Points(
        starGeometry,
        new THREE.PointsMaterial({ color: '#f5f3f4', size: 0.45, sizeAttenuation: true, fog: false, transparent: true, opacity: 0.9 }),
    );
    scene.add(stars);

    // Étoiles filantes
    const shooting: { line: THREE.Line; life: number; velocity: THREE.Vector3 }[] = [];
    const spawnShootingStar = (): void => {
        const geometry = new THREE.BufferGeometry().setFromPoints([new THREE.Vector3(), new THREE.Vector3(-5, -1.6, 0)]);
        const line = new THREE.Line(geometry, new THREE.LineBasicMaterial({ color: '#ffffff', transparent: true, opacity: 1, fog: false }));
        line.position.set(-20 + Math.random() * 50, 30 + Math.random() * 20, -70 - Math.random() * 20);
        scene.add(line);
        shooting.push({ line, life: 1, velocity: new THREE.Vector3(-30, -9, 0) });
    };

    // Panneaux
    const signs: THREE.Group[] = SIGN_LABELS.map((label, index) => {
        const sign = makeSign(label, index);
        sign.position.set(index % 2 === 0 ? 5.6 : -5.6, 0, -index * 26 - 10);
        sign.rotation.y = index % 2 === 0 ? -0.35 : 0.35;
        scene.add(sign);
        return sign;
    });

    // Voiture
    const car = await loadCar();
    scene.add(car);

    // Phares (halo rouge au sol)
    const glow = new THREE.PointLight('#ff2a2e', 2.2, 6, 2);
    glow.position.set(0, 0.5, 2.4);
    car.add(glow);

    let visible = true;
    let animationFrame: number | undefined;
    const clock = new THREE.Clock();
    let nextShootingStar = 1.5;
    let elapsed = 0;

    const resize = (): void => {
        const width = Math.max(canvasHost.clientWidth, 1);
        const height = Math.max(canvasHost.clientHeight, 1);
        renderer.setSize(width, height, false);
        camera.aspect = width / height;
        camera.updateProjectionMatrix();
    };

    const update = (delta: number): void => {
        elapsed += delta;
        const speed = 22;

        roadTexture.offset.y += (speed * delta * (ROAD_LENGTH / 8)) / ROAD_LENGTH;
        stars.rotation.y += delta * 0.004;

        for (const sign of signs) {
            sign.position.z += speed * delta;
            if (sign.position.z > 12) {
                sign.position.z -= SIGN_LABELS.length * 26;
            }
        }

        car.position.x = Math.sin(elapsed * 0.6) * 0.18;
        car.rotation.y = Math.sin(elapsed * 0.6 + 0.6) * 0.025;
        car.position.y = Math.sin(elapsed * 9) * 0.004;

        nextShootingStar -= delta;
        if (nextShootingStar <= 0) {
            spawnShootingStar();
            nextShootingStar = 2.5 + Math.random() * 3.5;
        }
        for (let i = shooting.length - 1; i >= 0; i--) {
            const star = shooting[i];
            star.life -= delta * 0.9;
            star.line.position.addScaledVector(star.velocity, delta);
            (star.line.material as THREE.LineBasicMaterial).opacity = Math.max(star.life, 0);
            if (star.life <= 0) {
                scene.remove(star.line);
                star.line.geometry.dispose();
                (star.line.material as THREE.Material).dispose();
                shooting.splice(i, 1);
            }
        }
    };

    const render = (): void => {
        animationFrame = undefined;
        if (!visible) {
            return;
        }

        update(Math.min(clock.getDelta(), 0.05));
        renderer.render(scene, camera);

        if (!reducedMotion) {
            animationFrame = requestAnimationFrame(render);
        }
    };

    const visibilityObserver = new IntersectionObserver((entries) => {
        visible = entries.some((entry) => entry.isIntersecting);
        if (visible && animationFrame === undefined && !reducedMotion) {
            clock.getDelta();
            render();
        } else if (!visible && animationFrame !== undefined) {
            cancelAnimationFrame(animationFrame);
            animationFrame = undefined;
        }
    });

    const resizeObserver = new ResizeObserver(() => {
        resize();
        if (reducedMotion) {
            renderer.render(scene, camera);
        }
    });
    resizeObserver.observe(canvasHost);
    visibilityObserver.observe(container);
    resize();
    render();
};
