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
            axios.post('http://dev.api.pcesports.com/login', {
                login: this.login,
                pass: this.pass
            })
            .then(function (response) {
                pce.storage('set', 'token', response.data);
            })
            .catch(function (error) {
                console.log(error.response.data.message);
            });
        }
    }
});