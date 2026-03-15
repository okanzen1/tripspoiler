window.initSwipers = function () {

    /* MOST POPULAR ACTIVITIES */
    const mostPopular = document.querySelector('.most-popular-swiper');

    if (mostPopular) {

        if (mostPopular.swiper) {
            mostPopular.swiper.destroy(true, true);
        }

        new Swiper(mostPopular, {
            loop: false,
            spaceBetween: 24,
            grabCursor: true,
            slidesPerView: 'auto',

            navigation: {
                nextEl: '.swiper-button-next-custom',
                prevEl: '.swiper-button-prev-custom',
            }
        });

    }


    /* REVIEWS */
    const reviewsSwiper = document.querySelector('.reviewsSwiper');

    if (reviewsSwiper) {

        if (reviewsSwiper.swiper) {
            reviewsSwiper.swiper.destroy(true, true);
        }

        new Swiper(reviewsSwiper, {
            spaceBetween: 24,
            slidesPerView: 1.15,

            breakpoints: {
                640: { slidesPerView: 1.5 },
                1024: { slidesPerView: 3 }
            }
        });

    }


    /* BLOG DETAIL ACTIVITIES */
    const premiumSwiper = document.querySelector('.premiumSwiper');

    if (premiumSwiper) {

        if (premiumSwiper.swiper) {
            premiumSwiper.swiper.destroy(true, true);
        }

        new Swiper(premiumSwiper, {

            slidesPerView: 1.1,
            spaceBetween: 24,
            grabCursor: true,

            breakpoints: {
                768: { slidesPerView: 1.4 },
                1024: { slidesPerView: 1.6 }
            },

            navigation: {
                nextEl: '.swiper-button-next-custom',
                prevEl: '.swiper-button-prev-custom',
            }

        });

    }

};