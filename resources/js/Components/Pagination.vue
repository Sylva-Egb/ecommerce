<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: Array,
});
</script>

<template>
    <div class="flex items-center justify-between mt-6">
        <div class="flex-1 flex justify-between sm:hidden">
            <Link
                v-if="links.prev"
                :href="links.prev"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
                Précédent
            </Link>
            <Link
                v-if="links.next"
                :href="links.next"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
                Suivant
            </Link>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-center">
            <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <template v-for="(link, index) in links">
                        <Link
                            v-if="index === 0"
                            :href="link.url || '#'"
                            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                            :class="{ 'opacity-50 cursor-not-allowed': !link.url }"
                            aria-label="Previous"
                        >
                            <span class="sr-only">Previous</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </Link>
                        <Link
                            v-else-if="index === links.length - 1"
                            :href="link.url || '#'"
                            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                            :class="{ 'opacity-50 cursor-not-allowed': !link.url }"
                            aria-label="Next"
                        >
                            <span class="sr-only">Next</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </Link>
                        <Link
                            v-else
                            :href="link.url || '#'"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium"
                            :class="{
                                'z-10 bg-blue-50 border-blue-500 text-blue-600': link.active,
                                'text-gray-500 hover:bg-gray-50': !link.active && link.url,
                                'text-gray-300 cursor-not-allowed': !link.url
                            }"
                        >
                            {{ link.label.replace('&laquo;', '').replace('&raquo;', '').trim() }}
                        </Link>
                    </template>
                </nav>
            </div>
        </div>
    </div>
</template>