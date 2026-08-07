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

defineOptions({
    layout: {
        title: 'Rejoindre un cabinet',
        description:
            'Demandez l’accès au cabinet de votre confrère en indiquant son adresse e-mail.',
    },
});
</script>

<template>
    <Head title="Rejoindre un cabinet" />

    <Form
        action="/join"
        method="post"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="name">Votre nom complet</Label>
                <Input
                    id="name"
                    type="text"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="name"
                    name="name"
                    placeholder="Dr Karim Haddad"
                />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Votre adresse e-mail</Label>
                <Input
                    id="email"
                    type="email"
                    required
                    :tabindex="2"
                    autocomplete="email"
                    name="email"
                    placeholder="vous@example.com"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="owner_email">
                    E-mail du propriétaire du cabinet
                </Label>
                <Input
                    id="owner_email"
                    type="email"
                    required
                    :tabindex="3"
                    name="owner_email"
                    placeholder="proprietaire@example.com"
                />
                <p class="text-xs text-muted-foreground">
                    L'adresse e-mail de l'administrateur qui a créé le cabinet.
                </p>
                <InputError :message="errors.owner_email" />
            </div>

            <div class="grid gap-2">
                <Label for="password">Mot de passe</Label>
                <PasswordInput
                    id="password"
                    required
                    :tabindex="4"
                    autocomplete="new-password"
                    name="password"
                    placeholder="Mot de passe"
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
                    :tabindex="5"
                    autocomplete="new-password"
                    name="password_confirmation"
                    placeholder="Confirmer le mot de passe"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <Button
                type="submit"
                class="mt-2 w-full"
                tabindex="6"
                :disabled="processing"
                data-test="join-cabinet-button"
            >
                <Spinner v-if="processing" />
                Envoyer ma demande
            </Button>
        </div>

        <div class="text-center text-sm text-muted-foreground">
            Vous avez déjà un compte ?
            <TextLink
                :href="login()"
                class="underline underline-offset-4"
                :tabindex="7"
                >Se connecter</TextLink
            >
        </div>
    </Form>
</template>
