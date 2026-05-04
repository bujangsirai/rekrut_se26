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
</script>

<template>
    <props.form.Field :name="name" :validators="validators">
        <template #default="{ field }">
            <div class="flex items-center space-x-2">
                <Checkbox
                    :id="name"
                    :checked="field.state.value"
                    @update:checked="(val: boolean) => field.handleChange(val)"
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
