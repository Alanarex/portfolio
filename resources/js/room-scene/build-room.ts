import {
    BoxGeometry,
    CylinderGeometry,
    Group,
    IcosahedronGeometry,
    Mesh,
    MeshStandardMaterial,
    type Object3D,
} from 'three';

/** Matériaux partagés : un par couleur, pour garder le nombre de matériaux très bas. */
export function createPalette() {
    const cache = new Map<string, MeshStandardMaterial>();

    return {
        solid(color: number, emissive = 0x000000, emissiveIntensity = 0): MeshStandardMaterial {
            const key = `${color}:${emissive}:${emissiveIntensity}`;
            let material = cache.get(key);
            if (!material) {
                material = new MeshStandardMaterial({ color, emissive, emissiveIntensity, roughness: 0.9, metalness: 0, flatShading: true });
                cache.set(key, material);
            }
            return material;
        },
        all: () => [...cache.values()],
    };
}

export interface AvatarRig {
    root: Group;
    torso: Group;
    rightArm: Group;
    leftArm: Group;
    head: Mesh;
}

export interface BuiltRoom {
    root: Group;
    avatar: AvatarRig;
    hotspots: Map<string, Object3D>;
    palette: ReturnType<typeof createPalette>;
}

type Palette = ReturnType<typeof createPalette>;

const unitBox = new BoxGeometry(1, 1, 1);
const unitCylinder = new CylinderGeometry(0.5, 0.5, 1, 8);
const unitBlob = new IcosahedronGeometry(0.5, 1);

function box(parent: Object3D, material: MeshStandardMaterial, size: [number, number, number], position: [number, number, number]): Mesh {
    const mesh = new Mesh(unitBox, material);
    mesh.scale.set(...size);
    mesh.position.set(...position);
    parent.add(mesh);
    return mesh;
}

function cylinder(parent: Object3D, material: MeshStandardMaterial, size: [number, number, number], position: [number, number, number]): Mesh {
    const mesh = new Mesh(unitCylinder, material);
    mesh.scale.set(...size);
    mesh.position.set(...position);
    parent.add(mesh);
    return mesh;
}

function tagged(root: Group, hotspots: Map<string, Object3D>, key: string): Group {
    const group = new Group();
    group.name = key;
    group.userData.hotspot = key;
    root.add(group);
    hotspots.set(key, group);
    return group;
}

/**
 * Pièce procédurale low-poly (aucun fichier GLB, aucune texture) : cubes et cylindres à géométrie partagée.
 * Les objets listés dans le manifeste sont des groupes nommés (`userData.hotspot`).
 */
export function buildRoom(): BuiltRoom {
    const palette: Palette = createPalette();
    const root = new Group();
    const hotspots = new Map<string, Object3D>();
    const wood = palette.solid(0x6b4a3a);
    const darkWood = palette.solid(0x3f2d27);
    const wall = palette.solid(0x5a4d50);
    const wallSide = palette.solid(0x4a3f42);
    const floor = palette.solid(0x2c2425);
    const metal = palette.solid(0x4b5258);
    const black = palette.solid(0x101214);
    const screen = palette.solid(0x0c2a3a, 0x2f9bd1, 0.9);
    const screenAlt = palette.solid(0x122a1c, 0x3fd18a, 0.8);
    const accent = palette.solid(0xe5383b, 0xe5383b, 0.25);
    const paper = palette.solid(0xf0ece8);
    const skin = palette.solid(0xd9a37e);
    const shirt = palette.solid(0xa4161a);
    const trousers = palette.solid(0x23303b);
    const hair = palette.solid(0x2b1b14);
    const green = palette.solid(0x2f8f5b);
    const blue = palette.solid(0x2f6fb0);
    const yellow = palette.solid(0xe0b341);

    // Structure : sol et deux murs (la face ouverte reste vers la caméra).
    box(root, floor, [7, 0.1, 6], [0, -0.05, 0]);
    box(root, wall, [7, 3.2, 0.1], [0, 1.6, -2.55]);
    box(root, wallSide, [0.1, 3.2, 6], [-3.55, 1.6, 0]);
    box(root, palette.solid(0x4a3a3d), [3.2, 0.02, 2.4], [0.1, 0.01, -0.4]); // tapis

    // Bureau.
    box(root, wood, [2.6, 0.08, 1.0], [0.2, 0.76, -1.85]);
    for (const [x, z] of [[-1.0, -2.25], [1.4, -2.25], [-1.0, -1.45], [1.4, -1.45]]) {
        box(root, darkWood, [0.08, 0.76, 0.08], [x, 0.38, z]);
    }

    // Chaise.
    const chair = new Group();
    chair.position.set(0.2, 0, -1.0);
    root.add(chair);
    box(chair, black, [0.6, 0.1, 0.6], [0, 0.5, 0]);
    box(chair, black, [0.6, 0.7, 0.1], [0, 0.95, 0.3]);
    cylinder(chair, metal, [0.08, 0.45, 0.08], [0, 0.25, 0]);
    box(chair, metal, [0.6, 0.05, 0.08], [0, 0.03, 0]);
    box(chair, metal, [0.08, 0.05, 0.6], [0, 0.03, 0]);

    // Écran principal.
    const main = tagged(root, hotspots, 'main_monitor');
    box(main, black, [1.1, 0.66, 0.05], [0.2, 1.22, -2.2]);
    box(main, screen, [1.0, 0.56, 0.02], [0.2, 1.22, -2.18]);
    for (let i = 0; i < 5; i += 1) {
        box(main, i % 2 ? paper : accent, [0.25 + (i % 3) * 0.2, 0.03, 0.01], [-0.05 + (i % 3) * 0.1, 1.4 - i * 0.09, -2.165]);
    }
    box(main, metal, [0.08, 0.3, 0.08], [0.2, 0.95, -2.2]);
    box(main, metal, [0.4, 0.03, 0.25], [0.2, 0.82, -2.15]);

    // Second écran.
    const secondary = tagged(root, hotspots, 'secondary_monitor');
    secondary.position.set(-1.05, 0, 0.08);
    secondary.rotation.y = 0.4;
    box(secondary, black, [0.8, 0.5, 0.05], [0, 1.15, -2.1]);
    box(secondary, screenAlt, [0.72, 0.42, 0.02], [0, 1.15, -2.08]);
    box(secondary, metal, [0.07, 0.26, 0.07], [0, 0.93, -2.1]);
    box(secondary, metal, [0.3, 0.03, 0.2], [0, 0.82, -2.05]);

    // Clavier, souris, portable, téléphone.
    box(root, palette.solid(0x20262b), [0.62, 0.03, 0.2], [0.2, 0.82, -1.62]);
    box(root, palette.solid(0x20262b), [0.08, 0.03, 0.12], [0.75, 0.82, -1.62]);
    const laptop = new Group();
    laptop.position.set(1.15, 0, 0);
    root.add(laptop);
    box(laptop, metal, [0.5, 0.03, 0.34], [0, 0.82, -1.9]);
    const lid = box(laptop, black, [0.5, 0.32, 0.02], [0, 0.99, -2.06]);
    lid.rotation.x = -0.18;
    const phone = tagged(root, hotspots, 'phone');
    box(phone, black, [0.16, 0.02, 0.3], [-0.55, 0.81, -1.55]).rotation.y = 0.3;
    box(phone, screen, [0.13, 0.01, 0.26], [-0.55, 0.822, -1.55]).rotation.y = 0.3;

    // Étagère (bibliothèque) contre le mur de gauche.
    const shelf = tagged(root, hotspots, 'bookshelf');
    box(shelf, darkWood, [0.45, 2.1, 1.6], [-3.25, 1.05, -0.6]);
    const bookColors = [accent.color.getHex(), green.color.getHex(), blue.color.getHex(), yellow.color.getHex(), paper.color.getHex()];
    for (let row = 0; row < 4; row += 1) {
        box(shelf, wood, [0.46, 0.04, 1.6], [-3.25, 0.2 + row * 0.5, -0.6]);
        for (let book = 0; book < 6; book += 1) {
            box(shelf, palette.solid(bookColors[(row + book) % bookColors.length]), [0.2, 0.34, 0.12], [-3.02, 0.4 + row * 0.5, -1.2 + book * 0.2]);
        }
    }

    // Maquette d'avion sur l'étagère.
    const airplane = tagged(root, hotspots, 'airplane_model');
    airplane.position.set(-3.2, 2.25, -0.6);
    cylinder(airplane, paper, [0.1, 0.6, 0.1], [0, 0, 0]).rotation.z = Math.PI / 2;
    box(airplane, accent, [0.12, 0.02, 0.6], [0, 0, 0]);
    box(airplane, accent, [0.14, 0.14, 0.02], [-0.28, 0.07, 0]);
    box(airplane, accent, [0.1, 0.02, 0.22], [-0.28, 0.01, 0]);

    // Voiture miniature sur le bureau.
    const car = tagged(root, hotspots, 'car_model');
    car.position.set(-0.85, 0.8, -1.65);
    car.rotation.y = 0.5;
    box(car, accent, [0.34, 0.07, 0.16], [0, 0.07, 0]);
    box(car, black, [0.18, 0.07, 0.14], [-0.02, 0.13, 0]);
    for (const [x, z] of [[-0.11, -0.09], [0.11, -0.09], [-0.11, 0.09], [0.11, 0.09]]) {
        const wheel = cylinder(car, black, [0.07, 0.04, 0.07], [x, 0.035, z]);
        wheel.rotation.x = Math.PI / 2;
    }

    // Diplômes et certificats (mur du fond).
    const certificates = tagged(root, hotspots, 'certificates');
    for (let i = 0; i < 3; i += 1) {
        box(certificates, darkWood, [0.62, 0.46, 0.03], [-2.2 + i * 0.75, 2.0, -2.48]);
        box(certificates, paper, [0.54, 0.38, 0.02], [-2.2 + i * 0.75, 2.0, -2.46]);
        box(certificates, accent, [0.3, 0.03, 0.01], [-2.2 + i * 0.75, 2.05, -2.445]);
    }

    // Tableau Kanban.
    const kanban = tagged(root, hotspots, 'kanban_board');
    box(kanban, paper, [1.5, 0.9, 0.03], [0.55, 2.2, -2.48]);
    for (let column = 0; column < 3; column += 1) {
        box(kanban, metal, [0.02, 0.8, 0.01], [0.05 + column * 0.5, 2.2, -2.455]);
        for (let card = 0; card < 3 - column; card += 1) {
            box(kanban, [yellow, green, blue][column], [0.36, 0.14, 0.01], [0.3 + column * 0.5, 2.5 - card * 0.22, -2.45]);
        }
    }

    // Calendrier.
    const calendar = tagged(root, hotspots, 'calendar');
    box(calendar, paper, [0.55, 0.65, 0.03], [1.65, 1.85, -2.48]);
    box(calendar, accent, [0.55, 0.14, 0.04], [1.65, 2.1, -2.47]);
    for (let i = 0; i < 12; i += 1) {
        box(calendar, metal, [0.07, 0.07, 0.01], [1.46 + (i % 4) * 0.12, 1.88 - Math.floor(i / 4) * 0.12, -2.45]);
    }

    // Baie serveur.
    const rack = tagged(root, hotspots, 'server_rack');
    box(rack, black, [0.65, 1.7, 0.6], [2.7, 0.85, -2.1]);
    for (let i = 0; i < 6; i += 1) {
        box(rack, metal, [0.58, 0.18, 0.02], [2.7, 0.3 + i * 0.26, -1.79]);
        box(rack, i % 2 ? green : accent, [0.05, 0.04, 0.02], [2.9, 0.3 + i * 0.26, -1.78]);
    }

    // Canne à pêche appuyée contre le mur.
    const fishing = tagged(root, hotspots, 'fishing_object');
    fishing.position.set(1.95, 1.0, -2.35);
    cylinder(fishing, yellow, [0.03, 2.0, 0.03], [0, 0, 0]).rotation.z = 0.12;
    cylinder(fishing, metal, [0.1, 0.06, 0.1], [0.02, -0.25, 0.04]).rotation.x = Math.PI / 2;

    // Avatar stylisé à partir de formes simples (assis, face au bureau) ; remplaçable par le modèle final.
    const avatarRoot = new Group();
    avatarRoot.name = 'avatar';
    avatarRoot.userData.avatar = true;
    avatarRoot.position.set(0.2, 0, -1.0);
    root.add(avatarRoot);
    box(avatarRoot, trousers, [0.42, 0.16, 0.5], [0, 0.62, -0.05]);
    box(avatarRoot, trousers, [0.16, 0.5, 0.16], [-0.12, 0.35, -0.3]);
    box(avatarRoot, trousers, [0.16, 0.5, 0.16], [0.12, 0.35, -0.3]);
    const torso = new Group();
    torso.position.set(0, 0.7, 0);
    avatarRoot.add(torso);
    box(torso, shirt, [0.46, 0.6, 0.28], [0, 0.3, 0]);
    const head = new Mesh(unitBlob, skin);
    head.scale.set(0.3, 0.34, 0.3);
    head.position.set(0, 0.8, 0);
    torso.add(head);
    box(torso, hair, [0.32, 0.12, 0.32], [0, 0.95, 0.01]);
    const rightArm = new Group();
    rightArm.position.set(0.3, 0.55, 0);
    torso.add(rightArm);
    box(rightArm, shirt, [0.12, 0.42, 0.12], [0, -0.2, 0]);
    box(rightArm, skin, [0.1, 0.1, 0.1], [0, -0.45, 0]);
    const leftArm = new Group();
    leftArm.position.set(-0.3, 0.55, 0);
    torso.add(leftArm);
    box(leftArm, shirt, [0.12, 0.42, 0.12], [0, -0.2, 0]);
    box(leftArm, skin, [0.1, 0.1, 0.1], [0, -0.45, 0]);

    return {
        root,
        avatar: { root: avatarRoot, torso, rightArm, leftArm, head },
        hotspots,
        palette,
    };
}

export function disposeRoom(room: BuiltRoom): void {
    unitBox.dispose();
    unitCylinder.dispose();
    unitBlob.dispose();
    for (const material of room.palette.all()) {
        material.dispose();
    }
}
