Vue.component('pagination', {
    template: '<div class="pagination">'+
                '<router-link v-for="page in pages"'+
                    'class="btn btn-info"'+
                    'role="button"'+
                    ':class="page.disabled?\'disabled\':false"'+
                    'v-bind:to="\'/\'+pageUrl+\'/page/\'+page.url">{{page.value}}</router-link>'+
              '</div>',
    props: {
		page: Number,
		amount: Number,
		pageUrl: String,
		amountPerPage: {
			type: Number,
			default: 5
		}
	},
   	data: function() {
   		return { pages: {} };
	},
    created: function() {
        this.render();
    },
    methods: {
        render: function() {
            // Getting max amount of possible pages
		    let maxPages = Math.ceil(this.amount / this.amountPerPage);
		    // Creating array
		    let pages = [];
		    // Defining start page
		    let startPage = this.page - 3;
		    let endPage = this.page + 3;

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
		    }

		    // Looping and creating buttons for pagination
		    for (let i = startPage; i <= endPage; ++i) {
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
});