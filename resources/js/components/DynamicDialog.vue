<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { buttonVariants } from '@/components/ui/button';
import { useDialog } from '@/composables/useDialog';

const { isOpen, options, resolve } = useDialog();
</script>

<template>
    <AlertDialog :open="isOpen">
        <AlertDialogContent>
            <div data-testid="confirm-dialog">
                <AlertDialogHeader>
                    <AlertDialogTitle>{{ options.title }}</AlertDialogTitle>
                    <AlertDialogDescription v-if="options.description">
                        {{ options.description }}
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter class="mt-4">
                    <AlertDialogCancel
                        data-testid="confirm-dialog-cancel"
                        @click="resolve(false)"
                    >
                        {{ options.cancelLabel ?? $t('Cancel') }}
                    </AlertDialogCancel>
                    <AlertDialogAction
                        :class="
                            buttonVariants({
                                variant: options.variant ?? 'default',
                            })
                        "
                        data-testid="confirm-dialog-confirm"
                        @click="resolve(true)"
                    >
                        {{ options.confirmLabel ?? $t('Confirm') }}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </div>
        </AlertDialogContent>
    </AlertDialog>
</template>
