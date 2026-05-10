<script setup lang="ts">
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { toast } from 'vue-sonner';
import { Spinner } from '@/components/ui/spinner';

import DeleteUser from '@/components/DeleteUser.vue';
import DevTools from '@/components/DevTools.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
    avatar_url?: string;
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Pengaturan profil',
        href: edit().url,
    },
];

const page = usePage();
const user = page.props.auth.user;

// Avatar upload state
const avatarInput = ref<HTMLInputElement | null>(null);
const avatarPreview = ref<string | null>(user.avatar_url || null);
const avatarFile = ref<File | null>(null);
const removeAvatar = ref(false);

// Form for profile update
const form = useForm({
    name: user.name,
    email: user.email,
    avatar: null as File | null,
    remove_avatar: false,
})

function handleAvatarChange(e: Event) {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        avatarFile.value = file;
        avatarPreview.value = URL.createObjectURL(file);
        form.avatar = file;
        removeAvatar.value = false;
        form.remove_avatar = false;
    }
}

function triggerAvatarUpload() {
    avatarInput.value?.click();
}

function deleteAvatar() {
    avatarPreview.value = null;
    avatarFile.value = null;
    form.avatar = null;
    removeAvatar.value = true;
    form.remove_avatar = true;
    if (avatarInput.value) {
        avatarInput.value.value = '';
    }
}

function submit() {
    const toastId = toast.loading('Menyimpan profil...')
    form.patch('/settings/profile', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            toast.dismiss(toastId)
            toast.success('Profil berhasil diperbarui')
            form.remove_avatar = false
            // Reset avatar file input
            form.avatar = null
            // Update avatar preview from page props after successful save
            if (page.props.auth?.user?.avatar_url) {
                avatarPreview.value = page.props.auth.user.avatar_url
            }
        },
        onError: (errors) => {
            toast.dismiss(toastId)
            const errorMessage = typeof errors === 'object' ? Object.values(errors).flat().join(', ') : errors.message || 'Terjadi kesalahan'
            toast.error('Gagal memperbarui profil', {
                description: errorMessage
            })
        }
    })
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Pengaturan profil" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    title="Informasi Profil"
                    description="Perbarui informasi profil Anda"
                />

                <form
                    @submit.prevent="submit"
                    class="space-y-6"
                >
                    <!-- Avatar Upload -->
                    <div class="flex flex-col gap-4">
                        <Label>Foto Profil</Label>
                        <div class="flex items-center gap-4">
                            <Avatar class="h-20 w-20">
                                <AvatarImage :src="avatarPreview || ''" :alt="user.name" />
                                <AvatarFallback class="text-2xl">{{ user.name.charAt(0).toUpperCase() }}</AvatarFallback>
                            </Avatar>
                            <div class="flex flex-col gap-2">
                                <div class="flex gap-2">
                                    <Button type="button" variant="outline" size="sm" @click="triggerAvatarUpload">
                                        {{ avatarPreview ? 'Ganti Foto' : 'Unggah Foto' }}
                                    </Button>
                                    <Button v-if="avatarPreview" type="button" variant="outline" size="sm" @click="deleteAvatar">
                                        Hapus
                                    </Button>
                                </div>
                                <p class="text-xs text-muted-foreground">Format: JPEG, PNG, JPG, WebP. Maks: 2MB</p>
                            </div>
                        </div>
                        <input
                            ref="avatarInput"
                            type="file"
                            accept="image/jpeg,image/png,image/jpg,image/webp"
                            class="hidden"
                            @change="handleAvatarChange"
                        />
                        <InputError :message="form.errors.avatar" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="name">Nama</Label>
                        <Input
                            id="name"
                            class="mt-1 block w-full"
                            v-model="form.name"
                            required
                            autocomplete="name"
                            placeholder="Nama lengkap"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="form.email"
                            required
                            autocomplete="username"
                            placeholder="Alamat email"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            Your email address is unverified.
                            <Link
                                :href="send()"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                Click here to resend the verification email.
                            </Link>
                        </p>

                        <div
                            v-if="status === 'verification-link-sent'"
                            class="mt-2 text-sm font-medium text-green-600"
                        >
                            A new verification link has been sent to your email
                            address.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            data-test="update-profile-button"
                        >
                            <Spinner v-if="form.processing" class="mr-2 size-4" />
                            Simpan
                        </Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="form.recentlySuccessful"
                                class="text-sm text-neutral-600"
                            >
                                Tersimpan.
                            </p>
                        </Transition>
                    </div>
                </form>
            </div>

            <DeleteUser />
            <DevTools />
        </SettingsLayout>
    </AppLayout>
</template>
