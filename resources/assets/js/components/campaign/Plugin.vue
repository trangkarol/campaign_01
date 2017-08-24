<template>
    <div class="btn-group bootstrap-select show-tick form-control style-2">
        <div class="dropdown-menu open" role="combobox">
            <div class="searchbox">
                <input
                    type="text"
                    v-model="search"
                    class="form-control"
                    role="textbox"
                    :placeholder="$t('messages.search')"
                    @input="searchMembers">
            </div>
            <ul class="dropdown-menu scroll-member inner" role="listbox" aria-expanded="false">
                <li class="member" v-for="member in members.data">
                    <router-link :to="{ name: 'user.timeline', params: { slug: member.id }}">
                        <span class="text">
                            <div class="inline-items">
                                <div class="author-thumb">
                                    <img :src="member.image_thumbnail" :alt="member.name">
                                </div>
                                <div class="h6 author-title">{{ member.name }}</div>
                            </div>
                        </span>
                        <span class="glyphicon glyphicon-ok check-mark"></span>
                    </router-link>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions } from 'vuex'

export default {
    data() {
        return {
            search: '',
            members: []
        }
    },
    created() {
        this.searchMember({
            campaignId: this.pageId,
            status: 1,
            search: '',
            roleId: 0,
            pageNumberEvent: 1,
            pageCurrent: 0
        })
        .then(member => {
            this.members = []
            this.members = member.members
        })
        .catch(err => {
            //
        })
    },
    computed: {
        ...mapState('campaign', [
            'listMembers'
        ]),
    },
    methods: {
        ...mapActions('campaign', [
            'loadMoreMembers',
            'searchMember'
        ]),
        searchMembers: _.debounce(function (e) {
            e.preventDefault()
            this.searchMember({
                campaignId: this.pageId,
                status: 1,
                search: this.search,
                roleId: 0,
                pageNumberEvent: 1,
                pageCurrent: 0
            })
            .then(data => {
                this.members = []
                this.members = data.members
            })
            .catch(err => {
                //
            })
        }, 100),
    },
    mounted() {
        $(this.$el).selectpicker()
        $('.scroll-member').scroll(() => {
            if ($('.scroll-member').scrollTop() + $('.scroll-member').innerHeight() >= $('.scroll-member')[0].scrollHeight) {
                this.searchMember({
                    campaignId: this.pageId,
                    status: 1,
                    search: this.search,
                    pageNumberEvent: this.members.last_page,
                    pageCurrent: this.members.current_page
                })
                .then(member => {
                    let list_members = this.members
                    member.data = [...list_members.data, ...member.data]
                    this.members = []
                    this.members = member
                })
                .catch(err => {
                    //
                })
            }
        })
    }
}
</script>

<style lang="scss">
.bootstrap-select {
    &.btn-group {
        .dropdown-menu {
            &.inner {
                max-height: 490px !important;
                .member {
                    height: 34px;
                    margin: 12px 0;
                    .author-thumb {
                        img {
                            height: 34px;
                        }
                    }
                }
            }
        }
    }
}
</style>
