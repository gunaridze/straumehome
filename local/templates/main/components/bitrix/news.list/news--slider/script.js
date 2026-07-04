new Swiper('.other-news__slider', {
    loop: true,
    slidesPerView: 1,
    spaceBetween: 23,
    speed: 1000,
    navigation: {
        nextEl: '.other-news__slider-arrow--next',
        prevEl: '.other-news__slider-arrow--prev',
    },
    breakpoints: {
        576: {
            slidesPerView: 2,
        },
        768: {
            slidesPerView: 2,
        },
        1025: {
            slidesPerView: 2,
            spaceBetween: 52,
        }
    }
});