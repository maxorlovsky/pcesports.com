<template>
<div class="profile">
    <div class="block">
        <div class="block-content semi-widths">
            <loading v-if="loading"></loading>

            <section class="profile-wrapper" v-if="!loading">
                <h4>Change Password</h4>

                <div class="alert alert-danger" v-if="formError" v-html="formError"></div>

                <form method="post" v-on:submit.prevent="submit()">
                    <div class="heading-text">Here you can change your password. Keep in mind that the only strict rule that we ask is minimum password length is 6 characters. But we do advice you to pick harder password using upper case and lower case letters.</div>

                    <div class="input-wrapper input-group form-group"
                        :class="{ error: errorClasses.oldPass }">
                        <div class="input-group-addon fa fa-lock"></div>
                        <input class="form-control" type="password" v-model="form.oldPass" placeholder="Current password" />
                    </div>

                    <div class="input-wrapper input-group form-group"
                        :class="{ error: errorClasses.pass }">
                        <div class="input-group-addon fa fa-lock"></div>
                        <input class="form-control" type="password" v-model="form.pass" placeholder="New password" />
                    </div>

                    <div class="input-wrapper input-group form-group"
                        :class="{ error: errorClasses.repeatPass }">
                        <div class="input-group-addon fa fa-lock"></div>
                        <input class="form-control" type="password" v-model="form.repeatPass" placeholder="Repeat new password" />
                    </div>

                    <button class="btn btn-primary btn-lg" :disabled="formLoading">Change password</button>
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

const changePasswordPage = {
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
            formError: '',
            form: {
                oldPass: '',
                pass: '',
                repeatPass: ''
            },
            errorClasses: {
                oldPass: false,
                pass: false,
                repeatPass: false
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
                oldPass: false,
                pass: false,
                repeatPass: false
            };

            // Frontend check
            if (!this.form.oldPass || !this.form.pass || !this.form.repeatPass) {
                // Generic error message
                this.formError = 'Please fill in the form';
                this.formLoading = false;

                // Mark specific fields as empty ones
                this.errorClasses = {
                    oldPass: !this.form.oldPass ? true : false,
                    pass: !this.form.pass ? true : false,
                    repeatPass: !this.form.repeatPass ? true : false
                };

                return false;
            }

            axios.put(`${pce.apiUrl}/user-data/change-password`, {
                oldPass: this.form.oldPass,
                pass: this.form.pass,
                repeatPass: this.form.repeatPass
            })
            .then((response) => {
                this.$parent.displayMessage(response.data.message, 'success');
                this.formLoading = false;
                this.form = {
                    oldPass: '',
                    pass: '',
                    repeatPass: ''
                };
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
    path: '/profile/change-password',
    component: changePasswordPage,
    meta: {
        loggedIn: true,
        title: 'Change Password - Profile',
        description: 'User page to change password'
    }
});

export default changePasswordPage;
</script>