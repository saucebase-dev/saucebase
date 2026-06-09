import { registerGlobalComponent } from '@/lib/globalComponents';
import '@modules/themes/resources/css/app.css';
import ThemePanel from './components/ThemePanel.vue';

export function setup() {
    registerGlobalComponent('top', ThemePanel);
}

export function afterMount() {}
