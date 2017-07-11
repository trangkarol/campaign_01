import { authGuard, guestGuard } from './middleware'
import Vue from 'vue'
import VueRouter from 'vue-router'
import store from '../store'
import { get } from '../helpers/api'
import { getUser } from './router'
// Register Components to router
import Campaign from '../components/campaign/Campaign.vue'
import TimelineCampaign from '../components/campaign/TimelineCampaign.vue'
import App from '../components/layout/App.vue'
import Followers from '../components/user/Followers.vue'
import MasterUser from '../components/user/MasterUser.vue'
import Timeline from '../components/user/Timeline.vue'
import Photo from '../components/user/Photo.vue'
import Video from '../components/user/Video.vue'
import About from '../components/user/About.vue'
import Auth from '../components/auth/Auth.vue'
import AddCampaign from '../components/campaign/AddCampaign.vue'
import CreateEvent from '../components/event/CreateEvent.vue'
import User from '../components/user/User.vue'
import Password from '../components/user/Password.vue'
import Profile from '../components/user/Profile.vue'
import Setting from '../components/user/Setting.vue'
import CampaignUser from '../components/user/CampaignUser.vue'

const router = [
    ...authGuard([{
        path: '/',
        component: App,
        children: [
            { path: '/campaign', component: Campaign },
            { path: '/campaign/create', component: AddCampaign },
            { path: '/event/create/:campaign_id', component: CreateEvent },
            {
                path: '/settings',
                component: User,
                children: [
                    { path: '', redirect: '/settings/settings' },
                    { path: 'password', component: Password },
                    { path: 'profile', component: Profile },
                    { path: 'settings', component: Setting },
                ]
            },
            {
                path: 'user/:id',
                name: 'user',
                component: MasterUser,
                children: [
                    { path: '', component: Timeline },
                    { path: 'followers', component: Followers },
                    { path: 'followings', component: Followers },
                    { path: 'photo', component: Photo },
                    { path: 'about', component: About },
                    { path: 'video', component: Video },
                    { path: ':path', component: CampaignUser },
                ]
            },
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
    }]),
    ...guestGuard([
        { path: '/register', component: Auth },
        { path: '/login', component: Auth },
    ])
]

Vue.use(VueRouter)

export default router
