<script setup lang="ts">
import { Label } from '@/components/ui/label';
import { usePage } from '@inertiajs/vue3';

const props = defineProps<{
    form: any;
    name: string;
    label: string;
    placeholder?: string;
    rows?: number;
    validators?: any;
    spellcheck?: boolean;
    errorKey?: string;
}>();
</script>

<template>
    <props.form.Field :name="name" :validators="validators">
        <template #default="{ field }">
            <div class="space-y-2">
                <Label :for="name">
                    {{ label }}
                </Label>

                <textarea
                    :id="name"
                    :rows="rows ?? 4"
                    class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]"
                    :placeholder="placeholder"
                    :spellcheck="spellcheck ?? false"
                    :value="field.state.value"
                    @blur="field.handleBlur"
                    @input="(e: Event) => field.handleChange((e.target as HTMLTextAreaElement).value)"
                />

                <p v-if="field.state.meta.errors.length" class="mt-1 text-xs text-destructive">
                    {{ field.state.meta.errors.join(', ') }}
                </p>
                <p v-else-if="usePage().props.errors?.[errorKey || name]" class="mt-1 text-xs text-destructive">
                    {{ usePage().props.errors[errorKey || name] }}
                </p>
            </div>
        </template>
    </props.form.Field>
</template>
