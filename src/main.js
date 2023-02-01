import Vue from 'vue'
import './plugins/axios'
import App from './App.vue'
import store from './store'

Vue.config.productionTip = false

window.addEventListener('load', () => {
  new Vue({
    store,
    render: h => h(App)
  }).$mount('#mapa-interativo')
})
