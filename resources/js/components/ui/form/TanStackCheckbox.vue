<script setup lang="ts">
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { usePage } from '@inertiajs/vue3';

const props = defineProps<{
    form: any;
    name: string;
    label: string;
    validators?: any;
}>();

defineEmits(['change']);
</script>

<template>
    <props.form.Field :name="name" :validators="validators">
        <template #default="{ field }">
            <div class="flex items-center space-x-2">
                <input
                    type="checkbox"
                    :id="name"
                    :checked="field.state.value"
                    @change="(e: any) => {
                        const val = e.target.checked;
                        console.log(`Native Checkbox ${name} changed to:`, val);
                        field.handleChange(val);
                        form.setFieldValue(name, val);
                    }"
                    class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500"
                />

                <Label :for="name" class="cursor-pointer text-sm font-normal">
                    {{ label }}
                </Label>

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
