/* ==========================================================================
   ROTARY SCHOOL URAN — Admin Dashboard JS
   ==========================================================================
   IMPORTANT — READ THIS BEFORE WIRING UP THE REAL BACKEND (Phase 2/3):

   Everything in `updatesStore` below is MOCK DATA standing in for rows that
   will eventually come from MySQL via a PHP endpoint, e.g.:

       GET /api/admin/recent_updates.php?limit=10
       -> [{ id, type, title, description, thumb, timestamp, admin, status }, ...]

   To go live, replace `fetchRecentUpdates()` near the bottom of this file
   with a real `fetch('/api/admin/recent_updates.php?limit=10')` call that
   resolves to the same shape, and call `renderRecentUpdates()` again inside
   every admin form's "on save success" handler (gallery upload, news save,
   notice save, event save, homepage save, announcement save, slider save)
   — that's the "auto-refresh after every successful update" requirement.
   Nothing else in this file needs to change.
   ========================================================================== */
(function () {
  "use strict";

  const TYPE_META = {
    gallery:      { icon: "&#128247;", label: "Gallery" },
    news:         { icon: "&#128240;", label: "News" },
    notice:       { icon: "&#128203;", label: "Notice" },
    event:        { icon: "&#128197;", label: "Event" },
    homepage:     { icon: "&#127968;", label: "Homepage" },
    announcement: { icon: "&#128226;", label: "Announcement" },
    banner:       { icon: "&#127916;", label: "Banner / Slider" },
  };

  const ADMINS = [
    { name: "Priya Deshmukh", avatar: "https://i.pravatar.cc/64?img=47" },
    { name: "Rahul Kadam", avatar: "https://i.pravatar.cc/64?img=12" },
    { name: "Office Admin", avatar: "https://i.pravatar.cc/64?img=33" },
  ];

  function minutesAgo(m) { return new Date(Date.now() - m * 60000).toISOString(); }

  // ---- MOCK DATA (stand-in for a DB table `admin_updates`) ----------------
  let updatesStore = [
    { id: 21, type: "news", title: "SSC board result 2026 announced", description: "Published the results announcement with topper highlights for the homepage news section.", thumb: null, timestamp: minutesAgo(6), admin: ADMINS[0], status: "published" },
    { id: 20, type: "gallery", title: "Annual Day Gathering — 14 new photos", description: "Uploaded photos from this year's Annual Day performances to the Gallery.", thumb: "https://i.pravatar.cc/120?img=5", timestamp: minutesAgo(38), admin: ADMINS[1], status: "published" },
    { id: 19, type: "event", title: "Annual Sports Meet & Yoga Day", description: "Created a new event listing with schedule and registration details.", thumb: null, timestamp: minutesAgo(75), admin: ADMINS[2], status: "draft" },
    { id: 18, type: "notice", title: "Half-day on account of Gram Panchayat elections", description: "Added a notice for parents regarding the revised school timing.", thumb: null, timestamp: minutesAgo(130), admin: ADMINS[0], status: "published" },
    { id: 17, type: "banner", title: "Admissions 2026–27 hero banner", description: "Replaced the homepage hero banner with the new admissions-open creative.", thumb: "https://i.pravatar.cc/120?img=15", timestamp: minutesAgo(200), admin: ADMINS[1], status: "updated" },
    { id: 16, type: "homepage", title: "Updated Welcome section copy", description: "Edited the homepage welcome paragraph and refreshed the supporting photo.", thumb: null, timestamp: minutesAgo(320), admin: ADMINS[2], status: "updated" },
    { id: 15, type: "announcement", title: "Fee payment deadline extended", description: "Changed the site-wide announcement banner text and expiry date.", thumb: null, timestamp: minutesAgo(410), admin: ADMINS[0], status: "published" },
    { id: 14, type: "gallery", title: "Science Exhibition — album published", description: "Created a new album and published 9 photos from the Science Exhibition.", thumb: "https://i.pravatar.cc/120?img=22", timestamp: minutesAgo(540), admin: ADMINS[1], status: "published" },
    { id: 13, type: "news", title: "New computer lab equipment installed", description: "Drafted a news post about the upgraded computer lab — pending review.", thumb: null, timestamp: minutesAgo(700), admin: ADMINS[2], status: "draft" },
    { id: 12, type: "event", title: "Farewell Day — Std 12", description: "Updated the venue and time for the Junior College farewell event.", thumb: null, timestamp: minutesAgo(920), admin: ADMINS[0], status: "updated" },
    { id: 11, type: "notice", title: "Parent-teacher meeting schedule", description: "Published the PTM schedule notice for all grades.", thumb: null, timestamp: minutesAgo(1300), admin: ADMINS[1], status: "published" },
    { id: 10, type: "banner", title: "Republic Day celebration slider", description: "Added a new slide to the homepage image slider.", thumb: "https://i.pravatar.cc/120?img=9", timestamp: minutesAgo(1600), admin: ADMINS[2], status: "published" },
  ];
  let nextId = 22;

  function timeAgo(iso) {
    const diffMs = Date.now() - new Date(iso).getTime();
    const mins = Math.round(diffMs / 60000);
    if (mins < 1) return "just now";
    if (mins < 60) return mins + " min ago";
    const hrs = Math.round(mins / 60);
    if (hrs < 24) return hrs + " hr" + (hrs > 1 ? "s" : "") + " ago";
    const days = Math.round(hrs / 24);
    if (days < 7) return days + " day" + (days > 1 ? "s" : "") + " ago";
    return new Date(iso).toLocaleDateString("en-IN", { day: "numeric", month: "short", year: "numeric" });
  }

  function fullDateTime(iso) {
    return new Date(iso).toLocaleString("en-IN", { day: "numeric", month: "short", year: "numeric", hour: "2-digit", minute: "2-digit" });
  }

  function updateItemHTML(u, isNew) {
    const meta = TYPE_META[u.type] || { icon: "&#128196;", label: u.type };
    const thumbHTML = u.thumb
      ? `<div class="update-thumb"><img src="${u.thumb}" alt=""></div>`
      : "";
    return `
      <div class="update-item${isNew ? " is-new" : ""}">
        <div class="update-ic type-${u.type}">${meta.icon}</div>
        <div class="update-body">
          <div class="update-body-top"><span class="update-type-label">${meta.label}</span></div>
          <div class="update-title">${u.title}</div>
          <p class="update-desc">${u.description}</p>
          <div class="update-meta">
            <span class="who"><img src="${u.admin.avatar}" alt="">${u.admin.name}</span>
            <span title="${fullDateTime(u.timestamp)}">&#128337; ${timeAgo(u.timestamp)}</span>
          </div>
        </div>
        <div class="update-right">
          <span class="status-badge ${u.status}">${u.status}</span>
          ${thumbHTML}
        </div>
      </div>`;
  }

  // Simulates GET /api/admin/recent_updates.php?limit=N
  function fetchRecentUpdates(limit) {
    const sorted = [...updatesStore].sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
    return sorted.slice(0, limit);
  }

  function renderRecentUpdates(highlightId) {
    const list = document.querySelector("#recentUpdatesList");
    if (!list) return;
    const items = fetchRecentUpdates(10);
    if (items.length === 0) {
      list.innerHTML = "";
      document.querySelector("#recentUpdatesEmpty").style.display = "block";
      return;
    }
    document.querySelector("#recentUpdatesEmpty").style.display = "none";
    list.innerHTML = items.map(u => updateItemHTML(u, u.id === highlightId)).join("");
  }

  function renderAllUpdates(filterType) {
    const list = document.querySelector("#allUpdatesList");
    if (!list) return;
    let items = [...updatesStore].sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
    if (filterType && filterType !== "all") {
      items = items.filter(u => u.type === filterType);
    }
    const emptyEl = document.querySelector("#allUpdatesEmpty");
    if (items.length === 0) {
      list.innerHTML = "";
      if (emptyEl) emptyEl.style.display = "block";
      return;
    }
    if (emptyEl) emptyEl.style.display = "none";
    list.innerHTML = items.map(u => updateItemHTML(u, false)).join("");
  }

  // Adds a new update to the top of the feed and re-renders — call this from
  // every admin "save" success handler once the real backend exists.
  function addUpdate(partial) {
    const admin = ADMINS[Math.floor(Math.random() * ADMINS.length)];
    const entry = Object.assign({
      id: nextId++,
      timestamp: new Date().toISOString(),
      admin,
      status: "published",
      thumb: null,
    }, partial);
    updatesStore.unshift(entry);
    renderRecentUpdates(entry.id);
    const item = document.querySelector(".update-item.is-new");
    if (item) setTimeout(() => item.classList.remove("is-new"), 3000);
  }

  // ---- demo controls (dashboard only) — remove once backend is wired up --
  document.querySelectorAll("[data-simulate]").forEach(btn => {
    btn.addEventListener("click", () => {
      const type = btn.getAttribute("data-simulate");
      const samples = {
        gallery: { title: "Tree Plantation Drive — 8 new photos", description: "Uploaded new photos to the Gallery under Environment.", thumb: "https://i.pravatar.cc/120?img=31" },
        news: { title: "HSC board result 2026 announced", description: "Published a news update celebrating this year's HSC results." },
        notice: { title: "School closed for local holiday", description: "Added a notice informing parents of a one-day closure." },
        event: { title: "Chhatrapati Shivaji Maharaj Jayanti", description: "Created a new event listing for the cultural celebration." },
        homepage: { title: "Updated admission counter numbers", description: "Refreshed the student/teacher counter figures on the homepage." },
        announcement: { title: "New admission helpline number added", description: "Updated the site-wide announcement banner." },
        banner: { title: "New homepage slider image", description: "Uploaded a new slide to the homepage hero slider.", thumb: "https://i.pravatar.cc/120?img=41" },
      };
      addUpdate(Object.assign({ type, status: "published" }, samples[type]));
      const feedback = document.querySelector("#simulateFeedback");
      if (feedback) {
        feedback.textContent = "Added a new \"" + (TYPE_META[type] ? TYPE_META[type].label : type) + "\" update — see it at the top of the feed.";
        feedback.style.opacity = "1";
        clearTimeout(feedback._t);
        feedback._t = setTimeout(() => { feedback.style.opacity = "0"; }, 3200);
      }
    });
  });

  const refreshBtn = document.querySelector("#refreshUpdatesBtn");
  if (refreshBtn) {
    refreshBtn.addEventListener("click", () => {
      refreshBtn.classList.add("spinning");
      setTimeout(() => {
        renderRecentUpdates();
        refreshBtn.classList.remove("spinning");
      }, 450);
    });
  }

  // filter buttons on the "all updates" page
  document.querySelectorAll(".updates-filter button").forEach(btn => {
    btn.addEventListener("click", () => {
      document.querySelectorAll(".updates-filter button").forEach(b => b.classList.remove("active"));
      btn.classList.add("active");
      renderAllUpdates(btn.getAttribute("data-filter"));
    });
  });

  if (document.querySelector("#recentUpdatesList")) renderRecentUpdates();
  if (document.querySelector("#allUpdatesList")) renderAllUpdates("all");

  // expose for console/demo use
  window.__adminUpdates = { addUpdate, renderRecentUpdates };
})();
