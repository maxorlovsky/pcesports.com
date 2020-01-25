<template>
<div class="registration-approval">
    <div class="block">
        <div class="block-content">
            <loading v-if="loading"></loading>

            <h4 class="alert alert-danger" v-if="message.error">{{message.error}}</h4>
            <h4 class="alert alert-success" v-if="message.success">{{message.success}}</h4>
        </div>
    </div>
</div>
</template>

<script>
// 3rd party libs
import axios from 'axios';

// Global functions
import { functions } from '../../functions.js';

// Components
import loading from '../../components/loading/loading.vue';

const registrationApprovalPage = {
    components: {
        loading
    },
    data: function() {
        return {
            loading: true,
            message: {
                success: '',
                error: ''
            }
        };
    },
    created: function() {
        axios.get(`${pce.apiUrl}/register/${this.$route.params.code}`)
        .then((response) => {
            this.message.success = response.data.message;

            functions.storage('set', 'token', { "sessionToken": response.data.sessionToken }, 604800000); // 7 days
            
            this.$parent.login();
            this.$parent.displayMessage('Registration successful', 'success');
            this.$router.push('/profile');
        })
        .catch((error) => {
            if (error.response.data.message) {
                this.message.error = error.response.data.message;
            } else {
                this.message.error = 'System error';
                console.log(error);
            }
            this.loading = false;
        });
    }
};

// Routing
pce.routes.push({
    path: '/registration/:code',
    component: registrationApprovalPage,
    meta: {
        title: 'Registration Approval',
        description: 'Complete your registration process on PC Esports website'
    }
});

export default registrationApprovalPage;
</script>