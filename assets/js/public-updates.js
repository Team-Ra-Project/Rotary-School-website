/* ==========================================================================
   ROTARY SCHOOL URAN — public Recent Updates feed
   ==========================================================================
   Public-facing version of the admin "Recent Updates" card: same visual
   language, but no admin name/avatar or Draft/Updated status — visitors
   only ever see published content.

   Live: reads from the PHP/MySQL backend via
       GET api/updates.php?limit=N
   which only ever returns rows where status = "published". Both the Home
   page and the News page call this same endpoint, so anything the admin
   adds, edits, publishes, hides, or deletes shows up on both automatically
   — no manual edits needed.
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
    banner:       { icon: "&#127916;", label: "Banner" },
  };

  function timeAgo(iso) {
    const days = Math.round((Date.now() - new Date(iso).getTime()) / 86400000);
    if (days < 1) return "today";
    if (days === 1) return "yesterday";
    if (days < 7) return days + " days ago";
    return new Date(iso).toLocaleDateString("en-IN", { day: "numeric", month: "short" });
  }

  function escapeHTML(str) {
    const div = document.createElement("div");
    div.textContent = str == null ? "" : String(str);
    return div.innerHTML;
  }

  function publicItemHTML(u) {
    const meta = TYPE_META[u.type] || { icon: "&#128196;", label: u.type };
    const thumbHTML = u.thumb
      ? `<div class="update-media-full"><img src="${escapeHTML(u.thumb)}" alt="${escapeHTML(u.title)}"></div>`
      : "";
    return `
      <a class="update-item${u.thumb ? " has-media" : ""}" href="${escapeHTML(u.link)}" style="text-decoration:none;color:inherit">
        <div class="update-ic type-${escapeHTML(u.type)}">${meta.icon}</div>
        <div class="update-body">
          <div class="update-body-top"><span class="update-type-label">${meta.label}</span></div>
          <div class="update-title">${escapeHTML(u.title)}</div>
          <p class="update-desc">${escapeHTML(u.description)}</p>
          <div class="update-meta"><span>&#128337; ${timeAgo(u.timestamp)}</span></div>
          ${thumbHTML}
        </div>
      </a>`;
  }

  function openLightbox(src, alt) {
    const lightbox = document.querySelector(".lightbox");
    if (!lightbox) return;
    const img = lightbox.querySelector("img");
    if (img) {
      img.src = src;
      img.alt = alt || "";
    }
    lightbox.classList.add("open");
  }

  // The .lightbox element (and its close button/backdrop click handling) already
  // exists in the page HTML and is wired up by main.js on page load — we just
  // reuse that same element here, the same way the Gallery page does.
  function wireLightboxClicks(list) {
    list.querySelectorAll(".update-media-full img").forEach(img => {
      img.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        openLightbox(img.src, img.alt);
      });
    });
  }

  async function getPublicUpdates(limit) {
    const res = await fetch("api/updates.php?limit=" + encodeURIComponent(limit || 8), {
      headers: { "Accept": "application/json" },
    });
    if (!res.ok) throw new Error("Failed to load updates");
    return res.json();
  }

  async function renderPublicUpdates(containerId, emptyId, limit) {
    const list = document.querySelector("#" + containerId);
    if (!list) return;
    const emptyEl = emptyId ? document.querySelector("#" + emptyId) : null;

    try {
      const items = await getPublicUpdates(limit);
      if (!items || items.length === 0) {
        list.innerHTML = "";
        if (emptyEl) emptyEl.style.display = "block";
        return;
      }
      if (emptyEl) emptyEl.style.display = "none";
      list.innerHTML = items.map(publicItemHTML).join("");
      wireLightboxClicks(list);
    } catch (err) {
      // Fail quietly on the public site — just show the existing empty state.
      list.innerHTML = "";
      if (emptyEl) emptyEl.style.display = "block";
    }
  }

  document.querySelectorAll("[data-public-updates]").forEach(el => {
    const limit = parseInt(el.getAttribute("data-limit") || "6", 10);
    const emptyId = el.getAttribute("data-empty") || null;
    renderPublicUpdates(el.id, emptyId, limit);
  });
})();