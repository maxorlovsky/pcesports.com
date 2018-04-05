<template>
<div class="float-message alert"
    v-if="parameters.message"
    :class="parameters.type"
    v-html="parameters.message"
></div>
</template>

<script>
export default {
    name: 'float-message',
    props: {
        parameters: {
            type: Object
        }
	},
   	data: function() {
   		return {
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
}
</script>