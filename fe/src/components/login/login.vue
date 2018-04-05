<template>
<div class="login-form">
    <div class="alert alert-danger" v-if="loginError">{{loginError}}</div>

    <form method="post" v-on:submit.prevent="submit()">
        <div class="input-wrapper">
            <input type="text" v-model="login" placeholder="Email" />
        </div>
        <div class="input-wrapper">
            <input type="password" v-model="pass" placeholder="Password" />
        </div>

        <button class="btn btn-primary" :disabled="formLoading">Login</button>
    </form>

    <a href="javascript:;" v-on:click="openRightMenu('forgot-pass')">Forgot password?</a>
    <a href="javascript:;" v-on:click="openRightMenu('register')" class="small-scren-reg">Sign up</a>
</div>
</template>

<script>
// 3rd party libs
import axios from 'axios';

// Global functions
import { functions } from '../../functions.js';

export default {
    name: 'login',
    data: function() {
        return {
            login: '',
            pass: '',
            loginError: '',
            loading: false,
            formLoading: false
        };
    },
    methods: {
        submit: function() {
            this.formLoading = true;

            if (!this.login || !this.pass) {
                this.loginError = 'Please fill in the form';
                this.formLoading = false;
                return false;
            }

            axios.post(`${pce.apiUrl}/login`, {
                login: this.login,
                pass: this.pass
            })
            .then((response) => {
                functions.storage('set', 'token', response.data, 604800000); // 7 days
                this.$parent.$parent.login();
                this.formLoading = false;
            })
            .catch((error) => {
                this.loginError = error.response.data.message;
                this.formLoading = false;
            });
        },
        openRightMenu: function(menu) {
            this.$parent.$parent.rightSideMenuForm = menu;
            this.$emit('right-menu');
        }
    }
}
</script>