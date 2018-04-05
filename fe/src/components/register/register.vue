<template>
<div class="register-form">
    <div class="alert alert-success" v-if="registerSuccess" v-html="registerSuccess"></div>
    <div class="alert alert-danger" v-if="registerError" v-html="registerError"></div>

    <form method="post"
        v-on:submit.prevent="submit()"
        v-if="!registerSuccess">
        <div class="input-wrapper"
            :class="{ error: errorClasses.name }">
            <input type="text" v-model="form.name" placeholder="Name" />
        </div>
        <div class="input-wrapper"
            :class="{ error: errorClasses.login }">
            <input type="text" v-model="form.login" placeholder="Email" />
        </div>
        <div class="input-wrapper"
            :class="{ error: errorClasses.pass }">
            <input type="password" v-model="form.pass" placeholder="Password" />
        </div>
        <div class="input-wrapper"
            :class="{ error: errorClasses.repeatPass }">
            <input type="password" v-model="form.repeatPass" placeholder="Repeat password" />
        </div>

        <div class="input-wrapper"
            :class="{ error: errorClasses.captcha }">
            <div class="g-recaptcha" id="reg-g-recaptcha" :data-sitekey="captchaKey"></div>
        </div>

        <button class="btn btn-primary" :disabled="formLoading">Register</button>
    </form>
</div>
</template>

<script>
// 3rd party libs
import axios from 'axios';

export default {
    name: 'register',
    data: function() {
        return {
            form: {
                name: '',
                login: '',
                pass: '',
                repeatPass: ''
            },
            registerError: '',
            registerSuccess: '',
            loading: false,
            formLoading: false,
            rcapt_id: 0,
            captchaKey: '6LcwJ_8SAAAAAL2SgH-NYduvEp9DLUlndHrlMs7Z',
            errorClasses: {
                name: false,
                login: false,
                pass: false,
                repeatPass: false,
                captcha: false
            }
        };
    },
    mounted() {
        if (window.grecaptcha) {
            this.rcapt_id = grecaptcha.render( document.getElementById('reg-g-recaptcha'), { sitekey : this.captchaKey });
        }
    },
    methods: {
        submit: function() {
            this.formLoading = true;

            this.errorClasses = {
                name: false,
                login: false,
                pass: false,
                repeatPass: false,
                captcha: false
            };

            // Frontend check
            if (!this.form.login || !this.form.pass || !this.form.repeatPass || !this.form.name) {
                // Generic error message
                this.registerError = 'Please fill in the form';
                this.formLoading = false;

                // Mark specific fields as empty ones
                this.errorClasses = {
                    name: !this.form.name ? true : false,
                    login: !this.form.login ? true : false,
                    pass: !this.form.pass ? true : false,
                    repeatPass: !this.form.repeatPass ? true : false,
                    captcha: false
                };

                return false;
            }

            const recaptcha_response = grecaptcha.getResponse(this.rcapt_id);

            axios.post(`${pce.apiUrl}/register`, {
                name: this.form.name,
                login: this.form.login,
                pass: this.form.pass,
                repeatPass: this.form.repeatPass,
                captcha: recaptcha_response
            })
            .then((response) => {
                this.registerSuccess = response.data.message;
                this.formLoading = false;
            })
            .catch((error) => {
                this.formLoading = false;

                // Display error message from API
                this.registerError = error.response.data.message;

                // Update recaptcha on error, new data required every time
                grecaptcha.reset();

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
}
</script>