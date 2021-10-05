var config = {
    paths: {
        'owl.carousel': 'Olegnax_Core/owl.carousel/owl.carousel.min',
        'OXowlCarousel': 'Olegnax_Core/owl.carousel'
    },
    shim: {
        'owl.carousel': {deps: ['jquery', 'jquery-ui-modules/widget']}
    }
};
if(OX_OWL_DISABLE){
    delete config.paths['owl.carousel'];
    delete config.paths['OXowlCarousel'];
    delete config.shim['owl.carousel'];
}