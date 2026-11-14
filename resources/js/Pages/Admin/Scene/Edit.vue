<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { cloneProps } from '@admin/lib/clone';
import AdminLayout from '@admin/Layouts/AdminLayout.vue';

type Hotspot = {
  key: string; target: string; is_visible: boolean; sort_order: number;
  label_fr: string; label_en: string; description_fr: string; description_en: string;
};
type Scene = {
  enabled: boolean; asset_version: string; poster_path: string; mobile_fallback_path: string;
  greeting_fr: string; greeting_en: string; performance_mode: string; reduced_motion_mode: string; hotspots: Hotspot[];
};

const props = defineProps<{ scene: Scene; targets: string[] }>();
const form = useForm(cloneProps(props.scene));

const hotspotNames: Record<string, string> = {
  main_monitor: 'Écran principal', secondary_monitor: 'Second écran', bookshelf: 'Bibliothèque', certificates: 'Diplômes',
  calendar: 'Calendrier', kanban_board: 'Tableau Kanban', server_rack: 'Baie serveur', phone: 'Téléphone',
  airplane_model: 'Maquette d’avion', car_model: 'Voiture miniature', fishing_object: 'Canne à pêche',
};
const targetNames: Record<string, string> = {
  projets: 'Projets', activite: 'Activité', competences: 'Compétences', formation: 'Formation',
  experience: 'Parcours', contact: 'Contact', interets: 'Centres d’intérêt',
};

function move(index: number, direction: -1 | 1): void {
  const target = index + direction;
  if (target < 0 || target >= form.hotspots.length) return;
  [form.hotspots[index], form.hotspots[target]] = [form.hotspots[target], form.hotspots[index]];
  form.hotspots.forEach((hotspot, position) => { hotspot.sort_order = (position + 1) * 10; });
}

function save(): void {
  form
    .transform((data) => ({
      ...data,
      poster_path: data.poster_path || null,
      mobile_fallback_path: data.mobile_fallback_path || null,
      greeting_fr: data.greeting_fr || null,
      greeting_en: data.greeting_en || null,
      hotspots: data.hotspots.map((hotspot) => ({
        ...hotspot,
        label_fr: hotspot.label_fr || null, label_en: hotspot.label_en || null,
        description_fr: hotspot.description_fr || null, description_en: hotspot.description_en || null,
      })),
    }))
    .put('/dashboard/scene', { preserveScroll: true });
}
</script>

<template>
  <Head title="Scène 3D — Administration" />
  <AdminLayout>
    <header class="mb-8"><p class="text-sm font-semibold text-primary">PORT-010</p><h1 class="mt-1 text-3xl font-bold">Scène 3D</h1><p class="mt-2 max-w-3xl text-sm text-muted-foreground">Configurez l’atelier 3D facultatif. La géométrie n’est pas modifiable ici ; chaque objet garde un lien HTML équivalent.</p></header>
    <div v-if="Object.keys(form.errors).length" class="mb-6 rounded-xl border border-destructive/50 bg-destructive/10 p-4 text-sm text-destructive" role="alert"><p class="font-semibold">Certains champs sont invalides.</p><ul class="mt-2 list-disc space-y-1 pl-5"><li v-for="(message, path) in form.errors" :key="path"><span class="font-mono">{{ path }}</span> : {{ message }}</li></ul></div>
    <form class="space-y-6" :aria-busy="form.processing" @submit.prevent="save">
      <section class="rounded-2xl border border-border bg-surface p-5 shadow-card sm:p-6" aria-labelledby="scene-general">
        <h2 id="scene-general" class="text-xl font-bold">Général</h2>
        <div class="mt-5 grid gap-4 md:grid-cols-2">
          <label class="flex items-center gap-3 text-sm font-semibold md:col-span-2"><input v-model="form.enabled" type="checkbox" class="h-5 w-5"> Scène 3D activée</label>
          <label class="text-sm font-semibold">Version des assets<input v-model="form.asset_version" class="admin-input mt-2" required maxlength="40" :aria-invalid="Boolean(form.errors.asset_version)"><span v-if="form.errors.asset_version" class="mt-2 block text-destructive" role="alert">{{ form.errors.asset_version }}</span></label>
          <label class="text-sm font-semibold">Mode de performance
            <select v-model="form.performance_mode" class="admin-input mt-2"><option value="auto">Automatique</option><option value="standard">Standard</option><option value="low-power">Économe (30 images/s, résolution 1×)</option></select>
          </label>
          <label class="text-sm font-semibold">Mouvement réduit
            <select v-model="form.reduced_motion_mode" class="admin-input mt-2"><option value="respect">Suivre la préférence du visiteur</option><option value="always">Toujours limiter les animations</option></select>
          </label>
          <label class="text-sm font-semibold">Poster (chemin d’image du site)<input v-model="form.poster_path" class="admin-input mt-2" placeholder="/assets/room/poster.webp" maxlength="255" :aria-invalid="Boolean(form.errors.poster_path)"><span v-if="form.errors.poster_path" class="mt-2 block text-destructive" role="alert">{{ form.errors.poster_path }}</span></label>
          <label class="text-sm font-semibold">Repli mobile (chemin d’image du site)<input v-model="form.mobile_fallback_path" class="admin-input mt-2" placeholder="/assets/room/mobile.webp" maxlength="255" :aria-invalid="Boolean(form.errors.mobile_fallback_path)"><span v-if="form.errors.mobile_fallback_path" class="mt-2 block text-destructive" role="alert">{{ form.errors.mobile_fallback_path }}</span></label>
          <label class="text-sm font-semibold">Message d’accueil (FR)<textarea v-model="form.greeting_fr" class="admin-input mt-2 min-h-20" maxlength="280" /></label>
          <label class="text-sm font-semibold">Greeting (EN)<textarea v-model="form.greeting_en" class="admin-input mt-2 min-h-20" maxlength="280" /></label>
        </div>
      </section>
      <section class="rounded-2xl border border-border bg-surface p-5 shadow-card sm:p-6" aria-labelledby="scene-hotspots">
        <h2 id="scene-hotspots" class="text-xl font-bold">Objets interactifs</h2>
        <p class="mt-1 text-sm text-muted-foreground">Libellés vides : les textes par défaut sont utilisés.</p>
        <article v-for="(hotspot, index) in form.hotspots" :key="hotspot.key" class="mt-5 rounded-xl border border-border p-4" :aria-labelledby="`hotspot-${hotspot.key}`">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <h3 :id="`hotspot-${hotspot.key}`" class="font-bold">{{ index + 1 }}. {{ hotspotNames[hotspot.key] ?? hotspot.key }}</h3>
            <div class="flex gap-2">
              <button type="button" class="admin-link min-h-11 rounded-md border border-border px-3" :disabled="index === 0" :aria-label="`Monter ${hotspotNames[hotspot.key]}`" @click="move(index, -1)">↑</button>
              <button type="button" class="admin-link min-h-11 rounded-md border border-border px-3" :disabled="index === form.hotspots.length - 1" :aria-label="`Descendre ${hotspotNames[hotspot.key]}`" @click="move(index, 1)">↓</button>
            </div>
          </div>
          <div class="mt-4 grid gap-4 md:grid-cols-2">
            <label class="flex items-center gap-3 text-sm font-semibold"><input v-model="hotspot.is_visible" type="checkbox" class="h-5 w-5"> Visible</label>
            <label class="text-sm font-semibold">Section ciblée<select v-model="hotspot.target" class="admin-input mt-2"><option v-for="target in targets" :key="target" :value="target">{{ targetNames[target] ?? target }}</option></select></label>
            <label class="text-sm font-semibold">Libellé (FR)<input v-model="hotspot.label_fr" class="admin-input mt-2" maxlength="80"></label>
            <label class="text-sm font-semibold">Label (EN)<input v-model="hotspot.label_en" class="admin-input mt-2" maxlength="80"></label>
            <label class="text-sm font-semibold">Description (FR)<input v-model="hotspot.description_fr" class="admin-input mt-2" maxlength="280"></label>
            <label class="text-sm font-semibold">Description (EN)<input v-model="hotspot.description_en" class="admin-input mt-2" maxlength="280"></label>
          </div>
        </article>
      </section>
      <div class="sticky bottom-4 flex justify-end"><button type="submit" class="admin-button shadow-glow" :disabled="form.processing">{{ form.processing ? 'Enregistrement…' : 'Enregistrer la scène' }}</button></div>
    </form>
  </AdminLayout>
</template>
