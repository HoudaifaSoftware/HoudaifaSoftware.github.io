<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import TextLink from '@/components/TextLink.vue';
import { logout } from '@/routes';

defineProps<{
    cabinet: { name: string } | null;
}>();

defineOptions({
    layout: {
        title: 'Demande en attente d’approbation',
        description: 'Votre demande d’accès a bien été envoyée.',
    },
});
</script>

<template>
    <Head title="En attente d’approbation" />

    <div class="space-y-6 text-center">
        <p class="text-base font-medium">
            Votre demande est en attente d'approbation
        </p>

        <p class="text-sm text-muted-foreground">
            <template v-if="cabinet">
                Votre demande pour rejoindre le cabinet
                <span class="font-semibold">{{ cabinet.name }}</span>
                a été envoyée.
            </template>
            Le propriétaire du cabinet doit approuver votre compte et vous
            attribuer un rôle avant que vous puissiez accéder à l'application.
        </p>

        <TextLink :href="logout()" as="button" class="mx-auto block text-sm">
            Se déconnecter
        </TextLink>
    </div>
</template>
