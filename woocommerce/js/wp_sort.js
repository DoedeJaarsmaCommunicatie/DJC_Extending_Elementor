class WP_Sort {

    constructor() {
        this._order_form = document.querySelector('.js__filter__order_by')
        this._target_field = document.querySelector('.js-filter-page-filter-form').dataset.filterTarget
        this._products = []
        this.addEvents()
    }

    addEvents() {
        this._order_form.addEventListener('change', (event) => {
            let el = event.target
            let selected = el.querySelectorAll('option:checked')[0]
            let value_full = selected.value.split(':')
            let order_by = value_full[0]
            let order = value_full[1]
            this.getAllProducts()
                .then( () => {
                     this.ajaxCall(order_by, order).then( res => {
                         let posts = res
                         document.querySelector(`.${this._target_field}`).innerHTML = ''
                         posts.forEach( element => {
                             document.querySelector(`.${this._target_field}`).insertAdjacentHTML( 'beforeend', element )
                         })
                    })


                })
        })
    }

    getAllProducts() {
        return new Promise( (resolve, reject) => {
            document.querySelectorAll('[data-product-id]').forEach( el => {
                this._products.push(el.dataset.productId)
            })
            if ( this._products ) resolve(this._products)
            reject( 'No Products found' )
        })

    }

    ajaxCall(order_by, order) {
        let data = {
            action: 'the_sorting_hat_callback',
            data: {
                order_by: order_by,
                order: order,
                post_ids: this._products
            }
        }

        return new Promise((resolve, reject) => {
            jQuery.post(djcee_ajax_object.ajax_url, data, res => {
                if( res ) {
                    console.log(res.data.posts.data)
                    resolve (res.data.posts.data);
                }
                reject(undefined);
            })
        })

    }
}

new WP_Sort()