import * as THREE from 'three';
import { GLTFLoader } from 'three/examples/jsm/loaders/GLTFLoader.js';
import { MeshoptDecoder } from 'three/examples/jsm/libs/meshopt_decoder.module.js';
import { RoomEnvironment } from 'three/examples/jsm/environments/RoomEnvironment.js';

const MAX_DEVICE_PIXEL_RATIO = 2;
const MODEL_URL = '/models/amg-gt-roadster.glb';
const CAR_LENGTH = 4.6;
const ROAD_HALF_WIDTH = 4.4;
const KERB_WIDTH = 0.5;
const SHOULDER_WIDTH = 5;
const SIGN_WIDTH = 6.4;
const SIGN_HEIGHT = 4;
const SIGN_CENTER_Y = 4.7;
const SIGN_LATERAL = 9.2;
/** Vitesse maximale de la progression (part du parcours par seconde) : évite de semer la voiture si l'on défile trop vite. */
/** Portion de la courbe parcourue pendant le défilement. */
const U_START = 0.03;
const U_SPAN = 0.93;
const PAINT = new THREE.Color('#9b1217');
const NIGHT = '#0b090a';

const clamp = (value: number, min: number, max: number): number => Math.min(Math.max(value, min), max);
const smooth = (edge0: number, edge1: number, value: number): number => {
    const t = clamp((value - edge0) / (edge1 - edge0), 0, 1);
    return t * t * (3 - 2 * t);
};
const lerp = (a: number, b: number, t: number): number => a + (b - a) * t;
const damp = (rate: number, delta: number): number => 1 - Math.exp(-rate * delta);

const supportsWebGl = (): boolean => {
    try {
        const canvas = document.createElement('canvas');
        return Boolean(canvas.getContext('webgl2') ?? canvas.getContext('webgl'));
    } catch {
        return false;
    }
};

const canvasTexture = (width: number, height: number, draw: (ctx: CanvasRenderingContext2D) => void): THREE.CanvasTexture => {
    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    const ctx = canvas.getContext('2d');
    if (ctx) {
        draw(ctx);
    }
    const texture = new THREE.CanvasTexture(canvas);
    texture.colorSpace = THREE.SRGBColorSpace;
    texture.wrapS = THREE.RepeatWrapping;
    texture.wrapT = THREE.RepeatWrapping;
    texture.anisotropy = 8;
    return texture;
};

const makeAsphaltTexture = (): THREE.CanvasTexture =>
    canvasTexture(512, 512, (ctx) => {
        ctx.fillStyle = '#17141a';
        ctx.fillRect(0, 0, 512, 512);
        for (let i = 0; i < 9000; i++) {
            const g = 22 + Math.random() * 30;
            ctx.fillStyle = `rgb(${g + 3}, ${g}, ${g + 2})`;
            ctx.fillRect(Math.random() * 512, Math.random() * 512, 1 + Math.random() * 2, 1 + Math.random() * 2);
        }
        // Lignes de rive pleines
        ctx.fillStyle = '#d9d6d6';
        ctx.fillRect(14, 0, 8, 512);
        ctx.fillRect(490, 0, 8, 512);
        // Axe pointillé
        ctx.fillStyle = '#e5383b';
        ctx.fillRect(252, 40, 8, 190);
        ctx.fillRect(252, 296, 8, 190);
    });

const makeKerbTexture = (): THREE.CanvasTexture =>
    canvasTexture(64, 128, (ctx) => {
        ctx.fillStyle = '#e5383b';
        ctx.fillRect(0, 0, 64, 64);
        ctx.fillStyle = '#f2eeee';
        ctx.fillRect(0, 64, 64, 64);
    });

const makeGlowTexture = (color: string): THREE.CanvasTexture =>
    canvasTexture(128, 128, (ctx) => {
        const gradient = ctx.createRadialGradient(64, 64, 0, 64, 64, 64);
        gradient.addColorStop(0, color);
        gradient.addColorStop(0.35, `${color}66`);
        gradient.addColorStop(1, `${color}00`);
        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, 128, 128);
    });

const makeShadowTexture = (): THREE.CanvasTexture =>
    canvasTexture(128, 128, (ctx) => {
        const gradient = ctx.createRadialGradient(64, 64, 0, 64, 64, 64);
        gradient.addColorStop(0, 'rgba(0,0,0,0.9)');
        gradient.addColorStop(0.6, 'rgba(0,0,0,0.45)');
        gradient.addColorStop(1, 'rgba(0,0,0,0)');
        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, 128, 128);
    });

interface SignCard {
    kind: string;
    period: string;
    title: string;
    org: string;
    summary: string;
    highlights: string[];
    counter: string;
    number: number;
}

const wrapText = (ctx: CanvasRenderingContext2D, text: string, maxWidth: number, maxLines: number): string[] => {
    const words = text.split(/\s+/).filter(Boolean);
    const lines: string[] = [];
    let line = '';
    for (const word of words) {
        const attempt = line ? `${line} ${word}` : word;
        if (ctx.measureText(attempt).width > maxWidth && line) {
            lines.push(line);
            line = word;
        } else {
            line = attempt;
        }
    }
    if (line) {
        lines.push(line);
    }
    if (lines.length > maxLines) {
        const kept = lines.slice(0, maxLines);
        let last = kept[maxLines - 1] ?? '';
        while (last.length > 1 && ctx.measureText(`${last}…`).width > maxWidth) {
            last = last.slice(0, -1);
        }
        kept[maxLines - 1] = `${last.trimEnd()}…`;
        return kept;
    }
    return lines;
};

const roundedRect = (ctx: CanvasRenderingContext2D, x: number, y: number, w: number, h: number, r: number): void => {
    ctx.beginPath();
    ctx.moveTo(x + r, y);
    ctx.arcTo(x + w, y, x + w, y + h, r);
    ctx.arcTo(x + w, y + h, x, y + h, r);
    ctx.arcTo(x, y + h, x, y, r);
    ctx.arcTo(x, y, x + w, y, r);
    ctx.closePath();
};

/** Face du panneau : feuille rétro-réfléchissante sombre, liseré blanc, écusson numéroté et texte de l'étape. */
const makeSignTexture = (card: SignCard, fonts: { display: string; mono: string }, anisotropy: number): THREE.CanvasTexture => {
    const width = 1600;
    const height = 1000;
    const texture = canvasTexture(width, height, (ctx) => {
        const base = ctx.createLinearGradient(0, 0, width, height);
        base.addColorStop(0, '#1b2024');
        base.addColorStop(1, '#0d1013');
        ctx.fillStyle = base;
        ctx.fillRect(0, 0, width, height);

        // Grain de la feuille rétro-réfléchissante
        for (let i = 0; i < 14000; i++) {
            ctx.fillStyle = `rgba(255,255,255,${Math.random() * 0.045})`;
            ctx.fillRect(Math.random() * width, Math.random() * height, 2, 2);
        }

        // Liseré blanc arrondi et filet rouge intérieur
        ctx.lineWidth = 26;
        ctx.strokeStyle = '#f5f3f4';
        roundedRect(ctx, 22, 22, width - 44, height - 44, 56);
        ctx.stroke();
        ctx.lineWidth = 5;
        ctx.strokeStyle = '#e5383b';
        roundedRect(ctx, 58, 58, width - 116, height - 116, 34);
        ctx.stroke();

        const left = 112;
        const textWidth = 1090;
        ctx.textBaseline = 'alphabetic';

        // Pastille du type d'étape + période
        ctx.font = `700 34px ${fonts.mono}`;
        const kind = card.kind.toUpperCase();
        const pillWidth = ctx.measureText(kind).width + 52;
        ctx.fillStyle = '#e5383b';
        roundedRect(ctx, left, 112, pillWidth, 58, 29);
        ctx.fill();
        ctx.fillStyle = '#ffffff';
        ctx.fillText(kind, left + 26, 152);
        ctx.font = `500 40px ${fonts.mono}`;
        ctx.fillStyle = '#d9d4d4';
        ctx.fillText(card.period, left + pillWidth + 34, 154);

        // Titre (3 lignes au plus, la taille s'adapte)
        ctx.fillStyle = '#ffffff';
        let size = 112;
        let lines: string[] = [];
        for (; size >= 62; size -= 6) {
            ctx.font = `700 ${size}px ${fonts.display}`;
            lines = wrapText(ctx, card.title, textWidth, 3);
            if (lines.length <= 2 || size <= 74) {
                break;
            }
        }
        let y = 262;
        for (const line of lines) {
            ctx.fillText(line, left, y);
            y += size * 1.04;
        }

        // Organisation
        if (card.org) {
            ctx.font = `600 58px ${fonts.display}`;
            ctx.fillStyle = '#ff7a7d';
            const [org] = wrapText(ctx, card.org, textWidth + 200, 1);
            ctx.fillText(org ?? '', left, y + 18);
            y += 76;
        }

        // Séparateur
        ctx.fillStyle = 'rgba(255,255,255,0.22)';
        ctx.fillRect(left, y + 18, width - left * 2, 3);
        y += 74;

        // Résumé
        if (card.summary) {
            ctx.font = `400 42px ${fonts.display}`;
            ctx.fillStyle = '#d9d4d4';
            for (const line of wrapText(ctx, card.summary, width - left * 2, 3)) {
                ctx.fillText(line, left, y);
                y += 58;
            }
            y += 6;
        }

        // Points clés
        ctx.font = `600 34px ${fonts.mono}`;
        let x = left;
        for (const item of card.highlights.slice(0, 4)) {
            const label = item.length > 26 ? `${item.slice(0, 25)}…` : item;
            const w = ctx.measureText(label).width + 44;
            if (x + w > width - left) {
                x = left;
                y += 76;
            }
            ctx.strokeStyle = 'rgba(245,243,244,0.7)';
            ctx.lineWidth = 3;
            roundedRect(ctx, x, y - 38, w, 56, 28);
            ctx.stroke();
            ctx.fillStyle = '#f5f3f4';
            ctx.fillText(label, x + 22, y);
            x += w + 18;
        }

        // Écusson numéroté (comme un panneau de route)
        const cx = width - 238;
        const cy = 220;
        ctx.fillStyle = '#f5f3f4';
        ctx.beginPath();
        ctx.arc(cx, cy, 112, 0, Math.PI * 2);
        ctx.fill();
        ctx.fillStyle = '#e5383b';
        ctx.beginPath();
        ctx.arc(cx, cy, 98, 0, Math.PI * 2);
        ctx.fill();
        ctx.fillStyle = '#ffffff';
        ctx.textAlign = 'center';
        ctx.font = `700 118px ${fonts.display}`;
        ctx.fillText(String(card.number).padStart(2, '0'), cx, cy + 40);

        // Pied : compteur et flèche
        ctx.font = `500 32px ${fonts.mono}`;
        ctx.fillStyle = '#8f8889';
        ctx.textAlign = 'left';
        ctx.fillText(card.counter.toUpperCase(), left, height - 100);
        ctx.textAlign = 'right';
        ctx.font = `700 64px ${fonts.display}`;
        ctx.fillStyle = '#e5383b';
        ctx.fillText('→', width - left, height - 92);
    });
    texture.wrapS = THREE.ClampToEdgeWrapping;
    texture.wrapT = THREE.ClampToEdgeWrapping;
    texture.anisotropy = anisotropy;
    return texture;
};

interface RoadSign {
    group: THREE.Group;
    face: THREE.MeshBasicMaterial;
    glow: THREE.Sprite;
}

/** Panneau de bord de route : plaque épaisse, deux poteaux, entretoises, lampadaire d'éclairage. */
const makeSign = (texture: THREE.Texture, glowTexture: THREE.Texture): RoadSign => {
    const group = new THREE.Group();
    const metal = new THREE.MeshStandardMaterial({ color: '#2b3034', metalness: 0.75, roughness: 0.45 });
    const darkMetal = new THREE.MeshStandardMaterial({ color: '#15181a', metalness: 0.6, roughness: 0.55 });
    const face = new THREE.MeshBasicMaterial({ map: texture, toneMapped: false });

    const plate = new THREE.Mesh(new THREE.BoxGeometry(SIGN_WIDTH, SIGN_HEIGHT, 0.14), [metal, metal, metal, metal, face, darkMetal]);
    plate.position.y = SIGN_CENTER_Y;
    const rim = new THREE.Mesh(new THREE.BoxGeometry(SIGN_WIDTH + 0.22, SIGN_HEIGHT + 0.22, 0.1), darkMetal);
    rim.position.set(0, SIGN_CENTER_Y, -0.04);
    group.add(rim, plate);

    // Poteaux (profilés), traverses et socle béton
    const postHeight = SIGN_CENTER_Y + SIGN_HEIGHT / 2 + 0.5;
    for (const x of [-SIGN_WIDTH * 0.3, SIGN_WIDTH * 0.3]) {
        const post = new THREE.Mesh(new THREE.BoxGeometry(0.2, postHeight, 0.2), metal);
        post.position.set(x, postHeight / 2 - 0.2, -0.28);
        const flange = new THREE.Mesh(new THREE.BoxGeometry(0.34, postHeight * 0.98, 0.05), darkMetal);
        flange.position.set(x, postHeight / 2 - 0.2, -0.38);
        const footing = new THREE.Mesh(new THREE.BoxGeometry(0.7, 0.35, 0.7), new THREE.MeshStandardMaterial({ color: '#4a4648', roughness: 1 }));
        footing.position.set(x, -0.05, -0.28);
        group.add(post, flange, footing);
    }
    for (const y of [SIGN_CENTER_Y - SIGN_HEIGHT * 0.32, SIGN_CENTER_Y + SIGN_HEIGHT * 0.32]) {
        const brace = new THREE.Mesh(new THREE.BoxGeometry(SIGN_WIDTH * 0.66, 0.14, 0.1), darkMetal);
        brace.position.set(0, y, -0.14);
        group.add(brace);
    }

    // Lampadaire fixé au haut du panneau, orienté vers lui
    const armBase = new THREE.Vector3(0, SIGN_CENTER_Y + SIGN_HEIGHT / 2 + 0.32, 0);
    const arm = new THREE.Mesh(new THREE.CylinderGeometry(0.045, 0.045, 1.5, 8), metal);
    arm.rotation.x = Math.PI / 2;
    arm.position.copy(armBase).add(new THREE.Vector3(0, 0.1, 0.75));
    const head = new THREE.Mesh(new THREE.BoxGeometry(1.1, 0.16, 0.32), new THREE.MeshBasicMaterial({ color: '#ffe3c2' }));
    head.position.copy(armBase).add(new THREE.Vector3(0, 0.12, 1.5));
    const glow = new THREE.Sprite(new THREE.SpriteMaterial({ map: glowTexture, blending: THREE.AdditiveBlending, depthWrite: false, fog: false, opacity: 0.55 }));
    glow.position.copy(head.position).add(new THREE.Vector3(0, -0.1, 0.05));
    glow.scale.setScalar(4.5);
    group.add(arm, head, glow);

    return { group, face, glow };
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
        const center = new THREE.Box3().setFromObject(mesh).getCenter(new THREE.Vector3());

        if (name === 'body') {
            mesh.material = new THREE.MeshPhysicalMaterial({
                color: PAINT,
                metalness: 0.75,
                roughness: 0.28,
                clearcoat: 1,
                clearcoatRoughness: 0.05,
                envMapIntensity: 1.8,
            });
        } else if (name === 'glass' || name === 'd_glass') {
            material.opacity = 0.35;
            material.transparent = true;
            material.depthWrite = false;
        } else if (name.startsWith('brakes')) {
            material.color = new THREE.Color('#e5383b');
        } else if (name === 'r_lights') {
            material.emissive = new THREE.Color('#ff2a2e');
            material.emissiveIntensity = 1.6;
        } else if (name === 'lights') {
            material.emissive = new THREE.Color('#fff1e0');
            material.emissiveIntensity = 0.9;
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

interface Wheel {
    pivot: THREE.Group;
    radius: number;
}

interface CarRig {
    holder: THREE.Group;
    wheels: Wheel[];
    tailLights: THREE.Vector3[];
}

interface TriangleBucket {
    position: number[];
    normal: number[];
    uv: number[];
}

const meshesOf = (root: THREE.Object3D): THREE.Mesh[] => {
    const meshes: THREE.Mesh[] = [];
    root.traverse((object) => {
        if ((object as THREE.Mesh).isMesh) {
            meshes.push(object as THREE.Mesh);
        }
    });
    return meshes;
};

const materialName = (mesh: THREE.Mesh): string => (mesh.material as THREE.Material).name;

/**
 * Le modèle est fusionné par matériau : les quatre roues sont mélangées au reste de la carrosserie.
 * On repère les roues avec les pneus, puis on déplace les triangles qui leur appartiennent
 * dans un pivot par roue, afin de pouvoir les faire tourner.
 */
const splitWheels = (holder: THREE.Group): Wheel[] => {
    holder.updateMatrixWorld(true);
    const inverse = new THREE.Matrix4().copy(holder.matrixWorld).invert();
    const meshes = meshesOf(holder);
    const tires = meshes.filter((mesh) => materialName(mesh) === 'tire_mat4');
    if (tires.length === 0) {
        return [];
    }

    const point = new THREE.Vector3();
    const boxes = new Map<string, THREE.Box3>();
    for (const tire of tires) {
        const positions = tire.geometry.getAttribute('position');
        const matrix = new THREE.Matrix4().multiplyMatrices(inverse, tire.matrixWorld);
        for (let i = 0; i < positions.count; i++) {
            point.fromBufferAttribute(positions, i).applyMatrix4(matrix);
            const key = `${point.x > 0 ? 'r' : 'l'}${point.z > 0 ? 'b' : 'f'}`;
            const box = boxes.get(key) ?? new THREE.Box3();
            box.expandByPoint(point);
            boxes.set(key, box);
        }
    }

    const wheels = [...boxes.values()].map((box) => {
        const center = box.getCenter(new THREE.Vector3());
        const pivot = new THREE.Group();
        pivot.position.copy(center);
        return { box, center, pivot, radius: (box.max.y - box.min.y) / 2, buckets: new Map<THREE.Material, TriangleBucket>() };
    });

    const a = new THREE.Vector3();
    const b = new THREE.Vector3();
    const c = new THREE.Vector3();
    const centroid = new THREE.Vector3();
    const normalMatrix = new THREE.Matrix3();
    const tmp = new THREE.Vector3();

    for (const mesh of meshes) {
        const name = materialName(mesh);
        const geometry = mesh.geometry;
        const index = geometry.getIndex();
        if (!index || name === 'body' || name === 'brakes_amg' || (mesh.material as THREE.Material).transparent) {
            continue;
        }
        const positions = geometry.getAttribute('position');
        const normals = geometry.getAttribute('normal');
        const uvs = geometry.getAttribute('uv');
        const matrix = new THREE.Matrix4().multiplyMatrices(inverse, mesh.matrixWorld);
        normalMatrix.getNormalMatrix(matrix);

        const kept: number[] = [];
        let moved = false;

        for (let t = 0; t < index.count; t += 3) {
            const i0 = index.getX(t);
            const i1 = index.getX(t + 1);
            const i2 = index.getX(t + 2);
            a.fromBufferAttribute(positions, i0).applyMatrix4(matrix);
            b.fromBufferAttribute(positions, i1).applyMatrix4(matrix);
            c.fromBufferAttribute(positions, i2).applyMatrix4(matrix);
            centroid.copy(a).add(b).add(c).divideScalar(3);

            const wheel = wheels.find(
                (candidate) =>
                    Math.sign(centroid.x) === Math.sign(candidate.center.x) &&
                    centroid.x >= candidate.box.min.x - 0.03 &&
                    centroid.x <= candidate.box.max.x + 0.03 &&
                    Math.hypot(centroid.y - candidate.center.y, centroid.z - candidate.center.z) <= candidate.radius * 1.03,
            );

            if (!wheel) {
                kept.push(i0, i1, i2);
                continue;
            }

            moved = true;
            const material = mesh.material as THREE.Material;
            const bucket = wheel.buckets.get(material) ?? { position: [], normal: [], uv: [] };
            wheel.buckets.set(material, bucket);
            for (const [vertex, source] of [
                [a, i0],
                [b, i1],
                [c, i2],
            ] as const) {
                bucket.position.push(vertex.x - wheel.center.x, vertex.y - wheel.center.y, vertex.z - wheel.center.z);
                if (normals) {
                    tmp.fromBufferAttribute(normals, source).applyMatrix3(normalMatrix).normalize();
                    bucket.normal.push(tmp.x, tmp.y, tmp.z);
                }
                if (uvs) {
                    bucket.uv.push(uvs.getX(source), uvs.getY(source));
                }
            }
        }

        if (moved) {
            geometry.setIndex(kept);
        }
    }

    return wheels.map((wheel) => {
        for (const [material, bucket] of wheel.buckets) {
            const geometry = new THREE.BufferGeometry();
            geometry.setAttribute('position', new THREE.Float32BufferAttribute(bucket.position, 3));
            if (bucket.normal.length > 0) {
                geometry.setAttribute('normal', new THREE.Float32BufferAttribute(bucket.normal, 3));
            }
            if (bucket.uv.length > 0) {
                geometry.setAttribute('uv', new THREE.Float32BufferAttribute(bucket.uv, 2));
            }
            wheel.pivot.add(new THREE.Mesh(geometry, material));
        }
        holder.add(wheel.pivot);
        return { pivot: wheel.pivot, radius: wheel.radius };
    });
};

/** Positions des feux arrière : un à gauche, un à droite, légèrement relevés. */
const findTailLights = (holder: THREE.Group): THREE.Vector3[] => {
    holder.updateMatrixWorld(true);
    const inverse = new THREE.Matrix4().copy(holder.matrixWorld).invert();
    const point = new THREE.Vector3();
    const left = new THREE.Box3();
    const right = new THREE.Box3();

    for (const mesh of meshesOf(holder).filter((item) => materialName(item) === 'r_lights')) {
        const positions = mesh.geometry.getAttribute('position');
        const matrix = new THREE.Matrix4().multiplyMatrices(inverse, mesh.matrixWorld);
        for (let i = 0; i < positions.count; i++) {
            point.fromBufferAttribute(positions, i).applyMatrix4(matrix);
            (point.x < 0 ? left : right).expandByPoint(point);
        }
    }

    const sides = [left, right].map((box, index) => {
        if (box.isEmpty()) {
            return null;
        }
        const center = box.getCenter(new THREE.Vector3());
        // Un bloc unique centré donne deux feux écartés de part et d'autre de l'axe.
        center.x = (index === 0 ? -1 : 1) * Math.max(Math.abs(center.x), 0.55);
        return center;
    });

    const fallback = new THREE.Vector3(0.6, 0.75, CAR_LENGTH / 2);
    const first = sides[0] ?? (sides[1] ? sides[1].clone().setX(-sides[1].x) : fallback.clone().setX(-0.6));
    const second = sides[1] ?? first.clone().setX(-first.x);
    return [first, second].map((light) => light.clone().add(new THREE.Vector3(0, 0.07, 0.04)));
};

/** Charge la voiture : longueur sur Z, avant vers -Z, roues posées à y = 0. */
const loadCar = async (): Promise<CarRig> => {
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

    if (longAxisIsX) {
        model.rotation.y = rearIsPositive ? -Math.PI / 2 : Math.PI / 2;
    } else if (!rearIsPositive) {
        model.rotation.y = Math.PI;
    }

    wrapper.add(model);
    const placed = new THREE.Box3().setFromObject(wrapper);
    wrapper.position.y -= placed.min.y;

    const holder = new THREE.Group();
    holder.add(wrapper);
    const wheels = splitWheels(holder);
    return { holder, wheels, tailLights: findTailLights(holder) };
};

/** Ruban plat suivant la courbe (route, bas-côtés, vibreurs). */
const buildRibbon = (
    curve: THREE.CatmullRomCurve3,
    segments: number,
    inner: number,
    outer: number,
    height: (side: -1 | 1, edge: 'inner' | 'outer') => number,
    texture: THREE.Texture | null,
    uRepeat: number,
    material: THREE.MeshStandardMaterialParameters,
): THREE.Mesh => {
    const positions: number[] = [];
    const uvs: number[] = [];
    const indices: number[] = [];
    const length = curve.getLength();
    const up = new THREE.Vector3(0, 1, 0);
    const side = outer < 0 || inner < 0 ? -1 : 1;

    for (let i = 0; i <= segments; i++) {
        const t = i / segments;
        const point = curve.getPointAt(t);
        const tangent = curve.getTangentAt(t);
        const right = new THREE.Vector3().crossVectors(tangent, up).normalize();
        const a = point.clone().addScaledVector(right, inner);
        const b = point.clone().addScaledVector(right, outer);
        positions.push(a.x, height(side as -1 | 1, 'inner'), a.z, b.x, height(side as -1 | 1, 'outer'), b.z);
        const v = (t * length) / uRepeat;
        uvs.push(0, v, 1, v);
        if (i < segments) {
            const k = i * 2;
            indices.push(k, k + 1, k + 2, k + 1, k + 3, k + 2);
        }
    }

    const geometry = new THREE.BufferGeometry();
    geometry.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
    geometry.setAttribute('uv', new THREE.Float32BufferAttribute(uvs, 2));
    geometry.setIndex(indices);
    geometry.computeVertexNormals();
    // Les normales doivent regarder vers le haut quel que soit l'ordre des sommets.
    const normal = geometry.getAttribute('normal');
    for (let i = 0; i < normal.count; i++) {
        if (normal.getY(i) < 0) {
            normal.setXYZ(i, -normal.getX(i), -normal.getY(i), -normal.getZ(i));
        }
    }

    return new THREE.Mesh(geometry, new THREE.MeshStandardMaterial({ ...material, map: texture, side: THREE.DoubleSide }));
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

    const renderer = new THREE.WebGLRenderer({ alpha: false, antialias: true, powerPreference: 'high-performance' });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, MAX_DEVICE_PIXEL_RATIO));
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.15;
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;
    renderer.domElement.setAttribute('aria-hidden', 'true');
    canvasHost.append(renderer.domElement);

    const scene = new THREE.Scene();
    scene.background = new THREE.Color(NIGHT);
    scene.fog = new THREE.Fog(NIGHT, 30, 150);

    const pmrem = new THREE.PMREMGenerator(renderer);
    scene.environment = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;
    scene.environmentIntensity = 0.65;

    const camera = new THREE.PerspectiveCamera(42, 1, 0.1, 400);

    scene.add(new THREE.HemisphereLight('#7a86a0', '#1a0b0c', 0.4));
    const rim = new THREE.DirectionalLight('#e5383b', 1.2);
    scene.add(rim, rim.target);
    const key = new THREE.DirectionalLight('#f5f3f4', 1.1);
    key.castShadow = true;
    key.shadow.mapSize.set(2048, 2048);
    Object.assign(key.shadow.camera, { left: -9, right: 9, top: 9, bottom: -9, near: 1, far: 40 });
    key.shadow.bias = -0.0004;
    key.shadow.normalBias = 0.03;
    scene.add(key, key.target);

    // Tracé : une route sinueuse en plan.
    const curve = new THREE.CatmullRomCurve3(
        [
            new THREE.Vector3(0, 0, 40),
            new THREE.Vector3(0, 0, 0),
            new THREE.Vector3(14, 0, -40),
            new THREE.Vector3(2, 0, -85),
            new THREE.Vector3(-18, 0, -130),
            new THREE.Vector3(-8, 0, -180),
            new THREE.Vector3(16, 0, -225),
            new THREE.Vector3(10, 0, -275),
            new THREE.Vector3(0, 0, -320),
        ],
        false,
        'centripetal',
    );
    const segments = 480;

    const asphalt = makeAsphaltTexture();
    const road = buildRibbon(curve, segments, -ROAD_HALF_WIDTH, ROAD_HALF_WIDTH, () => 0, asphalt, 8, { roughness: 0.5, metalness: 0.25 });
    road.receiveShadow = true;
    scene.add(road);

    const kerbTexture = makeKerbTexture();
    const kerbMaterial = { roughness: 0.7, metalness: 0 };
    for (const sign of [-1, 1] as const) {
        const inner = sign * ROAD_HALF_WIDTH;
        const outer = sign * (ROAD_HALF_WIDTH + KERB_WIDTH);
        scene.add(buildRibbon(curve, segments, inner, outer, () => 0.02, kerbTexture, 1, kerbMaterial));
        const shoulder = buildRibbon(
            curve,
            segments,
            outer,
            sign * (ROAD_HALF_WIDTH + KERB_WIDTH + SHOULDER_WIDTH),
            (_s, edge) => (edge === 'inner' ? 0.02 : -0.25),
            null,
            1,
            { color: '#120f11', roughness: 1, metalness: 0 },
        );
        scene.add(shoulder);
    }

    const ground = new THREE.Mesh(new THREE.PlaneGeometry(900, 900), new THREE.MeshStandardMaterial({ color: '#0a0809', roughness: 1 }));
    ground.rotation.x = -Math.PI / 2;
    ground.position.y = -0.3;
    scene.add(ground);

    // Glissières (poteaux) et lampadaires
    const up = new THREE.Vector3(0, 1, 0);
    const sideOffset = ROAD_HALF_WIDTH + KERB_WIDTH + SHOULDER_WIDTH + 0.4;
    const posts: THREE.Matrix4[] = [];
    const lamps: { position: THREE.Vector3; yaw: number }[] = [];
    const length = curve.getLength();
    for (let d = 0; d < length; d += 9) {
        const t = d / length;
        const point = curve.getPointAt(t);
        const tangent = curve.getTangentAt(t);
        const right = new THREE.Vector3().crossVectors(tangent, up).normalize();
        for (const sign of [-1, 1]) {
            const position = point.clone().addScaledVector(right, sign * sideOffset);
            position.y = 0.45;
            posts.push(new THREE.Matrix4().makeTranslation(position.x, position.y, position.z));
        }
    }
    for (let d = 14; d < length; d += 42) {
        const t = d / length;
        const point = curve.getPointAt(t);
        const tangent = curve.getTangentAt(t);
        const right = new THREE.Vector3().crossVectors(tangent, up).normalize();
        const sign = Math.round(d / 42) % 2 === 0 ? 1 : -1;
        lamps.push({ position: point.clone().addScaledVector(right, sign * (sideOffset + 0.6)), yaw: Math.atan2(-tangent.x, -tangent.z) });
    }

    const postMesh = new THREE.InstancedMesh(
        new THREE.BoxGeometry(0.14, 0.9, 0.14),
        new THREE.MeshStandardMaterial({ color: '#d9d6d6', emissive: '#e5383b', emissiveIntensity: 0.35, roughness: 0.5 }),
        posts.length,
    );
    posts.forEach((matrix, index) => postMesh.setMatrixAt(index, matrix));
    scene.add(postMesh);

    const poleMaterial = new THREE.MeshStandardMaterial({ color: '#23282c', metalness: 0.6, roughness: 0.5 });
    const glowTexture = makeGlowTexture('#ffb27a');
    for (const lamp of lamps) {
        const pole = new THREE.Mesh(new THREE.CylinderGeometry(0.07, 0.1, 7, 8), poleMaterial);
        pole.position.copy(lamp.position).setY(3.5);
        const head = new THREE.Mesh(new THREE.BoxGeometry(0.9, 0.12, 0.35), new THREE.MeshBasicMaterial({ color: '#ffd9b0' }));
        head.position.copy(lamp.position).setY(7);
        head.rotation.y = lamp.yaw;
        const glow = new THREE.Sprite(new THREE.SpriteMaterial({ map: glowTexture, blending: THREE.AdditiveBlending, depthWrite: false, fog: false }));
        glow.position.copy(head.position).setY(6.9);
        glow.scale.setScalar(6);
        scene.add(pole, head, glow);
    }

    // Montagnes lointaines (silhouettes basses)
    const mountainMaterial = new THREE.MeshStandardMaterial({ color: '#150f12', roughness: 1, flatShading: true });
    for (let i = 0; i < 46; i++) {
        const t = Math.random();
        const point = curve.getPointAt(t);
        const tangent = curve.getTangentAt(t);
        const right = new THREE.Vector3().crossVectors(tangent, up).normalize();
        const sign = Math.random() < 0.5 ? -1 : 1;
        const distance = 55 + Math.random() * 90;
        const height = 14 + Math.random() * 34;
        const mountain = new THREE.Mesh(new THREE.ConeGeometry(10 + Math.random() * 18, height, 5 + Math.floor(Math.random() * 2)), mountainMaterial);
        mountain.position.copy(point).addScaledVector(right, sign * distance).setY(height / 2 - 0.5);
        mountain.rotation.y = Math.random() * Math.PI;
        scene.add(mountain);
    }

    // Ciel : étoiles et lune rouge qui suivent la caméra
    const starCount = 1600;
    const starPositions = new Float32Array(starCount * 3);
    for (let i = 0; i < starCount; i++) {
        const theta = Math.random() * Math.PI * 2;
        const phi = Math.random() * 0.46 * Math.PI;
        starPositions[i * 3] = 300 * Math.sin(phi) * Math.cos(theta);
        starPositions[i * 3 + 1] = 12 + 300 * Math.cos(phi);
        starPositions[i * 3 + 2] = 300 * Math.sin(phi) * Math.sin(theta);
    }
    const starGeometry = new THREE.BufferGeometry();
    starGeometry.setAttribute('position', new THREE.BufferAttribute(starPositions, 3));
    const sky = new THREE.Group();
    sky.add(
        new THREE.Points(
            starGeometry,
            new THREE.PointsMaterial({ color: '#f5f3f4', size: 1.1, sizeAttenuation: false, fog: false, transparent: true, opacity: 0.85 }),
        ),
    );
    const moon = new THREE.Sprite(
        new THREE.SpriteMaterial({ map: makeGlowTexture('#e5383b'), blending: THREE.AdditiveBlending, depthWrite: false, fog: false, opacity: 0.7 }),
    );
    moon.position.set(-60, 90, -260);
    moon.scale.setScalar(110);
    sky.add(moon);
    scene.add(sky);

    // Panneaux : un par étape décrite dans le HTML (data-sign-card), posés au bord de la route.
    const stepItems = [...container.querySelectorAll<HTMLElement>('[data-step-index]')];
    const cards: SignCard[] = stepItems.map((item, index) => {
        let data: Partial<SignCard>;
        try {
            data = JSON.parse(item.dataset.signCard ?? '{}') as Partial<SignCard>;
        } catch {
            data = {};
        }
        return {
            kind: data.kind ?? '',
            period: data.period ?? '',
            title: data.title ?? '',
            org: data.org ?? '',
            summary: data.summary ?? '',
            highlights: data.highlights ?? [],
            counter: data.counter ?? '',
            number: index + 1,
        };
    });
    const count = cards.length;
    const styles = getComputedStyle(document.documentElement);
    const fonts = {
        display: styles.getPropertyValue('--font-display').trim() || 'sans-serif',
        mono: styles.getPropertyValue('--font-mono').trim() || 'monospace',
    };
    await Promise.all([
        document.fonts.load(`400 40px ${fonts.display}`),
        document.fonts.load(`600 40px ${fonts.display}`),
        document.fonts.load(`700 40px ${fonts.display}`),
        document.fonts.load(`500 40px ${fonts.mono}`),
        document.fonts.load(`700 40px ${fonts.mono}`),
    ]).catch(() => undefined);

    // Position de chaque panneau le long de la route (fraction du tracé).
    const carStops = cards.map((_, index) => (count === 1 ? 0.5 : 0.15 + (0.7 * index) / (count - 1)));

    const anisotropy = renderer.capabilities.getMaxAnisotropy();
    const signs = cards.map((card, index) => {
        const sign = makeSign(makeSignTexture(card, fonts, anisotropy), glowTexture);
        const u = U_START + ((carStops[index] ?? 0.5) + 0.014) * U_SPAN;
        const point = curve.getPointAt(u);
        const heading = curve.getTangentAt(u);
        const right = new THREE.Vector3().crossVectors(heading, up).normalize();
        const side = index % 2 === 0 ? 1 : -1;
        sign.group.position.copy(point).addScaledVector(right, side * SIGN_LATERAL);
        sign.group.position.y = -0.1;
        // Le panneau fait face aux voitures qui arrivent, légèrement tourné vers la route.
        const facing = heading.clone().multiplyScalar(-0.82).addScaledVector(right, -side * 0.57).normalize();
        sign.group.lookAt(sign.group.position.clone().add(facing));
        scene.add(sign.group);
        return { ...sign, facing };
    });

    // Voiture
    const rig = await loadCar();
    const car = rig.holder;
    car.traverse((object) => {
        if ((object as THREE.Mesh).isMesh) {
            object.castShadow = true;
        }
    });
    scene.add(car);

    const shadow = new THREE.Mesh(
        new THREE.PlaneGeometry(CAR_LENGTH * 1.25, CAR_LENGTH * 0.62),
        new THREE.MeshBasicMaterial({ map: makeShadowTexture(), transparent: true, depthWrite: false, opacity: 0.55 }),
    );
    shadow.rotation.x = -Math.PI / 2;
    shadow.position.y = 0.03;
    car.add(shadow);

    const headlight = new THREE.SpotLight('#fff1e0', 260, 60, 0.5, 0.6, 1.6);
    headlight.position.set(0, 0.7, -1.9);
    headlight.target.position.set(0, 0, -18);
    car.add(headlight, headlight.target);
    const tailGlow = makeGlowTexture('#ff2a2e');
    for (const light of rig.tailLights) {
        const lamp = new THREE.PointLight('#ff2a2e', 1.4, 4.5, 2);
        lamp.position.copy(light);
        const flare = new THREE.Sprite(new THREE.SpriteMaterial({ map: tailGlow, blending: THREE.AdditiveBlending, depthWrite: false, opacity: 0.8 }));
        flare.position.copy(light).add(new THREE.Vector3(0, 0, 0.05));
        flare.scale.setScalar(0.7);
        car.add(lamp, flare);
    }

    // État
    const state = {
        target: 0,
        progress: 0,
        velocity: 0,
        yaw: 0,
        pitch: 0,
        dragging: false,
        visible: true,
        elapsed: 0,
        along: 0,
        focus: 0,
        focusIndex: -1,
        activeStep: -1,
    };
    let animationFrame: number | undefined;
    const clock = new THREE.Clock();

    const tangent = new THREE.Vector3();
    const position = new THREE.Vector3();
    const desired = new THREE.Vector3();
    const lookAt = new THREE.Vector3();
    const smoothedLook = new THREE.Vector3();
    let cameraReady = false;

    const scrollProgress = (): number => {
        const rect = container.getBoundingClientRect();
        const runway = Math.max(rect.height - window.innerHeight, 1);
        return clamp(-rect.top / runway, 0, 1);
    };

    let lastU: number | undefined;
    const hudCount = container.querySelector<HTMLElement>('[data-journey-count]');
    const hudDots = [...container.querySelectorAll<HTMLElement>('[data-journey-dot]')];

    const placeCar = (): void => {
        const u = U_START + state.along * U_SPAN;
        curve.getPointAt(u, position);
        curve.getTangentAt(u, tangent);

        car.position.copy(position);
        car.position.y = 0.02;
        car.lookAt(car.position.clone().sub(tangent));

        // Les roues tournent selon la distance réellement parcourue (dans les deux sens du défilement).
        if (lastU !== undefined) {
            const travelled = (u - lastU) * curve.getLength();
            for (const wheel of rig.wheels) {
                wheel.pivot.rotation.x -= travelled / wheel.radius;
            }
        }
        lastU = u;
    };

    const signLook = new THREE.Vector3();
    const signEye = new THREE.Vector3();
    /** Pose de la caméra face au panneau : assez loin pour le voir en entier, quel que soit le format d'écran. */
    const signPose = (index: number, aspect: number): void => {
        const sign = signs[index];
        if (!sign) {
            return;
        }
        const vFov = THREE.MathUtils.degToRad(36);
        const hFov = 2 * Math.atan(Math.tan(vFov / 2) * aspect);
        const distance = Math.max(
            SIGN_HEIGHT / (0.6 * 2 * Math.tan(vFov / 2)),
            SIGN_WIDTH / (0.78 * 2 * Math.tan(hFov / 2)),
        );
        signLook.copy(sign.group.position).setY(SIGN_CENTER_Y - 0.1);
        signEye.copy(sign.group.position).addScaledVector(sign.facing, distance).setY(Math.min(SIGN_CENTER_Y - 0.5, 4.3));
    };

    const placeCamera = (delta: number): void => {
        const p = state.progress;
        const intro = smooth(0, 0.2, p);
        const outro = smooth(0.8, 1, p);
        const free = 1 - state.focus;
        // Azimut autour de la voiture (0 = derrière) : 3/4 avant → chasse → profil
        const azimuth = lerp(2.3, 0.3, intro) + lerp(0, 1.25, outro) + state.yaw * free;
        const distance = lerp(9.2, 8.4, intro) - 1.2 * outro;
        const height = clamp(lerp(1.7, 2.5, intro) + state.pitch * free, 0.5, 6);

        const rear = tangent.clone().multiplyScalar(-1);
        rear.applyAxisAngle(up, azimuth);
        desired.copy(car.position).addScaledVector(rear, distance).setY(height);

        const aimAhead = 2.4 * (1 - clamp(Math.abs(azimuth), 0, 1.5) / 1.5);
        lookAt.copy(car.position).addScaledVector(tangent, aimAhead).setY(0.8);

        let fov = 42;
        if (state.focus > 0 && state.focusIndex >= 0) {
            signPose(state.focusIndex, camera.aspect);
            const k = smooth(0, 1, state.focus);
            desired.lerp(signEye, k);
            lookAt.lerp(signLook, k);
            fov = lerp(42, 36, k);
        }

        if (!cameraReady || reducedMotion) {
            camera.position.copy(desired);
            smoothedLook.copy(lookAt);
            cameraReady = true;
        } else {
            camera.position.lerp(desired, damp(5, delta));
            smoothedLook.lerp(lookAt, damp(7, delta));
        }
        camera.lookAt(smoothedLook);

        const kick = clamp(Math.abs(state.velocity) * 60, 0, 1);
        camera.fov = lerp(camera.fov, fov + kick * 7 * free, damp(5, delta));
        camera.updateProjectionMatrix();
    };

    const updateLights = (): void => {
        rim.position.copy(car.position).add(new THREE.Vector3(-6, 4, 5));
        rim.target.position.copy(car.position);
        key.position.copy(car.position).add(new THREE.Vector3(5, 8, -4));
        key.target.position.copy(car.position);
        sky.position.copy(camera.position);
    };

    const updateSteps = (): void => {
        let active = 0;
        let best = Infinity;
        for (let index = 0; index < count; index++) {
            const distance = Math.abs(state.along - (carStops[index] ?? 0));
            if (distance < best) {
                best = distance;
                active = index;
            }
        }
        if (active !== state.activeStep) {
            state.activeStep = active;
            container.dataset.step = String(active);
            stepItems.forEach((item, index) => item.classList.toggle('is-active', index === active));
            hudDots.forEach((dot, index) => {
                dot.classList.toggle('is-active', index === active);
                dot.classList.toggle('is-done', index < active);
            });
            if (hudCount) {
                hudCount.textContent = cards[active]?.counter ?? '';
            }
        }
        container.dataset.moving = state.progress > 0.02 ? 'true' : 'false';
        container.dataset.focus = state.focus > 0.25 ? 'true' : 'false';
        container.style.setProperty('--journey', state.progress.toFixed(4));
        signs.forEach((sign, index) => {
            const lit = index === state.focusIndex ? state.focus : 0;
            sign.face.color.setScalar(0.82 + 0.18 * lit);
            sign.glow.material.opacity = 0.35 + 0.5 * lit;
        });
    };

    const update = (delta: number): void => {
        state.elapsed += delta;
        state.target = scrollProgress();
        const previous = state.progress;
        if (reducedMotion) {
            state.progress = state.target;
        } else {
            // La progression rattrape le défilement en douceur : la voiture roule en continu, sans arrêt devant les panneaux.
            state.progress += (state.target - state.progress) * damp(4.5, delta);
        }
        state.velocity = lerp(state.velocity, (state.progress - previous) / Math.max(delta, 0.001), damp(6, delta)) / 60;
        if (!state.dragging) {
            state.yaw *= 1 - damp(0.9, delta);
            state.pitch *= 1 - damp(1.2, delta);
        }

        // Conduite continue : la position sur la route suit directement le défilement (aucun arrêt, aucun zoom sur les panneaux).
        state.along = state.progress;
        state.focus = 0;
        state.focusIndex = -1;

        placeCar();
        placeCamera(delta);
        updateLights();
        updateSteps();
    };

    const render = (): void => {
        animationFrame = undefined;
        if (!state.visible) {
            return;
        }

        update(Math.min(clock.getDelta(), 0.05));
        renderer.render(scene, camera);

        if (!reducedMotion || state.dragging) {
            animationFrame = requestAnimationFrame(render);
        }
    };

    const wake = (): void => {
        if (state.visible && animationFrame === undefined) {
            clock.getDelta();
            render();
        }
    };

    const resize = (): void => {
        const width = Math.max(canvasHost.clientWidth, 1);
        const height = Math.max(canvasHost.clientHeight, 1);
        renderer.setSize(width, height, false);
        camera.aspect = width / height;
        camera.updateProjectionMatrix();
    };

    // Interaction : glisser pour tourner autour de la voiture (le défilement vertical reste libre au toucher)
    let lastX = 0;
    let lastY = 0;
    const stage = canvasHost;
    stage.addEventListener('pointerdown', (event) => {
        if (event.pointerType === 'mouse' && event.button !== 0) {
            return;
        }
        state.dragging = true;
        lastX = event.clientX;
        lastY = event.clientY;
        stage.setPointerCapture(event.pointerId);
        container.dataset.dragged = 'true';
        wake();
    });
    stage.addEventListener('pointermove', (event) => {
        if (!state.dragging) {
            return;
        }
        state.yaw -= (event.clientX - lastX) * 0.007;
        state.pitch = clamp(state.pitch + (event.clientY - lastY) * 0.012, -1, 3);
        lastX = event.clientX;
        lastY = event.clientY;
    });
    const endDrag = (event: PointerEvent): void => {
        state.dragging = false;
        if (stage.hasPointerCapture(event.pointerId)) {
            stage.releasePointerCapture(event.pointerId);
        }
        wake();
    };
    stage.addEventListener('pointerup', endDrag);
    stage.addEventListener('pointercancel', endDrag);

    // Visibilité : contrôle géométrique au défilement (fiable même quand l'IntersectionObserver est silencieux).
    const checkVisibility = (): void => {
        const rect = container.getBoundingClientRect();
        state.visible = rect.bottom > 0 && rect.top < window.innerHeight;
        if (state.visible) {
            wake();
        } else if (animationFrame !== undefined) {
            cancelAnimationFrame(animationFrame);
            animationFrame = undefined;
        }
    };
    window.addEventListener('scroll', checkVisibility, { passive: true });
    window.addEventListener('resize', checkVisibility, { passive: true });

    const resizeObserver = new ResizeObserver(() => {
        resize();
        if (reducedMotion) {
            wake();
        }
    });
    resizeObserver.observe(canvasHost);
    resize();
    checkVisibility();
    if (animationFrame === undefined && state.visible) {
        render();
    }
};
