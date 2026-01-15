<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import { logout } from '@/routes';
import { edit } from '@/routes/profile';
import type { User } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { BookOpen, LogOut, Settings, Phone } from 'lucide-vue-next';

interface Props {
    user: User;
}

const handleLogout = () => {
    router.flushAll();
};

defineProps<Props>();
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full" :href="edit()" prefetch as="button">
                <Settings class="mr-2 h-4 w-4" />
                Pengaturan
            </Link>
        </DropdownMenuItem>
        <DropdownMenuItem :as-child="true">
            <a
                class="flex w-full items-center"
                href="https://wa.me/089506495890?text=Halo%20Kak,%20Saya%20ingin%20meminta%20bantuan%20mengenai%20fitur%20..."
                target="_blank"
                rel="noopener noreferrer"
            >
                <Phone class="mr-2 h-4 w-4" />
                Kontak Bantuan
            </a>
        </DropdownMenuItem>
        <DropdownMenuItem :as-child="true">
            <a
                class="flex w-full items-center"
                href="https://github.com/ObiKusakabe/inv-dev"
                target="_blank"
                rel="noopener"
            >
                <BookOpen class="mr-2 h-4 w-4" />
                Panduan Web
            </a>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem :as-child="true">
        <Link
            class="block w-full"
            :href="logout()"
            @click="handleLogout"
            as="button"
            data-test="logout-button"
        >
            <LogOut class="mr-2 h-4 w-4" />
            Keluar
        </Link>
    </DropdownMenuItem>
</template>
