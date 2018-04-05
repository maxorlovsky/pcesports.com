<template>
<div class="unsubscribe">
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

// Components
import loading from '../../components/loading/loading.vue';

const unsubscribePage = {
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
        axios.get(`${pce.apiUrl}/unsubscribe/${this.$route.params.email}/${this.$route.params.unsublink}`)
        .then((response) => {
            this.message.success = response.data.message;

            this.loading = false;
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
    path: '/unsubscribe/:email/:unsublink',
    component: unsubscribePage,
    meta: {
        title: 'Unsubscribe',
        description: 'User unsubscribe page'
    }
});

export default unsubscribePage;
</script>