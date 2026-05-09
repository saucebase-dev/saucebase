<script setup lang="ts">
import { Field, FieldError, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import InputPassword from './InputPassword.vue';

const props = withDefaults(
    defineProps<{
        name: string;
        type?: string;
        label?: string;
        placeholder?: string;
        id?: string;
        testId?: string;
    }>(),
    {
        type: 'text',
    },
);

defineOptions({ inheritAttrs: false });

// Error state
const page = usePage();
const errors = computed(() => page.props.errors || {});
const errorMessage = computed(() => errors.value[props.name]);
const isInvalid = computed(() => !!errorMessage.value);

// IDs for accessibility (canonical id prefers provided id, falls back to name)
const id = computed(() => props.id || props.name);
const labelId = computed(() => `${id.value}-label`);
const errorId = computed(() => `${id.value}-error`);
const describedBy = computed(() =>
    isInvalid.value ? errorId.value : undefined,
);

// Test IDs (fallback to name to avoid undefined attributes)
const testIdComputed = computed(() => props.testId || props.name);
const errorTestId = computed(() => `${testIdComputed.value}-error`);

// Dynamic component selection
const component = computed(() =>
    props.type === 'password' ? InputPassword : Input,
);

// Type prop handling (only pass to Input, not InputPassword)
const typeAttribute = computed(() =>
    props.type !== 'password' ? props.type : undefined,
);
</script>

<template>
    <Field :data-invalid="isInvalid">
        <FieldLabel v-if="label" :id="labelId" :for="id">
            {{ label }}
        </FieldLabel>
        <component
            :is="component"
            :id="id"
            :name="name"
            :type="typeAttribute"
            :data-testid="testIdComputed"
            :placeholder="placeholder"
            :aria-invalid="isInvalid"
            :aria-labelledby="labelId"
            :aria-describedby="describedBy"
            v-bind="$attrs"
        />
        <FieldError
            v-if="isInvalid"
            :id="errorId"
            :data-testid="errorTestId"
            aria-live="polite"
        >
            {{ errorMessage }}
        </FieldError>
    </Field>
</template>
