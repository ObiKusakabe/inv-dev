import { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';
import type { Updater } from '@tanstack/vue-table';
// import { isRef } from 'vue';
import type { Ref } from 'vue';

export function valueUpdater<T>(
    updaterOrValue: Updater<T>,
    ref: Ref<T>,
) {
    if (typeof updaterOrValue === 'function') {
        ref.value = updaterOrValue(ref.value);
    } else {
        ref.value = updaterOrValue;
    }
}

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function urlIsActive(
    urlToCheck: NonNullable<InertiaLinkProps['href']>,
    currentUrl: string,
) {
    return toUrl(urlToCheck) === currentUrl;
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}
