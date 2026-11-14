import {
    Box3,
    Box3Helper,
    Color,
    DirectionalLight,
    HemisphereLight,
    MathUtils,
    PerspectiveCamera,
    Raycaster,
    Scene,
    Vector2,
    Vector3,
    WebGLRenderer,
    type Material,
    type Object3D,
} from 'three';
import { createAvatarMachine, type AvatarState } from './avatar-machine';
import { buildRoom, disposeRoom } from './build-room';
import type { RoomController, RoomManifest, RoomRuntimeOptions } from './types';

const OVERVIEW = { position: new Vector3(3.1, 2.5, 4.9), target: new Vector3(-0.1, 1.15, -1.3) };
const MAX_YAW = 0.45;
const MAX_PITCH = 0.2;
const DRAG_THRESHOLD = 5;

const POSES: Record<AvatarState, { torsoY: number; rightX: number; rightZ: number; leftX: number }> = {
    LOADING: { torsoY: 0, rightX: 1.1, rightZ: 0, leftX: 1.1 },
    IDLE_CODING: { torsoY: 0, rightX: 1.1, rightZ: 0, leftX: 1.1 },
    TURNING_TO_USER: { torsoY: 2.6, rightX: 0.2, rightZ: 0.2, leftX: 0.2 },
    GREETING: { torsoY: 2.6, rightX: 0, rightZ: 2.7, leftX: 0.2 },
    RETURNING_TO_CODING: { torsoY: 0, rightX: 1.1, rightZ: 0, leftX: 1.1 },
};

function easeInOut(t: number): number {
    return t < 0.5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2;
}

/** Monte la scène dans `host`. Lève une erreur si WebGL est indisponible (l'appelant bascule alors sur le repli HTML). */
export function mountRoomScene(host: HTMLElement, manifest: RoomManifest, options: RoomRuntimeOptions): RoomController {
    const canvasHost = host.querySelector<HTMLElement>('[data-room-canvas]');
    const stage = host.querySelector<HTMLElement>('[data-room-stage]');
    const bubble = host.querySelector<HTMLElement>('[data-room-bubble]');
    const controls = host.querySelector<HTMLElement>('[data-room-controls]');
    const enterButton = host.querySelector<HTMLButtonElement>('[data-room-enter]');
    const greetButton = host.querySelector<HTMLButtonElement>('[data-room-greet]');
    if (!canvasHost || !stage) {
        throw new Error('room markup missing');
    }

    const { reducedMotion, lowPower } = options;
    const abort = new AbortController();
    const { signal } = abort;
    const labels = new Map(manifest.hotspots.map((hotspot) => [hotspot.key, hotspot]));

    const renderer = new WebGLRenderer({ antialias: !lowPower, alpha: true, powerPreference: lowPower ? 'low-power' : 'default' });
    const maxDpr = lowPower ? 1 : 1.5;
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, maxDpr));
    host.dataset.roomDpr = String(renderer.getPixelRatio());
    const canvas = renderer.domElement;
    canvas.setAttribute('role', 'img');
    canvas.setAttribute('aria-label', host.dataset.roomLabel ?? '');
    canvasHost.append(canvas);

    const scene = new Scene();
    scene.background = new Color(0x120f10);
    const camera = new PerspectiveCamera(38, 16 / 9, 0.1, 40);
    scene.add(new HemisphereLight(0xffffff, 0x4a3f42, 2.0));
    const sun = new DirectionalLight(0xfff1e0, 2.2);
    sun.position.set(3, 5, 4);
    scene.add(sun);
    const room = buildRoom();
    scene.add(room.root);

    // Pose de la caméra : vue d'ensemble fixe, ou cadrage contraint sur un objet (transitions courtes).
    let fromPosition = OVERVIEW.position.clone();
    let fromTarget = OVERVIEW.target.clone();
    let toPosition = OVERVIEW.position.clone();
    let toTarget = OVERVIEW.target.clone();
    let tweenStart = 0;
    let tweenDuration = 0;
    let yaw = 0;
    let pitch = 0;
    const currentPosition = OVERVIEW.position.clone();
    const currentTarget = OVERVIEW.target.clone();

    const applyCamera = (): void => {
        const offset = currentPosition.clone().sub(currentTarget);
        offset.applyAxisAngle(new Vector3(0, 1, 0), yaw);
        offset.y += pitch * offset.length();
        camera.position.copy(currentTarget).add(offset);
        camera.lookAt(currentTarget);
    };

    const flyTo = (position: Vector3, target: Vector3): void => {
        fromPosition = currentPosition.clone();
        fromTarget = currentTarget.clone();
        toPosition = position;
        toTarget = target;
        tweenStart = performance.now();
        tweenDuration = reducedMotion ? 0 : 650;
        if (tweenDuration === 0) {
            currentPosition.copy(toPosition);
            currentTarget.copy(toTarget);
            applyCamera();
        }
        requestRender();
    };

    const focusOn = (key: string | null): void => {
        const node = key ? room.hotspots.get(key) : null;
        if (!node) {
            yaw = 0;
            pitch = 0;
            flyTo(OVERVIEW.position.clone(), OVERVIEW.target.clone());
            return;
        }
        const box = new Box3().setFromObject(node);
        const center = box.getCenter(new Vector3());
        const size = box.getSize(new Vector3()).length();
        const position = center.clone().add(new Vector3(0.45, 0.3, 1).normalize().multiplyScalar(Math.max(1.8, size * 2.2)));
        position.y = MathUtils.clamp(position.y, 0.9, 3);
        position.z = Math.min(position.z, 5);
        position.x = MathUtils.clamp(position.x, -2.6, 3.4);
        yaw = 0;
        pitch = 0;
        flyTo(position, center);
    };

    // Surbrillance discrète de l'objet survolé ou ciblé.
    const outline = new Box3Helper(new Box3(), new Color(0xe5383b));
    outline.visible = false;
    scene.add(outline);
    let highlighted: string | null = null;
    const setHighlight = (key: string | null): void => {
        if (key === highlighted) {
            return;
        }
        highlighted = key;
        const node = key ? room.hotspots.get(key) : null;
        if (node) {
            outline.box.setFromObject(node);
            outline.visible = true;
        } else {
            outline.visible = false;
        }
        for (const link of host.querySelectorAll<HTMLElement>('[data-room-hotspot]')) {
            link.classList.toggle('is-highlighted', link.dataset.roomHotspot === key);
        }
        requestRender();
    };

    const tooltip = document.createElement('div');
    tooltip.className = 'room__tooltip';
    tooltip.setAttribute('aria-hidden', 'true');
    tooltip.hidden = true;
    canvasHost.append(tooltip);
    if (bubble) {
        canvasHost.append(bubble);
    }

    // --- Avatar -----------------------------------------------------------------------------------------------
    const rig = room.avatar;
    const pose = { torsoY: 0, rightX: 1.1, rightZ: 0, leftX: 1.1 };

    const showBubble = (visible: boolean): void => {
        if (!bubble) {
            return;
        }
        const text = bubble.querySelector<HTMLElement>('[data-room-bubble-text]');
        if (visible && text) {
            text.textContent = manifest.greeting;
        }
        bubble.hidden = !visible;
    };

    const avatar = createAvatarMachine({
        reducedMotion,
        onChange: (state) => {
            host.dataset.avatarState = state;
            if (state === 'GREETING') {
                showBubble(true);
            } else if (state === 'RETURNING_TO_CODING') {
                showBubble(false);
            }
            if (greetButton) {
                greetButton.disabled = state !== 'IDLE_CODING';
            }
            for (const button of dialogGreetButtons) {
                button.disabled = state !== 'IDLE_CODING';
            }
            requestRender();
        },
    });
    const dialogGreetButtons: HTMLButtonElement[] = [];
    host.dataset.avatarState = avatar.state;

    const greet = (): void => avatar.greet();
    greetButton?.addEventListener('click', greet, { signal });
    bubble?.querySelector('button')?.addEventListener('click', () => avatar.dismiss(), { signal });

    // --- Rendu : pause hors viewport, résolution bornée, cadence limitée en mode économe -------------------------
    let intersecting = typeof IntersectionObserver === 'undefined';
    let documentVisible = !document.hidden;
    let dialogOpen = false;
    let disposed = false;
    let raf = 0;
    let dirty = true;
    let lastFrame = 0;
    let lastRendered = 0;
    const animated = !reducedMotion;

    const shouldRun = (): boolean => !disposed && (intersecting || dialogOpen) && documentVisible;
    const setRendering = (rendering: boolean): void => {
        host.dataset.roomRendering = String(rendering);
    };

    const resize = (): void => {
        const width = canvasHost.clientWidth;
        const height = canvasHost.clientHeight;
        if (width === 0 || height === 0) {
            return;
        }
        renderer.setSize(width, height, false);
        camera.aspect = width / height;
        // Garde l'ensemble de la pièce cadré sur les formats étroits.
        camera.fov = camera.aspect < 1.2 ? 38 + (1.2 - camera.aspect) * 28 : 38;
        camera.updateProjectionMatrix();
        requestRender();
    };

    const updatePoses = (dt: number): void => {
        const target = POSES[avatar.state];
        const t = performance.now() / 1000;
        const damp = reducedMotion ? 1 : Math.min(1, dt * 7);
        pose.torsoY += (target.torsoY - pose.torsoY) * damp;
        pose.rightX += (target.rightX - pose.rightX) * damp;
        pose.rightZ += (target.rightZ - pose.rightZ) * damp;
        pose.leftX += (target.leftX - pose.leftX) * damp;
        const typing = avatar.state === 'IDLE_CODING' && animated;
        const swing = typing ? Math.sin(t * (lowPower ? 5 : 10)) * 0.07 : 0;
        rig.torso.rotation.y = pose.torsoY;
        rig.leftArm.rotation.x = pose.leftX + swing;
        rig.rightArm.rotation.x = pose.rightX - swing;
        rig.rightArm.rotation.z = pose.rightZ + (avatar.state === 'GREETING' && animated ? Math.sin(t * 8) * 0.22 : 0);
        rig.head.rotation.x = typing ? Math.sin(t * 1.4) * 0.04 : 0;
    };

    const frame = (now: number): void => {
        raf = 0;
        if (!shouldRun()) {
            setRendering(false);
            return;
        }
        const minInterval = lowPower ? 1000 / 30 : 0;
        if (now - lastRendered < minInterval - 1) {
            schedule();
            return;
        }
        const dt = Math.min(0.1, (now - (lastFrame || now)) / 1000);
        lastFrame = now;
        lastRendered = now;

        let tweening = false;
        if (tweenDuration > 0) {
            const progress = Math.min(1, (performance.now() - tweenStart) / tweenDuration);
            const eased = easeInOut(progress);
            currentPosition.lerpVectors(fromPosition, toPosition, eased);
            currentTarget.lerpVectors(fromTarget, toTarget, eased);
            tweening = progress < 1;
            if (!tweening) {
                tweenDuration = 0;
            }
        }
        applyCamera();
        updatePoses(dt);
        renderer.render(scene, camera);
        dirty = false;
        setRendering(true);
        if (animated || tweening || dirty) {
            schedule();
        } else {
            setRendering(false);
        }
    };

    function schedule(): void {
        if (!raf && shouldRun()) {
            raf = requestAnimationFrame(frame);
        }
    }

    function requestRender(): void {
        dirty = true;
        lastFrame = 0;
        schedule();
    }

    const observer =
        typeof IntersectionObserver === 'undefined'
            ? null
            : new IntersectionObserver((entries) => {
                  intersecting = entries.some((entry) => entry.isIntersecting);
                  if (intersecting) {
                      requestRender();
                  } else {
                      setRendering(false);
                  }
              });
    observer?.observe(stage);
    document.addEventListener(
        'visibilitychange',
        () => {
            documentVisible = !document.hidden;
            requestRender();
        },
        { signal },
    );
    const resizeObserver = typeof ResizeObserver === 'undefined' ? null : new ResizeObserver(resize);
    resizeObserver?.observe(canvasHost);
    canvas.addEventListener(
        'webglcontextlost',
        (event) => {
            event.preventDefault();
            options.onFailure();
        },
        { signal },
    );

    // --- Interactions : survol, clic, glisser contraint, clavier ------------------------------------------------
    const raycaster = new Raycaster();
    const pointer = new Vector2();

    const pick = (clientX: number, clientY: number): { hotspot: string | null; avatar: boolean } => {
        const rect = canvas.getBoundingClientRect();
        pointer.set(((clientX - rect.left) / rect.width) * 2 - 1, -((clientY - rect.top) / rect.height) * 2 + 1);
        raycaster.setFromCamera(pointer, camera);
        const hit = raycaster.intersectObjects(room.root.children, true)[0];
        let node: Object3D | null = hit?.object ?? null;
        while (node) {
            if (typeof node.userData.hotspot === 'string' && labels.has(node.userData.hotspot)) {
                return { hotspot: node.userData.hotspot, avatar: false };
            }
            if (node.userData.avatar === true) {
                return { hotspot: null, avatar: true };
            }
            node = node.parent;
        }
        return { hotspot: null, avatar: false };
    };

    let dragging = false;
    let dragMoved = false;
    let dragStart = { x: 0, y: 0, yaw: 0, pitch: 0 };

    const showTooltip = (text: string | null, clientX: number, clientY: number): void => {
        if (!text) {
            tooltip.hidden = true;
            return;
        }
        const rect = canvasHost.getBoundingClientRect();
        tooltip.textContent = text;
        tooltip.style.left = `${clientX - rect.left}px`;
        tooltip.style.top = `${clientY - rect.top}px`;
        tooltip.hidden = false;
    };

    canvas.addEventListener(
        'pointerdown',
        (event) => {
            dragging = true;
            dragMoved = false;
            dragStart = { x: event.clientX, y: event.clientY, yaw, pitch };
            canvas.setPointerCapture(event.pointerId);
        },
        { signal },
    );
    canvas.addEventListener(
        'pointermove',
        (event) => {
            if (dragging) {
                const dx = event.clientX - dragStart.x;
                const dy = event.clientY - dragStart.y;
                if (Math.hypot(dx, dy) > DRAG_THRESHOLD) {
                    dragMoved = true;
                }
                // Le glisser n'est actif qu'en plein écran : l'aperçu en ligne garde sa caméra fixe.
                if (dragMoved && dialogOpen) {
                    yaw = MathUtils.clamp(dragStart.yaw - dx * 0.004, -MAX_YAW, MAX_YAW);
                    pitch = MathUtils.clamp(dragStart.pitch + dy * 0.002, -MAX_PITCH, MAX_PITCH);
                    requestRender();
                }
                return;
            }
            const hit = pick(event.clientX, event.clientY);
            setHighlight(hit.hotspot);
            canvas.style.cursor = hit.hotspot || hit.avatar ? 'pointer' : '';
            showTooltip(hit.hotspot ? (labels.get(hit.hotspot)?.label ?? null) : hit.avatar ? manifest.strings.greet : null, event.clientX, event.clientY);
        },
        { signal },
    );
    canvas.addEventListener(
        'pointerleave',
        () => {
            if (!dragging) {
                setHighlight(null);
                tooltip.hidden = true;
            }
        },
        { signal },
    );
    canvas.addEventListener(
        'pointerup',
        (event) => {
            const wasDrag = dragMoved;
            dragging = false;
            if (canvas.hasPointerCapture(event.pointerId)) {
                canvas.releasePointerCapture(event.pointerId);
            }
            if (wasDrag) {
                return;
            }
            const hit = pick(event.clientX, event.clientY);
            if (hit.hotspot) {
                activate(hit.hotspot);
            } else if (hit.avatar) {
                greet();
            }
        },
        { signal },
    );

    canvas.addEventListener(
        'keydown',
        (event) => {
            if (!dialogOpen) {
                return;
            }
            const step = 0.08;
            const handled = { ArrowLeft: () => (yaw -= step), ArrowRight: () => (yaw += step), ArrowUp: () => (pitch -= step / 2), ArrowDown: () => (pitch += step / 2), Home: () => { yaw = 0; pitch = 0; focusOn(null); } }[event.key];
            if (handled) {
                event.preventDefault();
                handled();
                yaw = MathUtils.clamp(yaw, -MAX_YAW, MAX_YAW);
                pitch = MathUtils.clamp(pitch, -MAX_PITCH, MAX_PITCH);
                requestRender();
            }
        },
        { signal },
    );

    // Les liens HTML équivalents (hors canvas) pilotent aussi la surbrillance.
    for (const link of host.querySelectorAll<HTMLAnchorElement>('[data-room-hotspot]')) {
        const key = link.dataset.roomHotspot ?? null;
        link.addEventListener('mouseenter', () => setHighlight(key), { signal });
        link.addEventListener('focus', () => setHighlight(key), { signal });
        link.addEventListener('mouseleave', () => setHighlight(null), { signal });
        link.addEventListener('blur', () => setHighlight(null), { signal });
    }

    // --- Plein écran : <dialog> modal (focus piégé, Échap, restitution du focus) ----------------------------------
    const dialog = document.createElement('dialog');
    dialog.className = 'room-dialog theme-dark';
    dialog.setAttribute('aria-label', host.dataset.roomDialogLabel ?? '');
    dialog.setAttribute('data-lenis-prevent', '');
    const bar = document.createElement('div');
    bar.className = 'room-dialog__bar';
    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'button button--ghost';
    closeButton.textContent = manifest.strings.close;
    const dialogGreet = document.createElement('button');
    dialogGreet.type = 'button';
    dialogGreet.className = 'button button--ghost';
    dialogGreet.textContent = manifest.strings.greet;
    dialogGreetButtons.push(dialogGreet);
    dialogGreet.addEventListener('click', greet, { signal });
    bar.append(closeButton, dialogGreet);
    const body = document.createElement('div');
    body.className = 'room-dialog__body';
    const slot = document.createElement('div');
    slot.className = 'room-dialog__slot';
    const list = document.createElement('ul');
    list.className = 'room-dialog__links';
    const pageLinks = new Map(
        [...host.querySelectorAll<HTMLAnchorElement>('[data-room-hotspot]')].map((link) => [link.dataset.roomHotspot ?? '', link]),
    );
    for (const hotspot of manifest.hotspots) {
        const item = document.createElement('li');
        const link = document.createElement('a');
        link.href = hotspot.href;
        link.dataset.roomDialogHotspot = hotspot.key;
        const strong = document.createElement('strong');
        strong.textContent = hotspot.label;
        const span = document.createElement('span');
        span.textContent = hotspot.description;
        link.append(strong, span);
        link.addEventListener('focus', () => { setHighlight(hotspot.key); focusOn(hotspot.key); }, { signal });
        link.addEventListener('mouseenter', () => { setHighlight(hotspot.key); focusOn(hotspot.key); }, { signal });
        link.addEventListener('blur', () => setHighlight(null), { signal });
        link.addEventListener('mouseleave', () => setHighlight(null), { signal });
        link.addEventListener('click', (event) => { event.preventDefault(); activate(hotspot.key); }, { signal });
        item.append(link);
        list.append(item);
    }
    body.append(slot, list);
    dialog.append(bar, body);
    document.body.append(dialog);

    let opener: HTMLElement | null = null;

    function activate(key: string): void {
        const target = pageLinks.get(key);
        if (dialogOpen) {
            dialog.close();
        }
        target?.click();
    }

    const openDialog = (): void => {
        if (dialogOpen || typeof dialog.showModal !== 'function') {
            return;
        }
        opener = document.activeElement instanceof HTMLElement ? document.activeElement : enterButton;
        dialogOpen = true;
        slot.append(canvasHost);
        canvas.setAttribute('role', 'group');
        canvas.tabIndex = 0;
        document.documentElement.classList.add('is-room-open');
        dialog.showModal();
        host.dataset.roomFullscreen = 'true';
        resize();
        requestRender();
    };

    const closeDialog = (): void => {
        dialog.close();
    };

    dialog.addEventListener(
        'close',
        () => {
            dialogOpen = false;
            canvas.setAttribute('role', 'img');
            canvas.removeAttribute('tabindex');
            stage.prepend(canvasHost);
            document.documentElement.classList.remove('is-room-open');
            host.dataset.roomFullscreen = 'false';
            focusOn(null);
            setHighlight(null);
            resize();
            (opener && opener.isConnected ? opener : enterButton)?.focus();
            opener = null;
        },
        { signal },
    );
    // Piège à focus : Tab et Maj+Tab bouclent sur les éléments focalisables de la boîte de dialogue.
    dialog.addEventListener(
        'keydown',
        (event) => {
            if (event.key !== 'Tab') {
                return;
            }
            const focusable = [...dialog.querySelectorAll<HTMLElement>('button:not([disabled]), a[href], canvas[tabindex="0"]')];
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last?.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first?.focus();
            }
        },
        { signal },
    );
    closeButton.addEventListener('click', closeDialog, { signal });
    enterButton?.addEventListener('click', openDialog, { signal });

    if (controls) {
        controls.hidden = false;
    }
    if (typeof dialog.showModal !== 'function' && enterButton) {
        enterButton.hidden = true;
    }

    // --- Démarrage ----------------------------------------------------------------------------------------------------
    resize();
    applyCamera();
    updatePoses(1);
    avatar.ready();
    requestRender();

    return {
        dispose() {
            if (disposed) {
                return;
            }
            disposed = true;
            abort.abort();
            cancelAnimationFrame(raf);
            observer?.disconnect();
            resizeObserver?.disconnect();
            avatar.dispose();
            if (dialog.open) {
                dialog.close();
            }
            dialog.remove();
            document.documentElement.classList.remove('is-room-open');
            tooltip.remove();
            if (bubble) {
                bubble.hidden = true;
                stage.append(bubble);
            }
            if (controls) {
                controls.hidden = true;
            }
            stage.prepend(canvasHost);
            outline.geometry.dispose();
            (outline.material as Material).dispose();
            disposeRoom(room);
            renderer.dispose();
            renderer.forceContextLoss();
            canvas.remove();
            setRendering(false);
        },
    };
}
