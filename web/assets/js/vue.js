Vue.component('pagination', {
	props: {
		page: Number,
		amount: Number,
		pageUrl: String,
		amountPerPage: {
			type: Number,
			default: 5
		}
	},
	template: '<div class="pagination">'+
				'<router-link v-for="page in pages"'+
					'class="btn btn-info" '+
					'role="button" '+
					':class="page.disabled?\'disabled\':false" '+
					'v-bind:to="\'/\'+pageUrl+\'/page/\'+page.url">{{page.value}}</router-link>'+
			   '</div>',
   	data: function() {
   		return { pages: {} };
	},
	methods: {
        render: function() {
            // Getting max amount of possible pages
		    var maxPages = Math.ceil(this.amount / this.amountPerPage);
		    // Creating array
		    var pages = [];
		    // Defining start page
		    var startPage = this.page - 3;
		    var endPage = this.page + 3;

		    // Checking if numbers are correct
		    if (startPage < 1) {
		        startPage = 1;
		    }
		    if (endPage > maxPages) {
		        endPage = maxPages;
		    }

		    // Add backward button
		    if (this.page != 1) {
		        pages.push({
		            url: parseInt(this.page) - 1,
		            value: '<<',
		        });
		        backward = true;
		    }

		    // Looping and creating buttons for pagination
		    for (i = startPage; i <= endPage; ++i) {
		        pages.push({
		            url: parseInt(i),
		            value: i,
		            disabled: (i == this.page ? true : false)
		        });
		    }

		    //Add forward button at the end
		    if (this.page != maxPages) {
		        pages.push({
		            url: parseInt(this.page) + 1,
		            value: '>>',
		        });
		        forward = true;
		    }

		    this.pages = pages;

		    return pages;
        }
    },
	watch: {
		page: function() {
			this.render();
		},
		amount: function() {
			this.render();
		}
	}
})

Vue.component('loading', {
	template: '<div class="loading"></div>',
   	data: function() {
   		return { pages: {} };
	}
});

