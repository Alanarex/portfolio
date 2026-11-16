<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { cloneProps } from '@admin/lib/clone';
import ErrorSummary from '@admin/Components/ErrorSummary.vue';
import CollapsibleControls from '@admin/Components/CollapsibleControls.vue';
import CollapsibleSection from '@admin/Components/CollapsibleSection.vue';
import PageHeader from '@admin/Components/PageHeader.vue';
import ToggleSwitch from '@admin/Components/ToggleSwitch.vue';
import AdminLayout from '@admin/Layouts/AdminLayout.vue';
import { hasErrorUnder, provideSectionGroup } from '@admin/lib/collapsible';

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
provideSectionGroup('scene');

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
    <PageHeader class="mb-8" title="Scène 3D" description="Configurez l’atelier 3D facultatif. La géométrie n’est pas modifiable ici ; chaque objet garde un lien HTML équivalent." />
    <ErrorSummary :errors="form.errors" class="mb-6" />
    <form class="space-y-6" :aria-busy="form.processing" @submit.prevent="save">
      <CollapsibleControls />
      <CollapsibleSection id="general" title="Général" default-open :invalid="hasErrorUnder(form.errors, 'enabled', 'asset_version', 'performance_mode', 'reduced_motion_mode', 'poster_path', 'mobile_fallback_path', 'greeting_fr', 'greeting_en')">
        <div class="grid gap-4 md:grid-cols-2">
          <ToggleSwitch v-model="form.enabled" class="md:col-span-2">Scène 3D activée</ToggleSwitch>
          <label class="admin-field">Version des assets<input v-model="form.asset_version" class="admin-input" required maxlength="40" :aria-invalid="Boolean(form.errors.asset_version)"><span v-if="form.errors.asset_version" class="admin-error" role="alert">{{ form.errors.asset_version }}</span></label>
          <label class="admin-field">Mode de performance
            <select v-model="form.performance_mode" class="admin-input"><option value="auto">Automatique</option><option value="standard">Standard</option><option value="low-power">Économe (30 images/s, résolution 1×)</option></select>
          </label>
          <label class="admin-field">Mouvement réduit
            <select v-model="form.reduced_motion_mode" class="admin-input"><option value="respect">Suivre la préférence du visiteur</option><option value="always">Toujours limiter les animations</option></select>
          </label>
          <label class="admin-field">Poster (chemin d’image du site)<input v-model="form.poster_path" class="admin-input" placeholder="/assets/room/poster.webp" maxlength="255" :aria-invalid="Boolean(form.errors.poster_path)"><span v-if="form.errors.poster_path" class="admin-error" role="alert">{{ form.errors.poster_path }}</span></label>
          <label class="admin-field">Repli mobile (chemin d’image du site)<input v-model="form.mobile_fallback_path" class="admin-input" placeholder="/assets/room/mobile.webp" maxlength="255" :aria-invalid="Boolean(form.errors.mobile_fallback_path)"><span v-if="form.errors.mobile_fallback_path" class="admin-error" role="alert">{{ form.errors.mobile_fallback_path }}</span></label>
          <label class="admin-field">Message d’accueil (FR)<textarea v-model="form.greeting_fr" class="admin-input min-h-20" maxlength="280" /></label>
          <label class="admin-field">Message d’accueil (EN)<textarea v-model="form.greeting_en" class="admin-input min-h-20" maxlength="280" /></label>
        </div>
      </CollapsibleSection>
      <CollapsibleSection id="hotspots" title="Objets interactifs" description="Libellés vides : les textes par défaut sont utilisés." :summary="`${form.hotspots.length} objets`" :invalid="hasErrorUnder(form.errors, 'hotspots')">
        <CollapsibleSection
          v-for="(hotspot, index) in form.hotspots" :id="`hotspot-${hotspot.key}`" :key="hotspot.key" class="mt-4" variant="sub" :level="3"
          :title="`${index + 1}. ${hotspotNames[hotspot.key] ?? hotspot.key}`" :summary="hotspot.is_visible ? 'Visible' : 'Masqué'" :invalid="hasErrorUnder(form.errors, `hotspots.${index}`)"
        >
          <template #actions>
            <div class="flex gap-2">
              <button type="button" class="admin-button admin-button-secondary admin-button-icon" :disabled="index === 0" :aria-label="`Monter ${hotspotNames[hotspot.key]}`" @click="move(index, -1)">↑</button>
              <button type="button" class="admin-button admin-button-secondary admin-button-icon" :disabled="index === form.hotspots.length - 1" :aria-label="`Descendre ${hotspotNames[hotspot.key]}`" @click="move(index, 1)">↓</button>
            </div>
          </template>
          <div class="grid gap-4 md:grid-cols-2">
            <ToggleSwitch v-model="hotspot.is_visible">Visible</ToggleSwitch>
            <label class="admin-field">Section ciblée<select v-model="hotspot.target" class="admin-input"><option v-for="target in targets" :key="target" :value="target">{{ targetNames[target] ?? target }}</option></select></label>
            <label class="admin-field">Libellé (FR)<input v-model="hotspot.label_fr" class="admin-input" maxlength="80"></label>
            <label class="admin-field">Libellé (EN)<input v-model="hotspot.label_en" class="admin-input" maxlength="80"></label>
            <label class="admin-field">Description (FR)<input v-model="hotspot.description_fr" class="admin-input" maxlength="280"></label>
            <label class="admin-field">Description (EN)<input v-model="hotspot.description_en" class="admin-input" maxlength="280"></label>
          </div>
        </CollapsibleSection>
      </CollapsibleSection>
      <div class="admin-savebar"><button type="submit" class="admin-button shadow-glow" :disabled="form.processing">{{ form.processing ? 'Enregistrement…' : 'Enregistrer la scène' }}</button></div>
    </form>
  </AdminLayout>
</template>
