"use strict";

/**
 * Tests nav highlight on non-Tests PA pages (Phase 5 Wave C).
 * On the Tests page, knowledge_tests.js owns handlerTestsOnPage and calls updateKnowledgeTestsNavFromList.
 */

function requestKnowledgeTestsNavHighlight() {
  if (typeof where !== "undefined" && where === "knowledge_tests") {
    return;
  }
  if (!document.querySelector(".knowledge_tests_nav_link")) {
    return;
  }
  if (typeof socket === "undefined" || socket.readyState !== WebSocket.OPEN) {
    return;
  }
  socket.send("showTests::: " + JSON.stringify({ page: 1, perPage: 1 }));
}

function handlerTestsOnPage(data, user) {
  updateKnowledgeTestsNavFromList(data);
}
