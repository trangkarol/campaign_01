import { authGuard, guestGuard } from './middleware'
import Vue from 'vue'
import VueRouter from 'vue-router'
import store from '../store'
import { get } from '../helpers/api'
import { getUser } from './router'
// Register Components to router
import Campaign from '../components/campaign/Campaign.vue'
import TimelineCampaign from '../components/campaign/TimelineCampaign.vue'
import PhotoCampaign from '../components/campaign/photo/PhotoCampaign.vue'
import CampaignRelated from '../components/campaign/CampaignRelated.vue'
import CampaignAbout from '../components/campaign/CampaignAbout.vue'
import OwnerCampaign from '../components/campaign/owner/OwnerCampaign.vue'
import MemberRequest from '../components/campaign/owner/MemberRequest.vue'
import ListMember from '../components/campaign/owner/ListMember.vue'
import App from '../components/layout/App.vue'
import Friend from '../components/user/Friend.vue'
import MasterUser from '../components/user/MasterUser.vue'
import Timeline from '../components/user/Timeline.vue'
import Photo from '../components/user/Photo.vue'
import Video from '../components/user/Video.vue'
import About from '../components/user/About.vue'
import Auth from '../components/auth/Auth.vue'
import PasswordReset from '../components/auth/PasswordReset.vue'
import PasswordForm from '../components/auth/PasswordForm.vue'
import PasswordVerify from '../components/auth/PasswordVerify.vue'
import AddCampaign from '../components/campaign/AddCampaign.vue'
import CreateEvent from '../components/event/create/CreateEvent.vue'
import User from '../components/user/User.vue'
import Password from '../components/user/Password.vue'
import Profile from '../components/user/Profile.vue'
import Setting from '../components/user/Setting.vue'
import CampaignUser from '../components/user/CampaignUser.vue'
import HomeEvent from '../components/event/HomeEvent.vue'
import ListAction from '../components/event/ListAction.vue'
import DonationList from '../components/event/donation/DonationList.vue'
import DonationInfo from '../components/event/donation/DonationInfo.vue'
import DonationReceived from '../components/event/donation/DonationReceived.vue'
import DonationManage from '../components/event/donation/DonationManage.vue'
import Expense from '../components/event/expense/Expense.vue'
import ListExpense from '../components/event/expense/ListExpense.vue'
import CreateExpense from '../components/event/expense/CreateExpense.vue'
import UpdateExpense from '../components/event/expense/UpdateExpense.vue'
import CreateExpenseBuy from '../components/event/expense/CreateExpenseBuy.vue'

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
                    { path: 'friends', component: Friend, name: 'user.friends' },
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
                    { path: 'photo', component: PhotoCampaign, name: 'campaign.photo' },
                    { path: 'campaign-related', component: CampaignRelated, name: 'campaign.campaign_related' },
                    { path: 'campaign-about', component: CampaignAbout, name: 'campaign.about' },
                    {
                        path: 'owner-campaign',
                        component: OwnerCampaign,
                        name: 'campaign.owner',
                        children: [
                            { path: 'member-request', component: MemberRequest, name: 'campaign.member_request' },
                            { path: 'list-member', component: ListMember, name: 'campaign.list_member' }
                        ]
                    }
                ]
            },
            {
                path: 'event/:event_id',
                component: HomeEvent,
                children: [
                    { path: 'detail', component: ListAction, name: 'event.index' },
                    { path: 'donation', component: DonationList, name: 'event.donation' },
                    {
                        path: 'donation/:id',
                        component: DonationInfo,
                        children: [
                            { path: '', component: DonationReceived, name: 'donation.received' },
                            { path: 'manage', component: DonationManage, name: 'donation.manage' }
                        ]
                    },
                    { 
                        path: 'expense',
                        component: Expense,
                        name: 'event.expense',
                        children: [
                            { path: 'list', component: ListExpense, name: 'expense.list' },
                            { path: 'create', component: CreateExpense, name: 'expense.create' },
                            { path: 'buy/create', component: CreateExpenseBuy, name: 'expense.create.buy' },
                            { path: 'update/:id', component: UpdateExpense, name: 'expense.update' }
                        ]
                    }
                ]
            }
        ]
    }]),

    ...guestGuard([
        { path: '/register', component: Auth, name: 'register' },
        { path: '/login', component: Auth, name: 'login' },
        {
            path: '/password',
            component: PasswordReset,
            children: [
                { path: 'reset', component: PasswordForm, name: 'password.reset'},
                { path: 'reset/:token', component: PasswordVerify }
            ]
        },
    ])
]

Vue.use(VueRouter)

export default router
