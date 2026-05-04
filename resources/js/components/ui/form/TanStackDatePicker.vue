<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';
import { usePage } from '@inertiajs/vue3';
import { DateFormatter, getLocalTimeZone, parseDate, type DateValue } from '@internationalized/date';
import {
    DatePickerCalendar,
    DatePickerCell,
    DatePickerCellTrigger,
    DatePickerContent,
    DatePickerGrid,
    DatePickerGridBody,
    DatePickerGridHead,
    DatePickerGridRow,
    DatePickerHeadCell,
    DatePickerHeader,
    DatePickerHeading,
    DatePickerNext,
    DatePickerPrev,
    DatePickerRoot,
    DatePickerTrigger,
} from 'reka-ui';
import { CalendarDays, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    form: any;
    name: string;
    label: string;
    placeholder?: string;
    validators?: any;
    errorKey?: string;
    locale?: string;
    closeOnSelect?: boolean;
    min?: string;
    max?: string;
}>();

const displayFormatter = computed(
    () =>
        new DateFormatter(props.locale ?? 'id-ID', {
            dateStyle: 'long',
        }),
);

const parseDateString = (value: string | null | undefined): DateValue | undefined => {
    if (!value) {
        return undefined;
    }

    try {
        return parseDate(value);
    } catch {
        return undefined;
    }
};

const formatDateLabel = (value: string | null | undefined): string => {
    const parsed = parseDateString(value);
    if (!parsed) {
        return props.placeholder ?? 'Pilih tanggal';
    }

    return displayFormatter.value.format(parsed.toDate(getLocalTimeZone()));
};
</script>

<template>
    <props.form.Field :name="name" :validators="validators">
        <template #default="{ field }">
            <div class="space-y-2">
                <Label :for="name">
                    {{ label }}
                </Label>

                <DatePickerRoot
                    :id="name"
                    :model-value="parseDateString(field.state.value)"
                    :min-value="parseDateString(min)"
                    :max-value="parseDateString(max)"
                    :close-on-select="closeOnSelect ?? true"
                    @update:model-value="(date) => field.handleChange(date ? date.toString() : '')"
                >
                    <DatePickerTrigger as-child>
                        <Button
                            type="button"
                            variant="outline"
                            :class="
                                cn(
                                    'w-full justify-start text-left font-normal cursor-pointer',
                                    !field.state.value && 'text-muted-foreground',
                                )
                            "
                        >
                            <CalendarDays class="mr-2 h-4 w-4" />
                            <span>{{ formatDateLabel(field.state.value) }}</span>
                        </Button>
                    </DatePickerTrigger>

                    <DatePickerContent class="z-50 w-auto rounded-md border bg-popover p-3 text-popover-foreground shadow-md">
                        <DatePickerCalendar v-slot="{ weekDays, grid }">
                            <DatePickerHeader class="mb-2 flex items-center justify-between gap-1">
                                <DatePickerPrev as-child>
                                    <Button type="button" variant="outline" size="icon" class="h-7 w-7 p-0 cursor-pointer">
                                        <ChevronLeft class="h-4 w-4" />
                                    </Button>
                                </DatePickerPrev>

                                <DatePickerHeading class="text-sm font-medium" />

                                <DatePickerNext as-child>
                                    <Button type="button" variant="outline" size="icon" class="h-7 w-7 p-0 cursor-pointer">
                                        <ChevronRight class="h-4 w-4" />
                                    </Button>
                                </DatePickerNext>
                            </DatePickerHeader>

                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                                <DatePickerGrid v-for="month in grid" :key="month.value.toString()" class="w-full border-collapse space-y-1">
                                    <DatePickerGridHead>
                                        <DatePickerGridRow class="flex">
                                            <DatePickerHeadCell
                                                v-for="day in weekDays"
                                                :key="day"
                                                class="w-9 rounded-md text-[0.8rem] font-normal text-muted-foreground"
                                            >
                                                {{ day }}
                                            </DatePickerHeadCell>
                                        </DatePickerGridRow>
                                    </DatePickerGridHead>

                                    <DatePickerGridBody>
                                        <DatePickerGridRow v-for="(weekDates, index) in month.rows" :key="`week-${index}`" class="mt-2 flex w-full">
                                            <DatePickerCell v-for="weekDate in weekDates" :key="weekDate.toString()" :date="weekDate" class="p-0 text-center text-sm">
                                                <DatePickerCellTrigger
                                                    :day="weekDate"
                                                    :month="month.value"
                                                    class="flex h-9 w-9 items-center justify-center rounded-md text-sm transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring data-[selected]:bg-primary data-[selected]:text-primary-foreground data-[today]:border data-[today]:border-primary data-[outside-view]:text-muted-foreground/50 data-[outside-view]:opacity-50 data-[disabled]:pointer-events-none data-[disabled]:opacity-50"
                                                />
                                            </DatePickerCell>
                                        </DatePickerGridRow>
                                    </DatePickerGridBody>
                                </DatePickerGrid>
                            </div>
                        </DatePickerCalendar>
                    </DatePickerContent>
                </DatePickerRoot>

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
