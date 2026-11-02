"use strict";

window.knowledgeTestChatStarsCache = window.knowledgeTestChatStarsCache || {};

function requestKnowledgeTestChatStars(userIds) {
  if (typeof socket === "undefined" || socket.readyState !== WebSocket.OPEN) {
    return;
  }
  let ids = [];
  (userIds || []).forEach(function (id) {
    let n = parseInt(id, 10);
    if (n > 0) {
      ids.push(n);
    }
  });
  ids = ids.filter(function (id, index, arr) {
    return arr.indexOf(id) === index;
  });
  if (ids.length === 0) {
    return;
  }
  socket.send("getKnowledgeTestChatStars::: " + JSON.stringify({ userIds: ids }));
}

function handlerKnowledgeTestChatStarsOnPage(data) {
  if (!data || data.ok === false || !data.starsByUserId) {
    return;
  }
  Object.keys(data.starsByUserId).forEach(function (key) {
    knowledgeTestChatStarsCache[key] = data.starsByUserId[key];
  });
  document.querySelectorAll(".knowledge_tests_author_with_medals[data-kt-author-id]").forEach(function (el) {
    refreshKnowledgeTestStarsOnAuthor(el);
  });
}

function collectDialogAuthorIds(messages) {
  let ids = [];
  (messages || []).forEach(function (msg) {
    if (msg && msg.idAuthor) {
      ids.push(msg.idAuthor);
    }
  });
  return ids;
}

function refreshKnowledgeTestStarsOnAuthor(wrapEl) {
  if (!wrapEl) {
    return;
  }
  let userId = wrapEl.getAttribute("data-kt-author-id");
  let existing = wrapEl.querySelector(".knowledge_test_chat_stars");
  if (existing) {
    existing.remove();
  }
  let stars = knowledgeTestChatStarsCache[userId] || [];
  if (stars.length === 0) {
    return;
  }
  let box = createTagHtml("span", "knowledge_test_chat_stars");
  stars.forEach(function (star) {
    let badge =
      typeof createKnowledgeTestsMedalBadge === "function"
        ? createKnowledgeTestsMedalBadge({
            tierValue: star.tierValue,
            rankName: star.rankName,
            achievementArea: star.achievementArea || star.testTitle || "",
            size: "sm",
          })
        : null;
    if (badge) {
      box.appendChild(badge);
    }
  });
  if (box.childNodes.length === 0) {
    return;
  }
  wrapEl.appendChild(box);
}

function appendKnowledgeTestStarsToAuthor(authorWrapEl, authorUserId) {
  if (!authorWrapEl || !authorUserId) {
    return;
  }
  if (!authorWrapEl.classList.contains("knowledge_tests_author_with_medals")) {
    authorWrapEl.classList.add("knowledge_tests_author_with_medals");
  }
  authorWrapEl.setAttribute("data-kt-author-id", String(authorUserId));
  refreshKnowledgeTestStarsOnAuthor(authorWrapEl);
}
