import { authGuard, guestGuard } from './middleware'
import Vue from 'vue'
import VueRouter from 'vue-router'
import store from '../store'
import { get } from '../helpers/api'
import { getUser } from './router'
// Register Components to router
import Campaign from '../components/campaign/Campaign.vue'
import TimelineCampaign from '../components/campaign/TimelineCampaign.vue'
import PhotoCampaign from '../components/campaign/Photo/PhotoCampaign.vue'
import App from '../components/layout/App.vue'
import Followers from '../components/user/Followers.vue'
import MasterUser from '../components/user/MasterUser.vue'
import Timeline from '../components/user/Timeline.vue'
import Photo from '../components/user/Photo.vue'
import Video from '../components/user/Video.vue'
import About from '../components/user/About.vue'
import Auth from '../components/auth/Auth.vue'
import AddCampaign from '../components/campaign/AddCampaign.vue'
import CreateEvent from '../components/event/create/CreateEvent.vue'
import User from '../components/user/User.vue'
import Password from '../components/user/Password.vue'
import Profile from '../components/user/Profile.vue'
import Setting from '../components/user/Setting.vue'
import CampaignUser from '../components/user/CampaignUser.vue'
import HomeEvent from '../components/event/HomeEvent.vue'
import GetLocation from '../components/google_map/GetLocation.vue'

const router = [
    ...authGuard([{
        path: '/',
        component: App,
        children: [
            { path: '/campaign', component: Campaign, name: 'campaign.index' },
            { path: '/campaign/create', component: AddCampaign, name: 'campaign.create' },
            { path: '/event/create/:campaign_id', component: CreateEvent, name: 'event.create' },
            {
                path: '/settings',
                component: User,
                children: [
                    { path: '', redirect: '/settings/settings', name: 'setting' },
                    { path: 'password', component: Password, name: 'setting.password' },
                    { path: 'profile', component: Profile, name: 'setting.profile' },
                    { path: 'settings', component: Setting, name: 'setting.setting' },
                ]
            },
            {
                path: 'user/:id',
                component: MasterUser,
                name: 'user',
                children: [
                    { path: '', component: Timeline, name: 'user.timeline' },
                    { path: 'followers', component: Followers, name: 'user.followers' },
                    { path: 'followings', component: Followers, name: 'user.followings' },
                    { path: 'photo', component: Photo, name: 'user.photo' },
                    { path: 'about', component: About, name: 'user.about' },
                    { path: 'video', component: Video, name: 'user.video' },
                    { path: ':path', component: CampaignUser, name: 'user.campaign' },
                ]
            },
            {
                path: 'campaign/:id',
                component: Campaign,
                children: [
                    { path: 'timeline', component: TimelineCampaign, name: 'campaign.timeline' },
                    { path: 'photo', component: PhotoCampaign, name: 'campaign.photo' }
                ]
            },
            { path: '/event/:event_id/index.html', component: HomeEvent, name: 'event.index' },
            { path: '/get-location', component: GetLocation },

        ]
    }]),

    ...guestGuard([
        { path: '/register', component: Auth, name: 'register' },
        { path: '/login', component: Auth, name: 'login' },
    ])
]

Vue.use(VueRouter)

export default router
