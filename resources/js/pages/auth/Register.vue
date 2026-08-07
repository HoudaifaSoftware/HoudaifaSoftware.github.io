<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';

defineProps<{
    passwordRules: string;
    specialtySuggestions: string[];
    wilayas: { code: number; name: string }[];
}>();

defineOptions({
    layout: {
        title: 'Créer un nouveau cabinet',
        description:
            'Enregistrez votre cabinet. Il sera activé par notre équipe après vérification.',
    },
});
</script>

<template>
    <Head title="Créer un cabinet" />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="cabinet_name">Nom du cabinet</Label>
                <Input
                    id="cabinet_name"
                    type="text"
                    required
                    autofocus
                    :tabindex="1"
                    name="cabinet_name"
                    placeholder="Cabinet médical Benali"
                />
                <InputError :message="errors.cabinet_name" />
            </div>

            <div class="grid gap-2">
                <Label for="specialization">Spécialité médicale</Label>
                <Input
                    id="specialization"
                    type="text"
                    required
                    :tabindex="2"
                    name="specialization"
                    list="medical-specialties"
                    autocomplete="organization-title"
                    placeholder="Médecine générale"
                />
                <datalist id="medical-specialties">
                    <option
                        v-for="specialty in specialtySuggestions"
                        :key="specialty"
                        :value="specialty"
                    />
                </datalist>
                <InputError :message="errors.specialization" />
            </div>

            <div class="grid gap-2">
                <Label for="wilaya">Wilaya</Label>
                <select
                    id="wilaya"
                    name="wilaya"
                    required
                    :tabindex="3"
                    class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                >
                    <option value="" disabled selected>
                        Sélectionnez une wilaya
                    </option>
                    <option
                        v-for="wilaya in wilayas"
                        :key="wilaya.code"
                        :value="wilaya.code"
                    >
                        {{ String(wilaya.code).padStart(2, '0') }} -
                        {{ wilaya.name }}
                    </option>
                </select>
                <InputError :message="errors.wilaya" />
            </div>

            <div class="grid gap-2">
                <Label for="name">Votre nom complet</Label>
                <Input
                    id="name"
                    type="text"
                    required
                    :tabindex="4"
                    autocomplete="name"
                    name="name"
                    placeholder="Dr Nadia Benali"
                />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Adresse e-mail</Label>
                <Input
                    id="email"
                    type="email"
                    required
                    :tabindex="5"
                    autocomplete="email"
                    name="email"
                    placeholder="email@example.com"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="password">Mot de passe</Label>
                <PasswordInput
                    id="password"
                    required
                    :tabindex="6"
                    autocomplete="new-password"
                    name="password"
                    placeholder="Mot de passe"
                    :passwordrules="passwordRules"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">
                    Confirmer le mot de passe
                </Label>
                <PasswordInput
                    id="password_confirmation"
                    required
                    :tabindex="7"
                    autocomplete="new-password"
                    name="password_confirmation"
                    placeholder="Confirmer le mot de passe"
                    :passwordrules="passwordRules"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <Button
                type="submit"
                class="mt-2 w-full"
                tabindex="8"
                :disabled="processing"
                data-test="register-user-button"
            >
                <Spinner v-if="processing" />
                Créer le cabinet
            </Button>
        </div>

        <div class="text-center text-sm text-muted-foreground">
            Vous avez déjà un compte ?
            <TextLink
                :href="login()"
                class="underline underline-offset-4"
                :tabindex="9"
                >Se connecter</TextLink
            >
        </div>

        <div class="text-center text-sm text-muted-foreground">
            Vous rejoignez un cabinet existant ?
            <TextLink
                href="/join"
                class="underline underline-offset-4"
                :tabindex="10"
                >Demander l'accès</TextLink
            >
        </div>
    </Form>
</template>
