<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { usePage } from '@inertiajs/vue3';
import { Eye, EyeOff } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    form: any;
    name: string;
    label: string;
    type?: 'text' | 'password' | 'email' | 'date';
    placeholder?: string;
    validators?: any;
    spellcheck?: boolean;
    numberOnly?: boolean;
    maxlength?: number;
}>();

const showPassword = ref(false);
</script>

<template>
    <props.form.Field :name="name" :validators="validators">
        <template #default="{ field }">
            <div class="space-y-2">
                <Label :for="name">
                    {{ label }}
                </Label>

                <div class="relative">
                    <Input
                        :id="name"
                        :type="type === 'password' && showPassword ? 'text' : (type || 'text')"
                        :placeholder="placeholder"
                        :model-value="field.state.value"
                        :spellcheck="spellcheck ?? false"
                        :maxlength="maxlength"
                        @blur="field.handleBlur"
                        @input="
                            (e: Event) => {
                                let val = (e.target as HTMLInputElement).value;
                                if (numberOnly) {
                                    val = val.replace(/\D/g, '');
                                    (e.target as HTMLInputElement).value = val;
                                }
                                field.handleChange(val);
                            }
                        "
                    />
                    
                    <button
                        v-if="type === 'password'"
                        type="button"
                        class="absolute inset-y-0 end-0 flex items-center px-3 text-muted-foreground hover:text-foreground focus:outline-none"
                        @click="showPassword = !showPassword"
                        tabindex="-1"
                    >
                        <Eye v-if="!showPassword" class="h-4 w-4" />
                        <EyeOff v-else class="h-4 w-4" />
                    </button>
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
