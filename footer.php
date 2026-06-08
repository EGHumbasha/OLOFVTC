    <!-- Footer Start -->
    <div class="container-fluid bg-dark text-light footer pt-5 mt-5">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-lg-3 col-md-6">
                    <h4 class="text-primary mb-3">Quick Links</h4>
                    <a class="btn btn-link text-light" href="about.html">About Us</a>
                    <a class="btn btn-link text-light" href="contact.php">Contact Us</a>
                    <a class="btn btn-link text-light" href="privacy.html">Privacy Policy</a>
                    <a class="btn btn-link text-light" href="terms.html">Terms &amp; Conditions</a>
                    <a class="btn btn-link text-light" href="faq.html">FAQs &amp; Help</a>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h4 class="text-primary mb-3">Contact</h4>
                    <p><i class="fa fa-map-marker-alt me-3"></i>Stand No. 24574 Pumula South, Bulawayo</p>
                    <p><i class="fa fa-phone-alt me-3"></i>+263 773 833 645</p>
                    <p><i class="fa fa-envelope me-3"></i>info@ourladyoffatima.co.zw</p>
                    <div class="d-flex pt-2">
                        <a class="btn btn-sm-square mx-1" style="background:#005f99;" href="#"><i class="fab fa-twitter" style="color:#fff;"></i></a>
                        <a class="btn btn-sm-square mx-1" style="background:#ff8c00;" href="https://www.facebook.com/people/Our-Lady-Of-Fatima-VTC/61583791887969/" target="_blank" rel="noopener"><i class="fab fa-facebook-f" style="color:#fff;"></i></a>
                        <a class="btn btn-sm-square mx-1" style="background:#005f99;" href="#"><i class="fab fa-youtube" style="color:#fff;"></i></a>
                        <a class="btn btn-sm-square mx-1" style="background:#005f99;" href="#"><i class="fab fa-linkedin-in" style="color:#fff;"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h4 class="text-primary mb-3">Gallery</h4>
                    <div class="row g-2 pt-2">
                        <div class="col-4"><img class="img-fluid bg-light p-1" src="img/course-1.jpg" alt="Course"></div>
                        <div class="col-4"><img class="img-fluid bg-light p-1" src="img/course-2.jpg" alt="Course"></div>
                        <div class="col-4"><img class="img-fluid bg-light p-1" src="img/course-3.jpg" alt="Course"></div>
                    </div>
                    <a href="gallery.php" class="btn btn-sm btn-outline-light mt-3">View Gallery</a>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h4 class="text-primary mb-3">Newsletter</h4>
                    <p>Subscribe to get updates on new courses and events.</p>
                    <input type="text" id="nl-hp" style="display:none!important;" tabindex="-1" autocomplete="off">
                    <div class="position-relative mx-auto" style="max-width:400px;">
                        <input id="nl-email" class="form-control border-0 w-100 py-3 ps-4 pe-5" type="email" placeholder="Your email address" aria-label="Newsletter email">
                        <button id="nl-btn" type="button" class="btn position-absolute top-0 end-0 py-3 px-4" style="background:#005f99;color:#fff;border-radius:0 6px 6px 0;">Sign Up</button>
                    </div>
                    <div id="nl-msg" class="mt-2 small" style="display:none;"></div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="copyright py-3">
                <div class="row">
                    <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">&copy; <a class="border-bottom text-primary" href="index.html">Our Lady of Fatima Vocational Training Centre</a>, All Rights Reserved.</div>
                    <div class="col-md-6 text-center text-md-end">
                        <div class="footer-menu">
                            <a class="text-light" href="index.html">Home</a>
                            <a class="text-light" href="contact.php">Help</a>
                            <a class="text-light" href="faq.html">FAQ</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer End -->
    <script>
    (function(){
        const btn=document.getElementById('nl-btn'),inp=document.getElementById('nl-email'),msg=document.getElementById('nl-msg'),hp=document.getElementById('nl-hp');
        if(!btn) return;
        btn.addEventListener('click',async function(){
            const email=(inp.value||'').trim();
            if(!email){show('Please enter your email address.','text-warning');return;}
            btn.disabled=true;btn.textContent='...';
            try{
                const r=await fetch('newsletter.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'email='+encodeURIComponent(email)+'&hp='+encodeURIComponent(hp.value)});
                const d=await r.json();
                show(d.message,d.success?'text-success':'text-danger');
                if(d.success) inp.value='';
            }catch(e){show('Something went wrong. Please try again.','text-danger');}
            finally{btn.disabled=false;btn.textContent='Sign Up';}
        });
        function show(t,c){msg.className='mt-2 small '+c;msg.textContent=t;msg.style.display='block';}
    })();
    </script>
