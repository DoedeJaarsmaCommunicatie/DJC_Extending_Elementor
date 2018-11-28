class Filter {

    constructor() {
        this.setters()
        this.addClickers()
        this._filters = []
    }

    setters() {
        this._elements = document.querySelectorAll('[data-filter]')
        this._target_field = document.querySelector('.js-filter-page-filter-form').dataset.filterTarget
        this._wordpress_filter = document.querySelector('.js-filter-page-filter-form').dataset.wordpressFilter
    }

    addClickers() {
        try {
            try {
                this._elements.forEach(($element) => {
                    this.addClicker($element)
                })
            } catch ( e ) {
                reject(e)
            }

        } catch ( e ) {
            reject(e)
        }
    }

    addClicker($element) {
        $element.addEventListener('change', () => {
            try {
                let _checked = document.querySelectorAll('[data-filter]:checked')

                this.ajaxRequest(_checked)

            } catch ( e ) {
                reject(e)
            }
        })
    }

    fetchAllFilters() {
        document.querySelectorAll('.js-target__section__filter').forEach( element => {
            this._filters.push(element.dataset.filterName)
        })
    }

    async ajaxRequest($checked) {
        let data = []

        this.fetchAllFilters()

        $checked.forEach( el => {
            // Parameters el.dataset.filter, el.dataset.filtrate

            data.push({
                filter: el.dataset.filter,
                filtrate: el.dataset.filtrate,
                wp_filter: this._wordpress_filter,
                filters: this._filters
            })
        })

        const meta = {
            action: 'filtered_callback',
            data: data
        }

        await jQuery.post(djcee_ajax_object.ajax_url, meta, response => {
            this.fillTheContent(response.data)
            // this.fixTheFilters(response.data)
        })
    }

    fillTheContent(content) {
        document.querySelector(`.${this._target_field}`).innerHTML = `${content.posts.data}`
    }

    fixTheFilters(filter) {
        filter.filters.forEach( filterList => {
            filterList.forEach( filter => {
                console.log( filter.slug )
            })
        })
    }
}

// jQuery.post(djcee_ajax_object.ajax_url, { action: 'filtered_callback' } , (response) => {
//     console.log(response)
// })

new Filter()