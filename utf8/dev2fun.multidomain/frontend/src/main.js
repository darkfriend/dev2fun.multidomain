import Vue from 'vue/dist/vue.js';
import mixins from './mixins/index';
// import VueDevMixins from 'vue-dev-mixins';
import VueBitrix from 'vue-bitrix';
// import App from './App';

// Vue.mixin(VueDevMixins);
Vue.use(VueBitrix);
Vue.use(mixins);

Vue.config.productionTip = false;
Vue.config.devtools = true;

document.addEventListener('DOMContentLoaded', function() {
    if (!document.querySelector("#dev2funMultiDomain")) {
        return false;
    }
    new Vue({
        // el: '#dev2funMultiDomain',
        components: {
            'app': () => import('./App'),
        },
        // render: h => h(App),
    }).$mount('#dev2funMultiDomain');
    // vm.$mount('#dev2funMultiDomain');
});


