<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    CalendarClock,
    Download,
    FileText,
    Stethoscope,
    Users,
    UsersRound,
} from '@lucide/vue';
import { computed } from 'vue';
import { dashboard, login, register } from '@/routes';

defineProps<{
    canRegister: boolean;
}>();

const page = usePage();
const desktopDownload = computed(() => page.props.desktopDownload);
</script>

<template>
    <Head title="Accueil" />

    <main
        class="relative flex min-h-screen items-center justify-center overflow-hidden bg-slate-950 px-5 py-10 text-white"
    >
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(14,165,233,0.22),transparent_36%),radial-gradient(circle_at_bottom_right,rgba(16,185,129,0.2),transparent_32%)]"
        />

        <section
            class="relative grid w-full max-w-5xl overflow-hidden rounded-3xl border border-white/10 bg-slate-900/90 shadow-2xl shadow-black/40 backdrop-blur lg:grid-cols-[1.15fr_0.85fr]"
        >
            <div class="p-8 sm:p-12 lg:p-14">
                <div
                    class="inline-flex items-center gap-3 rounded-2xl border border-sky-400/20 bg-sky-400/10 px-4 py-3"
                >
                    <span
                        class="flex size-10 items-center justify-center rounded-xl bg-sky-500 text-white shadow-lg shadow-sky-950/40"
                    >
                        <Stethoscope class="size-5" />
                    </span>
                    <div>
                        <p class="font-bold tracking-wide">MediSmart</p>
                        <p class="text-xs text-sky-100/70">
                            Gestion médicale locale et sécurisée
                        </p>
                    </div>
                </div>

                <h1
                    class="mt-8 max-w-xl text-3xl leading-tight font-black tracking-tight sm:text-5xl"
                >
                    La gestion complète de votre cabinet médical.
                </h1>
                <p class="mt-5 max-w-xl text-base leading-7 text-slate-300">
                    Patients, rendez-vous, prescriptions et sauvegardes réunis
                    dans une seule application. Multi-postes jusqu’à
                    3&nbsp;utilisateurs, disponible même sans Internet.
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="inline-flex h-12 items-center gap-2 rounded-xl bg-sky-500 px-5 font-bold text-white transition hover:bg-sky-400 focus-visible:ring-2 focus-visible:ring-sky-300 focus-visible:outline-none"
                    >
                        Ouvrir le cabinet
                        <ArrowRight class="size-4" />
                    </Link>
                    <template v-else>
                        <a
                            v-if="desktopDownload?.available && desktopDownload.url"
                            :href="desktopDownload.url"
                            class="inline-flex h-12 items-center gap-2 rounded-xl bg-emerald-500 px-5 font-bold text-white shadow-lg shadow-emerald-950/40 transition hover:bg-emerald-400 focus-visible:ring-2 focus-visible:ring-emerald-300 focus-visible:outline-none"
                        >
                            <Download class="size-4" />
                            Télécharger pour Windows
                        </a>
                        <button
                            v-else
                            type="button"
                            disabled
                            :title="desktopDownload?.reason ?? undefined"
                            class="inline-flex h-12 cursor-not-allowed items-center gap-2 rounded-xl bg-emerald-500/40 px-5 font-bold text-white/70"
                        >
                            <Download class="size-4" />
                            Télécharger pour Windows
                        </button>
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="inline-flex h-12 items-center gap-2 rounded-xl border border-white/20 bg-white/5 px-5 font-semibold text-white transition hover:bg-white/10 focus-visible:ring-2 focus-visible:ring-white/50 focus-visible:outline-none"
                        >
                            Créer un cabinet
                            <ArrowRight class="size-4" />
                        </Link>
                        <a
                            href="/join"
                            class="inline-flex h-12 items-center rounded-xl border border-white/20 bg-white/5 px-5 font-semibold text-white transition hover:bg-white/10 focus-visible:ring-2 focus-visible:ring-white/50 focus-visible:outline-none"
                        >
                            Rejoindre un cabinet
                        </a>
                    </template>
                </div>

                <p
                    v-if="!$page.props.auth.user"
                    class="mt-4 text-sm text-slate-400"
                >
                    Déjà un compte ?
                    <Link
                        :href="login()"
                        class="font-semibold text-sky-300 underline-offset-4 hover:underline"
                    >
                        Se connecter
                    </Link>
                </p>
            </div>

            <aside
                class="border-t border-white/10 bg-white/[0.035] p-8 sm:p-10 lg:border-t-0 lg:border-l"
            >
                <h2
                    class="text-sm font-bold tracking-[0.16em] text-sky-300 uppercase"
                >
                    Tout pour le cabinet
                </h2>
                <div class="mt-6 space-y-4">
                    <article
                        class="flex gap-4 rounded-2xl border border-white/10 bg-black/10 p-4"
                    >
                        <Users class="mt-0.5 size-5 shrink-0 text-sky-300" />
                        <div>
                            <h3 class="font-semibold">Dossiers patients</h3>
                            <p class="mt-1 text-sm leading-6 text-slate-400">
                                Fiches complètes, antécédents et documents
                                centralisés pour chaque patient.
                            </p>
                        </div>
                    </article>
                    <article
                        class="flex gap-4 rounded-2xl border border-white/10 bg-black/10 p-4"
                    >
                        <CalendarClock
                            class="mt-0.5 size-5 shrink-0 text-emerald-300"
                        />
                        <div>
                            <h3 class="font-semibold">Rendez-vous</h3>
                            <p class="mt-1 text-sm leading-6 text-slate-400">
                                Agenda des consultations, rappels et suivi du
                                planning du cabinet.
                            </p>
                        </div>
                    </article>
                    <article
                        class="flex gap-4 rounded-2xl border border-white/10 bg-black/10 p-4"
                    >
                        <FileText
                            class="mt-0.5 size-5 shrink-0 text-amber-300"
                        />
                        <div>
                            <h3 class="font-semibold">Prescriptions</h3>
                            <p class="mt-1 text-sm leading-6 text-slate-400">
                                Ordonnances et documents cliniques générés et
                                archivés en quelques clics.
                            </p>
                        </div>
                    </article>
                    <article
                        class="flex gap-4 rounded-2xl border border-white/10 bg-black/10 p-4"
                    >
                        <UsersRound
                            class="mt-0.5 size-5 shrink-0 text-violet-300"
                        />
                        <div>
                            <h3 class="font-semibold">
                                Multi-postes — 3 utilisateurs
                            </h3>
                            <p class="mt-1 text-sm leading-6 text-slate-400">
                                Travaillez à plusieurs sur le même cabinet,
                                jusqu’à trois comptes simultanés.
                            </p>
                        </div>
                    </article>
                </div>
            </aside>
        </section>
    </main>
</template>
