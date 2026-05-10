<script setup lang="ts">
import type { LucideIcon } from 'lucide-vue-next'
import { ChevronRight } from 'lucide-vue-next'
import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible'
import Link from '@/components/Link.vue';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar'

const page = usePage();

const props = defineProps<{
    groups: {
        label: string;
        items: {
            title: string;
            href: string;
            icon?: LucideIcon;
            items?: {
                title: string;
                href: string;
            }[];
        }[];
    }[];
}>();

// Check if URL is active
const isUrlActive = (href: string): boolean => {
    const currentUrl = page.url;
    if (href === '/') {
        return currentUrl === '/';
    }
    return currentUrl.startsWith(href);
};

// Track which items have been expanded (for lazy loading)
const expandedItems = ref<Set<string>>(new Set());

const handleOpenChange = (itemTitle: string, isOpen: boolean) => {
    if (isOpen) {
        expandedItems.value.add(itemTitle);
    }
};
</script>

<template>
    <SidebarGroup v-for="group in props.groups" :key="group.label">
        <SidebarGroupLabel>{{ group.label }}</SidebarGroupLabel>
        <SidebarMenu>
            <template v-for="item in group.items" :key="item.title">
                <!-- Menu with submenu - use Collapsible -->
                <Collapsible
                    v-if="item.items"
                    as-child
                    :default-open="isUrlActive(item.href)"
                    class="group/collapsible"
                    @update:open="(isOpen) => handleOpenChange(item.title, isOpen)"
                >
                    <SidebarMenuItem>
                        <CollapsibleTrigger as-child>
                            <SidebarMenuButton 
                                :tooltip="item.title"
                                :is-active="isUrlActive(item.href)"
                            >
                                <component :is="item.icon" v-if="item.icon" />
                                <span class="group-data-[collapsible=icon]:hidden">{{ item.title }}</span>
                                <ChevronRight
                                    class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90"
                                />
                            </SidebarMenuButton>
                        </CollapsibleTrigger>
                        <CollapsibleContent>
                            <SidebarMenuSub v-if="expandedItems.has(item.title) || isUrlActive(item.href)">
                                <SidebarMenuSubItem v-for="subItem in item.items" :key="subItem.title">
                                    <SidebarMenuSubButton as-child>
                                        <a :href="subItem.href">
                                            <span>{{ subItem.title }}</span>
                                        </a>
                                    </SidebarMenuSubButton>
                                </SidebarMenuSubItem>
                            </SidebarMenuSub>
                        </CollapsibleContent>
                    </SidebarMenuItem>
                </Collapsible>
                
                <!-- Menu without submenu - use Link directly -->
                <SidebarMenuItem v-else>
                    <SidebarMenuButton 
                        as-child
                        :tooltip="item.title"
                        :is-active="isUrlActive(item.href)"
                    >
                        <Link :href="item.href">
                            <component :is="item.icon" v-if="item.icon" />
                            <span class="group-data-[collapsible=icon]:hidden">{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </template>
        </SidebarMenu>
    </SidebarGroup>
</template>
