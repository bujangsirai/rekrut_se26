<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { usePage } from '@inertiajs/vue3';
import { Check, ChevronsUpDown, XCircle } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    form: any;
    name: string;
    label: string;
    placeholder?: string;
    options: any[]; // Can be string[] or {label: string, value: any}[]
    validators?: any;
    multiple?: boolean;
}>();

const open = ref(false);

const getOptionValue = (option: any) => {
    return typeof option === 'object' && option !== null ? option.value : option;
};

const getOptionLabel = (option: any) => {
    return typeof option === 'object' && option !== null ? option.label : option;
};

const getOptionByValue = (val: any) => {
    return props.options.find((o) => getOptionValue(o) === val);
};

const getLabelByValue = (val: any) => {
    const opt = getOptionByValue(val);
    return opt ? getOptionLabel(opt) : val;
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
                                <span v-if="!multiple && field.state.value" class="flex items-center gap-2 truncate">
                                    <component
                                        :is="getOptionByValue(field.state.value)?.icon"
                                        v-if="getOptionByValue(field.state.value)?.icon"
                                        :class="cn('h-4 w-4', getOptionByValue(field.state.value)?.iconClass)"
                                    />
                                    {{ getLabelByValue(field.state.value) }}
                                </span>
                                <span v-else-if="multiple && field.state.value && field.state.value.length > 0" class="truncate">
                                    {{ field.state.value.length }} opsi terpilih
                                </span>
                                <span v-else class="text-muted-foreground">
                                    {{ placeholder || 'Pilih...' }}
                                </span>
                                <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                            </Button>
                        </PopoverTrigger>
                        <!-- Using w-(--reka-popper-anchor-width) so the popover matches the button's width -->
                        <PopoverContent class="w-(--reka-popper-anchor-width) p-0 z-50">
                            <Command>
                                <CommandInput class="h-9" :placeholder="placeholder || 'Cari opsi...'" />
                                <CommandEmpty>Opsi tidak ditemukan.</CommandEmpty>
                                <CommandList class="max-h-48 group/list">
                                    <CommandGroup class="p-1">
                                        <CommandItem
                                            v-for="(option, idx) in options"
                                            :key="idx"
                                            :value="getOptionLabel(option)"
                                            class="data-[highlighted]:bg-primary/10 data-[highlighted]:text-primary group-hover/list:data-[highlighted]:bg-transparent group-hover/list:data-[highlighted]:text-foreground hover:!bg-primary/10 hover:!text-primary rounded-md transition-colors cursor-pointer"
                                            @select="(ev) => {
                                                const val = getOptionValue(option);
                                                if (multiple) {
                                                    const current = Array.isArray(field.state.value) ? field.state.value : [];
                                                    if (current.includes(val)) {
                                                        field.handleChange(current.filter((r: any) => r !== val));
                                                    } else {
                                                        field.handleChange([...current, val]);
                                                    }
                                                } else {
                                                    field.handleChange(val === field.state.value ? '' : val);
                                                    open = false;
                                                }
                                            }"
                                        >
                                            <div class="flex items-center gap-2">
                                                <component :is="option.icon" v-if="option.icon" :class="cn('h-4 w-4', option.iconClass)" />
                                                {{ getOptionLabel(option) }}
                                            </div>
                                            <Check
                                                class="ml-auto h-4 w-4 transition-opacity"
                                                :class="((multiple && Array.isArray(field.state.value) && field.state.value.includes(getOptionValue(option))) || (!multiple && field.state.value === getOptionValue(option))) ? 'opacity-100' : 'opacity-0'"
                                            />
                                        </CommandItem>
                                    </CommandGroup>
                                </CommandList>
                            </Command>
                        </PopoverContent>
                    </Popover>
                </div>

                <!-- Selected Badges untuk Combobox Multiple -->
                <div v-if="multiple && field.state.value && field.state.value.length > 0" class="mt-3 flex flex-wrap gap-2">
                    <div
                        v-for="val in field.state.value"
                        :key="val"
                        class="flex items-center gap-1.5 rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm"
                    >
                        <component
                            :is="getOptionByValue(val)?.icon"
                            v-if="getOptionByValue(val)?.icon"
                            :class="cn('h-3.5 w-3.5', getOptionByValue(val)?.iconClass)"
                        />
                        <span class="font-medium">{{ getLabelByValue(val) }}</span>
                        <button
                            type="button"
                            class="text-destructive hover:text-destructive/80 focus:outline-none"
                            @click="field.handleChange(field.state.value.filter((r: any) => r !== val))"
                        >
                            <XCircle class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <!-- FORMAT ERROR -->
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
