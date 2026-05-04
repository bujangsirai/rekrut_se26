<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandItem, CommandList } from '@/components/ui/command';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { usePage } from '@inertiajs/vue3';
import { Check, ChevronsUpDown } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    form: any;
    name: string;
    label: string;
    placeholder?: string;
    options: { label: string; value: any; icon?: any; iconClass?: string }[];
    validators?: any;
}>();

const open = ref(false);

const getOptionByValue = (val: any) => {
    return props.options.find((o) => o.value === val);
};
</script>

<template>
    <props.form.Field :name="name" :validators="validators">
        <template #default="{ field }">
            <div class="space-y-2">
                <Label :for="name">
                    {{ label }}
                </Label>

                <div class="block">
                    <Popover v-model:open="open">
                        <PopoverTrigger as-child>
                            <Button
                                variant="outline"
                                role="combobox"
                                :aria-expanded="open"
                                class="w-full justify-between font-normal cursor-pointer"
                            >
                                <span v-if="field.state.value" class="flex items-center gap-2 truncate">
                                    <component
                                        :is="getOptionByValue(field.state.value)?.icon"
                                        v-if="getOptionByValue(field.state.value)?.icon"
                                        :class="cn('h-4 w-4', getOptionByValue(field.state.value)?.iconClass)"
                                    />
                                    {{ getOptionByValue(field.state.value)?.label || field.state.value }}
                                </span>
                                <span v-else class="text-muted-foreground">
                                    {{ placeholder || 'Pilih...' }}
                                </span>
                                <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent class="w-(--reka-popper-anchor-width) p-0 z-50">
                            <Command>
                                <CommandList class="max-h-48 group/list">
                                    <CommandEmpty>Opsi tidak ditemukan.</CommandEmpty>
                                    <CommandGroup class="p-1">
                                        <CommandItem
                                            v-for="(option, idx) in options"
                                            :key="idx"
                                            :value="option.label"
                                            class="data-[highlighted]:bg-primary/10 data-[highlighted]:text-primary group-hover/list:data-[highlighted]:bg-transparent group-hover/list:data-[highlighted]:text-foreground hover:!bg-primary/10 hover:!text-primary rounded-md transition-colors cursor-pointer"
                                            @select="() => {
                                                field.handleChange(option.value === field.state.value ? '' : option.value);
                                                open = false;
                                            }"
                                        >
                                            <div class="flex items-center gap-2">
                                                <component :is="option.icon" v-if="option.icon" :class="cn('h-4 w-4', option.iconClass)" />
                                                {{ option.label }}
                                            </div>
                                            <Check
                                                class="ml-auto h-4 w-4 transition-opacity"
                                                :class="field.state.value === option.value ? 'opacity-100' : 'opacity-0'"
                                            />
                                        </CommandItem>
                                    </CommandGroup>
                                </CommandList>
                            </Command>
                        </PopoverContent>
                    </Popover>
                </div>

                <p v-if="field.state.meta.errors.length" class="mt-1 text-xs text-destructive">
                    {{ field.state.meta.errors.join(', ') }}
                </p>
                <p v-else-if="usePage().props.errors?.[name]" class="mt-1 text-xs text-destructive">
                    {{ usePage().props.errors[name] }}
                </p>
            </div>
        </template>
    </props.form.Field>
</template>
