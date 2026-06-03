import type { ComponentType } from 'react';

const top: ComponentType[] = [];
const bottom: ComponentType[] = [];

export function registerGlobalComponent(
    position: 'top' | 'bottom',
    component: ComponentType,
): void {
    (position === 'top' ? top : bottom).push(component);
}

export function getGlobalComponents(
    position: 'top' | 'bottom',
): ComponentType[] {
    return position === 'top' ? top : bottom;
}
