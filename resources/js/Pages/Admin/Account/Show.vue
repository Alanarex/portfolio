<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import CollapsibleControls from '@admin/Components/CollapsibleControls.vue';
import CollapsibleSection from '@admin/Components/CollapsibleSection.vue';
import ConfirmDialog from '@admin/Components/ConfirmDialog.vue';
import PageHeader from '@admin/Components/PageHeader.vue';
import AdminLayout from '@admin/Layouts/AdminLayout.vue';
import { hasErrorUnder, provideSectionGroup } from '@admin/lib/collapsible';
import { useConfirm } from '@admin/lib/confirm';

type SessionItem = { handle: string; browser: string; platform: string; device: string; ip: string | null; last_activity: string; current: boolean };
type LoginItem = { id: number; succeeded: boolean; ip: string | null; at: string; browser: string; platform: string };

const props = defineProps<{
  account: { name: string; email: string };
  password_min_length: number;
  sessions: { supported: boolean; items: SessionItem[] };
  logins: LoginItem[];
}>();

const messages = {
  fr: {
    title: 'Compte administrateur',
    description: 'Votre identité de connexion, votre mot de passe et les appareils connectés au tableau de bord.',
    profile: 'Profil', profileHint: 'Nom affiché et adresse e-mail de connexion.',
    name: 'Nom', email: 'Adresse e-mail de connexion',
    currentForEmail: 'Mot de passe actuel (requis pour changer l’adresse e-mail)',
    saveProfile: 'Enregistrer le profil', saving: 'Enregistrement…',
    password: 'Mot de passe', passwordHint: 'Le changement déconnecte tous les autres appareils.',
    current: 'Mot de passe actuel', next: 'Nouveau mot de passe', confirm: 'Confirmer le nouveau mot de passe',
    strength: 'Robustesse', weak: 'Faible', average: 'Moyenne', strong: 'Forte',
    rules: 'Critères', ruleLength: (n: number) => `${n} caractères minimum`, ruleLower: 'Une minuscule', ruleUpper: 'Une majuscule', ruleDigit: 'Un chiffre', ruleSymbol: 'Un symbole',
    met: 'respecté', unmet: 'à respecter',
    savePassword: 'Changer le mot de passe',
    sessions: 'Sessions et appareils', sessionsHint: 'Appareils actuellement connectés à ce compte.',
    thisDevice: 'Cet appareil', lastActivity: 'Dernière activité', unknownDevice: 'Appareil inconnu',
    signOut: 'Déconnecter cette session', signOutOthers: 'Déconnecter toutes les autres',
    noOther: 'Aucune autre session active.',
    unsupported: 'La liste détaillée des sessions n’est pas disponible avec le pilote de session actuel. Vous pouvez tout de même déconnecter tous les autres appareils.',
    confirmOneTitle: 'Déconnecter cette session ?', confirmOneMessage: 'Cet appareil devra se reconnecter.',
    confirmAllTitle: 'Déconnecter tous les autres appareils ?', confirmAllMessage: 'Seul l’appareil actuel restera connecté.',
    confirmLabel: 'Déconnecter',
    security: 'Sécurité', securityHint: 'Dix dernières tentatives de connexion (adresse IP masquée en partie).',
    succeeded: 'Réussie', failed: 'Échec', date: 'Date', ip: 'Adresse IP', result: 'Résultat', device: 'Appareil', empty: 'Aucune tentative enregistrée.',
    errorsTitle: 'Certains champs sont invalides.',
    device_mobile: 'Mobile', device_tablet: 'Tablette', device_desktop: 'Ordinateur',
  },
  en: {
    title: 'Administrator account',
    description: 'Your sign-in identity, your password and the devices signed in to the dashboard.',
    profile: 'Profile', profileHint: 'Display name and sign-in email address.',
    name: 'Name', email: 'Sign-in email address',
    currentForEmail: 'Current password (required to change the email address)',
    saveProfile: 'Save profile', saving: 'Saving…',
    password: 'Password', passwordHint: 'Changing it signs out every other device.',
    current: 'Current password', next: 'New password', confirm: 'Confirm new password',
    strength: 'Strength', weak: 'Weak', average: 'Fair', strong: 'Strong',
    rules: 'Requirements', ruleLength: (n: number) => `At least ${n} characters`, ruleLower: 'A lowercase letter', ruleUpper: 'An uppercase letter', ruleDigit: 'A number', ruleSymbol: 'A symbol',
    met: 'met', unmet: 'not met',
    savePassword: 'Change password',
    sessions: 'Sessions and devices', sessionsHint: 'Devices currently signed in to this account.',
    thisDevice: 'This device', lastActivity: 'Last activity', unknownDevice: 'Unknown device',
    signOut: 'Sign out this session', signOutOthers: 'Sign out all others',
    noOther: 'No other active session.',
    unsupported: 'The detailed session list is not available with the current session driver. You can still sign out every other device.',
    confirmOneTitle: 'Sign out this session?', confirmOneMessage: 'That device will have to sign in again.',
    confirmAllTitle: 'Sign out all other devices?', confirmAllMessage: 'Only the current device will stay signed in.',
    confirmLabel: 'Sign out',
    security: 'Security', securityHint: 'Last ten sign-in attempts (IP address partly masked).',
    succeeded: 'Succeeded', failed: 'Failed', date: 'Date', ip: 'IP address', result: 'Result', device: 'Device', empty: 'No attempt recorded.',
    errorsTitle: 'Some fields are invalid.',
    device_mobile: 'Mobile', device_tablet: 'Tablet', device_desktop: 'Computer',
  },
} as const;

const page = usePage<{ locale?: string }>();
const lang = computed(() => (page.props.locale === 'en' ? 'en' : 'fr'));
const t = computed(() => messages[lang.value]);

provideSectionGroup('account');
const confirm = useConfirm();

const profile = useForm({ name: props.account.name, email: props.account.email, current_password: '' });
const emailChanged = computed(() => profile.email.trim().toLowerCase() !== props.account.email.toLowerCase());

function saveProfile(): void {
  profile.put('/dashboard/account/profile', { preserveScroll: true, onSuccess: () => profile.reset('current_password') });
}

const passwordForm = useForm({ current_password: '', password: '', password_confirmation: '' });

const checks = computed(() => {
  const value = passwordForm.password;
  return [
    { key: 'length', label: t.value.ruleLength(props.password_min_length), ok: value.length >= props.password_min_length },
    { key: 'lower', label: t.value.ruleLower, ok: /\p{Ll}/u.test(value) },
    { key: 'upper', label: t.value.ruleUpper, ok: /\p{Lu}/u.test(value) },
    { key: 'digit', label: t.value.ruleDigit, ok: /\p{N}/u.test(value) },
    { key: 'symbol', label: t.value.ruleSymbol, ok: /[^\p{L}\p{N}]/u.test(value) },
  ];
});
const score = computed(() => checks.value.filter((check) => check.ok).length);
const strength = computed(() => {
  if (passwordForm.password === '') return { level: 0, label: '' };
  if (score.value === 5) return { level: 3, label: t.value.strong };
  return score.value >= 3 ? { level: 2, label: t.value.average } : { level: 1, label: t.value.weak };
});
const segmentClass = (index: number): string => {
  if (index > strength.value.level) return 'bg-border';
  return strength.value.level === 3 ? 'bg-success' : strength.value.level === 2 ? 'bg-warning' : 'bg-destructive';
};

function savePassword(): void {
  passwordForm.put('/dashboard/account/password', { preserveScroll: true, onFinish: () => passwordForm.reset() });
}

const formatter = computed(() => new Intl.DateTimeFormat(lang.value === 'en' ? 'en-GB' : 'fr-FR', { dateStyle: 'medium', timeStyle: 'short' }));
const when = (iso: string): string => formatter.value.format(new Date(iso));
const deviceLabel = (item: { browser: string; platform: string; device?: string }): string => {
  const name = [item.browser, item.platform].filter((part) => part !== '').join(' · ');
  return name === '' ? t.value.unknownDevice : name;
};
const deviceKind = (device: string): string => {
  const key = `device_${device}` as keyof typeof t.value;
  return key in t.value ? String(t.value[key]) : '';
};

const others = computed(() => props.sessions.items.filter((item) => !item.current));

function revokeOne(item: SessionItem): void {
  confirm.ask({
    title: t.value.confirmOneTitle,
    message: `${deviceLabel(item)} — ${t.value.confirmOneMessage}`,
    confirmLabel: t.value.confirmLabel,
    action: () => router.delete(`/dashboard/account/sessions/${item.handle}`, { preserveScroll: true }),
  });
}

function revokeOthers(): void {
  confirm.ask({
    title: t.value.confirmAllTitle,
    message: t.value.confirmAllMessage,
    confirmLabel: t.value.confirmLabel,
    action: () => router.delete('/dashboard/account/sessions', { preserveScroll: true }),
  });
}
</script>

<template>
  <Head :title="`${t.title} — Administration`" />
  <AdminLayout>
    <PageHeader class="mb-8" :title="t.title" :description="t.description" />

    <div class="space-y-6">
      <CollapsibleControls />

      <CollapsibleSection id="profile" :title="t.profile" :description="t.profileHint" default-open :invalid="hasErrorUnder(profile.errors, 'name', 'email', 'current_password')">
        <form :aria-busy="profile.processing" @submit.prevent="saveProfile">
          <div class="grid gap-5 md:grid-cols-2">
            <div>
              <label for="account-name" class="admin-label">{{ t.name }}</label>
              <input id="account-name" v-model="profile.name" class="admin-input" required maxlength="120" autocomplete="name" :aria-invalid="Boolean(profile.errors.name)" :aria-describedby="profile.errors.name ? 'account-name-error' : undefined">
              <p v-if="profile.errors.name" id="account-name-error" class="admin-error" role="alert">{{ profile.errors.name }}</p>
            </div>
            <div>
              <label for="account-email" class="admin-label">{{ t.email }}</label>
              <input id="account-email" v-model="profile.email" class="admin-input" type="email" required maxlength="255" autocomplete="username" :aria-invalid="Boolean(profile.errors.email)" :aria-describedby="profile.errors.email ? 'account-email-error' : undefined">
              <p v-if="profile.errors.email" id="account-email-error" class="admin-error" role="alert">{{ profile.errors.email }}</p>
            </div>
            <div v-if="emailChanged" class="md:col-span-2">
              <label for="account-email-password" class="admin-label">{{ t.currentForEmail }}</label>
              <input id="account-email-password" v-model="profile.current_password" class="admin-input" type="password" required maxlength="4096" autocomplete="current-password" :aria-invalid="Boolean(profile.errors.current_password)" :aria-describedby="profile.errors.current_password ? 'account-email-password-error' : undefined">
              <p v-if="profile.errors.current_password" id="account-email-password-error" class="admin-error" role="alert">{{ profile.errors.current_password }}</p>
            </div>
          </div>
          <div class="mt-6 flex justify-end">
            <button type="submit" class="admin-button" :disabled="profile.processing">{{ profile.processing ? t.saving : t.saveProfile }}</button>
          </div>
        </form>
      </CollapsibleSection>

      <CollapsibleSection id="password" :title="t.password" :description="t.passwordHint" :invalid="hasErrorUnder(passwordForm.errors, 'current_password', 'password', 'password_confirmation')">
        <form :aria-busy="passwordForm.processing" @submit.prevent="savePassword">
          <div class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
              <label for="account-current-password" class="admin-label">{{ t.current }}</label>
              <input id="account-current-password" v-model="passwordForm.current_password" class="admin-input" type="password" required maxlength="4096" autocomplete="current-password" :aria-invalid="Boolean(passwordForm.errors.current_password)" :aria-describedby="passwordForm.errors.current_password ? 'account-current-password-error' : undefined">
              <p v-if="passwordForm.errors.current_password" id="account-current-password-error" class="admin-error" role="alert">{{ passwordForm.errors.current_password }}</p>
            </div>
            <div>
              <label for="account-new-password" class="admin-label">{{ t.next }}</label>
              <input id="account-new-password" v-model="passwordForm.password" class="admin-input" type="password" required maxlength="4096" autocomplete="new-password" aria-describedby="account-password-rules" :aria-invalid="Boolean(passwordForm.errors.password)">
              <p v-if="passwordForm.errors.password" class="admin-error" role="alert">{{ passwordForm.errors.password }}</p>
            </div>
            <div>
              <label for="account-confirm-password" class="admin-label">{{ t.confirm }}</label>
              <input id="account-confirm-password" v-model="passwordForm.password_confirmation" class="admin-input" type="password" required maxlength="4096" autocomplete="new-password">
            </div>
            <div id="account-password-rules" class="md:col-span-2">
              <div class="flex items-center gap-3" aria-live="polite">
                <span class="text-sm font-semibold">{{ t.strength }}</span>
                <span class="grid h-2 w-40 grid-cols-3 gap-1" aria-hidden="true"><span v-for="index in 3" :key="index" class="rounded-full" :class="segmentClass(index)" /></span>
                <span class="text-sm text-muted-foreground">{{ strength.label }}</span>
              </div>
              <ul class="mt-3 grid gap-1 text-sm sm:grid-cols-2" :aria-label="t.rules">
                <li v-for="check in checks" :key="check.key" :class="check.ok ? 'text-success' : 'text-muted-foreground'">
                  <span aria-hidden="true">{{ check.ok ? '✓' : '○' }}</span> {{ check.label }}<span class="sr-only"> — {{ check.ok ? t.met : t.unmet }}</span>
                </li>
              </ul>
            </div>
          </div>
          <div class="mt-6 flex justify-end">
            <button type="submit" class="admin-button" :disabled="passwordForm.processing">{{ passwordForm.processing ? t.saving : t.savePassword }}</button>
          </div>
        </form>
      </CollapsibleSection>

      <CollapsibleSection id="sessions" :title="t.sessions" :description="t.sessionsHint" :summary="sessions.supported ? String(sessions.items.length) : undefined">
        <p v-if="!sessions.supported" class="text-sm text-muted-foreground">{{ t.unsupported }}</p>
        <ul v-else class="space-y-3">
          <li v-for="item in sessions.items" :key="item.handle" class="admin-subcard flex flex-wrap items-center justify-between gap-3">
            <div class="min-w-0">
              <p class="font-semibold">
                {{ deviceLabel(item) }}
                <span v-if="item.current" class="admin-badge ml-2">{{ t.thisDevice }}</span>
              </p>
              <p class="mt-1 text-xs text-muted-foreground">{{ [deviceKind(item.device), item.ip].filter((part) => part).join(' · ') }}</p>
              <p class="mt-1 text-xs text-muted-foreground">{{ t.lastActivity }} : <time :datetime="item.last_activity">{{ when(item.last_activity) }}</time></p>
            </div>
            <button v-if="!item.current" type="button" class="admin-button admin-button-secondary" @click="revokeOne(item)">{{ t.signOut }}</button>
          </li>
        </ul>
        <p v-if="sessions.supported && others.length === 0" class="mt-3 text-sm text-muted-foreground">{{ t.noOther }}</p>
        <div class="mt-5 flex justify-end">
          <button type="button" class="admin-button admin-button-danger" @click="revokeOthers">{{ t.signOutOthers }}</button>
        </div>
      </CollapsibleSection>

      <CollapsibleSection id="security" :title="t.security" :description="t.securityHint">
        <p v-if="logins.length === 0" class="text-sm text-muted-foreground">{{ t.empty }}</p>
        <ul v-else class="space-y-2">
          <li v-for="login in logins" :key="login.id" class="admin-subcard flex flex-wrap items-center justify-between gap-x-4 gap-y-1">
            <div class="min-w-0">
              <p class="text-sm font-semibold"><time :datetime="login.at">{{ when(login.at) }}</time></p>
              <p class="text-xs text-muted-foreground">{{ [deviceLabel(login), login.ip].filter((part) => part).join(' · ') }}</p>
            </div>
            <span class="admin-badge" :class="login.succeeded ? 'text-success' : 'text-destructive'">{{ login.succeeded ? t.succeeded : t.failed }}</span>
          </li>
        </ul>
      </CollapsibleSection>
    </div>

    <ConfirmDialog :open="confirm.request.value !== null" :title="confirm.request.value?.title ?? ''" :message="confirm.request.value?.message" :confirm-label="confirm.request.value?.confirmLabel" @confirm="confirm.accept" @cancel="confirm.dismiss" />
  </AdminLayout>
</template>
