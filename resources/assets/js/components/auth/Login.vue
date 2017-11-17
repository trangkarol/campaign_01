<template lang="html">
    <div class="tab-pane" :class="{ active: loginTab }" id="profile" role="tabpanel" data-mh="log-tab">
        <div class="title h6">{{ $t('form.title.login') }}</div>
        <form class="content" @submit.prevent="handlelogin">
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12">
                    <div class="form-group label-floating" :class="{ 'has-danger': errors.has('email')}">
                        <label class="control-label">{{ $t('form.label.email') }}</label>
                        <input data-vv-delay="300" v-validate="'required|email'"
                            name="email"
                            class="form-control"
                            placeholder=""
                            v-model="user.email">
                        <span v-show="errors.has('email')" class="material-input text-danger">
                            {{ errors.first('email') }}
                        </span>
                    </div>

                    <div class="form-group label-floating" :class="{ 'has-danger': errors.has('password')}">
                        <label class="control-label">{{ $t('form.label.password') }}</label>
                        <input class="form-control"
                            v-validate="'required|min:6|max:20'"
                            name="password"
                            type="password"
                            v-model="user.password">
                        <span v-show="errors.has('password')" class="material-input text-danger">
                            {{ errors.first('password') }}
                        </span>
                    </div>

                    <div class="remember">
                        <div class="checkbox clicked">
                            <label>
                                <input name="optionsCheckboxes" type="checkbox">
                                {{ $t('form.label.remember_me') }}
                            </label>
                        </div>
                        <router-link :to="{ name: 'password.reset' }" class="forgot">
                            {{ $t('form.label.forgot_password') }}
                        </router-link>
                    </div>

                    <button type="submit" class="btn btn-lg btn-primary full-width" :disabled="loading">
                        <i class="fa fa-spinner fa-spin" v-if="loading"></i> {{ $t('form.button.login') }}
                    </button>

                    <div class="or"></div>

                    <a href="#" @click.prevent="redirect('facebook')" class="btn btn-lg bg-facebook full-width btn-icon-left">
                        <i class="fa fa-facebook" aria-hidden="true"></i>
                        {{ $t('form.button.login_facebook') }}
                    </a>

                    <a href="#" @click.prevent="redirect('twitter')" class="btn btn-lg bg-twitter full-width btn-icon-left">
                        <i class="fa fa-twitter" aria-hidden="true"></i>
                        {{ $t('form.button.login_twitter') }}
                    </a>

                    <a href="#" @click.prevent="redirect('google')" class="btn btn-lg btn-google full-width btn-icon-left">
                        <i class="fa fa-google-plus" aria-hidden="true"></i>
                        {{ $t('form.button.login_google') }}
                    </a>

                    <a href="#" @click.prevent="redirect('framgia')" class="btn btn-lg btn-wsm full-width btn-icon-left">
                        <img src="/images/white_wsm.png" alt="Wsm" class="fa">
                        <span>
                            {{ $t('form.button.login_wsm') }}
                        </span>
                    </a>

                    <p>{{ $t('form.quote.confirm_account') }}
                        <a href="#" @click="$emit('handleTab', 'register')">
                            {{ $t('form.button.register') }}
                        </a>
                        {{ $t('form.quote.login_benefits') }}
                    </p>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import noty from '../../helpers/noty'

export default {
    data: () => ({
        user: {
            email: '',
            password: ''
        },
    }),
    computed: {
        ...mapState({
            loading: state => state.loading,
        })
    },
    props: ['loginTab'],
    methods: {
        ...mapActions('auth', [
            'login',
        ]),
        handlelogin() {
            this.$validator.validateAll().then(() => {
                this.$Progress.start()
                this.login(this.user)
                    .then(registered => {
                        if (registered) {
                            this.$Progress.finish()
                            if (window.Laravel.url_after_login) {
                                this.$router.push(window.Laravel.url_after_login)
                            } else {
                                this.$router.push("/")
                            }
                        }
                    })
                    .catch(err => {
                        this.$Progress.fail()
                        const message = this.$i18n.t('messages.login_fail')
                        noty({ text: message, force: true})
                    })
            })
        },
        redirect(provider) {
            window.location = `${window.Laravel.url}/redirect/${provider}`
        }
    }
}
</script>

<style lang="scss">
    .label-floating.has-danger {
        &:after {
            content: none;
        }
    }
</style>
