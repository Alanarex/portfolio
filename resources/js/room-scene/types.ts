export interface RoomHotspot {
    key: string;
    label: string;
    description: string;
    target: string;
    href: string;
}

export interface RoomManifest {
    version: number;
    enabled: boolean;
    locale: string;
    assetVersion: string;
    poster: string | null;
    mobileFallback: string | null;
    greeting: string;
    performanceMode: 'auto' | 'standard' | 'low-power';
    reducedMotion: 'respect' | 'always';
    hotspots: RoomHotspot[];
    strings: {
        loading: string;
        unavailable: string;
        close: string;
        greet: string;
        lowPower: string;
    };
}

export interface RoomRuntimeOptions {
    /** Aucune boucle de rendu continue ni animation de transition. */
    reducedMotion: boolean;
    /** Résolution et cadence réduites, sans lissage. */
    lowPower: boolean;
    /** Appelé quand la scène ne peut plus fonctionner (contexte WebGL perdu, erreur de rendu). */
    onFailure: () => void;
}

export interface RoomController {
    dispose: () => void;
}
