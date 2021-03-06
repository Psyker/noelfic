import Vue from 'vue'
import Vuex from 'vuex'
import './admin/material-dashboard/main.js'
import Argon from 'vue-argon-design-system/src/plugins/argon-kit'

import Chartist from 'chartist'
import PortalVue from 'portal-vue'
import i18n from './i18n'
import './routing'
import {
    AdminSidebar,
    Notification,

    DashboardPage,
    FictionEditPage,
    FictionAddPage
} from './admin/components/'
import {
    SortableTable
} from './components'
import { createProvider } from "./vue-apollo";
import store from './store'
import '../scss/admin.scss'

Vue.config.productionTip = false
Vue.config.devtools = process.env.NODE_ENV === "development"
Vue.config.debug = process.env.NODE_ENV === "development"
Vue.config.silent = process.env.NODE_ENV !== "development"

const components = {
    AdminSidebar,
    SortableTable,
    Notification,

    DashboardPage,
    FictionEditPage,
    FictionAddPage
}

Vue.use(Argon)
Vue.use(PortalVue)
Vue.use(Vuex)

new Vue({
    i18n,
    apolloProvider: createProvider(),
    store,
    el: '#admin',
    data: {
        Chartist
    },
    components
})

