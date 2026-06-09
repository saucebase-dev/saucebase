import { cn } from '@/lib/utils';
import { Minus, Plus } from 'lucide-react';
import {
    createContext,
    useCallback,
    useContext,
    useId,
    type HTMLAttributes,
    type ReactNode,
} from 'react';

interface NumberFieldContextValue {
    value: number;
    min?: number;
    max?: number;
    step: number;
    disabled?: boolean;
    onChange: (value: number) => void;
    inputId: string;
}

const NumberFieldContext = createContext<NumberFieldContextValue | null>(null);

function useNumberField() {
    const ctx = useContext(NumberFieldContext);
    if (!ctx)
        throw new Error(
            'NumberField components must be used within <NumberField>',
        );
    return ctx;
}

interface NumberFieldProps extends HTMLAttributes<HTMLDivElement> {
    value?: number;
    defaultValue?: number;
    min?: number;
    max?: number;
    step?: number;
    disabled?: boolean;
    onValueChange?: (value: number) => void;
    children?: ReactNode;
}

export function NumberField({
    className,
    value,
    defaultValue = 0,
    min,
    max,
    step = 1,
    disabled,
    onValueChange,
    children,
    ...props
}: NumberFieldProps) {
    const inputId = useId();
    const controlled = value !== undefined;
    const currentValue = controlled ? value : defaultValue;

    const onChange = useCallback(
        (next: number) => {
            const clamped = min !== undefined ? Math.max(min, next) : next;
            const final = max !== undefined ? Math.min(max, clamped) : clamped;
            onValueChange?.(final);
        },
        [min, max, onValueChange],
    );

    return (
        <NumberFieldContext.Provider
            value={{
                value: currentValue,
                min,
                max,
                step,
                disabled,
                onChange,
                inputId,
            }}
        >
            <div className={cn('grid gap-1.5', className)} {...props}>
                {children}
            </div>
        </NumberFieldContext.Provider>
    );
}

export function NumberFieldContent({
    className,
    ...props
}: HTMLAttributes<HTMLDivElement>) {
    return (
        <div
            className={cn(
                'relative [&>[data-slot=input]]:has-[[data-slot=decrement]]:pl-5 [&>[data-slot=input]]:has-[[data-slot=increment]]:pr-5',
                className,
            )}
            {...props}
        />
    );
}

export function NumberFieldInput({
    className,
    ...props
}: HTMLAttributes<HTMLInputElement>) {
    const { value, min, max, step, disabled, onChange, inputId } =
        useNumberField();
    return (
        <input
            id={inputId}
            type="number"
            data-slot="input"
            min={min}
            max={max}
            step={step}
            value={value}
            disabled={disabled}
            onChange={(e) => onChange(parseFloat(e.target.value) || 0)}
            className={cn(
                'border-input placeholder:text-muted-foreground focus-visible:ring-ring flex h-9 w-full rounded-md border bg-transparent py-1 text-center text-sm shadow-sm transition-colors focus-visible:ring-1 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50',
                className,
            )}
            {...props}
        />
    );
}

export function NumberFieldDecrement({
    className,
    children,
    ...props
}: HTMLAttributes<HTMLButtonElement>) {
    const { value, min, step, disabled, onChange } = useNumberField();
    return (
        <button
            type="button"
            data-slot="decrement"
            disabled={disabled || (min !== undefined && value <= min)}
            onClick={() => onChange(value - step)}
            className={cn(
                'absolute top-1/2 left-0 -translate-y-1/2 p-3 disabled:cursor-not-allowed disabled:opacity-20',
                className,
            )}
            {...props}
        >
            {children ?? <Minus className="h-4 w-4" />}
        </button>
    );
}

export function NumberFieldIncrement({
    className,
    children,
    ...props
}: HTMLAttributes<HTMLButtonElement>) {
    const { value, max, step, disabled, onChange } = useNumberField();
    return (
        <button
            type="button"
            data-slot="increment"
            disabled={disabled || (max !== undefined && value >= max)}
            onClick={() => onChange(value + step)}
            className={cn(
                'absolute top-1/2 right-0 -translate-y-1/2 p-3 disabled:cursor-not-allowed disabled:opacity-20',
                className,
            )}
            {...props}
        >
            {children ?? <Plus className="h-4 w-4" />}
        </button>
    );
}
