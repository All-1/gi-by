(function () {
  "use strict";

  if (window.__bpKnowledgeTestsMedalsLoaded) {
    return;
  }
  window.__bpKnowledgeTestsMedalsLoaded = true;

  const KNOWLEDGE_TESTS_MEDAL_IMAGE_BASE = "/wp-content/themes/wp-diary/images/";

  const KNOWLEDGE_TESTS_MEDAL_SRC = {
    bronze: KNOWLEDGE_TESTS_MEDAL_IMAGE_BASE + "Bronze.svg",
    silver: KNOWLEDGE_TESTS_MEDAL_IMAGE_BASE + "Silver.svg",
    gold: KNOWLEDGE_TESTS_MEDAL_IMAGE_BASE + "Gold.svg",
  };

  const KNOWLEDGE_TESTS_MEDAL_TIERS = ["bronze", "silver", "gold"];

  function knowledgeTestsRowMedalTier(row) {
    if (!row || typeof row !== "object") {
      return "";
    }
    return String(row.medalTier || row.medal_tier || row.medalValue || row.medal_value || "")
      .toLowerCase()
      .trim();
  }

  function knowledgeTestsNormalizeMedalTier(tierValue) {
    let tier = String(tierValue || "").toLowerCase().trim();
    return KNOWLEDGE_TESTS_MEDAL_TIERS.indexOf(tier) >= 0 ? tier : "";
  }

  function formatKnowledgeTestsMedalLabel(tierValue, medalId) {
    if (tierValue) {
      let labels = { bronze: "Бронза", silver: "Серебро", gold: "Золото", lock: "Закреплён" };
      return labels[String(tierValue).toLowerCase()] || String(tierValue);
    }
    if (medalId == null || medalId === "") {
      return "—";
    }
    return "★ " + medalId;
  }

  function knowledgeTestsMedalAreaFromRow(row) {
    if (!row) {
      return "";
    }
    return String(row.achievementArea || row.achievement_area || row.title || "").trim();
  }

  function knowledgeTestsMedalRankFromRow(row, tierRaw) {
    if (row && row.medalRankName) {
      return String(row.medalRankName).trim();
    }
    if (row && row.medal_rank_name) {
      return String(row.medal_rank_name).trim();
    }
    let tier = String(tierRaw || "").toLowerCase().trim();
    if (
      window.KNOWLEDGE_TESTS_RANK_BY_TIER &&
      window.KNOWLEDGE_TESTS_RANK_BY_TIER[tier]
    ) {
      return String(window.KNOWLEDGE_TESTS_RANK_BY_TIER[tier]).trim();
    }
    return "";
  }

  function appendKnowledgeTestsMedalHover(badge, rankName, achievementArea) {
    let rank = String(rankName || "").trim();
    let area = String(achievementArea || "").trim();
    if (!rank && !area) {
      return;
    }
    let hover = document.createElement("span");
    hover.className = "knowledge_tests_medal_hover";
    hover.setAttribute("role", "tooltip");
    let rankEl = document.createElement("span");
    rankEl.className = "knowledge_tests_medal_hover_rank";
    rankEl.textContent = rankName || "";
    let areaEl = document.createElement("span");
    areaEl.className = "knowledge_tests_medal_hover_area";
    areaEl.textContent = achievementArea || "";
    massAppendChild(hover, rank ? rankEl : null, area ? areaEl : null);
    badge.appendChild(hover);
  }

  /**
   * @param {{ tierValue?: string, rankName?: string, achievementArea?: string, size?: 'sm'|'md', showHover?: boolean }} opts
   */
  function createKnowledgeTestsMedalBadge(opts) {
    opts = opts || {};
    let tierRaw = String(opts.tierValue || "").toLowerCase().trim();
    let imgTier = knowledgeTestsNormalizeMedalTier(tierRaw);
    let rankName = String(opts.rankName || "").trim();
    let area = String(opts.achievementArea || opts.area || "").trim();
    let size = opts.size === "sm" ? "sm" : "md";
    let badge = document.createElement("span");
    badge.className =
      "knowledge_tests_medal_badge knowledge_tests_medal_badge--" +
      size +
      (imgTier ? " knowledge_tests_medal_badge--" + imgTier : " knowledge_tests_medal_badge--lock");

    if (imgTier) {
      let img = document.createElement("img");
      img.src = KNOWLEDGE_TESTS_MEDAL_SRC[imgTier];
      img.className = "knowledge_tests_medal_icon";
      img.width = size === "sm" ? 18 : 32;
      img.height = size === "sm" ? 18 : 32;
      img.alt = rankName && area ? rankName + " — " + area : rankName || area || "";
      badge.appendChild(img);
    } else if (tierRaw === "lock") {
      let lockEl = document.createElement("span");
      lockEl.className = "knowledge_tests_medal_lock_label";
      lockEl.textContent = formatKnowledgeTestsMedalLabel("lock", null);
      badge.appendChild(lockEl);
    } else {
      return null;
    }

    if (opts.showHover !== false) {
      appendKnowledgeTestsMedalHover(badge, rankName, area);
    }
    return badge;
  }

  function createKnowledgeTestsMedalIcon(tierValue, titleText) {
    let parts = String(titleText || "").split(" — ");
    let rankName = parts[0] || "";
    let area = parts.slice(1).join(" — ") || "";
    return createKnowledgeTestsMedalBadge({
      tierValue: tierValue,
      rankName: rankName,
      achievementArea: area,
      size: "sm",
    });
  }

  function fillKnowledgeTestsMedalCell(cell, row) {
    if (!cell) {
      return;
    }
    cell.textContent = "";
    cell.classList.add("knowledge_tests_medal_cell");
    let tierRaw = knowledgeTestsRowMedalTier(row);
    let badge = createKnowledgeTestsMedalBadge({
      tierValue: tierRaw,
      rankName: knowledgeTestsMedalRankFromRow(row, tierRaw),
      achievementArea: knowledgeTestsMedalAreaFromRow(row),
      size: "md",
    });
    if (badge) {
      cell.appendChild(badge);
      return;
    }
    cell.textContent = formatKnowledgeTestsMedalLabel(tierRaw || row.medalTier, row.medalId);
  }

  window.createKnowledgeTestsMedalBadge = createKnowledgeTestsMedalBadge;
  window.createKnowledgeTestsMedalIcon = createKnowledgeTestsMedalIcon;
  window.fillKnowledgeTestsMedalCell = fillKnowledgeTestsMedalCell;
  window.formatKnowledgeTestsMedalLabel = formatKnowledgeTestsMedalLabel;
})();
