<template>
<div class="profile">
    <div class="block">
        <div class="block-content semi-widths">
            <loading v-if="loading"></loading>

            <section class="profile-wrapper" v-if="!loading">
                <h4>Settings</h4>

                <div class="alert alert-danger" v-if="formError" v-html="formError"></div>

                <form method="post" v-on:submit.prevent="submit()">
                    <div class="heading-text">Here you can change website settings and your personal settings. This data is private and is not displayed on your profile.</div>

                    <div class="input-wrapper input-group form-group"
                        :class="{ error: errorClasses.timestyle }">
                        <div class="input-group-addon fa fa-clock-o"></div>
                        <select class="custom-select" v-model="form.timestyle">
                            <option value="">Please choose preferred time style</option>
                            <option value="0">12:00 AM/PM</option>
                            <option value="1">24:00</option>
                        </select>
                    </div>

                    <label class="input-wrapper input-group form-group custom-control custom-checkbox"
                        :class="{ error: errorClasses.subscribe }">
                        <input type="checkbox"
                            class="custom-control-input"
                            v-model="form.subscription"
                            :checked="form.subscription">
                        <span class="custom-control-indicator"></span>
                        <span class="custom-control-description">Subscribe to mail list</span>
                    </label>

                    <button class="btn btn-primary btn-lg" :disabled="formLoading">Update settings</button>
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

const settingsPage = {
    props: {
        userData: {
            type: Object
        }
    },
    components: {
        loading
    },
    data: function() {
        return {
            loading: true,
            formLoading: false,
            formError: '',
            form: {
                timestyle: null,
                subscription: false
            },
            errorClasses: {
                timestyle: false,
                subscription: false
            }
        };
    },
    watch: {
        'userData': function() {
            return this.fillUserData();
        }
    },
    created: function() {
        return this.fillUserData();  
    },
    methods: {
        fillUserData: function() {
            this.form.timestyle = this.userData.timestyle;
            this.form.subscription = this.userData.subscription.removed === "0" ? true : false;

            if (!this.userData) {
                this.$parent.authRequired();
            }

            this.loading = false;
        },
        submit: function() {
            this.formLoading = true;
            this.formError = '';

            this.errorClasses = {
                timestyle: false,
                subscription: false
            };

            // Frontend check
            /*if (!this.form.timestyle || !this.form.subscription) {
                // Generic error message
                this.formError = 'Please fill in the form';
                this.formLoading = false;

                // Mark specific fields as empty ones
                this.errorClasses = {
                    timestyle: !this.form.timestyle ? true : false,
                    subscription: !this.form.subscription ? true : false
                };

                return false;
            }*/

            axios.put(`${pce.apiUrl}/user-data/settings`, {
                timestyle: this.form.timestyle,
                subscription: this.form.subscription
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
    path: '/profile/settings',
    component: settingsPage,
    meta: {
        loggedIn: true,
        title: 'Settings - Profile',
        description: 'User page to change password and personal settings'
    }
});

export default settingsPage;
</script>