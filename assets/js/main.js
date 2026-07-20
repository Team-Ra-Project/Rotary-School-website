/* ==========================================================================
   ROTARY SCHOOL URAN — main.js
   Handles: preloader, sticky nav, mobile menu, smooth scroll, back-to-top,
   scroll reveals, counters, FAQ accordion, gallery filter + lightbox,
   testimonial slider, donation amount selector, newsletter/forms.
   ========================================================================== */
(function(){
  "use strict";

  /* ---------- Preloader ---------- */
  window.addEventListener("load", function(){
    var pre = document.getElementById("preloader");
    if(pre){ setTimeout(function(){ pre.classList.add("hide"); }, 350); }
  });

  /* ---------- Sticky navbar ---------- */
  var navbar = document.querySelector(".navbar");
  function onScrollNav(){
    if(!navbar) return;
    if(window.scrollY > 30){ navbar.classList.add("scrolled"); }
    else{ navbar.classList.remove("scrolled"); }
  }
  document.addEventListener("scroll", onScrollNav, {passive:true});
  onScrollNav();

  /* ---------- Mobile menu ---------- */
  var hamburger = document.querySelector(".hamburger");
  var overlay = document.querySelector(".mobile-overlay");
  var closeBtn = document.querySelector(".mobile-close");
  function openMenu(){ overlay && overlay.classList.add("open"); document.body.style.overflow="hidden"; }
  function closeMenu(){ overlay && overlay.classList.remove("open"); document.body.style.overflow=""; }
  hamburger && hamburger.addEventListener("click", openMenu);
  closeBtn && closeBtn.addEventListener("click", closeMenu);
  overlay && overlay.addEventListener("click", function(e){ if(e.target === overlay) closeMenu(); });

  document.querySelectorAll(".mobile-sub-toggle").forEach(function(btn){
    btn.addEventListener("click", function(){
      var sub = btn.parentElement.querySelector(".mobile-sub");
      sub && sub.classList.toggle("open");
      btn.classList.toggle("open");
    });
  });

  /* ---------- Back to top ---------- */
  var backBtn = document.querySelector(".back-to-top");
  function onScrollTop(){
    if(!backBtn) return;
    if(window.scrollY > 500){ backBtn.classList.add("show"); } else { backBtn.classList.remove("show"); }
  }
  document.addEventListener("scroll", onScrollTop, {passive:true});
  backBtn && backBtn.addEventListener("click", function(){ window.scrollTo({top:0,behavior:"smooth"}); });
  onScrollTop();

  /* ---------- Scroll reveal ---------- */
  var revealEls = document.querySelectorAll(".reveal, .reveal-scale");
  if("IntersectionObserver" in window){
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if(entry.isIntersecting){ entry.target.classList.add("in"); io.unobserve(entry.target); }
      });
    }, {threshold:0.15, rootMargin:"0px 0px -60px 0px"});
    revealEls.forEach(function(el, i){ el.style.setProperty("--i", i % 8); io.observe(el); });
  } else {
    revealEls.forEach(function(el){ el.classList.add("in"); });
  }

  /* ---------- Counters ---------- */
  var counters = document.querySelectorAll("[data-count]");
  function animateCounter(el){
    var target = parseFloat(el.getAttribute("data-count"));
    var suffix = el.getAttribute("data-suffix") || "";
    var duration = 1400, start = null;
    function step(ts){
      if(!start) start = ts;
      var progress = Math.min((ts - start) / duration, 1);
      var eased = 1 - Math.pow(1 - progress, 3);
      var value = Math.floor(eased * target);
      el.textContent = value.toLocaleString() + suffix;
      if(progress < 1){ requestAnimationFrame(step); } else { el.textContent = target.toLocaleString() + suffix; }
    }
    requestAnimationFrame(step);
  }
  if(counters.length && "IntersectionObserver" in window){
    var cio = new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if(entry.isIntersecting){ animateCounter(entry.target); cio.unobserve(entry.target); }
      });
    }, {threshold:0.4});
    counters.forEach(function(el){ cio.observe(el); });
  }

  /* ---------- FAQ accordion ---------- */
  document.querySelectorAll(".faq-item").forEach(function(item){
    var q = item.querySelector(".faq-q");
    var a = item.querySelector(".faq-a");
    q && q.addEventListener("click", function(){
      var wasOpen = item.classList.contains("open");
      document.querySelectorAll(".faq-item.open").forEach(function(other){
        other.classList.remove("open");
        other.querySelector(".faq-a").style.maxHeight = null;
      });
      if(!wasOpen){
        item.classList.add("open");
        a.style.maxHeight = a.scrollHeight + "px";
      }
    });
  });

  /* ---------- Gallery filter ---------- */
  var filterBtns = document.querySelectorAll(".filter-bar button");
  var galleryItems = document.querySelectorAll(".gallery-grid .g-item");
  filterBtns.forEach(function(btn){
    btn.addEventListener("click", function(){
      filterBtns.forEach(function(b){ b.classList.remove("active"); });
      btn.classList.add("active");
      var cat = btn.getAttribute("data-filter");
      galleryItems.forEach(function(item){
        var show = cat === "all" || item.getAttribute("data-cat") === cat;
        item.style.display = show ? "" : "none";
      });
    });
  });

  /* ---------- Lightbox ---------- */
  var lightbox = document.querySelector(".lightbox");
  var lightboxImg = lightbox ? lightbox.querySelector("img") : null;
  document.querySelectorAll(".gallery-grid .g-item img").forEach(function(img){
    img.addEventListener("click", function(){
      if(!lightbox) return;
      lightboxImg.src = img.src;
      lightbox.classList.add("open");
    });
  });
  document.querySelectorAll(".lightbox-close, .lightbox").forEach(function(el){
    el.addEventListener("click", function(e){
      if(e.target === el){ lightbox.classList.remove("open"); }
    });
  });

  /* ---------- Testimonial slider ---------- */
  var track = document.querySelector(".testi-track");
  if(track){
    var slides = track.children;
    var idx = 0;
    function showSlide(i){
      track.style.transform = "translateX(-" + (i * 100) + "%)";
    }
    document.querySelectorAll(".testi-next").forEach(function(b){ b.addEventListener("click", function(){ idx = (idx + 1) % slides.length; showSlide(idx); }); });
    document.querySelectorAll(".testi-prev").forEach(function(b){ b.addEventListener("click", function(){ idx = (idx - 1 + slides.length) % slides.length; showSlide(idx); }); });
    setInterval(function(){ idx = (idx + 1) % slides.length; showSlide(idx); }, 6000);
  }

  /* ---------- Donation amount selector ---------- */
  document.querySelectorAll(".amt-row .amt").forEach(function(btn){
    btn.addEventListener("click", function(){
      var row = btn.closest(".amt-row");
      row.querySelectorAll(".amt").forEach(function(b){ b.classList.remove("active"); });
      btn.classList.add("active");
      var custom = document.querySelector("#customAmount");
      if(custom) custom.value = btn.getAttribute("data-amt") || "";
    });
  });

  /* ---------- Basic form intercept (static demo — Phase 2 wires PHP) ---------- */
  document.querySelectorAll("form[data-demo-form]").forEach(function(form){
    form.addEventListener("submit", function(e){
      e.preventDefault();
      var note = form.querySelector(".form-note");
      if(note){
        note.textContent = "Thanks — this form will be connected to the school office in Phase 2 (PHP + MySQL backend).";
        note.style.display = "block";
      } else {
        alert("Thanks — this form will be connected to the school office in Phase 2 (PHP + MySQL backend).");
      }
      form.reset();
    });
  });

  /* ---------- Login / Register tabs ---------- */
  var authTabs = document.querySelectorAll(".auth-tabs button");
  authTabs.forEach(function(btn){
    btn.addEventListener("click", function(){
      var target = btn.getAttribute("data-tab");
      authTabs.forEach(function(b){ b.classList.remove("active"); });
      btn.classList.add("active");
      document.querySelectorAll(".auth-panel").forEach(function(p){ p.classList.remove("active"); });
      var panel = document.querySelector('.auth-panel[data-panel="' + target + '"]');
      panel && panel.classList.add("active");
    });
  });
  document.querySelectorAll("[data-switch-auth]").forEach(function(link){
    link.addEventListener("click", function(e){
      e.preventDefault();
      var target = link.getAttribute("data-switch-auth");
      var tabBtn = document.querySelector('.auth-tabs button[data-tab="' + target + '"]');
      tabBtn && tabBtn.click();
    });
  });

  /* ---------- Active nav link highlighting ---------- */
  var current = location.pathname.split("/").pop() || "index.html";
  document.querySelectorAll(".nav-menu a, .mobile-nav a").forEach(function(a){
    var href = a.getAttribute("href");
    if(href === current){ a.closest("li") && a.closest("li").classList.add("active"); a.style.fontWeight="800"; }
  });

})();
