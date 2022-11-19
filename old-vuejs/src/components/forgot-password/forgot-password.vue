<template>
<div class="forgot-password-form">
    <div class="alert alert-success" v-if="restoreSuccess">{{restoreSuccess}}</div>
    <div class="alert alert-danger" v-if="restoreError" v-html="restoreError"></div>

    <form method="post" v-on:submit.prevent="submit()" v-if="showForm === 1">
        <div class="input-wrapper"
            :class="{ error: errorClasses.login }">
            <input type="text" v-model="form.login" placeholder="Email" />
        </div>

        <div class="input-wrapper">
            <div class="g-recaptcha" id="fp-g-recaptcha" :data-sitekey="captchaKey"></div>
        </div>

        <button class="btn btn-primary" :disabled="formLoading">Restore password</button>
    </form>

    <form method="post" v-on:submit.prevent="submitReset()" v-if="showForm === 2">
        <div class="input-wrapper"
            :class="{ error: errorClasses.code }">
            <input type="text" v-model="resetForm.code" placeholder="Code" />
        </div>

        <div class="input-wrapper"
            :class="{ error: errorClasses.pass }">
            <input type="password" v-model="resetForm.pass" placeholder="New password" />
        </div>

        <div class="input-wrapper"
            :class="{ error: errorClasses.repeatPass }">
            <input type="password" v-model="resetForm.repeatPass" placeholder="Repeat new password" />
        </div>

        <button class="btn btn-primary" :disabled="formLoading">Set up new password</button>
    </form>

    <a href="javascript:;" v-on:click="openLoginMenu()">Back to login form</a>
</div>
</template>

<script>
// 3rd party libs
import axios from 'axios';

// Global functions
import { functions } from '../../functions.js';

export default {
    name: 'forgot-password',
    data: function() {
        return {
            restoreError: '',
            restoreSuccess: '',
            captchaId: 0,
            captchaKey: '6LcwJ_8SAAAAAL2SgH-NYduvEp9DLUlndHrlMs7Z',
            formLoading: false,
            showForm: 1,
            form: {
                login: ''
            },
            resetForm: {
                code: '',
                pass: '',
                repeatPass: ''
            },
            errorClasses: {
                login: false,
                code: false,
                pass: false,
                repeatPass: false
            }
        };
    },
    mounted() {
        if (window.grecaptcha) {
            this.captchaId = grecaptcha.render( document.getElementById('fp-g-recaptcha'), { sitekey : this.captchaKey });
        }
    },
    methods: {
        submit: function() {
            this.formLoading = true;
            this.restoreSuccess = '';
            this.restoreError = '';
            this.errorClasses.login = false;

            if (!this.form.login) {
                this.restoreError = 'Please fill in the form';
                this.formLoading = false;
                this.errorClasses.login = true;
                return false;
            }

            axios.post(`${pce.apiUrl}/forgot-password`, {
                login: this.form.login,
                captcha: grecaptcha.getResponse(this.captchaId)
            })
            .then((response) => {
                this.restoreSuccess = response.data.message;
                this.showForm = 2;
                this.formLoading = false;
                this.checkCaptcha();
            })
            .catch((error) => {
                this.restoreError = error.response.data.message;
                this.formLoading = false;
                this.errorClasses.login = true;
                this.checkCaptcha();
            });
        },
        submitReset: function() {
            this.formLoading = true;
            this.restoreSuccess = '';
            this.restoreError = '';
            this.errorClasses = {
                login: false,
                code: false,
                pass: false,
                repeatPass: false
            };

            if (!this.resetForm.code || !this.resetForm.pass || !this.resetForm.repeatPass) {
                this.restoreError = 'Please fill in the form';
                this.formLoading = false;
                this.errorClasses = {
                    login: true,
                    code: !this.form.code ? true : false,
                    pass: !this.form.pass ? true : false,
                    repeatPass: !this.form.repeatPass ? true : false
                };
                return false;
            }

            axios.post(`${pce.apiUrl}/forgot-password/reset`, {
                login: this.form.login,
                code: this.resetForm.code,
                pass: this.resetForm.pass,
                repeatPass: this.resetForm.repeatPass
            })
            .then((response) => {
                functions.storage('set', 'token', { "sessionToken": response.data.sessionToken }, 604800000); // 7 days
                this.$parent.$emit('right-menu');
                this.$parent.$parent.login();
                this.$parent.$parent.displayMessage(response.data.message, 'success');
                this.$router.push('/profile');
            })
            .catch((error) => {
                this.restoreError = error.response.data.message;
                this.formLoading = false;

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
        },
        openLoginMenu: function() {
            this.$parent.$parent.rightSideMenuForm = 'login';
            this.$emit('right-menu');
        },
        checkCaptcha: function() {
            if (!document.getElementById('fp-g-recaptcha').innerHTML) {
                this.captchaId = grecaptcha.render( document.getElementById('fp-g-recaptcha'), { sitekey : this.captchaKey });
            } else {
                grecaptcha.reset();
            }
        }
    }
}
</script>