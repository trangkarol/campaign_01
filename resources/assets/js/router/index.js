import Vue from 'vue'
import VueRouter from 'vue-router'
import store from '../store'
import {get } from '../helpers/api'
import { getUser } from './router'
// Register Components to router
import Campaign from '../components/campaign/Campaign.vue'
import TimelineCampaign from '../components/campaign/TimelineCampaign.vue'
import App from '../components/layout/App.vue'
import Auth from '../components/auth/Auth.vue'

Vue.use(VueRouter)

// Here register route
const router = new VueRouter({
    mode: 'history',
    routes: [
        { path: '/register', component: Auth },
        { path: '/login', component: Auth },
        {
            path: '/',
            component: App,
            children: [{
                path: 'campaign/:id',
                component: Campaign,
                children: [{
                    path: 'timeline',
                    component: TimelineCampaign
                }]
            }]
        }
    ]
})

router.beforeEach((to, from, next) => {
    const access_token = localStorage.getItem('access_token')

    // check access token exists within Api local storage
    if (!store.state.auth.user && access_token) {
        store.dispatch('auth/check')
            // get info user
        get(getUser).then((res) => {
            store.dispatch('auth/setUser', res.data)
            next()
        })
    } else {
        next()
    }

})

export default router