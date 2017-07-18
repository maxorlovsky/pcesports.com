Vue.component('float-message', {
    template: '<div class="float-message alert" v-if="parameters.message" :class="parameters.type">{{parameters.message}}</div>',
    props: {
        parameters: {
            type: 'object'
        }
	},
   	data: function() {
   		return {
            parameters: {},
            timeout: null
        };
	},
    created: function() {
        this.render();
    },
    methods: {
        render: function() {
            this.parameters.type = `alert-${this.parameters.type}`;

            if (this.timeout) {
                clearTimeout(this.timeout);
            }

            this.timeout = setTimeout(() => {
                this.$parent.displayMessage('');
            }, 5000);
        }
    },
	watch: {
		parameters: function() {
			this.render();
		}
	}
});