Vue.component('login', {
    template: dynamicTemplates.login,
    props: {
        login: {
            type: 'String'
        },
        pass: {
            type: 'String'
        }
    },
    data: function() {
        return {
            login: '',
            pass: ''
        };
    },
    mounted() {
        
    },
    methods: {
        submit: function() {
            axios.post('https://dev.api.pcesports.com/login', {
                login: this.login,
                pass: this.pass
            })
            .then(function (response) {
                pce.storage('set', 'token', response.data);
                console.log(response.data.sessionToken);
                //axios.defaults.headers.common['sessionToken'] = response.data.sessionToken;
            })
            .catch(function (error) {
                console.log(error.response.data.message);
            });
        }
    }
});