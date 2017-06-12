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
            console.log(this.login);

            axios.get(`http://dev.api.pcesports.com/login/${this.login}/${this.pass}`)
            .then(function (response) {
                console.log(response.data);
            });
        }
    }
});