<script setup lang="ts">
import { CircleChevronUp } from 'lucide-vue-next';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';

const showScrollTopButton = ref(false);

function updateScrollTopButtonVisibility(): void {
    showScrollTopButton.value = window.scrollY > 220;
}

function scrollToTop(): void {
    window.scrollTo({
        top: 0,
        behavior: 'smooth',
    });
}

onMounted(() => {
    updateScrollTopButtonVisibility();
    window.addEventListener('scroll', updateScrollTopButtonVisibility, { passive: true });
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', updateScrollTopButtonVisibility);
});
</script>

<template>
    <TooltipProvider :delay-duration="0">
        <Tooltip>
            <TooltipTrigger as-child>
                <Button
                    v-show="showScrollTopButton"
                    type="button"
                    size="icon"
                    class="fixed right-5 bottom-5 z-50 cursor-pointer rounded-full shadow-lg transition-all duration-300 hover:-translate-y-1 sm:right-7 sm:bottom-7"
                    aria-label="Kembali ke atas"
                    @click="scrollToTop"
                >
                    <CircleChevronUp class="size-5" />
                </Button>
            </TooltipTrigger>
            <TooltipContent
                side="left"
                class="border-transparent bg-black/80 text-white backdrop-blur-sm shadow-md"
            >
                Kembali Ke Atas
            </TooltipContent>
        </Tooltip>
    </TooltipProvider>
</template>
