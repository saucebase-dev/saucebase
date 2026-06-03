/// <reference types="vite/client" />

declare module '~icons/*' {
    import type { SVGProps } from 'react';
    const component: (props: SVGProps<SVGSVGElement>) => JSX.Element;
    export default component;
}
