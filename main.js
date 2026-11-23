(function ($) {
    "use strict";

    // ── Spinner — vanilla JS fallback ensures it ALWAYS hides ─────────────────
    // (Inline <script> in <head> already hides it instantly,
    //  this is belt-and-suspenders for when jQuery is available)
    function hideSpinner() {
        var s = document.getElementById('spinner');
        if (s) { s.style.display = 'none'; s.classList.remove('show'); }
        var $s = (typeof $ !== 'undefined') ? $('#spinner') : null;
        if ($s && $s.length) $s.removeClass('show').hide();
    }
    hideSpinner();
    $(document).ready(hideSpinner);
    $(window).on('load', hideSpinner);
    setTimeout(hideSpinner, 2000); // nuclear option

    // ── WOW.js ────────────────────────────────────────────────────────────────
    if (typeof WOW !== 'undefined') { new WOW().init(); }

    // ── Sticky Navbar ─────────────────────────────────────────────────────────
    $(window).scroll(function () {
        if ($(this).scrollTop() > 300) {
            $('.sticky-top').css('top', '0px');
        } else {
            $('.sticky-top').css('top', '-100px');
        }
    });

    // ── Dropdown hover (desktop) ──────────────────────────────────────────────
    var $dropdown = $(".dropdown");
    var $dropdownToggle = $(".dropdown-toggle");
    var $dropdownMenu = $(".dropdown-menu");
    var showClass = "show";
    $(window).on("load resize", function () {
        if (this.matchMedia("(min-width: 992px)").matches) {
            $dropdown.hover(
                function () {
                    var $this = $(this);
                    $this.addClass(showClass);
                    $this.find($dropdownToggle).attr("aria-expanded", "true");
                    $this.find($dropdownMenu).addClass(showClass);
                },
                function () {
                    var $this = $(this);
                    $this.removeClass(showClass);
                    $this.find($dropdownToggle).attr("aria-expanded", "false");
                    $this.find($dropdownMenu).removeClass(showClass);
                }
            );
        } else {
            $dropdown.off("mouseenter mouseleave");
        }
    });

    // ── Back to top ───────────────────────────────────────────────────────────
    $(window).scroll(function () {
        if ($(this).scrollTop() > 300) {
            $('.back-to-top').fadeIn('slow');
        } else {
            $('.back-to-top').fadeOut('slow');
        }
    });
    $('.back-to-top').click(function () {
        $('html, body').animate({ scrollTop: 0 }, 1500, 'easeInOutExpo');
        return false;
    });

    // ── Fix any stale .html links ─────────────────────────────────────────────
    $(document).ready(function () {
        $('a[href="enroll.html"]').attr('href', 'enroll.php');
        $('a[href="contact.html"]').attr('href', 'contact.php');
        $('a[href="about.html"]').attr('href', 'about.php');
        $('a[href="courses.html"]').attr('href', 'courses.php');
        $('a[href="index.html"]').attr('href', 'index.php');
    });

    // ── Owl Carousel ──────────────────────────────────────────────────────────
    if ($.fn.owlCarousel) {
        $(".header-carousel").owlCarousel({
            autoplay: true,
            smartSpeed: 1500,
            items: 1,
            dots: false,
            loop: true,
            nav: true,
            navText: [
                '<i class="bi bi-chevron-left"></i>',
                '<i class="bi bi-chevron-right"></i>'
            ]
        });
        $(".testimonial-carousel").owlCarousel({
            autoplay: true,
            smartSpeed: 1000,
            center: true,
            margin: 24,
            dots: true,
            loop: true,
            nav: false,
            responsive: {
                0:   { items: 1 },
                768: { items: 2 },
                992: { items: 3 }
            }
        });
    }

})(jQuery);
