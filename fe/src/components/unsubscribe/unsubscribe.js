const Unsubscribe = {
    template: '#unsubscribe-template',
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
        const self = this;

        axios.get(`${pce.apiUrl}/unsubscribe/${this.$route.params.unsublink}`)
        .then((response) => {
            self.message.success = response.data.message;

            self.loading = false;
        })
        .catch((error) => {
            if (error.response.data.message) {
                self.message.error = error.response.data.message;
            } else {
                self.message.error = 'System error';
                console.log(error);
            }

            self.loading = false;
        });
    }
};

// Routing
pce.routes.push({
    path: '/unsubscribe/:unsublink',
    component: Unsubscribe,
    meta: {
        title: 'Unsubscribe',
        template: 'unsubscribe',
        description: 'User unsubscribe page'
    }
});