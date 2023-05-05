/*
 * @Author: Warley Elias
 * @Email: warley.elias@pentagrama.com.br
 * @Date: 2023-05-03 17:27:35
 * @Last Modified by: Warley Elias
 * @Last Modified time: 2023-05-04 16:21:34
 */

TransparentCheckout = Class.create({
    init: function (config) {
        let self = this;
        this.checkoutId = config.checkoutId;
        this.checkoutUrl = config.checkoutUrl;
        this.retrieveAbandonedCartUrl = config.retrieveAbandonedCartUrl;
        this.checkoutUrlWithParameter = this.checkoutUrl + '&t=pagaleve';
        //Questionar handel o motivo de não enviar o atributo pm com valor split assim como no M2
        this.checkoutUrlWithParameter = this.checkoutUrlWithParameter.replace('&pm=&', '&pm=split&');

        self.initPagaLeve(this.checkoutUrlWithParameter);

        window.addEventListener('message', function (event) {
            if (event.data.action === 'pagaleve-checkout-finish') {
                let pagaleveData = event.data.data;

                //console.log(pagaleveData.reason) // cancel/confirm
                // https://sualoja.com.br/cancelament / https://sualoja.com.br/aprovacao 
                // { reason: 'cancel', value: 'https://sualoja.com.br/cancelamento' }
                if (pagaleveData.reason === 'cancel') {
                    self.retrieveAbandonedCart(self.retrieveAbandonedCartUrl);
                }
            }
        });
    },

    initPagaLeve: function (urlWithParameter) {
        parent.postMessage({ action: 'pagaleve-checkout-init', url: urlWithParameter }, '*');
    },

    retrieveAbandonedCart: function (retrieveAbandonedCartUrl) {
        window.location.replace(retrieveAbandonedCartUrl);
    }
});