import Swiper from 'swiper';
import { Navigation } from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/navigation';

window.initSwipers = function () {

    /* MOST POPULAR ACTIVITIES */
    const mostPopular = document.querySelector('.most-popular-swiper');

    if (mostPopular) {

        if (mostPopular.swiper) {
            mostPopular.swiper.destroy(true, true);
        }

        new Swiper(mostPopular, {
            modules: [Navigation],
            loop: false,
            spaceBetween: 24,
            grabCursor: true,
            slidesPerView: 'auto',

            navigation: {
                nextEl: mostPopular.closest('section')?.querySelector('.swiper-button-next-custom'),
                prevEl: mostPopular.closest('section')?.querySelector('.swiper-button-prev-custom'),
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
            modules: [Navigation],

            slidesPerView: 1.1,
            spaceBetween: 24,
            grabCursor: true,

            breakpoints: {
                768: { slidesPerView: 1.4 },
                1024: { slidesPerView: 1.6 }
            },

            navigation: {
                nextEl: premiumSwiper.closest('section')?.querySelector('.swiper-button-next-custom'),
                prevEl: premiumSwiper.closest('section')?.querySelector('.swiper-button-prev-custom'),
            }

        });
    }

};
