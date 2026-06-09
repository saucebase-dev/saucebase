import { registerIcon } from '@/lib/navigation';

import '@modules/blog/resources/css/style.css';

import IconBlog from '~icons/heroicons/newspaper';

export function setup() {
    registerIcon('blog', IconBlog);
}

export function afterMount() {}
