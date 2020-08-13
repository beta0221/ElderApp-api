/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');
import Vue from 'vue'
import Vuetify from 'vuetify'

Vue.use(Vuetify)


import CKEditor from '@ckeditor/ckeditor5-vue';
Vue.use(CKEditor)

import ClassicEditor from '@ckeditor/ckeditor5-build-classic'
window.ClassicEditor = ClassicEditor

import User from './Helpers/User'
window.User = User

import Exception from './Helpers/Exception'
window.Exception = Exception

window.EventBus = new Vue();
/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

Vue.component('app-home', require('./components/AppHome.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
import router from './router/router.js'
import Token from './Helpers/Token';
const app = new Vue({
    el: '#app',
    router,
});
