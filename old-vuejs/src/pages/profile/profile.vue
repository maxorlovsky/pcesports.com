<template>
<div class="profile">
    <div class="block">
        <div class="block-content semi-widths">
            <loading v-if="loading"></loading>

            <section class="profile-wrapper" v-if="!loading">
                <h4>Profile</h4>

                <div class="alert alert-danger" v-if="formError" v-html="formError"></div>

                <form method="post" v-on:submit.prevent="submit()">
                    <div class="heading-text">This is basic, public information of you, that is displayed to all. Name can be changed, but must be always unique, so if you change it and someone else take it, you wouldn't be able to take it back. Also be careful, if you change your name, link to your public profile will change as well.</div>

                    <div class="input-wrapper input-group form-group"
                        :class="{ error: errorClasses.name }">
                        <div class="input-group-addon fa fa-user"></div>
                        <input class="form-control" type="text" v-model="user.name" placeholder="Name" />
                    </div>

                    <div class="input-wrapper input-group form-group"
                        :class="{ error: errorClasses.battletag }">
                        <div class="input-group-addon icon-battlenet"></div>
                        <input class="form-control" type="text" v-model="user.battletag" placeholder="Battle Tag" />
                    </div>

                    <div class="input-wrapper input-group form-group"
                        :class="{ error: errorClasses.avatar }">
                        <div class="input-group-addon fa fa-picture-o"></div>
                        <div class="holder form-control">
                            <div class="avatars-list">
                                <div class="avatar-block"
                                    :class="{ picked: user.avatar == i }"
                                    :key="i"
                                    v-for="i in 30"
                                    v-on:click="user.avatar = i"
                                >
                                    <img v-bind:src="'/dist/assets/images/avatar/' + i + '.jpg'" />
                                </div>
                            </div>
                        </div>
                        <input class="form-control" type="hidden" v-model="user.avatar" placeholder="Avatar" />
                    </div>

                    <button class="btn btn-primary btn-lg" :disabled="formLoading">Update profile data</button>
                </form>
            </section>
        </div>
    </div>
</div>
</template>

<script>
// 3rd party libs
import axios from 'axios';

// Components
import loading from '../../components/loading/loading.vue';

const profilePage = {
    components: {
        loading
    },
    props: {
        userData: {
            type: Object
        }
    },
    data: function() {
        return {
            loading: true,
            formLoading: false,
            user: {},
            formSuccess: '',
            formError: '',
            errorClasses: {
                name: false,
                battletag: false,
                avatar: false
            }
        };
    },
    watch: {
        'userData': function() {
            return this.fillUserData();
        }
    },
    created: function() {
        this.fillUserData();
    },
    methods: {
        fillUserData: function() {
            this.user = this.userData;

            if (!this.user) {
                this.$parent.authRequired();
            }

            this.loading = false;
        },
        submit: function() {
            this.formLoading = true;
            this.formError = '';

            this.errorClasses = {
                name: false,
                battletag: false,
                avatar: false
            };

            // Frontend check
            if (!this.user.name || !this.user.avatar) {
                // Generic error message
                this.formError = 'Please fill in the form';
                this.formLoading = false;

                // Mark specific fields as empty ones
                this.errorClasses = {
                    name: !this.user.name ? true : false,
                    battletag: false,
                    avatar: !this.user.avatar ? true : false
                };

                return false;
            }

            axios.put(`${pce.apiUrl}/user-data/profile`, {
                name: this.user.name,
                battletag: this.user.battletag,
                avatar: this.user.avatar
            })
            .then((response) => {
                this.$parent.displayMessage(response.data.message, 'success');
                this.$parent.recacheLoggedInData();
                this.formLoading = false;
            })
            .catch((error) => {
                this.formLoading = false;

                // Display error message from API
                this.formError = error.response.data.message;

                let errorFields = error.response.data.fields;

                // In some cases slim return array as json, we need to convert it
                if (errorFields.constructor !== Array) {
                    errorFields = Object.keys(errorFields).map(key => errorFields[key]);
                }

                // Mark fields with error class
                for (let i = 0; i < errorFields.length; ++i) {
                    this.errorClasses[errorFields[i]] = true;
                }
            });
        }
    }
};

// Routing
pce.routes.push({
    path: '/profile',
    component: profilePage,
    meta: {
        loggedIn: true,
        title: 'Profile',
        description: 'User profile'
    }
});

export default profilePage;
</script>