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
  document.querySelectorAll(".author_message[data-kt-author-id]").forEach(function (el) {
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

function refreshKnowledgeTestStarsOnAuthor(authorEl) {
  if (!authorEl) {
    return;
  }
  let userId = authorEl.getAttribute("data-kt-author-id");
  let existing = authorEl.querySelector(".knowledge_test_chat_stars");
  if (existing) {
    existing.remove();
  }
  let stars = knowledgeTestChatStarsCache[userId] || [];
  if (stars.length === 0) {
    return;
  }
  let box = createTagHtml("span", "knowledge_test_chat_stars");
  stars.forEach(function (star) {
    let tier = (star.tierValue || "").toLowerCase();
    let tip =
      (star.rankName || "") +
      " — " +
      (star.achievementArea || star.testTitle || "");
    let span = createTagHtml("span", "knowledge_test_chat_star knowledge_test_chat_star--" + tier, "★");
    span.setAttribute("title", tip);
    box.appendChild(span);
  });
  authorEl.appendChild(box);
}

function appendKnowledgeTestStarsToAuthor(authorEl, authorUserId) {
  if (!authorEl || !authorUserId) {
    return;
  }
  authorEl.setAttribute("data-kt-author-id", String(authorUserId));
  refreshKnowledgeTestStarsOnAuthor(authorEl);
}
