/* ============================================================
   Blind School Dahod — global interactions (vanilla JS)
   Accessibility-first. No external dependencies.
   ============================================================ */
(() => {
  "use strict";
  const $  = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => [...c.querySelectorAll(s)];
  const prefersReduced = matchMedia("(prefers-reduced-motion: reduce)").matches;

  /* ---------------- Toasts ---------------- */
  function toast(msg, type = "ok", ms = 3800) {
    let wrap = $(".toast-wrap");
    if (!wrap) { wrap = document.createElement("div"); wrap.className = "toast-wrap"; document.body.appendChild(wrap); }
    const t = document.createElement("div");
    t.className = `toast ${type}`;
    t.setAttribute("role", "status");
    t.textContent = msg;
    wrap.appendChild(t);
    setTimeout(() => { t.style.opacity = "0"; t.style.transition = "opacity .4s"; setTimeout(() => t.remove(), 400); }, ms);
  }
  window.bsdToast = toast;

  /* ---------------- Scroll reveal + stagger ---------------- */
  function initReveal() {
    const els = $$(".reveal");
    if (!("IntersectionObserver" in window) || prefersReduced) {
      els.forEach(e => e.classList.add("is-visible"));
      return;
    }
    const io = new IntersectionObserver((entries) => {
      entries.forEach(en => {
        if (en.isIntersecting) { en.target.classList.add("is-visible"); io.unobserve(en.target); }
      });
    }, { threshold: 0.12, rootMargin: "0px 0px -8% 0px" });
    els.forEach(e => io.observe(e));
    $$("[data-stagger]").forEach(group => {
      [...group.children].forEach((c, i) => c.style.setProperty("--i", i));
    });
  }

  /* ---------------- Animated counters ---------------- */
  function animateCounter(el) {
    const target = parseFloat(el.dataset.counter || "0");
    const dur = parseInt(el.dataset.duration || "1800", 10);
    const dec = parseInt(el.dataset.decimals || "0", 10);
    const prefix = el.dataset.prefix || "";
    const suffix = el.dataset.suffix || "";
    if (prefersReduced) { el.textContent = prefix + target.toLocaleString("en-IN", { maximumFractionDigits: dec }) + suffix; return; }
    const start = performance.now();
    const ease = t => 1 - Math.pow(1 - t, 3);
    function step(now) {
      const p = Math.min(1, (now - start) / dur);
      const val = target * ease(p);
      el.textContent = prefix + val.toLocaleString("en-IN", { minimumFractionDigits: dec, maximumFractionDigits: dec }) + suffix;
      if (p < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }
  function initCounters() {
    const els = $$("[data-counter]");
    if (!("IntersectionObserver" in window)) { els.forEach(animateCounter); return; }
    const io = new IntersectionObserver((entries) => {
      entries.forEach(en => { if (en.isIntersecting) { animateCounter(en.target); io.unobserve(en.target); } });
    }, { threshold: 0.4 });
    els.forEach(e => io.observe(e));
  }

  /* ---------------- Parallax ---------------- */
  function initParallax() {
    const els = $$("[data-parallax]");
    if (!els.length || prefersReduced) return;
    let ticking = false;
    function update() {
      const vh = innerHeight;
      els.forEach(el => {
        const speed = parseFloat(el.dataset.parallax || "0.2");
        const r = el.getBoundingClientRect();
        const offset = (r.top + r.height / 2 - vh / 2) * -speed;
        el.style.transform = `translate3d(0, ${offset.toFixed(1)}px, 0)`;
      });
      ticking = false;
    }
    addEventListener("scroll", () => { if (!ticking) { requestAnimationFrame(update); ticking = true; } }, { passive: true });
    update();
  }

  /* ---------------- Scroll progress + header + back-to-top ---------------- */
  function initScrollUI() {
    const bar = $(".progress-bar");
    const header = $(".site-header");
    const top = $(".fab-top");
    function onScroll() {
      const sc = scrollY;
      const h = document.documentElement.scrollHeight - innerHeight;
      if (bar) bar.style.width = (h > 0 ? (sc / h) * 100 : 0) + "%";
      if (header) header.classList.toggle("scrolled", sc > 20);
      if (top) top.classList.toggle("show", sc > 500);
    }
    addEventListener("scroll", onScroll, { passive: true });
    onScroll();
    top && top.addEventListener("click", () => scrollTo({ top: 0, behavior: prefersReduced ? "auto" : "smooth" }));
  }

  /* ---------------- 3D tilt ---------------- */
  function initTilt() {
    if (matchMedia("(pointer: coarse)").matches || prefersReduced) return;
    $$("[data-tilt]").forEach(card => {
      const max = 10;
      card.addEventListener("mousemove", e => {
        const r = card.getBoundingClientRect();
        const px = (e.clientX - r.left) / r.width - 0.5;
        const py = (e.clientY - r.top) / r.height - 0.5;
        card.style.setProperty("--ry", (px * max) + "deg");
        card.style.setProperty("--rx", (-py * max) + "deg");
      });
      card.addEventListener("mouseleave", () => {
        card.style.setProperty("--ry", "0deg");
        card.style.setProperty("--rx", "0deg");
      });
    });
  }

  /* ---------------- Mobile menu ---------------- */
  function initMenu() {
    const btn = $(".menu-toggle");
    const menu = $("#mobileMenu");
    if (!btn || !menu) return;
    btn.addEventListener("click", () => {
      const open = menu.hasAttribute("hidden") ? false : true;
      if (open) { menu.setAttribute("hidden", ""); btn.setAttribute("aria-expanded", "false"); }
      else { menu.removeAttribute("hidden"); btn.setAttribute("aria-expanded", "true"); }
    });
    $$("#mobileMenu a").forEach(a => a.addEventListener("click", () => { menu.setAttribute("hidden", ""); btn.setAttribute("aria-expanded", "false"); }));
  }

  /* ---------------- Accessibility toolbar ---------------- */
  const A11Y = {
    get() { try { return JSON.parse(localStorage.getItem("bsd_a11y") || "{}"); } catch { return {}; } },
    set(s) { localStorage.setItem("bsd_a11y", JSON.stringify(s)); },
  };
  function applyA11y(s) {
    const root = document.documentElement;
    root.style.setProperty("--fs", String(s.fontScale || 1));
    if (s.contrast) root.setAttribute("data-contrast", "high"); else root.removeAttribute("data-contrast");
    if (s.motionOff) root.setAttribute("data-motion", "off"); else root.removeAttribute("data-motion");
    $("#a11yContrast")?.setAttribute("aria-pressed", s.contrast ? "true" : "false");
    $("#a11yMotion")?.setAttribute("aria-pressed", s.motionOff ? "true" : "false");
  }
  function initA11y() {
    const s = A11Y.get();
    s.fontScale = s.fontScale || 1;
    applyA11y(s);
    const save = () => { A11Y.set(s); applyA11y(s); };

    $("#a11yInc")?.addEventListener("click", () => { s.fontScale = Math.min(1.5, (s.fontScale || 1) + 0.1); save(); toast(`Text size ${Math.round(s.fontScale*100)}%`); });
    $("#a11yDec")?.addEventListener("click", () => { s.fontScale = Math.max(0.85, (s.fontScale || 1) - 0.1); save(); toast(`Text size ${Math.round(s.fontScale*100)}%`); });
    $("#a11yContrast")?.addEventListener("click", () => { s.contrast = !s.contrast; save(); toast(s.contrast ? "High contrast on" : "High contrast off"); });
    $("#a11yMotion")?.addEventListener("click", () => { s.motionOff = !s.motionOff; save(); toast(s.motionOff ? "Animations paused" : "Animations on"); });
    $("#a11yReset")?.addEventListener("click", () => { ["fontScale","contrast","motionOff"].forEach(k => delete s[k]); s.fontScale = 1; save(); toast("Accessibility reset"); });
  }

  /* ---------------- Listen to this page (Web Speech) ---------------- */
  function initSpeech() {
    const btns = $$("[data-speak]");
    if (!btns.length) return;
    const synth = window.speechSynthesis;
    if (!synth) { btns.forEach(b => b.style.display = "none"); return; }
    let speaking = false, nodes = [], idx = 0;

    function collect() {
      const main = $("main") || document.body;
      return $$("h1,h2,h3,h4,p,li,blockquote,figcaption,.speakable", main)
        .filter(el => el.offsetParent !== null && el.textContent.trim().length > 1 && !el.closest("[data-no-speak]"));
    }
    function clearHL() { nodes.forEach(n => n.classList.remove("tts-active")); }
    function stop() { synth.cancel(); speaking = false; clearHL(); setBtns(false); }
    function setBtns(on) { btns.forEach(b => { b.setAttribute("aria-pressed", on ? "true" : "false"); b.querySelector(".speak-label") && (b.querySelector(".speak-label").textContent = on ? "Stop" : "Listen"); }); }
    function speakNext() {
      if (idx >= nodes.length) { stop(); return; }
      const el = nodes[idx];
      clearHL(); el.classList.add("tts-active");
      el.scrollIntoView({ block: "center", behavior: prefersReduced ? "auto" : "smooth" });
      const u = new SpeechSynthesisUtterance(el.textContent.trim());
      u.lang = "en-IN"; u.rate = 1; u.pitch = 1;
      u.onend = () => { idx++; if (speaking) speakNext(); };
      u.onerror = () => { idx++; if (speaking) speakNext(); };
      synth.speak(u);
    }
    btns.forEach(b => b.addEventListener("click", () => {
      if (speaking) { stop(); return; }
      nodes = collect(); idx = 0;
      if (!nodes.length) { toast("Nothing to read here", "err"); return; }
      speaking = true; setBtns(true); speakNext();
      toast("Reading page aloud…");
    }));
    addEventListener("beforeunload", stop);
    document.addEventListener("keydown", e => { if (e.key === "Escape" && speaking) stop(); });
  }

  /* ---------------- Cookie consent ---------------- */
  function initCookie() {
    const el = $("#cookieBanner");
    if (!el) return;
    if (localStorage.getItem("bsd_cookie") === "1") return;
    setTimeout(() => el.classList.add("show"), 1200);
    $("#cookieAccept")?.addEventListener("click", () => { localStorage.setItem("bsd_cookie", "1"); el.classList.remove("show"); });
  }

  /* ---------------- Lightbox (gallery) ---------------- */
  function initLightbox() {
    const lb = $("#lightbox");
    if (!lb) return;
    const img = $("#lightboxImg"), cap = $("#lightboxCap");
    function open(src, caption) { img.src = src; cap.textContent = caption || ""; lb.classList.add("open"); lb.setAttribute("aria-hidden","false"); document.body.style.overflow = "hidden"; }
    function close() { lb.classList.remove("open"); lb.setAttribute("aria-hidden","true"); img.src = ""; document.body.style.overflow = ""; }
    document.addEventListener("click", e => {
      const t = e.target.closest("[data-lightbox]");
      if (t) { e.preventDefault(); open(t.dataset.lightbox, t.dataset.caption); }
      else if (e.target === lb || e.target.closest("[data-lb-close]")) close();
    });
    document.addEventListener("keydown", e => { if (e.key === "Escape") close(); });
  }

  /* ---------------- Modal (QR donate etc.) ---------------- */
  function initModals() {
    document.addEventListener("click", e => {
      const open = e.target.closest("[data-modal-open]");
      if (open) { e.preventDefault(); const m = $("#" + open.dataset.modalOpen); if (m) { m.classList.add("open"); m.setAttribute("aria-hidden","false"); document.body.style.overflow="hidden"; } }
      const close = e.target.closest("[data-modal-close]");
      if (close || e.target.classList.contains("modal-backdrop")) {
        const m = close ? close.closest(".modal-backdrop") : e.target;
        if (m) { m.classList.remove("open"); m.setAttribute("aria-hidden","true"); document.body.style.overflow=""; }
      }
    });
    document.addEventListener("keydown", e => { if (e.key === "Escape") $$(".modal-backdrop.open").forEach(m => { m.classList.remove("open"); document.body.style.overflow=""; }); });
  }

  /* ---------------- Sliders (testimonials + home images) ---------------- */
  function initSlider() {
    $$("[data-slider]").forEach(root => {
      const track = $(".slider-track", root);
      if (!track) return;
      const slides = $$(".slide", track);
      if (!slides.length) return;
      const dots = $$(".slider-dot", root);
      const interval = parseInt(root.getAttribute("data-interval") || "5500", 10);
      let i = 0, timer;
      function go(n) {
        i = (n + slides.length) % slides.length;
        track.style.transform = `translateX(-${i * 100}%)`;
        dots.forEach((d, k) => d.setAttribute("aria-current", k === i ? "true" : "false"));
      }
      function auto() { stop(); if (prefersReduced || slides.length < 2) return; timer = setInterval(() => go(i + 1), interval); }
      function stop() { clearInterval(timer); }
      $("[data-slider-next]", root)?.addEventListener("click", () => { go(i + 1); auto(); });
      $("[data-slider-prev]", root)?.addEventListener("click", () => { go(i - 1); auto(); });
      dots.forEach((d, k) => d.addEventListener("click", () => { go(k); auto(); }));
      root.addEventListener("mouseenter", stop);
      root.addEventListener("mouseleave", auto);
      go(0); auto();
    });
  }

  /* ---------------- AJAX forms (contact / donation feed) ---------------- */
  function initAjaxForms() {
    $$("form[data-ajax]").forEach(form => {
      form.addEventListener("submit", async e => {
        e.preventDefault();
        // honeypot
        const hp = form.querySelector('input[name="website"]');
        if (hp && hp.value) return;
        const btn = form.querySelector('[type="submit"]');
        const orig = btn ? btn.innerHTML : "";
        if (btn) { btn.disabled = true; btn.innerHTML = "Sending…"; }
        try {
          const res = await fetch(form.action, { method: "POST", body: new FormData(form), headers: { "X-Requested-With": "fetch" } });
          const data = await res.json();
          if (data.ok) {
            toast(data.message || "Done!", "ok");
            form.reset();
            if (form.dataset.success) { const cb = window[form.dataset.success]; typeof cb === "function" && cb(data); }
          } else {
            toast(data.message || "Something went wrong", "err");
          }
        } catch { toast("Network error. Please try again.", "err"); }
        finally { if (btn) { btn.disabled = false; btn.innerHTML = orig; } }
      });
    });
  }

  /* ---------------- Lazy reveal of skeletons / images ---------------- */
  function initLazy() {
    $$("img[loading]:not([loading='eager'])").forEach(img => {
      if (img.complete) return;
      img.style.opacity = "0"; img.style.transition = "opacity .5s";
      img.addEventListener("load", () => { img.style.opacity = "1"; }, { once: true });
      img.addEventListener("error", () => { img.style.opacity = "1"; }, { once: true });
    });
  }

  /* ---------------- Init ---------------- */
  function init() {
    initReveal(); initCounters(); initParallax(); initScrollUI(); initTilt();
    initMenu(); initA11y(); initSpeech(); initCookie();
    initLightbox(); initModals(); initSlider(); initAjaxForms(); initLazy();
  }
  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", init);
  else init();

  /* PWA service worker */
  if ("serviceWorker" in navigator) {
    addEventListener("load", () => navigator.serviceWorker.register("/sw.js").catch(() => {}));
  }
})();
